<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\GroceryController;
use App\Http\Controllers\Api\HabitController;
use App\Http\Controllers\Api\ReceiptController;
use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\Api\SummaryController;
use App\Http\Controllers\Api\TelegramController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

// Auth (public)
Route::post('/login', [AuthController::class, 'login']);

// Telegram Webhook (public — called by Telegram servers)
Route::post('/telegram/webhook', [TelegramController::class, 'handle']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Summary
    Route::get('/summary', SummaryController::class);

    // Expenses
    Route::apiResource('expenses', ExpenseController::class);
    Route::post('/expenses/import-csv', [ExpenseController::class, 'importCsv']);
    Route::get('/expenses-summary', [ExpenseController::class, 'summary']);

    // Receipts
    Route::post('/receipts', [ReceiptController::class, 'store']);
    Route::get('/receipts/{receipt}', [ReceiptController::class, 'show']);
    Route::delete('/receipts/{receipt}', [ReceiptController::class, 'destroy']);
    Route::patch('/receipts/{receipt}/link', [ReceiptController::class, 'linkToExpense']);

    // Groceries
    Route::get('/groceries', [GroceryController::class, 'index']);
    Route::post('/groceries', [GroceryController::class, 'store']);
    Route::get('/groceries/{grocery}', [GroceryController::class, 'show']);
    Route::delete('/groceries/{grocery}', [GroceryController::class, 'destroy']);
    Route::post('/groceries/{grocery}/items', [GroceryController::class, 'addItem']);
    Route::post('/groceries/{grocery}/clone', [GroceryController::class, 'cloneFromTemplate']);
    Route::patch('/grocery-items/{item}/toggle', [GroceryController::class, 'toggleItem']);
    Route::delete('/grocery-items/{item}', [GroceryController::class, 'removeItem']);

    // Habits
    Route::apiResource('habits', HabitController::class)->except(['show']);
    Route::post('/habits/{habit}/complete', [HabitController::class, 'complete']);
    Route::delete('/habits/{habit}/complete', [HabitController::class, 'uncomplete']);

    // Reminders
    Route::apiResource('reminders', ReminderController::class)->except(['show']);

    // Vehicles
    Route::apiResource('vehicles', VehicleController::class);
    Route::get('/vehicles-summary', [VehicleController::class, 'summary']);
    Route::get('/vehicles/{vehicle}/services', [VehicleController::class, 'services']);
    Route::post('/vehicles/{vehicle}/services', [VehicleController::class, 'storeService']);
    Route::put('/vehicle-services/{service}', [VehicleController::class, 'updateService']);
    Route::delete('/vehicle-services/{service}', [VehicleController::class, 'destroyService']);
    Route::post('/vehicle-services/{service}/receipt', [VehicleController::class, 'uploadServiceReceipt']);
    Route::get('/vehicles/{vehicle}/warranty-claims', [VehicleController::class, 'warrantyClaims']);
    Route::post('/vehicles/{vehicle}/warranty-claims', [VehicleController::class, 'storeWarrantyClaim']);
    Route::put('/vehicle-warranty-claims/{claim}', [VehicleController::class, 'updateWarrantyClaim']);
    Route::delete('/vehicle-warranty-claims/{claim}', [VehicleController::class, 'destroyWarrantyClaim']);
});
