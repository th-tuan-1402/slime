<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter, useToast, definePageMeta, useRuntimeConfig, useCookie } from '#imports'
import RecordList from '~/components/domain/record/RecordList.vue'
import RecordSearch from '~/components/domain/record/RecordSearch.vue'
import {
  buildRecordListRouteQuery,
  normalizeRecordListQuery,
  parseFilters,
  type RecordListQuery,
  type SortDir,
} from '~/composables/usePagination'
import { useRecords } from '~/composables/useRecords'

definePageMeta({
  title: 'Records',
})

const route = useRoute()
const router = useRouter()
const toast = useToast()
const runtime = useRuntimeConfig()

function buildHeaders(): HeadersInit {
  const tenantId = useCookie<string | null>('tenant_id').value
  const token = useCookie<string | null>('auth_token').value
  const headers: Record<string, string> = {}
  if (tenantId) headers['X-Tenant-ID'] = tenantId
  if (token) headers['Authorization'] = `Bearer ${token}`
  return headers
}

const schemaId = computed(() => String(route.params.id))
const state = computed<RecordListQuery>(() =>
  normalizeRecordListQuery(route.query as Record<string, unknown>),
)

const { data, pending, error, refresh } = await useRecords(schemaId.value, state.value)

const showSearch = ref(true)
const exporting = ref(false)
const importing = ref(false)
const importFile = ref<File | null>(null)

const rows = computed(() => data.value?.data ?? [])
const meta = computed(() => data.value?.meta)

const page = computed(() => meta.value?.current_page ?? state.value.page)
const perPage = computed(() => meta.value?.per_page ?? state.value.perPage)
const total = computed(() => meta.value?.total ?? 0)

const qProp = computed(() => (typeof route.query.q === 'string' ? route.query.q : undefined))
const filtersJsonProp = computed(() =>
  typeof route.query.filters === 'string' ? route.query.filters : undefined,
)

async function updateQuery(partial: Partial<RecordListQuery>) {
  const next: RecordListQuery = {
    ...state.value,
    ...partial,
  }
  await router.replace({
    query: buildRecordListRouteQuery(next),
  })
}

async function onSearchSubmit(payload: { q?: string; filtersJson?: string }) {
  const filters = payload.filtersJson ? parseFilters(payload.filtersJson) : undefined
  await updateQuery({
    page: 1,
    q: payload.q,
    filters,
  })
}

async function onSearchClear() {
  await updateQuery({
    page: 1,
    q: undefined,
    filters: undefined,
    sortBy: undefined,
    sortDir: undefined,
  })
}

async function onListChange(payload: {
  page?: number
  perPage?: number
  sortBy?: string
  sortDir?: SortDir
}) {
  await updateQuery({
    ...payload,
  })
}

async function exportCsv() {
  try {
    exporting.value = true
    const params = new URLSearchParams(buildRecordListRouteQuery(state.value))
    const blob = await $fetch<Blob>(
      `/api/v1/schemas/${encodeURIComponent(schemaId.value)}/records/export?${params.toString()}`,
      {
        baseURL: runtime.public.apiBase,
        headers: buildHeaders(),
        responseType: 'blob',
      },
    )
    const a = document.createElement('a')
    a.href = URL.createObjectURL(blob)
    a.download = `records-schema-${schemaId.value}.csv`
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(a.href)
  } catch (e) {
    toast.add({
      title: 'Export failed',
      description: e instanceof Error ? e.message : 'Unexpected error',
      color: 'red',
    })
  } finally {
    exporting.value = false
  }
}

async function importCsv() {
  if (!importFile.value) return
  try {
    importing.value = true
    const form = new FormData()
    form.append('file', importFile.value)
    const params = new URLSearchParams(buildRecordListRouteQuery(state.value))
    const body = await $fetch<{ success: boolean; message?: string; data?: unknown }>(
      `/api/v1/schemas/${encodeURIComponent(schemaId.value)}/records/import?${params.toString()}`,
      {
        method: 'POST',
        baseURL: runtime.public.apiBase,
        headers: buildHeaders(),
        body: form,
      },
    )
    if (!body?.success) {
      throw new Error(body?.message || 'Import failed')
    }
    toast.add({
      title: 'Import success',
      description: 'CSV imported.',
      color: 'green',
    })
    importFile.value = null
    await refresh()
  } catch (e) {
    toast.add({
      title: 'Import failed',
      description: e instanceof Error ? e.message : 'Unexpected error',
      color: 'red',
    })
  } finally {
    importing.value = false
  }
}
</script>

<template>
  <div class="space-y-4 p-4">
    <div class="flex items-start justify-between gap-3">
      <div>
        <h1 class="text-lg font-semibold">Records</h1>
        <p class="text-sm text-gray-600">Schema: {{ schemaId }}</p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <UButton size="xs" variant="soft" @click="showSearch = !showSearch">
          {{ showSearch ? 'Hide search' : 'Show search' }}
        </UButton>

        <UButton size="xs" variant="soft" :loading="exporting" :disabled="exporting" @click="exportCsv">
          Export CSV
        </UButton>

        <div class="flex items-center gap-2">
          <input
            type="file"
            accept=".csv,text/csv"
            class="text-xs"
            @change="importFile = ($event.target as HTMLInputElement).files?.[0] ?? null"
          >
          <UButton
            size="xs"
            color="primary"
            :loading="importing"
            :disabled="importing || !importFile"
            @click="importCsv"
          >
            Import CSV
          </UButton>
        </div>
      </div>
    </div>

    <UAlert
      v-if="error"
      color="red"
      variant="soft"
      title="Failed to load records"
      :description="(error as Error).message"
    >
      <template #actions>
        <UButton size="xs" variant="soft" @click="refresh">Retry</UButton>
      </template>
    </UAlert>

    <RecordSearch
      v-if="showSearch"
      :q="qProp"
      :filters-json="filtersJsonProp"
      @submit="onSearchSubmit"
      @clear="onSearchClear"
    />

    <RecordList
      :rows="rows"
      :loading="pending"
      :page="page"
      :per-page="perPage"
      :total="total"
      :sort-by="state.sortBy"
      :sort-dir="state.sortDir"
      @change="onListChange"
    />
  </div>
</template>

