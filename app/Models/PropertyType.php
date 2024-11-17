<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyType extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    public function propertyValues(): HasMany
    {
        return $this->hasMany(PropertyValue::class);
    }

    public function datasetProperties(): HasMany
    {
        return $this->hasMany(DatasetProperty::class);
    }
}
