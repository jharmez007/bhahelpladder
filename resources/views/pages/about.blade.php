<x-layouts.public
    title="About"
    description="Learn about BHA Help Ladder, a counselling and mental health advocacy organisation."
>
    <section class="bg-bha-50 py-16 sm:py-24">
        <div class="public-container grid gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-end">
            <div>
                <p class="eyebrow">About BHA Help Ladder</p>
                <h1 class="display-title mt-5">Helping people find steadier ground, one step at a time.</h1>
            </div>
            <p class="text-lg leading-8 text-zinc-600">BHA Help Ladder is a counselling and mental health advocacy organisation supporting individuals, schools and communities.</p>
        </div>
    </section>

    <section class="py-20 sm:py-28">
        <div class="public-container grid gap-12 lg:grid-cols-2">
            <div>
                <p class="eyebrow">Our focus</p>
                <h2 class="section-title mt-4">Respectful support without unrealistic promises.</h2>
            </div>
            <div class="space-y-5 text-base leading-8 text-zinc-600">
                <p>Our work brings together appointment-based individual counselling, school support, educator mental health awareness, community and faith-based outreach, and child protection advocacy.</p>
                <p>We aim to make the first step feel clear and manageable. Every enquiry is treated as a starting point for discussion—not as a diagnosis, clinical intake or guarantee of a particular outcome.</p>
                <blockquote class="rounded-3xl border-l-4 border-bha-500 bg-bha-50 p-7 font-display text-2xl leading-snug text-bha-900">“{{ config('organization.motto') }}”</blockquote>
            </div>
        </div>
    </section>

    <section class="bg-ink py-20 text-white sm:py-28">
        <div class="public-container">
            <p class="eyebrow !text-bha-300">How we approach the work</p>
            <div class="mt-10 grid gap-5 md:grid-cols-3">
                @foreach ([
                    ['Human first', 'We communicate with warmth, respect and plain language.'],
                    ['Privacy conscious', 'We ask for only the information needed at each stage and keep clinical records outside this product.'],
                    ['Safeguarding led', 'Child-related requests begin with a responsible adult while a formal consent workflow is developed.'],
                ] as [$title, $copy])
                    <article class="rounded-3xl border border-white/10 bg-white/[0.05] p-7">
                        <h2 class="font-display text-2xl font-semibold">{{ $title }}</h2>
                        <p class="mt-4 text-sm leading-7 text-zinc-300">{{ $copy }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-28">
        <div class="public-container grid items-center gap-12 lg:grid-cols-[0.8fr_1.2fr]">
            <div class="mx-auto grid aspect-square w-full max-w-sm place-items-center rounded-full bg-bha-100">
                <div class="grid size-32 place-items-center rounded-full bg-bha-700 font-display text-5xl font-semibold text-white">OI</div>
            </div>
            <div class="max-w-2xl">
                <p class="eyebrow">Leadership</p>
                <h2 class="section-title mt-4">{{ config('organization.founder') }}</h2>
                <p class="mt-5 text-base leading-8 text-zinc-600">{{ config('organization.founder') }} leads BHA Help Ladder. This site intentionally does not claim qualifications, accreditations or professional memberships that have not yet been supplied and verified by the client.</p>
                <a href="{{ route('contact') }}" class="btn-primary mt-8">Contact BHA Help Ladder</a>
            </div>
        </div>
    </section>
</x-layouts.public>
