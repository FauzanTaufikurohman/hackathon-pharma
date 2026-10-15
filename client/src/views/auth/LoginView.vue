<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { login } from '../../services/auth'
import type { AppRole } from '../../services/auth'

const route = useRoute()
const router = useRouter()
const email = ref('')
const password = ref('')
const isSubmitting = ref(false)
const errorMessage = ref('')

const roleHome: Record<AppRole, string> = {
  admin: '/admin/manajemen-pengiriman',
  checker: '/checker',
  courier: '/courier',
  buyer: '/',
  vendor: '/',
}

async function handleLogin() {
  if (isSubmitting.value) return
  errorMessage.value = ''
  isSubmitting.value = true

  try {
    const user = await login(email.value.trim(), password.value)
    const requestedPath = route.query.redirect
    const destination =
      typeof requestedPath === 'string' &&
      requestedPath.startsWith('/') &&
      !requestedPath.startsWith('//')
        ? requestedPath
        : roleHome[user.role]

    await router.replace(destination)
  } catch (error) {
    errorMessage.value =
      error instanceof Error
        ? error.message
        : 'Login tidak berhasil. Silakan coba kembali.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <main
    class="min-h-screen bg-slate-50 flex items-center justify-center px-4 py-10"
  >
    <section
      class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-7 shadow-sm sm:p-9"
    >
      <div class="mb-8">
        <img
          src="/pharma-logo.webp"
          alt="Anteraja Pharma"
          width="147"
          height="60"
          class="h-16 w-40 max-w-full object-contain object-left"
        />
        <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">
          Masuk ke akun
        </h1>
        <p class="mt-2 text-sm text-slate-600">
          Gunakan akun operasional Anda untuk melanjutkan.
        </p>
      </div>

      <div
        v-if="route.query.logout === 'failed'"
        role="status"
        class="mb-5 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-900"
      >
        Sesi lokal sudah diakhiri, tetapi server belum mengonfirmasi pencabutan
        token.
      </div>
      <div
        v-else-if="route.query.logout === 'success'"
        role="status"
        class="mb-5 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-900"
      >
        Anda sudah keluar.
      </div>

      <form class="space-y-5" @submit.prevent="handleLogin">
        <label class="block">
          <span class="mb-1.5 block text-sm font-medium text-slate-700"
            >Email</span
          >
          <input
            v-model="email"
            type="email"
            name="email"
            autocomplete="username"
            required
            class="w-full rounded-lg border border-slate-300 px-3.5 py-3 text-sm text-slate-900 outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15"
            placeholder="nama@perusahaan.co.id"
          />
        </label>

        <label class="block">
          <span class="mb-1.5 block text-sm font-medium text-slate-700"
            >Kata sandi</span
          >
          <input
            v-model="password"
            type="password"
            name="password"
            autocomplete="current-password"
            required
            class="w-full rounded-lg border border-slate-300 px-3.5 py-3 text-sm text-slate-900 outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15"
            placeholder="Masukkan kata sandi"
          />
        </label>

        <p
          v-if="errorMessage"
          role="alert"
          class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"
        >
          {{ errorMessage }}
        </p>

        <button
          type="submit"
          :disabled="isSubmitting"
          class="w-full rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-white transition hover:opacity-90 disabled:cursor-wait disabled:opacity-60"
        >
          {{ isSubmitting ? 'Memeriksa akun...' : 'Masuk' }}
        </button>
      </form>
    </section>
  </main>
</template>
