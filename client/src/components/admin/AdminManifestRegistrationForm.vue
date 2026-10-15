<script setup lang="ts">
import { ref } from 'vue'

const emit = defineEmits<{
  (e: 'submit-manifest', payload: any): void
  (e: 'copy-awb', awb: string): void
}>()

const form = ref({
  awb: 'PHA-260105',
  sender_name: 'PT BioFarma Persero',
  origin_city: 'Bandung Hub Staging',
  recipient_name: 'RS Medika Sehat Jakarta',
  recipient_phone: '+62 812-9842-1100 (Apt. Siti)',
  address: 'Jl. Raya Salemba No. 4, Instalasi Farmasi Gd. B Lt. 1, Jakarta Pusat',
  item_summary: 'Vaksin Meningococcal Konjugat ACYW',
  koli: '2 Koli',
  temperature_profile: 'chilled' as 'chilled' | 'ambient',
  min_temp: 2.0,
  max_temp: 8.0,
  handling_instructions: 'Wajib insulated active cooler box, dilarang dibalik, cek segel SL-8891 sebelum rilis.',
  inspector_id: 'Hendra Wijaya (CHK-012 • Hub Cawang)',
  scheduled_at: '14 Okt 2026, 15:30 WIB',
})

const setProfile = (profile: 'chilled' | 'ambient') => {
  form.value.temperature_profile = profile
  if (profile === 'chilled') {
    form.value.min_temp = 2.0
    form.value.max_temp = 8.0
  } else {
    form.value.min_temp = 15.0
    form.value.max_temp = 25.0
  }
}

