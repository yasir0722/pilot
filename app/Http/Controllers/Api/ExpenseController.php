<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;

class ExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Expense::with('receipts');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $expenses = $query->orderByDesc('date')->paginate($request->integer('per_page', 25));

        return response()->json($expenses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'date' => 'required|date',
            'source' => 'nullable|string|in:manual,telegram,csv',
        ]);

        $expense = Expense::create($validated);

        return response()->json($expense, 201);
    }

    public function show(Expense $expense): JsonResponse
    {
        return response()->json($expense->load('receipts'));
    }

    public function update(Request $request, Expense $expense): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'sometimes|numeric|min:0.01',
            'description' => 'sometimes|string|max:255',
            'category' => 'nullable|string|max:100',
            'date' => 'sometimes|date',
        ]);

        $expense->update($validated);

        return response()->json($expense);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();

        return response()->json(null, 204);
    }

    public function importCsv(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('file')->getRealPath();
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        $imported = 0;
        foreach ($csv->getRecords() as $record) {
            $validator = Validator::make($record, [
                'amount' => 'required|numeric',
                'description' => 'required|string',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                continue;
            }

            Expense::create([
                'amount' => $record['amount'],
                'description' => $record['description'],
                'category' => $record['category'] ?? null,
                'date' => $record['date'],
                'source' => 'csv',
            ]);
            $imported++;
        }

        return response()->json(['imported' => $imported]);
    }

    public function summary(): JsonResponse
    {
        $today = now();

        return response()->json([
            'today' => Expense::whereDate('date', $today)->sum('amount'),
            'this_week' => Expense::whereBetween('date', [
                $today->copy()->startOfWeek(),
                $today->copy()->endOfWeek(),
            ])->sum('amount'),
            'this_month' => Expense::whereMonth('date', $today->month)
                ->whereYear('date', $today->year)
                ->sum('amount'),
            'by_category' => Expense::whereMonth('date', $today->month)
                ->whereYear('date', $today->year)
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')
                ->pluck('total', 'category'),
        ]);
    }
}
