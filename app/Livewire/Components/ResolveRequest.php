<?php

namespace App\Livewire\Components;

use App\ActionRequestService\ActionRequestService;
use App\Models\ActionRequest;
use App\Traits\LivewireActions;
use App\Utils\Util;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Redirect;
use Livewire\Attributes\On;
use Livewire\Component;

class ResolveRequest extends Component
{
    use LivewireActions;

    public array $request;
    public string $comment = '';

    public function render()
    {
        return view('livewire.components.resolve-request');
    }

    #[On('init-resolve-request')]
    public function init($requestId): void
    {
        $this->request = ActionRequest::
            with(['dataset:id,display_name', 'user:id,email'])
            ->find($requestId)->toArray();
    }

    public function resolveRequest(ActionRequestService $requestService, $status)
    {
        try {
            $request = ActionRequest::findOrFail($this->request['id']);
            $result = $requestService->resolveRequest($request, $status, $this->comment);
            $this->handleResponse($result);
        } catch (ModelNotFoundException $e) {
            Util::logException($e, 'resolveRequest in AdminDatasets');
            $this->dispatch('flash-msg', type: 'error', message: 'Request not found');
        }
    }
}
