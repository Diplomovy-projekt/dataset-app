<?php

namespace App\DatasetCrud;

use App\Models\Dataset;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;

class DatasetCrud
{

    public function deleteDataset($id): Response
    {
        try {
            $dataset = Dataset::find($id);
            $dataset->delete();
            if(Storage::disk('datasets')->exists($dataset->unique_name)) {
                Storage::disk('datasets')->deleteDirectory($dataset->unique_name);
            }
            return Response::success('Dataset deleted successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }
}
