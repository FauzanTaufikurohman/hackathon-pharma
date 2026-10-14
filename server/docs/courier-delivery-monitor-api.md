# Kontrak monitor pengantaran courier

Branch server-courier-delivery-monitor mengacu tab 2 client/courier-delivery-monitor. Prefix /api/v1; Bearer courier aktif.

| Endpoint /courier/tasks/{awb}/... | Body | Hasil |
| --- | --- | --- |
| GET monitor | - | task, recipient, open_issues_count, can_depart/arrive/handover |
| POST depart | version, request_id UUID | accepted → in_transit, audit, outbox |
| POST arrive | version, request_id UUID, latitude/longitude opsional berpasangan | in_transit → arrived |
| GET telemetry | page, per_page 1–1000 | halaman sampel terbaru, urutan kronologis dalam halaman |
| POST telemetry | request_id, recorded_at ISO8601, temperature, latitude, longitude | tersimpan sekali, status/version terkini |

Telemetry opsional: sensor_id, battery_pct 0–100, speed_kmh 0–300. Koordinat divalidasi; recorded_at maksimal satu hari lalu atau satu menit ke depan untuk clock skew. Retry memakai UUID sama; offline sync mengirim ulang tiap sampel dengan UUID aslinya. Kiriman selesai menolak data baru; respons replay yang sudah tersimpan tetap dapat dibaca.

Accept dan depart terpisah. Approval tetap wajib. HOLD membekukan arrive/handover; telemetry tetap dapat diterima untuk memantau keadaan.

Rule engine lokal menahan suhu di luar batas selama >=180 detik berturut-turut; gap sampel >120 detik memutus rangkaian. Sampel aman berikutnya tidak otomatis membuka HOLD. Signal loss >15 menit dibuat scheduler; jalankan php artisan schedule:work di terminal terpisah. Tidak perlu API OpenAI.

GPS/background/permission dan offline buffer adalah tanggung jawab client. API tidak mengklaim browser dapat memancarkan GPS ketika layar terkunci. Data is_simulated dari seed/demo diberi label; kurir tidak bisa mengirim flag simulasi sendiri.

Pesan masuk outbox, bukan bukti provider telah mengirim WhatsApp/Telegram. Grafik dan map frontend sekarang memakai data statis; integrator perlu memakai sampel API dan empty/stale state. Client tidak diubah.
