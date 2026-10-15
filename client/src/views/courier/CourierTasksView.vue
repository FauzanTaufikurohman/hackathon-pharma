<script setup lang="ts">
import ShipmentDirectory from '../../components/layout/ShipmentDirectory.vue'
</script>
<template>
  <div class="flex flex-col gap-6">
    <header>
      <h1 class="text-3xl font-semibold tracking-tight">Tugas saya</h1>
      <p class="mt-3 text-on-surface-variant">
        Hanya kiriman yang ditugaskan ke akun kurir ini.
      </p>
    </header>
    <ShipmentDirectory role="courier" />
  </div>
</template>
