<?php

namespace App\Http\Controllers;

use App\TelegramLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorTelegramController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->is_active && in_array($user->role, ['buyer', 'vendor'], true), 403);

        return response()->json(['data' => ['connected' => filled($user->telegram_chat_id)]])
            ->header('Cache-Control', 'private, no-store');
    }

    public function createLink(Request $request, TelegramLinkService $telegram): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->is_active && in_array($user->role, ['buyer', 'vendor'], true), 403);

        return response()->json(['data' => $telegram->createLink($user)], 201)
            ->header('Cache-Control', 'private, no-store');
    }
}
