<?php

namespace App\Utils;

class Util
{
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

}
