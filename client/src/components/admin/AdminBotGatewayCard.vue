<script setup lang="ts">
import type { AdminBotGatewayLog } from '../../types/shipment'

defineProps<{
  logs: AdminBotGatewayLog[]
}>()

const emit = defineEmits<{
  (e: 'view-all'): void
}>()

const channelIcon = (channel: string) => {
  switch (channel.toLowerCase()) {
    case 'whatsapp':
      return { icon: 'chat', color: 'text-secondary' }
    case 'telegram':
      return { icon: 'send', color: 'text-primary' }
    case 'webhook':
      return { icon: 'webhook', color: 'text-on-surface-variant' }
    default:
      return { icon: 'notifications', color: 'text-secondary' }
  }
}
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-5 shadow-sm border border-surface-container flex flex-col gap-4">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-surface-container-low">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-primary shrink-0">
          <span class="material-symbols-outlined text-[20px]">cell_tower</span>
        </div>
        <div>
          <h3 class="font-headline-sm text-headline-sm text-on-surface">Bot Gateway Log</h3>
          <p class="font-body-sm text-body-sm text-on-surface-variant">Dispatch Notifikasi Otomatis</p>
        </div>
      </div>
      <span class="px-2.5 py-1 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-label-sm font-bold">
        {{ logs.length * 24 + 2 }} Terkirim
      </span>
    </div>

    <!-- Notification List -->
    <div class="flex flex-col gap-3">
      <div
        v-for="log in logs"
        :key="log.id"
        class="p-3.5 rounded-xl bg-surface-container-low hover:bg-surface-container transition-colors border border-surface-container/60"
      >
        <div class="flex items-center justify-between gap-2 mb-1.5">
          <div class="flex items-center gap-1.5 font-label-sm text-label-sm text-on-surface">
            <span
              class="material-symbols-outlined text-[16px]"
              :class="channelIcon(log.channel).color"
            >
              {{ channelIcon(log.channel).icon }}
            </span>
            <span class="font-bold">{{ log.recipient }}</span>
            <span class="text-on-surface-variant font-normal">{{ log.channel_label }}</span>
          </div>
          <span class="font-label-sm text-label-sm text-on-surface-variant">{{ log.time }}</span>
        </div>

        <p class="font-label-md text-label-md text-on-surface font-semibold mb-2 leading-snug">
          {{ log.title }}
        </p>

        <div class="flex items-center justify-between">
          <span class="font-body-sm text-[11px] text-on-surface-variant">
            {{ log.payload_label }}: <strong class="text-on-surface font-medium">{{ log.payload_val }}</strong>
          </span>
          <span
            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-label-sm text-label-sm"
            :class="log.status_badge_class"
          >
            <span v-if="log.status_icon" class="material-symbols-outlined text-[13px]">
              {{ log.status_icon }}
            </span>
            {{ log.status_label }}
          </span>
        </div>
      </div>
    </div>

    <!-- Bottom Action Button -->
    <button
      type="button"
      @click="emit('view-all')"
      class="w-full mt-1 py-2 bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface rounded-lg font-label-md text-label-md transition-colors flex items-center justify-center gap-1 border border-surface-container cursor-pointer"
    >
      <span>Lihat Riwayat Gateway Lengkap</span>
      <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
    </button>
  </div>
</template>
