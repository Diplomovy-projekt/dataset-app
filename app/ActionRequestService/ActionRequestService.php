<?php

namespace App\ActionRequestService;

use App\ActionRequestService\Factory\ActionRequestFactory;
use App\ActionRequestService\Interfaces\ActionRequestHandlerInterface;
use App\Models\ActionRequest;
use App\Models\Dataset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ActionRequestService
{
    /**
     * Create an action request for a dataset
     *
     * @param Dataset $dataset
     * @param string $type One of: 'new', 'extend', 'edit_info', 'delete'
     * @param array $payload Additional data for the request
     * @return ActionRequest
     * @throws \InvalidArgumentException
     * @throws ValidationException
     */
    public function createRequest(string $type, array $payload = []): ActionRequest {
        // Validate request type
        if (!in_array($type, ['new', 'extend', 'edit', 'reduce', 'delete'])) {
            throw new \InvalidArgumentException("Invalid action request type: {$type}");
        }

        // Validate payload based on type
        $handler = ActionRequestFactory::createHandler($type);
        $payload = $handler->validatePayload($payload);

        $user = Auth::user();

        $request = ActionRequest::create([
            'user_id' => $user->id,
            'dataset_id' => $payload['dataset_id'],
            'type' => $type,
            'payload' => json_encode($payload),
            'status' => $user->isAdmin() ? 'approved' : 'pending',
            'reviewed_by' => $user->isAdmin() ? $user->id : null
        ]);

        if ($user->isAdmin()) {
            $this->reviewRequest($request, 'approved', 'Auto-approved by system');
        }

        return $request;
    }

    /**
     * Review an action request
     * Either approve or reject the request
     *
     * @param ActionRequest $request
     * @param string|null $comment
     * @return ActionRequest
     */
    public function reviewRequest(ActionRequest $request, string $status, ?string $comment = null): bool
    {
        $user = Auth::user();

        try {
            $handler = $this->getHandler($request);
            $method = $status === 'approved' ? 'approve' : 'reject';
            $payload = json_decode($request->payload, true);
            $handler->$method($payload);

            $request->status = $status;
            $request->reviewed_by = $user->id;
            $request->comment = $comment;
            $request->save();

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }


    /**
     * Get the handler for the given action request.
     *
     * @param ActionRequest $request
     * @return ActionRequestHandlerInterface
     */
    private function getHandler(ActionRequest $request): ActionRequestHandlerInterface
    {
        return ActionRequestFactory::createHandler($request->type);
    }
}
