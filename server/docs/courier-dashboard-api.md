# Kontrak dashboard courier

Branch server-courier-dashboard mengacu client/courier-dashboard. Prefix /api/v1. Login /auth/login dengan akun role courier; token mempunyai ability courier.

| Endpoint | Input | Respons |
| --- | --- | --- |
| GET /courier/dashboard | Bearer token kurir aktif | data.profile, metrics, counts, priority_task, refreshed_at |

profile sesuai CourierProfile frontend. Sensor yang belum tersedia bernilai null; BLE, avatar, sertifikasi dan baterai tidak dibuat-buat. metrics hanya menghitung tugas kurir tersebut; completed_today_count memakai batas hari WIB.

priority_task mengutamakan HOLD, tugas baru, lalu jadwal terdekat. Kosong menghasilkan null. Kurir nonaktif, role lain atau token tanpa ability courier ditolak 403.

Fondasi approval, assignment, receipt, decisions dan POD ada pada migration untuk modul berikutnya. API Admin/Checker tidak dibuat; courier tidak dapat membuat approval.

Seed lokal: php artisan db:seed --class=CourierDemoSeeder. Akun satria@pharma.test / PharmaKurir!2026. Contoh diberi is_demo=true dan tidak menimpa kiriman existing.

Client tidak diubah. Ganti mockCourierProfile dan mockCourierMetrics dengan API, jangan memakai fallback sensor aman pada data kosong.
