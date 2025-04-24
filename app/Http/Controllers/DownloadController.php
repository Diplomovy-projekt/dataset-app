<?php

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

        session()->forget("download_file_path");

        if (file_exists($filepath)) {
            unlink($filepath);
            Log::info("File deleted after download: " . $filepath);
        }

        //exit;
        return response()->json([], 200);
    }
}
