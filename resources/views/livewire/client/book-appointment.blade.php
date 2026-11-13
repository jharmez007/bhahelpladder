<?php

use App\Enums\SessionMode;
use App\Exceptions\BookingConflictException;
use App\Models\CounsellorProfile;
use App\Models\Service;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $serviceId = '';
    public string $counsellorProfileId = '';
    public string $mode = '';
    public string $date = '';
    public string $startsAt = '';
    public bool $reviewing = false;

    #[Computed]
    public function services()
    {
        return Service::query()->where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function counsellors()
    {
        if (! $this->serviceId) {
            return collect();
        }

        return CounsellorProfile::query()
            ->where('is_active', true)
            ->whereHas('services', fn ($query) => $query->whereKey($this->serviceId))
            ->with('user')
            ->get()
            ->sortBy('user.name');
    }

    #[Computed]
    public function selectedService(): ?Service
    {
        return $this->serviceId ? Service::query()->where('is_active', true)->find($this->serviceId) : null;
    }

    #[Computed]
    public function slots(): array
    {
        if (! $this->selectedService || ! $this->counsellorProfileId || ! $this->date) {
            return [];
        }

        $profile = CounsellorProfile::query()->where('is_active', true)->find($this->counsellorProfileId);

        if (! $profile) {
            return [];
        }

        try {
            $date = CarbonImmutable::createFromFormat('Y-m-d', $this->date, config('booking.timezone'));

            return app(AvailabilityService::class)->availableSlots($this->selectedService, $profile, $date);
        } catch (Throwable) {
            return [];
        }
    }

    public function updatedServiceId(): void
    {
        $this->reset('counsellorProfileId', 'mode', 'date', 'startsAt', 'reviewing');
    }

    public function updatedCounsellorProfileId(): void { $this->reset('date', 'startsAt', 'reviewing'); }
    public function updatedDate(): void { $this->reset('startsAt', 'reviewing'); }
    public function updatedMode(): void { $this->reviewing = false; }
    public function updatedStartsAt(): void { $this->reviewing = false; }

    public function review(): void
    {
        $this->validateSelection();
        $this->reviewing = true;
    }

    public function book(): void
    {
        $this->validateSelection();

        try {
            $appointment = app(BookingService::class)->createForClient(
                auth()->user(),
                Service::query()->findOrFail($this->serviceId),
                CounsellorProfile::query()->findOrFail($this->counsellorProfileId),
                SessionMode::from($this->mode),
                CarbonImmutable::parse($this->startsAt)->utc(),
            );
        } catch (BookingConflictException $exception) {
            throw ValidationException::withMessages(['startsAt' => $exception->getMessage()]);
        }

        session()->flash('status', 'Your appointment request was submitted.');
        $this->redirectRoute('client.appointments.index', navigate: true);
    }

    private function validateSelection(): void
    {
        $service = $this->selectedService;

        $this->validate([
            'serviceId' => ['required', Rule::exists('services', 'id')->where('is_active', true)],
            'counsellorProfileId' => ['required', Rule::exists('counsellor_profiles', 'id')->where('is_active', true)],
            'mode' => ['required', Rule::enum(SessionMode::class)],
            'date' => ['required', 'date_format:Y-m-d'],
            'startsAt' => ['required', 'date'],
        ]);

        if (! $service || ! in_array($this->mode, $service->modes ?? [], true)) {
            throw ValidationException::withMessages(['mode' => 'That session mode is not available.']);
        }

        if (! collect($this->slots)->contains('value', $this->startsAt)) {
            throw ValidationException::withMessages(['startsAt' => 'That time is no longer available.']);
        }
    }
};
?>

<div class="mx-auto w-full max-w-5xl py-4 sm:py-8">
    <p class="eyebrow">Private booking</p>
    <h1 class="mt-3 font-display text-3xl font-semibold text-ink sm:text-4xl">Request an appointment</h1>
    <p class="mt-3 max-w-2xl text-sm leading-6 text-zinc-600">Times are shown in West Africa Time. Please do not enter clinical or sensitive information here.</p>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_0.75fr]">
        <form wire:submit="review" class="soft-card grid gap-5" novalidate>
            <flux:select wire:model.live="serviceId" label="Service" placeholder="Choose a service">
                @foreach ($this->services as $service)
                    <flux:select.option value="{{ $service->id }}">{{ $service->name }} · {{ $service->duration_minutes }} min</flux:select.option>
                @endforeach
            </flux:select>

            @if ($this->selectedService)
                <flux:select wire:model.live="mode" label="Session format" placeholder="Choose a format">
                    @foreach ($this->selectedService->modes as $availableMode)
                        <flux:select.option value="{{ $availableMode }}">{{ \App\Enums\SessionMode::from($availableMode)->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif

            <flux:select wire:model.live="counsellorProfileId" label="Counsellor" placeholder="Choose a counsellor">
                @foreach ($this->counsellors as $profile)
                    <flux:select.option value="{{ $profile->id }}">{{ $profile->user->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model.live="date" type="date" label="Date" min="{{ now(config('booking.timezone'))->toDateString() }}" />

            <flux:select wire:model.live="startsAt" label="Available time" placeholder="Choose a time">
                @foreach ($this->slots as $slot)
                    <flux:select.option value="{{ $slot['value'] }}">{{ $slot['label'] }} WAT</flux:select.option>
                @endforeach
            </flux:select>

            @if ($date && $counsellorProfileId && count($this->slots) === 0)
                <p class="text-sm text-amber-800">No times are currently available on this date.</p>
            @endif

            <flux:button type="submit" variant="primary">Review request</flux:button>
        </form>

        <aside class="soft-card h-fit">
            <h2 class="font-display text-xl font-semibold">Request summary</h2>
            @if ($reviewing && $this->selectedService && $startsAt)
                <dl class="mt-5 grid gap-4 text-sm">
                    <div><dt class="text-zinc-500">Service</dt><dd class="font-semibold">{{ $this->selectedService->name }}</dd></div>
                    <div><dt class="text-zinc-500">Date and time</dt><dd class="font-semibold">{{ \Carbon\CarbonImmutable::parse($startsAt)->setTimezone(config('booking.timezone'))->format('D, j M Y · g:i a') }} WAT</dd></div>
                    <div><dt class="text-zinc-500">Duration</dt><dd class="font-semibold">{{ $this->selectedService->duration_minutes }} minutes</dd></div>
                    @if ($this->selectedService->formattedFee())
                        <div><dt class="text-zinc-500">Session fee</dt><dd class="font-semibold">{{ $this->selectedService->formattedFee() }}</dd></div>
                    @endif
                </dl>
                <flux:button wire:click="book" variant="primary" class="mt-6 w-full" wire:loading.attr="disabled">Submit appointment request</flux:button>
            @else
                <p class="mt-4 text-sm leading-6 text-zinc-600">Choose a service, format, counsellor and available time to review your request.</p>
            @endif
        </aside>
    </div>
</div>
