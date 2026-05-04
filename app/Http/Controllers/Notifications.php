<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\UserPushToken;
use App\Traits\Notifiable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Notifications extends Controller
{
    use Notifiable;

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

    // Register push token for a user
    public function registerPushToken(Request $request)
    {
        Log::info('=== registerPushToken hit ===', $request->all());
        try {
            Log::info('Register push token request:', $request->all());

            $validated = $request->validate([
                'profile_id' => 'required', // Remove string requirement
                'push_token' => 'required|string',
                'device_type' => 'required|string|in:ios,android',
                'device_name' => 'nullable|string',
                'app_version' => 'nullable|string',
                'os_version' => 'nullable|string',
            ]);

            Log::info('Validation passed', $validated);

            // Convert profile_id to string for comparison
            $profileId = (string) $validated['profile_id'];

            $user = User::where('profile_id', $profileId)->first();

            if (! $user) {
                Log::error('User not found for profile_id: '.$profileId);

                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            Log::info('User found:', ['user_id' => $user->user_id]);

            // Update or create push token
            $pushToken = UserPushToken::updateOrCreate(
                [
                    'user_id' => $user->user_id,
                    'device_type' => $validated['device_type'],
                ],
                [
                    'push_token' => $validated['push_token'],
                    'is_active' => true,
                    'last_used_at' => now(),
                    'device_name' => $validated['device_name'] ?? null,
                    'app_version' => $validated['app_version'] ?? null,
                    'os_version' => $validated['os_version'] ?? null,
                ]
            );

            Log::info('Push token saved:', ['id' => $pushToken->id]);

            return response()->json([
                'success' => true,
                'message' => 'Push token registered successfully',
            ], 200);

        } catch (ValidationException $e) {
            Log::error('Validation error:', $e->errors());

            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Register push token error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server Error: '.$e->getMessage(),
            ], 500);
        }
    }

    // Send push notification to a specific user
    public function sendPushNotificationToUser(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer',
                'title' => 'required|string',
                'body' => 'required|string',
                'data' => 'nullable|array',
            ]);

            // Use the trait method
            $results = $this->sendPushNotification(
                $validated['user_id'],
                $validated['title'],
                $validated['body'],
                $validated['data'] ?? []
            );

            if (! $results) {
                return response()->json([
                    'message' => 'No active push tokens found for this user',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'results' => $results,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to send push notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Create notification and send push (combined function)
    public function createAndSendNotification(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer',
                'title' => 'required|string',
                'description' => 'required|string',
                'redirect_url' => 'nullable|string',
                'notifiable_type' => 'nullable|string',
                'notifiable_id' => 'nullable|integer',
                'data' => 'nullable|array',
            ]);

            // Use the trait method to create notification and send push
            $notification = $this->sendNotification(
                $validated['user_id'],
                $validated['title'],
                $validated['description'],
                [
                    'redirect_url' => $validated['redirect_url'] ?? null,
                    'notifiable_type' => $validated['notifiable_type'] ?? null,
                    'notifiable_id' => $validated['notifiable_id'] ?? null,
                    ...($validated['data'] ?? []),
                ]
            );

            return response()->json([
                'success' => true,
                'notification' => $notification,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to create notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Send bulk notifications to multiple users
    public function sendBulkNotifications(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'required|integer',
                'title' => 'required|string',
                'description' => 'required|string',
                'data' => 'nullable|array',
            ]);

            // Use the trait method for bulk notifications
            $results = $this->sendBulkNotifications(
                $validated['user_ids'],
                $validated['title'],
                $validated['description'],
                $validated['data'] ?? []
            );

            return response()->json([
                'success' => true,
                'results' => $results,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to send bulk notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Deactivate push token (when user logs out)
    public function deactivatePushToken(Request $request)
    {
        try {
            $validated = $request->validate([
                'profile_id' => 'required|string',
                'push_token' => 'required|string',
            ]);

            $user = User::where('profile_id', $validated['profile_id'])->first();

            if ($user) {
                $updated = UserPushToken::where('user_id', $user->user_id)
                    ->where('push_token', $validated['push_token'])
                    ->update(['is_active' => false]);

                if ($updated) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Push token deactivated successfully',
                    ], 200);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Push token not found',
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to deactivate push token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Get all active push tokens for a user (admin/debugging)
    public function getUserPushTokens(Request $request)
    {
        try {
            $validated = $request->validate([
                'profile_id' => 'required|string',
            ]);

            $user = User::where('profile_id', $validated['profile_id'])->first();

            if (! $user) {
                return response()->json([
                    'message' => 'User not found',
                ], 404);
            }

            $tokens = UserPushToken::where('user_id', $user->user_id)
                ->get()
                ->map(function ($token) {
                    return [
                        'id' => $token->id,
                        'device_type' => $token->device_type,
                        'is_active' => $token->is_active,
                        'device_name' => $token->device_name,
                        'last_used_at' => $token->last_used_at,
                        'masked_token' => $token->masked_token ?? substr($token->push_token, 0, 20).'...',
                    ];
                });

            return response()->json([
                'success' => true,
                'tokens' => $tokens,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to fetch push tokens',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Web methods (existing)
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
