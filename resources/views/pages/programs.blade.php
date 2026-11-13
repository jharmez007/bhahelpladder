<x-layouts.public
    title="School & Community Programs"
    description="School counselling, educator awareness, community outreach and child protection advocacy from BHA Help Ladder."
>
    <section class="bg-ink py-16 text-white sm:py-24">
        <div class="public-container grid gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-end">
            <div>
                <p class="eyebrow !text-bha-300">School &amp; community programs</p>
                <h1 class="mt-5 font-display text-4xl font-semibold leading-[1.08] tracking-tight sm:text-6xl">Building healthier conversations where people already gather.</h1>
            </div>
            <p class="text-lg leading-8 text-zinc-300">BHA Help Ladder supports awareness and wellbeing across schools, community groups and faith-based settings.</p>
        </div>
    </section>

    <section class="py-20 sm:py-28">
        <div class="public-container grid gap-12 lg:grid-cols-[0.7fr_1.3fr]">
            <div>
                <p class="eyebrow">For schools</p>
                <h2 class="section-title mt-4">Support across the school community.</h2>
                <p class="mt-5 text-base leading-8 text-zinc-600">Initial requests should come from an adult representative of the school or a parent or guardian.</p>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach ([
                    ['Students', 'School counselling pathways discussed through the appropriate responsible adult.'],
                    ['Teachers', 'Support and mental health awareness designed for educators.'],
                    ['Administrators', 'A route to discuss support needs across the wider school environment.'],
                    ['Seminars', 'Educator awareness sessions with content and scope agreed in advance.'],
                ] as [$title, $copy])
                    <article class="soft-card">
                        <h3 class="font-display text-2xl font-semibold">{{ $title }}</h3>
                        <p class="mt-3 text-sm leading-7 text-zinc-600">{{ $copy }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-bha-50 py-20 sm:py-28">
        <div class="public-container grid items-center gap-12 lg:grid-cols-2">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-3xl bg-bha-700 p-8 text-white sm:translate-y-7">
                    <span class="text-4xl" aria-hidden="true">○</span>
                    <h3 class="mt-12 font-display text-2xl font-semibold">Community outreach</h3>
                    <p class="mt-3 text-sm leading-7 text-bha-100">Mental health advocacy and awareness for community settings.</p>
                </div>
                <div class="rounded-3xl bg-white p-8 shadow-sm">
                    <span class="text-4xl text-bha-700" aria-hidden="true">◇</span>
                    <h3 class="mt-12 font-display text-2xl font-semibold">Faith-based outreach</h3>
                    <p class="mt-3 text-sm leading-7 text-zinc-600">Respectful engagement with faith-based groups and their communities.</p>
                </div>
            </div>
            <div>
                <p class="eyebrow">For communities</p>
                <h2 class="section-title mt-4">Awareness grounded in each setting.</h2>
                <p class="mt-5 text-base leading-8 text-zinc-600">Program format, audience, safeguarding needs and practical arrangements are discussed before any activity is confirmed.</p>
                <a href="{{ route('contact') }}" class="btn-primary mt-8">Enquire about a program</a>
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-28">
        <div class="public-container max-w-4xl">
            <div class="rounded-[2.5rem] border border-amber-200 bg-amber-50 p-8 sm:p-12">
                <p class="eyebrow !text-amber-800">Safeguarding note</p>
                <h2 class="mt-4 font-display text-3xl font-semibold text-amber-950">Child-related requests begin with an adult.</h2>
                <p class="mt-4 text-base leading-8 text-amber-900">Direct minor self-registration and clinical intake are not part of Phase 1. A parent, guardian, educator or another responsible adult should make the initial enquiry while BHA Help Ladder develops an approved consent and safeguarding workflow.</p>
            </div>
        </div>
    </section>
</x-layouts.public>
