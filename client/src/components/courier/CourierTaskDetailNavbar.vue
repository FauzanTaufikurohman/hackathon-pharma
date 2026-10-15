<script setup lang="ts">
import { useRouter } from 'vue-router'
import { logoutAndRedirect } from '../../services/auth'

interface Props {
  activeTab: number
  awb?: string
  isAccepted?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isAccepted: false,
})

const emit = defineEmits<{
  (e: 'tab-change', tab: number): void
  (e: 'locked-tab-click', tab: number): void
}>()

const router = useRouter()

function handleTabClick(tab: number) {
  if (tab === 1) {
    emit('tab-change', 1)
    return
  }

  if (!props.isAccepted) {
    emit('locked-tab-click', tab)
    return
  }

  emit('tab-change', tab)
}

function handleLogout() {
  return logoutAndRedirect(router)
}
</script>

<template>
  <header class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-xl border-b border-slate-100 shadow-sm">
    <div class="h-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
      <!-- Left: Logo & 4 Navigation Tabs -->
      <div class="flex items-center gap-5 lg:gap-8">
        <!-- Logo Anteraja Pharma -->
        <router-link
          to="/courier"
          class="flex items-center focus:outline-none rounded-lg shrink-0"
          title="Beranda Kurir Anteraja Pharma"
        >
          <img
            alt="Anteraja Pharma"
            class="h-9 w-auto object-contain"
            src="/pharma-logo.webp"
          />
        </router-link>

        <!-- 4 Nav Items (Styled like customer/vendor tabs) -->
        <nav class="flex items-center gap-1 sm:gap-1.5 overflow-x-auto py-1">
          <!-- 1. Tugas Masuk -->
          <button
            type="button"
            @click="handleTabClick(1)"
            class="transition-all py-1.5 px-3 sm:px-3.5 rounded-full text-xs font-semibold cursor-pointer flex items-center gap-1.5"
            :class="activeTab === 1
              ? 'bg-[#FCE7F3] text-[#D81E5B] shadow-xs'
              : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100'"
          >
            <span v-if="activeTab === 1" class="w-1.5 h-1.5 rounded-full bg-[#D81E5B]"></span>
            <span>Tugas Masuk</span>
          </button>

          <!-- 2. Pengantaran & Monitor -->
          <button
            type="button"
            @click="handleTabClick(2)"
            :title="!isAccepted ? 'Tugas belum diterima. Selesaikan checklist dan konfirmasi penerimaan terlebih dahulu.' : 'Pengantaran & Monitor'"
            class="transition-all py-1.5 px-3 sm:px-3.5 rounded-full text-xs font-semibold flex items-center gap-1.5"
            :class="[
              !isAccepted
                ? 'opacity-40 cursor-not-allowed text-slate-400 bg-slate-50/50'
                : activeTab === 2
                  ? 'bg-[#FCE7F3] text-[#D81E5B] shadow-xs cursor-pointer'
                  : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100 cursor-pointer'
            ]"
          >
            <span v-if="!isAccepted" class="material-symbols-outlined text-[14px] text-slate-400">lock</span>
            <span v-else-if="activeTab === 2" class="w-1.5 h-1.5 rounded-full bg-[#D81E5B]"></span>
            <span>Pengantaran &amp; Monitor</span>
          </button>

          <!-- 3. Lapor Kendala -->
          <button
            type="button"
            @click="handleTabClick(3)"
            :title="!isAccepted ? 'Tugas belum diterima. Selesaikan checklist dan konfirmasi penerimaan terlebih dahulu.' : 'Lapor Kendala'"
            class="transition-all py-1.5 px-3 sm:px-3.5 rounded-full text-xs font-semibold flex items-center gap-1.5"
            :class="[
              !isAccepted
                ? 'opacity-40 cursor-not-allowed text-slate-400 bg-slate-50/50'
                : activeTab === 3
                  ? 'bg-[#FCE7F3] text-[#D81E5B] shadow-xs cursor-pointer'
                  : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100 cursor-pointer'
            ]"
          >
            <span v-if="!isAccepted" class="material-symbols-outlined text-[14px] text-slate-400">lock</span>
            <span v-else-if="activeTab === 3" class="w-1.5 h-1.5 rounded-full bg-[#D81E5B]"></span>
            <span>Lapor Kendala</span>
          </button>

          <!-- 4. Serah Terima & Laporan -->
          <button
            type="button"
            @click="handleTabClick(4)"
            :title="!isAccepted ? 'Tugas belum diterima. Selesaikan checklist dan konfirmasi penerimaan terlebih dahulu.' : 'Serah Terima & Laporan'"
            class="transition-all py-1.5 px-3 sm:px-3.5 rounded-full text-xs font-semibold flex items-center gap-1.5"
            :class="[
              !isAccepted
                ? 'opacity-40 cursor-not-allowed text-slate-400 bg-slate-50/50'
                : activeTab === 4
                  ? 'bg-[#FCE7F3] text-[#D81E5B] shadow-xs cursor-pointer'
                  : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100 cursor-pointer'
            ]"
          >
            <span v-if="!isAccepted" class="material-symbols-outlined text-[14px] text-slate-400">lock</span>
            <span v-else-if="activeTab === 4" class="w-1.5 h-1.5 rounded-full bg-[#D81E5B]"></span>
            <span>Serah Terima &amp; Laporan</span>
          </button>
        </nav>
      </div>

      <!-- Right: Courier Profile Chip & Logout -->
      <div class="flex items-center gap-3 shrink-0">
        <div class="hidden sm:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-50 border border-slate-200/80 shadow-xs">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span class="font-semibold text-xs text-slate-800">Bambang Suryono (KURIR-007)</span>
          <span class="material-symbols-outlined text-emerald-600 text-[14px]">verified</span>
        </div>

        <button
          type="button"
          @click="handleLogout"
          class="inline-flex items-center rounded-full border border-slate-200 px-3.5 py-1.5 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 cursor-pointer"
        >
          Keluar
        </button>
      </div>
    </div>
  </header>
</template>
