<script setup lang="ts">
import { ref, computed } from 'vue'
import type { AdminShipmentItem } from '../../types/shipment'

const props = defineProps<{
  shipments: AdminShipmentItem[]
}>()

const emit = defineEmits<{
  (e: 'review-qc', awb: string): void
  (e: 'monitor-gps', awb: string): void
  (e: 'open-ticket', awb: string): void
  (e: 'view-detail', awb: string): void
  (e: 'view-epod', awb: string): void
}>()

// Active filter category
const selectedFilter = ref<'all' | 'pending_approval' | 'in_transit' | 'held' | 'qc_passed'>('all')
const searchQuery = ref('')
const currentPage = ref(1)

const filterCounts = computed(() => ({
  all: 18,
  pending_approval: 2,
  in_transit: 9,
  held: 1,
  qc_passed: 6,
}))

const filteredShipments = computed(() => {
  return props.shipments.filter((item) => {
    // Category match
    if (selectedFilter.value === 'pending_approval' && item.status !== 'pending_approval') {
      return false
    }
    if (selectedFilter.value === 'in_transit' && item.status !== 'in_transit') {
      return false
    }
    if (selectedFilter.value === 'held' && item.status !== 'held') {
      return false
    }
    if (selectedFilter.value === 'qc_passed' && !['delivered', 'approved', 'inspection'].includes(item.status)) {
      return false
    }

    // Query match
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase()
      const matchAwb = item.awb.toLowerCase().includes(q)
      const matchRecipient = item.recipient_name.toLowerCase().includes(q)
      const matchCity = item.recipient_city.toLowerCase().includes(q)
      const matchCommodity = item.commodity.toLowerCase().includes(q)
      const matchSender = item.sender_origin.toLowerCase().includes(q)
      return matchAwb || matchRecipient || matchCity || matchCommodity || matchSender
    }

    return true
  })
})

function handleAction(item: AdminShipmentItem) {
  switch (item.action_type) {
    case 'review_qc':
      emit('review-qc', item.awb)
      break
    case 'monitor_gps':
      emit('monitor-gps', item.awb)
      break
    case 'open_ticket':
      emit('open-ticket', item.awb)
      break
    case 'detail':
      emit('view-detail', item.awb)
      break
    case 'view_epod':
      emit('view-epod', item.awb)
      break
    default:
      emit('view-detail', item.awb)
  }
}
</script>

