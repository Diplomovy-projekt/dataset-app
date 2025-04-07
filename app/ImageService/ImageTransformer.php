<?php

namespace App\ImageService;

use App\Configs\AppConfig;
use App\Traits\CoordsTransformer;
use App\Utils\Response;
use App\Utils\Util;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Geometry\Factories\PolygonFactory;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\ImageManager;

trait ImageTransformer
{
    use CoordsTransformer;
    public function crop($bbox,$imagePath,$savePath, $padding = 0.1)
    {
        try
        {
            $manager = MyImageManager::getManager();
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
            Util::logException($e, 'ImageTransformer crop');
            return $e->getMessage();
        }
    }
    public function rescale($imagePath, $savePath, $width = AppConfig::MAX_THUMB_DIM, $height = AppConfig::MAX_THUMB_DIM)
    {
        try
        {
            $manager = MyImageManager::getManager();
            $image = $manager->read($imagePath);

            $image->scale($width, $height);
            $image->save($savePath);
            return true;
        }
        catch (\Exception $e)
        {
            Util::logException($e, 'ImageTransformer, rescale');
            return $e->getMessage();
        }
    }

    public function drawAnnotations($originalImgDims, $croppedImgPath, $annotations, $strokeColor = 'blue', $fillColor = 'transparent')
    {
        $manager = MyImageManager::getManager();
        $croppedImg = $manager->read($croppedImgPath);

        $annotations = is_array($annotations) ? $annotations : [$annotations];
        try
        {
            foreach ($annotations as $annotation)
            {
                $technique = isset($annotation['segmentation']) ? AppConfig::ANNOTATION_TECHNIQUES['POLYGON'] : AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX'];
                if ($technique == AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX']) {
                    $bbox = $this->pixelizeBbox($annotation, $originalImgDims[0], $originalImgDims[1]);
                    $bbox = $this->shiftBboxForCroppedImg($bbox, $croppedImg->width(), $croppedImg->height());
                    $borderSize = $this->calculateBorderSize($croppedImg->width(), $croppedImg->height());

                    $croppedImg->drawRectangle($bbox['x'], $bbox['y'], function (RectangleFactory $rectangle) use ($bbox, $fillColor, $strokeColor, $borderSize) {
                        $rectangle->size($bbox['width'], $bbox['height']);
                        $rectangle->background($fillColor);
                        $rectangle->border($strokeColor, $borderSize);
                    });
                    $croppedImg->save($croppedImgPath);

                } elseif ($technique == AppConfig::ANNOTATION_TECHNIQUES['POLYGON']) {
                    $segment = $this->pixelizePolygon($annotation['segmentation'], $originalImgDims[0], $originalImgDims[1]);
                    $segment = $this->shiftPolygonForCroppedImg($segment, $croppedImg->width(), $croppedImg->height());
                    $borderSize = $this->calculateBorderSize($croppedImg->width(), $croppedImg->height());
                    $croppedImg->drawPolygon(function (PolygonFactory $polygon) use ($segment, $strokeColor, $fillColor, $borderSize){
                        foreach ($segment as $point) {
                            $polygon->point($point['x'], $point['y']);
                        }
                        $polygon->background($fillColor);
                        $polygon->border($strokeColor, $borderSize); // border color and border width
                    });
                    $croppedImg->save($croppedImgPath);
                }
            }

            return Response::success();
        }
        catch(\Exception $e)
        {
            Util::logException($e, 'ImageTransofmer drawAnnotations');
            return Response::error($e->getMessage());
        }
    }

    private function calculateBorderSize($width, $height)
    {
        $baseSize = 2;
        $scalingFactor = 0.005;
        $maxBorderSize = 5;

        $averageDimension = ($width + $height) / 2;
        $borderSize = $baseSize + ($averageDimension * $scalingFactor);

        return min(round($borderSize), $maxBorderSize);
    }

}
