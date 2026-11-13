<x-layouts.public
    title="Book an Appointment"
    description="Request a private appointment from current counsellor availability at BHA Help Ladder."
>
    <section class="bg-bha-50 py-16 sm:py-24">
        <div class="public-container grid items-center gap-12 lg:grid-cols-[1.1fr_0.9fr]">
            <div>
                <p class="eyebrow">Book an appointment</p>
                <h1 class="display-title mt-5">Request a private appointment online.</h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-zinc-600">Verified client accounts can choose a service, counsellor and currently available time. Appointment details remain inside the authenticated portal.</p>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary mt-8">Open your appointment portal</a>
                @else
                    <a href="{{ route('login') }}" class="btn-primary mt-8">Sign in to book</a>
                    <a href="{{ route('register') }}" class="btn-secondary mt-8 ml-2">Create client account</a>
                @endauth
            </div>
            <div class="soft-card">
                <div class="flex items-start gap-4">
                    <span class="grid size-11 shrink-0 place-items-center rounded-full bg-amber-100 text-xl text-amber-900" aria-hidden="true">!</span>
                    <div>
                        <h2 class="text-base font-bold text-ink">Not monitored for emergencies</h2>
                        <p class="mt-2 text-sm leading-7 text-zinc-600">The booking and enquiry channels are not emergency services and are not monitored continuously. If there is immediate danger, contact local emergency services or go to the nearest appropriate emergency facility.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-28">
        <div class="public-container">
            <div class="max-w-2xl">
                <p class="eyebrow">What to expect</p>
                <h2 class="section-title mt-4">A simple, privacy-aware booking process.</h2>
            </div>
            <ol class="mt-12 grid gap-5 md:grid-cols-3">
                @foreach ([
                    ['01', 'Choose a suitable service', 'Select a service, session format and counsellor from your verified client portal.'],
                    ['02', 'Choose live availability', 'Times account for working hours, breaks, blocked dates, existing sessions and buffers.'],
                    ['03', 'Review and request', 'Confirm the time without entering diagnoses, therapy notes or other sensitive clinical details.'],
                ] as [$number, $title, $copy])
                    <li class="soft-card">
                        <span class="text-xs font-black tracking-[0.2em] text-bha-500">{{ $number }}</span>
                        <h3 class="mt-8 font-display text-2xl font-semibold">{{ $title }}</h3>
                        <p class="mt-3 text-sm leading-7 text-zinc-600">{{ $copy }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="pb-20 sm:pb-28">
        <div class="public-container max-w-4xl">
            <div class="rounded-[2.5rem] bg-ink p-8 text-white sm:p-12">
                <p class="eyebrow !text-bha-300">Available now</p>
                <h2 class="mt-4 font-display text-3xl font-semibold">Secure, availability-aware appointment booking.</h2>
                <p class="mt-4 text-sm leading-7 text-zinc-300">Verified clients can review and submit a request. Reminders are discreet and detailed joining information stays inside the portal.</p>
            </div>
        </div>
    </section>
</x-layouts.public>
