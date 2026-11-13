<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\SessionMode;
use Carbon\CarbonImmutable;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    protected $fillable = [];

    protected static function booted(): void
    {
        static::creating(function (self $appointment): void {
            $appointment->reference ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'status' => AppointmentStatus::class,
            'session_mode' => SessionMode::class,
            'starts_at_utc' => 'immutable_datetime',
            'ends_at_utc' => 'immutable_datetime',
            'buffer_starts_at_utc' => 'immutable_datetime',
            'buffer_ends_at_utc' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
            'rescheduled_at' => 'immutable_datetime',
            'online_meeting_details' => 'encrypted',
            'duration_minutes' => 'integer',
            'buffer_before_minutes' => 'integer',
            'buffer_after_minutes' => 'integer',
            'fee_minor' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function counsellorProfile(): BelongsTo
    {
        return $this->belongsTo(CounsellorProfile::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(AppointmentReminder::class);
    }

    public function localStart(): CarbonImmutable
    {
        return $this->starts_at_utc->setTimezone(config('booking.timezone'));
    }

    public function formattedFee(): ?string
    {
        return $this->fee_minor === null
            ? null
            : $this->currency.' '.number_format($this->fee_minor / 100, 2);
    }
}
