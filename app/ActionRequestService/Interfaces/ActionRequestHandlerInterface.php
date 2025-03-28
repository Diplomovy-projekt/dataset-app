<?php

namespace App\ActionRequestService\Interfaces;

use App\Models\ActionRequest;
use Illuminate\Database\Eloquent\Model;

interface ActionRequestHandlerInterface
{
    // Request handling methods
    public function validatePayload(array $payload): array;
    public function reviewChanges(Model $request): mixed;
    public function approve(array $payload): void;
    public function reject(array $payload): void;

    // Response methods
    public function adminResponse(Model $request): mixed;
    public function userResponse(Model $request): mixed;
    public function errorResponse(string $errorMessage, ?ActionRequest $request): mixed;

}
