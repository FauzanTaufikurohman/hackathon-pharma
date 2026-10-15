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
  <header class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-slate-100 shadow-[0_1px_8px_rgba(0,0,0,0.03)]">
    <div class="h-20 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4 sm:gap-6">
      <!-- Left: Logo & 4 Navigation Tabs -->
      <div class="flex items-center gap-4 lg:gap-6 shrink-0">
        <!-- Brand Logo Anteraja Pharma Express -->
        <router-link
          to="/courier"
          class="flex items-center gap-2.5 focus:outline-none"
          title="Satria Pharma Express"
        >
          <div class="flex items-center gap-1.5">
            <span class="w-8 h-8 rounded-lg bg-[#D81E5B] flex items-center justify-center text-white shadow-sm font-bold text-lg">
              <span class="material-symbols-outlined text-xl">local_shipping</span>
            </span>
            <div class="flex flex-col">
              <span class="font-headline-sm text-lg text-[#D81E5B] font-bold leading-none tracking-tight">anteraja</span>
              <span class="text-[10px] text-slate-400 uppercase tracking-widest font-semibold">Pharma Express</span>
            </div>
          </div>
          <span class="px-2.5 py-0.5 rounded-full bg-pink-100 text-[#D81E5B] text-xs uppercase tracking-wider font-bold hidden sm:inline-block">
            Satria Pharma
          </span>
        </router-link>

        <!-- 4 Nav Items -->
        <nav class="hidden lg:flex items-center gap-2">
          <!-- 1. Tugas Masuk -->
          <button
            type="button"
            @click="handleTabClick(1)"
            class="px-4 py-2 rounded-full text-sm font-semibold transition-all flex items-center gap-1.5 cursor-pointer"
            :class="activeTab === 1
              ? 'bg-[#FCE7F3] text-[#D81E5B] shadow-sm'
              : 'text-slate-600 hover:text-[#D81E5B] hover:bg-slate-50'"
          >
            <span class="material-symbols-outlined text-base">inbox</span>
            <span>Tugas Masuk</span>
          </button>

          <!-- 2. Pengantaran & Monitor -->
          <button
            type="button"
            @click="handleTabClick(2)"
            :title="!isAccepted ? 'Tugas belum diterima. Selesaikan checklist dan konfirmasi penerimaan terlebih dahulu.' : 'Pengantaran & Monitor'"
            class="px-4 py-2 rounded-full text-sm font-medium transition-all flex items-center gap-1.5"
            :class="[
              !isAccepted
                ? 'opacity-40 cursor-not-allowed text-slate-400 bg-slate-50/50'
                : activeTab === 2
                  ? 'bg-[#FCE7F3] text-[#D81E5B] font-semibold shadow-sm cursor-pointer'
                  : 'text-slate-600 hover:text-[#D81E5B] hover:bg-slate-50 cursor-pointer'
            ]"
          >
            <span v-if="!isAccepted" class="material-symbols-outlined text-[15px] text-slate-400">lock</span>
            <span>Pengantaran &amp; Monitor</span>
          </button>

          <!-- 3. Lapor Kendala -->
          <button
            type="button"
            @click="handleTabClick(3)"
            :title="!isAccepted ? 'Tugas belum diterima. Selesaikan checklist dan konfirmasi penerimaan terlebih dahulu.' : 'Lapor Kendala'"
            class="px-4 py-2 rounded-full text-sm font-medium transition-all flex items-center gap-1.5"
            :class="[
              !isAccepted
                ? 'opacity-40 cursor-not-allowed text-slate-400 bg-slate-50/50'
                : activeTab === 3
                  ? 'bg-[#FCE7F3] text-[#D81E5B] font-semibold shadow-sm cursor-pointer'
                  : 'text-slate-600 hover:text-[#D81E5B] hover:bg-slate-50 cursor-pointer'
            ]"
          >
            <span v-if="!isAccepted" class="material-symbols-outlined text-[15px] text-slate-400">lock</span>
            <span>Lapor Kendala</span>
          </button>

          <!-- 4. Serah Terima & Laporan -->
          <button
            type="button"
            @click="handleTabClick(4)"
            :title="!isAccepted ? 'Tugas belum diterima. Selesaikan checklist dan konfirmasi penerimaan terlebih dahulu.' : 'Serah Terima & Laporan'"
            class="px-4 py-2 rounded-full text-sm font-medium transition-all flex items-center gap-1.5"
            :class="[
              !isAccepted
                ? 'opacity-40 cursor-not-allowed text-slate-400 bg-slate-50/50'
                : activeTab === 4
                  ? 'bg-[#FCE7F3] text-[#D81E5B] font-semibold shadow-sm cursor-pointer'
                  : 'text-slate-600 hover:text-[#D81E5B] hover:bg-slate-50 cursor-pointer'
            ]"
          >
            <span v-if="!isAccepted" class="material-symbols-outlined text-[15px] text-slate-400">lock</span>
            <span>Serah Terima &amp; Laporan</span>
          </button>
        </nav>
      </div>

      <!-- Right: Courier Profile Chip & Logout -->
      <div class="flex items-center gap-3 shrink-0">
        <div class="flex items-center gap-3 bg-slate-50 border border-slate-200/80 rounded-full py-1.5 px-4 shadow-sm">
          <div class="w-8 h-8 rounded-full bg-[#D81E5B]/10 text-[#D81E5B] flex items-center justify-center font-bold text-xs uppercase">
            BS
          </div>
          <div class="text-left hidden sm:block">
            <div class="text-xs font-bold text-slate-800 leading-tight">Bambang Suryono</div>
            <div class="text-[11px] text-slate-500 font-mono">ID: KURIR-007</div>
          </div>
          <div class="flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full border border-emerald-200/60">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-[11px] font-semibold">Aktif</span>
          </div>
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
