<!DOCTYPE html>
<html lang="en">
    <head>
        @include('partials.head', ['title' => 'Secure account | '.config('app.name')])
    </head>
    <body class="min-h-screen bg-bha-50 antialiased">
        <div class="grid min-h-screen lg:grid-cols-[0.9fr_1.1fr]">
            <div class="hidden bg-bha-800 p-12 text-white lg:flex lg:flex-col lg:justify-between">
                <a href="{{ route('home') }}" class="inline-flex w-fit items-center gap-3">
                    <span class="grid size-11 place-items-center rounded-2xl bg-white text-xs font-black text-bha-700">BHA</span>
                    <span class="text-sm font-extrabold">BHA Help Ladder</span>
                </a>
                <div class="max-w-lg">
                    <p class="font-display text-4xl font-semibold leading-tight">“{{ config('organization.motto') }}”</p>
                    <p class="mt-5 text-sm text-bha-200">Secure access for clients, counsellors and administrators.</p>
                </div>
                <p class="text-xs text-bha-300">{{ config('organization.location') }}</p>
            </div>
            <div class="flex min-h-screen items-center justify-center p-6 sm:p-10">
                <div class="w-full max-w-md">
                    <a href="{{ route('home') }}" class="mb-8 flex items-center gap-3 lg:hidden">
                        <span class="grid size-10 place-items-center rounded-2xl bg-bha-600 text-[10px] font-black text-white">BHA</span>
                        <span class="text-sm font-extrabold">BHA Help Ladder</span>
                    </a>
                    <div class="rounded-3xl border border-bha-100 bg-white p-7 shadow-[0_24px_70px_-40px_rgba(70,35,82,0.45)] sm:p-9">
                        {{ $slot }}
                    </div>
                    <p class="mt-6 text-center text-xs text-zinc-500">This portal does not store therapy notes or clinical records.</p>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
