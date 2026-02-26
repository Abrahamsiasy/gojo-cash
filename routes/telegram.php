<?php

use App\Http\Controllers\Api\Telegram\TelegramController;
use App\Services\TelegramBot;
use Illuminate\Support\Facades\Route;

// Telegram Webhook
Route::post('telegram/webhook', function (TelegramBot $bot) {
    $bot->handleWebhook();

    return response()->json(['ok' => true]);
})->name('telegram.webhook');

// Telegram Mini App
Route::get('miniapp', [TelegramController::class, 'miniapp'])->name('miniapp.index');
Route::get('miniapp/index.html', [TelegramController::class, 'miniapp']);
Route::post('telegram/authenticate', [TelegramController::class, 'authenticate'])->name('telegram.authenticate');
