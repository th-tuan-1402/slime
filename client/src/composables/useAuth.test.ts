/// <reference types="vitest/globals" />
import { useAuth } from '~/composables/useAuth'
import * as nuxtImports from '#imports'
import * as authStoreModule from '~/stores/auth'
import * as endpointModule from '~/api/generated/endpoints'

const mockStore = {
  accessToken: null as null | string,
  refreshToken: null as null | string,
  user: null as null | { id: number; name: string },
  isLoading: false,
  setLoading: vi.fn((isLoading: boolean) => {
    mockStore.isLoading = isLoading
  }),
  setSession: vi.fn((payload: { accessToken: string; refreshToken?: string | null; user?: unknown }) => {
    mockStore.accessToken = payload.accessToken
    mockStore.refreshToken = payload.refreshToken ?? null
    mockStore.user = (payload.user ?? null) as { id: number; name: string } | null
  }),
  clearSession: vi.fn(() => {
    mockStore.accessToken = null
    mockStore.refreshToken = null
    mockStore.user = null
  }),
}
const mockTokenCookie = { value: null as null | string }
const mockTokenState = { value: null as null | string }

vi.mock('#imports', () => ({
  useCookie: vi.fn(),
  useState: vi.fn(),
}))

vi.mock('~/stores/auth', () => ({
  useAuthStore: vi.fn(),
}))

vi.mock('~/api/generated/endpoints', () => ({
  getAuthMe: vi.fn(),
  postAuthLogin: vi.fn(),
  postAuthLogout: vi.fn(),
  postAuthToken: vi.fn(),
}))

describe('useAuth', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    mockStore.accessToken = null
    mockStore.refreshToken = null
    mockStore.user = null
    mockStore.isLoading = false
    mockTokenCookie.value = null
    mockTokenState.value = null

    vi.mocked(nuxtImports.useCookie).mockReturnValue(mockTokenCookie as never)
    vi.mocked(nuxtImports.useState).mockReturnValue(mockTokenState as never)
    vi.mocked(authStoreModule.useAuthStore).mockReturnValue(mockStore as never)
  })

  it('logs in successfully and persists token/session', async () => {
    vi.mocked(endpointModule.postAuthLogin).mockResolvedValue({
      status: 200,
      data: {
        success: true,
        message: 'ok',
        data: {
          token: 'token-123',
          user: { id: 1, name: 'tester' } as never,
        },
      },
    } as never)

    const { login } = useAuth()
    const user = await login({ login_id: 'demo', password: 'pw' })

    expect(user).toEqual({ id: 1, name: 'tester' })
    expect(mockStore.setSession).toHaveBeenCalledWith({
      accessToken: 'token-123',
      user: { id: 1, name: 'tester' },
    })
    expect(mockTokenCookie.value).toBe('token-123')
    expect(mockTokenState.value).toBe('token-123')
  })

  it('clears session when login fails', async () => {
    vi.mocked(endpointModule.postAuthLogin).mockRejectedValue(new Error('Invalid credentials.'))

    const { login } = useAuth()
    await expect(login({ login_id: 'demo', password: 'wrong' })).rejects.toEqual({
      message: 'Invalid credentials.',
    })
    expect(mockStore.clearSession).toHaveBeenCalled()
    expect(mockTokenCookie.value).toBeNull()
    expect(mockTokenState.value).toBeNull()
  })

  it('clears local session on logout', async () => {
    mockStore.accessToken = 'token-123'
    vi.mocked(endpointModule.postAuthLogout).mockResolvedValue({} as never)

    const { logout } = useAuth()
    await logout()

    expect(endpointModule.postAuthLogout).toHaveBeenCalledTimes(1)
    expect(mockStore.clearSession).toHaveBeenCalledTimes(1)
    expect(mockTokenCookie.value).toBeNull()
    expect(mockTokenState.value).toBeNull()
  })
})
