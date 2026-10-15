<script setup lang="ts">
import CheckerNavbar from '../../components/checker/CheckerNavbar.vue'
import CheckerFooter from '../../components/checker/CheckerFooter.vue'
import ShipmentDirectory from '../../components/layout/ShipmentDirectory.vue'
</script>
<template>
  <div>
    <CheckerNavbar :active-tab="1" />
    <main
      class="max-w-6xl mx-auto px-4 sm:px-6 pt-28 pb-10 flex flex-col gap-6"
    >
      <header>
        <h1 class="text-3xl font-semibold tracking-tight">
          Antrean pemeriksaan
        </h1>
        <p class="mt-3 text-on-surface-variant">
          Buka resi yang ditugaskan kepada Anda. Catat hasil pemeriksaan dan
          foto sebelum meminta persetujuan Admin.
        </p>
      </header>
      <ShipmentDirectory role="checker" />
    </main>
    <CheckerFooter />
  </div>
</template>
