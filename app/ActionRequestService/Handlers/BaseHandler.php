<?php

namespace App\ActionRequestService\Handlers;

use App\ActionRequestService\Interfaces\ActionRequestHandlerInterface;
use App\DatasetActions\DatasetActions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;

abstract class BaseHandler implements ActionRequestHandlerInterface
{
    protected DatasetActions $datasetActions;
    public function __construct()
    {
        $this->datasetActions = new DatasetActions();
    }

    protected function baseValidationRules(): array
    {
        return [
            'dataset_id' => 'required|exists:datasets,id',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function validatePayload(array $payload): array
    {
        $rules = array_merge($this->baseValidationRules(), $this->validationRules());

        $validator = Validator::make($payload, $rules);

        return $validator->validate();
    }

    public function reject(array $payload): void
    {
        $this->datasetActions->deleteDataset($payload['dataset_id']);
    }

    abstract public function approve(array $payload): void;
    abstract protected function validationRules(): array;
    abstract public function reviewChanges(Model $request): mixed;
}
