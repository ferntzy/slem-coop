<?php

namespace App\Traits;

use App\Models\Notification;
use App\Models\UserPushToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait Notifiable
{
    /**
     * Send a notification to a user.
     */
    public function sendNotification(int $userId, string $title, string $description, array $data = [])
    {
        // Create notification in database
        $notification = Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'description' => $description,
            'status' => 'unseen',
            'is_read' => false,
            'notifiable_type' => $data['notifiable_type'] ?? null,
            'notifiable_id' => $data['notifiable_id'] ?? null,
            'redirect_url' => $data['redirect_url'] ?? null,
        ]);

        // Send push notification
        $this->sendPushNotification($userId, $title, $description, [
            'notification_id' => $notification->id,
            ...$data,
        ]);

        return $notification;
    }

    /**
     * Send push notification to a user's devices.
     */
    public function sendPushNotification(int $userId, string $title, string $body, array $data = [])
    {
        $tokens = UserPushToken::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        if ($tokens->isEmpty()) {
            return false;
        }

        $results = [];
        foreach ($tokens as $token) {
            try {
                $payload = [
                    'to' => $token->push_token,
                    'title' => $title,
                    'body' => $body,
                    'data' => $data,
                    'sound' => 'default',
                    'priority' => 'high',
                    'badge' => 1,
                ];

                if ($token->device_type === 'android') {
                    $payload['channelId'] = 'default';
                    $payload['sticky'] = true;
                    $payload['autoDismiss'] = false;
                }

                $response = Http::connectTimeout(5)
                    ->timeout(10)
                    ->retry(2, 200)
                    ->post('https://exp.host/--/api/v2/push/send', $payload);

                if ($response->successful()) {
                    $token->touchLastUsed();
                    $results[] = [
                        'success' => true,
                        'device_type' => $token->device_type,
                        'status' => 'sent',
                    ];
                } else {
                    // If token is invalid (410 Gone), deactivate it
                    if ($response->status() === 410) {
                        $token->deactivate();
                        $results[] = [
                            'success' => false,
                            'device_type' => $token->device_type,
                            'status' => 'invalid_token_deactivated',
                        ];
                    } else {
                        $results[] = [
                            'success' => false,
                            'device_type' => $token->device_type,
                            'status' => 'failed',
                            'error' => $response->json(),
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error('Push notification failed: '.$e->getMessage());
                $results[] = [
                    'success' => false,
                    'device_type' => $token->device_type,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Send bulk notifications to multiple users.
     */
    public function sendBulkNotifications(array $userIds, string $title, string $description, array $data = [])
    {
        $results = [];
        foreach ($userIds as $userId) {
            try {
                $notification = $this->sendNotification($userId, $title, $description, $data);
                $results[] = [
                    'user_id' => $userId,
                    'success' => true,
                    'notification_id' => $notification->id,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'user_id' => $userId,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
