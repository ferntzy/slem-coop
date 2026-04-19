<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Notifications extends Controller
{
    public function fetchNotifications(Request $request)
    {
        try {
            $uid = User::where('profile_id', $request->profile_id)->value('user_id');

            $notif = Notification::where('user_id', $uid)->latest()->get();

            return response()->json([
                'notifications' => $notif,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to fetch notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchUnreadNotifications(Request $request)
    {
        try {
            $uid = User::where('profile_id', $request->profile_id)->value('user_id');

            $notif = Notification::where('user_id', $uid)->where('status', 'unseen')->count();

            return response()->json([
                'notifications' => $notif,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to fetch unread notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteNotification(Request $request)
    {
        try {
            $delete = Notification::where('id', $request->notif_id)->delete();
            if (! $delete) {
                throw new Exception('Unable to delete data');
            }

            return response()->json([
                'success' => 'ok',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to delete notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function markAsRead(Request $request)
    {
        try {
            $markAsRead = Notification::where('id', $request->notif_id)->update([
                'status' => 'seen',
            ]);

            if (! $markAsRead) {
                throw new Exception('Unable to update status');
            }

            return response()->json([
                'status' => 'success',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'unable to update status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function WebfetchNotifications()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'user_id' => $notification->user_id,
                    'title' => $notification->title,
                    'description' => $notification->description,
                    'status' => $notification->status,
                    'is_read' => $notification->is_read,
                    'notifiable_type' => $notification->notifiable_type,
                    'notifiable_id' => $notification->notifiable_id,
                    'redirect_url' => $notification->getRedirectUrl(),
                    'created_at' => $notification->created_at,
                    'updated_at' => $notification->updated_at,
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread' => $notifications->where('is_read', false)->count(),
        ]);
    }

    public function WebdeleteNotification(Request $request)
    {
        $deleted = Notification::where('id', $request->notif_id)
            ->where('user_id', Auth::id())
            ->delete();

        return $deleted
            ? response()->json(['success' => true])
            : response()->json(['message' => 'Not found'], 404);
    }

    public function WebmarkAsRead(Request $request)
    {
        $updated = Notification::where('id', $request->notif_id)
            ->where('user_id', Auth::id())
            ->update(['is_read' => true]);

        return $updated
            ? response()->json(['success' => true])
            : response()->json(['message' => 'Not found'], 404);
    }

    public function WebmarkAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
