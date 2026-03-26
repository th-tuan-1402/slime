<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from '#imports'
import type { LayoutTab } from '~/components/layout/MenuTabs.vue'
import { useTenant } from '~/composables/useTenant'
import { useAuthStore } from '~/stores/auth'
import { useMenuStore } from '~/stores/menu'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const menuStore = useMenuStore()
const tenant = useTenant()

const tabs = ref<LayoutTab[]>([])
const activeTabKey = ref<string | null>(null)

function routeToTabKey(): string {
  const routeName = route.name ? String(route.name) : route.path
  return `${routeName}:${route.fullPath}`
}

function routeToTabLabel(): string {
  if (route.name) {
    return String(route.name)
  }
  if (route.path === '/') {
    return 'home'
  }
  return route.path.replace('/', '') || 'page'
}

function syncTabFromRoute(): void {
  const key = routeToTabKey()
  const existing = tabs.value.find((tab) => tab.key === key)
  if (!existing) {
    tabs.value.push({
      key,
      label: routeToTabLabel(),
      to: route.fullPath,
    })
  }
  activeTabKey.value = key
}

function resetTabsForTenant(): void {
  tabs.value = []
  activeTabKey.value = null
  syncTabFromRoute()
}

async function loadMenuForCurrentTenant(): Promise<void> {
  await menuStore.loadForTenant(tenant.currentTenantId.value)
}

async function navigateMenu(path: string): Promise<void> {
  await router.push(path)
}

async function onSelectTab(tabKey: string): Promise<void> {
  const selected = tabs.value.find((tab) => tab.key === tabKey)
  if (!selected) {
    return
  }
  await router.push(selected.to)
}

async function onCloseTab(tabKey: string): Promise<void> {
  const index = tabs.value.findIndex((tab) => tab.key === tabKey)
  if (index < 0) {
    return
  }
  const wasActive = activeTabKey.value === tabKey
  tabs.value.splice(index, 1)

  if (!wasActive) {
    return
  }
  const fallback = tabs.value[index] ?? tabs.value[index - 1]
  if (!fallback) {
    await router.push('/')
    return
  }
  await router.push(fallback.to)
}

async function onChangeTenant(nextTenantId: string): Promise<void> {
  await tenant.switchTenant(nextTenantId, async () => {
    menuStore.reset()
    resetTabsForTenant()
    await loadMenuForCurrentTenant()
    await router.push('/')
  })
}

const currentUserName = computed(() => authStore.user?.user_name ?? 'Guest')

watch(
  () => route.fullPath,
  () => {
    syncTabFromRoute()
  },
  { immediate: true },
)

onMounted(async () => {
  await loadMenuForCurrentTenant()
})
</script>

<template>
  <div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
    <SideNav
      :items="menuStore.items"
      :is-loading="menuStore.isLoading"
      :error-message="menuStore.errorMessage"
      :active-path="route.path"
      @navigate="navigateMenu"
    />

    <div class="flex min-h-screen flex-1 flex-col">
      <TopBar
        :user-name="currentUserName"
        :current-tenant-id="tenant.currentTenantId.value"
        :tenant-options="tenant.tenantOptions"
        :is-switching-tenant="tenant.isSwitching.value"
        :tenant-error="tenant.lastError.value"
        @change-tenant="onChangeTenant"
      />

      <MenuTabs :tabs="tabs" :active-key="activeTabKey" @select="onSelectTab" @close="onCloseTab" />

      <main class="flex-1 p-4">
        <slot />
      </main>
    </div>
  </div>
</template>
