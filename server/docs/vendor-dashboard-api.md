# Kontrak API dashboard vendor

Branch ini mewarisi fondasi paket. Untuk integrator VendorDashboardView, MetricCards dan HeroSection. Prefix /api/v1; Bearer token wajib.

| Endpoint | Parameter | Respons |
| --- | --- | --- |
| GET /vendor/dashboard | awb opsional, maksimal 100 karakter | data.facility, metrics, priority_shipment, counts, refreshed_at |
| PUT /vendor/shipments/{awb}/readiness | recipient_ready boolean, version integer, request_id UUID | data.tracking_number, recipient_ready, version, status; replayed |

Dashboard tanpa awb memilih kiriman aktif; kiriman ditahan lebih diprioritaskan, lalu ETA terdekat. awb yang tidak ditemukan atau milik akun lain menghasilkan 404, bukan fallback ke kiriman pertama. Gunakan encodeURIComponent(awb), khususnya resi mock yang diawali #.

metrics memakai nama field OperationalMetrics frontend. active_shipments_count tidak termasuk completed/delivered/returned. incident_count menghitung issue open. available_reports_count menghitung laporan terbit milik akun. Digital hash null jika belum ada laporan.

compliance_rate hanya rasio suhu aman dari sampel terbaru yang masih segar, bukan sertifikasi atau kepatuhan seluruh perjalanan. Data lebih lama 15 menit tidak dihitung. Tanpa pengukuran hasilnya null, bukan 100%. measured_shipments_count dan simulated_measurements_count memperjelas cakupan data. Jangan menampilkan klaim CDOB dari data fasilitas demo.

## Konfirmasi kesiapan

Body contoh:

```json
{"recipient_ready":true,"version":1,"request_id":"2b447015-4d5c-4b51-97bd-7a473875cc4f"}
```

Ambil version dari API, bukan hardcode. Buat request_id memakai crypto.randomUUID(); retry jaringan memakai UUID yang sama. Operasi baru memakai UUID baru.

Transaksi mengunci baris kiriman, memeriksa version dan mencatat audit event. Respons replay mengembalikan hasil tindakan semula; lakukan refresh dashboard setelah sukses. UUID yang dipakai untuk payload/akun/kiriman lain ditolak 409. Versi usang dan kiriman selesai juga ditolak 409. Status workflow tidak berubah saat penerima mengubah kesiapan.

Notifikasi ditambahkan ke outbox messages untuk Admin dan kurir yang ditugaskan. notification_status=queued berarti antre, bukan sudah terkirim ke WhatsApp/Telegram. Worker/gateway pengiriman pesan bukan cakupan tiga API vendor ini.

Frontend belum dimodifikasi: ganti mutasi mock recipient_ready dengan PUT ini, tampilkan sukses setelah respons, dan refresh data pada 409. Jangan memberi vendor akses approval atau penugasan kurir.
