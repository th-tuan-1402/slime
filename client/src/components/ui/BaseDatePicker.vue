<script setup lang="ts">
withDefaults(
  defineProps<{
    modelValue?: string
    label?: string
    mode?: 'date' | 'month'
  }>(),
  {
    modelValue: '',
    label: '',
    mode: 'date',
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>

<template>
  <div class="space-y-1">
    <label v-if="label" class="text-sm text-gray-700">{{ label }}</label>
    <input
      class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
      :type="mode"
      :value="modelValue"
      @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    >
  </div>
</template>
