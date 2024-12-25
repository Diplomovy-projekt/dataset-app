<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatasetMetadata extends Model
{
    use HasFactory;
    protected $fillable = [
        'dataset_id',
        'metadata_value_id'
    ];

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function metadataValue(): BelongsTo
    {
        return $this->belongsTo(MetadataValue::class);
    }
}
