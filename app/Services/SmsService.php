<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $apiToken;
    protected string $baseUri;
    protected string $defaultSender;
    protected Client $client;

    public function __construct()
    {
        $this->apiToken      = config('services.philsms.token');
        $this->baseUri       = rtrim(config('services.philsms.base'), '/') . '/';
        $this->defaultSender = config('services.philsms.default_sender');
        $this->client        = new Client([
            'base_uri' => $this->baseUri,
            'timeout'  => 15,
        ]);
    }

    public function sendBulkSms(
        array $numbers,
        string $message,
        ?string $senderId = null,
        ?string $schedule_time = null
    ): array {
        $senderId ??= $this->defaultSender ?? 'PhilSMS';

        $recipients = array_map(function ($n) {
            $digits = preg_replace('/\D+/', '', (string) $n);
            if (preg_match('/^09\d{9}$/', $digits)) {
                return '63' . substr($digits, 1);
            }
            return $digits;
        }, $numbers);

        $payload = [
            'recipient' => implode(',', $recipients),
            'sender_id' => mb_substr($senderId, 0, 11),
            'type'      => 'plain',
            'message'   => $message,
        ];

        if ($schedule_time) {
            $payload['schedule_time'] = $schedule_time;
        }

        try {
            // POST to the exact documented endpoint
            $response = $this->client->post('sms/send', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);

            $bodyRaw = (string) $response->getBody();
            $body = json_decode($bodyRaw, true) ?? [
                'status'  => 'error',
                'message' => 'Invalid JSON',
                'raw'     => $bodyRaw,
            ];

            Log::info('PhilSMS send', [
                'recipient' => $payload['recipient'],
                'status'    => $body['status'] ?? null,
            ]);

            return [
                'status'  => $body['status'] ?? 'error',
                'data'    => $body['data'] ?? null,
                'message' => $body['message'] ?? null,
                'raw'     => $body,
            ];
        } catch (RequestException $e) {
            $resp = $e->hasResponse() ? (string) $e->getResponse()->getBody() : null;
            Log::error('PhilSMS RequestException', [
                'error'    => $e->getMessage(),
                'response' => $resp,
            ]);

            return [
                'status'  => 'error',
                'message' => $resp ?? $e->getMessage(),
                'raw'     => $resp,
            ];
        }
    }

    // Send SMS to a single number (for OTP)

    // public function send(string $number, string $message, string $senderId = 'Coop'): array
    // {
    //     return $this->sendBulkSms([$number], $message, $senderId);
    // }
}
