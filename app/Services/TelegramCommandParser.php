<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\GroceryItem;
use App\Models\GroceryList;
use App\Models\Habit;
use App\Models\Vehicle;
use App\Models\VehicleService;

class TelegramCommandParser
{
    /**
     * Parse a Telegram message and execute the appropriate action.
     *
     * Supported formats:
     *   rm12 lunch       → Add expense: amount=12, description=lunch
     *   rm12.5 coffee    → Add expense: amount=12.5, description=coffee
     *   add milk         → Add grocery item "milk" to active list
     *   done meditate    → Mark habit "meditate" as complete
     *   summary          → Return daily summary
     *   service car rm150 oil change → Log vehicle service
     *   help             → Show available commands
     */
    public function parse(string $text): string
    {
        $text = trim($text);

        // /start command
        if (str_starts_with($text, '/start')) {
            return $this->help();
        }

        // /help command
        if ($text === '/help' || $text === 'help') {
            return $this->help();
        }

        // summary command
        if ($text === 'summary' || $text === '/summary') {
            return $this->summary();
        }

        // Expense: rm{amount} {description}
        if (preg_match('/^rm(\d+(?:\.\d{1,2})?)\s+(.+)$/i', $text, $matches)) {
            return $this->addExpense((float) $matches[1], trim($matches[2]));
        }

        // Grocery: add {item}
        if (preg_match('/^add\s+(.+)$/i', $text, $matches)) {
            return $this->addGroceryItem(trim($matches[1]));
        }

        // Habit: done {habit_name}
        if (preg_match('/^done\s+(.+)$/i', $text, $matches)) {
            return $this->completeHabit(trim($matches[1]));
        }

        // Vehicle service: service {vehicle} rm{amount} {description}
        if (preg_match('/^service\s+(\S+)\s+rm(\d+(?:\.\d{1,2})?)\s+(.+)$/i', $text, $matches)) {
            return $this->addVehicleService(trim($matches[1]), (float) $matches[2], trim($matches[3]));
        }

        return "Unknown command. Send *help* to see available commands.";
    }

    private function addExpense(float $amount, string $description): string
    {
        $expense = Expense::create([
            'amount' => $amount,
            'description' => $description,
            'date' => today(),
            'source' => 'telegram',
        ]);

        $todayTotal = Expense::whereDate('date', today())->sum('amount');

        return "Expense added: *{$description}* — RM{$amount}\nToday's total: RM{$todayTotal}";
    }

    private function addGroceryItem(string $name): string
    {
        $list = GroceryList::where('is_template', false)
            ->latest()
            ->first();

        if (!$list) {
            $list = GroceryList::create(['name' => 'Grocery - ' . now()->format('M d')]);
        }

        $list->items()->create(['name' => $name]);

        $pending = $list->items()->where('completed', false)->count();

        return "Added *{$name}* to grocery list.\n{$pending} items pending.";
    }

    private function completeHabit(string $name): string
    {
        $habit = Habit::where('active', true)
            ->where('name', 'like', "%{$name}%")
            ->first();

        if (!$habit) {
            return "Habit *{$name}* not found.";
        }

        $habit->completions()->firstOrCreate(['completed_date' => today()]);

        $total = Habit::where('active', true)->count();
        $done = Habit::where('active', true)
            ->whereHas('completions', fn ($q) => $q->where('completed_date', today()))
            ->count();

        return "Habit *{$habit->name}* marked complete!\n{$done}/{$total} habits done today.";
    }

    private function summary(): string
    {
        $todayExpenses = Expense::whereDate('date', today())->sum('amount');
        $habits = Habit::where('active', true)->count();
        $habitsDone = Habit::where('active', true)
            ->whereHas('completions', fn ($q) => $q->where('completed_date', today()))
            ->count();
        $groceryPending = GroceryItem::where('completed', false)->count();

        return "📊 *Daily Summary*\n\n"
            . "💰 Spent today: RM{$todayExpenses}\n"
            . "✅ Habits: {$habitsDone}/{$habits}\n"
            . "🛒 Grocery pending: {$groceryPending}";
    }

    private function addVehicleService(string $vehicleName, float $cost, string $description): string
    {
        $vehicle = Vehicle::where('name', 'like', "%{$vehicleName}%")->first();

        if (!$vehicle) {
            return "Vehicle *{$vehicleName}* not found.";
        }

        $vehicle->services()->create([
            'description' => $description,
            'cost' => $cost,
            'date' => today(),
        ]);

        $totalSpent = $vehicle->services()->sum('cost');

        return "🚗 Service logged for *{$vehicle->name}*\n{$description} — RM{$cost}\nTotal spent: RM{$totalSpent}";
    }

    private function help(): string
    {
        return "*Pilot Bot Commands*\n\n"
            . "`rm12 lunch` — Add RM12 expense for lunch\n"
            . "`add milk` — Add milk to grocery list\n"
            . "`done meditate` — Mark habit as done\n"
            . "`service car rm150 oil change` — Log vehicle service\n"
            . "`summary` — Daily summary\n"
            . "`help` — Show this message";
    }
}
