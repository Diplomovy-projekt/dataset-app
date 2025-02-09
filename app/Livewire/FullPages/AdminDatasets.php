<?php

namespace App\Livewire\FullPages;

use App\Models\Dataset;
use Livewire\Component;

class AdminDatasets extends Component
{

    public array $datasets;

    public function mount()
    {
        /*$this->datasets = $datasets = Dataset::with('categories:id,name')->get()->map(function ($dataset) {
            $dataset->categories->makeHidden('pivot');
            return $dataset;
        })->toArray();*/
        $this->datasets = Dataset::with('categories:id,name')
            ->get()
            ->each(fn($dataset) => $dataset->categories->makeHidden('pivot'))
            ->keyBy('id')
            ->toArray();
    }
    public function render()
    {

        return view('livewire.full-pages.admin-datasets');
    }

    public function toggleVisibility($id)
    {
        $dataset = Dataset::find($id);
        $dataset->update(['is_public' => !$dataset->is_public]);
        $this->datasets[$id]['is_public'] = $dataset->is_public; // Ensure Livewire state updates
    }

}
