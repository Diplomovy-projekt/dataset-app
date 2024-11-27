<?php

namespace App\Livewire\Components;

use Livewire\Component;

class DatasetCard extends Component
{
    public $dataset;
    public function render()
    {
        $this->dataset = [
            'image' => 'https://picsum.photos/200/300',
            'annotation_format' => 'Bounding Box',
            'image_count' => 3.1,
            'name' => 'rock-paper-scissors',
            'updated_at' => '13 hours ago',
        ];
        return view('livewire.components.dataset-card');
    }
}
