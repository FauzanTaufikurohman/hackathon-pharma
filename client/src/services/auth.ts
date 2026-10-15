import { ref } from 'vue'
import type { Router } from 'vue-router'

export type AppRole = 'admin' | 'checker' | 'courier' | 'buyer' | 'vendor'
export interface AuthUser {
  id: number
  name: string
  email: string
  role: AppRole
  facility?: Record<string, unknown> | null
}
export const currentUser = ref<AuthUser | null>(null)
export const apiBaseUrl = (
  import.meta.env.VITE_API_BASE_URL || '/api/v1'
).trim().replace(/\/+$/, '')
const sessionKey = 'pharma.session'
let accessToken: string | null = null
let restorePromise: Promise<void> | undefined
let sessionVersion = 0
try {
  accessToken = sessionStorage.getItem(sessionKey)
} catch {
  /* private browsing may disable storage */
}

export function getAccessToken() {
  return accessToken
}
export function clearSession() {
  sessionVersion++
  accessToken = null
  currentUser.value = null
  try {
    sessionStorage.removeItem(sessionKey)
  } catch {
    /* memory-only session */
  }
}
function setToken(token: string) {
  sessionVersion++
  accessToken = token
  try {
    sessionStorage.setItem(sessionKey, token)
  } catch {
    /* memory-only session */
  }
}
function isUser(value: unknown): value is AuthUser {
  if (!value || typeof value !== 'object') return false
  const user = value as Partial<AuthUser>
  return (
    typeof user.id === 'number' &&
    typeof user.name === 'string' &&
    typeof user.email === 'string' &&
    ['admin', 'checker', 'courier', 'buyer', 'vendor'].includes(user.role || '')
  )
}
export async function restoreSession(): Promise<void> {
  if (currentUser.value || !accessToken) return
  if (!restorePromise) {
    restorePromise = (async () => {
      const version = sessionVersion
      const token = accessToken
      const controller = new AbortController()
      const timer = setTimeout(() => controller.abort(), 15000)
      try {
        const response = await fetch(apiBaseUrl + '/auth/me', {
          headers: {
            Authorization: 'Bearer ' + token,
            Accept: 'application/json',
          },
          signal: controller.signal,
        })
        if (version !== sessionVersion) return
        if (response.status === 401 || response.status === 403) {
          clearSession()
          return
        }
        if (!response.ok)
          throw new Error('Sesi belum dapat diperiksa. Coba lagi.')
        const body = await response.json()
        if (version !== sessionVersion) return
        if (!isUser(body.data)) {
          clearSession()
          return
        }
        currentUser.value = body.data
      } catch {
        /* retain the token on network failure; never grant access from cached role */
      } finally {
        clearTimeout(timer)
        restorePromise = undefined
      }
    })()
  }
  await restorePromise
}
export async function login(
  email: string,
  password: string,
): Promise<AuthUser> {
  clearSession()
  const controller = new AbortController()
  const timer = setTimeout(() => controller.abort(), 15000)
  try {
    const response = await fetch(apiBaseUrl + '/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({ email, password, device_name: 'pharma-web' }),
      signal: controller.signal,
    })
    const body = await response.json()
    if (!response.ok) {
      const first = Object.values(body.errors || {}).flat()[0]
      throw new Error(
        typeof first === 'string'
          ? first
          : body.message || 'Login gagal. Periksa akun Anda.',
      )
    }
    if (!isUser(body.data) || typeof body.token !== 'string' || !body.token)
      throw new Error('Respons login tidak lengkap.')
    setToken(body.token)
    currentUser.value = body.data
    return body.data
  } catch (error) {
    if (error instanceof Error && error.name === 'AbortError')
      throw new Error('Server belum merespons. Silakan coba lagi.')
    throw error
  } finally {
    clearTimeout(timer)
  }
}
export async function logout(): Promise<void> {
  const token = accessToken
  const controller = new AbortController()
  const timer = setTimeout(() => controller.abort(), 10000)
  try {
    if (!token) return
    const response = await fetch(apiBaseUrl + '/auth/logout', {
      method: 'POST',
      headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
      signal: controller.signal,
    })
    if (!response.ok && response.status !== 401)
      throw new Error('Server belum mengonfirmasi pencabutan sesi.')
  } finally {
    clearTimeout(timer)
    clearSession()
  }
}
export async function logoutAndRedirect(router: Router): Promise<void> {
  let failed = false
  try {
    await logout()
  } catch {
    failed = true
  }
  await router.replace({
    name: 'login',
    query: { logout: failed ? 'failed' : 'success' },
  })
}
