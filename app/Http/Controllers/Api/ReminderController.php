<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function index(): JsonResponse
    {
        $reminders = Reminder::where('active', true)
            ->orderBy('remind_at')
            ->get();

        return response()->json($reminders);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
            'remind_at' => 'required|date',
            'recurrence' => 'nullable|in:none,daily,weekly,monthly',
        ]);

        $reminder = Reminder::create($validated);

        return response()->json($reminder, 201);
    }

    public function update(Request $request, Reminder $reminder): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'body' => 'nullable|string',
            'remind_at' => 'sometimes|date',
            'recurrence' => 'nullable|in:none,daily,weekly,monthly',
            'active' => 'sometimes|boolean',
        ]);

        $reminder->update($validated);

        return response()->json($reminder);
    }

    public function destroy(Reminder $reminder): JsonResponse
    {
        $reminder->delete();

        return response()->json(null, 204);
    }
}
