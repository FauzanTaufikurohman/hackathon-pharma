# server-admin-live-monitor

Backend untuk client/admin-live-monitor. Repo memuat client/ dan server/ lengkap; penambahan modul ini ada di server/.

## Endpoint

Prefix /api/v1; gunakan Bearer Sanctum. Role mengikuti akun di database, bukan pilihan login.

- GET /admin/monitor
- GET /admin/issues
- GET /admin/shipments/{awb}/telemetry
- GET /admin/shipments/{awb}/prediction
- POST /admin/shipments/{awb}/issues/{issue}/decision
- GET /admin/issues/{issue}/photo
- POST /admin/shipments/{awb}/simulate (khusus kiriman demo yang memenuhi syarat)

Simulator tersedia pada halaman Admin **Simulasi termal**. Skenario suhu,
sinyal, atau segel dan koreksi termal berbantuan prediksi dicatat sebagai
telemetry/event simulasi dan disertakan pada laporan ketika serah terima
diterbitkan. Koreksi termal membuat tiga sampel pada titik tengah rentang
kiriman; koreksi tidak mengendalikan perangkat nyata dan tidak melanjutkan
kiriman secara otomatis. Admin tetap harus meninjau kendala dan mengambil
keputusan. Data demo tidak dikirim keluar melalui Telegram.

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

Branch bertahap: branch ini memuat implementasi sampai langkah 6. Gabungkan sesuai urutan; branch terakhir memuat semua modul. Jalankan php vendor/bin/phpunit dari server/. Database PostgreSQL untuk tes harus berakhiran _test.
