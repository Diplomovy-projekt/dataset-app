<?php

namespace App\AnnotationHandler\Interfaces;

interface ValidatorInterface
{
    /**
     * Validate the structure of the archive (zip/rar, etc.)
     *
     * @param string $archiveDir The path to the archive file
     * @return bool|string Returns true if valid, or an error message if invalid
     */
    public function validateArchiveStructure(string $archiveDir);

    /**
     * Validate the schema/format of annotations (e.g., YOLO, COCO)
     *
     * @param string $annotationsPath The path to the annotations directory
     * @return bool|string Returns true if valid, or an error message if invalid
     */
    public function validateAnnotationFormat(string $annotationsPath);
}
