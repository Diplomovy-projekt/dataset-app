<?php

namespace App\Livewire\Components;

use App\Models\Image;
use Livewire\Component;

class SearchBar extends Component
{
    public $searchTerm;
    public $type;

    public function render()
    {
        return view('livewire.components.search-bar');
    }

    public function search()
    {
        switch ($this->type) {
            case 'images':
                $this->searchImages();
                break;
            case 'datasets':
                $this->searchDatasets();
                break;
            case 'users':
                $this->searchUsers();
                break;
        }
    }
    private function searchImages()
    {
        $result = Image::where('filename', 'like', '%' . $this->searchTerm . '%')->with(['annotations.class'])->get();
        dd($result);
        $this->emit('searchImages', $this->searchTerm);
    }
    private function searchDatasets()
    {
        $this->emit('searchDatasets', $this->searchTerm);
    }
    private function searchUsers()
    {
        $this->emit('searchUsers', $this->searchTerm);
    }
}
