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
        if(empty($images)) {
            return null;
        }

        if (!($images instanceof \Illuminate\Support\Collection) && !($images instanceof \Illuminate\Pagination\LengthAwarePaginator)) {
            $images = collect(is_array($images) ? $images : [$images]);
        }

        foreach ($images as $image) {
            $image->strokeWidth = $this->calculateBorderSize($image->width, $image->height);
            $image->path = Util::constructImagePath($image->dataset->unique_name, $image->filename);

            $image->annotations->each(function ($annotation) use ($image) {
                if ($annotation->segmentation) {
                    $pixelizedSegment = $this->pixelizePolygon($annotation->segmentation, $image->width, $image->height);
                    $annotation->polygonString = $this->transformPolygonToSvgString($pixelizedSegment);
                } else {
                    $annotation->bbox = $this->pixelizeBbox($annotation, $image->width, $image->height);
                }
            });
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
