<script setup lang="ts">
import { ref } from 'vue'

interface Props {
  awb: string
  isHeld?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isHeld: true,
})

const emit = defineEmits<{
  (e: 'submit-issue', payload: { category: string; description: string }): void
}>()

const selectedCategory = ref<string>('seal')
const description = ref(
  'Saat tiba dan akan diserahterimakan di instalasi farmasi RS Medika Sehat, ditemukan segel plastik tamper-evident nomor SL-8891 robek sebagian dan kait boks renggang saat proses penurunan dari reefer van.'
)

const categories = [
  {
    id: 'seal',
    title: 'Segel Rusak / Terbuka',
    code: 'seal',
    icon: 'lock_open',
    color: 'text-error',
  },
  {
    id: 'temperature',
    title: 'Suhu Melebihi Batas (>8.0°C)',
    code: 'temperature',
    icon: 'device_thermostat',
    color: 'text-tertiary',
  },
  {
    id: 'packaging',
    title: 'Kemasan Bocor / Rusak Fisik',
    code: 'packaging',
    icon: 'inventory_2',
    color: 'text-on-surface-variant',
  },
  {
    id: 'recipient_unavailable',
    title: 'Penerima / APJ Tidak di Tempat',
    code: 'recipient_unavailable',
    icon: 'person_off',
    color: 'text-on-surface-variant',
  },
]

