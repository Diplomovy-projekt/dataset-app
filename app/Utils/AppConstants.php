<?php

namespace App\Utils;

class AppConstants
{
    public const ANNOTATION_TECHNIQUES = [
        'BOUNDING_BOX' => 'Bounding Box',
        'POLYGON' => 'Polygon',
    ];
    public const SUPPORTED_FORMATS = ['Yolo', 'PascalVOC', 'COCO'];
    public const LIVEWIRE_TMP_PATH = 'app/private/livewire-tmp/';
    public const DATASETS_PATH = 'app/public/datasets/';
    public const IMG_THUMB_DIMENSIONS = 100; // 144x144 px
    public const FULL_IMG_FOLDER = 'full-images/';
    public const IMG_THUMB_FOLDER = 'thumbnails/';
    public const CLASS_IMG_FOLDER = 'class-images/';
}
