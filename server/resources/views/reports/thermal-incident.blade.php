<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Insiden Suhu</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }

        h1 {
            font-size: 19px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
        }

        th {
            width: 35%;
            background: #eff6ff;
        }

        .notice {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #f59e0b;
        }
    </style>
</head>

<body>
    <h1>Laporan Insiden Suhu</h1>
    <p>Insiden #{{ $incident->id }}</p>
    <p>Dibuat: {{ $generatedAt->format('d-m-Y H:i:s') }}</p>

    <table>
        <tr>
            <th>Nomor shipment</th>
            <td>{{ $incident->shipment->shipment_number ?? '-' }}</td>
        </tr>
        <tr>
            <th>Produk</th>
            <td>{{ $incident->shipment->product->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Customer</th>
            <td>{{ $incident->shipment->customer->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Suhu tercatat</th>
            <td>{{ $incident->temperature_celsius }} °C</td>
        </tr>
        <tr>
            <th>Rentang produk</th>
            <td>{{ $incident->allowed_min_temperature }} – {{ $incident->allowed_max_temperature }} °C</td>
        </tr>
        <tr>
            <th>Durasi paparan</th>
            <td>{{ $incident->exposure_duration_seconds !== null ? $incident->exposure_duration_seconds . ' detik' : 'Belum diketahui' }}
            </td>
        </tr>
        <tr>
            <th>Severity</th>
            <td>{{ $incident->severity }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ $incident->incident_status }}</td>
        </tr>
        <tr>
            <th>Waktu deteksi</th>
            <td>{{ $incident->detected_at?->format('d-m-Y H:i:s') ?? '-' }}</td>
        </tr>
        <tr>
            <th>Waktu pemulihan</th>
            <td>{{ $incident->recovered_at?->format('d-m-Y H:i:s') ?? '-' }}</td>
        </tr>
        <tr>
            <th>Deskripsi</th>
            <td>{{ $incident->description ?? '-' }}</td>
        </tr>
        <tr>
            <th>Tindakan korektif</th>
            <td>{{ $incident->corrective_action ?? '-' }}</td>
        </tr>
    </table>

    <div class="notice">
        Laporan ini mencatat insiden operasional. Keputusan tentang kelayakan
        produk harus mengikuti protokol stabilitas dan penilaian yang tervalidasi.
    </div>
</body>

</html>
