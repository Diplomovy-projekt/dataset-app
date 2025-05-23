<?php

namespace App\ActionRequestService;

use App\ActionRequestService\Factory\ActionRequestFactory;
use App\Jobs\RecalculateDatasetStats;
use App\Mail\NewRequestMail;
use App\Mail\UserInvitationMail;
use App\Models\ActionRequest;
use App\Models\Dataset;
use App\Utils\Util;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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
     * @throws \Exception
     */
    public function createRequest(string $type, array $payload = []): mixed
    {
        try {
            if (!in_array($type, $this->requestTypes)) {
                throw new \Exception("Invalid action request type: {$type}");
            }

            // Validate payload based on type
            $handler = ActionRequestFactory::createHandler($type);
            $payload = $handler->validatePayload($payload);

            $user = Auth::user();

            $result = $this->sameRequestExists($type, $payload);
            if ($result) {
                throw new \Exception("A similar request already exists");
            }
            $request = ActionRequest::create([
                'user_id' => $user->id,
                'dataset_id' => $payload['dataset_id'],
                'type' => $type,
                'payload' => $payload,
            ]);

            if ($user->isAdmin()) {
                $resolveResponse = $this->resolveRequest($request, 'approve', 'Auto-approved by system');
                if (is_array($resolveResponse) && isset($resolveResponse['type']) && $resolveResponse['type'] === 'error') {
                    return $resolveResponse;
                }
            }

            if ($user->isAdmin()) {
                return $handler->adminResponse($request);
            } else {
                Mail::to(config('mail.admin_email'))->send(new NewRequestMail($request, $this->getReviewUrl($request)));
                return $handler->userResponse($request);
            }
        } catch (\Exception $e) {
            Util::logException($e, 'createRequest');
            return $handler->errorResponse($e->getMessage());
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
    public function resolveRequest(ActionRequest $request, string $status, ?string $comment = null): mixed
    {
        $user = Auth::user();

        try {
            if (!in_array($status, ['approve', 'reject'])) {
                throw new \InvalidArgumentException("Invalid status: {$status}");
            }
            $handler = $this->getHandler($request);
            $method = $status;
            $handler->$method($request->payload);

            $request->status = $status === 'approve' ? 'approved' : 'rejected';
            $request->reviewed_by = $user->id;
            $request->comment = $comment;
            $request->save();

            if($request != 'edit'){
                RecalculateDatasetStats::dispatch()->onQueue('statistics');
            }

            return $handler->resolveResponse($request);
        } catch (\Exception $e) {
            Util::logException($e, 'resolveRequest');
            return $handler->errorResponse($e->getMessage(), $request);
        }
    }

    public function reviewChanges(ActionRequest $request): mixed
    {
        $handler = $this->getHandler($request);
        return $handler->reviewChanges($request);
    }

    public function getReviewUrl(ActionRequest $request): string
    {
        $handler = $this->getHandler($request);
        return $handler->getReviewUrl($request);
    }

    private function getHandler(ActionRequest $request)
    {
        return ActionRequestFactory::createHandler($request->type);
    }

    private function sameRequestExists(string $type, array $payload): bool
    {
        return ActionRequest::where('type', $type)
            ->where('status', 'pending')
            ->where('payload', json_encode($payload))
            ->exists();
    }
}
