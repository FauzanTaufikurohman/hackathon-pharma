# Kontrak daftar tugas courier

GET /api/v1/courier/tasks; Bearer courier aktif. Mengacu client/courier-task-list.

| Parameter | Nilai | Hasil |
| --- | --- | --- |
| status | all, assigned, in_transit, hold, delivered | filter bucket tampilan |
| search | string <=120 karakter | resi, muatan, penerima, alamat, pengirim |
| page / per_page | page >=1; per_page 1–100, default 20 | meta pagination |

data[] mengikuti CourierTaskItem; counts menghitung seluruh tugas kurir, bukan halaman/filter saja. Pencarian %/_ literal. Empty state adalah data=[].

workflow_status adalah status sebenarnya. Bucket in_transit mencakup accepted (siap berangkat), arrived dan returning; jangan mengubah accepted menjadi in_transit hanya karena masuk bucket monitor. Dashboard on_route_count menghitung perjalanan yang sudah dimulai.

CTA acceptance/rejection memakai API tugas masuk; monitor/POD memakai endpoint courier, bukan /vendor/monitor. Route frontend vendor yang sekarang dipakai kurir perlu diganti oleh integrator agar tidak menabrak RBAC. Client tidak diubah.
