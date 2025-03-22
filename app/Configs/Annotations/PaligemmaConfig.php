<?php

namespace App\Configs\Annotations;

class PaligemmaConfig extends BaseAnnotationConfig
{
    /*
     * Expected archive structure for PaliGemma:
     *
     * root_folder/
     * ├── image1.jpg
     * ├── image2.jpg
     * ├── image1.xml
     * ├── image2.xml
     */
    public const LABEL_EXTENSION = 'jsonl';
    public const LABELS_FILE = '_annotations.jsonl';

}
