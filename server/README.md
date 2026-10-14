# Menjalankan backend Anteraja Pharma v2

Backend vendor memakai Laravel 13, PHP 8.4, PostgreSQL lokal dan Sanctum 4. Client Vue milik tim tidak diubah. API prefix: /api/v1.

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
