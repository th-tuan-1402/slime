export type SortDir = 'asc' | 'desc'

export type RecordListQuery = {
  page: number
  perPage: number
  sortBy?: string
  sortDir?: SortDir
  q?: string
  filters?: Record<string, unknown>
}

const PER_PAGE_OPTIONS = [10, 20, 50, 100] as const

export function normalizePage(value: unknown, fallback = 1): number {
  const parsed = typeof value === 'string' ? Number.parseInt(value, 10) : Number(value)
  if (!Number.isFinite(parsed) || parsed < 1) return fallback
  return Math.floor(parsed)
}

export function normalizePerPage(value: unknown, fallback = 20): number {
  const parsed = typeof value === 'string' ? Number.parseInt(value, 10) : Number(value)
  if (!Number.isFinite(parsed)) return fallback
  const next = Math.floor(parsed)
  if (PER_PAGE_OPTIONS.includes(next as (typeof PER_PAGE_OPTIONS)[number])) return next
  return fallback
}

export function parseFilters(value: unknown): Record<string, unknown> | undefined {
  if (value === null || value === undefined) return undefined
  if (typeof value !== 'string' || value.trim() === '') return undefined
  try {
    const parsed = JSON.parse(value) as unknown
    if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
      return parsed as Record<string, unknown>
    }
    return undefined
  } catch {
    return undefined
  }
}

export function normalizeRecordListQuery(query: Record<string, unknown>): RecordListQuery {
  const page = normalizePage(query.page, 1)
  const perPage = normalizePerPage(query.perPage, 20)
  const sortBy = typeof query.sortBy === 'string' && query.sortBy !== '' ? query.sortBy : undefined
  const sortDir =
    query.sortDir === 'asc' || query.sortDir === 'desc' ? (query.sortDir as SortDir) : undefined
  const q = typeof query.q === 'string' && query.q.trim() !== '' ? query.q : undefined
  const filters = parseFilters(query.filters)

  return {
    page,
    perPage,
    sortBy,
    sortDir,
    q,
    filters,
  }
}

export function buildRecordListRouteQuery(state: RecordListQuery): Record<string, string> {
  const query: Record<string, string> = {
    page: String(state.page ?? 1),
    perPage: String(state.perPage ?? 20),
  }

  if (state.sortBy) query.sortBy = state.sortBy
  if (state.sortDir) query.sortDir = state.sortDir
  if (state.q) query.q = state.q
  if (state.filters && Object.keys(state.filters).length > 0) {
    query.filters = JSON.stringify(state.filters)
  }

  return query
}

