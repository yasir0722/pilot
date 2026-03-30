<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GroceryItem;
use App\Models\GroceryList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroceryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(GroceryList::with('items')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_template' => 'boolean',
        ]);

        $list = GroceryList::create($validated);

        return response()->json($list->load('items'), 201);
    }

    public function show(GroceryList $grocery): JsonResponse
    {
        return response()->json($grocery->load('items'));
    }

    public function destroy(GroceryList $grocery): JsonResponse
    {
        $grocery->delete();

        return response()->json(null, 204);
    }

    public function addItem(Request $request, GroceryList $grocery): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $item = $grocery->items()->create($validated);

        return response()->json($item, 201);
    }

    public function toggleItem(GroceryItem $item): JsonResponse
    {
        $item->update(['completed' => !$item->completed]);

        return response()->json($item);
    }

    public function removeItem(GroceryItem $item): JsonResponse
    {
        $item->delete();

        return response()->json(null, 204);
    }

    public function cloneFromTemplate(GroceryList $grocery): JsonResponse
    {
        $newList = GroceryList::create([
            'name' => $grocery->name . ' - ' . now()->format('M d'),
            'is_template' => false,
        ]);

        foreach ($grocery->items as $item) {
            $newList->items()->create([
                'name' => $item->name,
                'sort_order' => $item->sort_order,
            ]);
        }

        return response()->json($newList->load('items'), 201);
    }
}
