<?php

namespace App\Services;

use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBot
{
    public function setWebhook(string $url): void
    {
        Telegram::setWebhook(['url' => $url]);
    }

    public function deleteWebhook(): void
    {
        Telegram::deleteWebhook();
    }

    public function handleWebhook(): void
    {
        Telegram::commandsHandler(true);
    }

    public function pollOnce(): void
    {
        Telegram::commandsHandler();
    }
}
