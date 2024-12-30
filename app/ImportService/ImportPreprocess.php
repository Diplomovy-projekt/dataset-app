<?php

namespace App\ImportService;

namespace App\ImportService;

use App\ImportService\Factory\ImportComponentFactory;
use App\Utils\Response;

class ImportPreprocess
{
    private $zipValidator;
    private $annotationValidator;
    private $mapper;
    public $config;
    public function __construct(string $format) {
        $this->zipValidator = ImportComponentFactory::createValidator($format, 'zip');
        $this->annotationValidator = ImportComponentFactory::createValidator($format, 'annotation');
        $this->mapper = ImportComponentFactory::createMapper($format);
        $this->config = ImportComponentFactory::createConfig($format);
    }

    /**
     * Preprocess all steps: validate structure, validate annotations, and parse dataset.
     * Will return detailed information about any issues found.
     *
     * @param string $folderName
     * @param string $annotationTechnique
     * @return Response
     */
    public function preprocessDataset(string $folderName, string $annotationTechnique): Response
    {
        if ($this->zipValidator instanceof Response || $this->annotationValidator instanceof Response || $this->mapper instanceof Response) {
            return Response::error('Error creating import components');
        }
        // 1. Find structure errors
        $structureErrors = $this->zipValidator->validate($folderName);
        if (!$structureErrors->isSuccessful()) {
            return Response::error($structureErrors->message, data: $structureErrors->data);
        }

        // 2. Find annotation issues
        $annotationIssues = $this->annotationValidator->validate($folderName, $annotationTechnique);
        if (!$annotationIssues->isSuccessful()) {
            return Response::error($annotationIssues->message, data: $annotationIssues->data);
        }

        // 3. Parse the dataset
        $mappedData = $this->mapper->parse($folderName, $annotationTechnique);
        if (!$mappedData->isSuccessful()) {
            return Response::error($mappedData->message);
        }

        return Response::success(data: $mappedData->data);
    }
}

