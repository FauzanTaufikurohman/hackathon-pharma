# Menjalankan Telegram tanpa n8n

Laravel mengirim pesan dari tabel messages melalui Bot API Telegram. Perubahan status hanya mengantrekan pesan, sehingga kegagalan provider tidak membatalkan transaksi kiriman.

1. Buat bot di @BotFather. Simpan token hanya di server/.env:

   ```dotenv
   TELEGRAM_ENABLED=true
   TELEGRAM_BOT_TOKEN=isi_token_privat
   FRONTEND_URL=http://localhost:5173
   ```

2. Penerima membuka bot dan mengirim /start. Catat Chat ID akun uji dengan izin pemilik akun. Admin memasangnya lewat PUT /api/v1/admin/users/{id}/telegram dengan body {"chat_id":"123456789"}. Gunakan Bearer Admin; akun biasa tidak dapat memasang mapping akun lain. Mapping ini disiapkan Admin, bukan pendaftaran role publik.

3. Muat ulang konfigurasi dan jalankan scheduler dari server/:

   ```powershell
   php artisan config:clear
   php artisan schedule:work
   ```

   Untuk mencoba satu batch:

   ```powershell
   php artisan telegram:dispatch --limit=20
   ```

4. Buat perubahan kiriman non-demo melalui API, lalu cek GET /api/v1/admin/notifications. Jangan memakai data kesehatan pribadi untuk akun uji.

5. Periksa status pesan:

   | Status | Arti |
   | --- | --- |
   | pending | Masuk antrean, belum dikirim |
   | processing | Sedang ditangani worker |
   | sent | Telegram mengembalikan ok=true dan message_id; bukan bukti dibaca penerima |
   | retry | Provider meminta tunggu atau sedang gagal; next_attempt_at menentukan waktu berikutnya |
   | unconfigured | Chat ID belum tersedia atau akun tidak aktif |
   | skipped_demo | Data demo sengaja tidak dikirim keluar |
   | uncertain | Timeout/worker terhenti; pesan mungkin sudah sampai sehingga tidak otomatis dikirim ulang |
   | failed | Provider menolak atau batas percobaan tercapai |

Gateway tidak aktif/token kosong menghasilkan configured=false dan tidak membuat panggilan jaringan. Token tidak disertakan dalam respons API atau log error aplikasi yang dibuat worker.

Scheduler menjalankan batch lima pesan di proses background dengan overlap lock, sehingga provider lambat tidak menahan pengecekan sinyal kurir.

Untuk status failed/unconfigured, Admin dapat POST /api/v1/admin/notifications/{id}/retry. Untuk uncertain, sertakan {"confirm_possible_duplicate":true} setelah memeriksa HP penerima karena Telegram tidak menyediakan idempotency key sendMessage.

Setiap pesan membawa tautan ke resi yang benar pada frontend sesuai role penerima. Tautan tidak memuat token login. Gateway ini mengirim teks dan tautan aplikasi, bukan lampiran PDF atau WhatsApp/Email. FRONTEND_URL produksi harus alamat aplikasi HTTPS yang benar.

HTTP 429 menghormati retry_after. Gangguan 5xx memakai backoff dengan maksimal lima percobaan. Timeout tidak diklaim sukses. Kiriman demo selalu dilewati. Pengujian otomatis menggunakan Http::fake dan tidak membuktikan pesan sudah sampai ke HP; uji HP memerlukan token, Chat ID dan /start yang nyata.

Referensi: [sendMessage dan response Telegram Bot API](https://core.telegram.org/bots/api#sendmessage).
