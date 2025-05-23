<?php

namespace App\Livewire\Components;

use App\Configs\AppConfig;
use App\Models\Dataset;
use App\Utils\Util;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Component;

class ClassesSample extends Component
{
    public $dataset;

    #[Modelable]
    public $selectedClasses = [];
    #[Locked]
    public $selectable;

    public function mount($uniqueName, $selectable = false)
    {
        $this->selectable = $selectable;
        $dataset = Dataset::where('unique_name', $uniqueName)->with('classes')->firstOrFail();

        foreach ($dataset->classes as $class) {
            $this->selectedClasses[$class->id] = true;

            // Stats
            $class->annotationCount = $class->annotationsForClass($dataset->id);
            $class->imageCount = $class->imagesForClass($dataset->id);

            // Images
            $datasetPath = Util::getDatasetPath($dataset);
            $images = array_slice(Storage::files($datasetPath . AppConfig::CLASS_IMG_FOLDER . $class->id), 0, AppConfig::SAMPLES_COUNT);
            $class->images = array_map(fn($image) => [
                'filename' => pathinfo($image, PATHINFO_BASENAME),
                'folder' => AppConfig::CLASS_IMG_FOLDER . $class->id,
            ], $images);
        }

        $this->dataset = $dataset->toArray();

        if($this->selectable) {
            $this->dispatch('add-selected', selectedClasses: $this->selectedClasses, datasetId: $dataset->id);
        }
    }

    public function render()
    {
        return view('livewire.components.classes-sample');
    }
}
