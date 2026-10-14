<script setup lang="ts">
import { ref } from 'vue'

interface Props {
  hubLocation?: string
  tempValue?: string
  tempStatus?: string
  bleScannerStatus?: string
  gatewayStatus?: string
  gatewayLatency?: string
}

withDefaults(defineProps<Props>(), {
  hubLocation: 'Hub Gudang Cawang • Staging Cold Room',
  tempValue: '3.8°C',
  tempStatus: 'Optimal (2.0°C – 8.0°C)',
  bleScannerStatus: 'Terhubung (Kamera Aktif)',
  gatewayStatus: 'Aktif',
  gatewayLatency: '18ms',
})

const emit = defineEmits<{
  (e: 'sync'): void
}>()

const isSyncing = ref(false)

const handleSync = () => {
  if (isSyncing.value) return
  isSyncing.value = true
  emit('sync')
  setTimeout(() => {
    isSyncing.value = false
  }, 1200)
}
</script>

<template>
  <div
    class="bg-inverse-surface text-inverse-on-surface rounded-2xl p-4 shadow-lg flex flex-col lg:flex-row items-center justify-between gap-4 border border-surface-container-high/10"
  >
    <!-- Left: Temperature & Cold Room Status -->
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-surface-container-lowest/10 flex items-center justify-center text-secondary-fixed shadow-inner">
        <span class="material-symbols-outlined text-2xl">thermostat</span>
      </div>
      <div>
        <div class="font-label-sm text-label-sm text-surface-container-high uppercase tracking-wide font-medium">
          {{ hubLocation }}
        </div>
        <div class="flex items-center gap-2 mt-0.5">
          <span class="font-headline-sm text-headline-sm text-white font-bold tracking-tight">
            {{ tempValue }}
          </span>
          <span class="bg-secondary text-on-secondary font-label-sm text-label-sm px-2 py-0.5 rounded font-bold shadow-2xs">
            {{ tempStatus }}
          </span>
        </div>
      </div>
    </div>

    <!-- Status Toggles & Diagnostics -->
    <div class="flex flex-wrap items-center gap-6">
      <!-- BLE Scanner status -->
      <div class="flex items-center gap-2.5">
        <span class="relative flex h-2.5 w-2.5">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary-fixed opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-secondary-fixed"></span>
        </span>
        <div class="flex flex-col">
          <span class="font-label-sm text-label-sm text-surface-dim font-medium">Scanner BLE v5.3</span>
          <span class="font-label-md text-label-md text-white font-semibold">
            {{ bleScannerStatus }}
          </span>
        </div>
      </div>

      <div class="h-6 w-px bg-surface-container-high/20 hidden sm:block"></div>

      <!-- IoT Gateway status -->
      <div class="flex items-center gap-2.5">
        <span class="material-symbols-outlined text-secondary-fixed text-lg">sensors</span>
        <div class="flex flex-col">
          <span class="font-label-sm text-label-sm text-surface-dim font-medium">IoT Gateway Status</span>
          <span class="font-label-md text-label-md text-white font-semibold">
            {{ gatewayStatus }} • Latensi {{ gatewayLatency }}
          </span>
        </div>
      </div>

      <div class="h-6 w-px bg-surface-container-high/20 hidden sm:block"></div>

      <!-- Refresh / Sync Button -->
      <button
        type="button"
        @click="handleSync"
        :disabled="isSyncing"
        class="bg-surface-container-lowest/10 hover:bg-surface-container-lowest/20 active:scale-95 text-white font-label-md text-label-md px-3.5 py-1.5 rounded-lg flex items-center gap-1.5 transition-all cursor-pointer font-semibold disabled:opacity-50"
      >
        <span
          class="material-symbols-outlined text-base transition-transform"
          :class="{ 'animate-spin': isSyncing }"
        >
          refresh
        </span>
        <span>{{ isSyncing ? 'Menyinkronkan...' : 'Sinkronisasi' }}</span>
      </button>
    </div>
  </div>
</template>
