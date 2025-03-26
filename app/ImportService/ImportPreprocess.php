<?php

namespace App\ImportService;

namespace App\ImportService;

use App\Exceptions\DataException;
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
     * @throws DataException
     */
    public function preprocessDataset(string $folderName, string $annotationTechnique): Response
    {
        if ($this->zipValidator instanceof Response || $this->annotationValidator instanceof Response || $this->mapper instanceof Response) {
            throw new DataException("Invalid format");
        }
        // 1. Find structure errors
        $this->zipValidator->validateStructure($folderName);

        // 2. Find annotation issues
        $annotationIssues = $this->annotationValidator->validateAnnotationData($folderName, $annotationTechnique);
        if (!$annotationIssues->isSuccessful()) {
            throw new DataException($annotationIssues->message, $annotationIssues->data);
        }

        // 3. Parse the dataset
        $mappedData = $this->mapper->parse($folderName, $annotationTechnique);
        if (!$mappedData->isSuccessful()) {
            throw new DataException($mappedData->message);
        }

        return Response::success(data: $mappedData->data);
    }
}

