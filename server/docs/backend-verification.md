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

## Pembaruan courier — 15 Oktober 2026

Bagian di atas adalah hasil historis vendor. Enam modul courier kini ditambahkan di server/, tanpa mengubah client maupun membuat folder source baru. Implementasi Admin/Checker dan integrasi UI belum termasuk pekerjaan ini.

### Pengujian otomatis

Pada branch server-courier-handover-report, seluruh suite vendor + courier lulus: **45 tes, 254 assertions** pada SQLite in-memory dan PostgreSQL pharma_v2_test. Tes meliputi isolasi courier, role/ability/akun nonaktif, dashboard kosong/suhu stale, filter/pagination, approval dan kapasitas, checklist, foto inspeksi nyata untuk non-demo, penugasan tidak cocok, versi stale, retry payload, penolakan tugas, urutan berangkat/tiba, telemetry, hold suhu >=3 menit, signal loss >15 menit, kendala, keputusan retur Admin, POD wajib foto, suhu/segel tidak aman, PDF nyata, signature privat, penguncian kiriman, dan bukti immutable pada model.

Setiap branch juga diuji sendiri pada kedua database, bukan hanya branch terakhir:

| Branch | Tes lulus | Assertions |
| --- | ---: | ---: |
| server-courier-dashboard | 30 | 151 |
| server-courier-incoming-task | 36 | 177 |
| server-courier-task-list | 37 | 187 |
| server-courier-delivery-monitor | 40 | 211 |
| server-courier-issue-report | 42 | 225 |
| server-courier-handover-report | 45 | 254 |

Composer audit pada dependency terpasang tidak menemukan advisory keamanan. Tidak ada perubahan client terhadap main 8080399 pada keenam branch.

Lock DB dan request receipt mencegah pemrosesan mutasi yang sama berulang. Ini bukan hasil load test multi-host atau jaminan tidak ada bug. Larangan update/delete pada bukti/audit berlaku lewat model Eloquent, bukan klaim DBA tidak dapat mengubah database.

### Smoke test HTTP nyata

API http://127.0.0.1:8180/api/v1 memakai PostgreSQL lokal pharma_v2. Resi PHA-COUR-001 adalah demo; gambar lampiran uji hanya fixture, bukan foto penerimaan obat.

| Skenario | Hasil |
| --- | --- |
| Login satria@pharma.test | role courier, Bearer Sanctum |
| Dashboard + list assigned | hanya tugas akun sendiri |
| Terima tugas + retry UUID sama | accepted; replayed=true |
| Berangkat | in_transit |
| Kirim telemetry | tersimpan, is_simulated=true |
| Konfirmasi tiba | arrived |
| POD multipart + foto fixture | completed |
| Unduh laporan | application/pdf, 25.030 bytes |
| Unduh foto privat | 200, butuh Bearer pemilik |
| Logout lalu panggil dashboard | 401 |
| Notifikasi | queued, bukan sent |

Suhu/GPS dari endpoint adalah data yang dikirim perangkat atau simulator, bukan bukti sensor terkalibrasi. UI frontend pada branch client tetap perlu dihubungkan oleh integrator.
