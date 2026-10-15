<script setup lang="ts">
import { ref, computed } from 'vue'
import type { AdminIncidentData } from '../../types/shipment'

const props = defineProps<{
  incident: AdminIncidentData
  isSubmitting?: boolean
}>()

const emit = defineEmits<{
  (e: 'submit-decision', payload: { action: 'return' | 'recheck' | 'resume'; reason: string }): void
  (e: 'view-photo', url: string): void
}>()

const selectedAction = ref<'return' | 'recheck' | 'resume'>(props.incident.default_action)
const officialReason = ref(props.incident.default_reason)

const actionButtonLabel = computed(() => {
  if (selectedAction.value === 'return') return 'Kirim Keputusan & Instruksikan Kurir + Faskes via WA (status → returning)'
  if (selectedAction.value === 'recheck') return 'Kirim Keputusan & Instruksikan Kurir + Faskes via WA (status → inspection)'
  return 'Kirim Keputusan & Instruksikan Kurir + Faskes via WA (status → in_transit)'
})

function handleSubmit() {
  emit('submit-decision', {
    action: selectedAction.value,
    reason: officialReason.value,
  })
}
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl border-2 border-primary/40 shadow-md p-5 flex flex-col gap-4 relative overflow-hidden">
    <!-- Subtle Red Glow Tint -->
    <div class="absolute top-0 right-0 w-36 h-36 bg-primary/5 rounded-full blur-2xl pointer-events-none"></div>

    <!-- Incident Header -->
    <div class="flex items-start justify-between gap-3">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-primary text-on-primary flex items-center justify-center shrink-0 shadow-xs">
          <span class="material-symbols-outlined text-lg">gavel</span>
        </div>
        <div>
          <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Kargo Berstatus HELD</h2>
          <span class="font-body-sm text-body-sm text-on-surface-variant">Butuh Keputusan Resmi Admin Segera</span>
        </div>
      </div>
      <span class="font-label-sm text-label-sm bg-primary text-on-primary px-2.5 py-1 rounded-full font-bold uppercase tracking-wider shrink-0 shadow-sm">
        Status: HELD
      </span>
    </div>

    <!-- Issue Metadata Strip -->
    <div class="p-3 bg-surface-container-low rounded-lg border border-surface-container flex flex-col gap-1.5">
      <div class="flex items-center justify-between">
        <span class="font-label-sm text-label-sm text-on-surface-variant font-medium">Tiket Insiden • issues</span>
        <span class="font-label-sm text-label-sm font-bold text-primary">{{ incident.ticket_id }}</span>
      </div>
      <div class="flex items-center justify-between">
        <span class="font-body-md text-body-md text-on-surface font-semibold">Resi AWB: {{ incident.awb }}</span>
        <span class="font-label-sm text-label-sm text-on-surface-variant">Tujuan: {{ incident.destination }}</span>
      </div>
    </div>

    <!-- Detailed Courier Report (From 'issues' Table) -->
    <div class="flex flex-col gap-2.5 border-t border-surface-container-low pt-3">
      <div class="flex items-center justify-between text-on-surface font-label-sm text-label-sm flex-wrap gap-1">
        <span class="flex items-center gap-1.5">
          <span class="material-symbols-outlined text-base text-on-surface-variant">person_pin</span>
          Pelapor: <strong>{{ incident.reporter_name }}</strong>
        </span>
        <span class="text-on-surface-variant font-medium">Pukul: {{ incident.reported_at }}</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="font-label-sm text-label-sm text-on-surface-variant font-medium">Kategori:</span>
        <span class="font-label-sm text-label-sm bg-error-container text-on-error-container px-2.5 py-0.5 rounded-full font-bold flex items-center gap-1">
          <span class="material-symbols-outlined text-xs">broken_image</span>
          {{ incident.category_label }}
        </span>
      </div>
      <div class="p-3 rounded-lg bg-surface border border-outline-variant/40 font-body-sm text-body-sm text-on-surface leading-relaxed italic">
        {{ incident.description }}
      </div>

      <!-- Courier Photographic Proof with Geo/Integrity Watermark -->
      <div class="flex flex-col gap-1.5 pt-1">
        <span class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1 font-semibold">
          <span class="material-symbols-outlined text-sm">photo_camera</span>
          Foto Bukti Lapangan (Kurir Real-Time Upload):
        </span>
        <div
          @click="emit('view-photo', incident.photo_url)"
          class="relative rounded-lg overflow-hidden border border-outline-variant h-40 bg-surface-container group cursor-pointer"
          title="Klik untuk memperbesar foto bukti insiden"
        >
          <img
            class="w-full h-full object-cover group-hover:scale-105 transition-transform"
            alt="Foto Bukti Lapangan Segel Rusak"
            :src="incident.photo_url"
          />
          <div class="absolute inset-x-0 bottom-0 bg-inverse-surface/85 backdrop-blur-xs px-3 py-1.5 flex items-center justify-between text-inverse-on-surface font-label-sm text-[10px]">
            <span>{{ incident.gps_coords }}</span>
            <span class="font-mono text-primary-fixed-dim">{{ incident.hash }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- FORM KEPUTUSAN RESOLUSI RESMI ADMIN ('decisions' Table) -->
    <form class="flex flex-col gap-3.5 border-t border-surface-container pt-3" @submit.prevent="handleSubmit">
      <div class="flex items-center justify-between">
        <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Formulir Keputusan Otoritas QA &amp; Dispatcher</h3>
        <span class="font-label-sm text-label-sm text-primary font-bold">*Wajib Diisi</span>
      </div>

      <!-- Radio Actions ('decisions.action') -->
      <div class="flex flex-col gap-2">
        <!-- Option 1: RETURN -->
        <label
          :class="[
            'relative flex items-start gap-3 p-3 rounded-xl cursor-pointer transition-all',
            selectedAction === 'return'
              ? 'border-2 border-primary bg-primary/5 shadow-xs'
              : 'border border-surface-container hover:bg-surface-container-low',
          ]"
        >
          <input
            v-model="selectedAction"
            type="radio"
            name="incident_decision"
            value="return"
            class="mt-1 accent-primary w-4 h-4 cursor-pointer"
          />
          <div class="flex flex-col">
            <span class="font-label-md text-label-md font-bold text-primary">
              return • RETUR KARGO KE GUDANG ASAL (status → returning)
            </span>
            <span class="font-body-sm text-body-sm text-on-surface-variant">
              Kargo ditarik kembali ke Hub Cawang untuk investigasi karantina BAP &amp; perlindungan sterilitas.
            </span>
          </div>
        </label>

        <!-- Option 2: RECHECK -->
        <label
          :class="[
            'relative flex items-start gap-3 p-3 rounded-xl cursor-pointer transition-all',
            selectedAction === 'recheck'
              ? 'border-2 border-primary bg-primary/5 shadow-xs'
              : 'border border-surface-container hover:bg-surface-container-low',
          ]"
        >
          <input
            v-model="selectedAction"
            type="radio"
            name="incident_decision"
            value="recheck"
            class="mt-1 accent-primary w-4 h-4 cursor-pointer"
          />
          <div class="flex flex-col">
            <span class="font-label-md text-label-md font-bold" :class="selectedAction === 'recheck' ? 'text-primary' : 'text-on-surface'">
              recheck • PERIKSA ULANG DI HUB TERDEKAT (status → inspection)
            </span>
            <span class="font-body-sm text-body-sm text-on-surface-variant">
              Arahkan kurir ke Spoke Hub Matraman untuk pemeriksaan visual ulang oleh petugas QC.
            </span>
          </div>
        </label>

        <!-- Option 3: RESUME -->
        <label
          :class="[
            'relative flex items-start gap-3 p-3 rounded-xl cursor-pointer transition-all',
            selectedAction === 'resume'
              ? 'border-2 border-primary bg-primary/5 shadow-xs'
              : 'border border-surface-container hover:bg-surface-container-low',
          ]"
        >
          <input
            v-model="selectedAction"
            type="radio"
            name="incident_decision"
            value="resume"
            class="mt-1 accent-primary w-4 h-4 cursor-pointer"
          />
          <div class="flex flex-col">
            <span class="font-label-md text-label-md font-bold" :class="selectedAction === 'resume' ? 'text-primary' : 'text-on-surface'">
              resume • LANJUTKAN PENGANTARAN (status → in_transit)
            </span>
            <span class="font-body-sm text-body-sm text-on-surface-variant">
              Abaikan anomali fisik jika segel dalam masih utuh berdasarkan otorisasi apoteker.
            </span>
          </div>
        </label>
      </div>

      <!-- Official Reason Textarea ('decisions.reason') -->
      <div class="flex flex-col gap-1.5">
        <div class="flex items-center justify-between">
          <label class="font-label-sm text-label-sm text-on-surface font-bold" for="official-reason">
            Alasan Resmi Keputusan (Audit Trail BAP):
          </label>
          <span class="font-label-sm text-label-sm text-on-surface-variant">Min. 20 Karakter</span>
        </div>
        <textarea
          id="official-reason"
          v-model="officialReason"
          rows="3"
          class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest p-3 font-body-sm text-body-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none shadow-xs"
        ></textarea>
      </div>

      <!-- Signing Officer Badge -->
      <div class="flex items-center justify-between p-2.5 bg-surface-container-low rounded-lg border border-surface-container">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-secondary text-sm">verified_user</span>
          <span class="font-label-sm text-label-sm text-on-surface">
            Pejabat Otoritas: <strong>{{ incident.signing_officer }}</strong> ({{ incident.officer_title }})
          </span>
        </div>
        <span class="font-mono text-[10px] text-on-surface-variant">RSA-2048 Signed</span>
      </div>

      <!-- Primary Submit Decision CTA -->
      <button
        type="submit"
        :disabled="isSubmitting"
        class="w-full bg-primary hover:bg-primary-container text-on-primary font-headline-sm text-headline-sm py-3.5 px-4 rounded-xl shadow-md transition-all flex items-center justify-center gap-2 active:scale-[0.99] cursor-pointer"
      >
        <template v-if="isSubmitting">
          <span class="material-symbols-outlined animate-spin text-xl">progress_activity</span>
          <span>Mengirim Keputusan &amp; Menghubungi Kurir...</span>
        </template>
        <template v-else>
          <span class="material-symbols-outlined text-xl">send</span>
          <span>{{ actionButtonLabel }}</span>
        </template>
      </button>

      <!-- Audit Compliance Notice -->
      <div class="flex items-start gap-2 text-on-surface-variant font-label-sm text-[11px] leading-snug">
        <span class="material-symbols-outlined text-sm text-secondary shrink-0 mt-0.5">info</span>
        <span>
          Menekan tombol ini akan memperbarui status di database, mencatat ke audit trail, dan mengirim notifikasi otomatis ke WhatsApp Kurir &amp; RS Medika Sehat.
        </span>
      </div>
    </form>
  </div>
</template>
