<?php

namespace App\ImportService\Interfaces;

interface ExporterInterface
{
/**
* Validate the data before export
* @param array $data
* @return bool
*/
public function validate(array $data): bool;

/**
* Convert internal format to target format
* @param array $data
* @return array
*/
public function convert(array $data): array;
}
