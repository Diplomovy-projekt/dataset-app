<?php

namespace App\Traits;


use App\Utils\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait CoordsTransformer
{

    public static function normalizeBbox($bbox, $imgWidth, $imgHeight, $centerCoords = false)
    {
        $normalized_x = (float)$bbox['x'] / (float)$imgWidth;
        $normalized_y = (float)$bbox['y'] / (float)$imgHeight;
        $normalized_width = (float)$bbox['width'] / (float)$imgWidth;
        $normalized_height = (float)$bbox['height'] / (float)$imgHeight;

        if ($centerCoords) {
            $normalized_x += $normalized_width / 2;
            $normalized_y += $normalized_height / 2;
        }
        return [
            'x' => $normalized_x,
            'y' => $normalized_y,
            'width' => $normalized_width,
            'height' => $normalized_height
        ];

    }

    public function normalizePolygon($segment, $imgWidth, $imgHeight)
    {
        $segment = Helper::isJson($segment) ? json_decode($segment, true) : $segment;
        $normalizedPolygon = [];

        // Do this if $segment is our internal structure [['x' => x1, 'y' => y1], ...]
        if (isset($segment[0]['x']) && isset($segment[0]['y'])) {
            foreach ($segment as $point) {
                $normalizedPolygon[] = [
                    'x' => (float)$point['x'] / (float)$imgWidth,
                    'y' => (float)$point['y'] / (float)$imgHeight
                ];
            }
        } else {
            // Split the polygon array into chunks of 2 (x, y pairs)
            foreach (array_chunk($segment, 2) as $pair) {
                // Normalize each x, y pair
                $normalizedPolygon[] = [
                    'x' => (float)$pair[0] / (float)$imgWidth,
                    'y' => (float)$pair[1] / (float)$imgHeight
                ];
            }
        }

        return $normalizedPolygon;
    }

    public function pixelizeBbox($bbox, $imgWidth, $imgHeight, $coordsAreCentered = false)
    {
        if($bbox instanceof Model || $bbox instanceof Collection){
            $bbox = $bbox->toArray();
        }
        $pixelized_x = (float)$bbox['x'] * (float)$imgWidth;
        $pixelized_y = (float)$bbox['y'] * (float)$imgHeight;
        $pixelized_width = (float)$bbox['width'] * (float)$imgWidth;
        $pixelized_height = (float)$bbox['height'] * (float)$imgHeight;

        if ($coordsAreCentered) {
            $pixelized_x -= $pixelized_width / 2;
            $pixelized_y -= $pixelized_height / 2;
        }
        return [
            'x' => round($pixelized_x, 1),
            'y' => round($pixelized_y, 1),
            'width' => round($pixelized_width, 1),
            'height' => round($pixelized_height, 1)
        ];
    }

    public function pixelizePolygon($segment, $imgWidth, $imgHeight): array
    {
        if ($segment instanceof Collection) {
            $segment = $segment->toArray();
        } elseif ($segment instanceof Model) {
            $segment = $segment->segmentation;
        }
        $segment = Helper::isJson($segment) ? json_decode($segment, true) : $segment;

        $pixelizedPolygon = [];

        // Do this if $segment is our internal structure [['x' => x1, 'y' => y1], ...]
        if (isset($segment[0]['x']) && isset($segment[0]['y'])) {
            foreach ($segment as $point) {
                $pixelizedPolygon[] = [
                    'x' => (float)$point['x'] * (float)$imgWidth,
                    'y' => (float)$point['y'] * (float)$imgHeight
                ];
            }
        } else {
            // Split the polygon array into chunks of 2 (x, y pairs)
            foreach (array_chunk($segment, 2) as $pair) {
                // Normalize each x, y pair
                $pixelizedPolygon[] = [
                    'x' => (float)$pair[0] * (float)$imgWidth,
                    'y' => (float)$pair[1] * (float)$imgHeight
                ];
            }
        }

        return $pixelizedPolygon;
    }

    public function shiftPolygonForCroppedImg(array $polygon, int $cropImgWidth, int $cropImgHeight): array
    {
        // Calculate bounding box minimum coordinates
        $x_min = min(array_column($polygon, 'x'));
        $y_min = min(array_column($polygon, 'y'));

        // Calculate bbox dimensions directly from min/max
        $bboxWidth = max(array_column($polygon, 'x')) - $x_min;
        $bboxHeight = max(array_column($polygon, 'y')) - $y_min;

        // Calculate padding to center the bbox in the cropped image
        $paddingX = ($cropImgWidth - $bboxWidth) / 2;
        $paddingY = ($cropImgHeight - $bboxHeight) / 2;

        // Translate coordinates relative to bbox origin and add padding
        return array_map(function ($coord) use ($x_min, $y_min, $paddingX, $paddingY) {
            return [
                "x" => $coord["x"] - $x_min + $paddingX,
                "y" => $coord["y"] - $y_min + $paddingY
            ];
        }, $polygon);
    }

    public function shiftBBoxForCroppedImg(array $bbox, int $cropImgWidth, int $cropImgHeight): array
    {
        $paddingX = ($cropImgWidth - $bbox['width']) ;
        $paddingY = ($cropImgHeight - $bbox['height']);

        // If no padding was applied, no shifting is needed
        if($paddingX == 0){
            // We add slight padding so the annotation is not on the edge of the image
            return [
                'x' => 1,
                'y' =>  1,
                'height' => $bbox['height'] - 2,
                'width' => $bbox['width'] - 2
            ];
        }

        return [
            'x' => $paddingX / 2,
            'y' =>  $paddingY / 2,
            'height' => $bbox['height'],
            'width' => $bbox['width']
        ];
    }

}
