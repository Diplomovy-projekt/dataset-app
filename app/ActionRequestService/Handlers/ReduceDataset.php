<?php

namespace App\ActionRequestService\Handlers;

use App\ActionRequestService\Interfaces\ActionRequestHandlerInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReduceDataset extends BaseHandler
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
        // TODO: Implement approve() method.
    }

    public function reject(array $payload): void
    {
        // TODO: Implement reject() method.
    }
}
