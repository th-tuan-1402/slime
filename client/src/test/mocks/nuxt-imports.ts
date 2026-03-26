export function defineNuxtRouteMiddleware<T>(handler: T): T {
  return handler
}

export function useCookie<T>() {
  return { value: null as T | null }
}

export function useState<T>(_key: string, init: () => T) {
  return { value: init() }
}

export function navigateTo(...args: unknown[]) {
  void args
  return undefined
}
