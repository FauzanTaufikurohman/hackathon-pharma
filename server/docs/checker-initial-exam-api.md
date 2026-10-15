# server-checker-initial-exam

Backend untuk client/checker-initial-exam. Repo memuat client/ dan server/ lengkap; penambahan modul ini ada di server/.

## Endpoint

Prefix /api/v1; gunakan Bearer Sanctum. Role mengikuti akun di database, bukan pilihan login.

- GET /checker/shipments/{awb}
- POST /checker/shipments/{awb}/inspection
- GET /checker/inspections/{inspection}/photo

## Integrasi

Mutasi workflow memakai request_id UUID dan version terbaru; create tidak memakai version. Retry memakai UUID dan payload sama; konflik mendapat 409. List dipaginasi. Foto/PDF privat diambil dengan fetch Bearer kemudian Blob URL. Role salah mendapat 403; resi asing/tidak ditemukan mendapat 404, bukan paket pengganti.


Frontend tim tetap perlu disambungkan ke API. Telegram nyata membutuhkan credential dan /start penerima; status queued bukan sent. Tidak ada pemilih role login baru.

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

Branch bertahap: branch ini memuat implementasi sampai langkah 3. Gabungkan sesuai urutan; branch terakhir memuat semua modul. Jalankan php vendor/bin/phpunit dari server/. Database PostgreSQL untuk tes harus berakhiran _test.
