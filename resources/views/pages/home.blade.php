<x-layouts.public
    title="Counselling & Mental Health Advocacy"
    description="BHA Help Ladder offers counselling, school support, educator seminars and community outreach."
>
    @include('pages.partials.home-hero')

    {{-- Retained temporarily as a reference while the final carousel photography is supplied.
    <section class="relative overflow-hidden bg-bha-50">
        <div class="absolute -right-32 -top-32 size-[32rem] rounded-full border-[5rem] border-bha-100/70" aria-hidden="true"></div>
        <div class="absolute bottom-10 right-[34%] size-24 rounded-full bg-bha-200/50 blur-2xl" aria-hidden="true"></div>
        <div class="public-container relative grid min-h-[calc(100vh-5rem)] items-center gap-12 py-16 lg:grid-cols-[1.08fr_0.92fr] lg:py-24">
            <div class="max-w-3xl">
                <p class="eyebrow">Counselling &amp; mental health advocacy</p>
                <h1 class="display-title mt-5">A steady hand when life feels difficult to navigate.</h1>
                <p class="mt-7 max-w-2xl text-lg leading-8 text-zinc-600 sm:text-xl">BHA Help Ladder creates space for people, educators and communities to seek support, build awareness and take a considered next step.</p>
                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('booking') }}" class="btn-primary">Book an Appointment <span aria-hidden="true" class="ml-2">→</span></a>
                    <a href="{{ route('services') }}" class="btn-secondary">Explore Our Services</a>
                </div>
                <p class="mt-7 flex items-start gap-3 text-sm leading-6 text-zinc-500">
                    <span class="mt-2 size-2 shrink-0 rounded-full bg-bha-500" aria-hidden="true"></span>
                    Support begins with a conversation. No promise of a particular outcome—just a respectful route to the right next step.
                </p>
            </div>

            <div class="relative mx-auto w-full max-w-lg" aria-hidden="true">
                <div class="aspect-[4/5] overflow-hidden rounded-[3rem] bg-bha-700 p-7 shadow-[0_40px_100px_-35px_rgba(70,35,82,0.6)] sm:p-10">
                    <div class="flex h-full flex-col justify-between rounded-[2.25rem] border border-white/20 bg-gradient-to-br from-white/15 to-white/5 p-7 backdrop-blur sm:p-10">
                        <div class="flex items-center justify-between">
                            <span class="grid size-12 place-items-center rounded-2xl bg-white text-xs font-black text-bha-700">BHA</span>
                            <span class="rounded-full border border-white/25 px-4 py-2 text-xs font-bold text-white/90">Help Ladder</span>
                        </div>
                        <div>
                            <div class="mb-8 flex items-end gap-3">
                                <span class="h-16 w-5 rounded-full bg-white/25"></span>
                                <span class="h-24 w-5 rounded-full bg-white/45"></span>
                                <span class="h-32 w-5 rounded-full bg-white/70"></span>
                                <span class="h-40 w-5 rounded-full bg-white"></span>
                            </div>
                            <p class="font-display text-3xl leading-tight font-semibold text-white">“{{ config('organization.motto') }}”</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    --}}

    <section class="py-20 sm:py-28" id="services">
        <div class="public-container">
            <div class="max-w-2xl">
                <p class="eyebrow">Ways we can help</p>
                <h2 class="section-title mt-4">Support shaped around people and the places they learn and live.</h2>
                <p class="mt-5 text-base leading-7 text-zinc-600">Explore the services available and choose a suitable place to begin. A member of the team will confirm what support is appropriate for your needs.</p>
            </div>

            <div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['01', 'Individual counselling', 'Appointment-based support for adults seeking a private, respectful conversation.'],
                    ['02', 'School counselling', 'Support designed for students, teachers and school administrators.'],
                    ['03', 'Educator awareness', 'Mental health awareness seminars developed for educators and school communities.'],
                    ['04', 'Community outreach', 'Mental health advocacy and engagement with community and faith-based groups.'],
                    ['05', 'Child protection advocacy', 'Advocacy approached through responsible adults and safeguarding-led pathways.'],
                ] as [$number, $service, $summary])
                    <article class="soft-card group transition hover:-translate-y-1 hover:border-bha-200">
                        <span class="text-xs font-black tracking-[0.18em] text-bha-500">{{ $number }}</span>
                        <h3 class="mt-8 font-display text-2xl font-semibold text-ink">{{ $service }}</h3>
                        <p class="mt-3 text-sm leading-7 text-zinc-600">{{ $summary }}</p>
                    </article>
                @endforeach
                <a href="{{ route('services') }}" class="group flex min-h-64 flex-col justify-between rounded-3xl bg-ink p-8 text-white transition hover:-translate-y-1 hover:bg-bha-900">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-bha-300">All services</span>
                    <span class="font-display text-2xl font-semibold">Find the most relevant starting point <span class="ml-2 transition group-hover:ml-4" aria-hidden="true">→</span></span>
                </a>
            </div>
        </div>
    </section>

    <section class="bg-ink py-20 text-white sm:py-28">
        <div class="public-container">
            <div class="grid gap-12 lg:grid-cols-[0.8fr_1.2fr]">
                <div>
                    <p class="eyebrow !text-bha-300">How appointments work</p>
                    <h2 class="mt-4 font-display text-3xl font-semibold leading-tight sm:text-4xl">A clear path from enquiry to support.</h2>
                    <p class="mt-5 text-sm leading-7 text-zinc-300">Verified client accounts can request a time from live counsellor availability. General questions can still begin with a privacy-conscious enquiry.</p>
                </div>
                <ol class="grid gap-4 sm:grid-cols-3">
                    @foreach ([
                        ['1', 'Choose a service', 'Select the type of support that best matches where you would like to begin.'],
                        ['2', 'Pick an available time', 'View live counsellor availability and choose a suitable appointment.'],
                        ['3', 'Confirm your booking', 'Review the details and receive a discreet appointment confirmation.'],
                    ] as [$step, $title, $copy])
                        <li class="rounded-3xl border border-white/10 bg-white/[0.06] p-6">
                            <span class="grid size-9 place-items-center rounded-full bg-bha-500 text-sm font-black">{{ $step }}</span>
                            <h3 class="mt-8 text-base font-bold">{{ $title }}</h3>
                            <p class="mt-3 text-sm leading-6 text-zinc-300">{{ $copy }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-28">
        <div class="public-container grid items-center gap-12 lg:grid-cols-2">
            <div class="rounded-[2.5rem] bg-bha-50 p-8 sm:p-12">
                <p class="eyebrow">Schools &amp; communities</p>
                <h2 class="section-title mt-4">Wellbeing conversations belong beyond the consulting room.</h2>
                <p class="mt-5 text-base leading-7 text-zinc-600">BHA Help Ladder works across educational, community and faith-based settings to encourage awareness, support and responsible pathways for seeking help.</p>
                <a href="{{ route('programs') }}" class="btn-secondary mt-8">Explore school &amp; community programs</a>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="soft-card sm:translate-y-8">
                    <span class="text-3xl" aria-hidden="true">◎</span>
                    <h3 class="mt-10 font-display text-2xl font-semibold">For school communities</h3>
                    <p class="mt-3 text-sm leading-7 text-zinc-600">Support for students, educators and administrators, with adult-led enquiries for child-related needs.</p>
                </div>
                <div class="soft-card">
                    <span class="text-3xl" aria-hidden="true">◇</span>
                    <h3 class="mt-10 font-display text-2xl font-semibold">For community groups</h3>
                    <p class="mt-3 text-sm leading-7 text-zinc-600">Awareness and outreach for community and faith-based organisations.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-bha-50 py-20 sm:py-28">
        <div class="public-container grid items-center gap-10 lg:grid-cols-[0.75fr_1.25fr]">
            <div class="relative mx-auto grid aspect-square w-full max-w-sm place-items-center overflow-hidden rounded-full bg-bha-200">
                <div class="absolute inset-10 rounded-full border border-bha-500/30"></div>
                <div class="grid size-32 place-items-center rounded-full bg-bha-700 font-display text-5xl font-semibold text-white">OI</div>
            </div>
            <div class="max-w-2xl">
                <p class="eyebrow">Organisation lead</p>
                <h2 class="section-title mt-4">Led by {{ config('organization.founder') }}</h2>
                <p class="mt-5 text-base leading-8 text-zinc-600">BHA Help Ladder is a counselling and mental health advocacy organisation focused on helping people cope with life’s challenges and encouraging healthier conversations in schools and communities.</p>
                <p class="mt-4 text-sm leading-7 text-zinc-500">A full professional biography, qualifications and portrait have not yet been supplied and will be added only after client review.</p>
                <a href="{{ route('about') }}" class="mt-7 inline-flex items-center text-sm font-bold text-bha-700">Learn more about BHA <span class="ml-2" aria-hidden="true">→</span></a>
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-28">
        <div class="public-container">
            <div class="overflow-hidden rounded-[2.5rem] bg-bha-700 px-7 py-14 text-center text-white sm:px-12 sm:py-20">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-bha-200">Your next step can be simple</p>
                <h2 class="mx-auto mt-5 max-w-3xl font-display text-3xl font-semibold leading-tight sm:text-5xl">When you are ready, choose a suitable time to talk.</h2>
                <p class="mx-auto mt-5 max-w-xl text-sm leading-7 text-bha-100">Book from available appointments, or contact us about individual, school or community support.</p>
                <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                    <a href="{{ route('booking') }}" class="inline-flex min-h-12 items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-bold text-bha-800 transition hover:bg-bha-50">Book an Appointment</a>
                    <a href="{{ route('contact') }}" class="inline-flex min-h-12 items-center justify-center rounded-full border border-white/30 px-6 py-3 text-sm font-bold text-white transition hover:bg-white/10">Send an enquiry</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
