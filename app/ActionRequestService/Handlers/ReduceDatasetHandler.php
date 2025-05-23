<?php

namespace App\ActionRequestService\Handlers;

use App\ActionRequestService\Interfaces\ActionRequestHandlerInterface;
use App\Models\ActionRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;
class ReduceDatasetHandler extends BaseHandler
{
    protected function validationRules(): array
    {
        return [
            'dataset_unique_name' => 'required|exists:datasets,unique_name',
            'image_ids' => 'required|array',
            'image_ids.*' => 'exists:images,id',
            'reason' => 'sometimes|string',
        ];
    }

    public function approve(array $payload): void
    {
        $this->datasetActions->deleteImages($payload['dataset_unique_name'], $payload['image_ids']);
    }

    public function reject(array $payload): void
    {
        // Nothing needs to be done here.
    }

    public function getReviewUrl(Model $request): string
    {
        return route('dataset.review.reduce', [
            'requestId' => $request->id,
        ]);
    }

    public function adminResponse(Model $request): mixed
    {
        return ['action' => 'refreshComputed', 'type' => 'success', 'message' => 'Images removed successfully'];
    }
    public function errorResponse(string $errorMessage, ActionRequest $request = null): mixed
    {
        return ['type' => 'error', 'message' => 'Failed to submit request: ' . $errorMessage];
    }
}
