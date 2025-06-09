<?php

namespace MyCollection\app\utils;

use MyCollection\app\dto\IToArray;

class AppUtils
{
    /**
     * Vérifie si les clés d'un tableau sont présentes dans un autre tableau
     *
     * @param array $arraySource
     * @param array $keysArray
     * @return bool
     */
    public static function issetArray(array $arraySource, array $keysArray): bool
    {
        foreach ($keysArray as $key) {
            if (!isset($arraySource[$key])) {
                return false;
            }
        }
        return true;
    }

    public static function toArrayIToArray(array $retArray, $filteredKeys = []) : array
    {
        $finalArray = [];

        foreach ($retArray as $key => $value) {


            if (in_array($key, $filteredKeys, true)) {
                continue; // Skip keys that are in the filtered list
            }


            if ($value instanceof IToArray) {
                $finalArray[$key] = $value->toArray();
                $finalArray[$key] = self::toArrayIToArray($finalArray[$key], $filteredKeys);
            } else if (is_array($value)) {
                $finalArray[$key] = self::toArrayIToArray($value, $filteredKeys);
            } else {
                $finalArray[$key] = $value;
            }


        }

        return $finalArray;
    }

    public static function strToKebabCase(string $string, bool $isStripSpecCars = false) : string
    {
        if ($isStripSpecCars) {
            $string = preg_replace('/[^a-zA-Z0-9\s]/', '', $string);
        }

        $string = strtolower(trim($string));
        $string = preg_replace('/\s+/', '-', $string);
        return $string;
    }
}