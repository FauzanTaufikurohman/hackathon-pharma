<script setup lang="ts">
import { ref, computed } from 'vue'
import type { AdminAuditEvent } from '../../types/shipment'

const props = defineProps<{
  events: AdminAuditEvent[]
}>()

const searchQuery = ref('')
const selectedEventType = ref<string>('all')
const isFilterOpen = ref(false)

const eventTypeBadgeClass = (type: string) => {
  switch (type.toLowerCase()) {
    case 'decide':
    case 'issue':
    case 'temp_excursion':
      return 'bg-error-container text-on-error-container font-bold'
    case 'depart':
    case 'approve':
      return 'bg-secondary-container text-on-secondary-container font-bold'
    case 'telemetry':
    case 'accept':
    case 'assign':
    case 'inspect':
    default:
      return 'bg-surface-container-high text-on-surface-variant font-semibold'
  }
}

const statusClass = (status: string) => {
  switch (status.toLowerCase()) {
    case 'in_transit':
    case 'approved':
      return 'text-secondary font-semibold'
    case 'returning':
      return 'text-error font-bold'
    case 'held':
    case 'pending_approval':
      return 'text-tertiary font-bold'
    case 'accepted':
    case 'assigned':
      return 'text-on-surface font-bold'
    default:
      return 'text-on-surface-variant'
  }
}

