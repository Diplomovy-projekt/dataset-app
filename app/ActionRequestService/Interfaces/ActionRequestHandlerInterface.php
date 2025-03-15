<?php

namespace App\ActionRequestService\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface ActionRequestHandlerInterface
{
    public function validatePayload(array $payload): array;
    public function reviewChanges(Model $request): mixed;
    public function approve(array $payload): void;
    public function reject(array $payload): void;
}
