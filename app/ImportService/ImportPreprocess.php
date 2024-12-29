<?php

namespace App\ImportService;

namespace App\ImportService;

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
        if (!empty($structureErrors)) {
            return Response::error('Zip structure errors found', data: $structureErrors);
        }

        // 2. Find annotation issues
        $annotationIssues = $this->annotationValidator->validate($folderName, $annotationTechnique);
        if (!empty($annotationIssues)) {
            return Response::error('Annotation issues found', data: $annotationIssues);
        }

        // 3. Parse the dataset
        $mappedData = $this->mapper->parse($folderName, $annotationTechnique);
        if (empty($mappedData)) {
            return Response::error('Parsing failed or no data found.');
        }

        return Response::success(data: $mappedData);
    }
}