function handleSubmit() {
  emit('submit-issue', {
    category: selectedCategory.value,
    description: description.value.trim(),
  })
}
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm flex flex-col gap-5 border border-surface-container/60">
    <!-- Header -->
    <div class="flex items-start justify-between pb-4 border-b border-surface-container/60">
      <div>
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[22px]">report_problem</span>
          <h2 class="font-headline-md text-headline-md text-on-surface font-bold">
            Form Input Laporan Kendala Lapangan
          </h2>
        </div>
        <p class="font-label-md text-label-md text-on-surface-variant mt-0.5">
          Tabel basis data: <span class="font-mono">issues</span> • Terkait AWB:
          <span class="font-bold text-on-surface">{{ awb }}</span>
        </p>
      </div>
      <span class="px-2.5 py-1 rounded bg-surface-container text-on-surface-variant font-label-sm text-label-sm uppercase font-mono font-bold shadow-2xs">
        FR-04
      </span>
    </div>

    <!-- Issue Category Selector (issues.type) -->
    <div class="space-y-2.5">
      <label class="font-label-lg text-label-lg text-on-surface flex items-center justify-between font-semibold">
        <span>Kategori Kendala (<code class="font-mono text-label-sm">issues.type</code>)</span>
        <span class="text-error font-label-sm text-label-sm">*Wajib dipilih</span>
      </label>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5" id="issueTypeGroup">
        <button
          v-for="cat in categories"
          :key="cat.id"
          type="button"
          @click="selectedCategory = cat.id"
          class="group text-left p-3.5 rounded-xl transition-all flex items-start justify-between cursor-pointer border"
          :class="selectedCategory === cat.id
            ? 'bg-error-container text-on-error-container ring-2 ring-error shadow-sm border-transparent'
            : 'bg-surface-container-low hover:bg-surface-container text-on-surface border-surface-container/40'"
        >
          <div class="space-y-1">
            <div
              class="flex items-center gap-1.5 font-label-md text-label-md font-bold"
              :class="selectedCategory === cat.id ? 'text-on-error-container' : 'text-on-surface'"
            >
              <span class="material-symbols-outlined text-[18px]" :class="selectedCategory === cat.id ? 'text-error' : cat.color">
                {{ cat.icon }}
              </span>
              <span>{{ cat.title }}</span>
            </div>
            <div
              class="font-label-sm text-label-sm"
              :class="selectedCategory === cat.id ? 'opacity-80' : 'text-on-surface-variant'"
            >
              Kode: <span class="font-mono">{{ cat.code }}</span>
            </div>
          </div>

          <span
            v-if="selectedCategory === cat.id"
            class="material-symbols-outlined text-error text-[20px] shrink-0"
            style="font-variation-settings: 'FILL' 1;"
          >
            check_circle
          </span>
          <span
            v-else
            class="w-4 h-4 rounded-full bg-surface-container-high group-hover:bg-surface-container-highest mt-1 shrink-0 transition-colors"
          ></span>
        </button>
      </div>
    </div>

    <!-- Issue Description Textarea -->
    <div class="space-y-2">
      <label class="font-label-lg text-label-lg text-on-surface flex items-center justify-between font-semibold">
        <span>Deskripsi Kronologi Kejadian (<code class="font-mono text-label-sm">issues.description</code>)</span>
        <span class="font-label-sm text-label-sm text-on-surface-variant font-mono">
          {{ description.length }} / 500 karakter
        </span>
      </label>

      <div class="bg-surface-container-low rounded-xl p-3.5 focus-within:bg-surface-container-lowest focus-within:ring-2 focus-within:ring-primary-container transition-all border border-surface-container/60">
        <textarea
          v-model="description"
          maxlength="500"
          rows="4"
          placeholder="Jelaskan detail kendala lapangan yang terjadi..."
          class="w-full bg-transparent font-body-md text-body-md text-on-surface focus:outline-none resize-none leading-relaxed placeholder:text-on-surface-variant"
        ></textarea>
      </div>
      <p class="font-body-sm text-body-sm text-on-surface-variant">
        Cantumkan lokasi fisik, identitas saksi di tempat (jika ada), serta penanda segel unik.
      </p>
    </div>

    <!-- Evidence Photo Dropzone & Preview -->
    <div class="space-y-2.5">
      <label class="font-label-lg text-label-lg text-on-surface flex items-center justify-between font-semibold">
        <span>Bukti Foto Lapangan &amp; Tag Lokasi Otomatis</span>
        <span class="font-label-sm text-label-sm text-secondary font-semibold flex items-center gap-1">
          <span class="material-symbols-outlined text-[16px]">verified</span>
          <span>GPS Terverifikasi</span>
        </span>
      </label>

      <!-- Uploaded File Card -->
      <div class="bg-surface-container-low rounded-xl p-3.5 flex items-center gap-4 border border-surface-container/40">
        <div class="w-20 h-20 rounded-lg overflow-hidden shrink-0 relative bg-surface-container-highest shadow-2xs">
          <img
            class="w-full h-full object-cover"
            alt="Foto Bukti Kerusakan Segel SL-8891"
            src="https://lh3.googleusercontent.com/aida-public/AB6AXuAM4NnuboCZQugl3-VrgxXMTnwcFuSPgOtFJfW6y91Xo8OrKEjb82RY9gvgMmQaKLRKzqp2zSbvVYc_2neOjw35Pe60KO5vzd3fRBMC_7gY53Rf9_Mh0cAaGby_k-bBnlMcEg-aXuc17qFJCIaf8KpYWGarko7jzSGtzfe6pH8Tnz3WRBKtAEdlyQEcvjMAWnfPl63CCBIuNYL7zV9LU4c4QC64fsqw52GBOwIu9j3azj8mm8FjHYtWLw"
            loading="lazy"
          />
          <div class="absolute bottom-1 right-1 bg-inverse-surface/80 rounded px-1 text-[9px] text-inverse-on-surface font-mono font-bold">
            JPG
          </div>
        </div>

        <div class="flex-1 min-w-0 space-y-1">
          <div class="flex items-center justify-between gap-2">
            <span class="font-label-md text-label-md text-on-surface font-semibold truncate">
              foto_segel_rusak_8891.jpg
            </span>
            <span class="material-symbols-outlined text-secondary text-[20px] shrink-0">check_circle</span>
          </div>
          <p class="font-body-sm text-body-sm text-on-surface-variant truncate">
            Watermark GPS Tol Cawang / Salemba, 14 Okt 2026 15:15 WIB
          </p>
          <div class="flex items-center gap-2">
            <span class="px-2 py-0.5 rounded bg-surface-container font-mono font-label-sm text-label-sm text-on-surface-variant">
              3.4 MB
            </span>
            <span class="px-2 py-0.5 rounded bg-secondary-fixed text-on-secondary-fixed font-label-sm text-label-sm font-semibold">
              Exif Metadata OK
            </span>
          </div>
        </div>
      </div>

      <!-- Secondary Quick Action Dropzone -->
      <div
        class="rounded-xl bg-surface-container-low/60 hover:bg-surface-container-low p-3 flex items-center justify-center gap-2 cursor-pointer text-on-surface-variant transition-colors border border-dashed border-surface-container-highest"
      >
        <span class="material-symbols-outlined text-[20px] text-primary">add_a_photo</span>
        <span class="font-label-md text-label-md font-semibold">Ambil / Tambah Foto Tambahan (Maks. 3)</span>
      </div>
    </div>

    <!-- Submit Button -->
    <button
      type="button"
      @click="handleSubmit"
      class="w-full py-4 px-6 rounded-xl bg-error hover:bg-on-error-container text-on-error font-headline-sm text-headline-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 mt-2 cursor-pointer active:scale-[0.98]"
    >
      <span class="material-symbols-outlined text-[22px]">lock_clock</span>
      <span>Kirim Laporan Kendala &amp; Tahan Pengiriman (status → HELD)</span>
    </button>
  </div>
</template>
