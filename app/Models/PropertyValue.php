<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_type_id',
        'value'
    ];

    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function datasetProperties(): HasMany
    {
        return $this->hasMany(DatasetProperty::class);
    }
}
