<?php

namespace App\ActionRequestService\Factory;

class ActionRequestFactory
{
    protected static array $namespaces = [
        'handler' => "App\\ActionRequestService\\Handlers",
    ];

    public static function createHandler(string $type): object
    {
        return self::instantiateClass($type, 'DatasetHandler', 'handler');
    }

    protected static function instantiateClass(string $type, string $suffix, string $category): object
    {
        $classBaseName = ucfirst(strtolower($type));
        $namespace = self::$namespaces[$category];
        $className = "{$namespace}\\{$classBaseName}{$suffix}";

        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Invalid {$category}: {$className}");
        }

        return new $className();
    }
}
