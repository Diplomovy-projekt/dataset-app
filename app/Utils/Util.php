<?php

namespace App\Utils;

use App\Configs\AppConfig;
use App\Models\Dataset;
use App\Traits\CoordsTransformer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class Util
{
    use CoordsTransformer;
    public static function isJson($data) {

        if (!is_string($data)) {
            return false;
        }
        json_decode($data);

        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function generateDistinctColors(array $classes): array {
        $colors = [];
        $goldenRatio = 0.618033988749895;
        $hue = (float)mt_rand() / (float)mt_getrandmax();

        for ($i = 0; $i < count($classes); $i++) {
            $hue = fmod($hue + $goldenRatio, 1.0);
            $saturation = 0.8 + (mt_rand() / mt_getrandmax()) * 0.2;
            $value = 0.9 + (mt_rand() / mt_getrandmax()) * 0.1;

            // Just return the RGB array directly
            $rgb = self::hsvToRgb($hue, $saturation, $value);
            $colors[$classes[$i]] = "rgb($rgb[0], $rgb[1], $rgb[2])";
        }

        return $colors;
    }

    public static function hsvToRgb($h, $s, $v) {
        $h_i = floor($h * 6);
        $f = $h * 6 - $h_i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        $rgb = [];
        switch($h_i) {
            case 0:
                $rgb = [$v, $t, $p];
                break;
            case 1:
                $rgb = [$q, $v, $p];
                break;
            case 2:
                $rgb = [$p, $v, $t];
                break;
            case 3:
                $rgb = [$p, $q, $v];
                break;
            case 4:
                $rgb = [$t, $p, $v];
                break;
            default:
                $rgb = [$v, $p, $q];
                break;
        }

        return [
            floor($rgb[0] * 255),
            floor($rgb[1] * 255),
            floor($rgb[2] * 255)
        ];
    }

    public static function constructImagePath($datasetUniqueName, $filename, $folder = AppConfig::IMG_THUMB_FOLDER) {
        return AppConfig::LINK_DATASETS_PATH . $datasetUniqueName . "/" . $folder . $filename;
    }

    public static function formatNumber(float $value, int $decimals = 2): float|int
    {
        return ($value == floor($value)) ? (int) $value : round($value, $decimals);
    }

    protected static $startTime = [];

    public static function logStart($message)
    {
        self::$startTime[$message] = microtime(true);
        Log::channel('timing')->info("Start: $message");
    }

    public static function logEnd($message)
    {
        if (!isset(self::$startTime[$message])) {
            Log::channel('timing')->error("End timing not started for: $message");
            return;
        }

        $endTime = microtime(true);
        $duration = $endTime - self::$startTime[$message];

        Log::channel('timing')->info("End: $message | Duration: " . number_format($duration, 4) . " seconds");
        unset(self::$startTime[$message]);
    }

    public static function getDatasetPath(Dataset|string $dataset, $absolute = false): string {
        if (is_string($dataset)) {
            $dataset = Dataset::where('unique_name', $dataset)->orWhere('id', $dataset)->firstOrFail();
        }

        $path = ($dataset->is_public ? AppConfig::DATASETS_PATH['public'] : AppConfig::DATASETS_PATH['private'])
            . $dataset->unique_name . '/';

        if ($absolute) {
            return Storage::path($path);
        }

        return $path;
    }

    public static function getImageSizeStats(array $ids, bool $isImageId = false): array
    {
        if(empty($ids)) {
            return [
                'median' => '0x0',
                'min' => '0x0',
                'max' => '0x0',
            ];
        }

        $query = DB::table('images')
            ->select('width', 'height');

        if ($isImageId) {
            $query->whereIn('id', $ids);
        } else {
            $query->whereIn('dataset_id', $ids);
        }

        $sizes = $query->get();

        if ($sizes->isEmpty()) {
            return [
                'median' => '0x0',
                'min' => '0x0',
                'max' => '0x0',
            ];
        }

        $widths = $sizes->pluck('width')->sort()->values()->all();
        $heights = $sizes->pluck('height')->sort()->values()->all();

        $medianWidth = self::calculateMedian($widths);
        $medianHeight = self::calculateMedian($heights);

        return [
            'median' => $medianWidth . 'x' . $medianHeight,
            'min' => min($widths) . 'x' . min($heights),
            'max' => max($widths) . 'x' . max($heights),
        ];
    }

    private static function calculateMedian(array $values): int
    {
        $count = count($values);
        if ($count === 0) return 0;

        $middle = (int) floor($count / 2);

        return ($count % 2)
            ? $values[$middle]
            : (int) (($values[$middle - 1] + $values[$middle]) / 2);
    }

    public static function generateSvgPath(array $annotation, int $imageWidth, int $imageHeight): string
    {
        if (!empty($annotation['segmentation'])) {
            $segmentation = self::pixelizePolygon($annotation['segmentation'], $imageWidth, $imageHeight);
            $pathData = 'M' . $segmentation[0]['x'] . ',' . $segmentation[0]['y'];

            $prevX = $segmentation[0]['x'];
            $prevY = $segmentation[0]['y'];

            for ($i = 1; $i < count($segmentation); $i++) {
                $dx = $segmentation[$i]['x'] - $prevX;
                $dy = $segmentation[$i]['y'] - $prevY;
                $pathData .= 'l' . $dx . ',' . $dy;

                $prevX = $segmentation[$i]['x'];
                $prevY = $segmentation[$i]['y'];
            }

            return $pathData . 'z';
        }

        $pixelizedBbox = self::pixelizeBbox($annotation, $imageWidth, $imageHeight);
        $x = $pixelizedBbox['x'];
        $y = $pixelizedBbox['y'];
        $w = $pixelizedBbox['width'];
        $h = $pixelizedBbox['height'];

        return "M{$x},{$y}l{$w},0l0,{$h}l-{$w},0z";
    }

    public static function logException(Throwable $e, string $context = ''): void
    {
        Log::error('Exception caught' . ($context ? " in $context" : ''), [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);
    }

}
