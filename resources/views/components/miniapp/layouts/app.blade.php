<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#000000">
    <title>{{ $title ?? config('app.name') }} - Mini App</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    @vite('resources/css/app.css')
    @stack('styles')
    <style>
        body {
            margin: 0;
            padding: 0;
            background: var(--tg-theme-bg-color, #ffffff);
            color: var(--tg-theme-text-color, #000000);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        .container {
            padding: 16px;
            max-width: 100%;
            min-height: 100vh;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .card {
            background: var(--tg-theme-secondary-bg-color, #f0f0f0);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .button {
            background: var(--tg-theme-button-color, #3390ec);
            color: var(--tg-theme-button-text-color, #ffffff);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            margin-top: 8px;
            transition: opacity 0.2s;
        }
        .button:hover {
            opacity: 0.9;
        }
        .button:active {
            opacity: 0.8;
        }
        .info {
            font-size: 14px;
            color: var(--tg-theme-hint-color, #999999);
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        {{ $slot }}
    </div>

    <script>
        // Initialize Telegram Web App
        const tg = window.Telegram.WebApp;
        tg.ready();
        tg.expand();

        // Set theme colors based on Telegram theme
        if (tg.colorScheme === 'dark') {
            document.documentElement.classList.add('dark');
        }

        // Make Telegram Web App available globally
        window.tg = tg;
    </script>
    @stack('scripts')
</body>
</html>

