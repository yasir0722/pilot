<?php

use App\Jobs\SendHabitReminders;
use App\Jobs\SendReminderNotifications;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new SendReminderNotifications)->everyMinute();
Schedule::job(new SendHabitReminders)->everyMinute();
