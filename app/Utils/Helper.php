<?php

namespace App\Utils;

class Helper
{
    public static function isJson($data) {

        if (!is_string($data)) {
            return false;
        }
        json_decode($data);

        return json_last_error() === JSON_ERROR_NONE;
    }

}
