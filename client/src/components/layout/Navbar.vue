<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { currentFacility } from '../../data/mockShipments'

const router = useRouter()
const route = useRoute()
const searchInput = ref('')

const emit = defineEmits<{
  (e: 'search', query: string): void
}>()

function handleHeaderSearch() {
  if (searchInput.value.trim()) {
    emit('search', searchInput.value.trim())
  }
}
</script>

<template>
  <header class="fixed top-0 left-0 right-0 z-50 bg-surface-container-lowest/95 backdrop-blur-md border-b border-surface-container shadow-xs">
    <div class="h-16 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-space-md">
      <!-- Left: Brand Logo & Navigation -->
      <div class="flex items-center gap-space-md lg:gap-space-lg">
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-2.5 group">
          <div class="w-9 h-9 rounded-lg bg-primary flex items-center justify-center text-on-primary shadow-sm group-hover:scale-105 transition-transform">
            <span class="material-symbols-outlined text-[20px]">ac_unit</span>
          </div>
          <div class="flex flex-col">
            <span class="font-headline-sm text-[16px] font-bold text-on-surface leading-none tracking-tight">anteraja</span>
            <span class="font-label-sm text-[10px] text-primary uppercase tracking-wider font-extrabold mt-0.5">Pharma Cold-Chain</span>
          </div>
        </router-link>

        <!-- Navigation Tabs -->
        <nav class="flex items-center gap-1 bg-surface-container-low p-1 rounded-full">
          <router-link
            to="/"
            class="px-4 sm:px-5 py-1.5 sm:py-2 rounded-full text-label-md font-bold transition-all duration-200"
            :class="route.path === '/' || route.path === '/vendor' ? 'bg-primary-container text-on-primary shadow-xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high'"
          >
            Beranda
          </router-link>
          <router-link
            to="/paket-saya"
            class="px-4 sm:px-5 py-1.5 sm:py-2 rounded-full text-label-md font-bold transition-all duration-200"
            :class="route.path === '/paket-saya' ? 'bg-primary-container text-on-primary shadow-xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high'"
          >
            Paket Saya
          </router-link>
        </nav>
      </div>

      <!-- Right: Search Input & Profile Badge -->
      <div class="flex items-center gap-space-md">
        <!-- Search bar in header -->
        <div class="relative hidden md:block w-64 lg:w-72">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px] pointer-events-none">search</span>
          <input
            v-model="searchInput"
            @keyup.enter="handleHeaderSearch"
            type="text"
            placeholder="Lacak No. AWB / Resi Klinis..."
            class="w-full h-9 pl-9 pr-3 bg-surface-container-low border border-outline-variant/60 rounded-full font-body-sm text-[13px] text-on-surface placeholder:text-outline focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/30 transition-all"
          />
        </div>

        <!-- Facility Info & Profile -->
        <div class="flex items-center gap-space-sm pl-space-sm border-l border-surface-container-high">
          <div class="flex flex-col items-end hidden sm:flex">
            <div class="flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
              <span class="font-label-md text-label-md text-on-surface font-bold">{{ currentFacility.name }}</span>
            </div>
            <span class="font-label-sm text-[10px] text-secondary uppercase font-extrabold tracking-wider">{{ currentFacility.verified_status }}</span>
          </div>
          <div class="relative">
            <img
              alt="Profile Faskes"
              class="w-8 h-8 rounded-full object-cover ring-2 ring-primary/20 shadow-xs"
              src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&q=80&w=120"
            />
            <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-secondary ring-2 ring-surface-container-lowest"></span>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>
