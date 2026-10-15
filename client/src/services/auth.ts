import { ref } from 'vue'
import type { Router } from 'vue-router'

export type AppRole = 'admin' | 'checker' | 'courier' | 'buyer' | 'vendor'

export interface AuthUser {
  id: number
  name: string
  email: string
  role: AppRole
}

function isAppRole(value: unknown): value is AppRole {
  return value === 'admin'
    || value === 'checker'
    || value === 'courier'
    || value === 'buyer'
    || value === 'vendor'
}

interface LoginResponse {
  data: AuthUser
  token: string
}


export const currentUser = ref<AuthUser | null>(null)

let accessToken: string | null = null

const apiBaseUrl = (
  import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
).replace(/\/+$/, '')

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null
}

function getMessage(body: unknown, fallback: string): string {
  if (!isRecord(body)) return fallback

  if (typeof body.message === 'string') return body.message

  if (isRecord(body.errors)) {
    const firstError = Object.values(body.errors).find(
      (value): value is string[] => Array.isArray(value) && value.every((item) => typeof item === 'string'),
    )?.[0]
    if (firstError) return firstError
  }

  return fallback
}

function parseLoginResponse(value: unknown): LoginResponse {
  if (!isRecord(value) || !isRecord(value.data)) {
    throw new Error('Server memberikan respons login yang tidak valid.')
  }

  const { id, name, email, role } = value.data
  const token = value.token

  if (
    typeof id !== 'number'
    || typeof name !== 'string'
    || typeof email !== 'string'
    || !isAppRole(role)
    || typeof token !== 'string'
    || token.length === 0
  ) {
    throw new Error('Data akun atau token dari server tidak lengkap.')
  }

  return {
    data: { id, name, email, role },
    token,
  }
}

export async function login(email: string, password: string): Promise<AuthUser> {
  const response = await fetch(`${apiBaseUrl}/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ email, password, device_name: 'pharma-web' }),
  })
  const body: unknown = await response.json()

  if (!response.ok) {
    throw new Error(getMessage(body, 'Login gagal. Periksa kembali email dan kata sandi.'))
  }

  const result = parseLoginResponse(body)
  accessToken = result.token
  currentUser.value = result.data
  return result.data
}

export async function logout(): Promise<void> {
  const token = accessToken

  try {
    if (!token) return

    const response = await fetch(`${apiBaseUrl}/auth/logout`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
    })
    const body: unknown = await response.json()

    if (!response.ok) {
      throw new Error(getMessage(body, 'Server gagal mengakhiri sesi.'))
    }
  } finally {
    accessToken = null
    currentUser.value = null
  }
}

export async function logoutAndRedirect(router: Router): Promise<void> {
  let logoutFailed = false

  try {
    await logout()
  } catch {
    logoutFailed = true
  }

  await router.replace({
    name: 'login',
    query: { logout: logoutFailed ? 'failed' : 'success' },
  })
}
