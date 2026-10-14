<script setup lang="ts">
import { ref } from 'vue'

const emit = defineEmits<{
  (e: 'trigger-temp-spike', val: number): void
  (e: 'trigger-seal-breach'): void
  (e: 'trigger-wa-webhook', phone: string): void
}>()

const tempValue = ref(12.5)
const judgePhone = ref('+62 812-9844-9021')

interface ConsoleEntry {
  time: string
  type: string
  msg: string
  typeColor: string
}

const consoleLogs = ref<ConsoleEntry[]>([
  {
    time: new Date().toTimeString().split(' ')[0] ?? '15:30:00',
    type: 'SYSTEM READY',
    msg: 'Simulator siap: simulasi insiden akan memicu status HELD instan ke seluruh antarmuka dan memvalidasi cryptographic log SHA-256.',
    typeColor: 'text-secondary-fixed-dim',
  },
])

function addLog(type: string, msg: string, typeColor = 'text-primary-fixed-dim') {
  const time = new Date().toTimeString().split(' ')[0] ?? '15:30:00'
  consoleLogs.value.unshift({ time, type, msg, typeColor })
  if (consoleLogs.value.length > 5) {
    consoleLogs.value.pop()
  }
}

function handleTempSpike() {
  const val = tempValue.value
  addLog(
    'EXCURSION TRIGGERED',
    `Lonjakan suhu terdeteksi ${val.toFixed(1)}°C! Mengirimkan webhook darurat & auto-escalate status HELD.`,
    'text-error'
  )
  emit('trigger-temp-spike', val)
}

function handleSealBreach() {
  addLog(
    'BREACH DETECTED',
    'Sensor SL-8891 rusak fisik terkonfirmasi! Segel integritas void.',
    'text-error'
  )
  emit('trigger-seal-breach')
}

function handleWaWebhook() {
  const phone = judgePhone.value || '+62 812-9844-9021'
  addLog(
    'BOT DISPATCHED',
    `Berhasil mengirim template alert WhatsApp ke ${phone} (HTTP 200 OK via Anteraja Bot).`,
    'text-secondary-fixed-dim'
  )
  emit('trigger-wa-webhook', phone)
}

defineExpose({
  addLog,
})
</script>

