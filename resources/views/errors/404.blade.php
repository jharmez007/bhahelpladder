<x-layouts.public title="Page not found" description="The requested page could not be found.">
    <section class="grid min-h-[60vh] place-items-center bg-bha-50 px-5 py-20 text-center">
        <div class="max-w-xl">
            <p class="eyebrow">404 · Page not found</p>
            <h1 class="section-title mt-4">That page is not on this step of the ladder.</h1>
            <p class="mt-4 text-sm leading-7 text-zinc-600">The address may have changed, or the page may not exist yet.</p>
            <a href="{{ route('home') }}" class="btn-primary mt-7">Return home</a>
        </div>
    </section>
</x-layouts.public>
