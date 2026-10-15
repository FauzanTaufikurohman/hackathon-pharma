<script setup lang="ts">
defineProps<{ loading?: boolean; error?: string; notice?: string }>()
defineEmits<{ retry: [] }>()
</script>
<template>
  <div
    v-if="loading"
    role="status"
    class="live-feedback text-on-surface-variant"
    aria-live="polite"
  >
    Memuat data…
  </div>
  <div
    v-if="error"
    role="alert"
    class="live-feedback bg-error-container text-on-error-container"
  >
    <p>{{ error }}</p>
    <button type="button" class="live-button" @click="$emit('retry')">
      Muat ulang data
    </button>
  </div>
  <p
    v-if="notice"
    role="status"
    class="live-feedback bg-secondary-container text-on-secondary-container"
  >
    {{ notice }}
  </p>
</template>
