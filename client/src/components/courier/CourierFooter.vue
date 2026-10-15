<script setup lang="ts">
interface Props {
  isAccepted?: boolean
}

withDefaults(defineProps<Props>(), {
  isAccepted: false,
})

const emit = defineEmits<{
  (e: 'tab-change', tab: number): void
  (e: 'locked-tab-click', tab: number): void
}>()

function handleNavClick(tab: number, isAcceptedVal: boolean) {
  if (tab === 1) {
    emit('tab-change', 1)
    window.scrollTo({ top: 0, behavior: 'smooth' })
    return
  }

  if (!isAcceptedVal) {
    emit('locked-tab-click', tab)
    return
  }

  emit('tab-change', tab)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>

<template>
  <footer class="w-full bg-white border-t border-slate-100 pt-12 pb-8 px-4 sm:px-6 lg:px-8 mt-12">
    <div class="max-w-6xl mx-auto">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-10 border-b border-slate-100">
        <!-- Col 1: Brand & Desc -->
        <div class="space-y-4">
          <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-[#D81E5B] flex items-center justify-center text-white font-bold text-sm">
              <span class="material-symbols-outlined text-base">local_shipping</span>
            </span>
            <span class="font-bold text-lg text-[#D81E5B] tracking-tight">anteraja</span>
            <span class="text-[9px] bg-pink-100 text-[#D81E5B] font-bold px-1.5 py-0.5 rounded">PHARMA</span>
          </div>
          <p class="text-xs text-slate-500 leading-relaxed">
            Layanan logistik rantai dingin farmasi berteknologi presisi tinggi untuk rumah sakit dan fasilitas kesehatan.
          </p>
          <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-slate-50 border border-slate-200 text-slate-600 text-[11px] font-mono font-semibold">
            <span class="material-symbols-outlined text-emerald-600 text-sm">verified</span>
            <span>CDOB BPOM Certified</span>
          </div>
        </div>

        <!-- Col 2: Navigasi Satria -->
        <div class="space-y-3">
          <h5 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Navigasi Satria</h5>
          <ul class="space-y-2 text-xs text-slate-600 font-medium">
            <li>
              <button
                type="button"
                @click="handleNavClick(1, isAccepted)"
                class="hover:text-[#D81E5B] transition-colors cursor-pointer text-left"
              >
                Tugas Masuk
              </button>
            </li>
            <li>
              <button
                type="button"
                @click="handleNavClick(2, isAccepted)"
                class="transition-colors text-left"
                :class="!isAccepted ? 'text-slate-400 opacity-60 cursor-not-allowed' : 'hover:text-[#D81E5B] cursor-pointer'"
              >
                Pengantaran &amp; Monitor
              </button>
            </li>
            <li>
              <button
                type="button"
                @click="handleNavClick(3, isAccepted)"
                class="transition-colors text-left"
                :class="!isAccepted ? 'text-slate-400 opacity-60 cursor-not-allowed' : 'hover:text-[#D81E5B] cursor-pointer'"
              >
                Lapor Kendala
              </button>
            </li>
            <li>
              <button
                type="button"
                @click="handleNavClick(4, isAccepted)"
                class="transition-colors text-left"
                :class="!isAccepted ? 'text-slate-400 opacity-60 cursor-not-allowed' : 'hover:text-[#D81E5B] cursor-pointer'"
              >
                Serah Terima &amp; POD
              </button>
            </li>
          </ul>
        </div>

        <!-- Col 3: Bantuan Operasional -->
        <div class="space-y-3">
          <h5 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Bantuan Operasional</h5>
          <ul class="space-y-2 text-xs text-slate-600">
            <li><span class="font-medium text-slate-700">Care:</span> @AnterajaCare</li>
            <li><span class="font-medium text-slate-700">Call Center:</span> (021) 5066 3333</li>
            <li><span class="font-medium text-slate-700">Email:</span> pharma-support@anteraja.id</li>
            <li class="inline-flex items-center gap-1 text-[#D81E5B] font-semibold text-[11px] bg-pink-50 px-2 py-0.5 rounded-full mt-1">
              <span class="material-symbols-outlined text-xs">headset_mic</span>
              <span>Hotline Farmasi 24 Jam</span>
            </li>
          </ul>
        </div>

        <!-- Col 4: Sosial Media & Unduh -->
        <div class="space-y-3">
          <h5 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Sosial Media &amp; Unduh</h5>
          <div class="flex items-center gap-2">
            <a class="w-8 h-8 rounded-full bg-pink-50 text-[#D81E5B] hover:bg-[#D81E5B] hover:text-white transition-all flex items-center justify-center text-xs font-bold shadow-sm" href="#">ig</a>
            <a class="w-8 h-8 rounded-full bg-pink-50 text-[#D81E5B] hover:bg-[#D81E5B] hover:text-white transition-all flex items-center justify-center text-xs font-bold shadow-sm" href="#">fb</a>
            <a class="w-8 h-8 rounded-full bg-pink-50 text-[#D81E5B] hover:bg-[#D81E5B] hover:text-white transition-all flex items-center justify-center text-xs font-bold shadow-sm" href="#">in</a>
          </div>
          <div class="space-y-1.5 pt-1">
            <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-900 text-white rounded-lg text-left shadow-sm cursor-pointer hover:bg-slate-800 transition-colors">
              <span class="material-symbols-outlined text-xl">shop</span>
              <div>
                <div class="text-[8px] uppercase text-slate-300 leading-none">Temukan di</div>
                <div class="text-[11px] font-bold leading-tight">Google Play</div>
              </div>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-900 text-white rounded-lg text-left shadow-sm cursor-pointer hover:bg-slate-800 transition-colors">
              <span class="material-symbols-outlined text-xl">phone_iphone</span>
              <div>
                <div class="text-[8px] uppercase text-slate-300 leading-none">Unduh di</div>
                <div class="text-[11px] font-bold leading-tight">App Store</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="pt-6 text-center text-xs text-slate-400 font-medium">
        © 2026 PT Tri Adi Bersama (Anteraja). Hak Cipta Dilindungi Undang-Undang.
      </div>
    </div>
  </footer>
</template>
