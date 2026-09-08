<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OradoNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function recipients(): JsonResponse
    {
        return response()->json([
            'data' => User::query()
                ->whereDoesntHave('club')
                ->orderBy('name')
                ->get(['id', 'name', 'username', 'email']),
        ]);
    }

    public function store(Request $request, OradoNotificationService $notificationService): JsonResponse
    {
        $validated = $request->validate([
            'recipient_ids' => ['required', 'array', 'min:1'],
            'recipient_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $recipientIds = User::query()
            ->whereDoesntHave('club')
            ->whereIn('id', $validated['recipient_ids'])
            ->pluck('id')
            ->all();

        $notificationService->sendToPengurusUsers($recipientIds, $validated['title'], $validated['body'], [
            'type' => 'announcement',
            'menu_label' => 'Pengumuman ORADO',
            'url' => '/notifikasi',
            'destination_url' => '/pengumuman',
        ]);

        return response()->json([
            'message' => 'Pengumuman berhasil dikirim ke '.count($recipientIds).' penerima.',
        ]);
    }
}
