<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\Configs\TableDefinition;
use App\DatasetActions\DatasetActions;
use App\Models\Dataset;
use App\Models\Image;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
class AdminDatasets extends Component
{
    use WithPagination;
    private array $tableIds = ['dataset-overview', 'pending-requests', 'accepted-requests', 'rejected-requests'];
    public array $tables = [];

    public array $datasets;
    public array $users = [];
    public string $userSearchTerm = '';
    #[Computed]
    public function paginatedDatasetOverview()
    {
        return Dataset::query()
            ->select('datasets.*')
            ->with(['categories:id,name', 'user:id,email,name'])
            ->leftJoin('users', 'datasets.user_id', '=', 'users.id')
            ->when($this->tables['dataset-overview']['sortColumn'] === 'user.email', function ($query) {
                $query->orderBy('users.email', $this->tables['dataset-overview']['sortDirection']);
            }, function ($query) {
                $query->orderBy($this->tables['dataset-overview']['sortColumn'], $this->tables['dataset-overview']['sortDirection']);
            })
            ->paginate(AppConfig::PER_PAGE_OPTIONS['10']);
    }

    #[Computed]
    public function paginatedPendingRequests()
    {
        return new LengthAwarePaginator(collect([['id' => 1], ['id' => 2]]), 10, 5, 1);
    }
    #[Computed]
    public function paginatedAcceptedRequests()
    {
        return new LengthAwarePaginator(collect([['id' => 1], ['id' => 2]]), 10, 5, 1);
    }
    #[Computed]
    public function paginatedRejectedRequests()
    {
        return new LengthAwarePaginator(collect([['id' => 1], ['id' => 2]]), 10, 5, 1);
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
        $result = $datasetService->deleteDataset($uniqueName);
        if($result->isSuccessful()){
            unset($this->paginatedDatasetOverview);
        }
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
        $query = Image::where('dataset_id', $id)->with('annotations.class');
        $payload['query'] = \EloquentSerialize::serialize($query);
        $payload['datasets'] = [$id];

        $token = Str::random(32);
        Cache::put("download_query_{$token}", $payload, now()->addMinutes(30));

        $this->dispatch('store-download-token', token: $token);
    }

}
