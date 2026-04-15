<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class LoanOfficerNotifController extends Controller
{
    // List notifications for a loan officer's profile
    public function index()
    {
        $notifications = Notification::orderBy('created_at', 'desc')->get();

        return response()->json($notifications);
    }

    // Update notification (e.g., mark as read)
    public function update(Request $request, $notificationId)
    {
        $notification = Notification::findOrFail($notificationId);

        // Update fields (e.g., read status)
        $notification->update($request->only(['is_read']));

        return response()->json(['message' => 'Notification updated', 'notification' => $notification]);
    }

    // Delete a notification
    public function destroy($notificationId)
    {
        $notification = Notification::findOrFail($notificationId);
        $notification->delete();

        return response()->json(['message' => 'Notification deleted']);
    }
}
