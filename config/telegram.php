<?php

use App\Telegram\Commands\StartCommand;
use Telegram\Bot\Commands\HelpCommand;

return [
    'bots' => [
        'default' => [
            'name' => env('TELEGRAM_BOT_USERNAME', 'GojoBot'),
            'token' => env('TELEGRAM_BOT_TOKEN', ''),
            'certificate_path' => env('TELEGRAM_CERTIFICATE_PATH'),
            'webhook_url' => env('TELEGRAM_BOT_WEBHOOK_URL'),
            'allowed_updates' => null,
            'commands' => [],
        ],
    ],

    'default' => 'default',

    'async_requests' => env('TELEGRAM_ASYNC_REQUESTS', false),

    'http_client_handler' => null,

    'base_bot_url' => null,

    'resolve_command_dependencies' => true,

    'commands' => [
        HelpCommand::class,
        StartCommand::class,
    ],

    'command_groups' => [],

    'shared_commands' => [],
];
