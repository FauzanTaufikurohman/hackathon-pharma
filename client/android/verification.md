# Hasil pemeriksaan Android — 15 Oktober 2026

## Lolos tanpa SDK

- `npm test`: **8 tes lolos**. Cakupan: URL API HTTPS, pemisahan adapter Android/web, nama berkas aman, pembersihan cache laporan lama, tautan luar, pemetaan error GPS, serta keamanan konfigurasi/manifest.
- `npm run type-check`: **lolos**, mencakup adapter Android dan source Vue bersama.
- `npm run build:web`: **lolos**. Tailwind memakai konfigurasi/content website yang sama; aset disimpan di `client/android/www`.
- `cap sync android`: **lolos**, enam plugin native terdeteksi. Ini menyalin aset/memperbarui konfigurasi plugin, **bukan menjalankan Gradle**.
- XML resource Android: berhasil di-parse. Referensi ikon, tema, izin lokasi, dan FileProvider diperiksa pada source.
- Preview login Chrome: lebar **320 / 375 / 390 / 768 px** tidak mengalami overflow horizontal. Kedua input memakai 16 px. Tidak ada exception JavaScript halaman.
- Safe area 24 px diuji sebagai simulasi CSS di browser, bukan hasil pengukuran notch perangkat fisik. Google Fonts sengaja diblokir pada smoke test untuk memastikan fallback font tetap terbaca.
- Website asli: **135 template Vue, 0 error**, type-check dan production build tetap lolos. Source website dan server tidak diubah oleh pekerjaan Android ini.
- Semua tambahan source berada di `client/android/`. Dependency, output build, path SDK, cache pemeriksaan, dan konfigurasi lokal tidak ikut Git.

## Belum dijalankan — perlu komputer teman

- Compile/debug APK, Gradle Sync Android Studio, dan instrumentation test `PharmaAppTest`.
- Instalasi HP; Back native, share sheet PDF/CSV, GPS/izin Android, pemilih foto, dan perilaku keyboard/status bar perangkat asli.
- Login/API end-to-end dari APK dengan URL HTTPS, database, CORS, dan akun tim yang benar.
- Pengujian Telegram/AI dari APK tetap memerlukan konfigurasi backend yang valid.

Tidak ada SDK/emulator yang diunduh dan tidak ada APK yang dikompilasi di laptop Evan. Ikuti checklist `how_to.md` sebelum membagikan APK; hasil build web bukan bukti seluruh fitur native sudah lulus tes perangkat.
