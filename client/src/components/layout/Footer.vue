<script setup lang="ts">
</script>

<template>
  <footer class="w-full bg-white border-t border-slate-200 pt-12 pb-8 mt-12">
    <div class="max-w-6xl mx-auto px-6 lg:px-8">
      <!-- 4-Column Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 pb-10 border-b border-slate-100">
        <!-- Col 1: Brand & Bio -->
        <div class="space-y-4">
          <img
            alt="Anteraja Pharma"
            class="h-8 w-auto object-contain"
            src="/pharma-logo.webp"
          />
          <p class="text-xs text-slate-500 leading-relaxed">
            Solusi pengiriman logistik cold chain farmasi pintar bertenaga IoT &amp; AI. Menjaga integritas suhu vaksin, serum, dan obat esensial ke seluruh fasilitas kesehatan Indonesia.
          </p>
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            <span class="material-symbols-outlined text-[16px] text-emerald-600">verified</span>
            <span>Sertifikasi CDOB BPOM Resmi</span>
          </div>
        </div>

        <!-- Col 2: Layanan -->
        <div class="space-y-3">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Layanan</h3>
          <ul class="space-y-2 text-xs text-slate-500">
            <li><router-link to="/paket-saya" class="hover:text-[#D81E5B] transition-colors">Anteraja Pharma Express</router-link></li>
            <li><router-link to="/paket-saya" class="hover:text-[#D81E5B] transition-colors">Cold Chain Chilled (2°C – 8°C)</router-link></li>
            <li><router-link to="/paket-saya" class="hover:text-[#D81E5B] transition-colors">Frozen Medical Chain (-20°C)</router-link></li>
            <li><router-link to="/" class="hover:text-[#D81E5B] transition-colors">Distribusi Rumah Sakit &amp; Klinik</router-link></li>
            <li><router-link to="/paket-saya" class="hover:text-[#D81E5B] transition-colors">Sensor Telemetri Suhu IoT Aktif</router-link></li>
          </ul>
        </div>

        <!-- Col 3: Bantuan & Hubungi -->
        <div class="space-y-3">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Bantuan &amp; Hubungi</h3>
          <ul class="space-y-2.5 text-xs text-slate-500">
            <li class="flex items-center gap-2">
              <span class="material-symbols-outlined text-slate-400 text-[16px]">call</span>
              <span class="font-medium text-slate-700">Call Center: (021) 5060 3333</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="material-symbols-outlined text-slate-400 text-[16px]">mail</span>
              <span class="font-medium text-slate-700">pharma-support@anteraja.id</span>
            </li>
            <li><a class="hover:text-[#D81E5B] transition-colors" href="#">Pusat Bantuan Apoteker &amp; RS</a></li>
            <li><a class="hover:text-[#D81E5B] transition-colors" href="#">Syarat &amp; Ketentuan Standar CDOB</a></li>
            <li><a class="hover:text-[#D81E5B] transition-colors" href="#">Kebijakan Privasi Pasien &amp; Faskes</a></li>
          </ul>
        </div>

        <!-- Col 4: Ikuti & Unduh Aplikasi -->
        <div class="space-y-4">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Ikuti &amp; Unduh Aplikasi</h3>
          <!-- Social Icons -->
          <div class="flex items-center gap-2">
            <a class="w-8 h-8 rounded-full bg-rose-50 text-[#D81E5B] flex items-center justify-center hover:bg-[#D81E5B] hover:text-white transition-all" href="#" title="Instagram">
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"></path></svg>
            </a>
            <a class="w-8 h-8 rounded-full bg-rose-50 text-[#D81E5B] flex items-center justify-center hover:bg-[#D81E5B] hover:text-white transition-all" href="#" title="LinkedIn">
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"></path></svg>
            </a>
            <a class="w-8 h-8 rounded-full bg-rose-50 text-[#D81E5B] flex items-center justify-center hover:bg-[#D81E5B] hover:text-white transition-all" href="#" title="YouTube">
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"></path></svg>
            </a>
          </div>
          <!-- App Store Download Badges -->
          <div class="flex flex-col gap-2 pt-1">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-900 text-white cursor-pointer hover:bg-slate-800 transition-all w-fit">
              <span class="material-symbols-outlined text-[20px]">play_arrow</span>
              <div>
                <p class="text-[9px] uppercase tracking-wider text-slate-400 leading-none">Temukan di</p>
                <p class="text-xs font-semibold leading-tight">Google Play</p>
              </div>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-900 text-white cursor-pointer hover:bg-slate-800 transition-all w-fit">
              <span class="material-symbols-outlined text-[20px]">phone_iphone</span>
              <div>
                <p class="text-[9px] uppercase tracking-wider text-slate-400 leading-none">Unduh di</p>
                <p class="text-xs font-semibold leading-tight">App Store</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Copyright -->
      <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
        <p>© 2026 PT Tri Adi Bersama (Anteraja). Hak Cipta Dilindungi Undang-Undang.</p>
        <div class="flex items-center gap-4">
          <a class="hover:text-slate-600" href="#">Keamanan Data</a>
          <span>•</span>
          <a class="hover:text-slate-600" href="#">Audit Cold-Chain</a>
          <span>•</span>
          <a class="hover:text-slate-600" href="#">ISO 9001:2015</a>
        </div>
      </div>
    </div>
  </footer>
</template>
