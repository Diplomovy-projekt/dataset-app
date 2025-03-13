<?php

namespace App\ActionRequestService\Handlers;

use App\ActionRequestService\Interfaces\ActionRequestHandlerInterface;
use App\DatasetActions\DatasetActions;
use App\Models\Dataset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NewDatasetHandler extends BaseHandler
{
    protected function validationRules(): array
    {
        return [
            'dataset_unique_name' => 'required|exists:datasets,unique_name',
            'child_unique_name' => 'required|exists:datasets,unique_name',
        ];
    }

    public function approve(array $payload): void
    {
        Dataset::where('id', $payload['dataset_id'])->update([
            'is_approved' => true,
            'is_public' => true,
        ]);

        $this->datasetActions->moveDatasetTo($payload['dataset_unique_name'], 'public');
    }

}
