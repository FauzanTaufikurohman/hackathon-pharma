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
  <header class="fixed top-0 left-0 right-0 z-50 bg-surface/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-surface-container/40">
    <div class="h-20 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-space-md">
      <!-- Left: Logo & Role Pill -->
      <div class="flex items-center gap-space-md lg:gap-space-lg">
        <router-link
          to="/admin"
          class="flex items-center gap-space-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-lg group shrink-0"
          title="Admin Control Center"
        >
          <img
            alt="Anteraja Pharma"
            class="h-9 md:h-10 w-auto object-contain transition-transform group-hover:scale-105"
            src="/images/anteraja-pharma-logo.png"
          />
        </router-link>

        <span class="hidden sm:inline-flex bg-inverse-surface text-inverse-on-surface font-label-sm text-label-sm px-2.5 py-1 rounded-full uppercase tracking-widest items-center gap-1.5 shadow-sm font-bold">
          <span class="w-1.5 h-1.5 rounded-full bg-primary-fixed-dim"></span>
          ADMIN CONTROL CENTER
        </span>

        <!-- Center: 4 Navigation Tabs -->
        <nav
          class="hidden lg:flex items-center gap-space-xs pl-2"
          data-active-classes="bg-primary-fixed/40 text-primary font-label-lg rounded-full px-5 py-2"
        >
          <!-- Tab 1: Manajemen Pengiriman -->
          <button
            type="button"
            @click="handleTabClick(1)"
            class="transition-colors font-label-lg text-label-lg rounded-full cursor-pointer py-2 px-3.5 sm:px-4 whitespace-nowrap"
            :class="activeTabNum === 1
              ? 'bg-primary-fixed/40 text-primary font-bold shadow-2xs'
              : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low'"
          >
            Manajemen Pengiriman
          </button>

          <!-- Tab 2: Approval QC & Kurir -->
          <button
            type="button"
            @click="handleTabClick(2)"
            class="transition-colors font-label-lg text-label-lg rounded-full cursor-pointer py-2 px-3.5 sm:px-4 whitespace-nowrap"
            :class="activeTabNum === 2
              ? 'bg-primary-fixed/40 text-primary font-bold shadow-2xs'
              : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low'"
          >
            Approval QC &amp; Kurir
          </button>

          <!-- Tab 3: Live Monitor & Kendala -->
          <button
            type="button"
            @click="handleTabClick(3)"
            class="transition-colors font-label-lg text-label-lg rounded-full cursor-pointer py-2 px-3.5 sm:px-4 whitespace-nowrap"
            :class="activeTabNum === 3
              ? 'bg-primary-fixed/40 text-primary font-bold shadow-2xs'
              : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low'"
          >
            Live Monitor &amp; Kendala
          </button>

          <!-- Tab 4: Laporan & Audit Log -->
          <button
            type="button"
            @click="handleTabClick(4)"
            class="transition-colors font-label-lg text-label-lg rounded-full cursor-pointer py-2 px-3.5 sm:px-4 whitespace-nowrap"
            :class="activeTabNum === 4
              ? 'bg-primary-fixed/40 text-primary font-bold shadow-2xs'
              : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low'"
          >
            Laporan &amp; Audit Log
          </button>
        </nav>
      </div>

      <!-- Right: Bot Online & Admin Profile -->
      <div class="flex items-center gap-space-md shrink-0">
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
            class="w-9 h-9 rounded-full object-cover ring-2 ring-primary-fixed"
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
        </div>

        <!-- Role Switcher -->
        <div class="hidden xl:flex items-center gap-1.5 pl-2 border-l border-surface-container-high">
          <router-link
            to="/"
            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-primary font-label-sm text-[11px] font-semibold transition-all border border-surface-container"
            title="Beralih ke Portal Faskes"
          >
            <span class="material-symbols-outlined text-[14px]">local_hospital</span>
            <span>Faskes</span>
          </router-link>
          <router-link
            to="/courier"
            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-primary font-label-sm text-[11px] font-semibold transition-all border border-surface-container"
            title="Beralih ke Portal Kurir"
          >
            <span class="material-symbols-outlined text-[14px]">two_wheeler</span>
            <span>Kurir</span>
          </router-link>
          <router-link
            to="/checker"
            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-primary font-label-sm text-[11px] font-semibold transition-all border border-surface-container"
            title="Beralih ke Portal Checker QC"
          >
            <span class="material-symbols-outlined text-[14px]">verified</span>
            <span>Checker</span>
          </router-link>
        </div>
      </div>
    </div>
  </header>
</template>
