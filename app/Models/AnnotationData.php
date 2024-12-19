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
        'annotation_class_id',
        'x',
        'y',
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

    public function class(): BelongsTo
    {
        return $this->belongsTo(AnnotationClass::class, 'annotation_class_id');
    }
}
