import type { ApiErrorBody, Resource } from './types'

const API_URL: string = import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api'
const API_PATH = new URL(API_URL).pathname
const SENSITIVE_KEY = /password|token/i
const TOKEN_STORAGE_KEY = 'auth.session'

export const UNAUTHENTICATED_EVENT = 'auth:unauthenticated'

interface StoredToken {
  token: string
  expiresAt: string | null
}

export const tokenStorage = {
  read(): StoredToken | null {
    try {
      return JSON.parse(localStorage.getItem(TOKEN_STORAGE_KEY) ?? 'null') as StoredToken | null
    } catch {
      return null
    }
  },
  token(): string | null {
    return this.read()?.token ?? null
  },
  save(token: string, expiresAt: string | null): void {
    localStorage.setItem(TOKEN_STORAGE_KEY, JSON.stringify({ token, expiresAt }))
  },
  clear(): void {
    localStorage.removeItem(TOKEN_STORAGE_KEY)
  },
}

export type Query = Record<string, string | number | null | undefined>

type Method = 'GET' | 'POST' | 'PATCH' | 'DELETE'

export class ApiError extends Error {
  readonly status: number
  readonly code: string | null
  readonly errors: Record<string, string[]>

  constructor(status: number, body: ApiErrorBody | null) {
    super(body?.message ?? 'Ocurrió un error inesperado. Intenta nuevamente.')
    this.status = status
    this.code = body?.code ?? null
    this.errors = body?.errors ?? {}
  }

  fieldErrors(): Record<string, string> {
    return Object.fromEntries(Object.entries(this.errors).map(([field, messages]) => [field, messages[0] ?? '']))
  }

  firstMessage(): string {
    return Object.values(this.fieldErrors())[0] ?? this.message
  }
}

declare module '@tanstack/react-query' {
  interface Register {
    defaultError: ApiError
  }
}

export const unwrap = <T>(response: Resource<T>): T => response.data

function buildUrl(path: string, query: Query = {}): URL {
  const url = new URL(`${API_URL}${path}`)

  Object.entries(query).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') url.searchParams.set(key, String(value))
  })

  return url
}

function redact(value: unknown): unknown {
  if (value instanceof File) return `${value.name} (${value.size} B)`
  if (value instanceof FormData) return redact(Object.fromEntries(value.entries()))
  if (Array.isArray(value)) return value.map(redact)
  if (!value || typeof value !== 'object') return value

  return Object.fromEntries(Object.entries(value).map(([key, item]) => [key, SENSITIVE_KEY.test(key) ? '•••' : redact(item)]))
}

function logRequest(method: Method, url: URL, status: number, startedAt: number, body: unknown, data: unknown) {
  if (!import.meta.env.DEV) return

  const color = status >= 200 && status < 400 ? '#16a34a' : '#dc2626'
  const elapsed = Math.round(performance.now() - startedAt)

  console.groupCollapsed(
    `%c[API]%c ${method} ${url.pathname.replace(API_PATH, '')}${url.search} %c${status || 'sin conexión'} · ${elapsed} ms`,
    'color:#4f46e5;font-weight:600',
    'color:inherit',
    `color:${color}`,
  )
  if (body !== undefined) console.log('payload', redact(body))
  console.log('respuesta', redact(data))
  console.groupEnd()
}

async function request<T>(method: Method, path: string, { body, query }: { body?: unknown; query?: Query } = {}): Promise<T> {
  const url = buildUrl(path, query)
  const token = tokenStorage.token()
  const isFormData = body instanceof FormData
  const headers: Record<string, string> = { Accept: 'application/json' }
  const startedAt = performance.now()

  if (token) headers.Authorization = `Bearer ${token}`
  if (body !== undefined && !isFormData) headers['Content-Type'] = 'application/json'

  let response: Response

  try {
    response = await fetch(url, { method, headers, body: isFormData ? body : body === undefined ? undefined : JSON.stringify(body) })
  } catch {
    logRequest(method, url, 0, startedAt, body, null)
    throw new ApiError(0, { message: 'No se pudo conectar con el servidor.', code: 'network_error' })
  }

  const data: unknown = response.status === 204 ? null : await response.json().catch(() => null)

  logRequest(method, url, response.status, startedAt, body, data)

  if (!response.ok) {
    const error = new ApiError(response.status, data as ApiErrorBody | null)

    if (response.status === 401 && token) window.dispatchEvent(new CustomEvent<ApiError>(UNAUTHENTICATED_EVENT, { detail: error }))

    throw error
  }

  return data as T
}

export const http = {
  get: <T>(path: string, query?: Query) => request<T>('GET', path, { query }),
  post: <T = void>(path: string, body?: unknown) => request<T>('POST', path, { body }),
  patch: <T = void>(path: string, body?: unknown) => request<T>('PATCH', path, { body }),
  delete: (path: string) => request<void>('DELETE', path),
}
