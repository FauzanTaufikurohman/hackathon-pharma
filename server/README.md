<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Anteraja Pharma

## Integrasi AI, WhatsApp, dan laporan PDF

Konfigurasi backend tersedia di `.env.example`. Isi `OPENAI_API_KEY` untuk
analisis termal GPT-4.1, lalu isi kredensial WhatsApp Cloud API dan nama
template Meta yang telah disetujui pada `WHATSAPP_NOTIFICATION_TEMPLATE`.
Laporan menggunakan template statis `anteraja_pharma_shipment_report` (dapat
dioverride melalui `WHATSAPP_REPORT_TEMPLATE`) dengan header dokumen PDF dan
body statis tanpa parameter. Buat serta setujui template berbahasa Indonesia
dengan nama persis tersebut di WhatsApp Manager. Template notifikasi biasa
menerima satu parameter body berisi pesan. Gunakan bahasa template yang sesuai
dengan `WHATSAPP_TEMPLATE_LANGUAGE`.

Setujui opt-in WhatsApp customer melalui `whatsapp_opt_in` sebelum mengirim
pesan. Pastikan nomor menggunakan format internasional, misalnya
`6281234567890`. Pesan bebas hanya dapat dikirim dalam customer-service window
Meta; template yang disetujui digunakan untuk pesan bisnis di luar window itu.

Pemeriksaan checker dan simulasi excursion admin dianalisis secara asinkron di
queue `ai`. Jalankan worker dengan `php artisan queue:work --queue=ai,notifications,default`.
Setelah shipment delivered atau dibatalkan, queue `notifications` membuat PDF
dari data shipment terkini, mengunggahnya sebagai media privat ke Meta, lalu
mengirimkannya dengan template laporan. Jalankan migrasi database sebelum
menyalakan worker.

Setiap shipment baru menghubungkan satu akun customer produsen sebagai pengirim
dan satu akun customer distributor/apotek sebagai penerima. Kedua akun harus
memiliki profil perusahaan yang terdaftar; akun customer yang terkait dapat
melihat tracking shipment. Laporan PDF dikirim ke setiap profil pihak shipment
yang memiliki nomor WhatsApp dan telah opt-in. Admin dapat melihat akun customer
di halaman Customer; akun baru dibuat melalui registrasi customer.

Kurir dapat mengaktifkan geolocation browser saat shipment in-transit. Titik
lokasi dikirim berkala selama halaman kurir terbuka dan ditampilkan sebagai peta
OpenStreetMap untuk admin serta customer yang terkait. Simulasi thermal hanya
tersedia ketika shipment berstatus `in_transit`.

Analisis GPT adalah bantuan operasional, bukan keputusan stabilitas atau
kelayakan produk. Shipment tetap membutuhkan penilaian manusia; model tidak
melanjutkan atau membatalkan pengiriman secara otomatis.

Untuk mengaktifkan diagnostik AI termal di lingkungan pengembangan, set
`THERMAL_AI_DEBUG=true`, jalankan `php artisan config:clear`, lalu restart queue
worker. Migrasikan kolom diagnostik dengan `php artisan migrate`. Diagnostik
tersedia bagi admin melalui tombol **Debug AI** di halaman Thermal dan mencatat
tahap proses, latensi, status HTTP, request ID provider, model, serta token
pemakaian; isi prompt dan API key tidak dicatat. Log Laravel tersedia di
`storage/logs/laravel.log`. Matikan kembali dengan `THERMAL_AI_DEBUG=false`
setelah investigasi; jangan mengaktifkan `APP_DEBUG` di production.

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
