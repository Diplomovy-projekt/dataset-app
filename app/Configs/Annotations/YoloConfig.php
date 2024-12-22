<?php

namespace App\Configs\Annotations;

trait YoloConfig
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
     *     ├── annotation1.txt
     *     └── annotation2.txt
     */
    const DATA_YAML = 'data.yaml';
    const TXT_EXTENSION = 'txt';
    const IMAGE_FOLDER = 'images';
    const LABELS_FOLDER = 'labels';
}
