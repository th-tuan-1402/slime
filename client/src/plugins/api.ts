import { configureApiClient } from '../api/client';

/**
 * Wires the Orval mutator base URL and injects Authorization + X-Tenant-ID.
 *
 * Token / tenant resolution: cookies and/or Nuxt state can be set by auth flows later.
 */
export default defineNuxtPlugin(() => {
  const runtime = useRuntimeConfig();
  const authToken = useCookie<string | null>('auth_token', {
    default: () => null,
    sameSite: 'lax',
  });
  const tenantId = useCookie<string | null>('tenant_id', {
    default: () => null,
    sameSite: 'lax',
  });
  const tenantState = useState<string | null>('tenantId', () => null);
  const tokenState = useState<string | null>('authToken', () => null);

  configureApiClient({
    baseURL: runtime.public.apiBase,
    getExtraHeaders: (): Record<string, string | undefined> => {
      const tenant = tenantState.value ?? tenantId.value;
      const token = tokenState.value ?? authToken.value;
      const headers: Record<string, string | undefined> = {};

      if (tenant !== null && tenant !== '') {
        headers['X-Tenant-ID'] = tenant;
      }

      if (token !== null && token !== '') {
        headers['Authorization'] = `Bearer ${token}`;
      }

      return headers;
    },
  });
});
