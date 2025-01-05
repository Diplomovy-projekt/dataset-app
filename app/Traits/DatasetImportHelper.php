<?php

namespace App\Traits;

use App\FileManagement\ZipManager;
use App\ImportService\ImportService;
use App\ImportService\Strategies\NewDatasetStrategy;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait DatasetImportHelper
{
    public function normalizeErrors($errors): array
    {
        if (is_array($errors)) {
            return collect($errors)
                ->flatten()
                ->toArray();
        }

        return [$errors];
    }

    public function chunkUpload()
    {
        $chunkFileName = $this->fileChunk->getFileName();

        // Read the chunk file
        $buff = Storage::disk('private')->get('livewire-tmp/' . $chunkFileName);

        // Append chunk to final file
        $finalFilePath = 'livewire-tmp/' . $this->uniqueName;
        Storage::disk('private')->append($finalFilePath, $buff, null);

        // Delete the chunk file
        Storage::disk('private')->delete('livewire-tmp/' . $chunkFileName);

        // Check if the file is complete
        $curSize = Storage::disk('private')->size($finalFilePath);
        if ($curSize == $this->fileSize) {
            $this->finalFile = TemporaryUploadedFile::createFromLivewire('/' . $this->uniqueName);
            $this->finishImport(app(ZipManager::class));
        }
    }

}
