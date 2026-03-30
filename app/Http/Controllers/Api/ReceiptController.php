<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReceiptController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|max:10240',
            'expense_id' => 'nullable|exists:expenses,id',
        ]);

        $file = $request->file('file');
        $path = $file->store('receipts', 'public');

        $receipt = Receipt::create([
            'expense_id' => $request->expense_id,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return response()->json($receipt, 201);
    }

    public function show(Receipt $receipt): JsonResponse
    {
        return response()->json($receipt);
    }

    public function destroy(Receipt $receipt): JsonResponse
    {
        Storage::disk('public')->delete($receipt->file_path);
        $receipt->delete();

        return response()->json(null, 204);
    }

    public function linkToExpense(Request $request, Receipt $receipt): JsonResponse
    {
        $request->validate([
            'expense_id' => 'required|exists:expenses,id',
        ]);

        $receipt->update(['expense_id' => $request->expense_id]);

        return response()->json($receipt);
    }
}
