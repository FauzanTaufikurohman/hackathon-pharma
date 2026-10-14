<?php

namespace App;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourierEvidenceStore
{
    public function store(UploadedFile $file): array
    {
        $size = getimagesize($file->path());
        abort_unless($size && $size[0] * $size[1] <= 16000000, 422, 'Foto maksimal 16 megapiksel.');
        $bytes = file_get_contents($file->path());
        $hash = hash('sha256', $bytes);
        $path = 'courier-evidence/'.Str::uuid().'.bin';
        $payload = json_encode(['bytes' => base64_encode($bytes), 'mime' => $file->getMimeType(), 'sha256' => $hash], JSON_THROW_ON_ERROR);
        abort_unless(Storage::disk('local')->put($path, Crypt::encryptString($payload)), 503, 'Bukti belum berhasil disimpan.');

        return ['path' => $path, 'sha256' => $hash];
    }

    public function remove(string $path): void
    {
        Storage::disk('local')->delete($path);
    }

    public function response(string $path): Response
    {
        abort_unless(preg_match('#^courier-evidence/[a-f0-9-]+\\.bin$#', $path) && Storage::disk('local')->exists($path), 404, 'Bukti tidak tersedia.');
        try {
            $payload = json_decode(Crypt::decryptString(Storage::disk('local')->get($path)), true, flags: JSON_THROW_ON_ERROR);
            $bytes = base64_decode($payload['bytes'], true);
            abort_unless($bytes !== false && hash_equals($payload['sha256'], hash('sha256', $bytes))
                && in_array($payload['mime'], ['image/jpeg', 'image/png', 'image/webp'], true), 409, 'Integritas bukti tidak cocok.');
        } catch (DecryptException|\JsonException $exception) {
            abort(409, 'Bukti tidak dapat diverifikasi.');
        }

        return response($bytes, 200, ['Content-Type' => $payload['mime'], 'Cache-Control' => 'private, no-store',
            'Content-Disposition' => 'inline; filename="bukti-pengiriman"', 'X-Content-Type-Options' => 'nosniff']);
    }
}
