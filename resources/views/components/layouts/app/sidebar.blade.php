<aside :class="{ 'w-full md:w-64': sidebarOpen, 'w-0 md:w-16 hidden md:block': !sidebarOpen }"
    class="bg-sidebar text-sidebar-foreground border-r border-gray-200 dark:border-gray-700 sidebar-transition h-[calc(100vh-4rem)] overflow-y-auto custom-scrollbar">

    <div class="h-full flex flex-col">
        <nav class="flex-1 py-4">
            <ul class="space-y-1 px-2">

                <!-- Dashboard -->
                <x-layouts.sidebar-link href="{{ route('dashboard') }}" icon="fas-gauge" :active="request()->routeIs('dashboard')">
                    Dashboard
                </x-layouts.sidebar-link>

                <!-- Section Divider: Financial Tracking -->
                <li x-show="sidebarOpen" class="px-3 py-2 mt-4 mb-2">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Financial Tracking
                    </span>
                </li>

                <!-- Transactions -->
                @canany(['list transaction', 'view transaction', 'edit transaction', 'delete transaction'])
                    <x-layouts.sidebar-link href="{{ route('transactions.index') }}" icon="fas-receipt"
                        :active="request()->routeIs('transactions.*')">
                        Transactions
                    </x-layouts.sidebar-link>
                @endcanany

                <!-- Transaction Categories -->
                @canany([
                    'list transactioncategory',
                    'view transactioncategory',
                    'edit transactioncategory',
                    'delete transactioncategory',
                ])
                    <x-layouts.sidebar-link href="{{ route('transaction-categories.index') }}" icon="fas-tags"
                        :active="request()->routeIs('transaction-categories.*')">
                        Categories
                    </x-layouts.sidebar-link>
                @endcanany

                <!-- Section Divider: Accounts & Banks -->
                <li x-show="sidebarOpen" class="px-3 py-2 mt-4 mb-2">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Accounts & Banks
                    </span>
                </li>

                <!-- Accounts -->
                @canany(['list account', 'view account', 'edit account', 'delete account'])
                    <x-layouts.sidebar-link href="{{ route('accounts.index') }}" icon="fas-wallet" :active="request()->routeIs('accounts.*')">
                        Accounts
                    </x-layouts.sidebar-link>
                @endcanany

                <!-- Banks -->
                @canany(['list bank', 'view bank', 'edit bank', 'delete bank'])
                    <x-layouts.sidebar-link href="{{ route('banks.index') }}" icon="fas-university" :active="request()->routeIs('banks.*')">
                        Banks
                    </x-layouts.sidebar-link>
                @endcanany

                <!-- Section Divider: Company & Clients -->
                <li x-show="sidebarOpen" class="px-3 py-2 mt-4 mb-2">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Company & Clients
                    </span>
                </li>

                <!-- Companies -->
                @canany(['list company', 'view company', 'edit company', 'delete company'])
                    <x-layouts.sidebar-link href="{{ route('companies.index') }}" icon="fas-building" :active="request()->routeIs('companies.*')">
                        Companies
                    </x-layouts.sidebar-link>
                @endcanany

                <!-- Clients -->
                @canany(['list client', 'view client', 'edit client', 'delete client'])
                    <x-layouts.sidebar-link href="{{ route('clients.index') }}" icon="fas-users" :active="request()->routeIs('clients.*')">
                        Clients
                    </x-layouts.sidebar-link>
                @endcanany

                <!-- Section Divider: Settings -->
                <li x-show="sidebarOpen" class="px-3 py-2 mt-4 mb-2">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Settings
                    </span>
                </li>

                <!-- Settings -->
                <x-layouts.sidebar-link href="{{ route('settings.profile.edit') }}" icon="fas-cog"
                    :active="request()->routeIs('settings.*')">
                    Settings
                </x-layouts.sidebar-link>

                <!-- User Management -->
                <div x-data="{ open: {{ request()->routeIs('users.*') || request()->routeIs('roles.*') ? 'true' : 'false' }} }">
                    @canany([
                        'list user',
                        'view user',
                        'edit user',
                        'delete user',
                        'list role',
                        'view role',
                        'edit role',
                        'delete role',
                    ])
                        <li>
                            <button @click="open = !open"
                                class="flex items-center w-full px-3 py-2 text-sm rounded-md transition-colors duration-200 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground text-sidebar-foreground"
                                x-bind:class="sidebarOpen ? 'justify-start' : 'justify-center'">
                                @svg('fas-users-cog', 'w-5 h-5 text-gray-500')

                                <span x-show="sidebarOpen" class="ml-3 whitespace-nowrap flex-1 text-left">
                                    User Management
                                </span>

                                <span x-show="sidebarOpen">
                                    <i x-show="!open" class="fas fa-chevron-right text-xs ml-auto"></i>
                                    <i x-show="open" class="fas fa-chevron-down text-xs ml-auto"></i>
                                </span>
                            </button>
                        </li>
                    @endcanany

                    <!-- Submenu -->
                    <div x-show="open" x-transition class="ml-10 mt-1 space-y-1" x-cloak>
                        @canany(['list user', 'view user', 'edit user', 'delete user'])
                            <x-layouts.sidebar-two-level-link href="{{ route('users.index') }}" icon="fas-user"
                                :active="request()->routeIs('users.*')">
                                Users
                            </x-layouts.sidebar-two-level-link>
                        @endcanany
                        @canany(['list role', 'view role', 'edit role', 'delete role'])
                            <x-layouts.sidebar-two-level-link href="{{ route('roles.index') }}" icon="fas-id-badge"
                                :active="request()->routeIs('roles.*')">
                                Roles
                            </x-layouts.sidebar-two-level-link>
                        @endcanany
                    </div>
                </div>

            </ul>
        </nav>
    </div>
</aside>
