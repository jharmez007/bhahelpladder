<?php

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Exceptions\BookingConflictException;
use App\Models\Appointment;
use App\Models\CounsellorProfile;
use App\Services\BookingService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $statusFilter = '';
    public array $assignments = [];
    public array $meetingDetails = [];

    #[Computed]
    public function appointments()
    {
        return Appointment::query()
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->with(['client', 'service', 'counsellorProfile.user'])
            ->orderByDesc('starts_at_utc')->get();
    }

    #[Computed]
    public function counsellors()
    {
        return CounsellorProfile::query()->where('is_active', true)->with('user')->whereHas('user', fn ($query) => $query->where('role', UserRole::Counsellor))->get()->sortBy('user.name');
    }

    public function updateStatus(int $id, string $target): void
    {
        $status = AppointmentStatus::tryFrom($target);
        abort_unless($status, 422);
        $this->runBookingAction(fn () => app(BookingService::class)->transition(auth()->user(), Appointment::query()->findOrFail($id), $status));
    }

    public function assign(int $id): void
    {
        $this->validate(["assignments.{$id}" => ['required', 'exists:counsellor_profiles,id']]);
        $this->runBookingAction(fn () => app(BookingService::class)->assignCounsellor(auth()->user(), Appointment::query()->findOrFail($id), CounsellorProfile::query()->findOrFail($this->assignments[$id])));
    }

    public function saveMeetingDetails(int $id): void
    {
        $this->validate(["meetingDetails.{$id}" => ['nullable', 'string', 'max:1000']]);
        $this->runBookingAction(fn () => app(BookingService::class)->updateMeetingDetails(auth()->user(), Appointment::query()->findOrFail($id), $this->meetingDetails[$id] ?? null));
    }

    private function runBookingAction(Closure $action): void
    {
        try { $action(); } catch (BookingConflictException $exception) { throw ValidationException::withMessages(['appointment' => $exception->getMessage()]); }
        unset($this->appointments);
        session()->flash('status', 'Appointment updated.');
    }
};
?>

<div class="mx-auto w-full max-w-7xl py-4 sm:py-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="eyebrow">Booking operations</p><h1 class="mt-3 font-display text-3xl font-semibold text-ink">Manage appointments</h1></div><flux:select wire:model.live="statusFilter" label="Filter status" class="sm:w-56"><flux:select.option value="">All statuses</flux:select.option>@foreach (\App\Enums\AppointmentStatus::cases() as $status)<flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>@endforeach</flux:select></div>
    @if (session('status')) <div class="mt-6 rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-900">{{ session('status') }}</div> @endif @error('appointment')<div class="mt-6 rounded-2xl bg-red-50 p-4 text-sm text-red-800">{{ $message }}</div>@enderror
    <div class="mt-8 grid gap-4">@forelse ($this->appointments as $appointment)<article class="soft-card"><div class="grid gap-5 xl:grid-cols-[1.2fr_1fr_auto]"><div><div class="flex flex-wrap gap-3"><h2 class="font-display text-xl font-semibold">{{ $appointment->client->name }}</h2><span class="rounded-full bg-bha-50 px-3 py-1 text-xs font-bold">{{ $appointment->status->label() }}</span></div><p class="mt-2 text-sm font-semibold">{{ $appointment->localStart()->format('D, j M Y · g:i a') }} WAT</p><p class="mt-1 text-sm text-zinc-600">{{ $appointment->service->name }} · {{ $appointment->session_mode->label() }} · {{ $appointment->counsellorProfile->user->name }}</p><p class="mt-1 text-xs text-zinc-500">{{ strtoupper(substr($appointment->reference, 0, 8)) }}</p></div><div class="flex items-end gap-2"><flux:select wire:model="assignments.{{ $appointment->id }}" label="Assign counsellor" placeholder="Choose">@foreach ($this->counsellors as $profile)<flux:select.option value="{{ $profile->id }}">{{ $profile->user->name }}</flux:select.option>@endforeach</flux:select><flux:button wire:click="assign({{ $appointment->id }})" size="sm">Assign</flux:button></div><div class="flex flex-wrap items-start gap-2">@foreach ($appointment->status->allowedTransitions() as $status)<flux:button size="sm" :variant="$status === \App\Enums\AppointmentStatus::Cancelled ? 'danger' : 'filled'" wire:click="updateStatus({{ $appointment->id }}, '{{ $status->value }}')">{{ $status->label() }}</flux:button>@endforeach</div></div>@if ($appointment->session_mode === \App\Enums\SessionMode::Online)<div class="mt-5 border-t border-zinc-100 pt-5"><flux:textarea wire:model="meetingDetails.{{ $appointment->id }}" label="Online session details" rows="2" /><flux:button wire:click="saveMeetingDetails({{ $appointment->id }})" size="sm" class="mt-3">Save details</flux:button></div>@endif</article>@empty<div class="soft-card text-center text-sm text-zinc-600">No appointments match this filter.</div>@endforelse</div>
</div>
