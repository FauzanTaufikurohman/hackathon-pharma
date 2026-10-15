<script setup lang="ts">
import { useRouter } from 'vue-router'
import { logoutAndRedirect } from '../../services/auth'

interface Props {
  activeTab: number
  awb?: string
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'tab-change', tab: number): void
}>()

const router = useRouter()

function handleLogout() {
  return logoutAndRedirect(router)
}
</script>

<template>
  <header class="fixed top-0 left-0 w-full z-50 bg-surface-container-lowest border-b border-surface-container-high shadow-[0_1px_8px_rgba(0,0,0,0.03)]">
    <div class="h-16 max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between gap-4">
      <!-- Left: Logo & Role Pill -->
      <div class="flex items-center gap-3 shrink-0">
        <router-link
          to="/courier/tugas-saya"
          class="flex items-center gap-2 group cursor-pointer"
          title="Kembali ke Daftar Tugas Pengiriman"
        >
          <img
            alt="Profile Satria"
            class="w-8 h-8 rounded-full object-cover ring-1 ring-primary/20 group-hover:scale-105 transition-transform"
            src="https://lh3.googleusercontent.com/aida/AEtjO1VlCA1gT2NeF63LTHp4NUdJAzu3ELNjJ5-m0kgFZNK49vAhP9XAhblMao72jPxkMQXWXUcrGR4gJwFV0TTrw1MDoA8iKQzj0frZjK1a9XOklpKAEL1uqX8w2z1rAblx52T0aV3HF28xs8S0Zonq7ZRYg08yGG-YXIK1g5zb-9pYCFSuAKN9DbyClgxNf7mSeaUNHR_Y9BZImWQ7b_q28f8z4WxtEHhd74pOaBe-JF4AREOF12VAzJZya5XQan6ITd4eUWQnHfxnGG4"
          />
          <div class="flex flex-col">
            <span class="font-headline-sm text-headline-sm text-primary leading-none tracking-tight">Anteraja Pharma</span>
            <span class="font-label-sm text-label-sm text-on-surface-variant leading-tight">Cold-Chain Logistics</span>
          </div>
        </router-link>

        <span class="px-2.5 py-0.5 rounded-full bg-primary-fixed text-on-primary-fixed-variant font-label-sm text-label-sm uppercase tracking-wider font-bold">
          Satria Pharma
        </span>
      </div>

      <!-- Center: 4-Tab Navigation for Courier Task Execution -->
      <nav
        class="hidden lg:flex items-center gap-1.5 bg-surface-container-low p-1.5 rounded-full border border-surface-container"
        data-active-classes="bg-primary-fixed text-primary font-label-lg text-label-lg font-bold rounded-full shadow-sm"
      >
        <!-- Tab 1: Tugas Masuk -->
        <button
          type="button"
          @click="emit('tab-change', 1)"
          class="px-5 py-2 transition-all font-label-lg text-label-lg cursor-pointer"
          :class="activeTab === 1
            ? 'bg-primary-fixed text-primary font-bold rounded-full shadow-xs'
            : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high rounded-full'"
        >
          Tugas Masuk
        </button>

        <!-- Tab 2: Pengantaran & Monitor -->
        <button
          type="button"
          @click="emit('tab-change', 2)"
          class="px-5 py-2 transition-all font-label-lg text-label-lg cursor-pointer"
          :class="activeTab === 2
            ? 'bg-primary-fixed text-primary font-bold rounded-full shadow-xs'
            : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high rounded-full'"
        >
          Pengantaran &amp; Monitor
        </button>

        <!-- Tab 3: Lapor Kendala -->
        <button
          type="button"
          @click="emit('tab-change', 3)"
          class="px-5 py-2 transition-all font-label-lg text-label-lg cursor-pointer"
          :class="activeTab === 3
            ? 'bg-primary-fixed text-primary font-bold rounded-full shadow-xs'
            : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high rounded-full'"
        >
          Lapor Kendala
        </button>

        <!-- Tab 4: Serah Terima & Laporan -->
        <button
          type="button"
          @click="emit('tab-change', 4)"
          class="px-5 py-2 transition-all font-label-lg text-label-lg cursor-pointer"
          :class="activeTab === 4
            ? 'bg-primary-fixed text-primary font-bold rounded-full shadow-xs'
            : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high rounded-full'"
        >
          Serah Terima &amp; Laporan
        </button>
      </nav>

      <!-- Right: Courier Duty & Profile -->
      <div class="flex items-center gap-3 shrink-0">
        <div class="text-right hidden sm:block">
          <div class="font-label-lg text-label-lg text-on-surface font-semibold">Bambang Suryono</div>
          <div class="font-label-sm text-label-sm text-on-surface-variant">ID: KURIR-007</div>
        </div>

        <div class="flex items-center gap-1.5 bg-surface-container px-3 py-1 rounded-full border border-surface-container-high shadow-2xs">
          <span class="w-2 h-2 rounded-full bg-secondary"></span>
          <span class="font-label-sm text-label-sm text-on-surface font-semibold">Aktif</span>
        </div>
        <button
          type="button"
          @click="handleLogout"
          class="text-sm font-medium text-slate-500 transition-colors hover:text-primary"
        >
          Keluar
        </button>
      </div>
    </div>
  </header>
</template>
