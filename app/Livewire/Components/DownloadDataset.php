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
    #[Locked]
    public bool $locked = false;
    protected $rules = [
        'exportFormat' => 'required|string',
        'token' => 'required|string',
    ];

    protected $messages = [
        'exportFormat.required' => 'Please select an export format.',
        'token.required' => 'Download token not set. Please wait for data preparation to complete and try again.',
    ];



    #[On('store-download-token')]
    public function storeDownloadToken($token)
    {
        $this->token = $token;
    }

    private function fetchImages()
    {
        if (empty($this->token)) {
            $this->failedDownload = [
                'message' => 'Download token not provided',
                'data' => null
            ];
            return [];
        }

        $serializedQuery = Cache::get("download_query_{$this->token}");

        if (!$serializedQuery) {
            $this->failedDownload = [
                'message' => 'Download request expired or invalid',
                'data' => null
            ];
            return [];
        }

        return \EloquentSerialize::unserialize($serializedQuery)->get()->toArray();
    }
    public function download()
    {
        $this->validate();
        if($this->locked) {
            return;
        }
        $this->locked = true;
        $images = $this->fetchImages();

        $response = ExportService::handleExport($images, $this->exportFormat);
        if(!$response->isSuccessful()) {
            $this->failedDownload = [
                'message' => $response->message,
                'data' => $response->data
            ];
            return;
        }
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
            $this->locked = false;
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
