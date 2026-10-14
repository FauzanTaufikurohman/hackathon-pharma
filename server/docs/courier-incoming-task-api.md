# Kontrak tugas masuk

Branch server-courier-incoming-task mewarisi dashboard. Prefix /api/v1; Bearer courier aktif.

| Endpoint | Body | Hasil |
| --- | --- | --- |
| GET /courier/tasks/{awb} | - | data sesuai CourierTaskItem; workflow_status dan version |
| POST /courier/tasks/{awb}/accept | version, request_id UUID, pre_trip | accepted; bukan langsung in_transit |
| POST /courier/tasks/{awb}/reject | version, request_id UUID, category, notes | approved, assignment rejected, menunggu Admin |

pre_trip memuat seal, icepack, manifest yang semuanya harus dicentang, serta temperature numerik dalam rentang paket. Dialog frontend untuk melewati checklist tidak berlaku di server.

Approval Admin wajib merujuk inspeksi terbaru yang lulus. Kiriman non-demo juga wajib mempunyai bukti foto inspeksi. Kapasitas cold-box harus mencukupi; Admin mengisi courier_profile.capacity_kg. Kurir tidak dapat menyetujui inspeksi sendiri.

Accept hanya menerima penugasan. Mulai pengantaran memakai depart pada modul monitor. Reject tidak otomatis memilih kurir lain; tugas dikembalikan ke Admin.

Mutasi mengunci data, memeriksa version, mencatat event dan outbox. Retry memakai request_id serta payload sama; respons replay tidak menggandakan tindakan. 409 untuk versi usang/tahap tidak valid; 404 untuk resi lain.

notification_status=queued bukan bukti pesan WhatsApp/Telegram terkirim. Worker/gateway belum dikonfigurasi. Client belum diubah.
