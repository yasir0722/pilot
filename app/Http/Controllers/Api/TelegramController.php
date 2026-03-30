<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramCommandParser;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function handle(Request $request, TelegramCommandParser $parser, TelegramService $telegram): JsonResponse
    {
        $data = $request->all();

        Log::info('Telegram webhook received', $data);

        $message = $data['message'] ?? $data['edited_message'] ?? null;

        if (!$message || !isset($message['text'])) {
            return response()->json(['ok' => true]);
        }

        $chatId = (string) $message['chat']['id'];
        $text = $message['text'];

        $response = $parser->parse($text);
        $telegram->sendMessage($chatId, $response);

        return response()->json(['ok' => true]);
    }
}
