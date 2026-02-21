<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <header class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
            <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                {{-- <x-app-logo-icon class="w-20 text-black dark:text-white" /> --}}
                @if (isset($sessionName))
                    <span class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">{{ $sessionName }}</span>
                @endif
            </a>
            <div class="flex items-center gap-2">
                @auth
                    <flux:link href="{{ route('dashboard') }}" wire:navigate>{{ __('Dashboard') }}</flux:link>
                @endif
                @if (isset($tastingSessionId))
                    <a href="{{ route('tasting.leave', $tastingSessionId) }}" class="text-flux-primary hover:underline">{{ __('Leave session') }}</a>
                @endif
            </div>
        </header>

        <main class="p-4 md:p-6">
            {{ $slot }}
        </main>

        @if (isset($tastingSessionId) && config('broadcasting.default') === 'pusher' && config('broadcasting.connections.pusher.key'))
            @php
                $pusherConfig = [
                    'key' => config('broadcasting.connections.pusher.key'),
                    'cluster' => config('broadcasting.connections.pusher.options.cluster') ?? 'mt1',
                    'sessionId' => $tastingSessionId,
                ];
            @endphp
            <script>
                window.pusherTastingConfig = @json($pusherConfig);
            </script>
            <script src="https://js.pusher.com/8.3.0/pusher.min.js" crossorigin="anonymous"></script>
            <script>
                (function() {
                    var c = window.pusherTastingConfig;
                    if (!c || !c.key || !c.sessionId) return;
                    var pusher = new Pusher(c.key, { cluster: c.cluster });
                    var ch = pusher.subscribe('tasting.session.' + c.sessionId);
                    ch.bind('player.joined', function() { window.dispatchEvent(new CustomEvent('tasting-players-updated')); });
                    ch.bind('round.started', function() { window.dispatchEvent(new CustomEvent('round-started')); });
                    ch.bind('submission.received', function() { window.dispatchEvent(new CustomEvent('submission-received')); });
                    ch.bind('reveal.started', function() { window.dispatchEvent(new CustomEvent('reveal-started')); });
                    ch.bind('reveal.countdown_started', function() { window.dispatchEvent(new CustomEvent('reveal-countdown-started')); });
                    ch.bind('everyone.submitted', function() { window.dispatchEvent(new CustomEvent('everyone-submitted')); });
                    ch.bind('player.left', function() { window.dispatchEvent(new CustomEvent('tasting-players-updated')); });
                    ch.bind('slainte.success', function() { window.dispatchEvent(new CustomEvent('slainte-success')); });
                    ch.bind('slainte.pressed', function(data) { window.dispatchEvent(new CustomEvent('slainte-pressed', { detail: data })); });
                    ch.bind('emoji.reaction', function(data) { window.dispatchEvent(new CustomEvent('emoji-reaction', { detail: data })); });
                    ch.bind('participant.updated', function(data) { window.dispatchEvent(new CustomEvent('participant-updated', { detail: data })); });
                })();
            </script>
        @endif

        @if (isset($tastingSessionId))
            <style>
                @keyframes wiggle {
                    0%, 100% { transform: rotate(-4deg); }
                    50% { transform: rotate(4deg); }
                }
                .animate-wiggle { animation: wiggle 0.35s ease-in-out infinite; }
            </style>
            <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js" crossorigin="anonymous"></script>
        @endif

        @fluxScripts
    </body>
</html>
