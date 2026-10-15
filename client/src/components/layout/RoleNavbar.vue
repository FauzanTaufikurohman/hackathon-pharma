<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { currentUser, logoutAndRedirect } from '../../services/auth'
const props = defineProps<{
  role: 'admin' | 'checker' | 'courier' | 'vendor'
}>()
const route = useRoute(),
  router = useRouter()
const menuOpen = ref(false)
const menuButton = ref<HTMLButtonElement | null>(null)
const signingOut = ref(false)
async function closeMenu() {
  const wasOpen = menuOpen.value
  menuOpen.value = false
  if (wasOpen) {
    await nextTick()
    menuButton.value?.focus()
  }
}
async function toggleMenu() {
  if (menuOpen.value) return closeMenu()
  menuOpen.value = true
  await nextTick()
  document
    .getElementById('portal-nav-' + props.role)
    ?.querySelector<HTMLAnchorElement>('a')
    ?.focus()
}
watch(() => route.fullPath, closeMenu)
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
const facilityName = computed(() => {
  const name = currentUser.value?.facility?.name
  return typeof name === 'string' && name.trim()
    ? name
    : currentUser.value?.name
})
async function signOut() {
  if (signingOut.value) return
  signingOut.value = true
  try {
    await logoutAndRedirect(router)
  } finally {
    signingOut.value = false
  }
}
</script>
<template>
  <header
    class="z-40 w-full border-b border-outline/15 bg-surface-container-lowest"
    :class="role === 'admin' ? 'sticky top-0' : 'fixed inset-x-0 top-0'"
  >
    <div
      class="relative max-w-7xl mx-auto grid grid-cols-[auto_minmax(0,1fr)_auto] gap-3 px-4 sm:px-6 min-h-20 items-center"
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
        :id="'portal-nav-' + role"
        :aria-label="'Navigasi ' + role"
        class="absolute inset-x-0 top-full bg-surface-container-lowest border-b border-outline/15 px-4 py-3 gap-1 flex-col min-w-0 sm:static sm:p-0 sm:border-0 sm:flex-row sm:overflow-x-auto sm:col-start-2"
        :class="menuOpen ? 'flex' : 'hidden sm:flex'"
        @keydown.esc.stop.prevent="closeMenu"
      >
        <RouterLink
          v-for="link in links"
          :key="link.path"
          :to="link.path"
          class="live-button whitespace-nowrap border-transparent rounded-full font-semibold"
          :class="
            route.name === link.name
              ? 'bg-primary/10 text-primary'
              : 'text-on-surface-variant'
          "
          :aria-current="route.name === link.name ? 'page' : undefined"
          >{{ link.label }}</RouterLink
        >
      </nav>
      <div class="col-start-3 row-start-1 flex items-center gap-3">
        <p
          class="hidden lg:block max-w-44 truncate rounded-full bg-secondary/10 px-3 py-2 text-secondary"
          :title="facilityName"
        >
          {{ facilityName }}
        </p>
        <button
          ref="menuButton"
          type="button"
          class="live-button sm:hidden"
          :aria-controls="'portal-nav-' + role"
          :aria-expanded="menuOpen"
          @click="toggleMenu"
        >
          Menu
        </button>
        <button
          type="button"
          class="live-button"
          :disabled="signingOut"
          @click="signOut"
        >
          {{ signingOut ? 'Keluar…' : 'Keluar' }}
        </button>
      </div>
    </div>
  </header>
</template>
