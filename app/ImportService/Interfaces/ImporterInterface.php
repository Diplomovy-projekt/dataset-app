<?php

namespace App\ImportService\Interfaces;

interface ImporterInterface
{
/**
* Validate the input data
* @param array $data
* @return bool
*/
public function validate(array $data);

/**
* Convert input data to internal format
* @param array $data
* @return array
*/
public function convert(string $datasetPath);
}
