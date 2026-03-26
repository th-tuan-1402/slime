/// <reference types="vitest/globals" />
import authMiddleware from '~/middleware/auth.global'
import * as nuxtImports from '#imports'
import * as authStoreModule from '~/stores/auth'
import * as authModule from '~/composables/useAuth'

const mockStore = {
  hasValidSession: false,
  accessToken: null as null | string,
  user: null as null | { id: number },
}
const mockMe = vi.fn()

vi.mock('#imports', () => ({
  defineNuxtRouteMiddleware: vi.fn((handler: unknown) => handler),
  navigateTo: vi.fn(),
}))

vi.mock('~/stores/auth', () => ({
  useAuthStore: vi.fn(),
}))

vi.mock('~/composables/useAuth', () => ({
  useAuth: vi.fn(),
}))

describe('auth.global middleware', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    mockStore.hasValidSession = false
    mockStore.accessToken = null
    mockStore.user = null

    vi.mocked(nuxtImports.navigateTo).mockReturnValue(undefined)
    vi.mocked(authStoreModule.useAuthStore).mockReturnValue(mockStore as never)
    vi.mocked(authModule.useAuth).mockReturnValue({ me: mockMe } as never)
  })

  it('redirects protected route to /login with redirect query', async () => {
    vi.mocked(nuxtImports.navigateTo).mockReturnValue('redirected' as never)

    const result = await authMiddleware({
      path: '/dashboard',
      fullPath: '/dashboard?tab=report',
    } as never, {} as never)

    expect(nuxtImports.navigateTo).toHaveBeenCalledWith({
      path: '/login',
      query: {
        redirect: '/dashboard?tab=report',
      },
    })
    expect(result).toBe('redirected')
  })

  it('does not redirect /login when user is unauthenticated (loop prevention)', async () => {
    const result = await authMiddleware({
      path: '/login',
      fullPath: '/login',
    } as never, {} as never)

    expect(nuxtImports.navigateTo).not.toHaveBeenCalled()
    expect(result).toBeUndefined()
  })

  it('restores session via me() before redirect decision', async () => {
    mockStore.accessToken = 'token-123'
    mockMe.mockImplementation(async () => {
      mockStore.hasValidSession = true
      mockStore.user = { id: 1 }
    })

    const result = await authMiddleware({
      path: '/dashboard',
      fullPath: '/dashboard',
    } as never, {} as never)

    expect(mockMe).toHaveBeenCalledTimes(1)
    expect(nuxtImports.navigateTo).not.toHaveBeenCalled()
    expect(result).toBeUndefined()
  })

  it('redirects authenticated user away from /login', async () => {
    mockStore.hasValidSession = true
    vi.mocked(nuxtImports.navigateTo).mockReturnValue('home' as never)

    const result = await authMiddleware({
      path: '/login',
      fullPath: '/login',
    } as never, {} as never)

    expect(nuxtImports.navigateTo).toHaveBeenCalledWith('/')
    expect(result).toBe('home')
  })
})
