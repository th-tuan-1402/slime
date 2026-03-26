<script setup lang="ts">
import { ref } from 'vue'

export type TreeNode = {
  id: string | number
  label: string
  children?: TreeNode[]
}

defineProps<{
  nodes: TreeNode[]
  modelValue?: string | number | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string | number]
  select: [node: TreeNode]
}>()

const expanded = ref<Set<string | number>>(new Set())

const isExpanded = (id: string | number) => expanded.value.has(id)
const toggleExpand = (id: string | number) => {
  if (expanded.value.has(id)) {
    expanded.value.delete(id)
  } else {
    expanded.value.add(id)
  }
}

const onSelect = (node: TreeNode) => {
  emit('update:modelValue', node.id)
  emit('select', node)
}
</script>

<template>
  <ul class="space-y-1">
    <li v-for="node in nodes" :key="node.id">
      <div class="flex items-center gap-2">
        <button
          v-if="node.children?.length"
          type="button"
          class="w-5 text-xs text-gray-500"
          @click="toggleExpand(node.id)"
        >
          {{ isExpanded(node.id) ? '▼' : '▶' }}
        </button>
        <span v-else class="inline-block w-5" />
        <button
          type="button"
          class="text-sm"
          :class="modelValue === node.id ? 'font-semibold text-primary-600' : 'text-gray-700'"
          @click="onSelect(node)"
        >
          {{ node.label }}
        </button>
      </div>
      <div v-if="node.children?.length && isExpanded(node.id)" class="ml-5 mt-1">
        <BaseTreeView
          :nodes="node.children"
          :model-value="modelValue"
          @update:model-value="emit('update:modelValue', $event)"
          @select="emit('select', $event)"
        />
      </div>
    </li>
  </ul>
</template>
