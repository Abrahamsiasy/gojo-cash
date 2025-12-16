<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TelegramAuthService
{
    /**
     * Verify Telegram initData and authenticate user
     */
    public function authenticateFromTelegram(string $initData): ?User
    {
        // Verify initData (you should implement proper verification using bot token)
        $telegramUser = $this->parseInitData($initData);

        if (! $telegramUser) {
            return null;
        }

        // Find or create user by telegram_user_id
        $user = User::where('telegram_user_id', $telegramUser['id'])->first();

        if (! $user) {
            // Create new user or link existing user
            // For now, we'll create a new user
            $user = User::create([
                'name' => trim(($telegramUser['first_name'] ?? '').' '.($telegramUser['last_name'] ?? '')),
                'email' => $this->generateEmailFromTelegram($telegramUser),
                'password' => Hash::make(Str::random(32)), // Random password since Telegram auth doesn't use passwords
                'telegram_user_id' => $telegramUser['id'],
            ]);
        }

        // Log the user in
        Auth::login($user);

        return $user;
    }

    /**
     * Parse initData string to extract user information
     * Note: This is a simplified version. In production, you should verify the signature
     */
    protected function parseInitData(string $initData): ?array
    {
        parse_str($initData, $params);

        if (! isset($params['user'])) {
            return null;
        }

        $userData = json_decode($params['user'], true);

        if (! $userData || ! isset($userData['id'])) {
            return null;
        }

        return $userData;
    }

    /**
     * Generate a unique email from Telegram user data
     */
    protected function generateEmailFromTelegram(array $telegramUser): string
    {
        $username = $telegramUser['username'] ?? 'user';
        $id = $telegramUser['id'];

        return "telegram_{$username}_{$id}@telegram.local";
    }

    /**
     * Verify initData signature (proper implementation)
     * This should verify the data using your bot token
     */
    public function verifyInitData(string $initData, string $botToken): bool
    {
        // TODO: Implement proper signature verification
        // See: https://core.telegram.org/bots/webapps#validating-data-received-via-the-mini-app
        return true; // Placeholder
    }
}
