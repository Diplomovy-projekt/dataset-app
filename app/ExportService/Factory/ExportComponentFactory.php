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
        return self::instantiateClass(ucfirst(strtolower($format)), 'Config', 'config');
    }

    /**
     * @throws \Exception
     */
    protected static function instantiateClass(string $format, string $suffix, string $type): object
    {
        $namespace = self::$namespaces[$type];
        $className = "{$namespace}\\{$format}{$suffix}";

        if (!class_exists($className)) {
            throw new \Exception("Class $className not found");
        }

        return new $className();
    }
}
