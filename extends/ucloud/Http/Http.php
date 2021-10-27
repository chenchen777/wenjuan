<?php
namespace UCloud\Http;
use UCloud\Utils\Error;
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/20
 * Time: 下午11:48
 */
class Http
{
//@results: $val
    public static function UCloud_Header_Get($header, $key)
    {
        $val = @$header[$key];
        if (isset($val)) {
            if (is_array($val)) {
                return $val[0];
            }
            return $val;
        } else {
            return '';
        }
    }

//@results: $error
    public static function UCloud_ResponseError($resp)
    {
        $header = $resp->Header;
        $err = new Error($resp->StatusCode, null, '');

        if ($err->Code > 299) {
            if ($resp->ContentLength !== 0) {
                if (Http::UCloud_Header_Get($header, 'Content-Type') === 'application/json') {
                    $ret = json_decode($resp->Body, true);
                    $err->ErrRet = $ret['ErrRet'];
                    $err->ErrMsg = $ret['ErrMsg'];
                }
            }
        }
        $err->Reqid = Http::UCloud_Header_Get($header, 'X-SessionId');
        return $err;
    }

// --------------------------------------------------------------------------------

//@results: ($resp, $error)
    public static function UCloud_Client_Do(Request $req)
    {
        $ch = curl_init();
        $url = $req->URL;
        $options = array(
            CURLOPT_USERAGENT => $req->UA,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false,
            CURLOPT_CUSTOMREQUEST => $req->METHOD,
            CURLOPT_URL => $url['host'] . "/" . rawurlencode($url['path']) . "?" . $req->EncodedQuery(),
            CURLOPT_TIMEOUT => $req->Timeout,
            CURLOPT_CONNECTTIMEOUT => $req->Timeout
        );
        if($req->METHOD=='HEAD'){
            $options[CURLOPT_NOBODY] = true;
        }

        $httpHeader = $req->Header;
        if (!empty($httpHeader)) {
            $header = array();
            foreach ($httpHeader as $key => $parsedUrlValue) {
                $header[] = "$key: $parsedUrlValue";
            }
            $options[CURLOPT_HTTPHEADER] = $header;
        }
        $body = $req->Body;
        if (!empty($body)) {
            $options[CURLOPT_POSTFIELDS] = $body;
        } else {
            $options[CURLOPT_POSTFIELDS] = "";
        }
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $ret = curl_errno($ch);
        if ($ret !== 0) {
            $err = new Error(0, $ret, curl_error($ch));
            curl_close($ch);
            return array(null, $err);
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        $responseArray = explode("\r\n\r\n", $result);
        $responseArraySize = sizeof($responseArray);
        $headerString = $responseArray[$responseArraySize - 2];
        $respBody = $responseArray[$responseArraySize - 1];

        $headers = Http::parseHeaders($headerString);
        $resp = new Response($code, $respBody,$req->Conf);
        $resp->Header = $headers;
        $err = null;
        if (floor($resp->StatusCode / 100) != 2) {
            list($r, $m) = Http::parseError($respBody);
            $err = new Error($resp->StatusCode, $r, $m);
        }
        return array($resp, $err);
    }

    public static function parseError($bodyString)
    {

        $r = 0;
        $m = '';
        $mp = json_decode($bodyString);
        if (isset($mp->{'ErrRet'})) $r = $mp->{'ErrRet'};
        if (isset($mp->{'ErrMsg'})) $m = $mp->{'ErrMsg'};
        return array($r, $m);
    }

    public static function parseHeaders($headerString)
    {

        $headers = explode("\r\n", $headerString);
        foreach ($headers as $header) {
            if (strstr($header, ":")) {
                $header = trim($header);
                list($k, $v) = explode(":", $header);
                $headers[$k] = trim($v);
            }
        }
        return $headers;
    }
}