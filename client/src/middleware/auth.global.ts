import { defineNuxtRouteMiddleware, navigateTo } from '#imports'
import { useAuth } from '~/composables/useAuth'
import { useAuthStore } from '~/stores/auth'

const PUBLIC_ROUTES = new Set(['/login'])

export default defineNuxtRouteMiddleware(async (to) => {
  const authStore = useAuthStore()
  const { me } = useAuth()
  const isPublicRoute = PUBLIC_ROUTES.has(to.path)

  if (authStore.hasValidSession) {
    if (to.path === '/login') {
      return navigateTo('/')
    }
    return
  }

  if (authStore.accessToken && !authStore.user) {
    await me()
  }

  if (authStore.hasValidSession) {
    if (to.path === '/login') {
      return navigateTo('/')
    }
    return
  }

  if (isPublicRoute) {
    return
  }

  return navigateTo({
    path: '/login',
    query: {
      redirect: to.fullPath,
    },
  })
})
