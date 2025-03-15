<?php

namespace App\ActionRequestService;

use App\ActionRequestService\Factory\ActionRequestFactory;
use App\ActionRequestService\Interfaces\ActionRequestHandlerInterface;
use App\Models\ActionRequest;
use App\Models\Dataset;
use App\Utils\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ActionRequestService
{
    private $requestTypes = ['new', 'extend', 'edit', 'reduce', 'delete'];
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
    public function createRequest(string $type, array $payload = []): Response
    {
        try {
            if (!in_array($type, $this->requestTypes)) {
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
                'reviewed_by' => $user->isAdmin() ? $user->id : null
            ]);

            if (!$user->isAdmin()) {
                $this->resolveRequest($request, 'approved', 'Auto-approved by system');
            }

            return Response::success(data: ['isAdmin' => $user->isAdmin()]);
        } catch (\Throwable $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * Review an action request
     * Either approve or reject the request
     *
     * @param ActionRequest $request
     * @param string|null $comment
     * @return ActionRequest
     */
    public function resolveRequest(ActionRequest $request, string $status, ?string $comment = null): Response
    {
        $user = Auth::user();

        try {
            if(!in_array($status, ['approve', 'reject'])) {
                throw new \InvalidArgumentException("Invalid status: {$status}");
            }
            $handler = $this->getHandler($request);
            $payload = json_decode($request->payload, true);
            $method = $status;
            $handler->$method($payload);

            $request->status = $status === 'approve' ? 'approved' : 'rejected';
            $request->reviewed_by = $user->id;
            $request->comment = $comment;
            $request->save();

            return Response::success();
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    public function reviewChanges(ActionRequest $request): mixed
    {
        $handler = $this->getHandler($request);
        return $handler->reviewChanges($request);
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
