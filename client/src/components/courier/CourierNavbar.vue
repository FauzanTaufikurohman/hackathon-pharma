<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router'
import { mockCourierProfile } from '../../data/mockShipments'
import { logoutAndRedirect } from '../../services/auth'

const route = useRoute()
const router = useRouter()

const emit = defineEmits<{
  (e: 'notify-click'): void
}>()

function handleLogout() {
  return logoutAndRedirect(router)
}
</script>
<template><RoleNavbar role="courier" /></template>
