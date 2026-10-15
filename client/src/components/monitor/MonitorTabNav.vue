<script setup lang="ts">
import { nextTick, onMounted, ref, watch } from 'vue'
const nav = ref<HTMLElement | null>(null)
const props = defineProps<{
  activeTab: number // 1: Monitor Pengiriman, 2: Lacak Suhu, 3: Laporan
  awbNumber: string
}>()

const emit = defineEmits<{
  (e: 'update:activeTab', tab: number): void
}>()
async function revealActiveTab() {
  await nextTick()
  const element = nav.value?.querySelector<HTMLElement>(
    'button[aria-current="page"]',
  )
  element?.scrollIntoView({ block: 'nearest', inline: 'nearest' })
}
onMounted(revealActiveTab)
watch(() => props.activeTab, revealActiveTab)
</script>

<template>
  <div
    class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-md mb-6"
  >
    <!-- 3 Nav Tabs -->
    <nav
      ref="nav"
      aria-label="Bagian detail kiriman"
      class="flex min-w-0 items-center gap-1 bg-surface-container-low p-1 rounded-full border border-surface-container overflow-x-auto"
    >
      <!-- Tab 1: Monitor Pengiriman (Active) -->
      <button
        type="button"
        @click="emit('update:activeTab', 1)"
        :aria-current="activeTab === 1 ? 'page' : undefined"
        class="px-5 py-2 transition-all rounded-full font-label-md text-label-md font-bold whitespace-nowrap cursor-pointer"
        :class="
          activeTab === 1
            ? 'bg-primary-fixed text-primary shadow-xs'
            : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high'
        "
      >
        Monitor Pengiriman
      </button>

      <!-- Tab 2: Lacak Kiriman & Suhu -->
      <button
        type="button"
        @click="emit('update:activeTab', 2)"
        :aria-current="activeTab === 2 ? 'page' : undefined"
        class="px-5 py-2 transition-all rounded-full font-label-md text-label-md font-bold whitespace-nowrap cursor-pointer"
        :class="
          activeTab === 2
            ? 'bg-primary-fixed text-primary shadow-xs'
            : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high'
        "
      >
        Lacak Kiriman &amp; Suhu
      </button>

      <!-- Tab 3: Laporan & Riwayat Audit -->
      <button
        type="button"
        @click="emit('update:activeTab', 3)"
        :aria-current="activeTab === 3 ? 'page' : undefined"
        class="px-5 py-2 transition-all rounded-full font-label-md text-label-md font-bold whitespace-nowrap cursor-pointer"
        :class="
          activeTab === 3
            ? 'bg-primary-fixed text-primary shadow-xs'
            : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high'
        "
      >
        Laporan &amp; Riwayat Audit
      </button>
    </nav>

    <!-- Back to Package List Link -->
    <router-link
      to="/paket-saya"
      class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-surface-container hover:bg-surface-variant text-on-surface font-label-md text-[13px] font-semibold transition-all border border-surface-container-high self-start sm:self-auto cursor-pointer"
    >
      <span class="material-symbols-outlined text-[18px]">arrow_back</span>
      <span>Kembali ke Paket Saya</span>
    </router-link>
  </div>
</template>
