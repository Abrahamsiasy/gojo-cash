<?php

namespace App\Console\Commands;

use App\Services\TelegramBot;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;

class TelegramUsePollingCommand extends Command implements SignalableCommandInterface
{
    protected $signature = 'telegram:use-polling {--sleep=1 : Seconds to wait between polling requests}';

    protected $description = 'Switch Telegram delivery to getUpdates polling.';

    protected bool $running = true;

    public function __construct(protected TelegramBot $bot)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->bot->deleteWebhook();
        $this->writeEnvironmentValues(['TELEGRAM_USE_WEBHOOK' => 'false']);

        $sleepSeconds = max(1, (int) $this->option('sleep'));

        $this->info('Polling started. Press CTRL+C to stop.');

        if (app()->runningUnitTests()) {
            $this->bot->pollOnce();
            $this->comment('Polling stopped.');

            return self::SUCCESS;
        }

        while ($this->running) {
            $this->bot->pollOnce();
            sleep($sleepSeconds);
        }

        $this->comment('Polling stopped.');

        return self::SUCCESS;
    }

    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
    {
        $this->running = false;

        return $previousExitCode ?: self::SUCCESS;
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
