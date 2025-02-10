<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\Models\Dataset;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class AdminDatasets extends Component
{
    use WithPagination;

    public array $headers = [
        ['label' => 'Display Name', 'field' => 'display_name', 'sortable' => true, 'width' => 'w-64'],
        ['label' => 'Categories', 'field' => 'categories', 'sortable' => false, 'width' => 'w-20'],
        ['label' => 'Annotation Technique', 'field' => 'annotation_technique', 'sortable' => true, 'width' => 'w-18'],
        ['label' => 'Owner', 'field' => 'owner_id', 'sortable' => false, 'width' => 'w-18'],
        ['label' => 'Visibility', 'field' => 'is_public', 'sortable' => true, 'width' => 'w-16'],
        ['label' => 'Pending Changes', 'field' => 'pending_changes', 'sortable' => false, 'width' => 'w-14'],
        ['label' => 'Actions', 'field' => 'actions', 'sortable' => false, 'width' => 'w-14'],
    ];

    public $sortColumn = 'display_name';
    public $sortDirection = 'asc';

    public array $datasets;
    #[Computed]
    public function paginatedDatasets()
    {
        return Dataset::with('categories:id,name')
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate(AppConfig::PER_PAGE);
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
        $dataset = Dataset::find($id);
        $dataset->update(['is_public' => !$dataset->is_public]);
    }

}
