<script setup lang="ts">
import ShipmentDirectory from '../../components/layout/ShipmentDirectory.vue'
import RoleNavbar from '../../components/layout/RoleNavbar.vue'
import RoleFooter from '../../components/layout/RoleFooter.vue'
</script>
<template>
  <div class="min-h-screen flex flex-col">
    <RoleNavbar role="courier" />
    <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 py-8 flex-1 flex flex-col gap-6">
    <header>
      <h1 class="text-3xl font-semibold tracking-tight">Tugas saya</h1>
      <p class="mt-3 text-on-surface-variant">
        Hanya kiriman yang ditugaskan ke akun kurir ini.
      </p>
    </header>
    <ShipmentDirectory role="courier" />
    </main>
    <RoleFooter role="courier" />
  </div>
</template>
