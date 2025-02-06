<?php

namespace App\Livewire\Components;

use App\Configs\AppConfig;
use App\Models\Dataset;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Component;

class ClassesSample extends Component
{
    public $datasets;

    #[Modelable]
    public $selectedClasses = [];
    #[Locked]
    public $selectable;
    public function mount($uniqueNames, $selectable = false)
    {
        $this->selectable = $selectable;
        if(!is_array($uniqueNames)) {
            $uniqueNames = [$uniqueNames];
        }
        foreach ($uniqueNames as $name) {
            $dataset = Dataset::where('unique_name', $name)->with('classes')->first();
            foreach($dataset->classes as $class) {
                $class->annotationCount = $class->annotationsForClass($dataset->id);
                $class->imageCount = $class->imagesForClass($dataset->id);
                $images = Storage::disk('datasets')->files($dataset->unique_name . '/' . AppConfig::CLASS_IMG_FOLDER . $class->id);
                $class->images = array_map(fn($image) => AppConfig::LINK_DATASETS_PATH . $image, $images);
            }
            foreach($dataset->classes as $class) {
                $this->selectedClasses[$class->id] = true;
            }
            $this->datasets[] = $dataset->toArray();
        }
        $this->dispatch('add-selected', selectedClasses: $this->selectedClasses, datasetId: $dataset->id);
    }
    public function render()
    {
        return view('livewire.components.classes-sample');
    }
}
