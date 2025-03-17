<?php

namespace App\ExportService\Factory;

use App\Utils\Response;

class ExportComponentFactory
{
    protected static array $namespaces = [
        'mapper' => "App\\ExportService\\Mappers",
        'config' => "App\\Configs\\Annotations",
    ];

    public static function createMapper(string $format): object
    {
        return self::instantiateClass('To' . ucfirst(strtolower($format)), '', 'mapper');
    }

    public static function createConfig(string $format): object
    {
        return self::instantiateClass($format, 'Config', 'config');
    }

    protected static function instantiateClass(string $format, string $suffix, string $type): object
    {
        $classBaseName = ucfirst(strtolower($format));
        $namespace = self::$namespaces[$type];
        $className = "{$namespace}\\{$classBaseName}{$suffix}";

        if (!class_exists($className)) {
            return Response::error(ucfirst($type) . " {$className} does not exist.");
        }

        return new $className();
    }
}
