<?php

namespace App\ActionRequestService\Handlers;

use App\ActionRequestService\Interfaces\ActionRequestHandlerInterface;
use App\Models\Dataset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EditDatasetHandler extends BaseHandler
{
    protected function validationRules(): array
    {
        return [
            'dataset_unique_name' => 'required|exists:datasets,unique_name',
            'display_name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|nullable',
            'categories' => 'sometimes|array|nullable',
            'categories.*' => 'exists:categories,id',
            'metadata' => 'sometimes|array|nullable',
            'metadata.*' => 'exists:metadata_values,id',
        ];
    }

    public function approve(array $payload): void
    {
        try {
            $dataset = Dataset::findOrFail($payload['dataset_id']);
            DB::transaction(function () use ($dataset, $payload) {
                $dataset->update([
                    'display_name' => $payload['display_name'] ?? $dataset->display_name,
                    'description' => $payload['description'] ?? $dataset->description,
                ]);

                $dataset->metadataValues()->sync($payload['metadata']);
                $dataset->categories()->sync($payload['categories']);
            });
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }



    public function reject(array $payload): void
    {
        // Nothing needs to be done here.
    }

    public function reviewChanges(Model $request): mixed
    {
        return Redirect::route('dataset.review.edit', ['requestId' => $request->id]);
    }
}
