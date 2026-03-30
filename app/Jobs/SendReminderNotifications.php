<?php

namespace App\Jobs;

use App\Models\Reminder;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReminderNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(TelegramService $telegram): void
    {
        $chatId = config('services.telegram.bot_token') ? null : null;
        // In a real setup, you'd store chat_id from the first /start interaction.
        // For now, reminders are logged. Extend with a settings table for chat_id.

        $reminders = Reminder::where('active', true)
            ->where('sent', false)
            ->where('remind_at', '<=', now())
            ->get();

        foreach ($reminders as $reminder) {
            // Log the reminder for now; Telegram send requires a stored chat_id
            \Illuminate\Support\Facades\Log::info("Reminder due: {$reminder->title}");

            $reminder->update(['sent' => true]);

            // Handle recurrence
            if ($reminder->recurrence !== 'none') {
                $nextDate = match ($reminder->recurrence) {
                    'daily' => $reminder->remind_at->addDay(),
                    'weekly' => $reminder->remind_at->addWeek(),
                    'monthly' => $reminder->remind_at->addMonth(),
                };

                Reminder::create([
                    'title' => $reminder->title,
                    'body' => $reminder->body,
                    'remind_at' => $nextDate,
                    'recurrence' => $reminder->recurrence,
                ]);
            }
        }
    }
}
