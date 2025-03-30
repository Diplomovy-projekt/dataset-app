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

        // Log initial memory usage
        $initialMemory = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);
        Log::info("Download started: Initial memory: " . $this->formatBytes($initialMemory) .
            ", Peak memory: " . $this->formatBytes($peakMemory) .
            ", File size: " . $this->formatBytes($filesize));

        // Clean output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers for download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . $filesize);

        // Read and output file in chunks
        $handle = fopen($filepath, 'rb');
        $downloadedBytes = 0;
        $lastLoggedPercent = 0;

        // Log memory usage right before entering the loop
        $preLoopMemory = memory_get_usage(true);
        Log::info("Memory usage before download loop: " . $this->formatBytes($preLoopMemory));

        while (!feof($handle)) {
            $data = fread($handle, $chunkSize);
            $chunk_size = strlen($data);
            $downloadedBytes += $chunk_size;
            echo $data;

            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            // Run garbage collection after each chunk
            gc_collect_cycles();

            // Calculate and log progress (log every 5% increment)
            $percent = floor(($downloadedBytes / $filesize) * 100);

            // Update session with current progress
            session(["download_progress_{$filename}" => $percent]);

            if ($percent >= $lastLoggedPercent + 5 || $percent == 100) {
                $currentMemory = memory_get_usage(true);
                $peakMemory = memory_get_peak_usage(true);

                Log::info("Download progress: {$percent}% complete" .
                    " | Downloaded: " . $this->formatBytes($downloadedBytes) .
                    " of " . $this->formatBytes($filesize) .
                    " | Current memory: " . $this->formatBytes($currentMemory) .
                    " | Peak memory: " . $this->formatBytes($peakMemory) .
                    " | Chunk size: " . $this->formatBytes($chunk_size));

                $lastLoggedPercent = $percent;
            }
        }

        fclose($handle);

        // Log final memory statistics
        $finalMemory = memory_get_usage(true);
        $finalPeakMemory = memory_get_peak_usage(true);
        Log::info("Download completed: Final memory: " . $this->formatBytes($finalMemory) .
            ", Peak memory: " . $this->formatBytes($finalPeakMemory));

        // Clean up session
        session()->forget("download_progress_{$filename}");

        // Optional: Register a shutdown function to delete the file after download
        register_shutdown_function(function () use ($filepath) {
            if (file_exists($filepath)) {
                unlink($filepath);
                Log::info("File deleted after download: " . $filepath);
            }
        });

        // Final memory usage after cleanup
        Log::info("Final memory usage after cleanup: " .
            $this->formatBytes(memory_get_usage(true)));

        exit;
    }

    public function getDownloadProgress(Request $request)
    {
        $filename = $request->input('filename');
        $progress = session("download_progress_{$filename}", null);

        if ($progress === null) {
            $progress = 100;
        }

        return response()->json(['progress' => $progress]);
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
