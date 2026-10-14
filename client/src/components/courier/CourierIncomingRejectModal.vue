<script setup lang="ts">
import { ref } from 'vue'

interface Props {
  isOpen: boolean
  awb: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'submit', payload: { category: string; notes: string }): void
}>()

const selectedCategory = ref('equipment_failure')
const detailedNotes = ref('')
const errorMessage = ref('')

function handleClose() {
  errorMessage.value = ''
  emit('close')
}

function handleSubmit() {
  if (!detailedNotes.value.trim()) {
    errorMessage.value = 'Harap berikan catatan keterangan penolakan untuk audit CPOB.'
    return
  }
  errorMessage.value = ''
  emit('submit', {
    category: selectedCategory.value,
    notes: detailedNotes.value.trim(),
  })
}
</script>

<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 bg-inverse-surface/60 backdrop-blur-sm flex items-center justify-center p-4 transition-all"
    @click.self="handleClose"
  >
    <div
      class="bg-surface-container-lowest rounded-2xl max-w-lg w-full p-6 md:p-8 shadow-xl space-y-6 border border-surface-container/60 animate-in fade-in zoom-in-95 duration-200"
    >
      <!-- Modal Header -->
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-error-container flex items-center justify-center text-error shrink-0">
            <span class="material-symbols-outlined text-[22px]">warning</span>
          </div>
          <div>
            <h4 class="font-headline-md text-headline-md text-on-surface font-bold">
              Penolakan Tugas Kritis
            </h4>
            <span class="font-label-sm text-label-sm font-mono text-on-surface-variant">
              ID: {{ awb }} • Cold-Chain Exemption
            </span>
          </div>
        </div>

        <button
          type="button"
          @click="handleClose"
          class="text-on-surface-variant hover:text-on-surface p-1 rounded-full hover:bg-surface-container transition-colors cursor-pointer"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="space-y-4">
        <p class="font-body-md text-body-md text-on-surface-variant">
          Penolakan tugas vaksin memerlukan verifikasi alasan operasional untuk dialihkan secara otomatis ke Satria cadangan di Hub Cawang.
        </p>

        <!-- Category Select -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-semibold">
            Pilih Kategori Alasan Penolakan:
          </label>
          <select
            v-model="selectedCategory"
            class="w-full h-11 px-3 bg-surface-container-low rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary-container border border-surface-container/60 cursor-pointer"
          >
            <option value="equipment_failure">Cooler Box / IoT Logger Tidak Berfungsi Saat Cek Fisik</option>
            <option value="vehicle_issue">Kendaraan Mengalami Kendala Teknis / Mesin / Kelistrikan</option>
            <option value="shift_overtime">Mendekati Batas Maksimal Jam Operasional CPOB</option>
            <option value="capacity_limit">Kapasitas Carrier Bag Motor Penuh / Melebihi Beban Aman</option>
            <option value="other">Lainnya (Tuliskan Keterangan)</option>
          </select>
        </div>

        <!-- Notes Textarea -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-semibold">
            Keterangan Detail (Wajib):
          </label>
          <textarea
            v-model="detailedNotes"
            rows="3"
            placeholder="Contoh: Baterai sensor logger Sensirion di bawah 15% atau segel tamper terindikasi retak..."
            class="w-full p-3 bg-surface-container-low rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary-container placeholder:text-on-surface-variant border border-surface-container/60"
          ></textarea>
          <span v-if="errorMessage" class="text-xs text-error font-semibold mt-1 block">
            {{ errorMessage }}
          </span>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="flex items-center justify-end gap-3 pt-2">
        <button
          type="button"
          @click="handleClose"
          class="px-5 py-2.5 rounded-lg font-label-lg text-label-lg text-on-surface hover:bg-surface-container transition-colors cursor-pointer font-medium"
        >
          Batal
        </button>

        <button
          type="button"
          @click="handleSubmit"
          class="px-6 py-2.5 rounded-lg bg-error text-on-error font-label-lg text-label-lg font-semibold hover:bg-on-error-container transition-all shadow-xs cursor-pointer active:scale-95"
        >
          Kirim Penolakan Tugas
        </button>
      </div>
    </div>
  </div>
</template>
