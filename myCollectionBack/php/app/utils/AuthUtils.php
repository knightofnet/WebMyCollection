<?php

namespace MyCollection\app\utils;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use MiniPhpRest\core\exception\DirectResponseException;

class AuthUtils
{

    public const AUTH_COOKIE_NAME = 'auth_token';

    public const AUTH_COOKIE_EXPIRATION = 3600 * 24 * 31; // 31 days

    public static function generateNewToken(int $len = 32): string
    {
        return bin2hex(random_bytes($len));
    }

    public static function encodeJwtPayload(array $payload): string
    {
        return JWT::encode($payload, SITE_HASH, 'HS256');
    }

    public static function setAuthCookie(string $token): void
    {
        setcookie(
            self::AUTH_COOKIE_NAME
            , $token, [
            'expires' => time() + self::AUTH_COOKIE_EXPIRATION,
            'path' => '/',
            'secure' => true,       // HTTPS uniquement
            'httponly' => true,     // Protège contre XSS
            'samesite' => 'Strict', // Bloque CSRF
        ]);


    }

    /**
     * @throws DirectResponseException
     */
    public static function verifyTokenByCookieAndReturnProp(string $prop, bool $isThrowDirectResponseException = false): ?string
    {
        $payload = self::verifyTokenByCookieAndReturnPayload($isThrowDirectResponseException);
        if ($payload && isset($payload->$prop)) {
            return $payload->$prop;
        }

        if ($isThrowDirectResponseException) {
            throw new DirectResponseException(401, "Unauthorized");
        }
        return null;
    }

    /**
     * @param bool $isThrowDirectResponseException
     * @return \stdClass|null
     * @throws DirectResponseException
     */
    public static function verifyTokenByCookieAndReturnPayload(bool $isThrowDirectResponseException = false): ?\stdClass
    {
        if (!isset($_COOKIE[self::AUTH_COOKIE_NAME])) {
            if ($isThrowDirectResponseException) {
                throw new DirectResponseException(401, "Unauthorized");
            }
            return null;
        }

        try {
            return JWT::decode($_COOKIE[self::AUTH_COOKIE_NAME], new Key(SITE_HASH, 'HS256'));


        } catch (Exception $e) {
            if ($isThrowDirectResponseException) {
                throw new DirectResponseException("Unauthorized", 401);
            }
            return null;

        }


    }

    public function deleteAuthCookie(): void
    {
        setcookie(
            self::AUTH_COOKIE_NAME,
            '',
            [
                'expires' => time() - 3600, // Expire immédiatement
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
            ]
        );
    }


}