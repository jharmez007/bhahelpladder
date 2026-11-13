@props([
    'title' => null,
    'description' => 'Counselling and mental health advocacy support from BHA Help Ladder.',
])
<!DOCTYPE html>
<html lang="en">
    <head>
        @include('partials.head', [
            'title' => $title ? $title.' | '.config('app.name') : config('app.name'),
            'description' => $description,
        ])
    </head>
    <body class="min-h-screen bg-white antialiased">
        <a href="#main-content" class="sr-only z-50 rounded bg-white px-4 py-3 text-bha-800 focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to main content</a>

        <header class="sticky top-0 z-40 border-b border-bha-100/80 bg-white/95 backdrop-blur">
            <div class="public-container flex min-h-20 items-center justify-between gap-6">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3" aria-label="BHA Help Ladder home">
                    @if (config('organization.logo_path'))
                        <img src="{{ asset(config('organization.logo_path')) }}" alt="BHA Help Ladder" class="h-12 w-auto max-w-48 object-contain">
                    @else
                        <span class="grid size-10 place-items-center rounded-2xl bg-bha-600 text-sm font-black text-white" aria-hidden="true">BHA</span>
                        <span class="text-sm font-extrabold tracking-tight text-ink">BHA Help Ladder</span>
                    @endif
                </a>

                <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary navigation">
                    @foreach ([
                        'home' => 'Home',
                        'about' => 'About',
                        'services' => 'Services',
                        'programs' => 'Programs',
                        'contact' => 'Contact',
                    ] as $routeName => $label)
                        <a href="{{ route($routeName) }}" @class([
                            'rounded-full px-4 py-2 text-sm font-semibold transition',
                            'bg-bha-50 text-bha-800' => request()->routeIs($routeName),
                            'text-zinc-600 hover:bg-bha-50 hover:text-bha-800' => ! request()->routeIs($routeName),
                        ]) @if(request()->routeIs($routeName)) aria-current="page" @endif>{{ $label }}</a>
                    @endforeach
                </nav>

                <div class="hidden items-center gap-3 lg:flex">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-sm font-bold text-bha-800">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-bold text-bha-800">Sign in</a>
                    @endauth
                    <a href="{{ route('booking') }}" class="btn-primary !min-h-10 !px-5 !py-2">Book an Appointment</a>
                </div>

                <details class="relative lg:hidden">
                    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full border border-bha-200 px-4 py-2 text-sm font-bold text-bha-800">Menu</summary>
                    <nav class="absolute right-0 mt-3 w-64 rounded-2xl border border-bha-100 bg-white p-3 shadow-xl" aria-label="Mobile navigation">
                        @foreach ([
                            'home' => 'Home', 'about' => 'About', 'services' => 'Services',
                            'programs' => 'Programs', 'contact' => 'Contact', 'booking' => 'Book an Appointment',
                        ] as $routeName => $label)
                            <a href="{{ route($routeName) }}" class="block rounded-xl px-4 py-3 text-sm font-semibold text-zinc-700 hover:bg-bha-50">{{ $label }}</a>
                        @endforeach
                        <div class="my-2 border-t border-bha-100"></div>
                        <a href="{{ route(auth()->check() ? 'dashboard' : 'login') }}" class="block rounded-xl px-4 py-3 text-sm font-bold text-bha-800">{{ auth()->check() ? 'Dashboard' : 'Sign in' }}</a>
                    </nav>
                </details>
            </div>
        </header>

        <main id="main-content">
            {{ $slot }}
        </main>

        <footer class="border-t border-bha-100 bg-ink text-white">
            <div class="public-container grid gap-10 py-14 md:grid-cols-[1.2fr_0.8fr_0.8fr]">
                <div>
                    <p class="font-display text-2xl font-semibold">BHA Help Ladder</p>
                    <p class="mt-3 max-w-md text-sm leading-7 text-zinc-300">{{ config('organization.motto') }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-bha-300">Explore</p>
                    <div class="mt-4 grid gap-3 text-sm text-zinc-300">
                        <a class="hover:text-white" href="{{ route('about') }}">About us</a>
                        <a class="hover:text-white" href="{{ route('services') }}">Our services</a>
                        <a class="hover:text-white" href="{{ route('programs') }}">Programs</a>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-bha-300">Get support</p>
                    <div class="mt-4 grid gap-3 text-sm text-zinc-300">
                        <a class="hover:text-white" href="{{ route('booking') }}">Booking information</a>
                        <a class="hover:text-white" href="{{ route('contact') }}">Send an enquiry</a>
                        <a class="hover:text-white" href="{{ route('login') }}">Client sign in</a>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/10">
                <div class="public-container flex flex-col gap-2 py-5 text-xs text-zinc-400 sm:flex-row sm:items-center sm:justify-between">
                    <p>&copy; {{ now()->year }} BHA Help Ladder.</p>
                    <p>This website handles general enquiries and appointments, not clinical records.</p>
                </div>
            </div>
        </footer>
        @fluxScripts
    </body>
</html>
