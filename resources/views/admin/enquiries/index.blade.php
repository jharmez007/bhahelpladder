<x-layouts.app>
    <div class="mx-auto w-full max-w-6xl py-4 sm:py-8">
        <div>
            <p class="eyebrow">Administrator · enquiries</p>
            <h1 class="mt-3 font-display text-3xl font-semibold text-ink sm:text-4xl">General enquiries</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-zinc-600">Contact submissions are operational messages, not clinical records. Handle their contents with care and do not copy them into analytics or logs.</p>
        </div>

        <div class="mt-10 overflow-hidden rounded-3xl border border-bha-100 bg-white shadow-sm">
            @forelse ($enquiries as $enquiry)
                <article class="grid gap-4 border-b border-bha-100 p-6 last:border-0 sm:grid-cols-[0.3fr_0.7fr] sm:p-8">
                    <div>
                        <h2 class="font-bold text-ink">{{ $enquiry->name }}</h2>
                        <a class="mt-1 block break-all text-sm text-bha-700 hover:underline" href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a>
                        <time class="mt-3 block text-xs text-zinc-500" datetime="{{ $enquiry->created_at->toIso8601String() }}">{{ $enquiry->created_at->timezone('Africa/Lagos')->format('j M Y, g:i a') }} WAT</time>
                    </div>
                    <p class="whitespace-pre-line text-sm leading-7 text-zinc-700">{{ $enquiry->message }}</p>
                </article>
            @empty
                <div class="grid min-h-72 place-items-center p-8 text-center">
                    <div>
                        <span class="mx-auto grid size-14 place-items-center rounded-full bg-bha-100 text-2xl text-bha-700" aria-hidden="true">○</span>
                        <h2 class="mt-5 font-display text-2xl font-semibold">No enquiries yet</h2>
                        <p class="mt-2 text-sm text-zinc-600">New public contact submissions will appear here.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-6">{{ $enquiries->links() }}</div>
    </div>
</x-layouts.app>
