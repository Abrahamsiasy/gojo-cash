<?php

use App\Services\TelegramBot;
use Illuminate\Support\Facades\Route;

Route::post('telegram/webhook', function (TelegramBot $bot) {
    $bot->handleWebhook();

    return response()->json(['ok' => true]);
})->name('telegram.webhook');
