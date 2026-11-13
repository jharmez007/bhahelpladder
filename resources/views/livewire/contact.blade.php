<?php

use App\Models\Enquiry;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.public', [
    'title' => 'Contact',
    'description' => 'Send a brief general enquiry to BHA Help Ladder.',
])] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $message = '';
    public bool $submitted = false;

    public function submit(): void
    {
        $this->name = trim($this->name);
        $this->email = Str::lower(trim($this->email));
        $this->message = trim($this->message);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:1500'],
        ]);

        $key = 'enquiry:'.hash('sha256', request()->ip().'|'.$validated['email']);

        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages([
                'message' => 'Too many enquiries have been sent. Please wait '.RateLimiter::availableIn($key).' seconds and try again.',
            ]);
        }

        RateLimiter::increment($key, 600);

        Enquiry::query()->create($validated);

        $this->reset('name', 'email', 'message');
        $this->submitted = true;
    }
};
?>

<div>
    <section class="bg-bha-50">
        <div class="public-container grid gap-10 py-16 lg:grid-cols-[0.85fr_1.15fr] lg:py-24">
            <div class="max-w-xl">
                <p class="eyebrow">Contact BHA</p>
                <h1 class="display-title mt-4">Start with a simple conversation.</h1>
                <p class="mt-6 text-lg leading-8 text-zinc-600">Use this form for a brief general enquiry about counselling, school programs, community outreach or child protection advocacy.</p>

                <div class="mt-8 rounded-3xl border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-950">
                    <p class="font-bold">Please protect your privacy.</p>
                    <p class="mt-1">Do not include diagnoses, therapy notes, detailed health history or urgent safety information. This inbox is not monitored for emergencies.</p>
                </div>

                <div class="mt-8 border-l-2 border-bha-300 pl-5">
                    <p class="text-sm font-bold text-ink">Requests involving children</p>
                    <p class="mt-1 text-sm leading-6 text-zinc-600">A parent, guardian, educator or other responsible adult should make the initial enquiry while consent and safeguarding processes are being prepared.</p>
                </div>
            </div>

            <div class="soft-card">
                @if ($submitted)
                    <div role="status" class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-900">
                        <p class="font-bold">Your enquiry has been received.</p>
                        <p class="mt-1">A member of the BHA Help Ladder team can review it and respond using the email address you provided.</p>
                    </div>
                @endif

                <form wire:submit="submit" class="grid gap-6" novalidate>
                    <flux:input wire:model.blur="name" label="Name" autocomplete="name" required maxlength="120" />
                    <flux:input wire:model.blur="email" label="Email address" type="email" autocomplete="email" required maxlength="255" />
                    <flux:textarea wire:model.blur="message" label="Brief general enquiry" description="10–1,500 characters. Please avoid sensitive health details." rows="7" required maxlength="1500" />

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs leading-5 text-zinc-500">By submitting, you confirm this is a general enquiry.</p>
                        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="submit">
                            <span wire:loading.remove wire:target="submit">Send enquiry</span>
                            <span wire:loading wire:target="submit">Sending…</span>
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
