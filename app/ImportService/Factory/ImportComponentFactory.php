<?php

namespace App\ImportService\Factory;

use App\Utils\Response;

class ImportComponentFactory {
    public static function createValidator(string $format, string $type): object
    {
        // Convert format name to PascalCase
        $classBaseName = ucfirst(strtolower($format));
        $type = ucfirst(strtolower($type)) . 'Validator';

        // Determine the namespace based on the type
        $namespace = "App\\ImportService\\Validators";

        // Construct the full class name
        $className = "{$namespace}\\{$classBaseName}\\{$classBaseName}{$type}";

        // Check if the class exists
        if (!class_exists($className)) {
            return Response::error("Validator {$className} does not exist.");
        }

        return new $className();
    }

    public static function createMapper(string $format): object
    {
        // Convert format name to PascalCase
        $classBaseName = ucfirst(strtolower($format));

        // Determine the namespace for the mapper
        $namespace = "App\\ImportService\\Mappers";

        // Construct the full class name
        $className = "{$namespace}\\{$classBaseName}Mapper";

        // Check if the class exists
        if (!class_exists($className)) {
            return Response::error("Mapper {$className} does not exist.");
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
