<script setup lang="ts">
import { useToast } from '#imports'

const toast = useToast()

const notify = () => {
  toast.add({
    title: 'Saved',
    description: 'Your changes have been saved.',
    color: 'green',
  })
}
</script>

<template>
  <UButton @click="notify">Show toast</UButton>
</template>
