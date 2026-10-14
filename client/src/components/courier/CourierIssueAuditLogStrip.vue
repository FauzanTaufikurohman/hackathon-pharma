<script setup lang="ts">
// Active Investigation Log / Audit Trail Strip
</script>

<template>
  <div class="bg-surface-container-low rounded-xl p-5 shadow-sm space-y-3 border border-surface-container/60">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-on-surface-variant text-[20px]">history</span>
        <span class="font-label-lg text-label-lg text-on-surface font-bold uppercase tracking-wider">
          Audit Log Kejadian Cold-Chain
        </span>
      </div>
      <span class="font-label-sm text-label-sm text-on-surface-variant font-mono">
        ISO 13485:2016 Compliant
      </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <!-- Item 1 -->
      <div class="bg-surface-container-lowest p-3 rounded-lg flex items-start gap-2.5 border border-surface-container/40">
        <span class="material-symbols-outlined text-secondary text-[18px] shrink-0 mt-0.5">check_circle</span>
        <div class="text-left">
          <div class="font-label-sm text-label-sm text-on-surface font-semibold">15:15 WIB • Issue Logged</div>
          <div class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
            Kurir melaporkan anomali segel fisik SL-8891.
          </div>
        </div>
      </div>

      <!-- Item 2 -->
      <div class="bg-surface-container-lowest p-3 rounded-lg flex items-start gap-2.5 border border-surface-container/40">
        <span class="material-symbols-outlined text-secondary text-[18px] shrink-0 mt-0.5">check_circle</span>
        <div class="text-left">
          <div class="font-label-sm text-label-sm text-on-surface font-semibold">15:16 WIB • Status Changed: HELD</div>
          <div class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
            Otomatisasi ERP Anteraja mengunci AWB PHA-260104.
          </div>
        </div>
      </div>

      <!-- Item 3 -->
      <div class="bg-surface-container-lowest p-3 rounded-lg flex items-start gap-2.5 border border-surface-container/40">
        <span class="material-symbols-outlined text-primary text-[18px] shrink-0 mt-0.5">mark_chat_read</span>
        <div class="text-left">
          <div class="font-label-sm text-label-sm text-on-surface font-semibold">15:22 WIB • QA Decision Authorized</div>
          <div class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
            Instruksi RETUR disahkan oleh dr. Ardiansyah.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
