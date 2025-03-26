<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnnotationRendererController extends Controller
{
    public function getAnnotations(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'required|string'
        ]);

        $imageIds = $request->input('image_ids');

        // Use caching to avoid repeated queries for the same data
        $cacheKey = 'annotations_' . md5(json_encode($imageIds));
        $cacheTime = 60; // Cache for 1 hour

        $compressedData = Cache::remember($cacheKey, $cacheTime, function () use ($imageIds) {
            // Fetch raw data
            $images = Image::whereIn('id', $imageIds)
                ->select(['id', 'filename', 'dataset_folder', 'width', 'height'])
                ->with([
                    'annotations' => function ($query) {
                        $query->select(['id', 'image_id', 'x', 'y', 'width', 'height', 'segmentation', 'annotation_class_id'])
                            ->with(['class' => function ($query) {
                                $query->select(['id', 'name', 'rgb']);
                            }]);
                    }
                ])
                ->get();

            // Optimize and compress the data structure
            $optimizedData = [];

            // First, create a class dictionary to avoid duplication
            $classDict = [];
            $classIndex = 0;

            foreach ($images as $image) {
                $imageData = [
                    'i' => $image->id,
                    'w' => $image->width,
                    'h' => $image->height,
                    'a' => [] // annotations
                ];

                foreach ($image->annotations as $annotation) {
                    // Handle class dictionary
                    $classId = $annotation->class->id;
                    if (!isset($classDict[$classId])) {
                        $classDict[$classId] = [
                            'i' => $classId,
                            'n' => $annotation->class->name,
                            'c' => $annotation->class->rgb,
                            'idx' => $classIndex++
                        ];
                    }

                    // Create optimized annotation
                    $annotationData = [
                        'ci' => $classDict[$classId]['idx'], // Reference to class by index
                    ];

                    // Add coordinates based on what's available
                    if ($annotation->segmentation) {
                        // Optimize polygon points by reducing precision
                        $annotationData['s'] = $this->compressPolygon($annotation->segmentation);
                    } else {
                        $annotationData['x'] = round($annotation->x);
                        $annotationData['y'] = round($annotation->y);
                        $annotationData['w'] = round($annotation->width);
                        $annotationData['h'] = round($annotation->height);
                    }

                    $imageData['a'][] = $annotationData;
                }

                $optimizedData[$image->id] = $imageData;
            }

            // Create the final compressed structure
            return [
                'classes' => array_values($classDict),
                'images' => $optimizedData
            ];
        });

        // Set compression headers
        return response()->json($compressedData)
            ->header('Cache-Control', 'public, max-age=86400'); // Cache for 24 hours
    }

    /**
     * Compress polygon points by reducing precision and using delta encoding
     */
    private function compressPolygon(string $polygonPoints): string
    {
        $points = explode(' ', $polygonPoints);
        $compressed = [];
        $lastX = 0;
        $lastY = 0;

        foreach ($points as $point) {
            list($x, $y) = explode(',', $point);

            // Round to 1 decimal place for smaller coordinates
            $x = round(floatval($x), 1);
            $y = round(floatval($y), 1);

            // Use delta encoding (difference from previous point)
            $deltaX = $x - $lastX;
            $deltaY = $y - $lastY;

            $compressed[] = "$deltaX,$deltaY";

            $lastX = $x;
            $lastY = $y;
        }

        return implode(' ', $compressed);
    }
}
