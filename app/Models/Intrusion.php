<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Intrusion extends Model
{
    protected $fillable = [
        'word_1',
        'word_2',
        'word_3',
        'word_4',
        'word_5',
        'word_6',
        'outlier_id',
    ];

    public function intrusionLabels(): HasMany
    {
        return $this->hasMany(IntrusionLabel::class);
    }
}