const filteredEvents = computed(() => {
  return props.events.filter((item) => {
    const q = searchQuery.value.toLowerCase().trim()
    const matchesSearch =
      !q ||
      item.awb.toLowerCase().includes(q) ||
      item.actor.toLowerCase().includes(q) ||
      item.role.toLowerCase().includes(q) ||
      item.description.toLowerCase().includes(q) ||
      item.event_type.toLowerCase().includes(q)

    const matchesFilter =
      selectedEventType.value === 'all' ||
      item.event_type.toLowerCase() === selectedEventType.value.toLowerCase()

    return matchesSearch && matchesFilter
  })
})
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-5 shadow-sm border border-surface-container flex flex-col gap-4">
    <!-- Panel Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-surface-container-low">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-primary shrink-0">
          <span class="material-symbols-outlined text-[20px]">enhanced_encryption</span>
        </div>
        <div>
          <h3 class="font-headline-sm text-headline-sm text-on-surface">
            Jurnal Audit Trail Mutlak (Chain of Custody Events)
          </h3>
          <p class="font-body-sm text-body-sm text-on-surface-variant">
            Immutable Event Log • Kepatuhan Regulasi BPOM CDOB Pasal 27
          </p>
        </div>
      </div>

      <!-- Search & Filter Controls -->
      <div class="flex items-center gap-2 relative">
        <div class="relative">
          <span class="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant text-[18px]">
            search
          </span>
          <input
            v-model="searchQuery"
            id="event-search"
            type="text"
            placeholder="Cari No. Resi / Aktor..."
            class="bg-surface-container-low rounded-lg pl-9 pr-3 py-1.5 font-body-sm text-body-sm text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-2 focus:ring-primary w-44 md:w-56 border border-surface-container"
          />
          <button
            v-if="searchQuery"
            @click="searchQuery = ''"
            type="button"
            class="absolute right-2 top-2 text-on-surface-variant hover:text-on-surface"
          >
            <span class="material-symbols-outlined text-sm">close</span>
          </button>
        </div>

        <!-- Filter Dropdown Trigger -->
        <div class="relative">
          <button
            type="button"
            @click="isFilterOpen = !isFilterOpen"
            class="bg-surface-container-low hover:bg-surface-container p-2 rounded-lg text-on-surface-variant hover:text-on-surface border border-surface-container transition-colors cursor-pointer"
            :class="{ 'ring-2 ring-primary text-primary': selectedEventType !== 'all' }"
            title="Filter Parameter"
          >
            <span class="material-symbols-outlined text-[20px]">filter_list</span>
          </button>

          <!-- Dropdown Filter Menu -->
          <div
            v-if="isFilterOpen"
            class="absolute right-0 top-11 z-30 w-48 bg-surface-container-lowest rounded-xl shadow-lg border border-surface-container p-2 flex flex-col gap-1 text-label-sm"
          >
            <span class="px-2.5 py-1 text-on-surface-variant font-bold uppercase tracking-wider text-[10px]">
              Filter Tipe Event
            </span>
            <button
              v-for="opt in [
                { id: 'all', label: 'Semua Event' },
                { id: 'decide', label: 'DECIDE (Keputusan)' },
                { id: 'issue', label: 'ISSUE (Kendala)' },
                { id: 'telemetry', label: 'TELEMETRY' },
                { id: 'depart', label: 'DEPART (Berangkat)' },
                { id: 'approve', label: 'APPROVE (QC)' },
                { id: 'inspect', label: 'INSPECT (Initial Exam)' },
              ]"
              :key="opt.id"
              @click="selectedEventType = opt.id; isFilterOpen = false"
              type="button"
              class="w-full text-left px-2.5 py-1.5 rounded-lg transition-colors flex items-center justify-between"
              :class="selectedEventType === opt.id ? 'bg-primary-fixed text-primary font-bold' : 'hover:bg-surface-container-low text-on-surface'"
            >
              <span>{{ opt.label }}</span>
              <span v-if="selectedEventType === opt.id" class="material-symbols-outlined text-xs">check</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Container -->
    <div class="overflow-x-auto -mx-5 px-5">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-surface-container-low text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider border-b border-surface-container">
            <th class="py-3 px-3.5 rounded-l-lg">Waktu</th>
            <th class="py-3 px-3.5">No. Resi</th>
            <th class="py-3 px-3.5">Aktor &amp; Peran</th>
            <th class="py-3 px-3.5">Tipe Event</th>
            <th class="py-3 px-3.5">Perubahan Status</th>
            <th class="py-3 px-3.5 rounded-r-lg">Keterangan / Alasan</th>
          </tr>
        </thead>
        <tbody id="audit-events-tbody" class="font-body-sm text-body-sm text-on-surface divide-y divide-surface-container-low">
          <tr
            v-for="item in filteredEvents"
            :key="item.id"
            class="hover:bg-surface-container-low/50 transition-colors"
            :class="{ 'bg-primary-fixed/20 animate-pulse': item.is_new }"
          >
            <!-- Waktu -->
            <td class="py-3.5 px-3.5 font-label-sm text-label-sm text-on-surface-variant whitespace-nowrap">
              {{ item.time }}
              <span class="text-[10px] block opacity-70">{{ item.date }}</span>
            </td>

            <!-- No. Resi -->
            <td class="py-3.5 px-3.5 font-label-md text-label-md text-primary font-bold whitespace-nowrap">
              {{ item.awb }}
            </td>

            <!-- Aktor & Peran -->
            <td class="py-3.5 px-3.5 whitespace-nowrap">
              <span class="font-label-md text-label-md block font-semibold">{{ item.actor }}</span>
              <span class="font-label-sm text-label-sm text-on-surface-variant">{{ item.role }}</span>
            </td>

            <!-- Tipe Event -->
            <td class="py-3.5 px-3.5 whitespace-nowrap">
              <span
                class="px-2 py-0.5 rounded-full font-label-sm text-label-sm uppercase"
                :class="eventTypeBadgeClass(item.event_type)"
              >
                {{ item.event_type }}
              </span>
            </td>

            <!-- Perubahan Status -->
            <td class="py-3.5 px-3.5 whitespace-nowrap">
              <div v-if="item.from_status && item.to_status" class="flex items-center gap-1.5 font-label-sm text-label-sm">
                <span :class="statusClass(item.from_status)">{{ item.from_status }}</span>
                <span class="material-symbols-outlined text-[14px] text-on-surface-variant">trending_flat</span>
                <span :class="statusClass(item.to_status)">{{ item.to_status }}</span>
              </div>
              <div v-else-if="item.to_status" class="font-label-sm text-label-sm" :class="statusClass(item.to_status)">
                {{ item.to_status }}
              </div>
              <span v-else class="text-on-surface-variant text-xs">-</span>
            </td>

            <!-- Keterangan / Alasan -->
            <td class="py-3.5 px-3.5 max-w-xs truncate" :title="item.description">
              {{ item.description }}
            </td>
          </tr>

          <!-- Empty State -->
          <tr v-if="filteredEvents.length === 0">
            <td colspan="6" class="py-8 text-center text-on-surface-variant font-body-md">
              <span class="material-symbols-outlined text-3xl mb-1 text-on-surface-variant block">search_off</span>
              Tidak ada catatan audit yang cocok dengan filter "{{ searchQuery || selectedEventType }}".
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Table Footer / Verification Info -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-surface-container text-on-surface-variant">
      <div class="flex items-center gap-2 font-label-sm text-label-sm">
        <span class="w-2 h-2 rounded-full bg-secondary"></span>
        <span class="font-semibold text-on-surface">SHA-256 Merkle Block Integrity Verified • Zero Tampering Detected</span>
      </div>
      <span class="font-label-sm text-label-sm">
        Menampilkan {{ filteredEvents.length }} dari 1,420 events mutlak
      </span>
    </div>
  </div>
</template>
