<?php

namespace Enigmas\app\utils;

class ValidatorsUtils
{

    private function __construct() {}

    public static function isValidNumber(string $value, $min = null, $max = null): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        $value = (float)$value;


        if ($min !== null && $value < $min) {
            return false;
        }

        if ($max !== null && $value > $max) {
            return false;
        }

        return true;
    }

    public static function isValidInt(string $value, int $min = null, int $max = null): bool
    {
        if (!is_numeric($value) || !is_int($value + 0)) {
            return false;
        }

        $value = intval($value);

        if ($min !== null && $value < $min) {
            return false;
        }

        if ($max !== null && $value > $max) {
            return false;
        }

        return true;
    }

    public static function existsInArray(string $needle, array $array) : bool
    {
        return array_key_exists($needle, $array);
    }

    public static function allExistsInArray(array $array, $prop) : bool
    {
        foreach ($array as $item) {
            if (!self::existsInArray($item, $prop)) {
                return false;
            }
        }
        return true;
    }

}