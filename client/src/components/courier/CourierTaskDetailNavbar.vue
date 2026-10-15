<script setup lang="ts">
import RoleNavbar from '../layout/RoleNavbar.vue'
defineProps<{ activeTab?: number; awb?: string }>()
</script>
<template><RoleNavbar role="courier" /></template>
