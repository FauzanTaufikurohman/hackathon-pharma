# server-admin-shipments

Backend untuk client/admin-shipments. Repo tetap berisi client/ dan server/ lengkap; kode baru modul ini ada di server/.

## Endpoint

Prefix /api/v1; gunakan Bearer Sanctum. Role mengikuti akun di database, bukan pilihan login.

- GET/POST /admin/shipments
- GET /admin/metrics
- GET /admin/lookups
- GET /admin/shipments/export
- GET /admin/shipments/{awb}
- POST /admin/shipments/{awb}/cancel
- GET /admin/shipments/{awb}/inspection-photo

## Integrasi

Mutasi workflow memakai request_id UUID dan version terbaru, kecuali create tidak memerlukan version. Retry memakai UUID dan payload sama; konflik mendapat 409. List dipaginasi; jangan menganggap jumlah item halaman pertama sebagai total data. Foto/PDF privat diambil dengan fetch Bearer kemudian Blob URL. Role salah mendapat 403; resi asing/tidak ditemukan mendapat 404. Jangan membuat fallback ke paket lain atau mengaku pesan sudah terkirim saat masih queued.


Frontend tim tetap terpisah dan perlu disambungkan memakai kontrak ini. Tidak ada pemilih role login baru. Telegram nyata membutuhkan credential privat dan /start penerima; tes provider memakai mock sampai credential siap.

## Urutan penggabungan branch

Branch dibangun bertahap. Gabungkan dengan urutan berikut; branch terakhir memuat seluruh modul.

1. server-admin-shipments
2. server-checker-queue
3. server-checker-initial-exam
4. server-checker-history-approvals
5. server-admin-approvals
6. server-admin-live-monitor
7. server-admin-reports
8. server-vendor-shipment-detail-telemetry
9. server-vendor-shipment-detail-report-audit

Branch modul ini memuat implementasi sampai langkah 1. Modul sesudahnya belum tersedia pada branch ini. Jalankan php vendor/bin/phpunit dari server/; default memakai SQLite in-memory. PostgreSQL hanya boleh memakai database berakhiran _test.