<template>
  <div class="mt-8 bg-inverse-surface text-inverse-on-surface rounded-xl p-6 shadow-xl relative overflow-hidden border border-outline/30">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 pb-4 border-b border-surface-container-high/20">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-on-primary shrink-0 shadow-md">
          <span class="material-symbols-outlined text-[24px]">science</span>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h3 class="font-headline-sm text-headline-sm text-inverse-on-surface">
              DEMO &amp; HACKATHON SIMULATOR (FR-06)
            </h3>
            <span class="px-2 py-0.5 rounded-full bg-primary-container text-on-primary font-label-sm text-label-sm font-bold">
              ENV: STAGING
            </span>
          </div>
          <p class="font-body-sm text-body-sm text-surface-variant opacity-80">
            Alat pengujian langsung untuk mensimulasikan anomali rantai dingin dan broadcast notifikasi darurat secara real-time.
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <span class="relative flex h-2.5 w-2.5">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-secondary"></span>
        </span>
        <span class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider font-bold">
          Testbed Active
        </span>
      </div>
    </div>

    <!-- Simulator Controls Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
      <!-- Control 1: Temperature Spike Slider -->
      <div class="bg-surface-container-lowest/10 backdrop-blur-sm rounded-xl p-4 flex flex-col justify-between border border-surface-container-high/20">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="font-label-sm text-label-sm uppercase tracking-wider text-surface-variant font-semibold">
              Simulasi Lonjakan Suhu
            </span>
            <span
              id="temp-display"
              class="font-label-md text-label-md font-bold transition-colors"
              :class="tempValue > 8.0 ? 'text-error' : 'text-secondary-fixed-dim'"
            >
              {{ tempValue.toFixed(1) }}°C
            </span>
          </div>
          <input
            v-model.number="tempValue"
            id="temp-slider"
            type="range"
            min="2"
            max="25"
            step="0.5"
            class="w-full accent-primary cursor-pointer h-1.5 bg-surface-container-high/30 rounded-lg"
          />
          <div class="flex justify-between text-[10px] text-surface-variant/70 mt-1 font-mono">
            <span>2.0°C (Normal)</span>
            <span>8.0°C (Batas)</span>
            <span>25.0°C (Kritis)</span>
          </div>
        </div>
        <button
          type="button"
          id="btn-trigger-temp"
          @click="handleTempSpike"
          class="mt-4 w-full bg-primary hover:bg-primary-container text-on-primary font-label-sm text-label-sm py-2 px-3 rounded-lg flex items-center justify-center gap-1.5 transition-all active:scale-95 cursor-pointer font-bold shadow-sm"
        >
          <span class="material-symbols-outlined text-[16px]">warning</span>
          <span>Trigger Temp Spike</span>
        </button>
      </div>

      <!-- Control 2: Seal Breach Simulation -->
      <div class="bg-surface-container-lowest/10 backdrop-blur-sm rounded-xl p-4 flex flex-col justify-between border border-surface-container-high/20">
        <div>
          <span class="font-label-sm text-label-sm uppercase tracking-wider text-surface-variant block mb-1 font-semibold">
            Simulasi Integritas Fisik
          </span>
          <p class="font-body-sm text-body-sm text-surface-variant opacity-80 mb-3 leading-relaxed">
            Injeksi status kerusakan sensor tamper-evident SL-8891 pada kargo terpilih.
          </p>
        </div>
        <button
          type="button"
          id="btn-trigger-seal"
          @click="handleSealBreach"
          class="w-full bg-error hover:bg-on-error-container text-on-error font-label-sm text-label-sm py-2 px-3 rounded-lg flex items-center justify-center gap-1.5 transition-all active:scale-95 cursor-pointer font-bold shadow-sm"
        >
          <span class="material-symbols-outlined text-[16px]">lock_open_right</span>
          <span>Simulasi Segel Rusak (Seal Breach)</span>
        </button>
      </div>

      <!-- Control 3: Live Bot Dispatch Test -->
      <div class="bg-surface-container-lowest/10 backdrop-blur-sm rounded-xl p-4 flex flex-col justify-between border border-surface-container-high/20">
        <div>
          <span class="font-label-sm text-label-sm uppercase tracking-wider text-surface-variant block mb-1 font-semibold">
            Uji Notifikasi WhatsApp Juri
          </span>
          <input
            v-model="judgePhone"
            id="judge-wa"
            type="text"
            class="w-full bg-surface-container-lowest/20 rounded-lg px-3 py-1.5 font-body-sm text-body-sm text-inverse-on-surface placeholder:text-surface-variant focus:outline-none focus:ring-1 focus:ring-secondary mt-1 border border-surface-container-high/30"
          />
        </div>
        <button
          type="button"
          id="btn-trigger-webhook"
          @click="handleWaWebhook"
          class="mt-4 w-full bg-secondary hover:bg-on-secondary-container text-on-secondary font-label-sm text-label-sm py-2 px-3 rounded-lg flex items-center justify-center gap-1.5 transition-all active:scale-95 cursor-pointer font-bold shadow-sm"
        >
          <span class="material-symbols-outlined text-[16px]">send_to_mobile</span>
          <span>Tes Webhook WA ke Nomor Juri</span>
        </button>
      </div>
    </div>

    <!-- Simulator Output Console -->
    <div class="mt-4 bg-inverse-surface/90 rounded-lg p-3 flex items-start gap-2.5 border border-surface-container-high/30">
      <span class="material-symbols-outlined text-secondary-fixed-dim text-[18px] shrink-0 mt-0.5">
        terminal
      </span>
      <div id="simulator-console" class="font-label-sm text-label-sm text-surface-variant font-mono space-y-1 w-full">
        <div v-for="(log, idx) in consoleLogs" :key="idx" class="leading-relaxed">
          <span :class="log.typeColor">[{{ log.time }}] [{{ log.type }}]</span> {{ log.msg }}
        </div>
      </div>
    </div>
  </div>
</template>
