<?php

namespace App\Http\Controllers;

use App\Models\AnnotationData;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnnotationRendererController extends Controller
{
    public function getAnnotations(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'required|string'
        ]);

        $imageIds = $request->input('image_ids');

        // Example: Fetch annotations from database
        // Replace with your actual data retrieval logic
        $images = Image::whereIn('id', $imageIds)
            ->with([
                'annotations' => function ($query) {
                    $query->select(['id', 'image_id', 'x', 'y', 'width', 'height', 'segmentation', 'annotation_class_id'])
                        ->with(['class' => function ($query) {
                            $query->select(['id','name', 'rgb']);
                        }]);
                }
            ])
            ->get()
            ->mapWithKeys(function ($image) {
                return [$image->id => $image->annotations->toArray()];
            })
            ->toArray();



        return response()->json($images);
    }
}
