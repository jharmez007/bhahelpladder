<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'duration_minutes',
        'buffer_before_minutes',
        'buffer_after_minutes',
        'fee_minor',
        'currency',
        'modes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'buffer_before_minutes' => 'integer',
            'buffer_after_minutes' => 'integer',
            'fee_minor' => 'integer',
            'modes' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function counsellors(): BelongsToMany
    {
        return $this->belongsToMany(CounsellorProfile::class)->withTimestamps();
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function formattedFee(): ?string
    {
        if ($this->fee_minor === null) {
            return null;
        }

        return $this->currency.' '.number_format($this->fee_minor / 100, 2);
    }
}
