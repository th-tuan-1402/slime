import { defineNuxtPlugin } from '#app'
import { useAuth } from '~/composables/useAuth'
import { useAuthStore } from '~/stores/auth'

export default defineNuxtPlugin(async () => {
  const authStore = useAuthStore()
  const { me } = useAuth()

  authStore.hydrateFromStorage()
  if (authStore.accessToken) {
    await me()
  }
})
