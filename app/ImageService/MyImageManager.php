<?php

namespace App\ImageService;

use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Vips\Driver as VipsDriver;

class MyImageManager
{
    private static ?ImageManager $instance = null;

    private static array $preferredOrder = [
        'createVipsManager',
        'createGdManager',
        'createImagickManager',
    ];

    /**
     * @throws \Exception
     */
    public static function getManager(): ImageManager
    {
        if (!self::$instance) {
            self::$instance = self::detectDriver();
        }
        return self::$instance;
    }

    private static function detectDriver(): ImageManager
    {
        foreach (self::$preferredOrder as $method) {
            if (method_exists(self::class, $method)) {
                $manager = self::$method();
                if ($manager instanceof ImageManager) {
                    return $manager;
                }
            }
        }

        throw new \Exception("No suitable image driver is available.");
    }

    private static function createVipsManager(): ?ImageManager
    {
        if (extension_loaded('vips')) {
            return ImageManager::withDriver(VipsDriver::class);
        }
        return null;
    }

    private static function createImagickManager(): ?ImageManager
    {
        if (extension_loaded('imagick')) {
            return ImageManager::imagick();
        }
        return null;
    }

    private static function createGdManager(): ?ImageManager
    {
        if (extension_loaded('gd')) {
            return ImageManager::gd();
        }
        return null;
    }
}
