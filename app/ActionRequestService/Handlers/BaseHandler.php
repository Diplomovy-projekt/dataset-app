<?php

namespace App\ActionRequestService\Handlers;

use App\DatasetActions\DatasetActions;
use App\Models\ActionRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;

abstract class BaseHandler
{
    protected DatasetActions $datasetActions;
    public function __construct()
    {
        $this->datasetActions = new DatasetActions();
    }

    /**
     * Define the base validation rules for the request.
     *
     * @return array
     */
    protected function baseValidationRules(): array
    {
        return [
            'dataset_id' => 'required|exists:datasets,id',
        ];
    }

    /**
     * Validates the payload against the defined rules.
     *
     * @param array $payload The data to validate.
     * @throws ValidationException If validation fails.
     * @return array The validated payload.
     */
    public function validatePayload(array $payload): array
    {
        $rules = array_merge($this->baseValidationRules(), $this->validationRules());

        $validator = Validator::make($payload, $rules);

        return $validator->validate();
    }

    /**
     * Abstract method to approve the request.
     *
     * @param array $payload The data related to the approval.
     * @return void
     */
    abstract public function approve(array $payload): void;

    /**
     * Abstract method to reject the request.
     *
     * @param array $payload The data related to the rejection.
     * @return void
     */
    abstract public function reject(array $payload): void;

    /**
     * Define additional validation rules specific to the handler.
     *
     * @return array The validation rules.
     */
    abstract protected function validationRules(): array;

    // Response methods
    /**
     * Abstract method for the admin's response to the request.
     *
     * @param Model $request The request model to respond to.
     * @return mixed The admin response.
     */
    abstract public function adminResponse(Model $request): mixed;

    /**
     * Provides the success response for the user after submitting a request.
     *
     * @param Model $request The request model.
     * @return array The success response.
     */
    public function userResponse(Model $request): mixed
    {
        return ['type' => 'success', 'message' => 'Request submitted successfully'];
    }

    /**
     * Provides an error response with a specific error message.
     *
     * @param string $errorMessage The error message to include in the response.
     * @param ActionRequest|null $request Optional action request object.
     * @return array The error response.
     */
    public function errorResponse(string $errorMessage, ActionRequest $request = null): mixed
    {
        return ['type' => 'error', 'message' => 'Failed to submit request: ' . $errorMessage];
    }

    /**
     * Resolves the response based on the current route and admin action.
     *
     * @param Model $request The request model.
     * @return mixed The resolved response, either a success message or a redirection.
     */
    public function resolveResponse(Model $request): mixed
    {
        $currentRoute = URL::livewireCurrent(true);
        $adminRoute = route('admin.datasets');

        // If the current route is the admin dataset page, return a success message
        if ($currentRoute === $adminRoute) {
            return ['action' => 'refreshComputed', 'type' => 'success', 'message' => 'Request resolved successfully'];
        }

        // Otherwise, redirect to the admin dataset route
        return ['route' => $adminRoute];
    }

    public function reviewChanges(Model $request): mixed
    {
        return Redirect::to($this->getReviewUrl($request));
    }
}
