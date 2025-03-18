<?php

namespace App\Configs\Annotations;

class LabelmeConfig
{
    /*
     * Expected archive structure for Labelme:
     *
     * root_folder/
     * ├── images/
     * │   ├── image1.jpg
     * │   └── image2.jpg
     * └── labels/
     *     ├── annotation1.json
     *     └── annotation2.json
     */
    public const LABEL_EXTENSION = 'json';
    public const IMAGE_FOLDER = 'images';
    public const LABELS_FOLDER = 'labels';

}
