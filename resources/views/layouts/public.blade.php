@props(['hideHeader' => false])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-neutral-100 dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
        @unless($hideHeader)
            <header class="w-full border-b border-neutral-200 dark:border-neutral-700 bg-transparent">
                <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <img src="{{ file_exists(public_path('uploads/logo.png')) ? '/uploads/logo.svg' : '/favicon.svg' }}" alt="{{ config('app.name') }}" class="w-10 h-10 rounded-lg object-contain" />
                        <span class="font-semibold">Guardians of Whisky</span>
                    </a>
                    <nav class="flex items-center gap-3 text-sm">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-md shadow-sm">Dashboard</a>
                            @else
                                <a href="{{ route('tasting.join') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-200">Join</a>
                                <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-md shadow-sm">Log in</a>
                            @endauth
                        @endif
                    </nav>
                </div>
            </header>
        @endunless

        <main class="max-w-6xl mx-auto px-6 py-12">
            {{ $slot }}
        </main>

        <footer class="max-w-6xl mx-auto px-6 py-8 text-sm text-neutral-500 dark:text-neutral-400">
            <div class="flex items-center justify-between">
                <div>&copy; {{ date('Y') }} Guardians of Whisky</div>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:underline">Privacy</a>
                    <a href="#" class="hover:underline">Support</a>
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
