<?php

namespace App\AnnotationHandler\Factory;

use App\Utils\Response;

class ImportHandlerFactory
{
    /**
     * Dynamically create a class instance for a specific format and type.
     *
     * @param string $format
     * @param string $type ('import', 'export', 'validate')
     * @return object
     * @throws \Exception
     */
    protected static function createInstance(string $format): object
    {
        // Convert format name to PascalCase
        $classBaseName = ucfirst(strtolower($format));
        // Determine the namespace based on the type
        $namespace = "App\\AnnotationHandler\\ImportHandlers";

        // Construct the full class name
        $className = "{$namespace}\\{$classBaseName}\\" . $classBaseName . "ImportHandler";

        if (!class_exists($className)) {
            return Response::error("Class {$className} does not exist.");
        }

        return new $className();

    }

    /**
     * Create an importer for a specific format.
     *
     * @param string $format
     * @return object
     * @throws \Exception
     */
    public static function create(string $format): object
    {
        return self::createInstance($format);
    }
}