<template>
  <div class="space-y-space-md">
    <!-- Search & Filters Container -->
    <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm space-y-3">
      <!-- Search bar -->
      <div class="relative flex items-center">
        <span class="material-symbols-outlined absolute left-3.5 text-on-surface-variant text-lg">search</span>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari no. resi PHA-260XXX, rumah sakit, atau jenis vaksin..."
          class="w-full bg-surface-container-low pl-10 pr-4 py-2.5 rounded-lg font-body-md text-body-md text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:bg-surface-container-lowest transition-all"
        />
        <button
          v-if="searchQuery"
          type="button"
          @click="searchQuery = ''"
          class="absolute right-3 text-on-surface-variant hover:text-on-surface"
        >
          <span class="material-symbols-outlined text-sm">close</span>
        </button>
      </div>

      <!-- Quick Filter Pills -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        <!-- Semua -->
        <button
          type="button"
          @click="selectedFilter = 'all'"
          :class="[
            'shrink-0 font-label-md text-label-md px-3 py-1.5 rounded-full transition-all cursor-pointer',
            selectedFilter === 'all'
              ? 'bg-inverse-surface text-inverse-on-surface shadow-sm'
              : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface',
          ]"
        >
          Semua ({{ filterCounts.all }})
        </button>

        <!-- Menunggu Approval -->
        <button
          type="button"
          @click="selectedFilter = 'pending_approval'"
          :class="[
            'shrink-0 font-label-md text-label-md px-3 py-1.5 rounded-full transition-all flex items-center gap-1.5 cursor-pointer',
            selectedFilter === 'pending_approval'
              ? 'bg-primary-fixed text-primary shadow-sm font-bold'
              : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface',
          ]"
        >
          <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
          Menunggu Approval ({{ filterCounts.pending_approval }})
        </button>

        <!-- Dalam Perjalanan -->
        <button
          type="button"
          @click="selectedFilter = 'in_transit'"
          :class="[
            'shrink-0 font-label-md text-label-md px-3 py-1.5 rounded-full transition-all flex items-center gap-1.5 cursor-pointer',
            selectedFilter === 'in_transit'
              ? 'bg-secondary-fixed text-on-secondary-fixed-variant shadow-sm font-bold'
              : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface',
          ]"
        >
          <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
          Dalam Perjalanan ({{ filterCounts.in_transit }})
        </button>

        <!-- Ditahan -->
        <button
          type="button"
          @click="selectedFilter = 'held'"
          :class="[
            'shrink-0 font-label-md text-label-md px-3 py-1.5 rounded-full transition-all flex items-center gap-1.5 cursor-pointer',
            selectedFilter === 'held'
              ? 'bg-error text-on-error shadow-sm font-bold'
              : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface',
          ]"
        >
          <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
          Ditahan ({{ filterCounts.held }})
        </button>

        <!-- Lolos QC -->
        <button
          type="button"
          @click="selectedFilter = 'qc_passed'"
          :class="[
            'shrink-0 font-label-md text-label-md px-3 py-1.5 rounded-full transition-all cursor-pointer',
            selectedFilter === 'qc_passed'
              ? 'bg-primary-fixed text-primary shadow-sm font-bold'
              : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface',
          ]"
        >
          Lolos QC ({{ filterCounts.qc_passed }})
        </button>
      </div>
    </div>

    <!-- Directory Table Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden border border-surface-container-low">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container-low">
              <th class="py-3 px-3.5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">
                No. Resi (AWB)
              </th>
              <th class="py-3 px-3.5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">
                Fasilitas Penerima
              </th>
              <th class="py-3 px-3.5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">
                Komoditas &amp; Koli
              </th>
              <th class="py-3 px-3.5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">
                Profil Suhu
              </th>
              <th class="py-3 px-3.5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">
                Petugas / Kurir
              </th>
              <th class="py-3 px-3.5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">
                Status Kargo
              </th>
              <th class="py-3 px-3.5 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider text-right">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-surface-container-low">
            <tr
              v-for="item in filteredShipments"
              :key="item.id"
              :class="[
                'hover:bg-surface-container-low/60 transition-colors',
                item.status === 'held' ? 'bg-error-container/20' : '',
              ]"
            >
              <!-- AWB & Origin -->
              <td class="py-3.5 px-3.5 whitespace-nowrap">
                <div class="flex items-center gap-1.5">
                  <span
                    :class="[
                      'font-headline-sm text-headline-sm font-bold',
                      item.status === 'pending_approval' ? 'text-primary' : '',
                      item.status === 'held' ? 'text-error' : '',
                      item.status !== 'pending_approval' && item.status !== 'held' ? 'text-on-surface' : '',
                    ]"
                  >
                    {{ item.awb }}
                  </span>
                </div>
                <span class="font-body-sm text-body-sm text-on-surface-variant block">
                  {{ item.sender_origin }}
                </span>
              </td>

              <!-- Recipient & City -->
              <td class="py-3.5 px-3.5">
                <div class="font-label-lg text-label-lg text-on-surface font-semibold">
                  {{ item.recipient_name }}
                </div>
                <div class="font-body-sm text-body-sm text-on-surface-variant truncate max-w-[140px]">
                  {{ item.recipient_city }}
                </div>
              </td>

              <!-- Commodity & Koli -->
              <td class="py-3.5 px-3.5">
                <div class="font-body-md text-body-md text-on-surface font-medium">
                  {{ item.commodity }}
                </div>
                <span class="font-label-sm text-label-sm text-on-surface-variant bg-surface-container px-1.5 py-0.5 rounded inline-block mt-0.5">
                  {{ item.koli }}
                </span>
              </td>

              <!-- Temperature Profile -->
              <td class="py-3.5 px-3.5 whitespace-nowrap">
                <span class="font-label-md text-label-md text-on-surface flex items-center gap-1">
                  <span
                    v-if="item.temp_type === 'chilled'"
                    class="material-symbols-outlined text-xs text-secondary"
                  >
                    ac_unit
                  </span>
                  <span
                    v-else
                    class="material-symbols-outlined text-xs text-tertiary"
                  >
                    thermostat
                  </span>
                  {{ item.temp_profile }}
                </span>
              </td>

              <!-- Officer / Courier -->
              <td class="py-3.5 px-3.5 whitespace-nowrap">
                <template v-if="item.officer_role === 'unassigned'">
                  <span class="font-body-md text-body-md text-on-surface-variant italic">
                    {{ item.officer_name }}
                  </span>
                </template>
                <template v-else>
                  <span class="font-body-md text-body-md text-on-surface block font-medium">
                    {{ item.officer_name }}
                  </span>
                  <span
                    v-if="item.officer_role.includes('Kurir')"
                    class="font-label-sm text-label-sm text-secondary block font-semibold"
                  >
                    {{ item.officer_role }}
                  </span>
                  <span
                    v-else
                    class="font-label-sm text-label-sm text-on-surface-variant block"
                  >
                    {{ item.officer_role }}
                  </span>
                </template>
              </td>

              <!-- Cargo Status Badge -->
              <td class="py-3.5 px-3.5 whitespace-nowrap">
                <!-- pending_approval -->
                <span
                  v-if="item.status === 'pending_approval'"
                  class="bg-primary-fixed text-primary font-label-sm text-label-sm px-2.5 py-1 rounded-full font-bold flex items-center gap-1 w-max"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                  pending_approval
                </span>

                <!-- in_transit -->
                <span
                  v-else-if="item.status === 'in_transit'"
                  class="bg-secondary-fixed text-on-secondary-fixed-variant font-label-sm text-label-sm px-2.5 py-1 rounded-full font-bold flex items-center gap-1 w-max"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                  in_transit
                </span>

                <!-- held -->
                <span
                  v-else-if="item.status === 'held'"
                  class="bg-error text-on-error font-label-sm text-label-sm px-2.5 py-1 rounded-full font-bold flex items-center gap-1 w-max"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-on-error"></span>
                  held
                </span>

                <!-- inspection -->
                <span
                  v-else-if="item.status === 'inspection'"
                  class="bg-surface-container-high text-on-surface font-label-sm text-label-sm px-2.5 py-1 rounded-full font-bold flex items-center gap-1 w-max"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-outline"></span>
                  inspection
                </span>

                <!-- delivered -->
                <span
                  v-else-if="item.status === 'delivered'"
                  class="bg-surface-container-lowest text-secondary font-label-sm text-label-sm px-2.5 py-1 rounded-full font-bold flex items-center gap-1 w-max shadow-sm"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                  delivered
                </span>

                <!-- generic fallback -->
                <span
                  v-else
                  class="bg-surface-container text-on-surface-variant font-label-sm text-label-sm px-2.5 py-1 rounded-full font-medium"
                >
                  {{ item.status }}
                </span>
              </td>

              <!-- Action Column -->
              <td class="py-3.5 px-3.5 text-right whitespace-nowrap">
                <!-- Review QC button -->
                <button
                  v-if="item.action_type === 'review_qc'"
                  type="button"
                  @click="handleAction(item)"
                  class="bg-primary hover:bg-on-primary-fixed-variant text-on-primary font-label-md text-label-md px-3 py-1.5 rounded-lg shadow-sm transition-all inline-flex items-center gap-1 cursor-pointer"
                >
                  {{ item.action_label }}
                </button>

                <!-- Monitor GPS button -->
                <button
                  v-else-if="item.action_type === 'monitor_gps'"
                  type="button"
                  @click="handleAction(item)"
                  class="bg-surface-container-low hover:bg-surface-container text-on-surface font-label-md text-label-md px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1 cursor-pointer"
                >
                  <span class="material-symbols-outlined text-xs">gps_fixed</span>
                  {{ item.action_label }}
                </button>

                <!-- Buka Tiket button -->
                <button
                  v-else-if="item.action_type === 'open_ticket'"
                  type="button"
                  @click="handleAction(item)"
                  class="bg-error hover:bg-on-error-container text-on-error font-label-md text-label-md px-3 py-1.5 rounded-lg shadow-sm transition-all inline-flex items-center gap-1 cursor-pointer"
                >
                  {{ item.action_label }}
                </button>

                <!-- Detail button -->
                <button
                  v-else-if="item.action_type === 'detail'"
                  type="button"
                  @click="handleAction(item)"
                  class="bg-surface-container-low hover:bg-surface-container text-on-surface font-label-md text-label-md px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1 cursor-pointer"
                >
                  {{ item.action_label }}
                </button>

                <!-- Lihat e-POD button -->
                <button
                  v-else-if="item.action_type === 'view_epod'"
                  type="button"
                  @click="handleAction(item)"
                  class="bg-surface-container-low hover:bg-surface-container text-on-surface font-label-md text-label-md px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1 cursor-pointer"
                >
                  <span class="material-symbols-outlined text-xs">receipt_long</span>
                  {{ item.action_label }}
                </button>

                <button
                  v-else
                  type="button"
                  @click="handleAction(item)"
                  class="bg-surface-container-low hover:bg-surface-container text-on-surface font-label-md text-label-md px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1 cursor-pointer"
                >
                  {{ item.action_label }}
                </button>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="filteredShipments.length === 0">
              <td colspan="7" class="py-12 text-center text-on-surface-variant">
                <span class="material-symbols-outlined text-4xl block mb-2 text-outline">search_off</span>
                <p class="font-body-md text-body-md">Tidak ada pengiriman yang sesuai dengan filter pencarian.</p>
                <button
                  type="button"
                  @click="selectedFilter = 'all'; searchQuery = ''"
                  class="mt-3 text-primary font-label-md text-label-md hover:underline cursor-pointer"
                >
                  Reset Filter &amp; Pencarian
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Table Footer / Pagination -->
      <div class="p-space-md bg-surface-container-low flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-surface-container">
        <span class="font-body-sm text-body-sm text-on-surface-variant">
          Menampilkan <span class="font-bold text-on-surface">{{ filteredShipments.length }}</span> dari
          <span class="font-bold text-on-surface">{{ filterCounts.all }}</span> pengiriman aktif
        </span>
        <div class="flex items-center gap-1.5">
          <button
            type="button"
            :disabled="currentPage === 1"
            @click="currentPage = Math.max(1, currentPage - 1)"
            class="p-1.5 rounded-lg bg-surface-container-lowest text-on-surface-variant hover:text-on-surface disabled:opacity-50 transition-colors shadow-sm cursor-pointer disabled:cursor-not-allowed"
          >
            <span class="material-symbols-outlined text-lg">chevron_left</span>
          </button>
          <button
            type="button"
            @click="currentPage = 1"
            :class="[
              'px-3 py-1 rounded-lg font-label-md text-label-md shadow-sm cursor-pointer transition-colors',
              currentPage === 1
                ? 'bg-primary text-on-primary'
                : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container',
            ]"
          >
            1
          </button>
          <button
            type="button"
            @click="currentPage = 2"
            :class="[
              'px-3 py-1 rounded-lg font-label-md text-label-md shadow-sm cursor-pointer transition-colors',
              currentPage === 2
                ? 'bg-primary text-on-primary'
                : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container',
            ]"
          >
            2
          </button>
          <button
            type="button"
            @click="currentPage = 3"
            :class="[
              'px-3 py-1 rounded-lg font-label-md text-label-md shadow-sm cursor-pointer transition-colors',
              currentPage === 3
                ? 'bg-primary text-on-primary'
                : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container',
            ]"
          >
            3
          </button>
          <button
            type="button"
            :disabled="currentPage === 3"
            @click="currentPage = Math.min(3, currentPage + 1)"
            class="p-1.5 rounded-lg bg-surface-container-lowest text-on-surface-variant hover:text-on-surface disabled:opacity-50 transition-colors shadow-sm cursor-pointer disabled:cursor-not-allowed"
          >
            <span class="material-symbols-outlined text-lg">chevron_right</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Quick Regulatory Cold-Chain Guidance Alert -->
    <div class="bg-surface-container p-space-md rounded-xl flex items-start gap-3 border border-surface-container-high/60">
      <span class="material-symbols-outlined text-primary text-xl shrink-0 mt-0.5">verified</span>
      <div class="text-on-surface">
        <span class="font-label-lg text-label-lg font-bold block mb-0.5">
          Standar Kepatuhan Distribusi Dingin (GDP/CPOB RI 2026)
        </span>
        <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
          Semua kargo bertanda <strong class="text-on-surface">Controlled Chilled</strong> wajib melewati tahap validasi checker (inspeksi fisik boks, keutuhan seal, &amp; kalibrasi data logger IoT) sebelum diserahkan ke Satria Kurir Cold-Chain.
        </p>
      </div>
    </div>
  </div>
</template>
