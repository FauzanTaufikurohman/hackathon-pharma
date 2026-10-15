# Kontrak API monitor vendor

Integrasi VendorMonitorView memakai endpoint di bawah. Prefix /api/v1; Bearer token wajib. Gunakan encodeURIComponent(awb) untuk path resi; # dalam contoh resi wajib menjadi %23.

| Endpoint | Kegunaan | Data |
| --- | --- | --- |
| GET /vendor/shipments/{awb} | tab informasi dan pemeriksaan | data Shipment, inspection nullable, courier, links |
| GET /vendor/shipments/{awb}/telemetry | grafik suhu dan marker GPS | data[] sampel, meta kesegaran dan rentang suhu |
| GET /vendor/shipments/{awb}/events | timeline audit | data[] event tanpa metadata internal |
| GET /vendor/shipments/{awb}/issues | kendala pengiriman | data[] jenis, status, deskripsi, alasan resolusi |
| GET /vendor/shipments/{awb}/reports | arsip laporan terbit | data[] id, judul, revision, snapshot_sha256, download_url, verify_url |
| GET /vendor/shipments/{awb}/inspection-photo | foto inspeksi private | image/jpeg, png, webp; 404 jika belum tersedia |
| GET /vendor/reports/{id}/download | unduh PDF | application/pdf; 409 jika integritas snapshot gagal |
| GET /vendor/reports/{id}/verify | periksa integritas | data.integrity_valid, snapshot_sha256 |

Semua endpoint memeriksa buyer_id dari akun login sebelum membaca data. Resi tidak ada/milik vendor lain serta report milik vendor lain menghasilkan 404. Endpoint vendor bukan API Admin.

## Telemetry dan pagination

telemetry menerima from/to berupa tanggal ISO 8601, page >=1, per_page 1–1000 (default 300). Halaman pertama berisi sampel terbaru; isi setiap halaman disusun kronologis dari lama ke baru untuk grafik. Halaman kedua memuat sampel lebih lama. meta.total adalah jumlah hasil filter. Baca latitude/longitude untuk map; jangan memakai koordinat demo sebagai GPS nyata.

events, issues dan reports menerima page dan per_page 1–100 (default 20). Event terbaru ditampilkan dulu. Report berdasarkan revisi terbaru.

meta.is_stale berarti sampel terbaru tidak ada, lebih tua 15 menit, atau berada terlalu jauh di masa depan. recorded_at memakai UTC. Data null atau kosong harus menjadi empty state; jangan menganggap inspeksi passed=true, suhu 4.2°C, atau GPS aktif jika belum ada data.

Client saat ini punya placeholder tab 2/3 dan fallback inspeksi aman. Integrator harus menggantinya dengan data API dan empty/error/loading state; backend tidak mengubah halaman milik tim.

## Laporan dan foto

Unduh report memakai Authorization header; mengambil URL lewat link browser biasa tidak membawa Bearer token. Gunakan fetch lalu Blob/object URL dan revokeObjectURL setelah selesai. X-Report-SHA256 adalah hash bytes PDF; snapshot_sha256 adalah hash JSON snapshot, dua hal berbeda.

Snapshot laporan yang sudah terbit dijaga tidak dapat diubah lewat model. Perubahan membuat revisi baru. Hash diperiksa sebelum render PDF. URL verify tetap membutuhkan autentikasi; belum ada portal QR publik, PDF/A tervalidasi, enkripsi PDF, atau tanda tangan digital tersertifikasi. Jangan mengklaim fitur itu sudah ada.

Foto hanya dibaca dari disk private Laravel, bukan public/storage. Path internal tidak dikirim melalui JSON. Foto belum tersedia pada seed; API mengembalikan 404, bukan gambar bukti palsu.

## Integrasi halaman

Tab 1: fetch detail dan tampilkan status/inspection/kesiapan. Konfirmasi kesiapan memakai kontrak dashboard.

Tab 2: fetch telemetry; plot data grafik dan marker. Poll hanya saat tab aktif, cegah request overlap, hentikan saat unmount, gunakan AbortController. Sampel seed is_simulated=true.

Tab 3: fetch events/issues/reports, lalu ambil download_url report terpilih. Rekap semua paket memakai /vendor/packages/export, bukan BAP per resi.

Fondasi API baca ini tidak memasukkan endpoint operasional Admin/Checker/Kurir, AI anomaly engine, gateway Telegram/WA atau simulator lengkap. Tim dapat menyambungkan modul tersebut ke model yang tersedia; jangan menambah akses tulis vendor.
