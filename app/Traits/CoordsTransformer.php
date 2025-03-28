<?php

namespace App\Traits;


use App\Utils\Util;
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
        $segment = Util::isJson($segment) ? json_decode($segment, true) : $segment;
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

    public static function pixelizeBbox($bbox, $imgWidth, $imgHeight, $coordsAreCentered = false)
    {
        if($bbox instanceof Model || $bbox instanceof Collection){
            $bbox = $bbox->toArray();
        }
        $imgWidth = (float)$imgWidth;
        $imgHeight = (float)$imgHeight;

        $pixelized_x = (float)$bbox['x'] * $imgWidth;
        $pixelized_y = (float)$bbox['y'] * $imgHeight;
        $pixelized_width = (float)$bbox['width'] * $imgWidth;
        $pixelized_height = (float)$bbox['height'] * $imgHeight;

        if ($coordsAreCentered) {
            $pixelized_x -= $pixelized_width / 2;
            $pixelized_y -= $pixelized_height / 2;
        }
        return [
            'x' => (int)$pixelized_x,
            'y' => (int)$pixelized_y,
            'width' => (int)$pixelized_width,
            'height' => (int)$pixelized_height
        ];
    }

    public static function pixelizePolygon($segment, $imgWidth, $imgHeight): array
    {
        if ($segment instanceof Collection) {
            $segment = $segment->toArray();
        } elseif ($segment instanceof Model) {
            $segment = $segment->segmentation;
        }
        if(Util::isJson($segment)){
            $segment = json_decode($segment, true);
        }

        $pixelizedPolygon = [];

        $imgWidth = (float)$imgWidth;
        $imgHeight = (float)$imgHeight;

        // Do this if $segment is our internal structure [['x' => x1, 'y' => y1], ...]
        if (isset($segment[0]['x']) && isset($segment[0]['y'])) {
            $count = count($segment);
            $pixelizedPolygon = array_fill(0, $count, []);

            for ($i = 0; $i < $count; $i++) {
                $pixelizedPolygon[$i] = [
                    'x' => (int)($segment[$i]['x'] * $imgWidth),
                    'y' => (int)($segment[$i]['y'] * $imgHeight)
                ];
            }
        } else {
            $count = count($segment);
            if ($count % 2 == 0) { // Ensure we have pairs
                $pairs = $count / 2;
                $pixelizedPolygon = array_fill(0, $pairs, []);

                for ($i = 0, $j = 0; $i < $count; $i += 2, $j++) {
                    $pixelizedPolygon[$j] = [
                        'x' => (int)($segment[$i] * $imgWidth),
                        'y' => (int)($segment[$i + 1] * $imgHeight)
                    ];
                }
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
