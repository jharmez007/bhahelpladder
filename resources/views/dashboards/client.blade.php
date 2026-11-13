<x-layouts.app>
    <div class="mx-auto w-full max-w-6xl py-4 sm:py-8">
        <p class="eyebrow">Client dashboard</p>
        <div class="mt-3 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="font-display text-3xl font-semibold text-ink sm:text-4xl">Welcome, {{ str(auth()->user()->name)->before(' ') }}.</h1>
                <p class="mt-3 text-sm leading-6 text-zinc-600">Your private space for appointment activity.</p>
            </div>
            <a href="{{ route('client.appointments.book') }}" class="btn-primary">Request an appointment</a>
        </div>

        <section class="mt-10 soft-card">
            <div class="grid min-h-64 place-items-center text-center">
                <div class="max-w-lg">
                    <span class="mx-auto grid size-14 place-items-center rounded-full bg-bha-100 text-2xl text-bha-700" aria-hidden="true">○</span>
                    <h2 class="mt-5 font-display text-2xl font-semibold">Private appointment tools are ready</h2>
                    <p class="mt-3 text-sm leading-7 text-zinc-600">Request an available time, review your history, or manage an upcoming appointment from your secure portal.</p>
                    <a href="{{ route('client.appointments.index') }}" class="mt-5 inline-flex text-sm font-bold text-bha-700">View appointment history</a>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
