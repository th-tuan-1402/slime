<script setup lang="ts">
import type { MenuItem } from '~/stores/menu'

const props = withDefaults(
  defineProps<{
    items: MenuItem[]
    isLoading?: boolean
    errorMessage?: string | null
    activePath?: string
  }>(),
  {
    isLoading: false,
    errorMessage: null,
    activePath: '/',
  },
)

const emit = defineEmits<{
  navigate: [path: string]
}>()

function isActive(item: MenuItem): boolean {
  if (!item.to) {
    return false
  }
  return props.activePath === item.to
}

function handleClick(item: MenuItem): void {
  if (!item.to || item.disabled) {
    return
  }
  emit('navigate', item.to)
}
</script>

<template>
  <aside class="w-64 border-r border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950">
    <p class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Navigation</p>

    <div v-if="isLoading" class="rounded border border-dashed border-gray-300 p-3 text-sm text-gray-500">
      Loading menu...
    </div>

    <div
      v-else-if="errorMessage"
      class="rounded border border-amber-300 bg-amber-50 p-3 text-sm text-amber-700 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300"
    >
      {{ errorMessage }}
    </div>

    <ul v-else class="space-y-2 text-sm">
      <li v-for="item in items" :key="item.id">
        <button
          class="w-full rounded px-2 py-1.5 text-left transition hover:bg-gray-100 dark:hover:bg-gray-800"
          :class="{
            'bg-primary-50 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300': isActive(item),
            'opacity-50': item.disabled,
          }"
          :disabled="item.disabled"
          @click="handleClick(item)"
        >
          {{ item.label }}
        </button>

        <ul v-if="item.children?.length" class="mt-1 space-y-1 pl-4">
          <li v-for="child in item.children" :key="child.id">
            <button
              class="w-full rounded px-2 py-1 text-left text-xs transition hover:bg-gray-100 dark:hover:bg-gray-800"
              :class="{
                'bg-primary-50 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300': isActive(child),
                'opacity-50': child.disabled,
              }"
              :disabled="child.disabled"
              @click="handleClick(child)"
            >
              {{ child.label }}
            </button>
          </li>
        </ul>
      </li>
    </ul>
  </aside>
</template>
