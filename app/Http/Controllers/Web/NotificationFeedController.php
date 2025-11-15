<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\NotificationDispatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationFeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $dispatches = NotificationDispatch::query()
            ->with('notification')
            ->where('user_id', $user->id)
            ->where('channel', 'portal')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $unread = $dispatches->where('is_read', false)->count();

        return response()->json([
            'unread' => $unread,
            'notifications' => $dispatches->map(function (NotificationDispatch $dispatch) {
                return [
                    'id' => $dispatch->id,
                    'title' => optional($dispatch->notification)->title ?? 'Notifica',
                    'message' => data_get($dispatch->payload, 'message', optional($dispatch->notification)->message_body ?? ''),
                    'created_at' => optional($dispatch->created_at)->toIso8601String(),
                    'created_at_display' => optional($dispatch->created_at)->diffForHumans(),
                    'is_read' => (bool) $dispatch->is_read,
                ];
            }),
        ]);
    }

    public function markAsRead(Request $request, NotificationDispatch $dispatch): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $dispatch->user_id === $user->id, 403);

        if (!$dispatch->is_read) {
            $dispatch->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    public function destroy(Request $request, NotificationDispatch $dispatch): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $dispatch->user_id === $user->id, 403);

        $dispatch->delete();

        return response()->json(['status' => 'ok']);
    }
}
