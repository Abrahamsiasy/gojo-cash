<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class EnvEditor
{
    /**
     * Ensure .env file exists, copying from .env.bak if needed.
     * Preserves the original format from .env.bak.
     */
    public static function ensureExists(): bool
    {
        $envPath = base_path('.env');
        $envBakPath = base_path('.env.bak');

        // If .env exists, just ensure required values are set (preserve format)
        if (File::exists($envPath)) {
            $content = File::get($envPath);
            $modified = false;

            // Ensure SESSION_DRIVER is set to file during installation
            if (! preg_match('/^SESSION_DRIVER=/m', $content)) {
                // Add after SESSION section if it exists, otherwise at end
                if (preg_match('/^# =+.*Session.*=+/m', $content)) {
                    $content = preg_replace('/(SESSION_DOMAIN=.*)/m', "$1\nSESSION_DRIVER=file", $content);
                } else {
                    $content .= "\nSESSION_DRIVER=file\n";
                }
                $modified = true;
            } elseif (preg_match('/^SESSION_DRIVER=database/m', $content)) {
                $content = preg_replace('/^SESSION_DRIVER=database/m', 'SESSION_DRIVER=file', $content);
                $modified = true;
            }

            // Ensure APP_ENV is set to production
            if (preg_match('/^APP_ENV=(local|development|dev)/m', $content)) {
                $content = preg_replace('/^APP_ENV=(local|development|dev)/m', 'APP_ENV=production', $content);
                $modified = true;
            } elseif (! preg_match('/^APP_ENV=/m', $content)) {
                $content = preg_replace('/(APP_NAME=.*)/m', "$1\nAPP_ENV=production", $content);
                $modified = true;
            }

            // Ensure APP_DEBUG is set to false for production
            if (preg_match('/^APP_DEBUG=true/m', $content)) {
                $content = preg_replace('/^APP_DEBUG=true/m', 'APP_DEBUG=false', $content);
                $modified = true;
            } elseif (! preg_match('/^APP_DEBUG=/m', $content)) {
                $content = preg_replace('/(APP_ENV=.*)/m', "$1\nAPP_DEBUG=false", $content);
                $modified = true;
            }

            if ($modified) {
                File::put($envPath, $content);
            }

            return true;
        }

        // If .env doesn't exist, copy from .env.bak and preserve format
        if (File::exists($envBakPath)) {
            // Copy the entire file to preserve format
            $content = File::get($envBakPath);

            // Only modify values that need to be changed, preserving all formatting
            // Ensure SESSION_DRIVER is set to file
            if (preg_match('/^SESSION_DRIVER=database/m', $content)) {
                $content = preg_replace('/^SESSION_DRIVER=database/m', 'SESSION_DRIVER=file', $content);
            } elseif (! preg_match('/^SESSION_DRIVER=/m', $content)) {
                // Add SESSION_DRIVER in the Session Configuration section
                if (preg_match('/(SESSION_DOMAIN=.*)/m', $content, $matches)) {
                    $content = preg_replace('/(SESSION_DOMAIN=.*)/m', "$1\nSESSION_DRIVER=file", $content);
                } else {
                    $content .= "\nSESSION_DRIVER=file\n";
                }
            }

            // Ensure APP_ENV is set to production (fix typo if exists)
            if (preg_match('/^APP_ENV=(local|development|dev|prodcution)/m', $content)) {
                $content = preg_replace('/^APP_ENV=(local|development|dev|prodcution)/m', 'APP_ENV=production', $content);
            } elseif (! preg_match('/^APP_ENV=/m', $content)) {
                $content = preg_replace('/(APP_NAME=.*)/m', "$1\nAPP_ENV=production", $content);
            }

            // Ensure APP_DEBUG is set to false for production
            if (preg_match('/^APP_DEBUG=(true|1)/m', $content)) {
                $content = preg_replace('/^APP_DEBUG=(true|1)/m', 'APP_DEBUG=false', $content);
            } elseif (! preg_match('/^APP_DEBUG=/m', $content)) {
                $content = preg_replace('/(APP_ENV=.*)/m', "$1\nAPP_DEBUG=false", $content);
            }

            // Write the formatted content to .env
            File::put($envPath, $content);

            return true;
        }

        return false;
    }

    /**
     * Read environment variable from a file.
     */
    public static function read(string $key, ?string $filePath = null): ?string
    {
        $filePath = $filePath ?? base_path('.env.bak');

        if (! File::exists($filePath)) {
            return null;
        }

        $content = File::get($filePath);
        $pattern = "/^{$key}=(.*)$/m";

        if (preg_match($pattern, $content, $matches)) {
            return trim($matches[1] ?? '');
        }

        return null;
    }

    /**
     * Read multiple environment variables from a file.
     */
    public static function readMultiple(array $keys, ?string $filePath = null): array
    {
        $filePath = $filePath ?? base_path('.env.bak');
        $values = [];

        if (! File::exists($filePath)) {
            return $values;
        }

        $content = File::get($filePath);

        foreach ($keys as $key) {
            $pattern = "/^{$key}=(.*)$/m";
            if (preg_match($pattern, $content, $matches)) {
                $values[$key] = trim($matches[1] ?? '');
            } else {
                $values[$key] = null;
            }
        }

        return $values;
    }

    /**
     * Update environment variables in .env file.
     */
    public static function update(array $data): void
    {
        self::ensureExists();

        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            return;
        }

        $content = File::get($envPath);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content .= "\n{$replacement}";
            }
        }

        File::put($envPath, $content);
    }
}
