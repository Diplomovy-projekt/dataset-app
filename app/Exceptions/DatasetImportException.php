<?php

namespace App\Exceptions;

class DatasetImportException extends \Exception
{
    protected array $data;

    /**
     * Constructor to initialize exception with a message, data, code, and optional previous exception.
     *
     * @param string $message Exception message
     * @param mixed $data Additional data related to the exception
     * @param int $code Exception code
     * @param \Exception|null $previous Previous exception for chaining
     */
    public function __construct(string $message = "", $data = [], int $code = 0, \Exception $previous = null)
    {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Retrieve additional data passed with the exception.
     *
     * @return mixed|null
     */
    public function getData()
    {
        return $this->data;
    }
}
