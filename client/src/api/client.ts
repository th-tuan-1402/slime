/**
 * Orval mutator: `fetch` client calls `apiRequest<T>(url, init)`.
 *
 * @see https://orval.dev/guides/custom-client
 */
type HeadersResolver = () => Record<string, string | undefined>;

let baseURLResolver: () => string = () => '';
let headersResolver: HeadersResolver = () => ({});

export function configureApiClient(options: {
  baseURL: string | (() => string);
  getExtraHeaders?: HeadersResolver;
}): void {
  if (typeof options.baseURL === 'function') {
    baseURLResolver = options.baseURL;
  } else {
    const fixed = options.baseURL.replace(/\/$/, '');
    baseURLResolver = (): string => fixed;
  }
  const extra = options.getExtraHeaders;
  if (extra !== undefined) {
    headersResolver = extra;
  }
}

function resolveBaseURL(): string {
  return baseURLResolver().replace(/\/$/, '');
}

function mergeHeaders(initHeaders: HeadersInit | undefined): Headers {
  const out = new Headers(initHeaders);
  for (const [key, value] of Object.entries(headersResolver())) {
    if (value !== undefined && value !== '') {
      out.set(key, value);
    }
  }
  return out;
}

/**
 * Shared HTTP entry for all Orval-generated calls: base URL + inject
 * `Authorization` and `X-Tenant-ID` via {@link configureApiClient}.
 *
 * Returns the shape Orval’s `fetch` client expects: `{ data, status, headers }`.
 * Central 401/403 handling can be added here later.
 */
export async function apiRequest<T>(url: string, init?: RequestInit): Promise<T> {
  const base = resolveBaseURL();
  const path = url.startsWith('http') ? url : `${base}${url}`;
  const headers = mergeHeaders(init?.headers);

  if (process.env.NODE_ENV !== 'production' && !headers.has('X-Tenant-ID')) {
    console.warn('[api] Dev warning: missing X-Tenant-ID header (required by API).', {
      url: path,
    });
  }

  const response = await fetch(path, {
    ...init,
    headers,
  });

  let body: unknown;
  const contentType = response.headers.get('content-type') ?? '';
  if (contentType.includes('application/json')) {
    body = (await response.json()) as unknown;
  } else if (response.status === 204) {
    body = null;
  } else {
    body = (await response.text()) as unknown;
  }

  return {
    data: body,
    status: response.status,
    headers: response.headers,
  } as T;
}
