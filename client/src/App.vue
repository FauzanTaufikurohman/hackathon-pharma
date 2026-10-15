<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import RoleNavbar from './components/layout/RoleNavbar.vue'
import RoleFooter from './components/layout/RoleFooter.vue'

const route = useRoute()
const isAdmin = computed(() => route.path.startsWith('/admin'))
const standalone = computed(
  () =>
    route.name === 'login' ||
    route.name === 'not-found' ||
    route.path.startsWith('/courier') ||
    route.path.startsWith('/kurir') ||
    route.path.startsWith('/checker') ||
    route.path.startsWith('/pemeriksa'),
)
</script>

<template>
  <div
    v-if="standalone"
    class="min-h-screen bg-[#F8FAFC] font-sans text-slate-800 antialiased selection:bg-rose-100 selection:text-rose-900"
  >
    <router-view />
  </div>
  <div
    v-else-if="isAdmin"
    class="flex min-h-screen flex-col bg-[#F8FAFC] font-sans text-slate-800 antialiased"
  >
    <RoleNavbar role="admin" />
    <div class="w-full flex-1">
      <router-view />
    </div>
    <RoleFooter role="admin" />
  </div>
  <div
    v-else
    class="isolate flex min-h-screen flex-col bg-slate-50 font-sans text-on-surface antialiased selection:bg-primary-fixed selection:text-primary"
  >
    <RoleNavbar role="vendor" />
    <main class="w-full flex-1 bg-slate-50">
      <router-view />
    </main>
    <RoleFooter role="vendor" />
  </div>
</template>
