<?php

namespace App\ActionRequestService\Interfaces;

interface ActionRequestHandlerInterface
{
    public function validatePayload(array $payload): array;
    public function approve(array $payload): void;
    public function reject(array $payload): void;
}
