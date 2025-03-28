<?php

namespace App\ActionRequestService\Handlers;

use App\ActionRequestService\Interfaces\ActionRequestHandlerInterface;
use App\DatasetActions\DatasetActions;
use App\Models\ActionRequest;
use Illuminate\Support\Facades\URL;
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

    abstract public function approve(array $payload): void;
    abstract public function reject(array $payload): void;
    abstract protected function validationRules(): array;
    abstract public function reviewChanges(Model $request): mixed;


    // Response methods
    abstract public function adminResponse(Model $request): mixed;

    public function userResponse(Model $request): mixed
    {
        return ['type' => 'success', 'message' => 'Request submitted successfully'];
    }

    public function errorResponse(string $errorMessage, ActionRequest $request = null): mixed
    {
        return ['type' => 'error', 'message' => 'Failed to submit request: ' . $errorMessage];
    }

    public function resolveResponse(Model $request): mixed
    {
        $currentRoute = URL::livewireCurrent(true);
        $adminRoute = route('admin.datasets');
        if($currentRoute === $adminRoute) {
            return ['action' => 'refreshComputed', 'type' => 'success', 'message' => 'Request resolved successfully'];
        }
        return ['route' => $adminRoute];
    }
}
