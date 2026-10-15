<script setup lang="ts">
interface Props {
  activeTab: number
  awb?: string
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'tab-change', tab: number): void
}>()
</script>

<template>
  <header class="fixed top-0 left-0 right-0 z-50 bg-surface/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-surface-container/40">
    <div class="h-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
      <!-- Left: Logo & Role Pill -->
      <div class="flex items-center gap-space-md shrink-0">
        <router-link
          to="/courier/tugas-saya"
          class="flex items-center gap-space-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-lg group shrink-0"
          title="Kembali ke Daftar Tugas Pengiriman"
        >
          <img
            alt="Anteraja Pharma"
            class="h-9 md:h-10 w-auto object-contain transition-transform group-hover:scale-105"
            src="/images/anteraja-pharma-logo.png"
          />
        </router-link>

        <span class="hidden sm:inline-flex bg-inverse-surface text-inverse-on-surface font-label-sm text-label-sm px-2.5 py-1 rounded-full uppercase tracking-widest items-center gap-1.5 shadow-sm font-bold">
          <span class="w-1.5 h-1.5 rounded-full bg-primary-fixed-dim"></span>
          SATRIA PHARMA
        </span>
      </div>

      <!-- Center: 4-Tab Navigation for Courier Task Execution -->
      <nav
        class="hidden lg:flex items-center gap-1.5"
        data-active-classes="bg-primary-fixed/40 text-primary font-label-lg text-label-lg font-bold rounded-full shadow-2xs"
      >
        <!-- Tab 1: Tugas Masuk -->
        <button
          type="button"
          @click="emit('tab-change', 1)"
          class="px-4 py-2 transition-all font-label-lg text-label-lg rounded-full cursor-pointer"
          :class="activeTab === 1
            ? 'bg-primary-fixed/40 text-primary font-bold shadow-2xs'
            : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low'"
        >
          Tugas Masuk
        </button>

        <!-- Tab 2: Pengantaran & Monitor -->
        <button
          type="button"
          @click="emit('tab-change', 2)"
          class="px-4 py-2 transition-all font-label-lg text-label-lg rounded-full cursor-pointer"
          :class="activeTab === 2
            ? 'bg-primary-fixed/40 text-primary font-bold shadow-2xs'
            : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low'"
        >
          Pengantaran &amp; Monitor
        </button>

        <!-- Tab 3: Lapor Kendala -->
        <button
          type="button"
          @click="emit('tab-change', 3)"
          class="px-4 py-2 transition-all font-label-lg text-label-lg rounded-full cursor-pointer"
          :class="activeTab === 3
            ? 'bg-primary-fixed/40 text-primary font-bold shadow-2xs'
            : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low'"
        >
          Lapor Kendala
        </button>

        <!-- Tab 4: Serah Terima & Laporan -->
        <button
          type="button"
          @click="emit('tab-change', 4)"
          class="px-4 py-2 transition-all font-label-lg text-label-lg rounded-full cursor-pointer"
          :class="activeTab === 4
            ? 'bg-primary-fixed/40 text-primary font-bold shadow-2xs'
            : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low'"
        >
          Serah Terima &amp; Laporan
        </button>
      </nav>

      <!-- Right: Courier Duty & Profile -->
      <div class="flex items-center gap-3 shrink-0">
        <div class="text-right hidden sm:block">
          <div class="font-label-md text-label-md text-on-surface font-bold">Bambang Suryono</div>
          <div class="font-label-sm text-label-sm text-on-surface-variant">ID: KURIR-007</div>
        </div>

        <router-link
          to="/courier/tugas-saya"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface font-label-sm text-label-sm font-semibold transition-all border border-surface-container"
        >
          <span class="material-symbols-outlined text-[16px]">arrow_back</span>
          <span class="hidden sm:inline">Daftar Tugas</span>
        </router-link>
      </div>
    </div>
  </header>
</template>
