<x-layouts.public
    title="Book an Appointment"
    description="Read how appointment enquiries currently work at BHA Help Ladder. Online booking is coming in Phase 2."
>
    <section class="bg-bha-50 py-16 sm:py-24">
        <div class="public-container grid items-center gap-12 lg:grid-cols-[1.1fr_0.9fr]">
            <div>
                <p class="eyebrow">Book an appointment</p>
                <h1 class="display-title mt-5">Online scheduling is not available yet.</h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-zinc-600">The secure appointment system is planned for Phase 2. Until then, start with a brief general enquiry and the BHA Help Ladder team can respond about a suitable next step.</p>
                <a href="{{ route('contact') }}" class="btn-primary mt-8">Send a general enquiry <span class="ml-2" aria-hidden="true">→</span></a>
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
                <p class="eyebrow">What to expect now</p>
                <h2 class="section-title mt-4">A simple enquiry-first process.</h2>
            </div>
            <ol class="mt-12 grid gap-5 md:grid-cols-3">
                @foreach ([
                    ['01', 'Send the essentials', 'Provide your name, email address and a brief general reason for contacting BHA Help Ladder.'],
                    ['02', 'Protect sensitive details', 'Do not include diagnoses, detailed history, therapy notes or urgent safety information.'],
                    ['03', 'Wait for team follow-up', 'A team member can respond about current availability and the most appropriate next step.'],
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
                <p class="eyebrow !text-bha-300">Coming in Phase 2</p>
                <h2 class="mt-4 font-display text-3xl font-semibold">Secure, availability-aware appointment booking.</h2>
                <p class="mt-4 text-sm leading-7 text-zinc-300">Clients will be able to choose a service, session mode, counsellor and available time, then review and submit a booking. No working form is shown now because that system has not yet been built.</p>
            </div>
        </div>
    </section>
</x-layouts.public>
