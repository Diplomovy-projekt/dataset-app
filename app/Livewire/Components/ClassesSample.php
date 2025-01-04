<?php

namespace App\Livewire\Components;

use App\Configs\AppConfig;
use App\Models\Dataset;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ClassesSample extends Component
{
    public $datasets;

    public function mount($uniqueNames)
    {
        if(!is_array($uniqueNames)) {
            $uniqueNames = [$uniqueNames];
        }
        foreach ($uniqueNames as $name) {
            $dataset = Dataset::where('unique_name', $name)->with('classes')->first();
            foreach($dataset->classes as $class) {
                $images = Storage::disk('datasets')->files($dataset->unique_name . '/' . AppConfig::CLASS_IMG_FOLDER . $class->id);
                $class->images = array_map(fn($image) => AppConfig::LINK_DATASETS_PATH . $image, $images);
            }
            $this->datasets[] = $dataset->toArray();
        }

    }

    public function render()
    {
        return view('livewire.components.classes-sample');
    }
}
