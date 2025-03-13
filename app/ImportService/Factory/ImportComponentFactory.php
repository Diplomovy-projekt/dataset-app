<?php

namespace App\ImportService\Factory;

use App\Utils\Response;

class ImportComponentFactory
{
    protected static array $namespaces = [
        'validator' => "App\\ImportService\\Validators",
        'mapper' => "App\\ImportService\\Mappers",
        'config' => "App\\Configs\\Annotations",
    ];

    public static function createValidator(string $format, string $type): object
    {
        return self::instantiateClass($format, ucfirst(strtolower($type)) . 'Validator', 'validator');
    }

    public static function createMapper(string $format): object
    {
        return self::instantiateClass('From' . ucfirst(strtolower($format)), '', 'mapper');
    }

    public static function createConfig(string $format): object
    {
        return self::instantiateClass($format, 'Config', 'config');
    }

    protected static function instantiateClass(string $format, string $suffix, string $type): object
    {
        $namespace = self::$namespaces[$type];

        if ($type === 'validator') {
            $className = "{$namespace}\\{$format}\\{$format}{$suffix}";
        } else {
            $className = "{$namespace}\\{$format}{$suffix}";
        }

        if (!class_exists($className)) {
            return Response::error(ucfirst($type) . " {$className} does not exist.");
        }

        return new $className();
    }

}
