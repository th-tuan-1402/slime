/// <reference types="vitest/globals" />
import authMiddleware from '~/server/middleware/auth'
import * as h3 from 'h3'

vi.mock('h3', () => ({
  defineEventHandler: vi.fn((handler: unknown) => handler),
  getCookie: vi.fn(),
  getRequestURL: vi.fn(),
  sendRedirect: vi.fn(),
}))

describe('server auth middleware', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.mocked(h3.getRequestURL).mockReturnValue({
      pathname: '/dashboard',
      search: '?tab=report',
    } as never)
  })

  it('redirects unauthenticated protected routes to login', () => {
    vi.mocked(h3.getCookie).mockReturnValue(undefined)
    vi.mocked(h3.sendRedirect).mockReturnValue('redirect-response' as never)

    const event = { path: '/dashboard' } as never
    const result = authMiddleware(event)

    expect(h3.sendRedirect).toHaveBeenCalledWith(
      event,
      '/login?redirect=%2Fdashboard%3Ftab%3Dreport',
      302,
    )
    expect(result).toBe('redirect-response')
  })

  it('skips redirect for /login to avoid redirect loop', () => {
    vi.mocked(h3.getCookie).mockReturnValue(undefined)

    const result = authMiddleware({ path: '/login' } as never)

    expect(h3.sendRedirect).not.toHaveBeenCalled()
    expect(result).toBeUndefined()
  })
})
