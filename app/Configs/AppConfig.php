<?php

namespace App\Configs;

class AppConfig
{
    public const ANNOTATION_TECHNIQUES = [
        'BOUNDING_BOX' => 'Bounding box',
        'POLYGON' => 'Polygon',
    ];
    public const SUPPORTED_FORMATS = ['Yolo', 'PascalVOC', 'COCO'];
    public const LIVEWIRE_TMP_PATH = 'app/private/livewire-tmp/';
    public const DATASETS_PATH = 'app/public/datasets/';
    public const MAX_THUMB_DIM = 256;
    public const FULL_IMG_FOLDER = 'full-images/';
    public const IMG_THUMB_FOLDER = 'thumbnails/';
    public const CLASS_IMG_FOLDER = 'class-images/';
    public const CLASS_SAMPLE_PREFIX = 'sample_';
}
