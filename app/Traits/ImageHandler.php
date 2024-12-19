<?php

namespace App\Traits;

use App\AnnotationHandler\traits\CoordsConvertor;
use App\Utils\AppConstants;
use App\Utils\Response;
use Illuminate\Support\Facades\App;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Geometry\Factories\PolygonFactory;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\ImageManager;

trait ImageHandler
{
    use CoordsConvertor;
    public static function crop($imagePath,$savePath, $x, $y, $width, $height)
    {
        try
        {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($imagePath);

            $width = $width * $image->width();
            $height = $height * $image->height();
            $x = $x * $image->width();
            $y = $y * $image->height();

            $image->crop($width, $height, $x, $y);
            $image->save($savePath);
            return true;
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }

    public static function rescale($imagePath, $savePath, $width = 256, $height = 256)
    {
        try
        {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($imagePath);

            $image->scaleDown($width, $height);
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
                    $bbox = $this->pixelizeBbox($annotation, $image->width(), $image->height());
                    $image->drawRectangle($bbox['x'], $bbox['y'], function (RectangleFactory $rectangle) use ($bbox, $fillColor, $strokeColor) {
                        $rectangle->size($bbox['width'], $bbox['height']);
                        $rectangle->background($fillColor);
                        $rectangle->border($strokeColor, 2);
                    });
                } elseif ($technique == AppConstants::ANNOTATION_TECHNIQUES['POLYGON']) {
                    $segment = $this->pixelizePolygon($annotation['segmentation'], $originalImgDims[0], $originalImgDims[1]);

                    $segment = $this->rescaleCoords($segment, $image->width(), $image->height());
                    $image->drawPolygon(function (PolygonFactory $polygon) use ($segment, $strokeColor, $fillColor){
                        foreach ($segment as $point) {
                            $polygon->point($point['x'], $point['y']);
                        }
                        $polygon->background($fillColor);
                        $polygon->border($strokeColor, 2); // border color and border width
                    });
                    $image->save($imagePath);

                } else {
                    return Response::error("Invalid annotation technique");
                }
            }

            return Response::success("Annotation drawn successfully");
        }
        catch(\Exception $e)
        {
            return Response::error($e->getMessage());
        }
    }
}
