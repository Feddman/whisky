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
                    <div class="relative inline-block">
                        <button id="shareToggle" type="button" class="text-sm inline-flex items-center gap-2 px-3 py-1 rounded-md border border-zinc-200 bg-white dark:bg-zinc-900">Share</button>
                        <div id="shareMenu" class="hidden absolute right-0 mt-2 w-56 rounded-md bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 shadow-lg p-3 z-50">
                            <div class="text-sm mb-2 font-medium">{{ __('session.share') }}</div>
                            <div class="flex flex-col gap-2">
                                <button class="text-left text-sm px-2 py-1 rounded hover:bg-zinc-100 dark:hover:bg-zinc-800" onclick="navigator.clipboard.writeText('{{ $joinUrl ?? '' }}')">{{ __('session.copy') }}</button>
                                <a class="text-left text-sm px-2 py-1 rounded hover:bg-zinc-100 dark:hover:bg-zinc-800" target="_blank" rel="noopener" href="https://api.whatsapp.com/send?text={{ urlencode(__('session.join_link_message', ['url' => $joinUrl ?? ''])) }}">{{ __('session.share_whatsapp') }}</a>
                                <div class="border-t pt-2 mt-2 text-xs text-zinc-500">{{ __('session.join_code') }}: <strong>{{ $joinCode ?? '' }}</strong></div>
                            </div>
                        </div>
                    </div>
                    <script>
                        (function() {
                            var toggle = document.getElementById('shareToggle');
                            var menu = document.getElementById('shareMenu');
                            if (! toggle || ! menu) return;
                            toggle.addEventListener('click', function(e) { e.stopPropagation(); menu.classList.toggle('hidden'); });
                            menu.addEventListener('click', function(e) { e.stopPropagation(); });
                            document.addEventListener('click', function() { menu.classList.add('hidden'); });
                            document.addEventListener('keydown', function(e) { if (e.key === 'Escape') menu.classList.add('hidden'); });
                        })();
                    </script>
                    @guest
                        <a href="#" class="text-flux-primary hover:underline" onclick="if (confirm({{ json_encode(__('session.leave_guest_warning')) }})) { window.location = '{{ route('tasting.leave', $tastingSessionId) }}'; } return false;">{{ __('session.leave_session') }}</a>
                    @else
                        <a href="#" class="text-flux-primary hover:underline" onclick="if (confirm({{ json_encode(__('session.leave_confirm')) }})) { window.location = '{{ route('tasting.leave', $tastingSessionId) }}'; } return false;">{{ __('session.leave_session') }}</a>
                    @endguest
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
