<?php

namespace App\Traits;

use App\AnnotationHandler\traits\CoordsConvertor;
use App\Utils\AppConstants;
use App\Utils\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Geometry\Factories\PolygonFactory;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\ImageManager;

trait ImageHandler
{
    use CoordsConvertor;
    public function crop($bbox,$imagePath,$savePath, $padding = 0.1)
    {
        try
        {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($imagePath);

            // Base padding on the smaller dimension of the bounding box
            $padding = round($padding * min($bbox['width'], $bbox['height']), 1);

            // Calculate if padding overflows the image bounds
            $leftOverflow = $bbox['x'] - $padding;
            $topOverflow = $bbox['y'] - $padding;
            $rightOverflow = $image->width() - ($bbox['x'] + $bbox['width'] + $padding);
            $bottomOverflow = $image->height() - ($bbox['y'] + $bbox['height'] + $padding);

            // Find the biggest overflow (the smaller, the more overflow it is)
            $biggestOverflow = min($leftOverflow, $topOverflow, $rightOverflow, $bottomOverflow);

            $padding = $biggestOverflow < 0 ? round($padding - abs($biggestOverflow), 1) : $padding;

            $x = $bbox['x'] - $padding;
            $y = $bbox['y'] - $padding;
            $width = $bbox['width'] + 2 * $padding;
            $height = $bbox['height'] + 2 * $padding;

            $image->crop($width, $height, $x, $y);
            $image->save($savePath);
            return true;
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function rescale($imagePath, $savePath, $width = AppConstants::MAX_THUMB_DIM, $height = AppConstants::MAX_THUMB_DIM)
    {
        try
        {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($imagePath);

            $image->scale($width, $height);
            $image->save($savePath);
            return true;
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }

    public function drawAnnotations($originalImgDims, $imagePath, $annotations, $technique, $strokeColor = 'blue', $fillColor = 'transparent')
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($imagePath);

        $annotations = is_array($annotations) ? $annotations : [$annotations];
        try
        {
            foreach ($annotations as $annotation)
            {
                if ($technique == AppConstants::ANNOTATION_TECHNIQUES['BOUNDING_BOX']) {
                    $bbox = $this->pixelizeBbox($annotation, $originalImgDims[0], $originalImgDims[1]);
                    $bbox = $this->shiftBboxForCroppedImg($bbox, $image->width(), $image->height(), $originalImgDims);
                    $borderSize = $this->calculateBorderSize($image->width(), $image->height());

                    $image->drawRectangle($bbox['x'], $bbox['y'], function (RectangleFactory $rectangle) use ($bbox, $fillColor, $strokeColor, $borderSize) {
                        $rectangle->size($bbox['width'], $bbox['height']);
                        $rectangle->background($fillColor);
                        $rectangle->border($strokeColor, $borderSize);
                    });
                    $image->save($imagePath);

                } elseif ($technique == AppConstants::ANNOTATION_TECHNIQUES['POLYGON']) {
                    $segment = $this->pixelizePolygon($annotation['segmentation'], $originalImgDims[0], $originalImgDims[1]);
                    $segment = $this->shiftPolygonForCroppedImg($segment, $image->width(), $image->height());
                    $borderSize = $this->calculateBorderSize($image->width(), $image->height());
                    $image->drawPolygon(function (PolygonFactory $polygon) use ($segment, $strokeColor, $fillColor, $borderSize){
                        foreach ($segment as $point) {
                            $polygon->point($point['x'], $point['y']);
                        }
                        $polygon->background($fillColor);
                        $polygon->border($strokeColor, $borderSize); // border color and border width
                    });
                    $image->save($imagePath);
                }
            }

            return Response::success();
        }
        catch(\Exception $e)
        {
            return Response::error($e->getMessage());
        }
    }

    private function calculateBorderSize($width, $height)
    {
        $baseSize = 2;
        $scalingFactor = 0.005;

        $averageDimension = ($width + $height) / 2;
        $borderSize = $baseSize + ($averageDimension * $scalingFactor);

        return round($borderSize);
    }


    public function calculateThumbnailDimensions($originalWidth, $originalHeight, $maxDimension = AppConstants::MAX_THUMB_DIM)
    {
        $originalWidth = (int) $originalWidth;
        $originalHeight = (int) $originalHeight;
        $maxDimension = (int) $maxDimension;

        $aspectRatio = $originalWidth / $originalHeight;
        $thumbWidth = $originalWidth >= $originalHeight ? $maxDimension : intval($maxDimension * $aspectRatio);
        $thumbHeight = $originalWidth >= $originalHeight ? intval($maxDimension / $aspectRatio) : $maxDimension;

        return ['width' => $thumbWidth, 'height' => $thumbHeight];
    }

    public function prepareImagesForSvgRendering($images)
    {
        if ($images->isEmpty()) {
            return;
        }
        if($images instanceof Model || is_array($images)){
            $images = collect([$images]);
        }

        $images->each(function ($image) {
            $image->viewDims = $this->calculateThumbnailDimensions($image->img_width, $image->img_height);

            $image->annotations->each(function ($annotation) use ($image) {
                $annotation->class->color = $this->categories[$annotation->annotation_class_id]['color'];
                $annotation->bbox = $this->pixelizeBbox($annotation, $image->viewDims['width'], $image->viewDims['height']);
                if($annotation->segmentation){
                    $pixelizedSegment = $this->pixelizePolygon($annotation->segmentation, $image->viewDims['width'], $image->viewDims['height']);
                    $annotation->polygonString = $this->transformPolygonToSvgString($pixelizedSegment);
                }

            });
        });

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

    public function addColorsToClasses($classes)
    {
        return $classes->mapWithKeys(function($category) {
            $category->color = $this->generateRandomRgba();
            return [$category->id => $category];
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
