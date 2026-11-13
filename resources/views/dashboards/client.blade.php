<x-layouts.app>
    <div class="mx-auto w-full max-w-6xl py-4 sm:py-8">
        <p class="eyebrow">Client dashboard</p>
        <div class="mt-3 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="font-display text-3xl font-semibold text-ink sm:text-4xl">Welcome, {{ str(auth()->user()->name)->before(' ') }}.</h1>
                <p class="mt-3 text-sm leading-6 text-zinc-600">Your private space for appointment activity.</p>
            </div>
            <a href="{{ route('booking') }}" class="btn-primary">Appointment information</a>
        </div>

        <section class="mt-10 soft-card">
            <div class="grid min-h-64 place-items-center text-center">
                <div class="max-w-lg">
                    <span class="mx-auto grid size-14 place-items-center rounded-full bg-bha-100 text-2xl text-bha-700" aria-hidden="true">○</span>
                    <h2 class="mt-5 font-display text-2xl font-semibold">No appointments to show yet</h2>
                    <p class="mt-3 text-sm leading-7 text-zinc-600">Online booking and appointment history are planned for Phase 2. For now, use the public enquiry route to contact the team.</p>
                    <a href="{{ route('contact') }}" class="mt-5 inline-flex text-sm font-bold text-bha-700">Send a general enquiry <span class="ml-2" aria-hidden="true">→</span></a>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
