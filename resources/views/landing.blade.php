<x-layouts::public :title="__('landing.site_name')">
    <div class="flex items-center justify-center mb-4">
        <div class="w-36 h-40 lg:w-48 lg:h-56">
            <x-animated-logo :animation-duration="4" on-complete="logoDone" class="text-whisky-dark w-full h-full" />
        </div>
    </div>
    <div class="min-h-screen flex flex-col items-center justify-center bg-neutral-100 dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
        <main class="w-full max-w-sm md:max-w-md lg:max-w-lg px-4 py-8 flex flex-col gap-6 items-center text-center">
            <section class="space-y-4">
                <h2 class="text-2xl font-extrabold leading-tight">{{ __('landing.title') }}</h2>
                <p class="text-neutral-600 dark:text-neutral-300 max-w-64 mx-auto text-sm">{{ __('landing.subtitle') }}</p>

                <div class="flex flex-col gap-3 w-full">
                    @auth
                        <a href="{{ route('tasting.create') }}" class="inline-flex justify-center px-4 py-2 rounded-lg bg-[#F8B803] dark:bg-[#F0ACB8] text-neutral-900 font-semibold shadow">{{ __('landing.create_session') }}</a>
                    @else
                        <a href="{{ route('tasting.join') }}" class="inline-flex justify-center px-4 py-2 rounded-lg bg-white border border-neutral-200 dark:bg-neutral-800 dark:border-neutral-700 text-neutral-900 dark:text-neutral-100 font-semibold shadow">{{ __('landing.join_session') }}</a>
                        <a href="{{ route('register') }}" class="inline-flex justify-center px-4 py-2 rounded-lg bg-[#F8B803] dark:bg-[#F0ACB8] text-neutral-900 font-semibold shadow">{{ __('landing.sign_up') }}</a>
                    @endauth
                </div>

                <div class="grid grid-cols-1 gap-3 mt-4 w-full">
                    <div class="p-3 bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 shadow-sm text-left">
                        <h3 class="font-semibold text-sm">{{ __('landing.blind_title') }}</h3>
                        <p class="text-xs text-neutral-600 dark:text-neutral-300">{{ __('landing.blind_text') }}</p>
                    </div>
                    <div class="p-3 bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 shadow-sm text-left">
                        <h3 class="font-semibold text-sm">{{ __('landing.multiplayer_title') }}</h3>
                        <p class="text-xs text-neutral-600 dark:text-neutral-300">{{ __('landing.multiplayer_text') }}</p>
                    </div>
                </div>

                <div class="mt-4 text-xs text-neutral-500 dark:text-neutral-400">{{ __('landing.built_with') }}</div>
            </section>

            <div class="w-full max-w-60 p-4 bg-linear-to-br from-[#ffffff] to-[#fff2f2] dark:from-[#161615] dark:to-[#1D0002] rounded-xl shadow-sm">
                <img src="/storage/hero-drink.png" alt="Glass" class="w-full h-32 object-cover rounded-md mb-3" onerror="this.style.display='none'" />
                <div class="text-sm text-neutral-600 dark:text-neutral-300">{{ __('landing.next_tasting_label') }}</div>
                <div class="font-semibold">{{ __('landing.next_tasting_name') }}</div>
                <div class="text-xs text-neutral-500 mt-2">{{ __('landing.players_count', ['count' => 6]) }} &middot; {{ __('landing.team_plus', ['points' => 60]) }}</div>
            </div>
        </main>
    </div>
</x-layouts::public>
