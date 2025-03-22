<?php

namespace App\Configs;

class AppConfig
{
    public const ANNOTATION_TECHNIQUES = [
        'POLYGON' => 'Polygon',
        'BOUNDING_BOX' => 'Bounding box',
    ];
    public const ANNOTATION_FORMATS_INFO = [
        'yolo' => [
            'name' => 'YOLO',
            'extension' => 'txt',
            ],
        'labelme' => [
            'name' => 'Labelme',
            'extension' => 'json',
            ],
        'coco' => [
            'name' => 'COCO',
            'extension' => 'json',
            ],
        'pascalvoc' => [
            'name' => 'PascalVOC',
            'extension' => 'xml',
            ],
        'paligemma' => [
            'name' => 'PaliGemma',
            'extension' => 'jsonl',
            ],
    ];

    public const LIVEWIRE_TMP_PATH = 'app/private/livewire-tmp/';
    public const array DATASETS_PATH = [
        'public' => 'app/public/datasets/',
        'private' => 'app/private/datasets/',
    ];
    public const LINK_DATASETS_PATH = 'storage/datasets/';
    public const MAX_THUMB_DIM = 256;
    public const FULL_IMG_FOLDER = 'full-images/';
    public const IMG_THUMB_FOLDER = 'thumbnails/';
    public const CLASS_IMG_FOLDER = 'class-images/';
    public const SAMPLES_COUNT = 1;
    public const array MB_SIZE_LOOKUP = [
        '1MB' => 1000000,
        '2MB' => 2000000,
        '5MB' => 5000000,
        '10MB' => 10000000,
        '20MB' => 20000000,
        '50MB' => 50000000,
        '100MB' => 100000000,
        '200MB' => 200000000,
    ];
    public const int UPLOAD_CHUNK_SIZE = self::MB_SIZE_LOOKUP['20MB'];
    public const int DOWNLOAD_CHUNK_SIZE = self::MB_SIZE_LOOKUP['100MB'];
    public const PLACEHOLDER_IMG = 'placeholder-image.png';
    public const PER_PAGE_OPTIONS = [
        "10" => 10,
        "25" => 25,
        "50" => 50,
    ];
    public const AUTH_ROLES = [
        'user' => 'User',
        'admin' => 'Admin',
    ];

    // TODO increase expiration date for url invite
    public const EXPIRATION = [
        'URL' => ['value' => 5, 'unit' => 'seconds'],
        'TMP_FILE' => ['value' => 6, 'unit' => 'hours'],
    ];
}
