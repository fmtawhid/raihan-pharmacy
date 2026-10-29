<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DeelkoSmsService
{
    public function send(string $mobile, string $message): array
    {
        $apiKey = config('services.deelko_sms.api_key');
        if (!$apiKey) {
            return ['success' => false, 'message' => 'SMS API is not configured on the server'];
        }

        return $this->sendUsingEndpoint($mobile, $message, 'non-masking');
    }

    public function sendMasking(string $mobile, string $message): array
    {
        $maskingId = config('services.deelko_sms.masking_id');
        if (!$maskingId) {
            return ['success' => false, 'message' => 'Deelko masking SMS sender ID is not configured on the server'];
        }

        return $this->sendUsingEndpoint($mobile, $message, 'masking', ['maskingId' => $maskingId]);
    }

    private function sendUsingEndpoint(string $mobile, string $message, string $endpoint, array $parameters = []): array
    {
        $apiKey = config('services.deelko_sms.api_key');
        if (!$apiKey) {
            return ['success' => false, 'message' => 'SMS API is not configured on the server'];
        }

        try {
            $response = Http::timeout(20)->get('https://sms.deelko.net/smsapi/v2/' . $endpoint, [
                'api_key' => $apiKey,
                'mobileNo' => $mobile,
                'smsContent' => $message,
                ...$parameters,
            ]);

            if ($response->successful() && (int) data_get($response->json(), 'code') === 1000) {
                return ['success' => true, 'message' => 'SMS sent successfully'];
            }

            return ['success' => false, 'message' => $this->errorMessage($response)];
        } catch (\Throwable $exception) {
            return ['success' => false, 'message' => 'Could not connect to the SMS service'];
        }
    }

    private function errorMessage($response): string
    {
        $code = (int) data_get($response->json(), 'code');
        $messages = [
            1001 => 'SMS account balance is insufficient',
            1002 => 'SMS reseller balance is insufficient',
            1003 => 'SMS API key is invalid or missing',
            1004 => 'SMS API reports missing required parameters',
            1005 => 'SMS masking ID is invalid',
            1006 => 'SMS message is too long',
            1007 => 'Phone number format or prefix is invalid',
            1008 => 'Deelko blocked this server IP. Add the server public IP to your Deelko allowlist.',
        ];

        return data_get($response->json(), 'message')
            ?? data_get($response->json(), 'description')
            ?? $messages[$code]
            ?? 'SMS service rejected the request (HTTP ' . $response->status() . ')';
    }
}