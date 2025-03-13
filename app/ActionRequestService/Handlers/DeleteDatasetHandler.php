<?php

namespace App\ActionRequestService\Handlers;

use App\ActionRequestService\Interfaces\ActionRequestHandlerInterface;
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
        // TODO: Implement approve() method.
    }
}
