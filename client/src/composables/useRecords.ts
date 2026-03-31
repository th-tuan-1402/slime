import { useRuntimeConfig, useCookie } from '#imports'
import type { RecordListQuery } from '~/composables/usePagination'

export type RecordListResponse = {
  success: boolean
  message: string
  data: Array<Record<string, unknown>>
  meta: {
    current_page: number
    per_page: number
    total: number
    last_page: number
  }
}

function stableStringify(value: unknown): string {
  if (value === null || typeof value !== 'object') return JSON.stringify(value)
  if (Array.isArray(value)) return `[${value.map(stableStringify).join(',')}]`
  const entries = Object.entries(value as Record<string, unknown>).sort(([a], [b]) =>
    a.localeCompare(b),
  )
  return `{${entries.map(([k, v]) => `${JSON.stringify(k)}:${stableStringify(v)}`).join(',')}}`
}

function buildHeaders(): HeadersInit {
  const tenantId = useCookie<string | null>('tenant_id').value
  const token = useCookie<string | null>('auth_token').value
  const headers: Record<string, string> = {}
  if (tenantId) headers['X-Tenant-ID'] = tenantId
  if (token) headers['Authorization'] = `Bearer ${token}`
  return headers
}

export function useRecords(schemaId: string, query: RecordListQuery) {
  const runtime = useRuntimeConfig()
  const key = `records:${schemaId}:${stableStringify(query)}`

  return useAsyncData<RecordListResponse>(
    key,
    () =>
      $fetch(`/api/v1/schemas/${encodeURIComponent(schemaId)}/records`, {
        baseURL: runtime.public.apiBase,
        headers: buildHeaders(),
        query: {
          page: query.page,
          perPage: query.perPage,
          sortBy: query.sortBy,
          sortDir: query.sortDir,
          q: query.q,
          filters: query.filters ? JSON.stringify(query.filters) : undefined,
        },
      }),
    {
      server: true,
    },
  )
}

