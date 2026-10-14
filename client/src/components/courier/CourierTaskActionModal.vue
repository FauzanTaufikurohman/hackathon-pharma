<script setup lang="ts">
import { ref } from 'vue'
import type { CourierTaskItem } from '../../types/shipment'

interface Props {
  task: CourierTaskItem | null
  isOpen: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'accept', task: CourierTaskItem): void
  (e: 'reject', payload: { task: CourierTaskItem; reason: string }): void
}>()

const showRejectInput = ref(false)
const rejectReason = ref('')

function handleRejectClick() {
  if (!showRejectInput.value) {
    showRejectInput.value = true
    return
  }
  if (props.task) {
    emit('reject', {
      task: props.task,
      reason: rejectReason.value.trim() || 'Kendala Motor Box / Kapasitas Pendingin Penuh',
    })
    showRejectInput.value = false
    rejectReason.value = ''
  }
}

function handleAcceptClick() {
  if (props.task) {
    emit('accept', props.task)
  }
}
</script>

<template>
  <div
    v-if="isOpen && task"
    class="fixed inset-0 z-50 bg-inverse-surface/50 backdrop-blur-sm flex items-center justify-center p-4 transition-all"
    id="task-modal"
  >
    <div class="bg-surface-container-lowest rounded-3xl max-w-lg w-full p-6 shadow-xl flex flex-col gap-5 relative border border-surface-container animate-in fade-in zoom-in-95 duration-150">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-full bg-primary-fixed flex items-center justify-center text-primary-container shadow-2xs">
            <span class="material-symbols-outlined text-[20px]">assignment_add</span>
          </div>
          <div>
            <h3 class="font-headline-sm font-bold text-on-surface" id="modal-task-title">
              Pilih Penugasan {{ task.awb }}
            </h3>
            <p class="font-body-sm text-on-surface-variant" id="modal-task-sub">
              Kargo Farmasi: {{ task.awb }}
            </p>
          </div>
        </div>
        <button
          type="button"
          class="p-1 rounded-full text-outline hover:text-on-surface hover:bg-surface-container-low transition-colors cursor-pointer"
          @click="emit('close')"
        >
          <span class="material-symbols-outlined text-[24px]">close</span>
        </button>
      </div>

      <!-- Details Card -->
      <div class="bg-surface-container-low p-4 rounded-2xl flex flex-col gap-2.5 border border-surface-container/60 shadow-2xs">
        <div class="flex justify-between items-center font-body-sm">
          <span class="text-on-surface-variant font-medium">Tujuan Fasilitas:</span>
          <span class="font-semibold text-on-surface" id="modal-task-dest">{{ task.destination_name }}</span>
        </div>
        <div class="flex justify-between items-center font-body-sm">
          <span class="text-on-surface-variant font-medium">Komoditas:</span>
          <span class="font-semibold text-on-surface text-right" id="modal-task-cargo">{{ task.item_title }}</span>
        </div>
        <div class="flex justify-between items-center font-body-sm">
          <span class="text-on-surface-variant font-medium">Integritas Suhu Awal:</span>
          <span class="font-bold text-secondary flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">check_circle</span>
            4.2°C (Aman CDOB)
          </span>
        </div>
      </div>

      <!-- Legal CDOB Mandate Note -->
      <p class="font-body-sm text-on-surface-variant">
        Dengan menekan tombol <strong class="text-on-surface">Terima Tugas</strong>, Anda bertanggung jawab penuh atas rantai dingin dari Hub keberangkatan sampai verifikasi fisik Apoteker Penanggung Jawab di lokasi tujuan.
      </p>

      <!-- Rejection Reason Input (shown when clicked once) -->
      <div v-if="showRejectInput" class="flex flex-col gap-1.5 p-3 rounded-xl bg-error-container/30 border border-error-container">
        <label class="font-label-sm text-error font-bold">Alasan Penolakan Tugas:</label>
        <input
          v-model="rejectReason"
          type="text"
          placeholder="Contoh: Kendala Boks Reefer, Kapasitas Penuh..."
          class="w-full px-3 py-1.5 rounded-lg bg-surface-container-lowest text-on-surface font-body-sm border border-outline-variant focus:outline-none focus:ring-1 focus:ring-error"
        />
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-3 pt-2">
        <button
          type="button"
          class="flex-1 py-2.5 px-4 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md font-semibold transition-colors cursor-pointer border border-surface-container-high"
          @click="handleRejectClick"
        >
          {{ showRejectInput ? 'Konfirmasi Tolak' : 'Tolak Tugas' }}
        </button>
        <button
          type="button"
          class="flex-1 py-2.5 px-4 rounded-xl bg-primary-container hover:bg-primary text-on-primary font-label-md font-bold shadow-sm transition-all cursor-pointer active:scale-[0.98]"
          @click="handleAcceptClick"
        >
          Terima Tugas Sekarang
        </button>
      </div>
    </div>
  </div>
</template>
