<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\Configs\TableDefinition;
use App\Models\ActionRequest;
use App\Models\Scopes\DatasetVisibilityScope;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class MyRequests extends Component
{
    use WithPagination, WithoutUrlPagination;
    public array $tableIds = ['my-requests-pending', 'my-requests-resolved'];
    public array $tables = [];
    #[Computed]
    public function paginatedMyRequestsPending()
    {
        return $this->requestsQuery(['pending'], 'my-requests-pending', [auth()->id()]);
    }

    #[Computed]
    public function paginatedMyRequestsResolved()
    {
        return $this->requestsQuery(['approved', 'rejected'], 'my-requests-resolved', [auth()->id()]);
    }

    public function mount()
    {
        foreach($this->tableIds as $tableId) {
            $this->tables[$tableId] = TableDefinition::get($tableId);
        }
    }
    public function render()
    {
        return view('livewire.full-pages.my-requests');
    }

    public function sortBy($tableId, $column)
    {
        if ($this->tables[$tableId]['sortColumn'] === $column) {
            $this->tables[$tableId]['sortDirection'] = $this->tables[$tableId]['sortDirection'] === 'asc' ? 'desc' : 'asc';
        } else {
            // Set new column and default to ascending
            $this->tables[$tableId]['sortColumn'] = $column;
            $this->tables[$tableId]['sortDirection'] = 'asc';
        }
        $this->resetPage();
    }

    public function cancelRequest($id)
    {
        try {
            Gate::authorize('cancel-request', $id);
            $request = ActionRequest::findOrFail($id);
            if($request->status === 'pending')
            {
                $request->delete();
                $this->dispatch('flash-msg', type: 'success', message: 'Request has been cancelled.');
            } else {
                $this->dispatch('flash-msg', type: 'error', message: 'Request cannot be cancelled because its not pending');
            }
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'Request not found');
        }
    }

    private function requestsQuery($status, $tableName, $user_ids = [])
    {
        return ActionRequest::query()
            ->select('action_requests.id', 'action_requests.user_id', 'action_requests.dataset_id',
                'action_requests.status', 'action_requests.reviewed_by', 'action_requests.created_at',
                'action_requests.type', 'action_requests.comment')
            ->with([
                'dataset' => function ($query) {
                    $query->withoutGlobalScope(DatasetVisibilityScope::class)
                        ->select('id', 'display_name', 'unique_name');
                },
                'user:id,email',
                'reviewer:id,email'
            ])
            ->leftJoin('users as creators', 'action_requests.user_id', '=', 'creators.id')
            ->leftJoin('users as reviewers', 'action_requests.reviewed_by', '=', 'reviewers.id')
            ->leftJoin('datasets', 'action_requests.dataset_id', '=', 'datasets.id')
            ->whereIn('status', $status)
            ->when($user_ids, function ($query) use ($user_ids) {
                $query->whereIn('action_requests.user_id', $user_ids);
            })
            ->when($this->tables[$tableName]['sortColumn'] === 'user.email', function ($query) use ($tableName) {
                $query->orderBy('users.email', $this->tables[$tableName]['sortDirection']);
            })
            ->when($this->tables[$tableName]['sortColumn'] === 'reviewers.email', function ($query) use ($tableName) {
                $query->orderBy('reviewers.email', $this->tables[$tableName]['sortDirection']);
            })
            ->when($this->tables[$tableName]['sortColumn'] === 'dataset.display_name', function ($query) use ($tableName) {
                $query->orderBy('datasets.display_name', $this->tables[$tableName]['sortDirection']);
            })
            ->when(!in_array($this->tables[$tableName]['sortColumn'], ['user.email', 'dataset.display_name']), function ($query) use ($tableName) {
                $query->orderBy($this->tables[$tableName]['sortColumn'], $this->tables[$tableName]['sortDirection']);
            })
            ->paginate(AppConfig::PER_PAGE_OPTIONS['10'], pageName: $tableName);
    }
}
