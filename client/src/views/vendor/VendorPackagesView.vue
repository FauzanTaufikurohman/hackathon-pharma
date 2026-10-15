<script setup lang="ts">
import ShipmentDirectory from '../../components/layout/ShipmentDirectory.vue'
</script>
<template>
  <div class="flex flex-col gap-6">
    <header>
      <h1 class="text-3xl font-semibold tracking-tight">Paket saya</h1>
      <p class="mt-3 text-on-surface-variant">
        Daftar kiriman yang terhubung ke akun Anda.
      </p>
    </header>
    <ShipmentDirectory role="vendor" />
  </div>
</template>
