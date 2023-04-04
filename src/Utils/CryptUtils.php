<?php

namespace App\Utils;

class CryptUtils
{

    private const AES_KEY = "fkdoklqlkkj44545et7gg0e1g1e2";
    private const AES_KEY_ID = "fkdoklqlffffzzzzzfkkj44545et7gg0e1g1e2";

    public static function decrypt($val)
    {
        if ($val == null || empty($val)) {
            return $val;
        }
        $val = base64_decode($val);
        $iv = "";
        $ch = openssl_decrypt($val, 'AES-256-ECB', self::AES_KEY, 0, $iv);
        return $ch;
    }

    public static function crypt($val)
    {
        $iv = "";
        $encrypted = openssl_encrypt($val, 'AES-256-ECB', self::AES_KEY, 0, $iv);
        return base64_encode($encrypted);
    }

    /**
     *
     * @param string $val
     * @return string
     */
    public static function decryptId($val)
    {
        if ($val == null || empty($val)) {
            return $val;
        }
        $val = base64_decode($val);
        $iv = "";
        $ch = openssl_decrypt($val, 'AES-256-ECB', self::AES_KEY_ID, 0, $iv);
        return $ch;
    }

    public static function cryptId($val)
    {
        $iv = "";
        $encrypted = openssl_encrypt($val, 'AES-256-ECB', self::AES_KEY_ID, 0, $iv);
        return base64_encode($encrypted);
    }
}
