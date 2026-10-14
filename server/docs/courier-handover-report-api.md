# Kontrak serah terima dan laporan courier

Branch server-courier-handover-report mewarisi lima modul courier sebelumnya. Prefix /api/v1; Bearer courier aktif.

| Endpoint | Input | Hasil |
| --- | --- | --- |
| GET /courier/tasks/{awb}/handover | - | task, proof/report nullable, can_submit |
| POST /courier/tasks/{awb}/handover | multipart POD | completed atau returned, proof_id, report_id, download_url |
| GET /courier/tasks/{awb}/proof/photo | token | foto private terdekripsi |
| GET /courier/tasks/{awb}/proof/signature | token | gambar tanda tangan optional |
| GET /courier/reports/{id}/download | token | PDF nyata dari snapshot terverifikasi |
| GET /courier/tasks/{awb}/share-summary | token | teks ringkas dan dispatcher_phone dari konfigurasi |

POD wajib: version, request_id UUID, mode normal/return, recipient_name, temperature, seal_intact boolean, latitude/longitude dan photo. Optional recipient_department, recipient_identity, signature. Foto JPEG/PNG/WebP <=5 MB/16 MP; gambar tanda tangan <=1 MB.

Normal hanya dari arrived dengan approval valid, tanpa kendala terbuka, segel utuh dan suhu dalam rentang. Return hanya dari returning dengan keputusan return Admin. Tidak boleh langsung melompat dari in_transit ke completed.

POD memakai waktu server, menyimpan bukti terenkripsi, membuat snapshot laporan/checksum, mengunci kiriman dan mencatat outbox bagi Admin, pembeli serta kurir. Retry sama tidak menggandakan bukti atau laporan. Semua mutasi baru setelah penutupan ditolak.

Laporan berisi informasi kiriman, pemeriksaan, ringkasan suhu, insiden, POD dan audit. Preview telemetry maksimal 2000 sampel; ringkasan menghitung semua sampel dan data sumber tetap tersimpan. Hash adalah pemeriksaan integritas, bukan enkripsi atau sertifikat. digital_signature_verified=false: gambar tanda tangan bukan tanda tangan elektronik tersertifikasi.

Frontend sekarang memakai penerima/foto/tanggal statis dan toast unduh. Integrator harus memakai form/data nyata dan Blob PDF; client tidak diubah. Isi PHARMA_DISPATCHER_PHONE dengan nomor tujuan yang benar untuk tombol WhatsApp. Membuka share link adalah tindakan pengguna, bukan pengiriman otomatis provider.

PDF/A tervalidasi, sertifikasi CDOB/CPOB, QR publik dan pengiriman WA/Telegram nyata tidak diklaim. Outbox belum dikirim tanpa gateway/worker. Prototype browser tidak dapat menjamin background GPS ketika layar terkunci.
