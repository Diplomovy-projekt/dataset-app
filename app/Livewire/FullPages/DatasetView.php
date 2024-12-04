<?php

namespace App\Livewire\FullPages;

use App\Models\Dataset;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DatasetView extends Component
{
    public $uniqueName;
    #[Computed(cache:true)]
    public function dataset()
    {
        $idk = Dataset::with(['images' => function ($query) {
            $query->take(10)  // Limit the number of images to 10 (adjust as needed)
            ->with('annotations'); // Eager load annotations for the selected images
        }])
            ->where('unique_name', $this->uniqueName)
            ->first();
        return $idk;

    }

    public function mount($uniqueName)
    {
        $this->$uniqueName = $uniqueName;
    }

    public function render()
    {
        return view('livewire.full-pages.dataset-view');
    }
}
