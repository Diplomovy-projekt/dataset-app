<?php

namespace App\Livewire\FullPages;

use App\ActionRequestService\ActionRequestService;
use App\Configs\AppConfig;
use App\Configs\TableDefinition;
use App\DatasetActions\DatasetActions;
use App\Models\ActionRequest;
use App\Models\Dataset;
use App\Models\DatasetStatistics;
use App\Models\Image;
use App\Models\Scopes\DatasetVisibilityScope;
use App\Models\User;
use App\Traits\LivewireActions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
class AdminDatasets extends Component
{
    use WithPagination, LivewireActions, WithoutUrlPagination;
    private array $tableIds = ['dataset-overview', 'pending-requests', 'resolved-requests'];
    public array $tables = [];

    public array $datasets;
    public array $users = [];
    public string $userSearchTerm = '';
    #[Computed]
    public function paginatedDatasetOverview()
    {
        return Dataset::query()
            ->approved()
            ->select('datasets.*')
            ->with(['categories:id,name', 'user:id,email,name'])
            ->leftJoin('users', 'datasets.user_id', '=', 'users.id')
            ->when($this->tables['dataset-overview']['sortColumn'] === 'user.email', function ($query) {
                $query->orderBy('users.email', $this->tables['dataset-overview']['sortDirection']);
            }, function ($query) {
                $query->orderBy($this->tables['dataset-overview']['sortColumn'], $this->tables['dataset-overview']['sortDirection']);
            })
            ->paginate(AppConfig::PER_PAGE_OPTIONS['10'], pageName: 'datasets');
    }

    #[Computed]
    public function paginatedPendingRequests()
    {
        return $this->requestsQuery(['pending'], 'pending-requests');
    }
    #[Computed]
    public function paginatedResolvedRequests()
    {
        return $this->requestsQuery(['approved', 'rejected'], 'resolved-requests');
    }
    public function mount()
    {
        $this->users = User::all()->select('email', 'id', 'name', 'role')->toArray();
        foreach ($this->tableIds as $tableId) {
            $this->tables[$tableId] = TableDefinition::get($tableId);
        }
    }
    public function searchUsers()
    {
        $this->users = User::where('name', 'like', "%$this->userSearchTerm%")
            ->orWhere('email', 'like', "%$this->userSearchTerm%")
            ->select('email', 'id', 'role', 'name')
            ->get()
            ->toArray();
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

    public function reviewRequest(ActionRequestService $requestService, $id)
    {
        $request = ActionRequest::findOrFail($id);
        $requestService->reviewChanges($request);
    }

    public function toggleVisibility($id)
    {
        try {
            $dataset = Dataset::findOrFail($id);
            $newVisibility = $dataset->is_public ? 'private' : 'public';

            $response = DatasetActions::moveDatasetTo($dataset->unique_name, $newVisibility);

            if (!$response->isSuccessful()) {
                $this->dispatch('flash-msg', type: 'error', message: $response->message);
                return;
            }

            $this->dispatch('flash-msg', type: 'success', message: "Dataset visibility changed to {$newVisibility}");
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'An error occurred');
        }
    }

    public function deleteDataset(DatasetActions $datasetService, $uniqueName)
    {
        $payload = ['dataset_unique_name' => $uniqueName,
                    'dataset_id' => Dataset::where('unique_name', $uniqueName)->first()->id
        ];
        $result = app(ActionRequestService::class)->createRequest('delete', $payload);
        $this->handleResponse($result);
    }

    public function changeOwner($id, $newOwnerId)
    {
        try {
            $dataset = Dataset::findOrFail($id);
            $dataset->update(['user_id' => $newOwnerId]);
            $this->dispatch('flash-msg', type: 'success', message: 'Owner changed successfully!');
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'An error occurred');
        }
        unset($this->paginatedDatasetOverview);
    }

    public function cacheQuery($id)
    {
        $payload['datasets'] = [$id];

        $token = Str::random(32);
        Cache::put("download_query_{$token}", $payload, now()->addMinutes(30));

        $this->dispatch('store-download-token', token: $token);
    }

    public function recalculateStats(): void
    {
        try {
            DatasetStatistics::recalculateAllStatistics();
            $this->dispatch('flash-msg', type: 'success', message: 'Statistics recalculated successfully');
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'Failed to recalculate statistics');
        }
    }

    private function requestsQuery($status, $tableName)
    {
        return ActionRequest::query()
            ->select('action_requests.id', 'action_requests.user_id', 'action_requests.dataset_id',
                'action_requests.status', 'action_requests.reviewed_by', 'action_requests.created_at',
                'action_requests.type', 'action_requests.comment')
            ->with([
                'dataset' => function ($query) {
                    $query->withoutGlobalScope(DatasetVisibilityScope::class)
                        ->select('id', 'display_name', 'unique_name')
                        ->whereNotNull('id');
                },
                'user:id,email',
                'reviewer:id,email'
            ])
            ->leftJoin('users as creators', 'action_requests.user_id', '=', 'creators.id')
            ->leftJoin('users as reviewers', 'action_requests.reviewed_by', '=', 'reviewers.id')
            ->leftJoin('datasets', 'action_requests.dataset_id', '=', 'datasets.id')
            ->whereIn('status', $status)
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
