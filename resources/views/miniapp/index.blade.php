<x-miniapp.layouts.app title="Home">
    <x-miniapp.header header-subtitle="Telegram Mini App" />

    <x-miniapp.card title="Get Started">
        <p>This is your Telegram mini app. You can build your finance management interface here.</p>
        <x-miniapp.button onclick="handleClick()">Open Dashboard</x-miniapp.button>
    </x-miniapp.card>

    <x-miniapp.card title="User Info">
        <div id="user-info">
            <p class="info">Loading user information...</p>

        </div>
    </x-miniapp.card>

    @push('scripts')
    <script>
        // Get initData from Telegram Web App
        const initData = window.tg.initData;
        const userInfoDiv = document.getElementById('user-info');
        
        // Display Telegram user info (unverified, client-side)
        const telegramUser = window.tg.initDataUnsafe?.user;
        
        if (telegramUser) {
            userInfoDiv.innerHTML = `
                <p><strong>Telegram Name:</strong> ${telegramUser.first_name} ${telegramUser.last_name || ''}</p>
                <p><strong>Telegram Username:</strong> @${telegramUser.username || 'N/A'}</p>
                <p><strong>Telegram User ID:</strong> ${telegramUser.id}</p>
                <p class="info" style="margin-top: 12px;">
                    @if(auth()->check())
                        <strong>Laravel User:</strong> {{ auth()->user()->name }} ({{ auth()->user()->email }})
                    @else
                        <em>Not authenticated with Laravel. Send initData to authenticate.</em>
                    @endif
                </p>
            `;
            
            // Authenticate with Laravel if initData is available
            if (initData) {
                fetch('{{ route("telegram.authenticate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ initData: initData })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload page to show authenticated user
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Authentication error:', error);
                });
            }
        } else {
            userInfoDiv.innerHTML = '<p class="info">User information not available</p>';
        }

        function handleClick() {
            if (window.tg) {
                window.tg.showAlert('Dashboard feature coming soon!');
            }
        }
    </script>
    @endpush
</x-miniapp.layouts.app>

