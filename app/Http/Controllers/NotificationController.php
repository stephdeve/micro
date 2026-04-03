<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->take(50)->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['notifications' => $notifications]);
        }

        return view('notifications.index', compact('notifications'));
    }

    public function unreadCount(Request $request)
    {
        $count = $request->user()->unreadNotifications()->count();
        return response()->json(['unread_count' => $count]);
    }

    public function markAsRead(Request $request, $notificationId)
    {
        $notification = $request->user()->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json(['message' => 'Notification introuvable'], 404);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function deleteRead(Request $request)
    {
        $request->user()->notifications()->whereNotNull('read_at')->delete();
        return response()->json(['success' => true]);
    }
}
