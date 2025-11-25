<?php

namespace App\Telegram\Commands;

use Illuminate\Support\Str;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class StartCommand extends Command
{
    protected string $name = 'start';

    protected string $description = 'Display the welcome message and mini app button.';

    public function handle(): void
    {
        $this->replyWithMessage([
            'text' => 'Welcome to the Gojo Finance bot 👋',
        ]);

        $miniAppUrl = url('/miniapp/index.html');

        if (Str::startsWith(Str::lower($miniAppUrl), 'https://')) {
            $this->replyWithMessage([
                'text' => 'Tap the button below to launch the mini app.',
                'reply_markup' => Keyboard::make()
                    ->inline()
                    ->row(Keyboard::inlineButton([
                        'text' => 'Open Mini App',
                        'web_app' => [
                            'url' => $miniAppUrl,
                        ],
                    ])),
            ]);

            return;
        }

        $this->replyWithMessage([
            'text' => 'Set APP_URL to an https domain (e.g. an ngrok URL) so I can send the mini app button.',
        ]);
    }
}
