<?php

namespace App\Livewire\FullPages;

use App\Models\ActionRequest;
use App\Models\Category;
use App\Models\MetadataType;
use App\Models\MetadataValue;
use Livewire\Component;

class ReviewEditDataset extends Component
{
    public array $currentDataset;
    public array $requestDataset;
    public int $requestId;

    public function mount($requestId)
    {
        $request = ActionRequest::findOrFail($requestId);
        if($request->status != 'pending') {
            abort(403, 'Request is not pending');
        }
        $this->buildDataset($request->dataset()->first());
        $this->buildChangeRequest($request->payload);
    }
    public function render()
    {
        return view('livewire.full-pages.review-edit-dataset');
    }

    private function buildChangeRequest($payload)
    {
        $payload = json_decode($payload, true);
        $this->requestDataset['display_name'] = $payload['display_name'];
        $this->requestDataset['description'] = $payload['description'];
        $this->requestDataset['categories'] = Category::whereIn('id', $payload['categories'])->select('id', 'name')->get()->toArray();
        $groupedMetadata = [];
        foreach($payload['metadata'] as $metadata){
            $metadataValue = MetadataValue::where('id', $metadata)
                ->select('id', 'value', 'metadata_type_id')
                ->with('metadataType:id,name')
                ->first();

            $metadataType = $metadataValue->metadataType;

            if (!isset($groupedMetadata[$metadataType->id])) {
                $groupedMetadata[$metadataType->id] = [
                    'id' => $metadataType->id,
                    'name' => $metadataType->name,
                    'values' => [],
                ];
            }

            $groupedMetadata[$metadataType->id]['values'][] = $metadataValue->toArray();
        }
        $this->requestDataset['metadata'] = array_values($groupedMetadata);
    }

    private function buildDataset($dataset)
    {
        $this->currentDataset['display_name'] = $dataset->display_name;
        $this->currentDataset['description'] = $dataset->description;
        $this->currentDataset['categories'] = $dataset->categories()
            ->select('categories.id', 'categories.name')
            ->get()
            ->toArray();
        $this->currentDataset['metadata'] = $dataset->metadataGroupedByType()->toArray();
    }
}
