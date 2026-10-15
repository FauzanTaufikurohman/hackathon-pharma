import { onScopeDispose, ref, shallowRef, watch, type WatchSource } from 'vue'
import { ApiError, errorText, read, send } from './api'

export function usePolling(
  callback: () => Promise<unknown>,
  enabled: () => boolean,
  delay = 15000,
) {
  let running = false
  const timer = setInterval(async () => {
    if (document.hidden || running || !enabled()) return
    running = true
    try {
      await callback()
    } finally {
      running = false
    }
  }, delay)
  onScopeDispose(() => clearInterval(timer))
}

export function useApi<T>(path: WatchSource<string>) {
  const data = shallowRef<T | null>(null)
  const loading = ref(false)
  const error = ref('')
  let controller: AbortController | undefined
  let currentPath = ''
  let generation = 0
  async function reload() {
    controller?.abort()
    const mine = ++generation
    if (!currentPath) {
      data.value = null
      loading.value = false
      error.value = ''
      return
    }
    controller = new AbortController()
    loading.value = true
    error.value = ''
    try {
      const result = await read<T>(currentPath, controller.signal)
      if (mine === generation) data.value = result
    } catch (cause) {
      if (
        mine === generation &&
        !(cause instanceof DOMException && cause.name === 'AbortError')
      )
        error.value = errorText(cause)
    } finally {
      if (mine === generation) loading.value = false
    }
  }
  watch(
    path,
    (value) => {
      currentPath = value
      data.value = null
      void reload()
    },
    { immediate: true },
  )
  onScopeDispose(() => {
    generation++
    controller?.abort()
  })
  return { data, loading, error, reload }
}

function createRequestId(): string {
  if (typeof crypto.randomUUID === 'function') return crypto.randomUUID()
  const bytes = crypto.getRandomValues(new Uint8Array(16))
  bytes[6] = (bytes[6]! & 15) | 64
  bytes[8] = (bytes[8]! & 63) | 128
  const hex = [...bytes]
    .map((value) => value.toString(16).padStart(2, '0'))
    .join('')
  return [
    hex.slice(0, 8),
    hex.slice(8, 12),
    hex.slice(12, 16),
    hex.slice(16, 20),
    hex.slice(20),
  ].join('-')
}

export function useMutation() {
  const busy = ref(false)
  const error = ref('')
  const notice = ref('')
  let retryKey = ''
  let requestId = ''
  async function run<T>(
    path: string,
    values: Record<string, unknown> | FormData,
    method = 'POST',
  ): Promise<T | undefined> {
    if (busy.value) return
    busy.value = true
    error.value = ''
    notice.value = ''
    const keyValues =
      values instanceof FormData
        ? [...values.entries()]
            .filter(([key]) => key !== 'request_id')
            .map(([key, value]) => [
              key,
              value instanceof File
                ? [value.name, value.size, value.lastModified]
                : value,
            ])
        : values
    const key = path + method + JSON.stringify(keyValues)
    if (retryKey !== key) {
      requestId = createRequestId()
      retryKey = key
    }
    const payload =
      values instanceof FormData ? values : { ...values, request_id: requestId }
    if (payload instanceof FormData) payload.set('request_id', requestId)
    try {
      const result = await send<T>(path, payload, method)
      requestId = ''
      retryKey = ''
      notice.value = 'Perubahan sudah dikonfirmasi server.'
      return result
    } catch (cause) {
      error.value = errorText(cause)
      if (
        cause instanceof ApiError &&
        cause.status >= 400 &&
        cause.status < 500
      ) {
        requestId = ''
        retryKey = ''
      }
      return undefined
    } finally {
      busy.value = false
    }
  }
  return { busy, error, notice, run }
}
