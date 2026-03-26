<script setup lang="ts">
import { ref } from 'vue'

type DynamicField = {
  key: string
  label: string
  type: 'text' | 'textarea' | 'select' | 'checkbox' | 'toggle' | 'date'
  options?: { label: string; value: string }[]
  required?: boolean
  placeholder?: string
}

const props = defineProps<{
  schema: DynamicField[]
  modelValue: Record<string, unknown>
}>()

const emit = defineEmits<{
  'update:modelValue': [value: Record<string, unknown>]
  submit: [value: Record<string, unknown>]
}>()

const errors = ref<Record<string, string>>({})

const updateValue = (key: string, value: unknown) => {
  emit('update:modelValue', {
    ...props.modelValue,
    [key]: value,
  })
}

const validate = () => {
  const nextErrors: Record<string, string> = {}
  props.schema.forEach((field) => {
    if (!field.required) return
    const value = props.modelValue[field.key]
    if (value === null || value === undefined || value === '') {
      nextErrors[field.key] = `${field.label} is required`
    }
  })
  errors.value = nextErrors
  return Object.keys(nextErrors).length === 0
}

const onSubmit = () => {
  if (!validate()) return
  emit('submit', props.modelValue)
}
</script>

<template>
  <form class="space-y-4" @submit.prevent="onSubmit">
    <div v-for="field in schema" :key="field.key" class="space-y-1">
      <label class="text-sm text-gray-700">{{ field.label }}</label>

      <UInput
        v-if="field.type === 'text'"
        :model-value="(modelValue[field.key] as string) || ''"
        :placeholder="field.placeholder"
        @update:model-value="updateValue(field.key, $event)"
      />
      <UTextarea
        v-else-if="field.type === 'textarea'"
        :model-value="(modelValue[field.key] as string) || ''"
        :placeholder="field.placeholder"
        @update:model-value="updateValue(field.key, $event)"
      />
      <USelect
        v-else-if="field.type === 'select'"
        :model-value="(modelValue[field.key] as string) || ''"
        :options="field.options || []"
        @update:model-value="updateValue(field.key, $event)"
      />
      <UCheckbox
        v-else-if="field.type === 'checkbox'"
        :model-value="Boolean(modelValue[field.key])"
        @update:model-value="updateValue(field.key, $event)"
      />
      <UToggle
        v-else-if="field.type === 'toggle'"
        :model-value="Boolean(modelValue[field.key])"
        @update:model-value="updateValue(field.key, $event)"
      />
      <BaseDatePicker
        v-else-if="field.type === 'date'"
        :model-value="(modelValue[field.key] as string) || ''"
        @update:model-value="updateValue(field.key, $event)"
      />

      <p v-if="errors[field.key]" class="text-xs text-red-600">{{ errors[field.key] }}</p>
    </div>

    <slot name="actions">
      <UButton type="submit">Submit</UButton>
    </slot>
  </form>
</template>
