<?php

namespace App\ImportService\Validators\BaseValidator;

use App\Configs\Annotations\YoloConfig;
use Illuminate\Support\Facades\Storage;

class BaseZipValidator
{
    public function validateImageFolder(string $filePath, string $imageFolder): array|bool
    {
        $imagesPath = $filePath . '/' . $imageFolder;
        $images = collect(Storage::files($imagesPath));

        // Filter out invalid image files
        $invalidImages = $images->filter(function ($image) {
            return !in_array(pathinfo($image, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png']);
        });

        if ($invalidImages->isNotEmpty()) {
            return ["Invalid image files found" => $invalidImages->toArray()];
        }

        return true;
    }

}
