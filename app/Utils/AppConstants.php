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
    public const IMG_THUMB_DIMENSIONS = 100; // 100x100 px
}
