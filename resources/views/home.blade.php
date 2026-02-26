<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CashApp — Professional expense and income tracking for companies. Categorize transactions, manage multiple accounts, and view monthly financial overviews in one place.">
    <title>CashApp — Expense Manager for Companies</title>
    @vite('resources/css/app.css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    <script>
        function applyTheme() {
            const userPref = localStorage.getItem('darkMode');
            const systemPref = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (userPref === 'true' || (userPref === null && systemPref)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
        applyTheme();
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
            if (!('darkMode' in localStorage)) applyTheme();
        });
    </script>
    <style>
        .font-dm-sans { font-family: 'DM Sans', system-ui, sans-serif; }
        .hero-gradient { background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 50%, #ecfdf5 100%); }
        .dark .hero-gradient { background: linear-gradient(135deg, #022c22 0%, #064e3b 50%, #022c22 100%); }
        .card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1); }
        .dark .card-hover:hover { box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.3), 0 8px 10px -6px rgb(0 0 0 / 0.2); }
    </style>
</head>

<body class="font-dm-sans bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-200 antialiased">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 bg-white/80 dark:bg-gray-950/80 backdrop-blur-xl border-b border-gray-200/60 dark:border-gray-800/60">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white tracking-tight">CashApp</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}" class="bg-gray-900 dark:bg-emerald-500 hover:bg-gray-800 dark:hover:bg-emerald-400 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-all shadow-lg shadow-gray-900/10 dark:shadow-emerald-500/25">
                            Get Started
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero -->
        <section class="hero-gradient border-b border-gray-200/60 dark:border-gray-800/60">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28">
                <div class="text-center max-w-2xl mx-auto">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-6 leading-[1.15] tracking-tight">
                        Know exactly what your business earns — and spends — every month.
                    </h1>
                    <p class="text-xl text-gray-600 dark:text-gray-400 mb-10 leading-relaxed">
                        Simple expense tracking for companies. Categorize income and expenses, track multiple accounts, and see your finances at a glance.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 bg-gray-900 dark:bg-emerald-500 hover:bg-gray-800 dark:hover:bg-emerald-400 text-white px-8 py-4 rounded-xl text-base font-semibold transition-all shadow-xl shadow-gray-900/20 dark:shadow-emerald-500/30 hover:shadow-2xl">
                            Get Started
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 bg-white dark:bg-gray-800/50 border-2 border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 text-gray-900 dark:text-white px-8 py-4 rounded-xl text-base font-semibold transition-all">
                            Sign In
                        </a>
                    </div>
                    <ul class="text-left max-w-md mx-auto space-y-4 text-gray-600 dark:text-gray-400">
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-bold">✓</span>
                            <span>Custom categories: Salary, Sales, Rent, Software, Travel</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-bold">✓</span>
                            <span>Multiple bank accounts and cards in one dashboard</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-bold">✓</span>
                            <span>Separate companies with no data mixing</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-bold">✓</span>
                            <span>Monthly income vs expense at a glance</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="py-20 sm:py-24">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="card-hover p-8 rounded-2xl bg-white dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 shadow-sm">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Companies</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Create and manage multiple companies. Each has its own transactions, categories, and accounts.</p>
                    </div>

                    <div class="card-hover p-8 rounded-2xl bg-white dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 shadow-sm">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Categories</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Define custom categories: Salary, Sales, Missions. Each can be income or expense.</p>
                    </div>

                    <div class="card-hover p-8 rounded-2xl bg-white dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 shadow-sm">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Transactions</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Log every transaction. Track your company's full financial flow with categorized entries.</p>
                    </div>

                    <div class="card-hover p-8 rounded-2xl bg-white dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 shadow-sm">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Multiple Accounts</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Link bank accounts and wallets. Manage all company finances from one dashboard.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why CashApp -->
        <section class="py-16 border-t border-gray-200 dark:border-gray-800">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="p-10 rounded-3xl bg-white dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 shadow-sm">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-8 text-center">Why CashApp</h2>
                    <ul class="grid sm:grid-cols-2 gap-6 max-w-2xl mx-auto text-gray-600 dark:text-gray-400">
                        <li class="flex items-center gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold">•</span>
                            Categories and accounts tailored to your needs
                        </li>
                        <li class="flex items-center gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold">•</span>
                            Instant monthly overview without complexity
                        </li>
                        <li class="flex items-center gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold">•</span>
                            Clear separation for every company you manage
                        </li>
                        <li class="flex items-center gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold">•</span>
                            Transparent pricing for core tracking
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="py-20 sm:py-24">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm font-medium text-emerald-600 dark:text-emerald-400 mb-2 uppercase tracking-widest">Trusted by businesses worldwide</p>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-12 text-center">What our users say</h2>
                <div class="grid sm:grid-cols-2 gap-6">
                    <blockquote class="card-hover p-8 rounded-2xl bg-white dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 shadow-sm">
                        <p class="text-gray-600 dark:text-gray-400 mb-6 text-lg leading-relaxed">"Reduced my monthly bookkeeping time significantly. Clear and straightforward."</p>
                        <footer class="text-sm font-semibold text-gray-900 dark:text-white">— Mark, E-commerce</footer>
                    </blockquote>
                    <blockquote class="card-hover p-8 rounded-2xl bg-white dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 shadow-sm">
                        <p class="text-gray-600 dark:text-gray-400 mb-6 text-lg leading-relaxed">"Replaced spreadsheets with a single dashboard. I now have accurate monthly numbers."</p>
                        <footer class="text-sm font-semibold text-gray-900 dark:text-white">— Priya, Freelance Agency</footer>
                    </blockquote>
                    <blockquote class="card-hover p-8 rounded-2xl bg-white dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 shadow-sm">
                        <p class="text-gray-600 dark:text-gray-400 mb-6 text-lg leading-relaxed">"Visibility into which clients actually contribute to revenue. Invaluable."</p>
                        <footer class="text-sm font-semibold text-gray-900 dark:text-white">— Lisa, Marketing Consultant</footer>
                    </blockquote>
                    <blockquote class="card-hover p-8 rounded-2xl bg-white dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 shadow-sm">
                        <p class="text-gray-600 dark:text-gray-400 mb-6 text-lg leading-relaxed">"Tracking rent, salaries, and vendor payments per project simplified our workflow."</p>
                        <footer class="text-sm font-semibold text-gray-900 dark:text-white">— Ahmed, Construction</footer>
                    </blockquote>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-20 border-t border-gray-200 dark:border-gray-800">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-gray-600 dark:text-gray-400 mb-6 text-lg">Simple. Professional. Built for clarity.</p>
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-gray-900 dark:bg-emerald-500 hover:bg-gray-800 dark:hover:bg-emerald-400 text-white px-8 py-4 rounded-xl text-base font-semibold transition-all shadow-lg">
                    Create your account
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </section>
    </div>
</body>

</html>
