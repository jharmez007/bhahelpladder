<x-layouts.public
    title="Services"
    description="Explore BHA Help Ladder services for individuals, schools, educators and communities."
>
    <section class="bg-bha-50 py-16 sm:py-24">
        <div class="public-container max-w-4xl text-center">
            <p class="eyebrow">Our services</p>
            <h1 class="display-title mt-5">Different settings. One thoughtful starting point.</h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-zinc-600">Explore the areas BHA Help Ladder supports, then use the enquiry route to discuss current availability and fit.</p>
        </div>
    </section>

    <section class="py-20 sm:py-28">
        <div class="public-container grid gap-6 lg:grid-cols-2">
            @foreach ([
                ['Individual counselling', 'Appointment-based support for adults who would like space to talk through a general concern or life challenge.', ['Private, respectful conversation', 'Appointment-based support', 'Availability confirmed by the team']],
                ['School counselling', 'Support intended for students, teachers and administrators within school communities.', ['Student support pathways', 'Teacher and administrator support', 'Adult-led requests for minors']],
                ['Educator mental health seminars', 'Awareness sessions that help educators engage with mental health topics in their professional setting.', ['Mental health awareness', 'Education-focused context', 'Scope agreed before delivery']],
                ['Community & faith-based outreach', 'Mental health advocacy and awareness activities for community and faith-based groups.', ['Community conversation', 'Faith-based outreach', 'Program scope discussed by enquiry']],
                ['Child protection advocacy', 'Advocacy approached through safeguarding-conscious, adult-led enquiries.', ['Responsible adult as first contact', 'No direct minor self-registration', 'Consent workflow pending review']],
            ] as $index => [$title, $copy, $points])
                <article class="soft-card {{ $index === 4 ? 'lg:col-span-2' : '' }}">
                    <div class="flex items-start gap-5">
                        <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-bha-100 text-sm font-black text-bha-700">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <h2 class="font-display text-2xl font-semibold text-ink sm:text-3xl">{{ $title }}</h2>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-zinc-600">{{ $copy }}</p>
                            <ul class="mt-6 grid gap-3 text-sm text-zinc-700 sm:grid-cols-2">
                                @foreach ($points as $point)
                                    <li class="flex gap-3"><span class="mt-2 size-1.5 shrink-0 rounded-full bg-bha-500"></span>{{ $point }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="pb-20 sm:pb-28">
        <div class="public-container">
            <div class="rounded-[2.5rem] bg-ink p-8 text-white sm:p-12 lg:flex lg:items-center lg:justify-between lg:gap-10">
                <div class="max-w-2xl">
                    <p class="eyebrow !text-bha-300">Not sure where to begin?</p>
                    <h2 class="mt-4 font-display text-3xl font-semibold">A brief general enquiry is enough.</h2>
                    <p class="mt-4 text-sm leading-7 text-zinc-300">Please do not send detailed health or clinical information through the website.</p>
                </div>
                <a href="{{ route('contact') }}" class="mt-7 inline-flex min-h-12 shrink-0 items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-bold text-bha-800 lg:mt-0">Send an enquiry</a>
            </div>
        </div>
    </section>
</x-layouts.public>
