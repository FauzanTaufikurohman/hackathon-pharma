
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengiriman</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 19px; margin-bottom: 4px; }
        h2 { font-size: 13px; margin-top: 20px; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #eff6ff; }
        .muted { color: #64748b; }
        .notice { border: 1px solid #f59e0b; padding: 8px; margin-top: 12px; }
    </style>
</head>
<body>
    <h1>Laporan Pengiriman Anteraja Pharma</h1>
    <p class="muted">
        Nomor: {{ $shipment->shipment_number }}<br>
        Dibuat pada: {{ $generatedAt->format('d-m-Y H:i:s') }}
    </p>

    <h2>Informasi Pengiriman</h2>
    <table>
        <tr><th>Nomor pengiriman</th><td>{{ $shipment->shipment_number }}</td></tr>
        <tr><th>Status</th><td>{{ $shipment->status }}</td></tr>
        <tr><th>Produsen / pengirim</th><td>{{ $shipment->originCustomerUser->customer->company_name ?? $shipment->customer->company_name ?? '-' }}</td></tr>
        <tr><th>Kontak pengirim</th><td>{{ $shipment->originCustomerUser->name ?? '-' }} · {{ $shipment->originCustomerUser->customer->whatsapp_number ?? '-' }}</td></tr>
        <tr><th>Distributor / apotek</th><td>{{ $shipment->destinationCustomerUser->customer->company_name ?? '-' }}</td></tr>
        <tr><th>Kontak penerima</th><td>{{ $shipment->destinationCustomerUser->name ?? '-' }} · {{ $shipment->destinationCustomerUser->customer->whatsapp_number ?? '-' }}</td></tr>
        <tr><th>Produk</th><td>{{ $shipment->product->name ?? '-' }}</td></tr>
        <tr><th>Jumlah</th><td>{{ $shipment->quantity }}</td></tr>
        <tr><th>Asal</th><td>{{ $shipment->origin_address }}</td></tr>
        <tr><th>Tujuan</th><td>{{ $shipment->destination_address }}</td></tr>
        <tr><th>Checker</th><td>{{ $shipment->checker->name ?? '-' }}</td></tr>
        <tr><th>Kurir</th><td>{{ $shipment->courier->name ?? '-' }}</td></tr>
        <tr><th>Mulai</th><td>{{ $shipment->started_at?->format('d-m-Y H:i:s') ?? '-' }}</td></tr>
        <tr><th>Selesai</th><td>{{ $shipment->delivered_at?->format('d-m-Y H:i:s') ?? '-' }}</td></tr>
    </table>

    <h2>Analisis AI (saran operasional)</h2>
    <table>
        <thead>
            <tr><th>Waktu</th><th>Risiko</th><th>Ringkasan</th><th>Status</th></tr>
        </thead>
        <tbody>
        @foreach ($shipment->thermalChecks as $check)
            @if ($check->ai_assessment)
                <tr>
                    <td>{{ $check->ai_assessed_at?->format('d-m-Y H:i:s') ?? '-' }}</td>
                    <td>{{ $check->ai_assessment['risk_level'] ?? 'unknown' }}</td>
                    <td>{{ $check->ai_assessment['summary'] ?? '-' }}</td>
                    <td>{{ $check->ai_assessment_status }}</td>
                </tr>
            @endif
        @endforeach
        @foreach ($shipment->thermalIncidents as $incident)
            @if ($incident->ai_assessment)
                <tr>
                    <td>{{ $incident->ai_assessed_at?->format('d-m-Y H:i:s') ?? '-' }}</td>
                    <td>{{ $incident->ai_assessment['risk_level'] ?? 'unknown' }}</td>
                    <td>{{ $incident->ai_assessment['summary'] ?? '-' }}</td>
                    <td>{{ $incident->ai_assessment_status }}</td>
                </tr>
            @endif
        @endforeach
        @if ($shipment->thermalChecks->every(fn ($check) => ! $check->ai_assessment)
            && $shipment->thermalIncidents->every(fn ($incident) => ! $incident->ai_assessment))
            <tr><td colspan="4">Belum ada hasil analisis AI.</td></tr>
        @endif
        </tbody>
    </table>

    <h2>Pemeriksaan Suhu</h2>
    <table>
        <thead>
            <tr><th>Waktu</th><th>Suhu °C</th><th>Kemasan</th><th>Kondisi fisik</th><th>Catatan</th></tr>
        </thead>
        <tbody>
        @forelse ($shipment->thermalChecks as $check)
            <tr>
                <td>{{ $check->checked_at?->format('d-m-Y H:i:s') ?? '-' }}</td>
                <td>{{ $check->temperature_celsius }}</td>
                <td>{{ $check->packaging_condition }}</td>
                <td>{{ $check->physical_condition }}</td>
                <td>{{ $check->notes ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="5">Belum ada pemeriksaan suhu.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>Insiden Suhu</h2>
    <table>
        <thead>
            <tr><th>Waktu</th><th>Suhu °C</th><th>Rentang °C</th><th>Durasi detik</th><th>Severity</th><th>Status</th></tr>
        </thead>
        <tbody>
        @forelse ($shipment->thermalIncidents as $incident)
            <tr>
                <td>{{ $incident->detected_at?->format('d-m-Y H:i:s') ?? '-' }}</td>
                <td>{{ $incident->temperature_celsius }}</td>
                <td>{{ $incident->allowed_min_temperature }} – {{ $incident->allowed_max_temperature }}</td>
                <td>{{ $incident->exposure_duration_seconds ?? 'Tidak diketahui' }}</td>
                <td>{{ $incident->severity }}</td>
                <td>{{ $incident->incident_status }}</td>
            </tr>
        @empty
            <tr><td colspan="6">Tidak ada insiden yang tercatat.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>Riwayat Peristiwa</h2>
    <table>
        <thead>
            <tr><th>Waktu</th><th>Peristiwa</th><th>Perubahan status</th><th>Deskripsi</th></tr>
        </thead>
        <tbody>
        @forelse ($shipment->events as $event)
            <tr>
                <td>{{ $event->occurred_at?->format('d-m-Y H:i:s') ?? '-' }}</td>
                <td>{{ $event->event_type }}</td>
                <td>{{ $event->from_status ?? '-' }} → {{ $event->to_status ?? '-' }}</td>
                <td>{{ $event->description ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="4">Belum ada riwayat peristiwa.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="notice">
        <strong>Catatan:</strong>
        Laporan ini merupakan rekaman operasional berdasarkan data yang tersimpan.
        Lokasi dapat berupa simulasi. Catatan suhu dan status insiden tidak dengan
        sendirinya membuktikan kelayakan atau keamanan produk farmasi.
    </div>
</body>
</html>
