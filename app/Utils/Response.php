<?php

namespace App\Utils;

class Response
{
    public bool $success;
    public $message;
    public $data;

    public function __construct($success, $message = null, $data = null)
    {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
    }

    // Method to check if the operation was successful
    public function isSuccessful(): bool
    {
        return $this->success === true;
    }
    public function getMessage()
    {
        return $this->message;
    }

    public static function success($message = null, $data = null): Response
    {
        return new self(true, $message, $data);
    }

    public static function error($message = null, $data = null): Response
    {
        return new self(false, $message, $data);
    }

    public function getData()
    {
        return $this->data;
    }
}
