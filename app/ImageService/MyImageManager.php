<?php

namespace App\ImageService;

use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Vips\Driver as VipsDriver;

class MyImageManager
{
    // Get the appropriate image manager instance
    public static function getManager()
    {
        if (extension_loaded('vips')) {
            Log::channel('info_channel')->info("Using VipsDriver");
            return ImageManager::withDriver(VipsDriver::class);
        }

        // Check if Imagick is available
        if (extension_loaded('imagick')) {
            Log::channel('info_channel')->info("Using ImagickDriver");
            return ImageManager::imagick();
        }

        // Check if GD is available (default option)
        if (extension_loaded('gd')) {
            Log::channel('info_channel')->info("Using GdDriver");
            return ImageManager::gd();
        }

        // If no supported driver is available, throw an exception
        throw new \Exception("No suitable image driver (GD, Imagick, libvips) is available.");
    }

}
