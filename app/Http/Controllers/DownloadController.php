<?php

// DownloadController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DownloadController extends Controller
{
    public function downloadFile(Request $request)
    {
        $filepath = session('download_file_path');

        if (!$filepath || !file_exists($filepath)) {
            Log::error('Download failed: File not found', ['filepath' => $filepath]);
            abort(404, 'File not found');
        }

        $filename = basename($filepath);
        $filesize = filesize($filepath);
        $chunkSize = 1024 * 1024; // 1MB chunks

        // Clean output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . $filesize);

        $handle = fopen($filepath, 'rb');

        while (!feof($handle)) {
            $data = fread($handle, $chunkSize);
            echo $data;

            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
            gc_collect_cycles();
        }

        fclose($handle);

        session()->forget("download_progress_{$filename}");

        register_shutdown_function(function () use ($filepath) {
            if (file_exists($filepath)) {
                unlink($filepath);
                Log::info("File deleted after download: " . $filepath);
            }
        });

        exit;
    }

// Helper function to format bytes
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
