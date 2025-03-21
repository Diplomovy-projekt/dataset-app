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
            foreach ($image['annotations'] as &$annotation) {
                if (isset($annotation['segmentation'])) {
                    unset($annotation['x'], $annotation['y'], $annotation['width'], $annotation['height']);

                    $pixelizedSegment = $this->pixelizePolygon($annotation['segmentation'], $image['width'], $image['height']);
                    $annotation['segmentation'] = $this->transformPolygonToSvgString($pixelizedSegment);
                } else {
                    unset($annotation['segmentation']);

                    $pixelizedBbox = $this->pixelizeBbox($annotation, $image['width'], $image['height']);
                    $annotation['x'] = $pixelizedBbox['x'];
                    $annotation['y'] = $pixelizedBbox['y'];
                    $annotation['width'] = $pixelizedBbox['width'];
                    $annotation['height'] = $pixelizedBbox['height'];
                }
            }
        }
        if ($isPaginated) {
            // Re-wrap modified array into a collection and set it back to paginator
            $images->setCollection(collect($imageCollection));
        } else {
            $images = collect($imageCollection);
        }
        return $images;
    }

    private function transformPolygonToSvgString($segmentation): string
    {
        if (empty($segmentation)) {
            return '';
        }

        $count = count($segmentation);
        $parts = array_fill(0, $count, '');

        for ($i = 0; $i < $count; $i++) {
            $parts[$i] = $segmentation[$i]['x'] . ',' . $segmentation[$i]['y'];
        }

        return implode(' ', $parts);
    }

}
