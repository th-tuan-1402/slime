<script setup lang="ts">
import { computed, ref } from 'vue'

export type DataTableColumn = {
  key: string
  label: string
  sortable?: boolean
}

const props = withDefaults(
  defineProps<{
    columns: DataTableColumn[]
    rows: Record<string, unknown>[]
    loading?: boolean
    currentPage?: number
    perPage?: number
    total?: number
  }>(),
  {
    loading: false,
    currentPage: 1,
    perPage: 10,
    total: 0,
  },
)

const emit = defineEmits<{
  sort: [key: string, direction: 'asc' | 'desc']
  pageChange: [page: number]
}>()

const sortState = ref<{ key: string; direction: 'asc' | 'desc' } | null>(null)
const totalPages = computed(() =>
  Math.max(1, Math.ceil((props.total || props.rows.length) / props.perPage)),
)

const toggleSort = (key: string, sortable?: boolean) => {
  if (!sortable) return
  const current = sortState.value
  const nextDirection =
    current && current.key === key && current.direction === 'asc' ? 'desc' : 'asc'
  sortState.value = { key, direction: nextDirection }
  emit('sort', key, nextDirection)
}

const movePage = (nextPage: number) => {
  if (nextPage < 1 || nextPage > totalPages.value || nextPage === props.currentPage) return
  emit('pageChange', nextPage)
}
</script>

<template>
  <div class="space-y-4">
    <div class="overflow-x-auto border border-gray-200 rounded-md">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              class="px-3 py-2 text-left font-semibold text-gray-700"
            >
              <button
                type="button"
                class="inline-flex items-center gap-1"
                :class="{ 'cursor-default': !column.sortable }"
                @click="toggleSort(column.key, column.sortable)"
              >
                {{ column.label }}
                <span
                  v-if="sortState?.key === column.key"
                  class="text-xs text-gray-500"
                >
                  {{ sortState.direction === 'asc' ? '▲' : '▼' }}
                </span>
              </button>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          <tr v-if="loading">
            <td :colspan="columns.length" class="px-3 py-8 text-center text-gray-500">
              Loading...
            </td>
          </tr>
          <tr v-else-if="rows.length === 0">
            <td :colspan="columns.length" class="px-3 py-8 text-center text-gray-500">
              No data
            </td>
          </tr>
          <tr v-for="(row, index) in rows" v-else :key="index">
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-3 py-2 text-gray-800"
            >
              {{ row[column.key] }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex items-center justify-end gap-2">
      <UButton
        size="xs"
        variant="soft"
        :disabled="currentPage <= 1"
        @click="movePage(currentPage - 1)"
      >
        Prev
      </UButton>
      <span class="text-sm text-gray-600">{{ currentPage }} / {{ totalPages }}</span>
      <UButton
        size="xs"
        variant="soft"
        :disabled="currentPage >= totalPages"
        @click="movePage(currentPage + 1)"
      >
        Next
      </UButton>
    </div>
  </div>
</template>
