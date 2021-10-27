<?php
namespace UCloud\Http;

use UCloud\Conf;
use UCloud\Utils\Error;

/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/20
 * Time: 下午11:46
 */
class HttpClient
{
    //@results: ($resp, $error)
    public function RoundTrip($req, $type)
    {
        return Http::UCloud_Client_Do($req);
    }

    //@results: ($data, $error)
    public function UCloud_Client_Ret($resp)
    {
        $code = $resp->StatusCode;
        $data = null;
        if ($code >= 200 && $code <= 299) {
            if ($resp->ContentLength !== 0 && Http::UCloud_Header_Get($resp->Header, 'Content-Type') == 'application/json') {
                $data = json_decode($resp->Body, true);
                if ($data === null) {
                    $err = new Error($code, 0, "");
                    return array(null, $err);
                }
            }
        }

        $etag = Http::UCloud_Header_Get($resp->Header, 'ETag');
        $content_type = Http::UCloud_Header_Get($resp->Header, 'Content-Type');
        $content_length = Http::UCloud_Header_Get($resp->Header, 'Content-Length');
        if ($etag != '') $data['ETag'] = $etag;
        if ($content_type != '') $data['ContentType'] = $content_type;
        if ($content_length != '') $data['ContentLength'] = $content_length;
        if (floor($code / 100) == 2) {
            return array($data, null);
        }
        return array($data, Http::UCloud_ResponseError($resp));
    }

//@results: ($data, $error)
    public function UCloud_Client_Call($req, $type = HEAD_FIELD_CHECK)
    {
        list($resp, $err) = $this->RoundTrip($req, $type);
        if ($err !== null) {
            return array(null, $err);
        }
        return $this->UCloud_Client_Ret($resp);
    }

//@results: $error
    public function UCloud_Client_CallNoRet($req, $type = HEAD_FIELD_CHECK)
    {
        list($resp, $err) = $this->RoundTrip($req, $type);
        if ($err !== null) {
            return array(null, $err);
        }
        if (floor($resp->StatusCode / 100) == 2) {
            return null;
        }
        return Http::UCloud_ResponseError($resp);
    }

//@results: ($data, $error)
    public function UCloud_Client_CallWithForm($req, $body, $contentType = 'application/x-www-form-urlencoded')
    {
        if ($contentType === 'application/x-www-form-urlencoded') {
            if (is_array($req->Params)) {
                $body = http_build_query($req->Params);
            }
        }
        if ($contentType !== 'multipart/form-data') {
            $req->Header['Content-Type'] = $contentType;
        }
        $req->Body = $body;
        list($resp, $err) = $this->RoundTrip($req, HEAD_FIELD_CHECK);
        if ($err !== null) {
            return array(null, $err);
        }
        return $this->UCloud_Client_Ret($resp);
    }

// --------------------------------------------------------------------------------

    public function UCloud_Client_CallWithMultipartForm($req, $fields, $files)
    {
        list($contentType, $body) = $this->UCloud_Build_MultipartForm($fields, $files);
        return $this->UCloud_Client_CallWithForm($req, $body, $contentType);
    }

//@results: ($contentType, $body)
    public function UCloud_Build_MultipartForm($fields, $files)
    {
        $data = array();
        $boundary = md5(microtime());

        foreach ($fields as $name => $val) {
            array_push($data, '--' . $boundary);
            array_push($data, "Content-Disposition: form-data; name=\"$name\"");
            array_push($data, '');
            array_push($data, $val);
        }

        foreach ($files as $file) {
            array_push($data, '--' . $boundary);
            list($name, $fileName, $fileBody, $mimeType) = $file;
            $mimeType = empty($mimeType) ? 'application/octet-stream' : $mimeType;
            $fileName = self::UCloud_EscapeQuotes($fileName);
            array_push($data, "Content-Disposition: form-data; name=\"$name\"; filename=\"$fileName\"");
            array_push($data, "Content-Type: $mimeType");
            array_push($data, '');
            array_push($data, $fileBody);
        }

        array_push($data, '--' . $boundary . '--');
        array_push($data, '');

        $body = implode("\r\n", $data);
        $contentType = 'multipart/form-data; boundary=' . $boundary;
        return array($contentType, $body);
    }

    public static function UCloud_UserAgent()
    {
        $SDK_VER = Conf::SDK_VER;
        $sdkInfo = "UCloudPHP/$SDK_VER";

        $systemInfo = php_uname("s");
        $machineInfo = php_uname("m");

        $envInfo = "($systemInfo/$machineInfo)";

        $phpVer = phpversion();

        $ua = "$sdkInfo $envInfo PHP/$phpVer";
        return $ua;
    }

    public function UCloud_EscapeQuotes($str)
    {
        $find = array("\\", "\"");
        $replace = array("\\\\", "\\\"");
        return str_replace($find, $replace, $str);
    }

}