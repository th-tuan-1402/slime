<script setup lang="ts">
export interface LayoutTab {
  key: string
  label: string
  to: string
}

defineProps<{
  tabs: LayoutTab[]
  activeKey: string | null
}>()

const emit = defineEmits<{
  select: [key: string]
  close: [key: string]
}>()
</script>

<template>
  <nav
    class="flex min-h-12 items-end gap-2 overflow-x-auto border-b border-gray-200 bg-gray-50 px-4 pt-2 dark:border-gray-800 dark:bg-gray-900/60"
  >
    <button
      v-for="tab in tabs"
      :key="tab.key"
      class="inline-flex items-center gap-2 rounded-t border px-3 py-1.5 text-sm"
      :class="
        tab.key === activeKey
          ? 'border-gray-300 bg-white text-gray-900 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100'
          : 'border-transparent bg-gray-200/80 text-gray-600 hover:text-gray-800 dark:bg-gray-800 dark:text-gray-300'
      "
      @click="emit('select', tab.key)"
    >
      <span>{{ tab.label }}</span>
      <span class="rounded px-1 text-xs hover:bg-gray-200 dark:hover:bg-gray-700" @click.stop="emit('close', tab.key)">
        x
      </span>
    </button>
  </nav>
</template>
