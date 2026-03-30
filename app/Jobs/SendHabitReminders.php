<?php

namespace App\Jobs;

use App\Models\Habit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendHabitReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = now();

        $habits = Habit::where('active', true)
            ->whereNotNull('reminder_time')
            ->get()
            ->filter(function ($habit) use ($now) {
                $reminderTime = $habit->reminder_time;
                return $reminderTime
                    && $now->format('H:i') === $reminderTime->format('H:i')
                    && !$habit->isCompletedToday();
            });

        foreach ($habits as $habit) {
            Log::info("Habit reminder: {$habit->name}");
            // Extend to send Telegram notification when chat_id is stored
        }
    }
}
