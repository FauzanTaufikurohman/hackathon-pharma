# API Admin dan Checker

Gunakan endpoint ini untuk menyambungkan frontend ke data PostgreSQL. Semua URL di bawah memakai prefix /api/v1 dan Authorization: Bearer dari POST /auth/login. Role berasal dari akun, bukan pilihan di halaman login atau parameter request.

## Pemetaan modul

| Modul client | Target branch backend saat pemisahan commit | Endpoint utama |
| --- | --- | --- |
| client/admin-shipments | server-admin-shipments | /admin/shipments, /admin/metrics, /admin/lookups |
| client/admin-approvals | server-admin-approvals | /admin/shipments/{awb}, /approval, /assign, /recheck |
| client/admin-live-monitor | server-admin-live-monitor | /admin/monitor, /admin/issues, /telemetry, /decision |
| client/admin-reports | server-admin-reports | /admin/reports, /admin/events, /admin/notifications |
| client/checker-queue | server-checker-queue | /checker/queue |
| client/checker-initial-exam | server-checker-initial-exam | /checker/shipments/{awb}, /inspection |
| client/checker-history-approvals | server-checker-history-approvals | /checker/history, /checker/inspections/{id}/photo |
| client/vendor-shipment-detail-telemetry | server-vendor-shipment-detail-telemetry | /vendor/shipments/{awb}/telemetry, /events, /issues |
| client/vendor-shipment-detail-report-audit | server-vendor-shipment-detail-report-audit | /vendor/shipments/{awb}/reports, /proof, /vendor/reports/{id}/download |

Branch di atas disiapkan bertahap: admin-shipments → checker-queue → checker-initial-exam → checker-history-approvals → admin-approvals → admin-live-monitor → admin-reports → vendor-shipment-detail-telemetry → vendor-shipment-detail-report-audit. Branch terakhir memuat semua modul; setiap branch berisi repo lengkap. Header tidak membutuhkan backend terpisah; pakai /auth/me. Fondasi courier dan vendor yang sudah dibuat tetap dipakai. Jangan membuat endpoint dengan logika yang sama untuk tiap tab.

## Kontrak umum

- Mutasi workflow mengirim request_id UUID. Retry harus memakai UUID dan payload sama; payload berbeda dengan UUID sama mendapat 409.
- Selain create, mutasi kiriman mengirim version yang dibaca dari detail terbaru. Konflik versi mendapat 409; jangan langsung menimpa versi dari tab lain.
- unknown AWB mendapat 404 pada detail, bukan fallback ke paket pertama.
- Semua list dipaginasi. page minimal 1; per_page dibatasi. counts dashboard adalah total, bukan jumlah item halaman pertama.
- 401 berarti login ulang; 403 berarti role/ability/akun tidak sesuai; 404 berarti objek tidak tersedia untuk akun tersebut; 409 berarti tahapan atau versi tidak cocok; 422 berarti perbaiki input; 429 berarti tunggu lalu coba lagi.
- Foto dan PDF memerlukan Bearer. Frontend harus fetch lalu membuat Blob URL dan me-revoke-nya setelah dipakai. Jangan menaruh token pada query string.

## Manajemen pengiriman

| Endpoint | Tujuan | Input utama |
| --- | --- | --- |
| GET /admin/lookups | Pilihan akun buyer/checker/kurir aktif dan kapasitas | Bearer admin |
| GET /admin/metrics | Hitungan nyata per status, kendala dan laporan | Bearer admin |
| GET /admin/shipments | Daftar seluruh kiriman Admin | status opsional, search literal, page, per_page 1–100 |
| GET /admin/shipments/{awb} | Detail kiriman, inspeksi, approval, assignment dan POD | awb URL-encoded |
| POST /admin/shipments | Buat manifest, masuk antrean Checker | request_id dan field di bawah |
| POST /admin/shipments/{awb}/cancel | Batalkan sebelum pengantaran | version, request_id, reason 5–4000 karakter |
| GET /admin/shipments/export | Unduh CSV nyata, melindungi formula CSV | Maksimal 10.000 baris; lebih banyak mendapat 413 |

Create menerima tracking_number, buyer_id, inspector_id, sender_name, recipient_name, recipient_phone, address, origin_city, destination_city, item_summary, package_count, weight_kg, temperature_profile (controlled/ambient/deep_freeze), temperature_min, temperature_max, scheduled_at ISO8601, handling_instructions. batch_number dan package_type opsional. Suhu maksimal harus lebih besar dari minimal. Jumlah paket positif dan berat >0. Buyer dan Checker harus akun aktif dengan role sesuai.

Frontend form lama memakai nama awb/koli_count/temp_min/temp_max; integrator harus memetakannya ke tracking_number/package_count/temperature_min/temperature_max. Gunakan ID dari lookups, bukan menebak ID berdasarkan nama atau alamat. buyer_id, inspector_id, weight_kg dan destination_city perlu disediakan form. Backend menetapkan created_by, status, version dan is_demo; client tidak dapat menjadikannya otomatis lolos QC.

## Pemeriksaan dan persetujuan

