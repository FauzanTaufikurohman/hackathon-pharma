import { apiBaseUrl, clearSession, getAccessToken } from './auth'

export class ApiError extends Error {
  constructor(
    message: string,
    public status: number,
    public fields: Record<string, string[]> = {},
  ) {
    super(message)
  }
}
export interface Page<T> {
  data: T[]
  counts?: Record<string, number>
  meta: {
    total: number
    current_page: number
    last_page: number
    per_page?: number
  }
}
export function endpoint(path: string): string {
  if (!path.startsWith('/') || path.startsWith('//') || path.includes('://'))
    throw new Error('Endpoint tidak valid.')
  return apiBaseUrl + path
}
export async function request<T>(
  path: string,
  options: RequestInit = {},
): Promise<T> {
  const controller = new AbortController()
  const abort = () => controller.abort()
  if (options.signal?.aborted) controller.abort()
  options.signal?.addEventListener('abort', abort, { once: true })
  const timer = setTimeout(abort, 20000)
  try {
    const headers = new Headers(options.headers)
    if (!headers.has('Accept')) headers.set('Accept', 'application/json')
    const token = getAccessToken()
    if (token) headers.set('Authorization', 'Bearer ' + token)
    if (options.body && !(options.body instanceof FormData))
      headers.set('Content-Type', 'application/json')
    const response = await fetch(endpoint(path), {
      ...options,
      headers,
      signal: controller.signal,
      cache: 'no-store',
    })
    if (!response.ok) {
      const body = await response.json().catch(() => ({}))
      if (response.status === 401) {
        clearSession()
        window.dispatchEvent(new Event('pharma:unauthorized'))
      }
      const fields: Record<string, string[]> =
        response.status === 422 ? body.errors || {} : {}
      const message =
        (response.status === 404
          ? 'Data tidak ditemukan atau akunmu tidak memiliki akses.'
          : response.status === 403
            ? 'Akunmu tidak memiliki izin untuk tindakan ini.'
            : response.status === 401
              ? 'Sesi berakhir. Silakan masuk kembali.'
              : response.status >= 500
                ? 'Server sedang bermasalah. Coba lagi setelah beberapa saat.'
                : Object.values(fields).flat()[0] || body.message) ||
        'Permintaan belum berhasil.'
      throw new ApiError(
        response.status === 409
          ? message + ' Muat ulang data sebelum mencoba lagi.'
          : message,
        response.status,
        fields,
      )
    }
    if (
      options.headers &&
      new Headers(options.headers).get('Accept') === 'application/octet-stream'
    )
      return (await response.blob()) as T
    return (await response.json()) as T
  } catch (error) {
    if (error instanceof ApiError) throw error
    if (options.signal?.aborted) throw new DOMException('Aborted', 'AbortError')
    throw new ApiError(
      'Koneksi terputus atau server belum merespons. Data belum dikonfirmasi tersimpan; coba lagi dengan input yang sama.',
      0,
    )
  } finally {
    clearTimeout(timer)
    options.signal?.removeEventListener('abort', abort)
  }
}
export const read = <T>(path: string, signal?: AbortSignal) =>
  request<T>(path, { signal })
export const send = <T>(path: string, data: unknown, method = 'POST') =>
  request<T>(path, {
    method,
    body: data instanceof FormData ? data : JSON.stringify(data),
  })
export function query(
  values: Record<string, string | number | undefined>,
): string {
  const params = new URLSearchParams()
  Object.entries(values).forEach(([key, value]) => {
    if (value !== undefined && value !== '') params.set(key, String(value))
  })
  return params.size ? '?' + params : ''
}
export async function download(path: string, filename: string) {
  const blob = await request<Blob>(path, {
    headers: { Accept: 'application/octet-stream' },
  })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename.replace(/[^a-zA-Z0-9._-]/g, '_')
  document.body.appendChild(link)
  link.click()
  link.remove()
  setTimeout(() => URL.revokeObjectURL(url), 1000)
}
export async function privateImage(path: string, signal?: AbortSignal) {
  return URL.createObjectURL(
    await request<Blob>(path, {
      signal,
      headers: { Accept: 'application/octet-stream' },
    }),
  )
}
export const errorText = (error: unknown) =>
  error instanceof Error ? error.message : 'Permintaan belum berhasil.'
