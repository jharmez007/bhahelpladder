<x-layouts.app>
    <div class="mx-auto w-full max-w-6xl py-4 sm:py-8">
        <p class="eyebrow">Administrator dashboard</p>
        <div class="mt-3 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="font-display text-3xl font-semibold text-ink sm:text-4xl">Operations overview</h1>
                <p class="mt-3 text-sm leading-6 text-zinc-600">Review public enquiries and manage appointment operations.</p>
            </div>
            <a href="{{ route('admin.enquiries.index') }}" class="btn-primary">View enquiries</a>
        </div>

        <div class="mt-10 grid gap-5 md:grid-cols-3">
            <div class="soft-card">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-zinc-500">Enquiries</p>
                <p class="mt-5 text-3xl font-bold text-ink">{{ \App\Models\Enquiry::query()->count() }}</p>
                <p class="mt-2 text-xs text-zinc-500">Stored contact submissions</p>
            </div>
            <div class="soft-card md:col-span-2">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-zinc-500">Appointments</p>
                <p class="mt-5 text-3xl font-bold text-ink">{{ \App\Models\Appointment::query()->count() }}</p>
                <p class="mt-2 text-sm leading-6 text-zinc-600">Database-backed bookings with assignment and status controls.</p>
            </div>
        </div>
    </div>
</x-layouts.app>
