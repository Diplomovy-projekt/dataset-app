<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Annotation extends Model
{
    protected $fillable = [
        'image_id',
        'class_id',
        'bbox_xmin',
        'bbox_ymin',
        'bbox_xmax',
        'bbox_ymax',
        'segmented',
        'segmentation'
    ];

    protected $casts = [
        'segmented' => 'boolean',
        'segmentation' => 'json',
    ];

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
