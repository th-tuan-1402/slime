<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    label: string
    color?: 'primary' | 'gray' | 'red' | 'green'
    variant?: 'solid' | 'outline' | 'soft' | 'ghost'
    loading?: boolean
    disabled?: boolean
  }>(),
  {
    color: 'primary',
    variant: 'solid',
    loading: false,
    disabled: false,
  },
)
</script>

<template>
  <UButton
    :color="props.color"
    :variant="props.variant"
    :loading="props.loading"
    :disabled="props.disabled || props.loading"
  >
    {{ label }}
  </UButton>
</template>
