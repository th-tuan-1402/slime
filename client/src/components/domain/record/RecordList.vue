<script setup lang="ts">
import { computed } from 'vue'
import type { DataTableColumn } from '~/components/ui/BaseDataTable.vue'
import type { SortDir } from '~/composables/usePagination'

const props = withDefaults(
  defineProps<{
    rows: Array<Record<string, unknown>>
    loading?: boolean
    page: number
    perPage: number
    total: number
    sortBy?: string
    sortDir?: SortDir
  }>(),
  {
    loading: false,
    sortBy: undefined,
    sortDir: undefined,
  },
)

const emit = defineEmits<{
  change: [
    payload: {
      page?: number
      perPage?: number
      sortBy?: string
      sortDir?: SortDir
    },
  ]
}>()

const columns = computed<DataTableColumn[]>(() => {
  const keys = new Set<string>()
  for (const row of props.rows) {
    for (const k of Object.keys(row)) keys.add(k)
  }
  const base = ['record_id', 'parent_record_id', 'record_outer_id', 'regist_date', 'update_date']
  const ordered = [...base.filter((k) => keys.has(k)), ...[...keys].filter((k) => !base.includes(k))]
  return ordered.slice(0, 12).map((key) => ({
    key,
    label: key,
    sortable: true,
  }))
})

const perPageOptions = [10, 20, 50, 100]

function onSort(key: string, dir: 'asc' | 'desc') {
  emit('change', { page: 1, sortBy: key, sortDir: dir })
}

function onPageChange(nextPage: number) {
  emit('change', { page: nextPage })
}

function onPerPageChange(next: number) {
  emit('change', { page: 1, perPage: next })
}
</script>

<template>
  <div class="space-y-3">
    <div class="flex items-center justify-end gap-2">
      <span class="text-sm text-gray-600">Per page</span>
      <USelect
        :model-value="perPage"
        :options="perPageOptions"
        size="xs"
        class="w-24"
        @update:model-value="onPerPageChange($event as number)"
      />
    </div>

    <BaseDataTable
      :columns="columns"
      :rows="rows"
      :loading="loading"
      :current-page="page"
      :per-page="perPage"
      :total="total"
      :sort-key="sortBy ?? null"
      :sort-dir="sortDir ?? null"
      @sort="onSort"
      @page-change="onPageChange"
    />
  </div>
</template>

