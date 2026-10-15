<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
const props = defineProps<{
  role: 'admin' | 'checker' | 'courier' | 'vendor'
}>()
const route = useRoute()
const baseLinks = {
  admin: [
    { to: '/admin/manajemen-pengiriman', label: 'Manajemen pengiriman' },
    { to: '/admin/approval-qc-kurir', label: 'Persetujuan dan penugasan' },
    { to: '/admin/live-monitor-kendala', label: 'Pemantauan dan kendala' },
    { to: '/admin/laporan-audit-log', label: 'Laporan dan notifikasi' },
    { to: '/admin/pengguna', label: 'Pengguna dan hak akses' },
  ],
  checker: [
    { to: '/checker', label: 'Antrean pemeriksaan' },
    { to: '/checker/riwayat', label: 'Riwayat dan keputusan Admin' },
  ],
  courier: [
    { to: '/courier', label: 'Beranda Satria' },
    { to: '/courier/tugas-saya', label: 'Daftar tugas saya' },
  ],
  vendor: [
    { to: '/', label: 'Beranda penerima' },
    { to: '/paket-saya', label: 'Paket dan laporan saya' },
  ],
}
const links = computed(() => {
  const result = [...baseLinks[props.role]]
  const awb = typeof route.params.awb === 'string' ? route.params.awb : ''
  if (awb && props.role === 'vendor')
    for (const [screen, label] of [
      [1, 'Monitor kesiapan'],
      [2, 'Suhu dan lokasi'],
      [3, 'Laporan resi ini'],
    ] as const)
      result.push({
        to: '/vendor/monitor/' + encodeURIComponent(awb) + '?screen=' + screen,
        label,
      })
  if (awb && props.role === 'courier')
    result.push({
      to: '/courier/tugas/' + encodeURIComponent(awb),
      label: 'Lanjutkan tugas ini',
    })
  return result
})
</script>
<template>
  <footer
    class="w-full bg-surface-container-low border-t border-outline/15 mt-10"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div class="flex flex-col items-start gap-4">
          <img
            src="/pharma-logo.webp"
            alt="Anteraja Pharma"
            width="110"
            height="45"
            class="h-10 w-auto mix-blend-multiply"
          />
          <p class="text-base sm:text-sm text-on-surface-variant max-w-sm">
            Pantau pemeriksaan, pengantaran, dan bukti serah terima dalam satu
            alur.
          </p>
        </div>
        <nav
          aria-label="Tautan layanan portal"
          class="flex flex-col items-start gap-3"
        >
          <h2 class="font-semibold">Layanan portal</h2>
          <RouterLink
            v-for="link in links"
            :key="link.to"
            :to="link.to"
            class="text-on-surface-variant hover:text-primary focus-visible:outline-2 focus-visible:outline-primary"
            >{{ link.label }}</RouterLink
          >
        </nav>
        <div>
          <h2 class="font-semibold">Alur pengiriman</h2>
          <ol
            class="list-decimal pl-5 mt-4 space-y-2 text-on-surface-variant text-base sm:text-sm"
          >
            <li>Checker memeriksa kondisi paket.</li>
            <li>Admin menyetujui dan menugaskan kurir.</li>
            <li>Kurir mencatat perjalanan dan kendala.</li>
            <li>Bukti serah terima menjadi dasar laporan.</li>
          </ol>
        </div>
      </div>
      <div
        class="flex flex-col sm:flex-row justify-between gap-3 mt-8 pt-5 border-t border-outline/15 text-base sm:text-sm text-on-surface-variant"
      >
        <p>© 2026 Anteraja Pharma</p>
        <p>Foto dan laporan hanya tersedia sesuai hak akses akun.</p>
      </div>
    </div>
  </footer>
</template>
