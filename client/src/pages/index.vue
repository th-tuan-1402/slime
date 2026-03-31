<template>
  <AppLayout>
    <div class="rounded border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-950">
      <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Home</h1>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
        Logged in as
        <span class="font-semibold">{{ authStore.user?.user_name ?? authStore.user?.login_id ?? 'User' }}</span>
      </p>
      <UButton class="mt-4" color="primary" :loading="authStore.isLoading" @click="onLogout">
        Logout
      </UButton>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { navigateTo } from '#imports'
import { useAuth } from '~/composables/useAuth'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const { logout } = useAuth()

async function onLogout(): Promise<void> {
  await logout()
  await navigateTo('/login')
}
</script>

