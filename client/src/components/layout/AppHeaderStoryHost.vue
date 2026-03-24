<script setup lang="ts">
import { onBeforeMount } from 'vue'
import { useUiStore, type UiTheme } from '~/stores/ui'

const props = withDefaults(
  defineProps<{
    initialTheme?: UiTheme
    initialSidebarCollapsed?: boolean
    initialToasts?: number
  }>(),
  {
    initialTheme: 'system',
    initialSidebarCollapsed: false,
    initialToasts: 0,
  },
)

const ui = useUiStore()

onBeforeMount(() => {
  ui.setTheme(props.initialTheme)
  ui.sidebarCollapsed = props.initialSidebarCollapsed
  ui.setPendingToasts(props.initialToasts)
  ui.setGlobalLoading(false)
})
</script>

<template>
  <AppHeader />
</template>
