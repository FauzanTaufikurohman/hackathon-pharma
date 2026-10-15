<script setup lang="ts">
import { useRouter } from 'vue-router'
import { mockCheckerProfile } from '../../data/mockShipments'

interface Props {
  activeTab?: number
}

withDefaults(defineProps<Props>(), {
  activeTab: 1,
})

const emit = defineEmits<{
  (e: 'tab-change', tab: number): void
}>()

const router = useRouter()

const handleTabClick = (tab: number) => {
  emit('tab-change', tab)
  if (tab === 1) {
    router.push('/checker')
  } else if (tab === 2) {
    router.push('/checker/form-initial-exam')
  } else if (tab === 3) {
    router.push('/checker/riwayat')
  }
}
</script>

<template>
  <header class="fixed top-0 left-0 right-0 z-50 bg-surface/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-surface-container/40">
    <div class="h-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-space-md">
      <!-- Left: Logo & Role Pill -->
      <div class="flex items-center gap-space-md lg:gap-space-lg">
        <router-link
          to="/checker"
          class="flex items-center gap-space-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-lg group shrink-0"
          title="Portal Checker QC Hub"
        >
          <img
            alt="Anteraja Pharma"
            class="h-9 md:h-10 w-auto object-contain transition-transform group-hover:scale-105"
            src="/images/anteraja-pharma-logo.png"
          />
        </router-link>

        <span class="hidden sm:inline-flex bg-inverse-surface text-inverse-on-surface font-label-sm text-label-sm px-2.5 py-1 rounded-full uppercase tracking-widest items-center gap-1.5 shadow-sm font-bold">
          <span class="w-1.5 h-1.5 rounded-full bg-primary-fixed-dim"></span>
          CHECKER QC HUB
        </span>

        <!-- Center: 3 Feature Navigation Tabs -->
        <nav class="hidden md:flex items-center gap-space-xs sm:gap-space-sm pl-2">
          <!-- Tab 1: Antrean Pemeriksaan -->
          <button
            type="button"
            @click="handleTabClick(1)"
            class="transition-colors font-label-lg text-label-lg rounded-full py-2 px-3.5 sm:px-4 cursor-pointer whitespace-nowrap"
            :class="activeTab === 1
              ? 'bg-primary-fixed/40 text-primary font-bold shadow-2xs'
              : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low'"
          >
            Antrean Pemeriksaan
          </button>

          <!-- Tab 2: Form Initial Exam -->
          <button
            type="button"
            @click="handleTabClick(2)"
            class="transition-colors font-label-lg text-label-lg rounded-full py-2 px-3.5 sm:px-4 cursor-pointer whitespace-nowrap"
            :class="activeTab === 2
              ? 'bg-primary-fixed/40 text-primary font-bold shadow-2xs'
              : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low'"
          >
            Form Initial Exam
          </button>

          <!-- Tab 3: Riwayat & Status Approval -->
          <button
            type="button"
            @click="handleTabClick(3)"
            class="transition-colors font-label-lg text-label-lg rounded-full py-2 px-3.5 sm:px-4 cursor-pointer whitespace-nowrap"
            :class="activeTab === 3
              ? 'bg-primary-fixed/40 text-primary font-bold shadow-2xs'
              : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low'"
          >
            Riwayat &amp; Status Approval
          </button>
        </nav>
      </div>

      <!-- Right: Hub Staging Location & Checker Profile -->
      <div class="flex items-center gap-3 sm:gap-4 shrink-0">
        <!-- Warehouse Staging Badge -->
        <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-full bg-surface-container-low text-on-surface-variant font-label-sm text-label-sm border border-surface-container shadow-2xs">
          <span class="material-symbols-outlined text-secondary text-base">warehouse</span>
          <span>Hub Cawang Staging #02</span>
        </div>

        <!-- Checker Profile -->
        <div class="flex items-center gap-2.5">
          <div class="hidden sm:flex flex-col text-right">
            <span class="font-label-md text-label-md text-on-surface font-bold leading-tight">
              {{ mockCheckerProfile.name }}
            </span>
            <span class="font-label-sm text-[11px] text-on-surface-variant">
              {{ mockCheckerProfile.id_code }} • Shift Pagi
            </span>
          </div>

          <div class="relative shrink-0">
            <img
              alt="Checker Profile"
              class="w-9 h-9 rounded-full object-cover ring-2 ring-primary/20 shadow-xs"
              src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=120"
            />
            <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-secondary ring-2 ring-surface-container-lowest"></span>
          </div>
        </div>

        <!-- Role Switcher -->
        <div class="hidden lg:flex items-center gap-1.5 pl-2 border-l border-surface-container-high">
          <router-link
            to="/admin"
            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-primary font-label-sm text-[11px] font-semibold transition-all border border-surface-container"
            title="Beralih ke Portal Admin"
          >
            <span class="material-symbols-outlined text-[14px]">admin_panel_settings</span>
            <span>Admin</span>
          </router-link>
        </div>
      </div>
    </div>
  </header>
</template>
