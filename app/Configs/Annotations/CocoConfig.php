<?php

namespace App\Configs\Annotations;

use Faker\Provider\Base;

class CocoConfig extends BaseAnnotationConfig
{
    /*
     * Expected archive structure for YOLO:
     *
     * root_folder/
     * ├── images/
     * │   ├── image1.jpg
     * │   └── image2.jpg
     * └── _annotations.coco.json
     */
    public const LABEL_EXTENSION = 'json';
    public const IMAGE_FOLDER = 'images';
    public const LABELS_FILE = '_annotations.coco.json';

}
