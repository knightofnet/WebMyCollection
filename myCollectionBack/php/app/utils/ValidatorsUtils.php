<?php

namespace MyCollection\app\utils;

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

    public static function isValidInt(string $value, int $min = null, int $max = null, bool $throwException = false, string $msgIfException = null): bool
    {
        $isOk = true;
        if (!is_numeric($value) || !is_int($value + 0)) {
            $isOk = false;
        }

        if ($isOk) {


            $value = intval($value);

            if ($min !== null && $value < $min) {
                $isOk = false;
            }

            if ($isOk && $max !== null && $value > $max) {
                $isOk = false;
            }
        }

        if (!$isOk && $throwException) {
            if ($msgIfException !== null) {
                throw new \InvalidArgumentException('isValidInt: '.$msgIfException);
            }
            throw new \InvalidArgumentException('isValidInt: La valeur doit être un entier valide.');
        }

        return $isOk;
    }

    public static function existsInArray(string $needle, array $array) : bool
    {
        return array_key_exists($needle, $array);
    }

    public static function allExistsInArray(array $needles, array $datas, bool $throwException = false, string $msgIfException = null) : bool
    {
        $returnBool = true;
        $keyNotExist = '';
        foreach ($needles as $needle) {
            if (!self::existsInArray($needle, $datas)) {
                $returnBool = false;
                $keyNotExist = $needle;
                break;

            }
        }

        if (!$returnBool && $throwException) {
            if ($msgIfException !== null) {
                throw new \InvalidArgumentException('allExistsInArray: '.$msgIfException);
            }
            throw new \InvalidArgumentException('allExistsInArray: '.$keyNotExist.' n\'existe pas dans le tableau.');
        }

        return $returnBool;
    }

    public static function isNotEmptyString(string $string, bool $throwException = false, string $msgIfException = null) : bool
    {
        $isValid = !empty($string);

        if (!$isValid && $throwException) {
            if ($msgIfException !== null) {
                throw new \InvalidArgumentException('isNotEmptyString: '.$msgIfException);
            }
            throw new \InvalidArgumentException('isNotEmptyString: La chaîne de caractères ne peut pas être vide.');
        }

        return $isValid;
    }

    public static function isStringLengthLessThan(string $string, int $length, bool $throwException = false, string $msgIfException = null) : bool
    {
        $isValid = strlen($string) < $length;

        if (!$isValid && $throwException) {
            if ($msgIfException !== null) {
                throw new \InvalidArgumentException('isStringLengthLessThan: '.$msgIfException);
            }
            throw new \InvalidArgumentException('isStringLengthLessThan: La chaîne de caractères ne peut pas dépasser '.$length.' caractères.');
        }

        return $isValid;
    }

    public static function uriIsValidAndRespond(string $url, bool $throwException = false, string $msgIfException = null) : bool
    {
        $isValid = filter_var($url, FILTER_VALIDATE_URL) !== false;

        if (!$isValid && $throwException) {
            if ($msgIfException !== null) {
                throw new \InvalidArgumentException('uriIsValidAndRespond: '.$msgIfException);
            }
            throw new \InvalidArgumentException('uriIsValidAndRespond: L\'URL n\'est pas valide.');
        }

        return $isValid;
    }

}