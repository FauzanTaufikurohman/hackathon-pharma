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
    if (route.path !== '/paket-saya') {
      router.push({ path: '/paket-saya', query: { q: searchInput.value.trim() } })
    }
  }
}
</script>

<template>
  <header class="fixed top-0 left-0 right-0 z-50 bg-surface/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-surface-container/40">
    <div class="h-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-space-md">
      <!-- Left: Logo & Navigation Tabs -->
      <div class="flex items-center gap-space-lg lg:gap-space-xl">
        <!-- Official Brand Logo -->
        <router-link
          to="/"
          class="flex items-center gap-space-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-lg group shrink-0"
          title="Anteraja Pharma Cold-Chain Logistics"
        >
          <img
            alt="Anteraja Pharma"
            class="h-9 md:h-10 w-auto object-contain transition-transform group-hover:scale-105"
            src="/images/anteraja-pharma-logo.png"
          />
        </router-link>

        <!-- Navigation Tabs -->
        <nav class="flex items-center gap-space-xs sm:gap-space-sm">
          <router-link
            to="/"
            class="font-label-lg text-label-lg transition-colors py-2 px-3.5 sm:px-4 rounded-full"
            :class="route.path === '/' || route.path === '/vendor'
              ? 'text-primary font-bold bg-primary-fixed/40 shadow-2xs'
              : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low'"
          >
            Beranda
          </router-link>
          <router-link
            to="/paket-saya"
            class="font-label-lg text-label-lg transition-colors py-2 px-3.5 sm:px-4 rounded-full"
            :class="route.path === '/paket-saya'
              ? 'text-primary font-bold bg-primary-fixed/40 shadow-2xs'
              : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low'"
          >
            Lacak Kiriman
          </router-link>
        </nav>
      </div>

      <!-- Right: Search Input & Profile Badge -->
      <div class="flex items-center gap-space-md">
        <!-- Search bar in header -->
        <div class="relative hidden md:block w-60 lg:w-72">
          <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline text-[18px] pointer-events-none">
            search
          </span>
          <input
            v-model="searchInput"
            @keyup.enter="handleHeaderSearch"
            type="text"
            placeholder="Lacak No. AWB / Resi Klinis..."
            class="w-full h-10 pl-10 pr-3.5 bg-surface-container-low border border-outline-variant/60 rounded-full font-body-sm text-[13px] text-on-surface placeholder:text-outline focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all shadow-inner"
          />
        </div>

        <!-- Facility Info & Profile -->
        <div class="flex items-center gap-space-sm pl-space-xs border-l border-surface-container-high/80">
          <div class="flex flex-col items-end hidden sm:flex">
            <div class="flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
              <span class="font-label-md text-label-md text-on-surface font-bold leading-tight">
                {{ currentFacility.name }}
              </span>
            </div>
            <span class="font-label-sm text-[10px] text-secondary uppercase font-extrabold tracking-wider">
              {{ currentFacility.verified_status }}
            </span>
          </div>

          <div class="relative shrink-0">
            <img
              alt="Profile Faskes"
              class="w-9 h-9 rounded-full object-cover ring-2 ring-primary/20 shadow-xs"
              src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&q=80&w=120"
            />
            <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-secondary ring-2 ring-surface-container-lowest"></span>
          </div>

          <!-- Switch to other roles quick link -->
          <div class="hidden xl:flex items-center gap-1.5 pl-2">
            <router-link
              to="/courier"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-primary font-label-sm text-[11px] font-semibold transition-all border border-surface-container"
              title="Beralih ke Portal Kurir"
            >
              <span class="material-symbols-outlined text-[14px]">two_wheeler</span>
              <span>Kurir</span>
            </router-link>
            <router-link
              to="/checker"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-primary font-label-sm text-[11px] font-semibold transition-all border border-surface-container"
              title="Beralih ke Portal Checker QC"
            >
              <span class="material-symbols-outlined text-[14px]">verified</span>
              <span>Checker</span>
            </router-link>
            <router-link
              to="/admin"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-primary font-label-sm text-[11px] font-semibold transition-all border border-surface-container"
              title="Beralih ke Portal Admin"
            >
              <span class="material-symbols-outlined text-[14px]">admin_panel_settings</span>
              <span>Admin</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>
