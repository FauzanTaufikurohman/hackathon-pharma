<?php

namespace App;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;

class VendorReportService
{
    public function pdf(string $title, string $content, string $filename): Response
    {
        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isJavascriptEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $pdf = new Dompdf($options);
        $pdf->loadHtml('<!doctype html><html><head><meta charset="utf-8"><style>body{font:11px DejaVu Sans;color:#163c3c}h1{font-size:20px}table{width:100%;border-collapse:collapse}th,td{padding:7px;border-bottom:1px solid #ddd;text-align:left}footer{font-size:9px;color:#666}</style></head><body><h1>'.e($title).'</h1>'.$content.'<footer>Dokumen aplikasi demo Anteraja Pharma. Bukan sertifikat CDOB/CPOB atau PDF/A tersertifikasi.</footer></body></html>');
        $pdf->setPaper('A4');
        $pdf->render();
        $bytes = $pdf->output();

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store',
            'X-Report-SHA256' => hash('sha256', $bytes),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
