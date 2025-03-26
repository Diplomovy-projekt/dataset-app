<?php

namespace App\Configs\Annotations;

class YoloConfig extends BaseAnnotationConfig
{
    /*
     * Expected archive structure for YOLO:
     *
     * root_folder/
     * ├── data.yaml
     * ├── images/
     * │   ├── image1.jpg
     * │   └── image2.jpg
     * └── labels/
     *     ├── image1.txt
     *     └── image2.txt
     */
    public const DATA_YAML = 'data.yaml';
    public const LABEL_EXTENSION = 'txt';
    public const IMAGE_FOLDER = 'images';
    public const LABELS_FOLDER = 'labels';

}
