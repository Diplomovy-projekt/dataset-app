<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnotationData extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_id',
        'annotation_category_id',
        'center_x',
        'center_y',
        'width',
        'height',
        'segmentation'
    ];

    protected $casts = [
        'segmentation' => 'json',
    ];

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AnnotationCategory::class, 'annotation_category_id');
    }
}
