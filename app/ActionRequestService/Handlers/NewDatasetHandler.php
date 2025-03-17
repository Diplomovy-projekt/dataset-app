<?php

namespace App\ActionRequestService\Handlers;

use App\ActionRequestService\Interfaces\ActionRequestHandlerInterface;
use App\DatasetActions\DatasetActions;
use App\Models\ActionRequest;
use App\Models\Dataset;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;
class NewDatasetHandler extends BaseHandler
{
    protected function validationRules(): array
    {
        return [
            'dataset_unique_name' => 'required|exists:datasets,unique_name',
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

    public function reject(array $payload): void
    {
        $this->datasetActions->deleteDataset($payload['dataset_unique_name']);
    }

    public function reviewChanges(Model $request): mixed
    {
        $uniqueName = $request->dataset()->first()->unique_name;
        return Redirect::route('dataset.review.new', ['uniqueName' => $uniqueName, 'requestId' => $request->id]);
    }

    public function adminResponse(Model $request): mixed
    {
        return ['route' => 'dataset.show', 'params' => ['uniqueName' => $request->dataset->unique_name]];
    }
    public function errorResponse(string $errorMessage): mixed
    {
        return ['type' => 'error', 'message' => 'Failed to submit request: ' . $errorMessage];
    }

}
