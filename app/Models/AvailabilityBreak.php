<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityBreak extends Model
{
    protected $fillable = ['counsellor_profile_id', 'weekday', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return ['weekday' => 'integer'];
    }

    public function counsellorProfile(): BelongsTo
    {
        return $this->belongsTo(CounsellorProfile::class);
    }
}
