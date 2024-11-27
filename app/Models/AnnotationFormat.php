<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnotationFormat extends Model
{
    /** @use HasFactory<\Database\Factories\AnnotationFormatFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'extension'
    ];
}
