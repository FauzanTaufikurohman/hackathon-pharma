# Cara mencoba dan membuat APK Anteraja Pharma

Versi Android memakai **Vue yang sama dengan website**, dibungkus Capacitor 8. Backend tetap Laravel dan database yang sama. Tidak ada backend Java baru dan tidak ada pilihan role palsu saat login.

**Di laptop Evan: SDK, emulator, Gradle, dan compile APK tidak dijalankan.** Source Android sudah disiapkan; teman yang punya SDK perlu melakukan langkah compile dan pengujian HP di bawah.

## 1. Ambil source lengkap

- Gunakan branch `client-implement-android` setelah branch tersebut tersedia di remote.
- Ambil repository lengkap, bukan folder Android saja. Source tampilan ada di `client/src`.
- Semua tambahan khusus Android ada di `client/android`. Website dan server tidak perlu dipindahkan.
- Jalankan terminal dari folder repository, lalu:

```powershell
cd client
npm ci
cd android
npm ci
```

- Gunakan Node.js **24.12 atau lebih baru**. Cek dengan `node -v`.
- `npm ci` hanya memasang dependency JavaScript. Perintah ini tidak mengunduh Android SDK atau membuat APK.
- Untuk melihat UI tanpa APK, lanjut sampai bagian 4 saja.

## 2. Isi alamat backend yang dapat dibuka HP

- Dari `client/android`, buat file konfigurasi lokal:

```powershell
Copy-Item .env.example .env.local
```

- Buka `.env.local`, ganti contoh URL dengan alamat HTTPS Laravel yang benar:

```dotenv
ANDROID_API_BASE_URL=https://ALAMAT-BACKEND-MILIK-TIM/api/v1
```

- Ganti `ALAMAT-BACKEND-MILIK-TIM`; jangan menyalin placeholder mentah-mentah.
- Backend harus aktif dan memakai database yang benar. Akun login sama seperti website, dari seeder/database tim.
- Jangan pakai `127.0.0.1` atau `localhost`: dari HP, itu berarti HP sendiri, **bukan laptop yang menjalankan Laravel**.
- Jika Laravel masih di laptop, gunakan tunnel HTTPS aktif. URL tunnel bisa berubah setelah tunnel dimatikan. Ganti konfigurasi lalu build ulang jika berubah.
- APK menyimpan alamat API, **bukan** password database, OpenAI key, atau Telegram token. Rahasia itu tetap di `server/.env`.
- Jangan masukkan `.env.local` ke Git. File ini sudah diabaikan.

## 3. Izinkan asal aplikasi Android di Laravel

- Android memuat UI lokal dengan origin `https://localhost`.
- Pada komputer/server yang menjalankan Laravel, tambahkan origin tersebut ke `CORS_ALLOWED_ORIGINS` di `server/.env`.
- Kalau sudah ada origin lain, pertahankan dan pisahkan dengan koma. Contoh:

```dotenv
CORS_ALLOWED_ORIGINS=https://alamat-web-tim.example,https://localhost
```

- Dari folder `server`, bersihkan cache konfigurasi:

```powershell
php artisan config:clear
```

- Tidak perlu mengubah endpoint, mengizinkan semua origin, mematikan HTTPS, atau membuat database Android baru.
- Login tetap memakai token Sanctum. Role dan izin diperiksa Laravel, bukan ditentukan pengguna di layar login.
- Telegram/AI tetap diproses backend; kredensialnya tidak boleh dimasukkan ke aplikasi Android.

## 4. Cek kode dan preview tanpa SDK

- Dari `client/android`, jalankan berurutan:

```powershell
npm test
npm run type-check
npm run build:web
npm run preview
```

- Buka `http://127.0.0.1:5188/login`.
- Tekan F12 dan pilih mode HP, coba lebar 375 px dan 390 px.
- Coba login, navigasi, pencarian, detail paket, pesan error, dan logout.
- Backend perlu mengizinkan origin preview `http://127.0.0.1:5188` jika ingin tes login dari preview. Tambahkan pada daftar CORS di backend, jangan mengganti origin yang sudah ada.
- Tekan `Ctrl+C` untuk menghentikan preview.
- Preview browser **bukan pengujian native**. GPS memakai izin browser; tombol Back Android dan menu berbagi dokumen baru bisa dipastikan di APK.
- Jika mengubah tampilan website, edit source di `client/src`, lalu ulangi `npm run build:web`. Tidak perlu menyalin file Vue ke folder Android.

## 5. Compile APK di komputer teman yang punya SDK

