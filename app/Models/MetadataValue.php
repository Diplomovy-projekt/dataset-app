<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetadataValue extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'metadata_type_id',
        'value'
    ];

    public function metadataType(): BelongsTo
    {
        return $this->belongsTo(MetadataType::class);
    }

    public function datasetMetadata(): HasMany
    {
        return $this->hasMany(DatasetMetadata::class);
    }
}
