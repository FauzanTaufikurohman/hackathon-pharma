<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from './components/layout/Navbar.vue'
import Footer from './components/layout/Footer.vue'
import CourierNavbar from './components/courier/CourierNavbar.vue'
import CourierFooter from './components/courier/CourierFooter.vue'

const route = useRoute()
const isCourier = computed(() => route.path.startsWith('/courier') || route.path.startsWith('/kurir'))
const isChecker = computed(() => route.path.startsWith('/checker') || route.path.startsWith('/pemeriksa'))
const isAdmin = computed(() => route.path.startsWith('/admin'))
const isLogin = computed(() => route.name === 'login')
const isCourierTaskDetail = computed(() => route.name === 'courier-task-detail')
</script>

<template>
  <!-- Dedicated role portal layouts -->
  <div v-if="isChecker || isAdmin || isLogin || isCourierTaskDetail" class="min-h-screen bg-surface font-sans text-on-surface antialiased selection:bg-primary-fixed selection:text-on-primary-fixed">
    <router-view />
  </div>

  <!-- Courier or Vendor Role Layout -->
  <div
    v-else
    class="min-h-screen flex flex-col font-sans text-on-surface antialiased"
    :class="isCourier ? 'bg-[#F8FAFC]' : 'bg-surface selection:bg-primary-fixed selection:text-primary'"
  >
    <!-- Role-based Navigation Bar -->
    <CourierNavbar v-if="isCourier" />
    <Navbar v-else />

    <!-- Main Content Area with Top Padding for fixed Navbar -->
    <main
      class="w-full flex-1"
      :class="isCourier ? 'pt-20 bg-[#F8FAFC]' : 'pt-16 bg-surface'"
    >
      <div
        :class="isCourier ? 'max-w-6xl mx-auto px-4 sm:px-6 py-8' : 'max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-space-xl'"
      >
        <router-view />
      </div>
    </main>

    <!-- Role-based Global Footer -->
    <CourierFooter v-if="isCourier" />
    <Footer v-else />
  </div>
</template>