- Langkah ini **hanya untuk komputer yang siap Android development**.
- Siapkan Android Studio **2025.2.1 atau lebih baru**, Android SDK Platform **36**, SDK Build Tools yang diminta Gradle, dan JDK **21**.
- Gunakan JDK 21 pada Gradle JDK di Android Studio. Cek terminal dengan `java -version` jika memakai terminal untuk build.
- HP minimal Android **7.0 / API 24**. Tidak perlu emulator jika memakai HP fisik.
- Dari `client/android`, jalankan:

```powershell
npm run sync
npm run open
```

- `sync` membangun aset web dan menyalinnya ke proyek native. Ini **belum** compile APK.
- `open` membuka proyek `client/android/native` di Android Studio. Di komputer teman, Gradle bisa mengunduh dependency yang belum tersedia.
- Tunggu Gradle Sync selesai. Jika Android Studio meminta SDK Location, pilih SDK yang sudah terpasang di komputer teman.
- Untuk langsung menjalankan: hubungkan HP dengan USB debugging aktif, pilih perangkat, lalu klik tombol **Run** di Android Studio.
- Untuk membuat file APK dari terminal PowerShell, setelah `npm run sync` berhasil:

```powershell
$env:ANDROID_ALLOW_COMPILE = 'yes'
npm run apk:debug
Remove-Item Env:ANDROID_ALLOW_COMPILE
```

- Compile sengaja dikunci sampai `ANDROID_ALLOW_COMPILE=yes`, supaya tidak sengaja memakai storage komputer yang belum siap.
- Alternatif dari folder `client/android/native`: `./gradlew.bat assembleDebug` di Windows atau `./gradlew assembleDebug` di macOS/Linux. Ini benar-benar compile; jangan jalankan di laptop Evan.
- File APK hasil build berada di:

```text
client/android/native/app/build/outputs/apk/debug/app-debug.apk
```

- Debug APK untuk tes internal, bukan rilis Play Store. Rilis produksi perlu signing key sendiri; jangan commit keystore atau password signing.

## 6. Pasang dan tes di HP

- Kirim `app-debug.apk` ke HP, buka, lalu izinkan pemasangan dari sumber yang dipakai jika Android meminta.
- Atau, jika `adb` sudah tersedia di komputer teman, dari `client/android`:

```powershell
adb devices
adb install -r native/app/build/outputs/apk/debug/app-debug.apk
```

- Saat `adb devices` menampilkan `unauthorized`, terima dialog izin USB debugging di HP.
- Buka **Anteraja Pharma** dari launcher. Login dengan akun tim yang benar; jangan membuat akun admin baru sembarangan.
- Setelah mengganti source Vue atau URL API: `npm run sync`, compile lagi, lalu pasang ulang APK.
- Android WebView/Chrome di HP sebaiknya diperbarui. Aplikasi memerlukan internet untuk data; bukan aplikasi offline penuh.

## 7. Checklist tes sebelum dibagikan

- [ ] Login password salah: ada pesan error, tidak masuk dashboard.
- [ ] Login Admin, Checker, Courier, dan Vendor memakai akun masing-masing: masuk portal yang tepat.
- [ ] Akun non-admin tidak dapat mengakses data admin melalui URL/API.
- [ ] Menu HP bisa dibuka dan dipilih. Tombol Back menutup menu dulu; dari detail kembali ke daftar.
- [ ] Pada halaman awal, Back menawarkan keluar; pembatalan tidak menutup aplikasi.
- [ ] Cari resi yang ada dan yang tidak ada; tidak muncul data paket buatan.
- [ ] Vendor hanya melihat paketnya; detail, peta, laporan, dan kesiapan penerima tetap berfungsi sesuai izin.
- [ ] Checker bisa membuka antrean dan mengirim pemeriksaan sesuai urutan status paket.
- [ ] Courier bisa membuka tugas; status, pemeriksaan, kendala, dan serah terima mengikuti validasi server.
- [ ] Tombol GPS meminta izin saat dipakai, bukan saat login. Tes izin ditolak, GPS mati, dan izin lokasi perkiraan.
- [ ] Upload foto/bukti dari pemilih file HP berhasil; format/ukuran salah menampilkan validasi.
- [ ] PDF/CSV membuka menu berbagi Android; coba simpan ke Files atau buka dengan aplikasi yang sesuai.
- [ ] Logout lalu tekan Back: data privat tidak bisa dibuka lagi tanpa login.
- [ ] Matikan internet: ada peringatan/pesan koneksi; aplikasi tidak menampilkan sukses palsu.
- [ ] Internet aktif lagi: muat ulang data lewat navigasi/tombol yang tersedia; jangan mengulang aksi simpan sebelum mengecek status server.
- [ ] Keyboard tidak menutupi input aktif/tombol, teks tetap bisa diperbesar, tidak ada elemen terpotong pada HP portrait/landscape.
- [ ] Notch/status bar/tombol navigasi HP tidak menutupi header atau isi halaman.
- [ ] Telegram benar-benar sampai ke akun uji jika backend Telegram sudah dikonfigurasi. APK tidak menggantikan setup bot di server.

