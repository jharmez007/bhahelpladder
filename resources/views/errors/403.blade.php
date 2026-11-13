<x-layouts.public title="Access denied" description="You do not have permission to view this page.">
    <section class="grid min-h-[60vh] place-items-center bg-bha-50 px-5 py-20 text-center">
        <div class="max-w-xl">
            <p class="eyebrow">403 · Access denied</p>
            <h1 class="section-title mt-4">This area is not available to your account.</h1>
            <p class="mt-4 text-sm leading-7 text-zinc-600">Return to your dashboard to continue with the tools assigned to your role.</p>
            <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="btn-primary mt-7">{{ auth()->check() ? 'Return to dashboard' : 'Return home' }}</a>
        </div>
    </section>
</x-layouts.public>
