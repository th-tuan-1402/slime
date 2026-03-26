<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    modelValue: boolean
    title?: string
    description?: string
    loading?: boolean
  }>(),
  {
    title: '',
    description: '',
    loading: false,
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  confirm: []
  cancel: []
}>()

const close = () => {
  emit('cancel')
  emit('update:modelValue', false)
}

const onConfirm = () => emit('confirm')
</script>

<template>
  <UModal :model-value="props.modelValue" @update:model-value="emit('update:modelValue', $event)">
    <UCard>
      <template #header>
        <div class="font-semibold">{{ title }}</div>
        <div v-if="description" class="text-sm text-gray-500 mt-1">{{ description }}</div>
      </template>

      <slot />

      <template #footer>
        <div class="flex justify-end gap-2">
          <UButton variant="ghost" :disabled="loading" @click="close">Cancel</UButton>
          <UButton :loading="loading" @click="onConfirm">Confirm</UButton>
        </div>
      </template>
    </UCard>
  </UModal>
</template>
