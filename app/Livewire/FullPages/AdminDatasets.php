<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\DatasetActions\DatasetActions;
use App\Models\Dataset;
use App\Models\Image;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
class AdminDatasets extends Component
{
    use WithPagination;

    public array $headers = [
        ['label' => 'Display Name', 'field' => 'display_name', 'sortable' => true, 'width' => 'w-64'],
        ['label' => 'Categories', 'field' => 'categories', 'sortable' => false, 'width' => 'w-20'],
        ['label' => 'Annotation Technique', 'field' => 'annotation_technique', 'sortable' => true, 'width' => 'w-18'],
        ['label' => 'Owner', 'field' => 'user.email', 'sortable' => true, 'width' => 'w-18'],
        ['label' => 'Visibility', 'field' => 'is_public', 'sortable' => true, 'width' => 'w-16'],
        ['label' => 'Pending Changes', 'field' => 'pending_changes', 'sortable' => false, 'width' => 'w-14'],
        ['label' => 'Actions', 'field' => 'actions', 'sortable' => false, 'width' => 'w-14'],
    ];

    public $sortColumn = 'display_name';
    public $sortDirection = 'asc';

    public array $datasets;
    public array $users = [];
    public string $userSearchTerm = '';
    #[Computed]
    public function paginatedDatasets()
    {
        return Dataset::query()
            ->select('datasets.*')
            ->with(['categories:id,name', 'user:id,email,name'])
            ->leftJoin('users', 'datasets.user_id', '=', 'users.id')
            ->when($this->sortColumn === 'user.email', function ($query) {
                $query->orderBy('users.email', $this->sortDirection);
            }, function ($query) {
                $query->orderBy($this->sortColumn, $this->sortDirection);
            })
            ->paginate(AppConfig::PER_PAGE_OPTIONS['10']);
    }
    public function mount()
    {
        $this->users = User::all()->select('email', 'id', 'name', 'role')->toArray();
    }
    public function searchUsers()
    {
        $this->users = User::where('name', 'like', "%$this->userSearchTerm%")
            ->orWhere('email', 'like', "%$this->userSearchTerm%")
            ->select('email', 'id', 'role', 'name')
            ->get()
            ->toArray();
    }

    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function toggleVisibility($id)
    {
        try {
            DB::beginTransaction();
            $dataset = Dataset::findOrFail($id);
            $newVisibility = !$dataset->is_public;

            $fromPath = AppConfig::DATASETS_PATH[$dataset->is_public ? "public" : "private"] . $dataset->unique_name;
            $toPath = AppConfig::DATASETS_PATH[$newVisibility ? "public" : "private"] . $dataset->unique_name;

            if (Storage::exists($fromPath)) {
                Storage::move($fromPath, $toPath);
                Log::info("Moved dataset: {$fromPath} → {$toPath}");

                $dataset->update(['is_public' => $newVisibility]);
                $visibilityText = $newVisibility ? 'public' : 'private';
                $this->dispatch('flash-msg', type: 'success', message: "Dataset visibility changed to {$visibilityText}");
                DB::commit();
                return;
            }

            Log::error("Source directory not found: {$fromPath}");
            DB::rollBack();
            $this->dispatch('flash-msg', type: 'error', message: 'Dataset not found');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Exception in toggleVisibility: " . $e->getMessage());
            $this->dispatch('flash-msg', type: 'error', message: 'An error occurred');
        }
    }


    public function deleteDataset(DatasetActions $datasetService, $uniqueName)
    {
        $result = $datasetService->deleteDataset($uniqueName);
        if($result->isSuccessful()){
            unset($this->paginatedDatasets);
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
        unset($this->paginatedDatasets);
    }

    public function cacheQuery($id)
    {
        $query = Image::where('dataset_id', $id)->with('annotations.class');

        $token = Str::random(32);
        Cache::put("download_query_{$token}", \EloquentSerialize::serialize($query), now()->addMinutes(30));

        $this->dispatch('store-download-token', token: $token);
    }

}
