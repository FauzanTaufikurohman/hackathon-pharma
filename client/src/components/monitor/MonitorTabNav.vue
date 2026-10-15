<script setup lang="ts">
const props = defineProps<{
  activeTab: number // 1: Halaman Pantau, 2: Pelacakan GPS, 3: Laporan
  awbNumber: string
}>()

const emit = defineEmits<{
  (e: 'update:activeTab', tab: number): void
}>()
</script>

<template>
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <!-- 3 Nav Tabs -->
    <nav class="flex items-center gap-1.5 bg-white p-1.5 rounded-full border border-slate-200/80 shadow-xs overflow-x-auto">
      <!-- Tab 1: Halaman Pantau -->
      <button
        type="button"
        @click="emit('update:activeTab', 1)"
        class="px-4 py-1.5 transition-all rounded-full text-sm whitespace-nowrap cursor-pointer"
        :class="activeTab === 1
          ? 'bg-[#FCE7F3] text-[#D81E5B] font-semibold shadow-xs'
          : 'text-slate-600 hover:text-slate-900 font-medium hover:bg-slate-100'"
      >
        Halaman Pantau
      </button>

      <!-- Tab 2: Pelacakan GPS -->
      <button
        type="button"
        @click="emit('update:activeTab', 2)"
        class="px-4 py-1.5 transition-all rounded-full text-sm whitespace-nowrap cursor-pointer"
        :class="activeTab === 2
          ? 'bg-[#FCE7F3] text-[#D81E5B] font-semibold shadow-xs'
          : 'text-slate-600 hover:text-slate-900 font-medium hover:bg-slate-100'"
      >
        Pelacakan GPS
      </button>

      <!-- Tab 3: Laporan -->
      <button
        type="button"
        @click="emit('update:activeTab', 3)"
        class="px-4 py-1.5 transition-all rounded-full text-sm whitespace-nowrap cursor-pointer"
        :class="activeTab === 3
          ? 'bg-[#FCE7F3] text-[#D81E5B] font-semibold shadow-xs'
          : 'text-slate-600 hover:text-slate-900 font-medium hover:bg-slate-100'"
      >
        Laporan
      </button>
    </nav>

    <!-- Back to Package List Link -->
    <router-link
      to="/paket-saya"
      class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs transition-all border border-slate-200/60 self-start sm:self-auto cursor-pointer"
    >
      <span class="material-symbols-outlined text-[16px]">arrow_back</span>
      <span>Kembali ke Paket Saya</span>
    </router-link>
  </div>
</template>
