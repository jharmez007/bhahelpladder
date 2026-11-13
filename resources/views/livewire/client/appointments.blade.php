<?php

use App\Exceptions\BookingConflictException;
use App\Models\Appointment;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public ?int $rescheduleId = null;
    public string $rescheduleDate = '';
    public string $rescheduleStart = '';

    #[Computed]
    public function appointments()
    {
        return Appointment::query()
            ->where('client_id', auth()->id())
            ->with(['service', 'counsellorProfile.user'])
            ->orderByDesc('starts_at_utc')
            ->get();
    }

    #[Computed]
    public function rescheduleAppointment(): ?Appointment
    {
        return $this->rescheduleId
            ? Appointment::query()->where('client_id', auth()->id())->with(['service', 'counsellorProfile'])->find($this->rescheduleId)
            : null;
    }

    #[Computed]
    public function slots(): array
    {
        if (! $this->rescheduleAppointment || ! $this->rescheduleDate) {
            return [];
        }

        $date = CarbonImmutable::createFromFormat('Y-m-d', $this->rescheduleDate, config('booking.timezone'));

        return app(AvailabilityService::class)->availableSlots(
            $this->rescheduleAppointment->service,
            $this->rescheduleAppointment->counsellorProfile,
            $date,
            $this->rescheduleAppointment,
        );
    }

    public function beginReschedule(int $id): void
    {
        $appointment = Appointment::query()->where('client_id', auth()->id())->findOrFail($id);
        $this->rescheduleId = $appointment->id;
        $this->rescheduleDate = '';
        $this->rescheduleStart = '';
    }

    public function updatedRescheduleDate(): void { $this->rescheduleStart = ''; }

    public function reschedule(): void
    {
        $this->validate(['rescheduleStart' => ['required', 'date']]);
        $appointment = Appointment::query()->where('client_id', auth()->id())->findOrFail($this->rescheduleId);

        try {
            app(BookingService::class)->reschedule(auth()->user(), $appointment, CarbonImmutable::parse($this->rescheduleStart)->utc());
        } catch (BookingConflictException $exception) {
            throw ValidationException::withMessages(['rescheduleStart' => $exception->getMessage()]);
        }

        $this->reset('rescheduleId', 'rescheduleDate', 'rescheduleStart');
        unset($this->appointments, $this->rescheduleAppointment, $this->slots);
        session()->flash('status', 'Appointment rescheduled.');
    }

    public function cancel(int $id): void
    {
        $appointment = Appointment::query()->where('client_id', auth()->id())->findOrFail($id);

        try {
            app(BookingService::class)->cancel(auth()->user(), $appointment);
        } catch (BookingConflictException $exception) {
            throw ValidationException::withMessages(['appointment' => $exception->getMessage()]);
        }

        unset($this->appointments);
        session()->flash('status', 'Appointment cancelled.');
    }
};
?>

<div class="mx-auto w-full max-w-6xl py-4 sm:py-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="eyebrow">Appointments</p><h1 class="mt-3 font-display text-3xl font-semibold text-ink">Your appointment history</h1></div>
        <a href="{{ route('client.appointments.book') }}" class="btn-primary">Request an appointment</a>
    </div>

    @if (session('status')) <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">{{ session('status') }}</div> @endif
    @error('appointment') <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $message }}</div> @enderror

    @if ($rescheduleId && $this->rescheduleAppointment)
        <section class="soft-card mt-8">
            <div class="flex items-start justify-between"><div><h2 class="font-display text-xl font-semibold">Choose a new time</h2><p class="mt-1 text-sm text-zinc-600">The current appointment remains unchanged until the new time is secured.</p></div><button wire:click="$set('rescheduleId', null)" class="text-sm font-bold text-zinc-500">Close</button></div>
            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                <flux:input wire:model.live="rescheduleDate" type="date" label="New date" min="{{ now(config('booking.timezone'))->toDateString() }}" />
                <flux:select wire:model="rescheduleStart" label="New time" placeholder="Choose a time">
                    @foreach ($this->slots as $slot) <flux:select.option value="{{ $slot['value'] }}">{{ $slot['label'] }} WAT</flux:select.option> @endforeach
                </flux:select>
                <div class="self-end"><flux:button wire:click="reschedule" variant="primary">Confirm new time</flux:button></div>
            </div>
        </section>
    @endif

    <div class="mt-8 grid gap-4">
        @forelse ($this->appointments as $appointment)
            <article class="soft-card">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-3"><h2 class="font-display text-xl font-semibold">{{ $appointment->service->name }}</h2><span class="rounded-full bg-bha-50 px-3 py-1 text-xs font-bold text-bha-800">{{ $appointment->status->label() }}</span></div>
                        <p class="mt-3 text-sm font-semibold">{{ $appointment->localStart()->format('D, j M Y · g:i a') }} WAT</p>
                        <p class="mt-1 text-sm text-zinc-600">{{ $appointment->session_mode->label() }} · {{ $appointment->duration_minutes }} min · {{ $appointment->counsellorProfile->user->name }}</p>
                        <p class="mt-2 text-xs text-zinc-500">Reference {{ strtoupper(substr($appointment->reference, 0, 8)) }}</p>
                        @if ($appointment->session_mode === \App\Enums\SessionMode::Online && $appointment->online_meeting_details)
                            <div class="mt-4 rounded-xl bg-bha-50 p-3 text-sm"><span class="font-bold">Online session details:</span> {{ $appointment->online_meeting_details }}</div>
                        @endif
                    </div>
                    @if ($appointment->status->reservesAvailability())
                        <div class="flex gap-2"><flux:button size="sm" wire:click="beginReschedule({{ $appointment->id }})">Reschedule</flux:button><flux:button size="sm" variant="danger" wire:click="cancel({{ $appointment->id }})" wire:confirm="Cancel this appointment?">Cancel</flux:button></div>
                    @endif
                </div>
            </article>
        @empty
            <div class="soft-card text-center"><h2 class="font-display text-xl font-semibold">No appointments yet</h2><p class="mt-2 text-sm text-zinc-600">Your requests and appointment history will appear here.</p></div>
        @endforelse
    </div>
</div>
