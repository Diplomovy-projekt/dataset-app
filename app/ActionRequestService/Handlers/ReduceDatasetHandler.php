<?php

namespace App\ActionRequestService\Handlers;

use App\ActionRequestService\Interfaces\ActionRequestHandlerInterface;
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

    public function reviewChanges(Model $request): mixed
    {
        return Redirect::route('dataset.review.reduce', ['requestId' => $request->id]);
    }
}
