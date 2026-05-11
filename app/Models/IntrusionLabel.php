<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntrusionLabel extends Model
{
    protected $fillable = [
        'user_id',
        'intrusion_id',
        'outlier_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function intrusion(): BelongsTo
    {
        return $this->belongsTo(Intrusion::class);
    }
}
