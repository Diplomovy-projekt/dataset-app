<?php

namespace App\Livewire\Components;

use App\ExportService\ExportService;
use App\Models\Dataset;
use App\Models\Image;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

class DownloadDataset extends Component
{
    public string $exportDataset = '';
    public string $filePath;
    public mixed $progress;
    public  $failedDownload = null;
    public string $exportFormat = '';
    public string $token = '';


    #[On('store-download-token')]
    public function storeDownloadToken($token)
    {
        $this->token = $token;
    }

    private function fetchImages()
    {
        $serializedQuery = Cache::get("download_query_{$this->token}");

        if (!$serializedQuery) {
            abort(404, 'Download request expired or invalid.');
        }
        return \EloquentSerialize::unserialize($serializedQuery)->get()->toArray();
    }
    public function download()
    {
        $images = $this->fetchImages();

        $response = ExportService::handleExport($images, $this->exportFormat);
        $this->exportDataset = $response->data['datasetFolder'];
        $this->filePath = storage_path("app/public/datasets/{$this->exportDataset}");

        if (!file_exists($this->filePath)) {
            abort(404, "File not found.");
        }

        $fileSize = filesize($this->filePath);
        $chunkSize = 1024 * 1024; // 1MB per chunk
        $bytesSent = 0;

        $this->progress = 0; // Reset progress when starting the download

        return response()->stream(function () use ($chunkSize, &$bytesSent, $fileSize) {
            $handle = fopen($this->filePath, 'rb');

            while (!feof($handle)) {
                $chunk = fread($handle, $chunkSize);
                echo $chunk;
                flush();

                $bytesSent += strlen($chunk); // Track actual bytes read
                $this->progress = round(($bytesSent / $fileSize) * 100, 2);
                session()->put("download_progress_{$this->exportDataset}", $this->progress);
            }

            fclose($handle);
            session()->forget("download_progress_{$this->exportDataset}");
        }, 200, [
            "Content-Type" => "application/zip",
            "Content-Length" => $fileSize,
            "Content-Disposition" => "attachment; filename=\"{$this->exportDataset}\"",
            "Cache-Control" => "no-cache",
            "Connection" => "keep-alive",
        ]);
    }

    public function updateProgress()
    {
        $progress = session()->get("download_progress_{$this->exportDataset}", 0);

        if ($progress < 100) {
            $this->progress = $progress;
        } else {
            $this->progress = 100;
        }
    }
}
