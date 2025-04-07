<?php

namespace App\Livewire\FullPages;

use App\Models\Category;
use App\Models\Dataset;
use App\Models\DatasetStatistics;
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
    public array $metadata = [];
    public array $categories = [];

    public function mount()
    {
        $this->userCount = User::count();
        $this->datasetCount = Dataset::count();
        $this->totalStorage = $this->getTotalDatasetSize();
        $this->categories = Category::get()->toArray();
        $this->setMetadata();
    }

    public function render()
    {
        return view('livewire.full-pages.admin-dashboard');
    }

    public function getTotalDatasetSize(): float
    {
        $paths = [
            base_path('storage/app/public/datasets'),
            base_path('storage/app/private/datasets'),
        ];

        $totalSize = 0;

        foreach ($paths as $path) {
            if (is_dir($path)) {
                foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                    $totalSize += $file->getSize();
                }
            }
        }

        return round($totalSize / 1024 / 1024 / 1024, 2); // GB
    }

    public function setMetadata(): void
    {
        $this->metadata = MetadataType::select('id', 'name', 'description')
            ->with(['metadataValues' => function ($query) {
                $query->select('id', 'metadata_type_id', 'value');
            }])->get()->toArray();
    }

    public function saveType($name, $id = null, $description = ''): void
    {
        try {
            $type = MetadataType::find($id);

            if (!$type || $type->name !== $name) {
                MetadataType::updateOrCreate(
                    ['id' => $id],
                    ['name' => $name, 'description' => $description]
                );

                $this->setMetadata();
                $this->dispatch('flash-msg', type: 'success', message: 'Type saved successfully');
            }
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'Failed to save type');
        }
    }

    public function saveValue($typeId, $value, $valueId = null, $description = ''): void
    {
        try {
            $metadataValue = MetadataValue::find($valueId);

            if (!$metadataValue || $metadataValue->metadata_type_id !== $typeId ||
                $metadataValue->value !== $value) {

                MetadataValue::updateOrCreate(
                    ['id' => $valueId],
                    ['metadata_type_id' => $typeId, 'value' => $value, 'description' => $description]
                );

                $this->setMetadata();
                $this->dispatch('flash-msg', type: 'success', message: 'Value saved successfully');
            }
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'Failed to save value');
        }
    }


    public function deleteType($id): void
    {
        try {
            MetadataType::where('id', $id)->delete();
            $this->setMetadata();
            $this->dispatch('flash-msg', type: 'success', message: 'Type deleted successfully');
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'Failed to delete type');
        }
    }


    public function deleteValue($id): void
    {
        try {
            MetadataValue::where('id', $id)->delete();
            $this->setMetadata();
            $this->dispatch('flash-msg', type: 'success', message: 'Value deleted successfully');
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'Failed to delete value');
        }
    }

    public function saveCategory($name, $id = null): void
    {
        try {
            $category = Category::find($id);

            if (!$category || $category->name !== $name) {
                Category::updateOrCreate(
                    ['id' => $id],
                    ['name' => $name]
                );

                $this->categories = Category::get()->toArray();
                $this->dispatch('flash-msg', type: 'success', message: 'Category saved successfully');
            }
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'Failed to save category');
        }
    }

    public function deleteCategory($id): void
    {
        try {
            Category::where('id', $id)->delete();
            $this->categories = Category::get()->toArray();
            $this->dispatch('flash-msg', type: 'success', message: 'Category deleted successfully');
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'Failed to delete category');
        }
    }
}
