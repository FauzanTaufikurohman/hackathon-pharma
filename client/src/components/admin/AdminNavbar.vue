<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { mockAdminProfile } from '../../data/mockShipments'
import { logoutAndRedirect } from '../../services/auth'

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

const activeTabNum = computed(() => navigation.findIndex(({ tab }) => tab === props.activeTab) + 1)

function handleTabClick(tab: number) {
  const selectedTab = navigation[tab - 1]

  if (selectedTab) {
    return router.push(selectedTab.path)
  }
}

async function handleLogout() {
  await logoutAndRedirect(router)
}
</script>
<template><RoleNavbar role="admin" /></template>
