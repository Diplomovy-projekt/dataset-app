<?php

namespace App\ActionRequestService\Handlers;

use App\ActionRequestService\Interfaces\ActionRequestHandlerInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EditDatasetHandler extends BaseHandler
{
    protected function validationRules(): array
    {
        return [
            'dataset_unique_name' => 'required|exists:datasets,unique_name',
            'display_name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'categories' => 'sometimes|array',
            'categories.*' => 'exists:categories,id',
            'metadata' => 'sometimes|array',
            'metadata.*' => 'exists:metadata_values,id',
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
