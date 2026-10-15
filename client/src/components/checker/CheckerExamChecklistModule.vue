<script setup lang="ts">
interface Props {
  packagingPassed: boolean
  sealPassed: boolean
  labelPassed: boolean
  temperature: number | string
  inspectionNote: string
  minimum?: number
  maximum?: number
}

withDefaults(defineProps<Props>(), { minimum: 2, maximum: 8 })

const emit = defineEmits<{
  (e: 'update:packagingPassed', val: boolean): void
  (e: 'update:sealPassed', val: boolean): void
  (e: 'update:labelPassed', val: boolean): void
  (e: 'update:temperature', val: number | string): void
  (e: 'update:inspectionNote', val: string): void
}>()
</script>

<template>
  <div
    class="bg-surface-container-lowest rounded-xl p-6 shadow-sm flex flex-col gap-6 border border-surface-container/60"
  >
    <!-- Section Title & Standards Tag -->
    <div
      class="flex items-center justify-between pb-2 border-b border-surface-container/40"
    >
      <div>
        <span
          class="font-label-sm text-label-sm text-primary uppercase tracking-wider block font-bold"
        >
          Modul Verifikasi Fisik
        </span>
        <h2 class="font-headline-md text-headline-md text-on-surface font-bold">
          1. Daftar Pemeriksaan Fisik Wajib
        </h2>
      </div>
      <div
        class="flex items-center gap-1.5 bg-surface-container-low text-secondary font-label-sm text-label-sm px-3 py-1.5 rounded-full border border-surface-container font-bold shadow-2xs"
      >
        <span class="material-symbols-outlined text-sm">verified_user</span>
        <span>Standar CDOB BPOM RI</span>
      </div>
    </div>

    <!-- TOGGLE 1: Packaging (inspections.packaging) -->
    <div
      class="bg-surface p-4 rounded-xl flex flex-col gap-3 border border-surface-container/40"
    >
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
          <span
            class="w-6 h-6 rounded bg-primary-fixed text-primary font-label-md text-label-md flex items-center justify-center shrink-0 mt-0.5 font-bold shadow-2xs"
          >
            A
          </span>
          <div>
            <p
              class="font-headline-sm text-headline-sm text-on-surface font-semibold"
            >
              Kondisi Kemasan &amp; Insulated Box Utuh
            </p>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
              Boks luar tidak penyok, tidak ada rembesan air/es cair, insulasi
              vacuum panel rapat dan kedap udara.
            </p>
          </div>
        </div>
        <span
          class="material-symbols-outlined"
          :class="packagingPassed ? 'text-secondary' : 'text-error'"
        >
          {{ packagingPassed ? 'check_circle' : 'cancel' }}
        </span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
        <button
          type="button"
          @click="emit('update:packagingPassed', true)"
          class="font-label-lg text-label-lg px-4 py-3 rounded-lg flex items-center justify-center gap-2 transition-all cursor-pointer font-bold shadow-xs active:scale-98"
          :class="
            packagingPassed
              ? 'bg-secondary-container text-on-secondary-container shadow-sm'
              : 'bg-surface-container text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high'
          "
        >
          <span class="material-symbols-outlined text-lg">check</span>
          <span>Sesuai / Utuh (True)</span>
        </button>

        <button
          type="button"
          @click="emit('update:packagingPassed', false)"
          class="font-label-lg text-label-lg px-4 py-3 rounded-lg flex items-center justify-center gap-2 transition-all cursor-pointer font-bold active:scale-98"
          :class="
            !packagingPassed
              ? 'bg-error-container text-error shadow-sm'
              : 'bg-surface-container text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high'
          "
        >
          <span class="material-symbols-outlined text-lg">close</span>
          <span>Rusak / Bocor (False)</span>
        </button>
      </div>
    </div>

    <!-- TOGGLE 2: Seal (inspections.seal) -->
    <div
      class="bg-surface p-4 rounded-xl flex flex-col gap-3 border border-surface-container/40"
    >
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
          <span
            class="w-6 h-6 rounded bg-primary-fixed text-primary font-label-md text-label-md flex items-center justify-center shrink-0 mt-0.5 font-bold shadow-2xs"
          >
            B
          </span>
          <div>
            <p
              class="font-headline-sm text-headline-sm text-on-surface font-semibold"
            >
              Keutuhan Segel Keamanan (Tamper-Evident Seal)
            </p>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
              Cocokkan segel dengan manifest kiriman ini dan periksa tanda
              kerusakan atau pelepasan paksa.
            </p>
          </div>
        </div>
        <span
          class="material-symbols-outlined"
          :class="sealPassed ? 'text-secondary' : 'text-error'"
        >
          {{ sealPassed ? 'lock' : 'gpp_bad' }}
        </span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
        <button
          type="button"
          @click="emit('update:sealPassed', true)"
          class="font-label-lg text-label-lg px-4 py-3 rounded-lg flex items-center justify-center gap-2 transition-all cursor-pointer font-bold shadow-xs active:scale-98"
          :class="
            sealPassed
              ? 'bg-secondary-container text-on-secondary-container shadow-sm'
              : 'bg-surface-container text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high'
          "
        >
          <span class="material-symbols-outlined text-lg">verified</span>
          <span>Segel Utuh (True)</span>
        </button>

        <button
          type="button"
          @click="emit('update:sealPassed', false)"
          class="font-label-lg text-label-lg px-4 py-3 rounded-lg flex items-center justify-center gap-2 transition-all cursor-pointer font-bold active:scale-98"
          :class="
            !sealPassed
              ? 'bg-error-container text-error shadow-sm'
              : 'bg-surface-container text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high'
          "
        >
          <span class="material-symbols-outlined text-lg">gpp_bad</span>
          <span>Segel Rusak (False)</span>
        </button>
      </div>
    </div>

    <!-- TOGGLE 3: Label (inspections.label) -->
    <div
      class="bg-surface p-4 rounded-xl flex flex-col gap-3 border border-surface-container/40"
    >
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
          <span
            class="w-6 h-6 rounded bg-primary-fixed text-primary font-label-md text-label-md flex items-center justify-center shrink-0 mt-0.5 font-bold shadow-2xs"
          >
            C
          </span>
          <div>
            <p
              class="font-headline-sm text-headline-sm text-on-surface font-semibold"
            >
              Kesesuaian Label, Koli &amp; Instruksi Suhu
            </p>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
              Cocokkan label, jumlah koli, dan instruksi suhu dengan manifest
              yang ditampilkan di atas.
            </p>
          </div>
        </div>
        <span
          class="material-symbols-outlined"
          :class="labelPassed ? 'text-secondary' : 'text-error'"
        >
          {{ labelPassed ? 'label' : 'priority_high' }}
        </span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
        <button
          type="button"
          @click="emit('update:labelPassed', true)"
          class="font-label-lg text-label-lg px-4 py-3 rounded-lg flex items-center justify-center gap-2 transition-all cursor-pointer font-bold shadow-xs active:scale-98"
          :class="
            labelPassed
              ? 'bg-secondary-container text-on-secondary-container shadow-sm'
              : 'bg-surface-container text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high'
          "
        >
          <span class="material-symbols-outlined text-lg">done_all</span>
          <span>Lengkap &amp; Jelas (True)</span>
        </button>

        <button
          type="button"
          @click="emit('update:labelPassed', false)"
          class="font-label-lg text-label-lg px-4 py-3 rounded-lg flex items-center justify-center gap-2 transition-all cursor-pointer font-bold active:scale-98"
          :class="
            !labelPassed
              ? 'bg-error-container text-error shadow-sm'
              : 'bg-surface-container text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high'
          "
        >
          <span class="material-symbols-outlined text-lg">priority_high</span>
          <span>Tidak Sesuai (False)</span>
        </button>
      </div>
    </div>

    <!-- INPUT SUHU FISIK (inspections.temperature) -->
    <div
      class="bg-surface-container-low p-5 rounded-xl flex flex-col gap-3 border border-surface-container/60 shadow-inner"
    >
      <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-2"
      >
        <label
          class="font-label-lg text-label-lg text-on-surface flex items-center gap-1.5 font-bold"
          for="temp-input"
        >
          <span class="material-symbols-outlined text-primary text-base"
            >device_thermostat</span
          >
          Input Pembacaan Suhu Fisik Termometer (°C) *
        </label>
        <span class="font-label-sm text-label-sm text-on-surface-variant"
          >Metode: Probe Sensor Internal</span
        >
      </div>

      <div
        class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4"
      >
        <div class="relative flex-1 max-w-xs">
          <input
            id="temp-input"
            name="temperature"
            type="number"
            min="-100"
            max="100"
            required
            step="0.1"
            :value="temperature"
            @input="
              emit(
                'update:temperature',
                ($event.target as HTMLInputElement).value,
              )
            "
            class="w-full bg-surface-container-lowest text-on-surface font-telemetry-value text-telemetry-value px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary shadow-sm border border-surface-container/60 font-bold"
          />
          <span
            class="absolute right-4 top-1/2 -translate-y-1/2 font-headline-md text-headline-md text-on-surface-variant font-bold"
          >
            °C
          </span>
        </div>

        <!-- Inline Temperature Validation Pill -->
        <div
          v-if="
            temperature !== '' &&
            Number(temperature) >= minimum &&
            Number(temperature) <= maximum
          "
          class="flex-1 bg-secondary-container text-on-secondary-container px-4 py-3 rounded-lg flex items-center gap-2 shadow-2xs"
        >
          <span class="material-symbols-outlined text-base">check_circle</span>
          <span class="font-label-sm text-label-sm font-semibold"
            >Dalam rentang kiriman ({{ minimum }}–{{ maximum }} °C)</span
          >
        </div>
        <div
          v-else
          class="flex-1 bg-error-container text-error px-4 py-3 rounded-lg flex items-center gap-2 shadow-2xs"
        >
          <span class="material-symbols-outlined text-base">warning</span>
          <span class="font-label-sm text-label-sm font-bold">{{
            temperature === ''
              ? 'Isi hasil pengukuran suhu.'
              : 'Di luar rentang kiriman (' + minimum + '–' + maximum + ' °C).'
          }}</span>
        </div>
      </div>
    </div>

    <!-- CATATAN PEMERIKSAAN (inspections.note) -->
    <div class="flex flex-col gap-2">
      <label
        class="font-label-lg text-label-lg text-on-surface flex items-center gap-1.5 font-bold"
        for="inspection-note"
      >
        <span class="material-symbols-outlined text-secondary text-base"
          >edit_note</span
        >
        Catatan pemeriksaan
      </label>
      <textarea
        id="inspection-note"
        name="inspection_note"
        minlength="5"
        maxlength="4000"
        required
        rows="3"
        :value="inspectionNote"
        @input="
          emit(
            'update:inspectionNote',
            ($event.target as HTMLTextAreaElement).value,
          )
        "
        class="w-full bg-surface-container-lowest text-on-surface font-body-md text-body-md p-3.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary resize-none shadow-sm leading-relaxed border border-surface-container/60"
        placeholder="Tuliskan catatan kondisi visual, nomor segel, dan catatan penting lainnya..."
      ></textarea>
      <span
        class="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-1"
      >
        <span class="material-symbols-outlined text-xs">info</span>
        Catatan ini akan diteruskan ke dashboard Supervisor / Admin Farmasi saat
        approval gate.
      </span>
    </div>
  </div>
</template>
