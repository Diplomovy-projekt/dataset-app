<?php

namespace App\ImportService\Interfaces;

use App\Utils\Response;

interface DatasetSavingStrategyInterface {
    /**
     * Save dataset data to database
     *
     * @param array $mappedData The parsed dataset from import handler
     * @param array $data Original import request data
     * @return Response
     */
    public function saveToDatabase(array $mappedData, array $requestData): Response;

    /**
     * Handle rollback in case of failure
     * Should handle both database and file system rollback
     *
     * @param string $uniqueName Unique identifier/folder name for the dataset
     * @return void
     */
    public function handleRollback(array $requestData): void;
}
