<?php

namespace App\Livewire\FullPages;

use App\Models\Dataset;
use App\Models\MetadataType;
use App\Models\MetadataValue;
use App\Models\User;
use Livewire\Component;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class AdminDashboard extends Component
{
    public int $userCount;
    public int $datasetCount;
    public float $totalStorage;
    public array $metadata =  [];

    public function mount()
    {
        $this->userCount = User::count();
        $this->datasetCount = Dataset::count();
        $this->totalStorage = $this->getTotalDatasetSize();
        $this->setMetadta();
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

    public function setMetadta()
    {
        $this->metadata = MetadataType::select('id', 'name', 'description')
            ->with(['metadataValues' => function ($query) {
                $query->select('id', 'metadata_type_id', 'value');
            }])->get()->toArray();
    }
    public function saveType($name, $id = null, $description = '')
    {
        try {
            MetadataType::updateOrCreate(
                ['id' => $id],
                ['name' => $name, 'description' => $description]
            );

            $this->setMetadta();

            $this->dispatch('flash-msg', type: 'success', message: 'Type saved successfully');
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'Failed to save type');
        }
    }

    public function saveValue($typeId, $value, $valueId = null, $description = '')
    {
        try {
            MetadataValue::updateOrCreate(
                ['id' => $valueId],
                ['metadata_type_id' => $typeId, 'value' => $value, 'description' => $description]
            );

            $this->setMetadta();

            $this->dispatch('flash-msg', type: 'success', message: 'Value saved successfully');
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'Failed to save value');
        }
    }

    public function deleteType($id)
    {
        try {
            MetadataType::where('id', $id)->delete();
            $this->setMetadta();
            $this->dispatch('flash-msg', type: 'success', message: 'Type deleted successfully');
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'Failed to delete type');
        }
    }


    public function deleteValue($id)
    {
        try {
            MetadataValue::where('id', $id)->delete();
            $this->setMetadta();
            $this->dispatch('flash-msg', type: 'success', message: 'Value deleted successfully');
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'Failed to delete value');
        }
    }


}
