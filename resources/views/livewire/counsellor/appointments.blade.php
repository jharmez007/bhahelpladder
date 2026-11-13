<?php

use App\Enums\AppointmentStatus;
use App\Exceptions\BookingConflictException;
use App\Models\Appointment;
use App\Services\BookingService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public array $meetingDetails = [];

    #[Computed]
    public function appointments()
    {
        $profile = auth()->user()->counsellorProfile;

        return $profile
            ? $profile->appointments()->with(['client', 'service'])->orderByDesc('starts_at_utc')->get()
            : collect();
    }

    public function updateStatus(int $id, string $target): void
    {
        $status = AppointmentStatus::tryFrom($target);
        abort_unless($status, 422);
        $appointment = $this->ownedAppointment($id);

        try {
            app(BookingService::class)->transition(auth()->user(), $appointment, $status);
        } catch (BookingConflictException $exception) {
            throw ValidationException::withMessages(['appointment' => $exception->getMessage()]);
        }
        unset($this->appointments);
    }

    public function saveMeetingDetails(int $id): void
    {
        $this->validate(["meetingDetails.{$id}" => ['nullable', 'string', 'max:1000']]);
        app(BookingService::class)->updateMeetingDetails(auth()->user(), $this->ownedAppointment($id), $this->meetingDetails[$id] ?? null);
        unset($this->appointments);
        session()->flash('status', 'Online session details updated.');
    }

    private function ownedAppointment(int $id): Appointment
    {
        return auth()->user()->counsellorProfile->appointments()->findOrFail($id);
    }
};
?>

<div class="mx-auto w-full max-w-6xl py-4 sm:py-8">
    <p class="eyebrow">Assigned appointments</p><h1 class="mt-3 font-display text-3xl font-semibold text-ink">Your schedule</h1>
    @if (session('status')) <div class="mt-6 rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-900">{{ session('status') }}</div> @endif
    @error('appointment') <div class="mt-6 rounded-2xl bg-red-50 p-4 text-sm text-red-800">{{ $message }}</div> @enderror
    <div class="mt-8 grid gap-4">
        @forelse ($this->appointments as $appointment)
            <article class="soft-card">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                    <div><div class="flex flex-wrap gap-3"><h2 class="font-display text-xl font-semibold">{{ $appointment->client->name }}</h2><span class="rounded-full bg-bha-50 px-3 py-1 text-xs font-bold">{{ $appointment->status->label() }}</span></div><p class="mt-3 text-sm font-semibold">{{ $appointment->localStart()->format('D, j M Y · g:i a') }} WAT</p><p class="mt-1 text-sm text-zinc-600">{{ $appointment->service->name }} · {{ $appointment->session_mode->label() }}</p></div>
                    <div class="flex flex-wrap gap-2">@foreach ($appointment->status->allowedTransitions() as $status)<flux:button size="sm" :variant="$status === \App\Enums\AppointmentStatus::Cancelled ? 'danger' : 'filled'" wire:click="updateStatus({{ $appointment->id }}, '{{ $status->value }}')" wire:confirm="Update this appointment to {{ strtolower($status->label()) }}?">{{ $status->label() }}</flux:button>@endforeach</div>
                </div>
                @if ($appointment->session_mode === \App\Enums\SessionMode::Online && $appointment->status->reservesAvailability())
                    <div class="mt-5 border-t border-zinc-100 pt-5"><flux:textarea wire:model="meetingDetails.{{ $appointment->id }}" label="Online session details" placeholder="Secure meeting URL or brief joining instructions" rows="2" /><flux:button wire:click="saveMeetingDetails({{ $appointment->id }})" size="sm" class="mt-3">Save details</flux:button></div>
                @endif
            </article>
        @empty <div class="soft-card text-center text-sm text-zinc-600">No appointments are assigned to you.</div> @endforelse
    </div>
</div>
