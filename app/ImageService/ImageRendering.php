<?php

namespace App\ImageService;

use App\Configs\AppConfig;
use App\Traits\CoordsTransformer;
use Illuminate\Database\Eloquent\Model;

trait ImageRendering
{
    use CoordsTransformer;
    public function prepareImagesForSvgRendering($images)
    {
        if ($images->isEmpty()) {
            return $images;
        }
        if($images instanceof Model || is_array($images)){
            $images = collect([$images]);
        }

        $images->each(function ($image) {
            $image->viewDims = $this->calculateThumbnailDimensions($image->img_width, $image->img_height);

            $image->annotations->each(function ($annotation) use ($image) {
                $annotation->class->color = $this->classes[$annotation->annotation_class_id]['color'];

                $annotation->bbox = $this->pixelizeBbox($annotation, $image->viewDims['width'], $image->viewDims['height']);
                if($annotation->segmentation){
                    $pixelizedSegment = $this->pixelizePolygon($annotation->segmentation, $image->viewDims['width'], $image->viewDims['height']);
                    $annotation->polygonString = $this->transformPolygonToSvgString($pixelizedSegment);
                }

            });
        });

        return $images;
    }

    public function calculateThumbnailDimensions($originalWidth, $originalHeight, $maxDimension = AppConfig::MAX_THUMB_DIM)
    {
        $originalWidth = (int) $originalWidth;
        $originalHeight = (int) $originalHeight;
        $maxDimension = (int) $maxDimension;

        $aspectRatio = $originalWidth / $originalHeight;
        $thumbWidth = $originalWidth >= $originalHeight ? $maxDimension : intval($maxDimension * $aspectRatio);
        $thumbHeight = $originalWidth >= $originalHeight ? intval($maxDimension / $aspectRatio) : $maxDimension;

        return ['width' => $thumbWidth, 'height' => $thumbHeight];
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

    public function addColorsAndStateToClasses($classes)
    {
        return $classes->mapWithKeys(function($class) {
            $class->color = $this->generateRandomRgba();
            $class->state = 'true';
            return [
                $class->id => $class
            ];
        })->toArray();
    }

    public function generateRandomRgba()
    {
        $r = rand(0, 255);
        $g = rand(0, 255);
        $b = rand(0, 255);

        return [
            'fill' => "rgba($r, $g, $b, 0.2)",
            'stroke' => "rgba($r, $g, $b, 1)"
        ];
    }
}
