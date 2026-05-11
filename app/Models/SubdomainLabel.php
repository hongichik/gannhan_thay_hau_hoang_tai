<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubdomainLabel extends Model
{
    protected $fillable = [
        'user_id',
        'subdomain_id',
        'label',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subdomain(): BelongsTo
    {
        return $this->belongsTo(Subdomain::class);
    }
}
