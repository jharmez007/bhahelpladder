<?php

use App\Models\AvailabilityBreak;
use App\Models\AvailabilityRule;
use App\Models\BlockedPeriod;
use Carbon\CarbonImmutable;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public int $weekday = 1;
    public string $startsAt = '09:00';
    public string $endsAt = '17:00';
    public int $breakWeekday = 1;
    public string $breakStartsAt = '13:00';
    public string $breakEndsAt = '14:00';
    public string $blockedStartsAt = '';
    public string $blockedEndsAt = '';
    public string $blockedReason = '';

    #[Computed]
    public function profile()
    {
        return auth()->user()->counsellorProfile()->firstOrCreate([], ['is_active' => true]);
    }

    #[Computed]
    public function rules()
    {
        return $this->profile->availabilityRules()->orderBy('weekday')->orderBy('starts_at')->get();
    }

    #[Computed]
    public function breaks()
    {
        return $this->profile->availabilityBreaks()->orderBy('weekday')->orderBy('starts_at')->get();
    }

    #[Computed]
    public function blockedPeriods()
    {
        return $this->profile->blockedPeriods()->where('ends_at_utc', '>', now('UTC'))->orderBy('starts_at_utc')->get();
    }

    public function addRule(): void
    {
        $data = $this->validate([
            'weekday' => ['required', 'integer', 'between:0,6'],
            'startsAt' => ['required', 'date_format:H:i'],
            'endsAt' => ['required', 'date_format:H:i', 'after:startsAt'],
        ]);
        $this->profile->availabilityRules()->create([
            'weekday' => $data['weekday'], 'starts_at' => $data['startsAt'], 'ends_at' => $data['endsAt'],
        ]);
        unset($this->rules);
    }

    public function deleteRule(int $id): void
    {
        $this->profile->availabilityRules()->whereKey($id)->delete();
        unset($this->rules);
    }

    public function addBreak(): void
    {
        $data = $this->validate([
            'breakWeekday' => ['required', 'integer', 'between:0,6'],
            'breakStartsAt' => ['required', 'date_format:H:i'],
            'breakEndsAt' => ['required', 'date_format:H:i', 'after:breakStartsAt'],
        ]);
        $this->profile->availabilityBreaks()->create([
            'weekday' => $data['breakWeekday'], 'starts_at' => $data['breakStartsAt'], 'ends_at' => $data['breakEndsAt'],
        ]);
        unset($this->breaks);
    }

    public function deleteBreak(int $id): void
    {
        $this->profile->availabilityBreaks()->whereKey($id)->delete();
        unset($this->breaks);
    }

    public function addBlockedPeriod(): void
    {
        $data = $this->validate([
            'blockedStartsAt' => ['required', 'date'],
            'blockedEndsAt' => ['required', 'date', 'after:blockedStartsAt'],
            'blockedReason' => ['nullable', 'string', 'max:120'],
        ]);
        $timezone = config('booking.timezone');
        $this->profile->blockedPeriods()->create([
            'starts_at_utc' => CarbonImmutable::parse($data['blockedStartsAt'], $timezone)->utc(),
            'ends_at_utc' => CarbonImmutable::parse($data['blockedEndsAt'], $timezone)->utc(),
            'reason' => filled($data['blockedReason']) ? trim($data['blockedReason']) : null,
        ]);
        $this->reset('blockedStartsAt', 'blockedEndsAt', 'blockedReason');
        unset($this->blockedPeriods);
    }

    public function deleteBlockedPeriod(int $id): void
    {
        $this->profile->blockedPeriods()->whereKey($id)->delete();
        unset($this->blockedPeriods);
    }

    public function toggleActive(): void
    {
        $this->profile->update(['is_active' => ! $this->profile->is_active]);
        unset($this->profile);
    }

    public function dayName(int $day): string
    {
        return CarbonImmutable::create()->startOfWeek(CarbonImmutable::SUNDAY)->addDays($day)->format('l');
    }
};
?>

