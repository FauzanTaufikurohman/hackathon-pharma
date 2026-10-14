# Kontrak API paket vendor

Frontend integrator memakai endpoint ini untuk mengganti mock di VendorPackagesView. Prefix: `/api/v1`. Branch: `server/vendor-packages`.

| Endpoint | Input | Hasil |
| --- | --- | --- |
| POST /auth/login | email, password, device_name opsional | data akun, token Bearer, expires_at |
| GET /auth/me | Authorization: Bearer TOKEN | data akun dan facility |
| POST /auth/logout | token aktif | token tersebut dicabut |
| GET /vendor/packages | category, status, search, page, per_page, sort | data[], counts, meta |
| GET /vendor/packages/export | category, status, search, from, to | berkas PDF rekap |

Semua endpoint vendor hanya menerima role buyer/vendor dan hanya mengakses shipments.buyer_id milik akun tersebut. Admin/checker/courier mendapat 403 pada API vendor; mereka memerlukan API role masing-masing.

## Parameter daftar

category: all (default), transit, qc, delivered. search maksimal 120 karakter; pencarian mencakup resi, muatan, pengirim, penerima, kota. Karakter % dan _ dicari literal, bukan wildcard SQL.

page minimal 1; per_page 1–100 (default 20); sort newest (default), oldest, eta. counts menghitung seluruh paket akun, tidak mengikuti filter/pagination. meta.total menghitung hasil filter.

Ekspor menerima from/to dalam YYYY-MM-DD, batas hari WIB; maksimal 500 paket per permintaan. Ekspor kosong tetap menghasilkan PDF. Ini rekap data, bukan dokumen compliance tersertifikasi.

## Kontrak data

data[] mengikuti field Shipment dalam client/src/types/shipment.ts. scheduled_at, completed_at, created_at, updated_at memakai ISO 8601 UTC, bukan teks tanggal mock. Tampilkan tanggal memakai zona Asia/Jakarta di client.

Field tambahan: is_demo, telemetry_recorded_at, telemetry_fresh, telemetry_simulated. Suhu yang hilang bernilai null, bukan angka aman palsu. Data lebih tua dari 15 menit ditandai belum diperbarui. inspection nullable; tidak menampilkan path foto private.

Status internal memakai nama pendek frontend. Padanan FRD: CREATED_AWAITING_EXAM → inspection; EXAMINED_PENDING_APPROVAL → pending_approval; APPROVED_READY_FOR_ASSIGNMENT → approved; DEVIATION_HOLD → deviation_hold; COMPLETED_CLOSED → completed. assigned dan accepted adalah dua tahap berbeda.

## Error dan keamanan

401: token hilang/kedaluwarsa; 403: role tidak berhak; 422: parameter tidak valid; 429: batas request. Login dibatasi 5 percobaan/menit per email+IP dan 30/menit per IP. Token berlaku satu hari. Jangan simpan password atau token dalam URL, log, Git atau contoh publik.

API mendukung Sanctum cookie stateful dengan CSRF untuk integrasi SPA, tetapi endpoint login saat ini menerbitkan Bearer token; tidak membuat sesi cookie. Simpan token sementara di memori frontend dan kirim header Authorization. Gunakan HTTPS di cloud.

Client belum diubah. Tombol refresh harus memanggil API ulang; toast sukses baru muncul setelah respons berhasil. Tombol unduh harus mengambil response sebagai Blob, bukan menampilkan toast saja.

## Batas cakupan

Fondasi ini menyiapkan data baca vendor, bukan endpoint mutasi Admin/Checker/Kurir. Enam FRD tetap acuan keseluruhan produk; tidak diklaim sudah terimplementasi semua pada branch ini. Konfigurasi OpenAI server-only disiapkan; halaman vendor tidak membutuhkan panggilan AI.
