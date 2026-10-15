# server-admin-reports

Backend untuk client/admin-reports. Repo memuat client/ dan server/ lengkap; penambahan modul ini ada di server/.

## Endpoint

Prefix /api/v1; gunakan Bearer Sanctum. Role mengikuti akun di database, bukan pilihan login.

- GET /admin/reports
- GET /admin/reports/daily
- GET /admin/reports/{report}/download
- POST /admin/reports/{report}/distribute
- GET /admin/events
- GET /admin/notifications
- POST /admin/notifications/{message}/retry
- PUT /admin/users/{user}/telegram
- POST /admin/shipments/{awb}/simulate

## Integrasi

Mutasi workflow memakai request_id UUID dan version terbaru; create tidak memakai version. Retry memakai UUID dan payload sama; konflik mendapat 409. List dipaginasi. Foto/PDF privat diambil dengan fetch Bearer kemudian Blob URL. Role salah mendapat 403; resi asing/tidak ditemukan mendapat 404, bukan paket pengganti.


Frontend Admin telah terhubung pada branch integrasi. POST /admin/reports/{report}/distribute mengantrekan PDF asli kepada Admin aktif, pemilik kiriman, dan kurir terkait. Laporan juga otomatis diantrekan setelah serah terima; dedupe per laporan/penerima mencegah pengiriman ganda. Token dan Chat ID tetap privat. Status queued bukan sent; worker memerlukan konfigurasi dan scheduler aktif.

Pesan laporan memiliki shipment_report_id serta attachment_sha256; worker memeriksa checksum snapshot dan akses penerima sebelum upload multipart sendDocument. Timeout/5xx/ACK tidak lengkap menjadi uncertain dan memerlukan pengecekan sebelum retry manual. Kiriman/laporan demo tidak dikirim. Laporan saat ini berisi data aplikasi, bukan keluaran analisis AI.

Cara memasang bot, memetakan akun, dan mengirim PDF diagnostik ada di [panduan Telegram](telegram-setup.md). Tidak ada pemilih role login baru.

## Urutan merge

1. server-admin-shipments
2. server-checker-queue
3. server-checker-initial-exam
4. server-checker-history-approvals
5. server-admin-approvals
6. server-admin-live-monitor
7. server-admin-reports
8. server-vendor-shipment-detail-telemetry
9. server-vendor-shipment-detail-report-audit

Branch bertahap: branch ini memuat implementasi sampai langkah 7. Gabungkan sesuai urutan; branch terakhir memuat semua modul. Jalankan php vendor/bin/phpunit dari server/. Database PostgreSQL untuk tes harus berakhiran _test.
