import { useCookie, useState } from '#imports'

// Must match backend `X-Tenant-ID` validation (7-char Crockford Base32).
const DEFAULT_TENANT = '2345678'
const TENANT_COOKIE_KEY = 'tenant_id'

export interface TenantOption {
  id: string
  name: string
}

const TENANT_OPTIONS: TenantOption[] = [
  { id: '2345678', name: 'Tenant A (local default)' },
  { id: '2345679', name: 'Tenant B' },
  { id: '234567a', name: 'Tenant Error (demo)' },
]

export function useTenant() {
  const tenantCookie = useCookie<string | null>(TENANT_COOKIE_KEY, {
    default: () => DEFAULT_TENANT,
    sameSite: 'lax',
  })
  const tenantState = useState<string>('tenantId', () => tenantCookie.value ?? DEFAULT_TENANT)
  const isSwitching = useState<boolean>('tenantSwitching', () => false)
  const lastError = useState<string | null>('tenantSwitchError', () => null)

  function clearTenantError(): void {
    lastError.value = null
  }

  async function switchTenant(
    nextTenantId: string,
    onAfterSwitch?: () => Promise<void> | void,
  ): Promise<boolean> {
    const previousTenantId = tenantState.value
    if (nextTenantId === previousTenantId) {
      return true
    }

    isSwitching.value = true
    lastError.value = null
    tenantState.value = nextTenantId
    tenantCookie.value = nextTenantId

    try {
      if (onAfterSwitch) {
        await onAfterSwitch()
      }
      return true
    } catch {
      tenantState.value = previousTenantId
      tenantCookie.value = previousTenantId
      lastError.value = 'Chuyen tenant that bai. Da phuc hoi tenant truoc do.'
      return false
    } finally {
      isSwitching.value = false
    }
  }

  return {
    tenantOptions: TENANT_OPTIONS,
    currentTenantId: tenantState,
    isSwitching,
    lastError,
    clearTenantError,
    switchTenant,
  }
}
