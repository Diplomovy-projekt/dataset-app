<?php

namespace App\Utils;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FileUtil
{
    public static function deleteEmptyDirectories($directory)
    {
        if(!Storage::exists($directory)) {
            return;
        }

        $directories = Storage::directories($directory);

        foreach ($directories as $dir) {
            // Recursively delete empty directories inside the current directory
            self::deleteEmptyDirectories($dir);
        }

        // Check if the current directory is empty
        $files = Storage::files($directory);
        $subdirectories = Storage::directories($directory);

        // If no files or subdirectories, delete the directory
        if (empty($files) && empty($subdirectories)) {
            Storage::deleteDirectory($directory);
        }
    }

    public static function ensureFolderExists(string $path, bool $useStorage = false): void
    {
        $lastPart = basename($path);

        // If the last part contains a dot and isn't a directory starting with a dot, assume it's a file
        if (str_contains($lastPart, '.') && !str_starts_with($lastPart, '.')) {
            $folderPath = rtrim(dirname($path), '/');
        } else {
            $folderPath = rtrim($path, '/');
        }

        if ($useStorage) {
            if (!Storage::exists($folderPath)) {
                $absolutePath = Storage::path($folderPath);
                File::makeDirectory($absolutePath, 0777, true);
            }
        } else {
            if (!File::exists($folderPath)) {
                mkdir($folderPath, 0777, true);
                //File::makeDirectory($folderPath, 0777, true);
            }
        }
    }

    public static function addUniqueSuffix(string $filename, string $suffix = null): string
    {
        if(!$filename) {
            return '';
        }
        $pathInfo = pathinfo($filename);
        $baseName = $pathInfo['filename'];
        $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

        $uniqueSuffix = $suffix ?? uniqid('_da_', true);

        return $pathInfo['dirname'] . '/' . $baseName . $uniqueSuffix . $extension;
    }
}
