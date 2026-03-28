<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = AppNotification::forUser($request->user()->id)
            ->when(app()->bound('currentCompany'), fn ($q) => $q->where('company_id', app('currentCompany')->id))
            ->orderByDesc('created_at')
            ->paginate(20);

        return inertia('App/Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Return unread count (JSON — for bell polling).
     */
    public function unreadCount(Request $request)
    {
        $count = AppNotification::forUser($request->user()->id)
            ->when(app()->bound('currentCompany'), fn ($q) => $q->where('company_id', app('currentCompany')->id))
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark one notification as read.
     */
    public function markRead(Request $request, AppNotification $notification)
    {
        abort_if($notification->user_id !== $request->user()->id, 403);

        $notification->update(['is_read' => true]);

        return back()->with('success', 'Notification lue.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request)
    {
        AppNotification::forUser($request->user()->id)
            ->when(app()->bound('currentCompany'), fn ($q) => $q->where('company_id', app('currentCompany')->id))
            ->unread()
            ->update(['is_read' => true]);

        return back()->with('success', 'Toutes les notifications marquées comme lues.');
    }
}
