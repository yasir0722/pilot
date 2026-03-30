<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use App\Models\HabitCompletion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HabitController extends Controller
{
    public function index(): JsonResponse
    {
        $habits = Habit::where('active', true)
            ->with(['completions' => fn ($q) => $q->where('completed_date', '>=', now()->subDays(30))])
            ->get()
            ->map(function ($habit) {
                $habit->completed_today = $habit->isCompletedToday();
                return $habit;
            });

        return response()->json($habits);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'frequency' => 'required|in:daily,weekly,monthly',
            'reminder_time' => 'nullable|date_format:H:i',
        ]);

        $habit = Habit::create($validated);

        return response()->json($habit, 201);
    }

    public function update(Request $request, Habit $habit): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'frequency' => 'sometimes|in:daily,weekly,monthly',
            'reminder_time' => 'nullable|date_format:H:i',
            'active' => 'sometimes|boolean',
        ]);

        $habit->update($validated);

        return response()->json($habit);
    }

    public function destroy(Habit $habit): JsonResponse
    {
        $habit->delete();

        return response()->json(null, 204);
    }

    public function complete(Habit $habit): JsonResponse
    {
        $completion = HabitCompletion::firstOrCreate([
            'habit_id' => $habit->id,
            'completed_date' => today(),
        ]);

        return response()->json($completion);
    }

    public function uncomplete(Habit $habit): JsonResponse
    {
        HabitCompletion::where('habit_id', $habit->id)
            ->where('completed_date', today())
            ->delete();

        return response()->json(null, 204);
    }
}
