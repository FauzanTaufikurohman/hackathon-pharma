<script setup lang="ts">
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
</script>

<template>
  <header class="fixed top-0 left-0 w-full z-50 bg-surface-container-lowest/95 backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-surface-container/40">
    <div class="h-20 w-full px-4 sm:px-8 flex items-center justify-between">
      <!-- Left: Brand Logo & Role Pill -->
      <div class="flex items-center gap-4">
        <div class="flex items-center gap-3">
          <router-link to="/checker" class="flex items-center group cursor-pointer">
            <span class="font-headline-lg text-headline-lg text-primary tracking-tight font-extrabold group-hover:scale-105 transition-transform">
              Anteraja
            </span>
            <span class="font-headline-sm text-headline-sm text-secondary ml-1 font-bold">
              Pharma
            </span>
          </router-link>

          <div class="h-6 w-px bg-surface-container-high hidden sm:block"></div>

          <span class="bg-inverse-surface text-inverse-on-surface font-label-sm text-label-sm px-2.5 py-1 rounded-md tracking-wider uppercase flex items-center gap-1.5 shadow-2xs font-bold">
            <span class="w-1.5 h-1.5 rounded-full bg-primary-fixed-dim"></span>
            CHECKER QC HUB
          </span>
        </div>
      </div>

      <!-- Center: 3 Feature Navigation Tabs -->
      <nav
        class="hidden md:flex items-center gap-2 p-1 bg-surface-container-low/60 rounded-full border border-surface-container/60"
        data-active-classes="bg-primary-fixed text-primary font-label-lg rounded-full px-6 py-2.5"
      >
        <!-- Tab 1: Antrean Pemeriksaan -->
        <button
          type="button"
          @click="emit('tab-change', 1)"
          class="transition-all font-label-lg text-label-lg rounded-full px-6 py-2.5 cursor-pointer font-bold"
          :class="activeTab === 1
            ? 'bg-primary-fixed text-primary shadow-xs'
            : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container'"
        >
          Antrean Pemeriksaan
        </button>

        <!-- Tab 2: Form Initial Exam -->
        <button
          type="button"
          @click="emit('tab-change', 2)"
          class="transition-all font-label-lg text-label-lg rounded-full px-5 py-2.5 cursor-pointer font-bold"
          :class="activeTab === 2
            ? 'bg-primary-fixed text-primary shadow-xs'
            : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container'"
        >
          Form Initial Exam
        </button>

        <!-- Tab 3: Riwayat & Status Approval -->
        <button
          type="button"
          @click="emit('tab-change', 3)"
          class="transition-all font-label-lg text-label-lg rounded-full px-5 py-2.5 cursor-pointer font-bold"
          :class="activeTab === 3
            ? 'bg-primary-fixed text-primary shadow-xs'
            : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container'"
        >
          Riwayat &amp; Status Approval
        </button>
      </nav>

      <!-- Right: Hub Staging Location & Checker Profile -->
      <div class="flex items-center gap-4">
        <!-- Warehouse Staging Badge -->
        <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-surface-container-low text-on-surface-variant font-label-md text-label-md border border-surface-container/60 shadow-2xs">
          <span class="material-symbols-outlined text-secondary text-sm">warehouse</span>
          <span class="font-semibold">{{ mockCheckerProfile.hub_name }}</span>
        </div>

        <!-- Checker Profile Avatar & Status -->
        <div class="flex items-center gap-3 pl-2">
          <div class="relative flex items-center">
            <img
              alt="Hendra Wijaya Profile"
              class="w-8 h-8 rounded-full object-cover ring-2 ring-surface-container-high shadow-2xs"
              :src="mockCheckerProfile.avatar_url"
            />
            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-secondary ring-2 ring-surface-container-lowest"></span>
          </div>

          <div class="hidden sm:flex flex-col text-left">
            <span class="font-label-md text-label-md text-on-surface leading-tight font-bold">
              {{ mockCheckerProfile.name }}
            </span>
            <span class="font-body-sm text-body-sm text-on-surface-variant leading-tight">
              {{ mockCheckerProfile.id_code }}
            </span>
          </div>

          <!-- Switch portal / logout -->
          <router-link
            to="/"
            class="font-label-sm text-slate-400 hover:text-primary transition-colors pl-2 font-medium"
            title="Beralih ke Portal Buyer"
          >
            Keluar
          </router-link>
        </div>
      </div>
    </div>
  </header>
</template>
