<?php

namespace App\Console\Commands;

use App\Services\TelegramBot;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TelegramUseWebhookCommand extends Command
{
    protected $signature = 'telegram:use-webhook {--url=}';

    protected $description = 'Configure Telegram to deliver updates via webhook.';

    public function __construct(protected TelegramBot $bot)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $url = $this->option('url') ?: $this->defaultWebhookUrl();

        if (blank($url)) {
            $this->error('Provide an https URL via --url or TELEGRAM_BOT_WEBHOOK_URL.');

            return self::FAILURE;
        }

        if (! Str::startsWith(Str::lower($url), 'https://')) {
            $this->error('Telegram requires HTTPS webhook URLs.');

            return self::FAILURE;
        }

        $this->bot->setWebhook($url);

        $this->writeEnvironmentValues([
            'TELEGRAM_BOT_WEBHOOK_URL' => $url,
            'TELEGRAM_USE_WEBHOOK' => 'true',
        ]);

        $this->info(sprintf('Webhook registered: %s', $url));

        return self::SUCCESS;
    }

    protected function defaultWebhookUrl(): ?string
    {
        $envUrl = env('TELEGRAM_BOT_WEBHOOK_URL');

        if (! blank($envUrl)) {
            return $envUrl;
        }

        $appUrl = config('app.url');

        return blank($appUrl) ? null : rtrim($appUrl, '/').'/telegram/webhook';
    }

    protected function writeEnvironmentValues(array $replacements): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $path = base_path('.env');

        if (! is_file($path)) {
            return;
        }

        $contents = file_get_contents($path);

        foreach ($replacements as $key => $value) {
            $pattern = sprintf('/^%s=.*$/m', preg_quote($key, '/'));
            $line = sprintf('%s=%s', $key, $value);
            if (preg_match($pattern, $contents)) {
                $contents = preg_replace($pattern, $line, $contents);
            } else {
                $contents .= PHP_EOL.$line;
            }
        }

        file_put_contents($path, $contents);
    }
}
