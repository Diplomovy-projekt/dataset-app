<?php

namespace App\ImageService;

use App\Configs\AppConfig;
use App\Traits\CoordsTransformer;
use App\Utils\Util;

trait ImageRendering
{
    use ImageTransformer;

    public function prepareImagesForSvgRendering($images)
    {
        if (empty($images)) {
            return null;
        }
        $isPaginated = $images instanceof \Illuminate\Pagination\LengthAwarePaginator;

        if (!($images instanceof \Illuminate\Support\Collection) && !$isPaginated) {
            $images = collect(is_array($images) ? $images : [$images]);
        }

        if ($isPaginated) {
            $imageCollection = $images->getCollection()->toArray();
        } else {
            $imageCollection = $images->toArray();
        }
        foreach ($imageCollection as &$image) {
            $image['strokeWidth'] = $this->calculateBorderSize($image['width'], $image['height']);

            // Group annotations by class for more efficient rendering
            $annotationsByClass = collect($image['annotations'])
                ->groupBy('class.id')
                ->map(function ($annotations, $classId) use ($image) {
                    $rgb = $annotations->first()['class']['rgb'];

                    $maskPathData = '';

                    foreach ($annotations as $annotation) {
                        $maskPathData .= $annotation['svg_path'];
                    }

                    return [
                        'classId' => $classId,
                        'rgb' => $rgb,
                        'maskPathData' => $maskPathData
                    ];
                })
                ->values()
                ->toArray();

            $image['annotations'] = $annotationsByClass;
        }
        if ($isPaginated) {
            $images->setCollection(collect($imageCollection));
        } else {
            $images = collect($imageCollection);
        }
        return $images;
    }
}
