<?php

use App\Enums\UserRole;
use App\Models\CounsellorProfile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $name = '';
    public string $description = '';
    public int $durationMinutes = 60;
    public int $bufferBeforeMinutes = 10;
    public int $bufferAfterMinutes = 10;
    public string $fee = '';
    public bool $inPerson = true;
    public bool $online = true;

    #[Computed]
    public function services()
    {
        return Service::query()->orderBy('name')->get();
    }

    #[Computed]
    public function counsellors()
    {
        return User::query()->where('role', UserRole::Counsellor)->with(['counsellorProfile.services'])->orderBy('name')->get();
    }

    public function createService(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'durationMinutes' => ['required', 'integer', 'between:15,480'],
            'bufferBeforeMinutes' => ['required', 'integer', 'between:0,120'],
            'bufferAfterMinutes' => ['required', 'integer', 'between:0,120'],
            'fee' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'inPerson' => ['boolean'],
            'online' => ['boolean'],
        ]);
        $modes = array_values(array_filter([
            $data['inPerson'] ? 'in_person' : null,
            $data['online'] ? 'online' : null,
        ]));

        if ($modes === []) {
            $this->addError('online', 'Select at least one session format.');
            return;
        }

        $slug = Str::slug($data['name']);
        if (Service::query()->where('slug', $slug)->exists()) {
            $slug .= '-'.Str::lower(Str::random(5));
        }

        Service::query()->create([
            'name' => trim($data['name']), 'slug' => $slug, 'description' => filled($data['description']) ? trim($data['description']) : null,
            'duration_minutes' => $data['durationMinutes'], 'buffer_before_minutes' => $data['bufferBeforeMinutes'], 'buffer_after_minutes' => $data['bufferAfterMinutes'],
            'fee_minor' => filled($data['fee']) ? (int) round(((float) $data['fee']) * 100) : null, 'currency' => 'NGN', 'modes' => $modes, 'is_active' => true,
        ]);
        $this->reset('name', 'description', 'fee');
        unset($this->services);
    }

    public function toggleService(int $id): void
    {
        $service = Service::query()->findOrFail($id);
        $service->update(['is_active' => ! $service->is_active]);
        unset($this->services);
    }

    public function toggleCounsellorService(int $userId, int $serviceId): void
    {
        $user = User::query()->where('role', UserRole::Counsellor)->findOrFail($userId);
        $profile = $user->counsellorProfile()->firstOrCreate([], ['is_active' => true]);
        $profile->services()->toggle($serviceId);
        unset($this->counsellors);
    }

    public function toggleCounsellor(int $userId): void
    {
        $user = User::query()->where('role', UserRole::Counsellor)->findOrFail($userId);
        $profile = $user->counsellorProfile()->firstOrCreate([], ['is_active' => true]);
        $profile->update(['is_active' => ! $profile->is_active]);
        unset($this->counsellors);
    }
};
?>

<div class="mx-auto w-full max-w-6xl py-4 sm:py-8">
    <p class="eyebrow">Booking setup</p><h1 class="mt-3 font-display text-3xl font-semibold text-ink">Services and counsellor assignments</h1>
    <div class="mt-8 grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
        <section class="soft-card"><h2 class="font-display text-xl font-semibold">Add a service</h2><form wire:submit="createService" class="mt-5 grid gap-4"><flux:input wire:model="name" label="Service name" /><flux:textarea wire:model="description" label="Short description (optional)" rows="2" /><div class="grid grid-cols-3 gap-3"><flux:input wire:model="durationMinutes" type="number" label="Minutes" /><flux:input wire:model="bufferBeforeMinutes" type="number" label="Buffer before" /><flux:input wire:model="bufferAfterMinutes" type="number" label="Buffer after" /></div><flux:input wire:model="fee" type="number" step="0.01" label="Fee in NGN (optional)" /><div class="flex gap-5"><flux:checkbox wire:model="inPerson" label="In person" /><flux:checkbox wire:model="online" label="Online" /></div><flux:button type="submit" variant="primary">Create active service</flux:button></form></section>
        <section class="soft-card"><h2 class="font-display text-xl font-semibold">Available services</h2><div class="mt-5 divide-y divide-zinc-100">@forelse ($this->services as $service)<div class="flex items-start justify-between gap-4 py-4"><div><p class="font-bold">{{ $service->name }}</p><p class="mt-1 text-xs text-zinc-500">{{ $service->duration_minutes }} min · {{ $service->buffer_before_minutes }}/{{ $service->buffer_after_minutes }} min buffers · {{ implode(', ', array_map(fn($mode) => str($mode)->replace('_', ' ')->title(), $service->modes)) }}</p></div><flux:button size="sm" wire:click="toggleService({{ $service->id }})" :variant="$service->is_active ? 'filled' : 'primary'">{{ $service->is_active ? 'Deactivate' : 'Activate' }}</flux:button></div>@empty<p class="py-5 text-sm text-zinc-500">No services configured.</p>@endforelse</div></section>
    </div>

    <section class="soft-card mt-6"><h2 class="font-display text-xl font-semibold">Counsellor assignments</h2><p class="mt-2 text-sm text-zinc-600">Counsellor accounts are created securely from the command line; services can then be assigned here.</p><div class="mt-5 grid gap-4">@forelse ($this->counsellors as $counsellor) @php($profile = $counsellor->counsellorProfile)<div class="rounded-2xl border border-zinc-200 p-4"><div class="flex items-center justify-between"><div><p class="font-bold">{{ $counsellor->name }}</p><p class="text-xs text-zinc-500">{{ $counsellor->email }}</p></div><flux:button size="sm" wire:click="toggleCounsellor({{ $counsellor->id }})">{{ $profile?->is_active ? 'Pause booking' : 'Enable booking' }}</flux:button></div><div class="mt-4 flex flex-wrap gap-4">@foreach ($this->services as $service)<label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:click="toggleCounsellorService({{ $counsellor->id }}, {{ $service->id }})" @checked($profile?->services->contains($service->id)) class="rounded border-zinc-300 text-bha-700">{{ $service->name }}</label>@endforeach</div></div>@empty<p class="text-sm text-zinc-500">No counsellor accounts exist yet.</p>@endforelse</div></section>
</div>
