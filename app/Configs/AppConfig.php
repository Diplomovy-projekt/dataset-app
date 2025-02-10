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
            'extension' => 'txt',],
        'pascalvoc' => [
            'name' => 'Pascal Voc',
            'extension' => 'xml',],
        'coco' => [
            'name' => 'COCO',
            'extension' => 'json',],
    ];

    public const LIVEWIRE_TMP_PATH = 'app/private/livewire-tmp/';
    public const DATASETS_PATH = 'app/public/datasets/';
    public const LINK_DATASETS_PATH = 'storage/datasets/';
    public const MAX_THUMB_DIM = 256;
    public const FULL_IMG_FOLDER = 'full-images/';
    public const IMG_THUMB_FOLDER = 'thumbnails/';
    public const CLASS_IMG_FOLDER = 'class-images/';
    public const SAMPLES_COUNT = 3;
    public const UPLOAD_CHUNK_SIZE = [
        '1MB' => 1000000,
        '2MB' => 2000000,
        '5MB' => 5000000,
        '10MB' => 10000000,
        '20MB' => 20000000,
        '50MB' => 50000000,
        '100MB' => 100000000,
        '200MB' => 200000000,
    ];
    public const PLACEHOLDER_IMG = 'placeholder-image.png';
    public const PER_PAGE = 25;
    public const AUTH_ROLES = [
        'user' => 'User',
        'admin' => 'Admin',
    ];
}
