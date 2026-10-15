<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { currentUser, logoutAndRedirect } from '../../services/auth'
const props = defineProps<{
  role: 'admin' | 'checker' | 'courier' | 'vendor'
}>()
const route = useRoute(),
  router = useRouter()
const menus = {
  admin: [
    {
      path: '/admin/manajemen-pengiriman',
      name: 'admin-shipments',
      label: 'Pengiriman',
    },
    {
      path: '/admin/approval-qc-kurir',
      name: 'admin-approvals',
      label: 'Persetujuan',
    },
    {
      path: '/admin/live-monitor-kendala',
      name: 'admin-monitor',
      label: 'Pemantauan',
    },
    {
      path: '/admin/laporan-audit-log',
      name: 'admin-reports',
      label: 'Laporan',
    },
    { path: '/admin/pengguna', name: 'admin-users', label: 'Pengguna' },
  ],
  checker: [
    { path: '/checker', name: 'checker-queue', label: 'Antrean' },
    {
      path: '/checker/form-initial-exam',
      name: 'checker-exam',
      label: 'Pemeriksaan',
    },
    { path: '/checker/riwayat', name: 'checker-history', label: 'Riwayat' },
  ],
  courier: [
    { path: '/courier', name: 'courier-home', label: 'Beranda' },
    { path: '/courier/tugas-saya', name: 'courier-tasks', label: 'Tugas saya' },
  ],
  vendor: [
    { path: '/', name: 'vendor-home', label: 'Beranda' },
    { path: '/paket-saya', name: 'vendor-packages', label: 'Paket saya' },
  ],
}
const links = computed(() => menus[props.role])
const home = computed(() => links.value[0]!.path)
</script>
<template>
  <header
    class="z-40 w-full border-b border-outline/15 bg-surface-container-lowest"
    :class="role === 'admin' ? 'sticky top-0' : 'fixed inset-x-0 top-0'"
  >
    <div
      class="max-w-7xl mx-auto grid grid-cols-[auto_minmax(0,1fr)_auto] gap-x-4 gap-y-2 px-4 sm:px-6 py-2 items-center"
    >
      <RouterLink
        :to="home"
        aria-label="Beranda Anteraja Pharma"
        class="col-start-1 row-start-1 shrink-0 rounded focus-visible:outline-2 focus-visible:outline-primary"
        ><img
          src="/pharma-logo.webp"
          alt="Anteraja Pharma"
          width="110"
          height="45"
          class="h-10 w-auto object-contain"
      /></RouterLink>
      <nav
        :aria-label="'Navigasi ' + role"
        class="row-start-2 col-span-3 flex gap-1 overflow-x-auto min-w-0 sm:row-start-1 sm:col-start-2 sm:col-span-1"
      >
        <RouterLink
          v-for="link in links"
          :key="link.path"
          :to="link.path"
          class="live-button whitespace-nowrap border-transparent"
          :class="route.name === link.name ? 'bg-primary/10 text-primary' : ''"
          :aria-current="route.name === link.name ? 'page' : undefined"
          >{{ link.label }}</RouterLink
        >
      </nav>
      <div class="col-start-3 row-start-1 flex items-center gap-3">
        <p class="hidden md:block max-w-32 truncate" :title="currentUser?.name">
          {{ currentUser?.name }}
        </p>
        <button
          type="button"
          class="live-button"
          @click="logoutAndRedirect(router)"
        >
          Keluar
        </button>
      </div>
    </div>
  </header>
</template>
