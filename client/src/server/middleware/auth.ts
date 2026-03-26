import { defineEventHandler, getCookie, getRequestURL, sendRedirect } from 'h3'

const PUBLIC_PATH_PREFIXES = ['/login', '/_nuxt', '/api', '/__nuxt_error']

export default defineEventHandler((event) => {
  if (!event.path.startsWith('/')) {
    return
  }

  const isPublicPath = PUBLIC_PATH_PREFIXES.some((prefix) => event.path.startsWith(prefix))
  if (isPublicPath) {
    return
  }

  const authToken = getCookie(event, 'auth_token')
  if (authToken) {
    return
  }

  const requestUrl = getRequestURL(event)
  const redirect = encodeURIComponent(requestUrl.pathname + requestUrl.search)
  return sendRedirect(event, `/login?redirect=${redirect}`, 302)
})
