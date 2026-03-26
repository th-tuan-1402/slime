import type { LoginRequestBody } from '~/api/generated/model/loginRequestBody'
import type { UserResource } from '~/api/generated/model/userResource'
import { useCookie, useState } from '#imports'
import { getAuthMe, postAuthLogin, postAuthLogout, postAuthToken } from '~/api/generated/endpoints'
import { useAuthStore } from '~/stores/auth'

export interface AuthError {
  message: string
  status?: number
}

const DEFAULT_AUTH_ERROR: AuthError = {
  message: 'Authentication request failed.',
}

function normalizeAuthError(error: unknown): AuthError {
  if (error instanceof Error && error.message !== '') {
    return { message: error.message }
  }
  return DEFAULT_AUTH_ERROR
}

function syncTokenState(token: string | null): void {
  const tokenState = useState<string | null>('authToken', () => null)
  const tokenCookie = useCookie<string | null>('auth_token', {
    default: () => null,
    sameSite: 'lax',
  })
  tokenState.value = token
  tokenCookie.value = token
}

export function useAuth() {
  const authStore = useAuthStore()

  async function me(): Promise<UserResource | null> {
    if (!authStore.accessToken) {
      authStore.clearSession()
      syncTokenState(null)
      return null
    }

    authStore.setLoading(true)
    try {
      const response = await getAuthMe()
      const user = response.data.data
      authStore.setSession({
        accessToken: authStore.accessToken,
        refreshToken: authStore.refreshToken,
        user,
      })
      return user
    } catch {
      authStore.clearSession()
      syncTokenState(null)
      return null
    } finally {
      authStore.setLoading(false)
    }
  }

  async function login(credentials: LoginRequestBody): Promise<UserResource> {
    authStore.setLoading(true)
    try {
      const response = await postAuthLogin(credentials)
      if (response.status !== 200 || !response.data.success) {
        throw {
          message: response.data.message,
          status: response.status,
        } satisfies AuthError
      }
      const token = response.data.data.token
      const user = response.data.data.user
      authStore.setSession({
        accessToken: token,
        user,
      })
      syncTokenState(token)
      return user
    } catch (error) {
      authStore.clearSession()
      syncTokenState(null)
      throw normalizeAuthError(error)
    } finally {
      authStore.setLoading(false)
    }
  }

  async function refreshToken(): Promise<string | null> {
    if (!authStore.accessToken) {
      return null
    }
    try {
      const response = await postAuthToken()
      const nextToken = response.data.data.token
      authStore.setSession({
        accessToken: nextToken,
        refreshToken: authStore.refreshToken,
        user: authStore.user,
      })
      syncTokenState(nextToken)
      return nextToken
    } catch {
      await logout()
      return null
    }
  }

  async function logout(): Promise<void> {
    try {
      if (authStore.accessToken) {
        await postAuthLogout()
      }
    } finally {
      authStore.clearSession()
      syncTokenState(null)
    }
  }

  return {
    login,
    logout,
    me,
    refreshToken,
  }
}
