<script setup lang="ts">
import { useRouter } from 'vue-router'
import { currentUser, logoutAndRedirect } from '../../services/auth'

interface Props {
  activeTab?: string
}

const props = withDefaults(defineProps<Props>(), {
  activeTab: 'shipments',
})

const router = useRouter()

const navigation = [
  { label: 'Pengiriman', path: '/admin/manajemen-pengiriman', tab: 'shipments' },
  { label: 'Persetujuan', path: '/admin/approval-qc-kurir', tab: 'approvals' },
  { label: 'Pemantauan', path: '/admin/live-monitor-kendala', tab: 'monitor' },
  { label: 'Laporan', path: '/admin/laporan-audit-log', tab: 'reports' },
  { label: 'Pengguna', path: '/admin/pengguna', tab: 'users' },
]

async function handleLogout() {
  await logoutAndRedirect(router)
}
</script>

<template>
  <header class="sticky top-0 z-40 border-b border-slate-200 bg-white">
    <div class="mx-auto flex min-h-16 max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-2 sm:px-6">
      <router-link to="/admin" aria-label="Beranda Anteraja Pharma" class="inline-flex shrink-0 items-center rounded-md focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary">
        <img src="/pharma-logo.webp" alt="Anteraja Pharma" width="110" height="45" class="h-10 w-auto shrink-0 object-contain mix-blend-multiply" />
      </router-link>

      <nav class="order-3 flex w-full gap-1 overflow-x-auto pb-1 sm:order-none sm:w-auto sm:pb-0" aria-label="Navigasi admin">
        <router-link
          v-for="item in navigation"
          :key="item.tab"
          :to="item.path"
          class="whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium transition-colors"
          :class="props.activeTab === item.tab
            ? 'bg-primary/10 text-primary'
            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
        >
          {{ item.label }}
        </router-link>
      </nav>

      <div class="flex items-center gap-3">
        <span class="hidden text-sm text-slate-600 sm:inline">{{ currentUser?.name }}</span>
        <button
          type="button"
          class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
          @click="handleLogout"
        >
          Keluar
        </button>
      </div>
    </div>
  </header>
</template>
