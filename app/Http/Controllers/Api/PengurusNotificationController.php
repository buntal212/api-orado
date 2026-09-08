<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengurusNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengurusNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = PengurusNotification::query()->where('user_id', $request->user()->id);

        return response()->json([
            'message' => 'Riwayat notifikasi berhasil ditampilkan.',
            'data' => (clone $notifications)->latest()->limit(30)->get(),
            'meta' => [
                'unread_count' => (clone $notifications)->where('is_read', 0)->count(),
            ],
        ]);
    }

    public function markAsRead(Request $request, PengurusNotification $notification): JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 404);

        if ($notification->is_read === 0) {
            $notification->update(['is_read' => 1]);
        }

        return response()->json([
            'message' => 'Notifikasi ditandai sudah dibaca.',
            'data' => $notification->fresh(),
            'meta' => [
                'unread_count' => PengurusNotification::query()
                    ->where('user_id', $request->user()->id)
                    ->where('is_read', 0)
                    ->count(),
            ],
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        PengurusNotification::query()
            ->where('user_id', $request->user()->id)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return response()->json([
            'message' => 'Semua notifikasi ditandai sudah dibaca.',
            'meta' => ['unread_count' => 0],
        ]);
    }
}