<div class="mx-auto w-full max-w-6xl py-4 sm:py-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="eyebrow">Availability</p><h1 class="mt-3 font-display text-3xl font-semibold text-ink">Manage your bookable time</h1><p class="mt-2 text-sm text-zinc-600">All entries are shown in West Africa Time.</p></div>
        <flux:button wire:click="toggleActive" :variant="$this->profile->is_active ? 'filled' : 'primary'">{{ $this->profile->is_active ? 'Pause new bookings' : 'Enable new bookings' }}</flux:button>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section class="soft-card">
            <h2 class="font-display text-xl font-semibold">Weekly working hours</h2>
            <form wire:submit="addRule" class="mt-5 grid gap-4 sm:grid-cols-4">
                <flux:select wire:model="weekday" label="Day">@foreach (range(0, 6) as $day)<flux:select.option value="{{ $day }}">{{ $this->dayName($day) }}</flux:select.option>@endforeach</flux:select>
                <flux:input wire:model="startsAt" type="time" label="Starts" />
                <flux:input wire:model="endsAt" type="time" label="Ends" />
                <div class="self-end"><flux:button type="submit" variant="primary">Add hours</flux:button></div>
            </form>
            <div class="mt-6 divide-y divide-zinc-100">@forelse ($this->rules as $rule)<div class="flex items-center justify-between py-3 text-sm"><span><b>{{ $this->dayName($rule->weekday) }}</b> · {{ substr($rule->starts_at, 0, 5) }}–{{ substr($rule->ends_at, 0, 5) }}</span><button wire:click="deleteRule({{ $rule->id }})" class="font-bold text-red-700">Remove</button></div>@empty<p class="py-4 text-sm text-zinc-500">No working hours configured.</p>@endforelse</div>
        </section>

        <section class="soft-card">
            <h2 class="font-display text-xl font-semibold">Recurring breaks</h2>
            <form wire:submit="addBreak" class="mt-5 grid gap-4 sm:grid-cols-4">
                <flux:select wire:model="breakWeekday" label="Day">@foreach (range(0, 6) as $day)<flux:select.option value="{{ $day }}">{{ $this->dayName($day) }}</flux:select.option>@endforeach</flux:select>
                <flux:input wire:model="breakStartsAt" type="time" label="Starts" />
                <flux:input wire:model="breakEndsAt" type="time" label="Ends" />
                <div class="self-end"><flux:button type="submit" variant="primary">Add break</flux:button></div>
            </form>
            <div class="mt-6 divide-y divide-zinc-100">@forelse ($this->breaks as $break)<div class="flex items-center justify-between py-3 text-sm"><span><b>{{ $this->dayName($break->weekday) }}</b> · {{ substr($break->starts_at, 0, 5) }}–{{ substr($break->ends_at, 0, 5) }}</span><button wire:click="deleteBreak({{ $break->id }})" class="font-bold text-red-700">Remove</button></div>@empty<p class="py-4 text-sm text-zinc-500">No recurring breaks configured.</p>@endforelse</div>
        </section>
    </div>

    <section class="soft-card mt-6">
        <h2 class="font-display text-xl font-semibold">Blocked dates and times</h2>
        <form wire:submit="addBlockedPeriod" class="mt-5 grid gap-4 md:grid-cols-4">
            <flux:input wire:model="blockedStartsAt" type="datetime-local" label="Starts" />
            <flux:input wire:model="blockedEndsAt" type="datetime-local" label="Ends" />
            <flux:input wire:model="blockedReason" label="Private label (optional)" maxlength="120" />
            <div class="self-end"><flux:button type="submit" variant="primary">Block time</flux:button></div>
        </form>
        <div class="mt-6 divide-y divide-zinc-100">@forelse ($this->blockedPeriods as $period)<div class="flex items-center justify-between gap-4 py-3 text-sm"><span><b>{{ $period->starts_at_utc->setTimezone(config('booking.timezone'))->format('D, j M · g:i a') }}</b> to {{ $period->ends_at_utc->setTimezone(config('booking.timezone'))->format('D, j M · g:i a') }} @if($period->reason)· {{ $period->reason }}@endif</span><button wire:click="deleteBlockedPeriod({{ $period->id }})" class="font-bold text-red-700">Remove</button></div>@empty<p class="py-4 text-sm text-zinc-500">No upcoming blocked periods.</p>@endforelse</div>
    </section>
</div>
