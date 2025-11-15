<?php

namespace Tests\Feature\Telegram;

use App\Services\TelegramBot;
use Mockery;
use Tests\TestCase;

class TelegramWebhookControllerTest extends TestCase
{
    public function test_webhook_endpoint_triggers_command_handler(): void
    {
        $bot = Mockery::mock(TelegramBot::class);
        $bot->shouldReceive('handleWebhook')->once();
        $this->app->instance(TelegramBot::class, $bot);

        $response = $this->postJson('/telegram/webhook', ['dummy' => 'payload']);

        $response->assertOk()->assertJson(['ok' => true]);
    }
}
