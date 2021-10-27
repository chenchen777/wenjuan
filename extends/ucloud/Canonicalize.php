<?php
namespace UCloud;


class Canonicalize
{
    // ----------------------------------------------------------
    public static function CanonicalizedResource($bucket, $key)
    {
        return "/" . $bucket . "/" . $key;
    }

    public static function CanonicalizedUCloudHeaders($headers)
    {

        $keys = array();
        foreach ($headers as $header) {
            $header = trim($header);
            $arr = explode(':', $header);
            if (count($arr) < 2) continue;
            list($k, $v) = $arr;
            $k = strtolower($k);
            if (strncasecmp($k, "x-ucloud",strlen($k)) === 0) {
                $keys[] = $k;
            }
        }

        $c = '';
        sort($keys, SORT_STRING);
        foreach ($keys as $k=>$v) {
            $c .= $k . ":" . trim($headers[$v], " ") . "\n";
        }
        return $c;
    }

    public static function makeAuth($auth,$conf,$type=null)
    {
        if (isset($auth)) {
            return $auth;
        }
        return new Auth($conf->UCLOUD_PUBLIC_KEY, $conf->UCLOUD_PRIVATE_KEY);
    }

    //@results: token
    public static function signRequest($auth, $req, $conf,$type = HEAD_FIELD_CHECK)
    {
        return self::makeAuth($auth,$conf)->SignRequest($req, $type);
    }

// ----------------------------------------------------------
}




