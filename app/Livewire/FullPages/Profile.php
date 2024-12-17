<?php

namespace App\Livewire\FullPages;

use App\Models\Dataset;
use Livewire\Component;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class Profile extends Component
{
    public $datasets;
    public function render()
    {
        // create image manager with desired driver
        $manager = new ImageManager(new Driver());

// Load an image
        $image = $manager->read('C:\laragon\www\dataset-app\public/IMG_1517.jpg');

// Coordinates and dimensions for cropping
        $x_center = 500;  // X-coordinate of the center
        $y_center = 500;  // Y-coordinate of the center
        $width = 500;     // Width of the crop area
        $height = 500;    // Height of the crop area

// Calculate the top-left corner of the crop area
        $x = $x_center - ($width / 2);
        $y = $y_center - ($height / 2);

// Crop the image
        $image->crop(46.0, 129.0,661.0, 1700.0, position: 'top-left');

// Save the cropped image
        $image->save('C:\laragon\www\dataset-app\public/cropped_image.jpg');



        $this->loadDatasets();
        return view('livewire.full-pages.profile');
    }

    public function loadDatasets()
    {
        $this->datasets = Dataset::all();
    }
}
