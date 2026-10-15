<script setup lang="ts">
interface Props {
  isAccepted?: boolean
  activeTab?: number
}

const props = withDefaults(defineProps<Props>(), {
  isAccepted: false,
  activeTab: 1,
})

const emit = defineEmits<{
  (e: 'tab-change', tab: number): void
  (e: 'locked-tab-click', tab: number): void
}>()

function handleNavClick(tab: number) {
  if (tab === 1) {
    emit('tab-change', 1)
    window.scrollTo({ top: 0, behavior: 'smooth' })
    return
  }

  if (!props.isAccepted) {
    emit('locked-tab-click', tab)
    return
  }

  emit('tab-change', tab)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>

<template>
  <footer class="w-full bg-surface-container-low shadow-[0_1px_8px_rgba(0,0,0,0.04)] mt-space-xl border-t border-border-subtle/50">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 pt-space-xl pb-space-lg">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-space-xl pb-space-xl">
        <!-- Col 1: Brand & Desc (4 cols) -->
        <div class="lg:col-span-4 flex flex-col items-start gap-space-md">
          <div class="flex items-center gap-space-xs">
            <img
              alt="Anteraja Pharma"
              class="h-9 w-auto object-contain"
              src="/pharma-logo.webp"
            />
          </div>
          <p class="font-body-md text-body-md text-on-surface-variant max-w-sm leading-relaxed">
            Solusi pengiriman logistik pintar berbasis teknologi dengan armada Satria terpercaya untuk kebutuhan perorangan maupun korporasi di seluruh Indonesia.
          </p>
        </div>

        <!-- Col 2: Layanan (2 cols) -->
        <div class="lg:col-span-2 flex flex-col gap-space-md">
          <span class="font-headline-sm text-headline-sm text-on-surface font-bold">Layanan</span>
          <nav class="flex flex-col gap-space-sm font-body-md text-body-md text-on-surface-variant">
            <button
              type="button"
              @click="handleNavClick(1)"
              class="text-left hover:text-primary transition-colors cursor-pointer"
              :class="{ 'text-primary font-bold': activeTab === 1 }"
            >
              Tugas Masuk
            </button>
            <button
              type="button"
              @click="handleNavClick(2)"
              class="text-left transition-colors"
              :class="[
                !isAccepted
                  ? 'opacity-40 cursor-not-allowed text-slate-400'
                  : activeTab === 2
                    ? 'text-primary font-bold cursor-pointer'
                    : 'hover:text-primary cursor-pointer'
              ]"
            >
              Pengantaran &amp; Monitor
            </button>
            <button
              type="button"
              @click="handleNavClick(3)"
              class="text-left transition-colors"
              :class="[
                !isAccepted
                  ? 'opacity-40 cursor-not-allowed text-slate-400'
                  : activeTab === 3
                    ? 'text-primary font-bold cursor-pointer'
                    : 'hover:text-primary cursor-pointer'
              ]"
            >
              Lapor Kendala
            </button>
            <button
              type="button"
              @click="handleNavClick(4)"
              class="text-left transition-colors"
              :class="[
                !isAccepted
                  ? 'opacity-40 cursor-not-allowed text-slate-400'
                  : activeTab === 4
                    ? 'text-primary font-bold cursor-pointer'
                    : 'hover:text-primary cursor-pointer'
              ]"
            >
              Serah Terima &amp; Laporan
            </button>
          </nav>
        </div>

        <!-- Col 3: Bantuan & Hubungi (3 cols) -->
        <div class="lg:col-span-3 flex flex-col gap-space-md">
          <span class="font-headline-sm text-headline-sm text-on-surface font-bold">Bantuan &amp; Hubungi</span>
          <div class="flex flex-col gap-space-sm font-body-md text-body-md text-on-surface-variant">
            <div class="flex items-start gap-space-xs">
              <span class="material-symbols-outlined text-primary text-[20px] mt-0.5">share</span>
              <div class="flex flex-col">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold">X</span>
                <span class="font-body-md text-body-md text-on-surface font-semibold">@AnterajaCare</span>
              </div>
            </div>
            <div class="flex items-start gap-space-xs">
              <span class="material-symbols-outlined text-primary text-[20px] mt-0.5">call</span>
              <div class="flex flex-col">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold">Call center</span>
                <span class="font-headline-sm text-headline-sm text-on-surface font-bold">021 - 5066 - 3333</span>
              </div>
            </div>
            <div class="flex items-start gap-space-xs">
              <span class="material-symbols-outlined text-on-surface-variant text-[20px] mt-0.5">mail</span>
              <div class="flex flex-col">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold">Customer Service</span>
                <span class="font-body-md text-body-md text-on-surface">cs@anteraja.id</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Col 4: Sosial Media (3 cols) -->
        <div class="lg:col-span-3 flex flex-col gap-space-md">
          <span class="font-headline-sm text-headline-sm text-on-surface font-bold">Sosial Media</span>
          <div class="flex flex-col gap-space-sm pt-space-xs">
            <div class="flex items-center gap-2.5 flex-wrap">
              <!-- Facebook -->
              <a
                aria-label="Facebook Anteraja"
                class="w-9 h-9 rounded-full bg-[#EC008C] hover:opacity-90 transition-all flex items-center justify-center text-white shadow-sm"
                href="https://facebook.com/anteraja.id"
                rel="noopener noreferrer"
                target="_blank"
              >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                  <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"></path>
                </svg>
              </a>
              <!-- Instagram -->
              <a
                aria-label="Instagram Anteraja"
                class="w-9 h-9 rounded-full bg-[#EC008C] hover:opacity-90 transition-all flex items-center justify-center text-white shadow-sm"
                href="https://instagram.com/anteraja.id"
                rel="noopener noreferrer"
                target="_blank"
              >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"></path>
                </svg>
              </a>
              <!-- TikTok -->
              <a
                aria-label="TikTok Anteraja"
                class="w-9 h-9 rounded-full bg-[#EC008C] hover:opacity-90 transition-all flex items-center justify-center text-white shadow-sm"
                href="https://tiktok.com/@anteraja.id"
                rel="noopener noreferrer"
                target="_blank"
              >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                  <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.298-.002.595.042.88.13V9.4a6.33 6.33 0 0 0-1-.08A6.34 6.34 0 0 0 3 15.66a6.34 6.34 0 0 0 10.82 4.49 6.27 6.27 0 0 0 1.95-4.48V8.75a8.28 8.28 0 0 0 4.82 1.56V6.86a4.86 4.86 0 0 1-1-.17z"></path>
                </svg>
              </a>
              <!-- X Twitter -->
              <a
                aria-label="X Twitter Anteraja"
                class="w-9 h-9 rounded-full bg-[#EC008C] hover:opacity-90 transition-all flex items-center justify-center text-white shadow-sm"
                href="https://twitter.com/anteraja_id"
                rel="noopener noreferrer"
                target="_blank"
              >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                  <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path>
                </svg>
              </a>
              <!-- YouTube -->
              <a
                aria-label="YouTube Anteraja"
                class="w-9 h-9 rounded-full bg-[#EC008C] hover:opacity-90 transition-all flex items-center justify-center text-white shadow-sm"
                href="https://youtube.com/@anteraja"
                rel="noopener noreferrer"
                target="_blank"
              >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                  <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"></path>
                </svg>
              </a>
            </div>

            <!-- Download Badges -->
            <div class="flex flex-wrap items-center gap-2 pt-1">
              <a
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white hover:bg-neutral-800 transition-colors shadow-sm"
                href="#"
              >
                <svg class="w-5 h-5 fill-current shrink-0" viewBox="0 0 24 24">
                  <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 1.01-2.87-.9.04-2 .6-2.65 1.36-.57.66-.99 1.73-.95 2.76 1.01.08 1.97-.5 2.59-1.25z"></path>
                </svg>
                <div class="flex flex-col text-left">
                  <span class="text-[9px] uppercase leading-tight font-medium opacity-80">Download on the</span>
                  <span class="text-[13px] font-bold leading-tight tracking-tight">App Store</span>
                </div>
              </a>
              <a
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black text-white hover:bg-neutral-800 transition-colors shadow-sm"
                href="#"
              >
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24">
                  <path d="M3.609 1.814L13.793 12 3.61 22.186A2.22 2.22 0 0 1 3 20.615V3.385c0-.625.231-1.189.609-1.571z" fill="#EA4335"></path>
                  <path d="M17.556 8.238l-3.763 3.762 3.763 3.763 4.288-2.45c.744-.425.744-1.199 0-1.625l-4.288-2.45z" fill="#FBBC04"></path>
                  <path d="M3.609 1.814l10.184 10.186 3.763-3.762L5.808.825C5.074.407 4.254.85 3.609 1.814z" fill="#4285F4"></path>
                  <path d="M3.609 22.186l13.947-7.973-3.763-3.763L3.609 20.635c.191.684.717 1.252 1.399 1.551.353.155.734.204 1.109.138-.173-.024-.343-.07-.509-.138z" fill="#34A853"></path>
                </svg>
                <div class="flex flex-col text-left">
                  <span class="text-[9px] uppercase leading-tight font-medium opacity-80">GET IT ON</span>
                  <span class="text-[13px] font-bold leading-tight tracking-tight">Google Play</span>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Copyright Strip -->
      <div class="text-center text-sm text-on-surface-variant py-4 border-t border-border-subtle/50 mt-space-lg">
        © 2026 PT Tri Adi Bersama (Anteraja). Hak Cipta Dilindungi Undang-Undang.
      </div>
    </div>
  </footer>
</template>