const handleSubmit = () => {
  emit('submit-manifest', { ...form.value })
}
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm border border-surface-container/60">
    <!-- Form Ribbon Header -->
    <div class="flex items-center justify-between pb-space-md mb-space-md bg-surface-container-low -mx-space-lg -mt-space-lg p-space-lg rounded-t-xl border-b border-surface-container/40">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-lg bg-primary text-on-primary flex items-center justify-center shadow-xs">
          <span class="material-symbols-outlined text-xl">post_add</span>
        </div>
        <div>
          <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">
            Registrasi Manifest Kargo Baru
          </h2>
          <p class="font-body-sm text-body-sm text-on-surface-variant">
            Schema tabel shipments • Cold-chain verified
          </p>
        </div>
      </div>
      <span class="bg-surface-container-lowest text-primary font-label-sm text-label-sm px-2.5 py-1 rounded-full uppercase tracking-wider font-bold shadow-sm border border-primary/20">
        FR-01 BPOM Verified
      </span>
    </div>

    <!-- Form Content -->
    <form class="space-y-4" @submit.prevent="handleSubmit">
      <!-- Nomor Resi Auto -->
      <div>
        <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-bold">
          Nomor Resi (AWB / Tracking Number)
        </label>
        <div class="relative flex items-center">
          <input
            class="w-full bg-surface-container-low font-headline-sm text-headline-sm text-primary px-3.5 py-2 rounded-lg cursor-not-allowed focus:outline-none font-bold font-mono border border-surface-container"
            readonly
            type="text"
            :value="form.awb"
          />
          <button
            type="button"
            @click="emit('copy-awb', form.awb)"
            class="absolute right-3 text-on-surface-variant hover:text-primary transition-colors cursor-pointer p-1"
            title="Salin No Resi"
          >
            <span class="material-symbols-outlined text-lg">content_copy</span>
          </button>
        </div>
        <span class="font-body-sm text-body-sm text-on-surface-variant mt-1 block">
          Otomatis digenerate sistem batch per-hari
        </span>
      </div>

      <!-- Pengirim & Asal -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-semibold">
            Pengirim (sender_name)
          </label>
          <input
            v-model="form.sender_name"
            class="w-full bg-surface-container-low focus:bg-surface-container-lowest text-on-surface font-body-md text-body-md px-3 py-2 rounded-lg focus:outline-none transition-colors border border-surface-container/60 focus:border-primary"
            type="text"
          />
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-semibold">
            Kota Asal (origin_city)
          </label>
          <input
            v-model="form.origin_city"
            class="w-full bg-surface-container-low focus:bg-surface-container-lowest text-on-surface font-body-md text-body-md px-3 py-2 rounded-lg focus:outline-none transition-colors border border-surface-container/60 focus:border-primary"
            type="text"
          />
        </div>
      </div>

      <!-- Penerima & Kontak -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-semibold">
            Penerima (recipient_name)
          </label>
          <input
            v-model="form.recipient_name"
            class="w-full bg-surface-container-low focus:bg-surface-container-lowest text-on-surface font-body-md text-body-md px-3 py-2 rounded-lg focus:outline-none transition-colors border border-surface-container/60 focus:border-primary"
            type="text"
          />
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-semibold">
            Kontak PIC (recipient_phone)
          </label>
          <input
            v-model="form.recipient_phone"
            class="w-full bg-surface-container-low focus:bg-surface-container-lowest text-on-surface font-body-md text-body-md px-3 py-2 rounded-lg focus:outline-none transition-colors border border-surface-container/60 focus:border-primary"
            type="text"
          />
        </div>
      </div>

      <!-- Alamat Fasilitas Tujuan -->
      <div>
        <label class="block font-label-md text-label-md text-on-surface mb-1 font-semibold">
          Alamat Fasilitas Farmasi Tujuan (address)
        </label>
        <textarea
          v-model="form.address"
          class="w-full bg-surface-container-low focus:bg-surface-container-lowest text-on-surface font-body-md text-body-md px-3 py-2 rounded-lg focus:outline-none transition-colors resize-none border border-surface-container/60 focus:border-primary"
          rows="2"
        ></textarea>
      </div>

      <!-- Ringkasan Muatan & Jumlah Koli -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="sm:col-span-2">
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-semibold">
            Ringkasan Muatan (item_summary)
          </label>
          <input
            v-model="form.item_summary"
            class="w-full bg-surface-container-low focus:bg-surface-container-lowest text-on-surface font-body-md text-body-md px-3 py-2 rounded-lg focus:outline-none transition-colors border border-surface-container/60 focus:border-primary"
            type="text"
          />
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-semibold">
            Jumlah Koli
          </label>
          <input
            v-model="form.koli"
            class="w-full bg-surface-container-low focus:bg-surface-container-lowest text-on-surface font-body-md text-body-md px-3 py-2 rounded-lg focus:outline-none transition-colors border border-surface-container/60 focus:border-primary"
            type="text"
          />
        </div>
      </div>

      <!-- Profil Suhu & Range -->
      <div>
        <label class="block font-label-md text-label-md text-on-surface mb-2 font-bold">
          Profil Suhu Farmasi (temperature_profile)
        </label>
        <div class="grid grid-cols-2 gap-2 mb-3">
          <button
            type="button"
            @click="setProfile('chilled')"
            class="px-3 py-2.5 rounded-lg font-label-md text-label-md flex items-center justify-center gap-1.5 transition-all text-left cursor-pointer font-bold active:scale-98"
            :class="form.temperature_profile === 'chilled'
              ? 'bg-primary text-on-primary shadow-sm'
              : 'bg-surface-container-low hover:bg-surface-container text-on-surface-variant border border-surface-container'"
          >
            <span class="material-symbols-outlined text-base">ac_unit</span>
            Controlled Chilled (2°C - 8°C)
          </button>

          <button
            type="button"
            @click="setProfile('ambient')"
            class="px-3 py-2.5 rounded-lg font-label-md text-label-md flex items-center justify-center gap-1.5 transition-all cursor-pointer font-bold active:scale-98"
            :class="form.temperature_profile === 'ambient'
              ? 'bg-primary text-on-primary shadow-sm'
              : 'bg-surface-container-low hover:bg-surface-container text-on-surface-variant border border-surface-container'"
          >
            <span class="material-symbols-outlined text-base">thermostat</span>
            Ambient (15°C - 25°C)
          </button>
        </div>

        <div class="grid grid-cols-2 gap-3 bg-surface-container-low p-3 rounded-lg border border-surface-container">
          <div>
            <span class="font-label-sm text-label-sm text-on-surface-variant block mb-1 font-medium">Min Boundary Suhu</span>
            <div class="flex items-center gap-1">
              <input
                v-model="form.min_temp"
                class="w-20 bg-surface-container-lowest font-headline-sm text-headline-sm text-on-surface px-2.5 py-1 rounded text-center focus:outline-none border border-surface-container font-bold"
                step="0.1"
                type="number"
              />
              <span class="font-body-md text-body-md text-on-surface-variant font-bold">°C</span>
            </div>
          </div>

          <div>
            <span class="font-label-sm text-label-sm text-on-surface-variant block mb-1 font-medium">Max Boundary Suhu</span>
            <div class="flex items-center gap-1">
              <input
                v-model="form.max_temp"
                class="w-20 bg-surface-container-lowest font-headline-sm text-headline-sm text-on-surface px-2.5 py-1 rounded text-center focus:outline-none border border-surface-container font-bold"
                step="0.1"
                type="number"
              />
              <span class="font-body-md text-body-md text-on-surface-variant font-bold">°C</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Instruksi Khusus CPOB -->
      <div>
        <label class="block font-label-md text-label-md text-on-surface mb-1 font-semibold">
          Instruksi Khusus CPOB (handling_instructions)
        </label>
        <textarea
          v-model="form.handling_instructions"
          class="w-full bg-surface-container-low focus:bg-surface-container-lowest text-on-surface font-body-sm text-body-sm px-3 py-2 rounded-lg focus:outline-none transition-colors resize-none border border-surface-container/60 focus:border-primary"
          rows="2"
        ></textarea>
      </div>

      <!-- Petugas Checker QC & Jadwal Tiba -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-semibold">
            Petugas Checker QC (inspector_id)
          </label>
          <select
            v-model="form.inspector_id"
            class="w-full bg-surface-container-low text-on-surface font-body-md text-body-md px-3 py-2 rounded-lg focus:outline-none border border-surface-container/60 cursor-pointer font-medium"
          >
            <option>Hendra Wijaya (CHK-012 • Hub Cawang)</option>
            <option>Bambang Haryanto (CHK-009 • Hub Cengkareng)</option>
            <option>Rina Melati (CHK-015 • Hub Halim)</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-semibold">
            Target Jadwal Tiba (scheduled_at)
          </label>
          <input
            v-model="form.scheduled_at"
            class="w-full bg-surface-container-low focus:bg-surface-container-lowest text-on-surface font-body-md text-body-md px-3 py-2 rounded-lg focus:outline-none transition-colors border border-surface-container/60 focus:border-primary"
            type="text"
          />
        </div>
      </div>

      <!-- Primary Action -->
      <div class="pt-2">
        <button
          type="submit"
          class="w-full bg-primary hover:bg-on-primary-fixed-variant text-on-primary font-headline-sm text-headline-sm py-3.5 px-4 rounded-xl shadow-md transition-all flex items-center justify-center gap-2 group cursor-pointer active:scale-98 font-bold"
        >
          <span class="material-symbols-outlined group-hover:rotate-12 transition-transform">send</span>
          <span>Buat Pengiriman &amp; Kirim Tugas ke Checker (status → inspection)</span>
        </button>
        <p class="font-body-sm text-body-sm text-on-surface-variant text-center mt-2.5">
          Data manifest akan langsung sinkron ke antrean Checker Hub dan tercatat ke audit trail ISO 9001:2015.
        </p>
      </div>
    </form>
  </div>
</template>
