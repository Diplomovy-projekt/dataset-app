<?php

namespace App\ImportService\Validators\Labelme;

use App\Configs\Annotations\LabelmeConfig;
use App\Configs\Annotations\YoloConfig;
use App\Exceptions\DatasetImportException;
use App\ImportService\Validators\BaseValidator\BaseZipValidator;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;

class LabelmeZipValidator extends BaseZipValidator
{

    /**
     * @throws DatasetImportException
     */
    public function validateStructure(string $folderName): void
    {
        $filePath = $this->getPath($folderName);

        $this->validateImageOrganization($filePath, LabelmeConfig::IMAGE_FOLDER);
        $this->validateAnnotationOrganization($filePath, LabelmeConfig::LABEL_EXTENSION, LabelmeConfig::LABELS_FOLDER);

        if (!empty($errors)) {
            throw new DatasetImportException("Zip structure issues found", $errors);
        }
    }
}