| Endpoint | Tujuan | Input utama |
| --- | --- | --- |
| GET /checker/queue | Tugas pemeriksaan yang inspector_id-nya milik akun | search, page, per_page 1–100 |
| GET /checker/shipments/{awb} | Konteks resi dan can_submit | Bearer checker yang ditugaskan |
| POST /checker/shipments/{awb}/inspection | Kirim pemeriksaan multipart | version, request_id, packaging/seal/label boolean, temperature, note 5–4000, photo wajib |
| GET /checker/history | Riwayat pemeriksaan milik Checker | awb opsional, page, per_page |
| GET /checker/inspections/{id}/photo | Foto revisi pemeriksaan milik Checker | Bearer |
| POST /admin/shipments/{awb}/approval | Keputusan Admin setelah pemeriksaan | version, request_id, decision approved/held, reason wajib |
| POST /admin/shipments/{awb}/recheck | Minta Checker memeriksa ulang hold QC | version, request_id, reason |
| POST /admin/shipments/{awb}/assign | Tugas kurir setelah persetujuan | version, request_id, courier_id, reason |
| GET /admin/shipments/{awb}/inspection-photo | Bukti terbaru untuk reviewer Admin | Bearer admin |

Foto JPG/PNG/WebP maksimal 5 MB dan 16 megapiksel. Tidak memakai foto bawaan sebagai pengganti unggahan wajib. passed dihitung server; client dilarang mengirim passed. Inspeksi gagal tetap menunggu keputusan Admin, dan tidak bisa disetujui. Pemeriksaan terkirim tidak memiliki endpoint edit/delete; pemeriksaan ulang menghasilkan record baru. Keputusan lama tetap dipertahankan.

OCR belum diintegrasikan pada modul ini: response menyatakan available=false/not_requested, bukan angka OCR karangan. Field ini tidak boleh ditampilkan sebagai verifikasi AI sukses.

Assign mengecek approval yang merujuk inspeksi terbaru, bukti foto, checklist/suhu aman, akun kurir aktif dan kapasitas termasuk tugas pending yang sudah dipesan. Kurir menerima tugas terlebih dahulu, lalu berangkat melalui endpoint courier terpisah.

## Monitor dan keputusan kendala

| Endpoint | Tujuan | Input utama |
| --- | --- | --- |
| GET /admin/monitor | Kiriman aktif dan telemetry terbaru | awb opsional, page |
| GET /admin/shipments/{awb}/telemetry | Riwayat lokasi/suhu nyata | from, to ISO8601, page, per_page 1–1000 |
| GET /admin/shipments/{awb}/prediction | Tren suhu dari sampel tersimpan, bukan teks banner statis | Bearer admin |
| GET /admin/issues | Kendala dan keputusan | awb/status open atau resolved opsional, page |
| GET /admin/issues/{id}/photo | Foto kendala privat | Bearer admin |
| POST /admin/shipments/{awb}/issues/{id}/decision | Putuskan kendala | version, request_id, action resume/recheck/return, reason wajib |

Resume memerlukan sampel segar, suhu aman, tidak ada kendala lain terbuka dan bukan kerusakan segel/kemasan. Recheck menutup penugasan lama dan mengembalikan kiriman ke antrean Checker. Return mencatat instruksi; status tetap HOLD sampai kurir mengonfirmasi /confirm-return. Ini mencegah Admin mengaku kurir sudah melakukan perjalanan retur.

Prediction membutuhkan minimal tiga sampel dalam 15 menit terakhir, rentang waktu >=2 menit, dan sampel terbaru <=2 menit. Regresi tren lokal memperkirakan waktu ke batas suhu atas; warning aktif jika perkiraannya <=5 menit. Data kurang/stale menghasilkan available=false, bukan klaim kondisi aman. Ini estimasi matematis, bukan keputusan mutu atau model LLM.

Simulator hanya menerima kiriman is_demo=true pada local/testing atau PHARMA_SIMULATOR_ENABLED=true. POST /admin/shipments/{awb}/simulate menerima version, request_id, type temperature/signal_loss/seal dan temperature untuk type temperature. Lonjakan suhu merekam tiga sampel selama empat menit untuk rule >=3 menit. Data demo tidak dikirim keluar lewat Telegram.

## Laporan dan audit

| Endpoint | Tujuan | Input utama |
| --- | --- | --- |
| GET /admin/reports | Metadata laporan terbit | awb opsional, page |
| GET /admin/reports/daily | Agregasi harian, batas hari WIB | date YYYY-MM-DD opsional |
| GET /admin/reports/{id}/download | PDF dari snapshot, checksum diverifikasi | Bearer admin |
| POST /admin/reports/{id}/distribute | Antrekan pemberitahuan laporan, deduplikasi report/user | Bearer admin, respons 202 queued |
| GET /admin/events | Audit tersimpan | awb opsional, page |
| GET /admin/notifications | Status gateway dan pengiriman pesan | page |

Laporan tidak diberi klaim PDF/A, sertifikasi CDOB/BPOM, tanda tangan tersertifikasi atau enkripsi SHA-256. SHA-256 adalah checksum. Antrean notifikasi bukan bukti pesan sudah terkirim.

Tab telemetry Vendor memakai endpoint yang sudah ada dengan ownership buyer_id; tab laporan menambah GET /vendor/shipments/{awb}/proof dan /proof/photo serta /proof/signature. Signature tidak ada menghasilkan 404, bukan gambar pengganti. Vendor tidak dapat mengambil keputusan Admin atau melihat paket buyer lain.
