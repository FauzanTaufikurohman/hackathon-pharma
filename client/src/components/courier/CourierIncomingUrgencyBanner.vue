<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

interface Props {
  initialSeconds?: number
  hubLocation?: string
}

const props = withDefaults(defineProps<Props>(), {
  initialSeconds: 14 * 60 + 52, // 14m 52s as per design
  hubLocation: 'Hub Cawang Staging #02',
})

const remainingSeconds = ref(props.initialSeconds)
let timerInterval: ReturnType<typeof setInterval> | null = null

const formattedTimer = ref('14:52')

function updateDisplay() {
  if (remainingSeconds.value <= 0) {
    formattedTimer.value = '00:00 EXPIRED'
    return
  }
  const m = Math.floor(remainingSeconds.value / 60)
    .toString()
    .padStart(2, '0')
  const s = (remainingSeconds.value % 60).toString().padStart(2, '0')
  formattedTimer.value = `${m}:${s}`
}

onMounted(() => {
  updateDisplay()
  timerInterval = setInterval(() => {
    if (remainingSeconds.value > 0) {
      remainingSeconds.value--
      updateDisplay()
    } else {
      if (timerInterval) clearInterval(timerInterval)
    }
  }, 1000)
})

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})
</script>

<template>
  <div
    class="w-full bg-surface-container-high rounded-xl p-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xs relative overflow-hidden"
  >
    <!-- Left Accent Stripe -->
    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-tertiary"></div>

    <!-- Alert Title & Context -->
    <div class="flex items-center gap-3.5 pl-2">
      <div class="w-10 h-10 rounded-full bg-tertiary-fixed flex items-center justify-center shrink-0 shadow-2xs">
        <span class="material-symbols-outlined text-tertiary" style="font-variation-settings: 'FILL' 1;">
          notifications_active
        </span>
      </div>

      <div>
        <div class="flex items-center gap-2 flex-wrap">
          <span class="font-headline-sm text-headline-sm text-on-surface font-bold">
            Penugasan Baru Menunggu Respon Satria
          </span>
          <span class="font-label-sm text-label-sm font-mono bg-surface-container px-2 py-0.5 rounded text-on-surface-variant">
            assignments.status = 'pending'
          </span>
        </div>
        <p class="font-body-sm text-body-sm text-on-surface-variant">
          1 Paket Cold-Chain Kritis Siap Diambil di {{ hubLocation }}
        </p>
      </div>
    </div>

    <!-- Right Countdown Widget -->
    <div class="flex items-center gap-2 self-end md:self-auto bg-surface-container-lowest px-4 py-2 rounded-lg shadow-xs border border-surface-container/60">
      <span class="material-symbols-outlined text-tertiary text-sm animate-pulse">timer</span>
      <span class="font-label-md text-label-md text-on-surface-variant font-medium">Batas Waktu Respon:</span>
      <span
        class="font-headline-sm text-headline-sm font-mono tracking-wider font-bold"
        :class="remainingSeconds <= 60 ? 'text-error animate-pulse' : 'text-tertiary'"
      >
        {{ formattedTimer }}
      </span>
    </div>
  </div>
</template>
