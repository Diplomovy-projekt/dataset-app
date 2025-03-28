<?php

namespace App\ActionRequestService\Handlers;

use App\ActionRequestService\Interfaces\ActionRequestHandlerInterface;
use App\Models\ActionRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteDatasetHandler extends BaseHandler
{
    protected function validationRules(): array
    {
        return [
            'dataset_unique_name' => 'required|exists:datasets,unique_name',
            'reason' => 'sometimes|string',
        ];
    }

    public function approve(array $payload): void
    {
        $this->datasetActions->deleteDataset($payload['dataset_unique_name']);
    }

    public function reject(array $payload): void
    {
        // Nothing needs to be done here.
    }

    public function reviewChanges(Model $request): mixed
    {
        $uniqueName = $request->dataset()->first()->unique_name;
        return Redirect::route('dataset.review.delete', ['uniqueName' => $uniqueName, 'requestId' => $request->id]);
    }

    public function adminResponse(Model $request): mixed
    {
        $currentRoute = URL::livewireCurrent(true);
        $adminDatasetRoute = route('admin.datasets');
        if($currentRoute === $adminDatasetRoute) {
            return ['action' => 'refreshComputed', 'type' => 'success', 'message' => 'Dataset deleted successfully'];
        }
        return ['route' => 'dataset.index'];
    }

    public function errorResponse(string $errorMessage, ActionRequest $request = null): mixed
    {
        return ['type' => 'error', 'message' => 'Failed to submit request: ' . $errorMessage];
    }
}
