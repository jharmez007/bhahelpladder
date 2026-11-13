@php
    $heroSlides = [
        ['label' => 'Counselling support', 'file' => 'hero-counselling.jpg', 'fallback' => '#4e285f'],
        ['label' => 'School wellbeing programs', 'file' => 'hero-schools.jpg', 'fallback' => '#263d35'],
        ['label' => 'Community mental health advocacy', 'file' => 'hero-community.jpg', 'fallback' => '#554036'],
    ];
@endphp

<section
    class="relative isolate min-h-[calc(100svh-5rem)] overflow-hidden bg-bha-900 text-white"
    x-data="{
        active: 0,
        total: {{ count($heroSlides) }},
        timer: null,
        reducedMotion: false,
        init() {
            this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            this.start();
        },
        start() {
            this.stop();
            if (!this.reducedMotion) this.timer = window.setInterval(() => this.next(), 7000);
        },
        stop() {
            if (this.timer) window.clearInterval(this.timer);
            this.timer = null;
        },
        show(index) {
            this.active = index;
            this.start();
        },
        next() {
            this.active = (this.active + 1) % this.total;
        },
        previous() {
            this.active = (this.active - 1 + this.total) % this.total;
        },
    }"
    x-on:mouseenter="stop()"
    x-on:mouseleave="start()"
    x-on:focusin="stop()"
    x-on:focusout="start()"
>
    <div class="absolute inset-0" aria-hidden="true">
        @foreach ($heroSlides as $index => $slide)
            <div
                class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-opacity duration-1000 ease-in-out"
                style="background-color: {{ $slide['fallback'] }}; background-image: linear-gradient(90deg, rgba(17, 7, 24, .94) 0%, rgba(32, 14, 43, .74) 42%, rgba(17, 7, 24, .28) 72%, rgba(17, 7, 24, .18) 100%), url('{{ asset('images/home/'.$slide['file']) }}');"
                x-bind:class="active === {{ $index }} ? 'opacity-100' : 'opacity-0'"
            ></div>
        @endforeach
        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(0,0,0,0.02)_50%,rgba(5,11,9,0.62)_100%)]"></div>
    </div>

    <div class="public-container relative z-10 flex min-h-[calc(100svh-5rem)] items-center py-20 sm:py-24">
        <div class="max-w-4xl pb-20 pt-6 sm:pb-24 lg:pt-0">
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-white/75">Counselling &amp; mental health advocacy</p>
            <h1 class="mt-6 max-w-4xl font-display text-5xl leading-[0.98] font-semibold tracking-[-0.035em] text-white sm:text-6xl lg:text-[5.75rem]">
                A steady hand when life feels difficult to navigate.
            </h1>
            <p class="mt-7 max-w-2xl text-base leading-8 text-white/85 sm:text-lg">
                Thoughtful support for individuals, educators and communities seeking a considered next step.
            </p>
            <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('booking') }}" class="inline-flex min-h-13 items-center justify-center rounded-lg bg-white px-7 py-3.5 text-sm font-bold text-bha-900 shadow-xl shadow-black/10 transition hover:-translate-y-0.5 hover:bg-bha-50">
                    Book an Appointment <span aria-hidden="true" class="ml-3">&rarr;</span>
                </a>
                <a href="{{ route('services') }}" class="inline-flex min-h-13 items-center justify-center rounded-lg border border-white/40 bg-white/10 px-7 py-3.5 text-sm font-bold text-white backdrop-blur-sm transition hover:-translate-y-0.5 hover:bg-white/20">
                    Explore Our Services
                </a>
            </div>
        </div>
    </div>

    <div class="public-container absolute inset-x-0 bottom-7 z-20 sm:bottom-9">
        <div class="flex items-center justify-between gap-5 border-t border-white/25 pt-5">
            <p class="hidden text-xs font-semibold tracking-wide text-white/70 sm:block" aria-live="polite">
                <span x-text="String(active + 1).padStart(2, '0')"></span>
                <span class="mx-2 text-white/40">/</span>
                <span>{{ str_pad((string) count($heroSlides), 2, '0', STR_PAD_LEFT) }}</span>
                <span class="sr-only" x-text="' — ' + {{ Illuminate\Support\Js::from(array_column($heroSlides, 'label')) }}[active]"></span>
            </p>

            <div class="flex flex-1 items-center justify-center gap-3" aria-label="Homepage images">
                @foreach ($heroSlides as $index => $slide)
                    <button
                        type="button"
                        class="group flex min-h-11 items-center px-1"
                        x-on:click="show({{ $index }})"
                        x-bind:aria-pressed="active === {{ $index }}"
                        aria-label="Show {{ strtolower($slide['label']) }} image"
                    >
                        <span
                            class="block h-0.5 rounded-full bg-white transition-all duration-300"
                            x-bind:class="active === {{ $index }} ? 'w-12 opacity-100' : 'w-6 opacity-45 group-hover:opacity-80'"
                        ></span>
                    </button>
                @endforeach
            </div>

            <div class="flex items-center gap-2">
                <button type="button" class="grid size-11 place-items-center rounded-full border border-white/35 text-lg text-white transition hover:bg-white/15" x-on:click="previous(); start()" aria-label="Previous image">&larr;</button>
                <button type="button" class="grid size-11 place-items-center rounded-full border border-white/35 text-lg text-white transition hover:bg-white/15" x-on:click="next(); start()" aria-label="Next image">&rarr;</button>
            </div>
        </div>
    </div>
</section>
