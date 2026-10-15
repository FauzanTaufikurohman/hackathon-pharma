<script setup lang="ts">
import RoleNavbar from '../layout/RoleNavbar.vue'
defineProps<{ activeTab?: string | number }>()
</script>
<template><RoleNavbar role="admin" /></template>