## 8. Kalau ada kendala

- **Node terlalu lama:** gunakan Node 24.12+. Jangan ubah versi dependency asal-asalan.
- **ANDROID_API_BASE_URL belum diisi:** periksa `.env.local` di `client/android`, bukan `.env` Laravel.
- **URL tunnel berubah:** isi URL baru, ulangi `sync` dan compile APK.
- **Login gagal / Failed to fetch:** pastikan Laravel aktif, HTTPS valid, HP punya internet, serta CORS mengizinkan `https://localhost`. Cek log Laravel tanpa membagikan token/password.
- **401:** sesi habis/password salah. Login ulang; ini bukan alasan mematikan pemeriksaan auth.
- **403:** role akun tidak berhak mengakses endpoint. Jangan mengganti role lewat frontend.
- **SDK location not found:** hanya di komputer teman, pilih SDK di Android Studio. `native/local.properties` bersifat lokal dan tidak ikut Git.
- **Java version mismatch:** pilih Gradle JDK 21.
- **Plugin not implemented / layar tidak sesuai update:** jalankan `npm run sync` sebelum compile ulang.
- **GPS gagal:** hidupkan lokasi HP, cek izin lokasi aplikasi, coba dekat jendela. Tidak ada koordinat palsu sebagai fallback.
- **Tidak bisa membuka PDF:** pastikan ada aplikasi pembaca PDF atau pilih simpan ke Files lewat menu berbagi. Berkas sementara berada di cache privat aplikasi.
- **Install gagal karena signature berbeda:** gunakan APK dari signing key yang sama. Uninstall adalah pilihan terakhir karena menghapus data lokal/sesi aplikasi.
- **Butuh log Android:** di Android Studio buka Logcat, pilih perangkat dan filter `package:com.anteraja.pharma`. Kirim pesan error relevan saja, jangan token/isi data pribadi.

## 9. File yang boleh diubah

- `../src`: tampilan dan fitur bersama website/Android.
- `mobile.css`: penyesuaian safe area, ukuran sentuh, dan keyboard khusus HP.
- `native.ts`: tombol Back, lokasi, peringatan offline, dan tautan luar.
- `api.ts`: menyimpan laporan sementara lalu membuka menu berbagi Android; autentikasi tetap menggunakan layanan API bersama.
- `.env.local`: alamat backend untuk build Android di komputermu.
- `capacitor.config.json`: nama aplikasi, package ID, lokasi proyek native, dan kebijakan HTTPS.
- `native/app/src/main/AndroidManifest.xml`: izin Android. Jangan menambah izin yang tidak diperlukan.
- `native/app/src/main/res`: ikon, splash, dan tema native.
- `vite.config.mjs`: build khusus Android dari source web yang sama.
- `scripts`: validasi konfigurasi, build web, dan compile yang harus diaktifkan secara eksplisit.
- `test`: tes otomatis konfigurasi dan aturan adapter; bukan pengganti tes HP.
- Jangan edit `www`, `native/app/src/main/assets/public`, atau `node_modules`: semuanya hasil build/dependency.

## 10. Status verifikasi dan batasan

- Pengujian otomatis memeriksa endpoint HTTPS, aturan file laporan, pemilihan adapter, tautan luar, kode error GPS, dan konfigurasi Capacitor.
- Type-check dan build web dapat dilakukan tanpa SDK. Hasil pemeriksaan dicatat di `verification.md`.
- **APK belum dikompilasi dan perangkat fisik belum diuji di laptop Evan**, sesuai permintaan untuk menghemat storage. Checklist HP di atas wajib dikerjakan teman sebelum menyatakan siap dibagikan.
- Tidak ada janji aplikasi bebas semua bug sebelum pengujian perangkat dan backend yang dipakai tim.
- Referensi resmi: [Capacitor Android](https://capacitorjs.com/docs/android), [konfigurasi](https://capacitorjs.com/docs/config), [Geolocation](https://capacitorjs.com/docs/apis/geolocation), dan [Share](https://capacitorjs.com/docs/apis/share).
