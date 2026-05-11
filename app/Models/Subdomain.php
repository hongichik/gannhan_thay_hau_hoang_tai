<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subdomain extends Model
{
    protected $fillable = [
        'child',
        'parent',
        'label',
    ];

    public function subdomainLabels(): HasMany
    {
        return $this->hasMany(SubdomainLabel::class);
    }
}
