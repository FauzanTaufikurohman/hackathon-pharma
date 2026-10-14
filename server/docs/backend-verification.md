# Hasil verifikasi backend vendor

Pengujian dilakukan 14 Oktober 2026 pada branch monitor yang memuat ketiga modul bertahap. Client tidak diubah.

## Pengujian otomatis

26 tes, 132 assertions lulus pada SQLite in-memory dan PostgreSQL lokal dengan database khusus pharma_v2_test. Formatter Pint lulus setelah perapian. Composer audit tidak menemukan advisory keamanan pada dependency yang terpasang.

Kasus yang diuji: login benar/salah, rate limit, expiry dan ability token, browser origin, role non-vendor, isolasi buyer_id, filter/pagination/pencarian wildcard literal, empty state sensor, dashboard berbasis data, sensor stale, optimistic version, request replay, kiriman selesai, audit immutable, resi tidak ada, resi dengan #, pagination/filter waktu telemetry, integritas laporan, PDF nyata, foto private dan path traversal.

Tes tidak boleh memakai database aplikasi; guard sebelum RefreshDatabase menolak database non-SQLite yang namanya tidak berakhiran _test.

## Smoke test HTTP nyata

API lokal: http://127.0.0.1:8180/api/v1. PostgreSQL aplikasi: pharma_v2 pada port 5544, terpisah dari Odoo.

| Skenario | Hasil |
| --- | --- |
| Login vendor dari origin localhost:5173 | berhasil tanpa cookie/CSRF |
| Daftar paket vendor | 5 paket, kategori transit 2 / qc 2 / delivered 1 |
| Dashboard | 4 kiriman aktif |
| Detail PHA-260104 | resi sesuai permintaan |
| Telemetry resi yang diawali # | sampel tersedia dan ditandai simulasi |
| Laporan demo completed | satu laporan, integritas snapshot valid |
| Download laporan | application/pdf, 18.853 bytes pada pengujian |
| Konfirmasi kesiapan + retry UUID sama | tersimpan sekali; replayed=true |
| Logout lalu akses kembali | 401 |

Data seed adalah contoh, bukan produk obat nyata, sensor IoT nyata, pemeriksaan bersertifikat atau POD sungguhan.

## Batas verifikasi

API data vendor siap diintegrasikan. Halaman frontend masih mock dan tab monitoring/arsip masih placeholder; integrator tim perlu memakai tiga kontrak API yang tersedia.

Gateway Telegram/WhatsApp, AI Vision/anomaly, simulator operasional, endpoint Admin/Checker/Kurir, PDF/A/QR publik serta sertifikasi regulatory bukan hasil implementasi tiga modul ini. Jangan menyatakan workflow end-to-end seluruh Pharma sudah berjalan berdasarkan pengujian API vendor saja.
