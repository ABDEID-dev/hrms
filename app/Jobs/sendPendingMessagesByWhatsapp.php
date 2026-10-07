<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Throwable;

class sendPendingMessagesByWhatsapp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $pendingMessages = Message::where('is_sent', 0)->get();

        foreach ($pendingMessages as $messages) {
            $response = $this->sendText($messages->text, $messages->recipient);
            // info($messages->recipient);
            if ($response == true) {
                $messages->update(['is_sent' => true, 'error' => 'Sent by WhatsApp API']);
            } else {
                $messages->update(['is_sent' => false, 'error' => '!! NOT SENT !!']);
            }
            sleep(1);
        }
    }

    public function sendText($messageBody = 'Test', $recipientNumbers = '933697861')
    {
        $recipientNumbers = preg_replace('/\D+/', '', (string) $recipientNumbers);
        $driver = Config::get('services.whatsapp.driver', 'waha');

        if ($driver === 'wsapi') {
            return $this->sendViaWsapi($messageBody, $recipientNumbers);
        }

        $baseUrl = Config::get('services.whatsapp.base_url', 'http://localhost:3000');
        $session = Config::get('services.whatsapp.session', 'default');

        try {
            $response = Http::timeout(12)
                ->connectTimeout(5)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($baseUrl.'/api/sendText', [
                    'session' => $session,
                    'chatId' => $recipientNumbers.'@c.us',
                    'text' => $messageBody,
                ]);
        } catch (Throwable $exception) {
            return $this->formatWhatsappException($exception);
        }

        if ($response->getStatusCode() == 201) {
            return true;
        } else {
            return $this->formatWhatsappResponse($response);
        }
    }

    public function sendDocument($recipientNumbers, $documentData, $fileName, $caption = null)
    {
        $recipientNumbers = preg_replace('/\D+/', '', (string) $recipientNumbers);
        $driver = Config::get('services.whatsapp.driver', 'waha');

        if ($driver === 'wsapi') {
            return $this->sendDocumentViaWsapi($recipientNumbers, $documentData, $fileName, $caption);
        }

        return 'Sending PDF documents is currently supported only with the WSAPI driver in this project.';
    }

    private function sendViaWsapi(string $messageBody, string $recipientNumbers)
    {
        $apiKey = Config::get('services.whatsapp.api_key');
        $instanceId = Config::get('services.whatsapp.instance_id');
        $baseUrl = Config::get('services.whatsapp.base_url', 'https://api.wsapi.chat');
        $recipientJid = $recipientNumbers.'@s.whatsapp.net';

        if (! $apiKey || ! $instanceId) {
            return 'WSAPI is not configured. Please set WHATSAPP_API_KEY and WHATSAPP_INSTANCE_ID in .env';
        }

        $headers = [
            'Content-Type' => 'application/json',
            'X-Api-Key' => $apiKey,
            'X-Instance-Id' => $instanceId,
        ];

        try {
            $response = Http::timeout(12)
                ->connectTimeout(5)
                ->withHeaders($headers)
                ->post($baseUrl.'/messages/text', [
                    'to' => $recipientJid,
                    'message' => $messageBody,
                ]);
        } catch (Throwable $exception) {
            return $this->formatWhatsappException($exception);
        }

        if ($response->successful()) {
            return true;
        }

        $responseBody = (string) $response->body();

        // Some WSAPI deployments still validate the legacy `text` field name.
        if ($response->status() === 400 && str_contains($responseBody, 'SendTextRequest.Text')) {
            try {
                $fallbackResponse = Http::timeout(12)
                    ->connectTimeout(5)
                    ->withHeaders($headers)
                    ->post($baseUrl.'/messages/text', [
                        'to' => $recipientJid,
                        'text' => $messageBody,
                    ]);
            } catch (Throwable $exception) {
                return $this->formatWhatsappException($exception);
            }

            if ($fallbackResponse->successful()) {
                return true;
            }

            return $this->formatWhatsappResponse($fallbackResponse);
        }

        return $this->formatWhatsappResponse($response);
    }

    private function sendDocumentViaWsapi(string $recipientNumbers, string $documentData, string $fileName, ?string $caption = null)
    {
        $apiKey = Config::get('services.whatsapp.api_key');
        $instanceId = Config::get('services.whatsapp.instance_id');
        $baseUrl = Config::get('services.whatsapp.base_url', 'https://api.wsapi.chat');
        $recipientJid = $recipientNumbers.'@s.whatsapp.net';

        if (! $apiKey || ! $instanceId) {
            return 'WSAPI is not configured. Please set WHATSAPP_API_KEY and WHATSAPP_INSTANCE_ID in .env';
        }

        $payload = [
            'to' => $recipientJid,
            'data' => $documentData,
            'fileName' => $fileName,
        ];

        if ($caption !== null && trim($caption) !== '') {
            $payload['caption'] = $caption;
        }

        try {
            $response = Http::timeout(20)
                ->connectTimeout(5)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Api-Key' => $apiKey,
                    'X-Instance-Id' => $instanceId,
                ])->post($baseUrl.'/messages/document', $payload);
        } catch (Throwable $exception) {
            return $this->formatWhatsappException($exception);
        }

        if ($response->successful()) {
            return true;
        }

        return $this->formatWhatsappResponse($response);
    }

    private function formatWhatsappException(Throwable $exception): string
    {
        $message = trim($exception->getMessage());

        return $message !== ''
            ? 'WhatsApp API error: '.$message
            : 'WhatsApp API error: connection failed.';
    }

    private function formatWhatsappResponse($response): string
    {
        $body = trim((string) $response->body());
        $decoded = json_decode($body, true);
        $detail = is_array($decoded) ? (string) ($decoded['detail'] ?? '') : '';
        $status = is_array($decoded) ? (string) ($decoded['status'] ?? $response->status()) : (string) $response->status();

        if (str_contains(strtolower($detail), 'instance has expired')) {
            return 'WhatsApp instance has expired. Please reconnect or renew the WhatsApp instance in WSAPI, then try again.';
        }

        if ($response->status() === 401) {
            return $detail !== ''
                ? 'WhatsApp API unauthorized: '.$detail
                : 'WhatsApp API unauthorized. Please check the API key, instance ID, and connected WhatsApp session.';
        }

        if ($detail !== '') {
            return 'WhatsApp API error '.$status.': '.$detail;
        }

        return $body !== ''
            ? $body
            : 'WhatsApp API error '.$response->status().'.';
    }

    // https://waha.devlike.pro/docs/how-to/contacts/#check-phone-number-exists
    public function checkExists($recipientNumbers)
    {
        $driver = Config::get('services.whatsapp.driver', 'waha');

        if ($driver === 'wsapi') {
            return 'Contact existence check is not implemented for WSAPI in this project yet.';
        }

        $baseUrl = Config::get('services.whatsapp.base_url', 'http://localhost:3000');
        $session = Config::get('services.whatsapp.session', 'default');

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get($baseUrl.'/api/contacts/check-exists', [
            'session' => $session,
            'phone' => $recipientNumbers,
        ]);

        dd($response);
    }
}
