<?php

namespace App\AnnotationHandler\traits;


use App\Utils\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait CoordsConvertor
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
        $pixelized_x = (float)$bbox['x'] * (float)$imgWidth;
        $pixelized_y = (float)$bbox['y'] * (float)$imgHeight;
        $pixelized_width = (float)$bbox['width'] * (float)$imgWidth;
        $pixelized_height = (float)$bbox['height'] * (float)$imgHeight;

        if ($coordsAreCentered) {
            $pixelized_x -= $pixelized_width / 2;
            $pixelized_y -= $pixelized_height / 2;
        }
        return [
            'x' => $pixelized_x,
            'y' => $pixelized_y,
            'width' => $pixelized_width,
            'height' => $pixelized_height
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

    /**
     * Clip coordinates to ensure they fall within image bounds
     *
     * @param array<array{x: float, y: float}> $coords Array of coordinate arrays with 'x' and 'y' keys
     * @param array{0: int, 1: int} $shape Image dimensions as [height, width]
     * @return array<array{x: float, y: float}> Clipped coordinates
     */
    private function clipCoords(array $coords, array $shape): array {
        return array_map(function($coord) use ($shape) {
            return [
                'x' => max(0, min($coord['x'], $shape[1])),  // Clip x between 0 and width
                'y' => max(0, min($coord['y'], $shape[0]))   // Clip y between 0 and height
            ];
        }, $coords);
    }

    /**
     * Rescale segment coordinates (xy) from img1_shape to img0_shape
     *
     * @param array{0: int, 1: int} $img1Shape The shape of the image that the coords are from
     * @param array<array{x: float, y: float}> $coords Array of coordinate arrays with 'x' and 'y' keys
     * @param array{0: int, 1: int} $img0Shape The shape of the image that the segmentation is being applied to
     * @param ?array{0: array{0: float}, 1: array{0: float, 1: float}} $ratioPad The ratio of the image size to the padded image size
     * @param bool $normalize Whether to normalize output coordinates to [0,1] range
     * @param bool $padding Whether to subtract YOLO-style padding from coordinates
     * @param bool $inputNormalized Whether input coordinates are already normalized (between 0 and 1)
     * @return array<array{x: float, y: float}> The scaled coordinates
     */
    public function scaleCoords(
        array $img1Shape,
        array $coords,
        array $img0Shape,
        ?array $ratioPad = null,
        bool $normalize = false,
        bool $padding = false,
        bool $inputNormalized = false
    ): array {
        // If input is normalized, denormalize to pixels first
        if ($inputNormalized) {
            $coords = $this->pixelizePolygon($coords, $img1Shape[1], $img1Shape[0]);
        }

        // Calculate ratio_pad if not provided
        if ($ratioPad === null) {
            $gain = min(
                $img1Shape[0] / $img0Shape[0],
                $img1Shape[1] / $img0Shape[1]
            );

            $pad = [
                ($img1Shape[1] - $img0Shape[1] * $gain) / 2,  // x padding
                ($img1Shape[0] - $img0Shape[0] * $gain) / 2   // y padding
            ];
        } else {
            $gain = $ratioPad[0][0];
            $pad = $ratioPad[1];
        }

        // Apply padding adjustments if needed
        if ($padding) {
            $coords = array_map(function($coord) use ($pad) {
                return [
                    'x' => $coord['x'] - $pad[0],
                    'y' => $coord['y'] - $pad[1]
                ];
            }, $coords);
        }

        // Apply gain scaling
        $coords = array_map(function($coord) use ($gain) {
            return [
                'x' => $coord['x'] / $gain,
                'y' => $coord['y'] / $gain
            ];
        }, $coords);

        // Clip coordinates
        $coords = $this->clipCoords($coords, $img0Shape);

        // Normalize if requested
        if ($normalize) {
            $coords = array_map(function($coord) use ($img0Shape) {
                return [
                    'x' => $coord['x'] / $img0Shape[1],
                    'y' => $coord['y'] / $img0Shape[0]
                ];
            }, $coords);
        }

        return $coords;
    }


    /**
     * Rescales coordinates from an original image to a cropped image.
     *
     * @param array $coords Array of coordinates with 'x' and 'y' keys. in Pixelized form
     * @param int $new_width Width of the cropped image.
     * @param int $new_height Height of the cropped image.
     * @return array Rescaled coordinates for the cropped image.
     */
    public function rescaleCoords(array $coords, int $new_width, int $new_height): array
    {
        // Calculate bounding box
        $x_min = min(array_column($coords, 'x'));
        $y_min = min(array_column($coords, 'y'));
        $x_max = max(array_column($coords, 'x'));
        $y_max = max(array_column($coords, 'y'));

        // Crop width and height
        $crop_width = $x_max - $x_min;
        $crop_height = $y_max - $y_min;

        // Calculate scale factors
        $scale_x = $new_width / $crop_width;
        $scale_y = $new_height / $crop_height;

        // Adjust coordinates for cropped image
        return array_map(function ($coord) use ($x_min, $y_min, $scale_x, $scale_y) {
            return [
                "x" => ($coord["x"] - $x_min) * $scale_x,
                "y" => ($coord["y"] - $y_min) * $scale_y
            ];
        }, $coords);
    }
}
