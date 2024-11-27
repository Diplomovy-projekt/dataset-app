<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatasetProperty extends Model
{
    use HasFactory;
    protected $fillable = [
        'dataset_id',
        'property_value_id'
    ];

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function propertyValue(): BelongsTo
    {
        return $this->belongsTo(PropertyValue::class);
    }
}
