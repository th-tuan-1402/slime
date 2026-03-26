<script setup lang="ts">
const props = defineProps<{
  items: { key: string; label: string }[]
  modelValue: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>

<template>
  <div class="flex gap-2 border-b border-gray-200">
    <button
      v-for="tab in props.items"
      :key="tab.key"
      type="button"
      class="px-3 py-2 text-sm border-b-2"
      :class="modelValue === tab.key ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-600'"
      @click="emit('update:modelValue', tab.key)"
    >
      {{ tab.label }}
    </button>
  </div>
</template>
