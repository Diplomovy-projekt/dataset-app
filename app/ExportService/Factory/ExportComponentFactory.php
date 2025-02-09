<?php

namespace App\ExportService\Factory;

use App\Utils\Response;

class ExportComponentFactory
{

    public static function createMapper(string $format): object
    {
        // Convert format name to PascalCase
        $classBaseName = ucfirst(strtolower($format));

        // Determine the namespace for the mapper
        $namespace = "App\\ExportService\\Mappers";

        // Construct the full class name
        $className = "{$namespace}\\To{$classBaseName}";

        // Check if the class exists
        if (!class_exists($className)) {
            return Response::error("Mappers {$className} does not exist.");
        }

        return new $className();
    }

    public static function createConfig(string $format): object
    {
        // Convert format name to PascalCase
        $classBaseName = ucfirst(strtolower($format));

        // Determine the namespace for the config
        $namespace = "App\\Configs\\Annotations";

        // Construct the full class name
        $className = "{$namespace}\\{$classBaseName}Config";

        // Check if the class exists
        if (!class_exists($className)) {
            return Response::error("Config {$className} does not exist.");
        }

        return new $className();
    }
}
