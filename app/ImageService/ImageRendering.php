<?php

namespace App\ImageService;

use App\Configs\AppConfig;
use App\Traits\CoordsTransformer;
use App\Utils\Util;

trait ImageRendering
{
    use CoordsTransformer, ImageTransformer;

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

                        if (isset($annotation['segmentation'])) {
                            unset($annotation['x'], $annotation['y'], $annotation['width'], $annotation['height']);
                            $pixelizedSegment = $this->pixelizePolygon($annotation['segmentation'], $image['width'], $image['height']);
                            $pathData = $this->transformPolygonToSvgPath($pixelizedSegment);
                        } else {
                            unset($annotation['segmentation']);
                            $pixelizedBbox = $this->pixelizeBbox($annotation, $image['width'], $image['height']);
                            $x = $pixelizedBbox['x'];
                            $y = $pixelizedBbox['y'];
                            $w = $pixelizedBbox['width'];
                            $h = $pixelizedBbox['height'];

                            $pathData = "M{$x},{$y}l{$w},0l0,{$h}l-{$w},0z";
                        }

                        $maskPathData .= $pathData;
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

    private function transformPolygonToSvgPath($segmentation): string
    {
        if (empty($segmentation)) {
            return '';
        }

        $pathData = 'M' . $segmentation[0]['x'] . ',' . $segmentation[0]['y'];

        $prevX = $segmentation[0]['x'];
        $prevY = $segmentation[0]['y'];

        // Builds relative path data
        for ($i = 1; $i < count($segmentation); $i++) {
            $dx = $segmentation[$i]['x'] - $prevX;
            $dy = $segmentation[$i]['y'] - $prevY;
            $pathData .= 'l' . $dx . ',' . $dy;

            $prevX = $segmentation[$i]['x'];
            $prevY = $segmentation[$i]['y'];
        }

        $pathData .= 'z';
        return $pathData;
    }
}
