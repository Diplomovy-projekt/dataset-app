<?php

namespace App\ImageService;

use App\Configs\AppConfig;
use App\Traits\CoordsTransformer;

trait ImageRendering
{
    use CoordsTransformer, ImageTransformer;
    public function prepareImagesForSvgRendering($images)
    {
        if(empty($images)) {
            return null;
        }
        $images = $images instanceof \Illuminate\Pagination\LengthAwarePaginator ? $images : collect([$images]);

        foreach ($images as $image) {
            //$image->viewDims = ['width' => $image->width, 'height' => $image->height];//$this->calculateThumbnailDimensions($image->width, $image->height);

            $image->strokeWidth = $this->calculateBorderSize($image->width, $image->height);
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

    /*public function calculateThumbnailDimensions($originalWidth, $originalHeight, $maxDimension = AppConfig::MAX_THUMB_DIM)
    {
        $originalWidth = (int) $originalWidth;
        $originalHeight = (int) $originalHeight;
        $maxDimension = (int) $maxDimension;

        $aspectRatio = $originalWidth / $originalHeight;
        $thumbWidth = $originalWidth >= $originalHeight ? $maxDimension : intval($maxDimension * $aspectRatio);
        $thumbHeight = $originalWidth >= $originalHeight ? intval($maxDimension / $aspectRatio) : $maxDimension;

        return ['width' => $thumbWidth, 'height' => $thumbHeight];
    }*/

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
