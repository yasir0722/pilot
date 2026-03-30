<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $token;
    private string $apiUrl;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token', '');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}";
    }

    public function sendMessage(string $chatId, string $text): void
    {
        if (empty($this->token)) {
            Log::warning('Telegram bot token not configured');
            return;
        }

        Http::post("{$this->apiUrl}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);
    }

    public function setWebhook(string $url): array
    {
        $response = Http::post("{$this->apiUrl}/setWebhook", [
            'url' => $url,
        ]);

        return $response->json();
    }
}
