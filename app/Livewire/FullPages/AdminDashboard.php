<?php

namespace App\Livewire\FullPages;

use App\Models\Dataset;
use App\Models\User;
use Livewire\Component;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class AdminDashboard extends Component
{
    public int $userCount;
    public int $datasetCount;
    public float $totalStorage;

    public array $emailSettings = [
        'smtp_server' =>  'smtp.mailtrap.io',
        'smtp_port' =>  2525,
        'smtp_username' =>  'your-username',
        'smtp_password' =>  'your-password',
        'smtp_encryption' =>  'tls',
        'from_address' =>  'no-reply@dataset.com'
        ];

    public function mount()
    {
        $this->userCount = User::count();
        $this->datasetCount = Dataset::count();
        $this->totalStorage = $this->getTotalDatasetSize();
    }
    public function render()
    {
        return view('livewire.full-pages.admin-dashboard');
    }

    public function getTotalDatasetSize()
    {
        $folder = storage_path('app/public/datasets');
        $size = 0;

        if (is_dir($folder)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                $size += $file->getSize();
            }
        }

        return round($size / 1024 / 1024 / 1024, 2); // GB
    }
}
