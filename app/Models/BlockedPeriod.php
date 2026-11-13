<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedPeriod extends Model
{
    protected $fillable = ['counsellor_profile_id', 'starts_at_utc', 'ends_at_utc', 'reason'];

    protected function casts(): array
    {
        return [
            'starts_at_utc' => 'immutable_datetime',
            'ends_at_utc' => 'immutable_datetime',
        ];
    }

    public function counsellorProfile(): BelongsTo
    {
        return $this->belongsTo(CounsellorProfile::class);
    }
}
