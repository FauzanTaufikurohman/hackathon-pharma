<script setup lang="ts">
import type { CheckerHistoryItem } from '../../types/shipment'

interface Props {
  item: CheckerHistoryItem
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'action-click', item: CheckerHistoryItem): void
}>()
</script>

<template>
  <div
    class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md border border-surface-container/60"
    :class="{ 'shadow-md': item.is_alert }"
  >
    <!-- Card Top Ribbon Header -->
    <div
      class="px-6 py-4 flex flex-wrap items-center justify-between gap-3 border-b border-surface-container/40"
      :class="item.is_alert ? 'bg-error-container/30' : 'bg-surface-container-low'"
    >
      <div class="flex items-center gap-3">
        <span
          class="font-headline-sm text-headline-sm tracking-mono font-bold"
          :class="item.is_alert ? 'text-error' : 'text-on-surface'"
        >
          {{ item.awb }}
        </span>
        <span class="h-4 w-px bg-outline-variant"></span>
        <span class="font-headline-sm text-headline-sm text-on-surface font-semibold">
          {{ item.commodity_title }}
        </span>
        <span
          class="px-2 py-0.5 rounded-md font-label-sm text-label-sm shadow-2xs"
          :class="item.is_alert ? 'bg-surface-container-lowest text-on-surface-variant' : 'bg-surface-container text-on-surface-variant'"
        >
          {{ item.package_count_str }}
        </span>
      </div>

      <div
        class="flex items-center gap-2 font-body-sm text-body-sm"
        :class="item.is_alert ? 'text-error' : 'text-on-surface-variant'"
      >
        <span class="material-symbols-outlined text-sm">
          {{ item.is_alert ? 'event_busy' : 'schedule' }}
        </span>
        <span>Waktu Submit: <strong :class="item.is_alert ? 'text-error' : 'text-on-surface'">{{ item.submitted_at }}</strong></span>
      </div>
    </div>

    <!-- Main Body: Checker QC Summary & Admin Gate Section -->
    <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
      <!-- Checker Side: Inspections Table Attributes (7 Cols) -->
      <div class="lg:col-span-7 flex flex-col justify-between space-y-4">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-bold">
              Hasil Checklist Pemeriksaan Fisik (Checker QC)
            </span>
            <span
              v-if="item.qc_passed"
              class="px-2.5 py-0.5 rounded-full bg-secondary-container/40 text-secondary font-label-sm text-label-sm font-bold flex items-center gap-1 shadow-2xs"
            >
              <span class="material-symbols-outlined text-xs">check_circle</span>
              PASSED = TRUE
            </span>
            <span
              v-else
              class="px-2.5 py-0.5 rounded-full bg-error-container text-error font-label-sm text-label-sm font-bold flex items-center gap-1 shadow-2xs"
            >
              <span class="material-symbols-outlined text-xs">cancel</span>
              PASSED = FALSE
            </span>
          </div>

          <!-- Parameters Grid -->
          <div
            class="grid gap-2"
            :class="item.checklist_params.length === 3 ? 'grid-cols-2 sm:grid-cols-3' : 'grid-cols-2 sm:grid-cols-4'"
          >
            <div
              v-for="(param, idx) in item.checklist_params"
              :key="idx"
              class="p-3 rounded-lg border border-surface-container/40"
              :class="param.is_error ? 'bg-error-container/40 border-error/30' : 'bg-surface-container-low'"
            >
              <span
                class="font-body-sm text-body-sm block"
                :class="param.is_error ? 'text-error font-medium' : 'text-on-surface-variant'"
              >
                {{ param.label }}
              </span>
              <span
                class="font-label-md text-label-md flex items-center gap-1 mt-0.5"
                :class="param.is_error ? 'text-error font-bold' : (param.is_highlight ? 'text-secondary font-bold' : (param.value.includes('Ambient') ? 'text-on-surface font-semibold' : 'text-secondary font-bold'))"
              >
                <span class="material-symbols-outlined text-sm">{{ param.icon }}</span>
                <span>{{ param.value }}</span>
              </span>
            </div>
          </div>
        </div>

        <!-- Cold-Chain Telemetry Strip -->
        <div
          class="p-3.5 rounded-lg flex items-center justify-between border border-surface-container/40"
          :class="item.cold_chain_strip.is_error ? 'bg-error-container/20 border-error/30' : 'bg-surface-container-high/40'"
        >
          <div class="flex items-center gap-3">
            <span
              class="material-symbols-outlined"
              :class="item.cold_chain_strip.is_error ? 'text-error' : 'text-secondary'"
            >
              {{ item.cold_chain_strip.icon }}
            </span>
            <div>
              <span class="font-body-sm text-body-sm text-on-surface-variant font-medium">
                {{ item.cold_chain_strip.label }}
              </span>
              <span
                v-if="item.cold_chain_strip.type === 'temperature'"
                class="font-headline-sm text-headline-sm text-on-surface ml-1.5 font-bold"
              >
                {{ item.cold_chain_strip.value }}
              </span>
              <span
                v-else
                class="font-label-md text-label-md ml-1 font-bold"
                :class="item.cold_chain_strip.is_error ? 'text-error' : 'text-on-surface'"
              >
                {{ item.cold_chain_strip.value }}
              </span>
            </div>
          </div>

          <!-- Strip Badge -->
          <span
            v-if="item.cold_chain_strip.type === 'verifier'"
            class="font-body-sm text-body-sm text-secondary font-bold"
          >
            {{ item.cold_chain_strip.badge_text }}
          </span>
          <span
            v-else-if="item.cold_chain_strip.is_error"
            class="px-2 py-0.5 rounded-md bg-error text-on-error font-label-sm text-label-sm font-bold shadow-2xs"
          >
            {{ item.cold_chain_strip.badge_text }}
          </span>
          <span
            v-else
            class="px-2 py-0.5 rounded-full bg-secondary-container text-secondary font-label-sm text-label-sm font-bold shadow-2xs"
          >
            {{ item.cold_chain_strip.badge_text }}
          </span>
        </div>
      </div>

      <!-- Admin Gate Decision Side: Approvals Table (5 Cols) -->
      <div class="lg:col-span-5 bg-surface-container-low rounded-xl p-5 flex flex-col justify-between space-y-4 border border-surface-container/40">
        <div>
          <div class="flex items-center justify-between gap-2 mb-3">
            <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-bold">
              Keputusan Admin Control Tower
            </span>

            <!-- Pending Pill with pulsating icon -->
            <span
              v-if="item.admin_decision.status === 'pending'"
              class="px-3 py-1 rounded-full bg-amber-100 text-tertiary font-label-sm text-label-sm font-bold flex items-center gap-1.5 shadow-2xs"
            >
              <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-tertiary"></span>
              </span>
              {{ item.admin_decision.status_label }}
            </span>

            <!-- Approved Pill -->
            <span
              v-else-if="item.admin_decision.status === 'approved'"
              class="px-3 py-1 rounded-full bg-secondary-container text-secondary font-label-sm text-label-sm font-bold flex items-center gap-1 shadow-2xs"
            >
              <span class="material-symbols-outlined text-xs">check_circle</span>
              {{ item.admin_decision.status_label }}
            </span>

            <!-- Rejected Pill -->
            <span
              v-else-if="item.admin_decision.status === 'rejected'"
              class="px-3 py-1 rounded-full bg-error-container text-error font-label-sm text-label-sm font-bold flex items-center gap-1 shadow-2xs"
            >
              <span class="material-symbols-outlined text-xs">block</span>
              {{ item.admin_decision.status_label }}
            </span>
          </div>

          <!-- Admin Note Container -->
          <div class="p-3 rounded-lg bg-surface-container-lowest shadow-2xs border border-surface-container/40 space-y-1.5">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-1.5">
                <span
                  v-if="item.admin_decision.status === 'pending'"
                  class="material-symbols-outlined text-amber-600 text-lg"
                >
                  hourglass_top
                </span>
                <span
                  class="font-label-sm text-label-sm font-bold block"
                  :class="item.admin_decision.status === 'rejected' ? 'text-error' : 'text-on-surface'"
                >
                  {{ item.admin_decision.reviewer_role_title }}
                </span>
              </div>
              <span v-if="item.admin_decision.timestamp" class="font-body-sm text-body-sm text-on-surface-variant font-medium">
                {{ item.admin_decision.timestamp }}
              </span>
            </div>
            <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
              {{ item.admin_decision.note }}
            </p>
          </div>
        </div>

        <div class="pt-2 flex items-center justify-between gap-3">
          <!-- Footer Status Indicator -->
          <span
            class="font-body-sm text-body-sm flex items-center gap-1 font-semibold"
            :class="item.admin_decision.status === 'rejected' ? 'text-error' : 'text-on-surface-variant'"
          >
            <span
              class="material-symbols-outlined text-sm"
              :class="{
                'text-secondary': item.admin_decision.status === 'approved',
                'text-error': item.admin_decision.status === 'rejected',
              }"
            >
              {{ item.admin_decision.footer_status_icon }}
            </span>
            <span>{{ item.admin_decision.footer_status_label }}</span>
          </span>

          <!-- Action Button -->
          <button
            type="button"
            @click="emit('action-click', item)"
            class="px-4 py-2 rounded-lg font-label-md text-label-md transition-all shadow-sm flex items-center gap-1.5 cursor-pointer font-bold active:scale-95"
            :class="{
              'bg-primary text-on-primary hover:bg-primary-container': item.admin_decision.action_button_type === 'exam_detail',
              'bg-surface-container-lowest text-on-surface hover:bg-surface-container-high border border-surface-container': item.admin_decision.action_button_type === 'pdf_download' || item.admin_decision.action_button_type === 'audit_trail',
              'bg-error text-on-error hover:opacity-90': item.admin_decision.action_button_type === 'quarantine_doc',
            }"
          >
            <span
              v-if="item.admin_decision.action_button_type !== 'exam_detail'"
              class="material-symbols-outlined text-sm"
              :class="{ 'text-primary': item.admin_decision.action_button_type === 'pdf_download' }"
            >
              {{ item.admin_decision.action_button_icon }}
            </span>
            <span>{{ item.admin_decision.action_button_label }}</span>
            <span
              v-if="item.admin_decision.action_button_type === 'exam_detail'"
              class="material-symbols-outlined text-sm"
            >
              {{ item.admin_decision.action_button_icon }}
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
