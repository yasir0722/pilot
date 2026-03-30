<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Habit;
use App\Models\Reminder;
use Illuminate\Http\JsonResponse;

class SummaryController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $today = now();

        $habits = Habit::where('active', true)
            ->with(['completions' => fn ($q) => $q->where('completed_date', today())])
            ->get();

        return response()->json([
            'expenses' => [
                'today' => Expense::whereDate('date', $today)->sum('amount'),
                'this_month' => Expense::whereMonth('date', $today->month)
                    ->whereYear('date', $today->year)
                    ->sum('amount'),
            ],
            'habits' => [
                'total' => $habits->count(),
                'completed_today' => $habits->filter->isCompletedToday()->count(),
            ],
            'upcoming_reminders' => Reminder::where('active', true)
                ->where('remind_at', '>=', $today)
                ->orderBy('remind_at')
                ->limit(5)
                ->get(['id', 'title', 'remind_at']),
        ]);
    }
}
