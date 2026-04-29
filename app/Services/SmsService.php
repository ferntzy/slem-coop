<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $apiKey;

    protected string $domain;

    protected string $fromName;

    protected Client $client;

    public function __construct()
    {
        $this->apiKey = config('services.mailgun.secret');
        $this->domain = config('services.mailgun.domain');
        $this->fromName = config('services.mailgun.sms_from', 'SLEM Coop');
        $this->client = new Client([
            'base_uri' => 'https://api.mailgun.net/v3',
            'timeout' => 15,
        ]);
    }

    public function sendBulkSms(
        array $numbers,
        string $message,
        ?string $senderId = null,
        ?string $schedule_time = null
    ): array {
        $senderId ??= $this->fromName;

        $recipients = array_map(function ($n) {
            $digits = preg_replace('/\D+/', '', (string) $n);
            if (preg_match('/^09\d{9}$/', $digits)) {
                return '+63'.substr($digits, 1);
            }

            return $digits;
        }, $numbers);

        $payload = [
            'from' => $senderId,
            'to' => implode(',', $recipients),
            'message' => $message,
        ];

        if ($schedule_time) {
            $payload['o:deliverytime'] = $schedule_time;
        }

        try {
            $response = $this->client->post("{$this->domain}/messages", [
                'auth' => ['api', $this->apiKey],
                'form_params' => $payload,
            ]);

            $bodyRaw = (string) $response->getBody();
            $body = json_decode($bodyRaw, true) ?? [
                'status' => 'error',
                'message' => 'Invalid JSON',
                'raw' => $bodyRaw,
            ];

            Log::info('Mailgun SMS send', [
                'recipient' => implode(',', $recipients),
                'status' => $body['message'] ?? null,
            ]);

            return [
                'status' => $body['message'] ?? 'ok',
                'data' => $body,
                'message' => $body['message'] ?? null,
            ];
        } catch (RequestException $e) {
            $resp = $e->hasResponse() ? (string) $e->getResponse()->getBody() : null;
            Log::error('Mailgun SMS RequestException', [
                'error' => $e->getMessage(),
                'response' => $resp,
            ]);

            return [
                'status' => 'error',
                'message' => $resp ?? $e->getMessage(),
                'raw' => $resp,
            ];
        }
    }
}
