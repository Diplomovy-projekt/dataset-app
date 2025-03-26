<?php

namespace App\Configs\Annotations;

class PascalvocConfig extends BaseAnnotationConfig
{
    /*
     * Expected archive structure for Pascal Voc:
     *
     * root_folder/
     * ├── image1.jpg
     * ├── image2.jpg
     * ├── image1.xml
     * ├── image2.xml
     */
    public const LABEL_EXTENSION = 'xml';

}
