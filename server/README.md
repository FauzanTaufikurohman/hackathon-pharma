# Menjalankan backend Anteraja Pharma v2

Backend vendor dan courier memakai Laravel 13, PHP 8.4, PostgreSQL lokal dan Sanctum 4. Client Vue milik tim tidak diubah. API prefix: /api/v1.

## Setup pada komputer baru

Sediakan PHP 8.4 dengan pdo_pgsql, pdo_sqlite, mbstring, openssl, fileinfo, gd dan zip; Composer 2; PostgreSQL lokal. Jalankan dari folder server:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Isi DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD di .env sesuai PostgreSQL kamu. Buat database pharma_v2 terlebih dahulu menggunakan pgAdmin atau createdb. Jangan memakai database proyek lain.

```powershell
php artisan migrate
php artisan db:seed
php artisan serve --host=127.0.0.1 --port=8180
```

APP_URL harus sesuai alamat backend; FRONTEND_URL sesuai origin client. Seeder hanya di local/testing dan tidak menimpa kiriman yang sudah ada. Data demo diberi is_demo=true; lokasi/suhu contoh ditandai is_simulated=true.

## Command pada laptop Evan

PHP portable disiapkan di Backups/Pharma-v2-runtime, bukan dalam repo. PostgreSQL Pharma v2 berjalan di 127.0.0.1:5544, terpisah dari Odoo. .env lokal sudah terisi; jangan menimpanya dengan .env.example.

```powershell
cd "D:\Work & Organization\Work\Maxy Academy\Modules\Hackathon\anteraja-pharma-v2\server"
& "D:\Work & Organization\Work\Maxy Academy\Modules\Backups\Pharma-v2-runtime\php84\php.exe" artisan serve --host=127.0.0.1 --port=8180
```

Buka terminal kedua untuk client; ini masih frontend mock sampai integrator tim menyambungkan endpoint:

```powershell
cd "D:\Work & Organization\Work\Maxy Academy\Modules\Hackathon\anteraja-pharma-v2\client"
npm install
npm run dev
```

## Akun demo lokal

| Email | Role | Password |
| --- | --- | --- |
| vendor@pharma.test | buyer/vendor penerima | PharmaVendor!2026 |
| vendor.lain@pharma.test | buyer lain, tes isolasi | PharmaVendor!2026 |
| admin@pharma.test | admin | PharmaAdmin!2026 |
| checker@pharma.test | checker | PharmaChecker!2026 |
| satria@pharma.test | courier | PharmaKurir!2026 |

Password demo ditentukan di database/seeders/DatabaseSeeder.php. Seeder tidak mereset password akun existing. Jangan pakai akun demo di produksi.

## Uji endpoint dengan PowerShell

```powershell
$api = 'http://127.0.0.1:8180/api/v1'
$login = Invoke-RestMethod "$api/auth/login" -Method Post -ContentType 'application/json' -Body '{"email":"vendor@pharma.test","password":"PharmaVendor!2026"}'
$headers = @{ Authorization = "Bearer $($login.token)"; Accept = 'application/json' }
Invoke-RestMethod "$api/vendor/packages?category=all&per_page=20" -Headers $headers
Invoke-WebRequest "$api/vendor/packages/export" -Headers $headers -OutFile "$env:TEMP\rekap-pharma.pdf"
```

## Tes dan format

```powershell
php artisan test --compact
php vendor/bin/pint --dirty --format agent
```

Default tes memakai SQLite in-memory. Untuk uji PostgreSQL buat database terpisah berakhiran _test, jangan database aplikasi:

```powershell
$env:DB_CONNECTION='pgsql'
$env:DB_DATABASE='pharma_v2_test'
php artisan test --compact
Remove-Item Env:DB_CONNECTION,Env:DB_DATABASE
```

## Kontrak dan branch

Lihat [kontrak paket](docs/vendor-packages-api.md), [dashboard](docs/vendor-dashboard-api.md), dan [monitor](docs/vendor-monitor-api.md). Branch lokal bertahap: server/vendor-packages → server/vendor-dashboard → server/vendor-shipment-detail-monitor. Tiap branch berisi client dan server lengkap; branch berikutnya mewarisi fondasi branch sebelumnya. Merge berurutan, jangan hapus folder lain. Nama push server/vendor-* terblokir selama remote masih punya branch bernama server; jangan menghapus branch tim tanpa izin.

Pada branch monitor, uji tab dengan GET /vendor/shipments/PHA-260104; telemetry/events/issues/reports tersedia sebagai endpoint terpisah. Backend vendor tidak memberi hak create shipment, pemeriksaan atau approval.

Pengujian saat implementasi: SQLite in-memory dan PostgreSQL 12.4 lokal terpisah. Untuk deployment baru gunakan versi PostgreSQL yang masih mendapat dukungan. Tidak ada data Odoo yang dipakai atau diubah.

