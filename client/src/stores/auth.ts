import type { UserResource } from '~/api/generated/model/userResource'

const AUTH_STORAGE_KEY = 'slime:auth:v1'

export interface AuthSessionPayload {
  accessToken: string
  refreshToken?: string | null
  user?: UserResource | null
}

export interface StoredAuthState {
  accessToken: string | null
  refreshToken: string | null
  user: UserResource | null
}

function readStorage(): string | null {
  if (!import.meta.client || typeof localStorage === 'undefined') {
    return null
  }
  return localStorage.getItem(AUTH_STORAGE_KEY)
}

function writeStorage(state: StoredAuthState): void {
  if (!import.meta.client || typeof localStorage === 'undefined') {
    return
  }
  const payload: StoredAuthState = {
    accessToken: state.accessToken,
    refreshToken: state.refreshToken,
    user: state.user,
  }
  localStorage.setItem(AUTH_STORAGE_KEY, JSON.stringify(payload))
}

function clearStorage(): void {
  if (!import.meta.client || typeof localStorage === 'undefined') {
    return
  }
  localStorage.removeItem(AUTH_STORAGE_KEY)
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    accessToken: null as string | null,
    refreshToken: null as string | null,
    user: null as UserResource | null,
    isLoading: false,
    isHydrated: false,
  }),

  getters: {
    isAuthenticated: (state) => Boolean(state.accessToken) && Boolean(state.user),
    hasValidSession: (state) =>
      Boolean(state.accessToken) && Boolean(state.user),
    authHeader: (state) =>
      state.accessToken ? `Bearer ${state.accessToken}` : null,
  },

  actions: {
    setSession(payload: AuthSessionPayload): void {
      this.accessToken = payload.accessToken
      this.refreshToken = payload.refreshToken ?? null
      this.user = payload.user ?? null
      this.isHydrated = true
      writeStorage({
        accessToken: this.accessToken,
        refreshToken: this.refreshToken,
        user: this.user,
      })
    },

    clearSession(): void {
      this.accessToken = null
      this.refreshToken = null
      this.user = null
      this.isLoading = false
      this.isHydrated = true
      clearStorage()
    },

    setLoading(isLoading: boolean): void {
      this.isLoading = isLoading
    },

    hydrateFromStorage(): void {
      if (!import.meta.client) {
        return
      }
      const raw = readStorage()
      if (!raw) {
        this.isHydrated = true
        return
      }
      try {
        const parsed = JSON.parse(raw) as Partial<StoredAuthState>
        this.accessToken = typeof parsed.accessToken === 'string' ? parsed.accessToken : null
        this.refreshToken =
          typeof parsed.refreshToken === 'string' ? parsed.refreshToken : null
        this.user = parsed.user ?? null
      } catch {
        clearStorage()
        this.accessToken = null
        this.refreshToken = null
        this.user = null
      } finally {
        this.isHydrated = true
      }
    },
  },
})
