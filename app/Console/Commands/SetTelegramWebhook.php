<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class SetTelegramWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {url?}';
    protected $description = 'Set the Telegram bot webhook URL';

    public function handle(TelegramService $telegram): int
    {
        $url = $this->argument('url') ?? config('services.telegram.webhook_url');

        if (empty($url)) {
            $this->error('No webhook URL provided. Set TELEGRAM_WEBHOOK_URL in .env or pass as argument.');
            return self::FAILURE;
        }

        $result = $telegram->setWebhook($url);
        $this->info('Webhook set: ' . json_encode($result));

        return self::SUCCESS;
    }
}