.env diabaikan Git. OPENAI_API_KEY diisi privat dengan key baru setelah key yang terekspos dicabut; OPENAI_MODEL=gpt-4.1-mini sudah disiapkan. API baca vendor tidak memanggil OpenAI atau mengirim data farmasi ke provider AI.

Panduan [Sanctum resmi](https://laravel.com/docs/13.x/sanctum) dipakai untuk instalasi dan autentikasi.

## Enam modul courier

Implementasi courier dibangun dari main 8080399 dengan membaca keenam branch client/courier-* dan dokumen di server/docs. Seluruh perubahan ada di server/. Client tetap milik tim; endpoint siap dipasang, bukan klaim frontend mock sudah tersambung.

| Branch backend | Kontrak di docs | Fungsi utama |
| --- | --- | --- |
| server-courier-dashboard | [Dashboard](docs/courier-dashboard-api.md) | profil, metrik tugas sendiri, prioritas, sensor kosong/stale |
| server-courier-incoming-task | [Tugas masuk](docs/courier-incoming-task-api.md) | detail, terima checklist, tolak beserta alasan |
| server-courier-task-list | [Daftar tugas](docs/courier-task-list-api.md) | pencarian, tab status, pagination, hitungan global sendiri |
| server-courier-delivery-monitor | [Monitor](docs/courier-delivery-monitor-api.md) | berangkat, tiba, ingest/history telemetry, hold suhu/sinyal |
| server-courier-issue-report | [Kendala](docs/courier-issue-report-api.md) | kendala, foto privat, baca keputusan Admin, konfirmasi retur |
| server-courier-handover-report | [Serah terima](docs/courier-handover-report-api.md) | POD, foto/tanda tangan privat, penutupan, PDF, ringkasan berbagi |

Branch bertahap: merge dashboard → incoming-task → task-list → delivery-monitor → issue-report → handover-report. Branch terakhir memuat semua modul. Setiap branch tetap berisi struktur repo lengkap; bukan branch satu-folder. Nama server-courier-* dipakai karena branch tim bernama server memblokir namespace server/*. Jangan menghapus branch server.

### Menjalankan dan mencoba courier

Dari server/, sesudah setup PostgreSQL:

```powershell
php artisan migrate
php artisan db:seed --class=CourierDemoSeeder
php artisan serve --host=127.0.0.1 --port=8180
```

Di terminal kedua:

```powershell
php artisan schedule:work
```

Scheduler diperlukan untuk mendeteksi kehilangan sinyal >15 menit; pengecekan sekali: php artisan courier:check-signals. Tidak memanggil OpenAI. Seeder lokal tidak mereset tugas existing. PHA-COUR-001 sampai PHA-COUR-005 adalah fixture berlabel demo, bukan pemeriksaan/POD sungguhan.

Login memakai satria@pharma.test / PharmaKurir!2026. Pakai token dari login sebagai Authorization: Bearer. Endpoint GET /courier/dashboard dan GET /courier/tasks hanya melihat kiriman yang courier_id-nya sesuai akun aktif.

Alur normal: assigned → accept → accepted → depart → in_transit → arrive → arrived → handover → completed. Jangan menganggap accept sebagai berangkat. Retur memerlukan keputusan Admin nyata, kendala resolved, confirm-return, lalu handover mode return sehingga status returned.

Mutasi mengirim request_id UUID; ulangi UUID dan payload yang sama untuk retry. Mutasi bisnis juga mengirim version terbaru; 409 berarti benturan versi/tahap/penugasan dan harus muat ulang. Telemetry tidak menggunakan version agar antrean offline tidak macet, tetapi setiap sampel tetap mempunyai UUID sendiri. Koordinat dan recorded_at divalidasi.

Frontend integrator harus mengganti mock dengan API sesuai tabel, menangani 401/403/404/409/422/429, dan membaca data.workflow_status untuk tahap rinci. data.status pada kartu adalah kategori tab. Unduh foto/PDF melalui fetch dengan Bearer lalu Blob URL; jangan menaruh token di URL atau link publik. Backend tidak menjalankan GPS/BLE atau offline buffer di perangkat.

Kiriman non-demo wajib punya approval Admin yang terkait inspeksi terbaru, checklist aman, suhu sesuai rentang dan foto inspeksi yang benar-benar ada. Bukti kurir disimpan terenkripsi di private storage; jangan menjalankan storage:link untuk membukanya. Simpan APP_KEY dan backup file privat dengan aman. Perlu persistent private storage pada deployment, bukan disk serverless sementara.

Notifikasi yang dibuat masih outbox pending/queued. Tidak diklaim sampai ke HP tanpa integrasi provider. PDF aplikasi dan checksum bukan sertifikasi CDOB/CPOB, PDF/A, tanda tangan digital tersertifikasi, atau layanan QR publik. Endpoint Admin/Checker yang menulis approval/assignment/decision tetap tanggung jawab modul tim terkait.

Hasil verifikasi: [backend-verification.md](docs/backend-verification.md). Tes terakhir pada branch lengkap: 45 tes / 254 assertions lulus di SQLite dan PostgreSQL test terpisah.
