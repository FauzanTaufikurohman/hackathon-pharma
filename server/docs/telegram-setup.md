# Menjalankan Telegram dan lampiran PDF tanpa n8n

Gunakan bot untuk menerima notifikasi status dan PDF laporan di akun Telegram yang telah dipetakan. Laravel mengantrekan pesan di tabel `messages`; gangguan Telegram tidak membatalkan transaksi pengiriman.

## Siapkan bot dan penerima

Jalankan command berikut dari folder `server/` dengan PHP yang memiliki extension cURL, OpenSSL, dan CA bundle tepercaya.

1. Simpan credential di `server/.env`, bukan di client atau Git. Jika token pernah dibagikan di chat/repository, revoke melalui BotFather dan ganti tokennya.

   ```dotenv
   TELEGRAM_ENABLED=false
   TELEGRAM_BOT_TOKEN=isi_token_privat
   FRONTEND_URL=http://localhost:5173
   ```

2. Terapkan migrasi tambahan dan bersihkan cache konfigurasi. Migrasi menambah kolom lampiran, bukan menghapus data lama.

   ```powershell
   php artisan migrate --force
   php artisan config:clear
   php artisan telegram:check
   ```

3. Penerima membuka bot dan menekan **Start**. Bot notifikasi ini tidak membalas /start otomatis. Periksa Chat ID pribadi yang mengirim /start:

   ```powershell
   php artisan telegram:check --starts
   ```

   Command ini tidak menghubungkan akun secara otomatis, tidak menghapus update, dan tidak menampilkan isi percakapan. Jangan jalankan bersamaan dengan poller/webhook bot lain. Telegram hanya menyimpan update sementara; tekan /start lagi jika update lama sudah tidak tersedia.

4. Dengan izin penerima, petakan Chat ID ke email akun aplikasi yang benar. Ganti email dan angka contoh berikut:

   ```powershell
   php artisan telegram:link admin@pharma.test 123456789
   ```

   Command hanya menerima akun aktif dan Chat ID pribadi numerik. Mapping lama tidak ditimpa kecuali kamu menambahkan `--replace`. Jangan memilih akun orang lain hanya karena mengetahui emailnya. Admin API juga tersedia: `PUT /api/v1/admin/users/{id}/telegram`, Bearer Admin, body `{"chat_id":"123456789"}`.

5. Opsional: kirim satu PDF diagnostik tanpa membaca data paket. Ini boleh dilakukan saat pengiriman antrean masih nonaktif:

   ```powershell
   php artisan telegram:test 123456789 --confirm
   ```

   Command memerlukan konfirmasi eksplisit. Setelah timeout, periksa HP sebelum mengulang agar tidak menghasilkan duplikat.

6. Pastikan mapping dan penerima sudah benar. Ubah `TELEGRAM_ENABLED=true`, jalankan `php artisan config:clear`, lalu jalankan worker:

   ```powershell
   php artisan schedule:work
   ```

   Untuk satu batch manual:

   ```powershell
   php artisan telegram:dispatch --limit=5
   ```

   Batch juga memproses pesan lama yang masih pending/retry. Periksa antrean melalui `GET /api/v1/admin/notifications` sebelum mengaktifkannya. Jangan memakai data pasien untuk uji coba.

## Kirim laporan

Selesaikan serah terima melalui alur kurir. Saat laporan terbit, aplikasi otomatis mengantrekan PDF kepada Admin aktif, pemilik kiriman, dan kurir terkait. Admin dapat mengantrekan kembali laporan yang belum pernah didistribusikan lewat `POST /api/v1/admin/reports/{report}/distribute`; dedupe mencegah pengiriman ulang laporan yang sudah sent.

Notifikasi status biasa memakai `sendMessage`. Pesan laporan memakai `sendDocument` dengan upload PDF multipart, caption, dan tautan aplikasi sesuai role. Telegram tidak perlu mengakses endpoint privat, Bearer token, atau storage laptop.

Worker memeriksa kecocokan shipment, checksum snapshot, status demo, dan akses penerima terkini sebelum mengunggah. Batas aplikasi adalah 10 MiB per PDF; caption dibatasi 1.000 karakter. Kolom `shipment_report_id` mengikat pesan ke revisi laporan, sedangkan `attachment_sha256` mencatat checksum file yang diunggah. Pesan laporan lama yang belum punya kolom relasi dikenali dari dedupe key valid.

Laporan saat ini adalah rangkuman data aplikasi, **bukan hasil analisis AI**. Generator analisis AI belum terintegrasi. Jangan menyebut laporan sebagai keluaran AI, diagnosis, sertifikat CDOB/CPOB, atau PDF/A tersertifikasi. PDF juga dapat memuat informasi penerima; kirim hanya kepada pihak yang berhak.

## Periksa hasil dan masalah koneksi

| Status | Tindakan |
| --- | --- |
| pending / processing | Tunggu worker; ini belum bukti terkirim. |
| sent | Telegram mengembalikan message_id dan, untuk PDF, document.file_id. Periksa HP; ACK bukan bukti dibaca. |
| retry | HTTP 429: tunggu next_attempt_at; maksimal lima percobaan. |
| unconfigured | Periksa akun aktif dan mapping Chat ID. |
| skipped_demo | Kiriman atau laporan demo sengaja tidak dikirim keluar. |
| uncertain | Timeout, HTTP 5xx, ACK tidak lengkap, atau worker terhenti. Periksa HP dahulu; tidak otomatis mengulang. |
| failed | Periksa error_code: akses dicabut, checksum tidak cocok, ukuran PDF, atau penolakan provider. |

Admin dapat mencoba ulang failed/unconfigured melalui `POST /api/v1/admin/notifications/{id}/retry`. Untuk uncertain, sertakan `{"confirm_possible_duplicate":true}` hanya setelah memeriksa kemungkinan pesan ganda. Telegram tidak menyediakan idempotency key untuk sendMessage/sendDocument.

Jika `telegram:check` menunjukkan cURL 60, pasang CA bundle tepercaya pada `curl.cainfo` dan `openssl.cafile` di php.ini, kemudian restart PHP. Jangan mematikan verifikasi TLS. Token kosong/nonaktif tidak mengirim antrean. Error command/worker tidak mencetak token atau URL bertoken.

Scheduler menjalankan batch lima pesan di background dengan overlap lock. Menutup scheduler menghentikan pemrosesan otomatis, tetapi API masih dapat mengantrekan notifikasi. Di hosting, sediakan scheduler/worker yang tetap berjalan; push Git saja tidak mengaktifkannya.

Referensi: [upload sendDocument](https://core.telegram.org/bots/api#senddocument) dan [sendMessage](https://core.telegram.org/bots/api#sendmessage).
