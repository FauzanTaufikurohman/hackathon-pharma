<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import VendorLiveMonitor from '../../components/vendor/VendorLiveMonitor.vue'
const route = useRoute()
const awb = computed(() => String(route.params.awb || ''))
</script>
<template><VendorLiveMonitor :key="awb" :awb="awb" /></template>
