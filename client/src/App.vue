<script setup lang="ts">
import Navbar from './components/layout/Navbar.vue'
import Footer from './components/layout/Footer.vue'
</script>

<template>
  <div class="min-h-screen flex flex-col bg-surface font-sans text-on-surface antialiased selection:bg-primary-fixed selection:text-primary">
    <!-- Sticky Navigation Bar -->
    <Navbar />

    <!-- Main Content Area with Top Padding for fixed Navbar -->
    <main class="w-full flex-1 pt-16 bg-surface">
      <div class="max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-space-xl">
        <router-view />
      </div>
    </main>

    <!-- Global Footer -->
    <Footer />
  </div>
</template>
