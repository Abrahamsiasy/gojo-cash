<?php

namespace Tests\Feature\Telegram;

use App\Services\TelegramBot;
use Mockery;
use Tests\TestCase;

class TelegramCommandsTest extends TestCase
{
    public function test_use_webhook_command_sets_webhook(): void
    {
        config(['app.url' => 'https://example.test']);

        $bot = Mockery::mock(TelegramBot::class);
        $bot->shouldReceive('setWebhook')->once()->with('https://example.test/telegram/webhook');
        $this->app->instance(TelegramBot::class, $bot);

        $this->artisan('telegram:use-webhook')
            ->expectsOutput('Webhook registered: https://example.test/telegram/webhook')
            ->assertExitCode(0);
    }

    public function test_use_polling_command_switches_to_polling(): void
    {
        $bot = Mockery::mock(TelegramBot::class);
        $bot->shouldReceive('deleteWebhook')->once();
        $bot->shouldReceive('pollOnce')->once();
        $this->app->instance(TelegramBot::class, $bot);

        $this->artisan('telegram:use-polling')
            ->expectsOutput('Polling started. Press CTRL+C to stop.')
            ->expectsOutput('Polling stopped.')
            ->assertExitCode(0);
    }
}
