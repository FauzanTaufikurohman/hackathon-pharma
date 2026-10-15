<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from './components/layout/Navbar.vue'
import Footer from './components/layout/Footer.vue'
import CourierNavbar from './components/courier/CourierNavbar.vue'
import CourierFooter from './components/courier/CourierFooter.vue'

const route = useRoute()
const isCourier = computed(
  () => route.path.startsWith('/courier') || route.path.startsWith('/kurir'),
)
const isChecker = computed(
  () =>
    route.path.startsWith('/checker') || route.path.startsWith('/pemeriksa'),
)
const isAdmin = computed(() => route.path.startsWith('/admin'))

</script>

<template>
  <!-- Checker, Admin, & Courier Dedicated Portal Layouts -->
  <div v-if="isChecker || isAdmin || isCourier" class="min-h-screen bg-[#F8FAFC] font-sans text-slate-800 antialiased selection:bg-rose-100 selection:text-rose-900">
    <router-view />
  </div>

  <!-- Courier or Vendor Role Layout -->
  <div
    v-else
    class="isolate min-h-screen flex flex-col font-sans text-on-surface antialiased"
    :class="
      isCourier
        ? 'bg-[#F8FAFC]'
        : 'bg-slate-50 selection:bg-primary-fixed selection:text-primary'
    "
  >
    <!-- Role-based Navigation Bar -->
    <CourierNavbar v-if="isCourier" />
    <Navbar v-else />

    <!-- Main Content Area with Top Padding for fixed Navbar -->
    <main
      class="w-full flex-1"
      :class="isCourier ? 'pt-20 bg-[#F8FAFC]' : 'pt-20 bg-slate-50'"
    >
      <div
        :class="
          isCourier
            ? 'max-w-6xl mx-auto px-4 sm:px-6 py-8'
            : 'max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-space-xl'
        "
      >
        <router-view />
      </div>
    </main>

    <!-- Role-based Global Footer -->
    <CourierFooter v-if="isCourier" />
    <Footer v-else />
  </div>
</template>
