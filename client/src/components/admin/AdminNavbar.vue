<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { mockAdminProfile } from '../../data/mockShipments'

interface Props {
  activeTab?: number | string
}

const props = withDefaults(defineProps<Props>(), {
  activeTab: 1,
})

const emit = defineEmits<{
  (e: 'tab-change', tab: number): void
}>()

const router = useRouter()

const activeTabNum = computed<number>(() => {
  if (typeof props.activeTab === 'number') {
    return props.activeTab
  }
  if (props.activeTab === 'approval-qc-kurir') return 2
  if (props.activeTab === 'live-monitor-kendala') return 3
  if (props.activeTab === 'laporan-audit-log') return 4
  return 1
})

const handleTabClick = (tab: number) => {
  emit('tab-change', tab)
  if (tab === 1) {
    router.push('/admin/manajemen-pengiriman')
  } else if (tab === 2) {
    router.push('/admin/approval-qc-kurir')
  } else if (tab === 3) {
    router.push('/admin/live-monitor-kendala')
  } else if (tab === 4) {
    router.push('/admin/laporan-audit-log')
  }
}
</script>

<template>
  <header class="sticky top-0 z-40 h-20 w-full bg-surface-container-lowest border-b border-surface-container-low px-4 sm:px-8 shadow-sm">
    <div class="h-full w-full max-w-7xl mx-auto flex items-center justify-between gap-space-md">
      <!-- Left: Logo & Role Pill -->
      <div class="flex items-center gap-space-sm">
        <router-link to="/admin" class="flex items-center gap-2 group cursor-pointer">
          <img
            alt="Anteraja Pharma Admin"
            class="w-8 h-8 rounded-full object-cover ring-2 ring-primary-fixed group-hover:scale-105 transition-transform"
            :src="mockAdminProfile.avatar_url"
          />
          <div class="flex flex-col">
            <span class="font-headline-sm text-headline-sm text-on-surface leading-tight tracking-tight font-extrabold">
              Anteraja <span class="text-primary font-bold">Pharma</span>
            </span>
            <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-semibold">
              Cold-Chain Logistics
            </span>
          </div>
        </router-link>

        <span class="ml-space-xs bg-inverse-surface text-inverse-on-surface font-label-sm text-label-sm px-2.5 py-1 rounded-full uppercase tracking-widest flex items-center gap-1.5 shadow-sm font-bold">
          <span class="w-1.5 h-1.5 rounded-full bg-primary-fixed-dim"></span>
          ADMIN CONTROL CENTER
        </span>
      </div>

      <!-- Center: 4 Navigation Tabs -->
      <nav
        class="hidden lg:flex items-center gap-space-xs"
        data-active-classes="bg-primary-fixed text-primary font-label-lg rounded-full px-5 py-2.5"
      >
        <!-- Tab 1: Manajemen Pengiriman -->
        <button
          type="button"
          @click="handleTabClick(1)"
          class="transition-colors font-label-lg rounded-full cursor-pointer font-bold"
          :class="activeTabNum === 1
            ? 'bg-primary-fixed text-primary px-5 py-2.5 shadow-xs'
            : 'text-on-surface-variant hover:text-on-surface px-4 py-2.5 hover:bg-surface-container-low'"
        >
          Manajemen Pengiriman
        </button>

        <!-- Tab 2: Approval QC & Kurir -->
        <button
          type="button"
          @click="handleTabClick(2)"
          class="transition-colors font-label-lg rounded-full cursor-pointer font-bold"
          :class="activeTabNum === 2
            ? 'bg-primary-fixed text-primary px-5 py-2.5 shadow-xs'
            : 'text-on-surface-variant hover:text-on-surface px-4 py-2.5 hover:bg-surface-container-low'"
        >
          Approval QC &amp; Kurir
        </button>

        <!-- Tab 3: Live Monitor & Kendala -->
        <button
          type="button"
          @click="handleTabClick(3)"
          class="transition-colors font-label-lg rounded-full cursor-pointer font-bold"
          :class="activeTabNum === 3
            ? 'bg-primary-fixed text-primary px-5 py-2.5 shadow-xs'
            : 'text-on-surface-variant hover:text-on-surface px-4 py-2.5 hover:bg-surface-container-low'"
        >
          Live Monitor &amp; Kendala
        </button>

        <!-- Tab 4: Laporan & Audit Log -->
        <button
          type="button"
          @click="handleTabClick(4)"
          class="transition-colors font-label-lg rounded-full cursor-pointer font-bold"
          :class="activeTabNum === 4
            ? 'bg-primary-fixed text-primary px-5 py-2.5 shadow-xs'
            : 'text-on-surface-variant hover:text-on-surface px-4 py-2.5 hover:bg-surface-container-low'"
        >
          Laporan &amp; Audit Log
        </button>
      </nav>

      <!-- Right: Bot Online & Admin Profile -->
      <div class="flex items-center gap-space-md">
        <!-- WA/Telegram Bot Status -->
        <div class="hidden sm:flex items-center gap-2 bg-surface-container-low px-3.5 py-1.5 rounded-full border border-surface-container shadow-2xs">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-secondary"></span>
          </span>
          <span class="font-label-sm text-label-sm text-secondary font-bold">
            {{ mockAdminProfile.bot_status }}
          </span>
        </div>

        <!-- Admin Profile -->
        <div class="flex items-center gap-space-sm pl-space-xs">
          <img
            alt="Profile Admin"
            class="w-8 h-8 rounded-full object-cover ring-2 ring-primary-fixed shadow-2xs"
            :src="mockAdminProfile.avatar_url"
          />
          <div class="hidden xl:flex flex-col text-left">
            <span class="font-label-md text-label-md text-on-surface leading-snug font-bold">
              {{ mockAdminProfile.name }}
            </span>
            <span class="font-label-sm text-label-sm text-on-surface-variant leading-none">
              {{ mockAdminProfile.role }}
            </span>
          </div>

          <!-- Switch portal / logout -->
          <router-link
            to="/"
            class="font-label-sm text-slate-400 hover:text-primary transition-colors pl-2 font-medium"
            title="Keluar ke Portal Buyer"
          >
            Keluar
          </router-link>
        </div>
      </div>
    </div>
  </header>
</template>
