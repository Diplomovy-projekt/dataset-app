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
                $annotation->bbox = $this->pixelizeBbox($annotation, $image->width, $image->height);
                if ($annotation->segmentation) {
                    $pixelizedSegment = $this->pixelizePolygon($annotation->segmentation, $image->width, $image->height);
                    $annotation->polygonString = $this->transformPolygonToSvgString($pixelizedSegment);
                }
            });
        }

        return $images;
    }

    private function transformPolygonToSvgString($segmentation): string
    {
        $svgString = '';
        foreach ($segmentation as $point) {
            $svgString .= $point['x'] . ',' . $point['y'] . ' ';
        }
        $svgString = rtrim($svgString);
        return $svgString;
    }

}
