<script setup lang="ts">
import { useUiStore } from '~/stores/ui'

const ui = useUiStore()

async function mockLoadingToggle(): Promise<void> {
  ui.setGlobalLoading(true)
  await new Promise((r) => setTimeout(r, 600))
  ui.setGlobalLoading(false)
}
</script>

<template>
  <header
    class="flex flex-wrap items-center gap-3 border-b border-gray-200 bg-white px-4 py-2 dark:border-gray-800 dark:bg-gray-950"
  >
    <UButton
      icon="i-heroicons-bars-3"
      variant="ghost"
      color="gray"
      :aria-pressed="ui.sidebarCollapsed"
      :aria-label="ui.sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
      @click="ui.toggleSidebar()"
    />
    <span class="font-semibold text-gray-900 dark:text-gray-100"> Studio </span>
    <UBadge v-if="ui.isGlobalLoading" color="primary" variant="soft"> Loading… </UBadge>
    <span class="text-sm text-gray-600 dark:text-gray-400">
      Sidebar:
      {{ ui.sidebarCollapsed ? 'collapsed' : 'open' }}
    </span>
    <span class="text-sm text-gray-600 dark:text-gray-400">
      Theme: {{ ui.activeTheme }} ({{ ui.isDarkMode ? 'dark' : 'light' }})
    </span>
    <span class="text-sm text-gray-600 dark:text-gray-400"> Toasts: {{ ui.pendingToasts }} </span>
    <div class="ml-auto flex flex-wrap items-center gap-2">
      <UButton size="xs" variant="outline" color="gray" @click="ui.setTheme('light')"> Light </UButton>
      <UButton size="xs" variant="outline" color="gray" @click="ui.setTheme('dark')"> Dark </UButton>
      <UButton size="xs" variant="outline" color="gray" @click="ui.setTheme('system')"> System </UButton>
      <UButton size="xs" variant="solid" color="primary" @click="mockLoadingToggle"> Toggle loading </UButton>
      <UButton size="xs" variant="soft" color="gray" @click="ui.setPendingToasts(ui.pendingToasts + 1)">
        + toast
      </UButton>
    </div>
  </header>
</template>
