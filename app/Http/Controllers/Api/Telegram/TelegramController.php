<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use App\Services\TelegramAuthService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TelegramController extends Controller
{
    public function __construct(private TelegramAuthService $telegramAuthService) {}

    public function miniapp(Request $request): View
    {
        // Try to authenticate if initData is provided
        $initData = $request->query('_auth');
        $user = null;

        if ($initData) {
            $user = $this->telegramAuthService->authenticateFromTelegram($initData);
        }

        return view('miniapp.index', [
            'user' => $user ?? $request->user(),
        ]);
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'initData' => ['required', 'string'],
        ]);

        $user = $this->telegramAuthService->authenticateFromTelegram($request->initData);

        if (! $user) {
            return response()->json(['error' => 'Authentication failed'], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
