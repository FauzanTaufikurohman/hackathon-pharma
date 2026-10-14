# Kontrak laporan kendala courier

Prefix /api/v1; Bearer courier aktif; mengacu client/courier-issue-report.

| Endpoint | Body | Hasil |
| --- | --- | --- |
| GET /courier/tasks/{awb}/issues | page/per_page <=100 | laporan, keputusan Admin nullable, photo_url private |
| POST /courier/tasks/{awb}/issues | version, request_id, category, description | issue_id, deviation_hold, version, notification queued |
| POST /courier/tasks/{awb}/confirm-return | version, request_id | returning jika ada keputusan return Admin |
| GET /courier/issues/{id}/photo | token | gambar bukti setelah autentikasi |

category: seal, temperature, packaging, recipient_unavailable, address, other. description 10–4000 karakter. Koordinat optional berpasangan; jika tidak tersedia memakai posisi terakhir atau null, tidak mengarang lokasi.

Foto optional memakai multipart/form-data; JPEG/PNG/WebP <=5 MB dan <=16 MP. Foto disimpan terenkripsi di disk private, bukan public/storage. Request replay yang sama tidak membuat foto/laporan ganda.

Laporan membekukan tugas. Keputusan Admin dibaca dari decisions, tidak dari kartu mock frontend. Courier hanya mengakui keputusan return dari Admin; tidak ada endpoint courier untuk menentukan resume/return/recheck.

Worker/gateway pesan belum dikonfigurasi: queued bukan sent. Client tetap tidak diubah; tombol/form/toast harus disambungkan API oleh integrator.
