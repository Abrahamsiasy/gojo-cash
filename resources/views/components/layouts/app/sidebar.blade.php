<aside :class="{ 'w-full md:w-64': sidebarOpen, 'w-0 md:w-16 hidden md:block': !sidebarOpen }"
    class="bg-sidebar text-sidebar-foreground border-r border-gray-200 dark:border-gray-700 sidebar-transition overflow-hidden">

    <div class="h-full flex flex-col">
        <nav class="flex-1 overflow-y-auto custom-scrollbar py-4">
            <ul class="space-y-1 px-2">

                <!-- Dashboard -->
                <x-layouts.sidebar-link href="{{ route('dashboard') }}" icon="fas-gauge" :active="request()->routeIs('dashboard')">
                    Dashboard
                </x-layouts.sidebar-link>

                <!-- Companies -->
                <x-layouts.sidebar-link href="{{ route('companies.index') }}" icon="fas-building" :active="request()->routeIs('companies.*')">
                    Companies
                </x-layouts.sidebar-link>

                <!-- Banks -->
                <x-layouts.sidebar-link href="{{ route('banks.index') }}" icon="fas-house" :active="request()->routeIs('banks.*')">
                    Banks
                </x-layouts.sidebar-link>

                <!-- Accounts -->
                <x-layouts.sidebar-link href="{{ route('accounts.index') }}" icon="fas-wallet" :active="request()->routeIs('accounts.*')">
                    Accounts
                </x-layouts.sidebar-link>

                <!-- Transaction Categories -->
                <x-layouts.sidebar-link href="{{ route('transaction-categories.index') }}" icon="fas-tags"
                    :active="request()->routeIs('transaction-categories.*')">
                    Transaction Categories
                </x-layouts.sidebar-link>

                <!-- Transactions -->
                <x-layouts.sidebar-link href="{{ route('transactions.index') }}" icon="fas-arrows-rotate"
                    :active="request()->routeIs('transactions.*')">
                    Transactions
                </x-layouts.sidebar-link>

            </ul>
        </nav>
    </div>
</aside>
