<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/24
 * Time: 下午3:09
 */

namespace UCloud;

use common\component\Params;
use UCloud\Http\HttpClient;
use UCloud\Utils\ActionType;
use UCloud\Utils\Util;
use UCloud\Utils\Error;
use UCloud\Http\Request;
use UCloud\Http\AuthHttpClient;

class Proxy
{
    public function __construct(Conf $conf=null)
    {
        if(empty($conf)){
            $this->conf = new Conf(Params::getParams('UCloud_API',[]));
        }else {
            $this->conf = $conf;
        }
    }

    public function meta($bucket,$key)
    {

        $action_type = ActionType::NONE;
        $err = Util::CheckConfig(ActionType::NONE,$this->conf);
        if ($err != null) {
            return array(null, $err);
        }

        //global $UCLOUD_PROXY_SUFFIX;
        $UCLOUD_PROXY_SUFFIX = $this->conf->UCLOUD_PROXY_SUFFIX;
        $host = $bucket . $UCLOUD_PROXY_SUFFIX;
        $path = $key;

        $req = new Request('HEAD', array('host'=>$host, 'path'=>$path), null, $bucket, $key, $this->conf,$action_type);

        $client = new AuthHttpClient(null,$this->conf, null);
        list($data, $err) = $client->UCloud_Client_Call($req);
        return array($data, $err);
    }

//------------------------------普通上传------------------------------
    public function UCloud_PutFile($bucket, $key, $file, $filename)
    {
        $action_type = ActionType::PUTFILE;
        $err = Util::CheckConfig(ActionType::PUTFILE,$this->conf);
        if ($err != null) {
            return array(null, $err);
        }

        $f = @fopen($file, "r");
        if (!$f) return array(null, new Error(-1, -1, "open $file error"));

        //global $UCLOUD_PROXY_SUFFIX;
        $UCLOUD_PROXY_SUFFIX = $this->conf->UCLOUD_PROXY_SUFFIX;
        $host = $bucket . $UCLOUD_PROXY_SUFFIX;
        $path = $key;
        $content  = @fread($f, filesize($file));
        list($mimetype, $err) = Util::GetFileMimeType($filename);
        if ($err) {
            fclose($f);
            return array("", $err);
        }
        $req = new Request('PUT', array('host'=>$host, 'path'=>$path), $content, $bucket, $key, $this->conf,$action_type);
        $req->Header['Expect'] = '';
        $req->Header['Content-Type'] = $mimetype;
        $client = new AuthHttpClient(null,$this->conf, $mimetype);
        list($data, $err) = $client->UCloud_Client_Call($req);
        fclose($f);
        return array($data, $err);
    }

//------------------------------表单上传------------------------------
    public function UCloud_MultipartForm($bucket, $key, $file)
    {
        $action_type = ActionType::POSTFILE;
        $err = Util::CheckConfig(ActionType::POSTFILE,$this->conf);
        if ($err != null) {
            return array(null, $err);
        }

        $f = @fopen($file, "r");
        if (!$f) return array(null, new Error(-1, -1, "open $file error"));

        //global $UCLOUD_PROXY_SUFFIX;
        $UCLOUD_PROXY_SUFFIX = $this->conf->UCLOUD_PROXY_SUFFIX;
        $host = $bucket . $UCLOUD_PROXY_SUFFIX;
        $path = "";
        $fsize = filesize($file);
        $content = "";
        if ($fsize != 0) {
            $content = @fread($f, filesize($file));
            if ($content == FALSE) {
                fclose($f);
                return array(null, new Error(0, -1, "read file error"));
            }
        }
        list($mimetype, $err) = Util::GetFileMimeType($file);
        if ($err) {
            fclose($f);
            return array("", $err);
        }

        $req = new Request('POST', array('host'=>$host, 'path'=>$path), $content, $bucket, $key, $this->conf,$action_type);
        $req->Header['Expect'] = '';
        $auth = new Auth($this->conf->UCLOUD_PUBLIC_KEY,$this->conf->UCLOUD_PRIVATE_KEY);
        $token = $auth->SignRequest(null, $req, $mimetype);

        $fields = array('Authorization'=>$token, 'FileName' => $key);
        $files  = array('files'=>array('file', $file, $content, $mimetype));

        $client = new AuthHttpClient(null,$this->conf, NO_AUTH_CHECK);
        list($data, $err) = $client->UCloud_Client_CallWithMultipartForm($req, $fields, $files);
        fclose($f);
        return array($data, $err);
    }

//------------------------------分片上传------------------------------
    public function UCloud_MInit($bucket, $key)
    {

        $err = Util::CheckConfig(ActionType::MINIT,$this->conf);
        if ($err != null) {
            return array(null, $err);
        }

        //global $UCLOUD_PROXY_SUFFIX;
        $UCLOUD_PROXY_SUFFIX = $this->conf->UCLOUD_PROXY_SUFFIX;
        $host = $bucket . $UCLOUD_PROXY_SUFFIX;
        $path = $key;
        $querys = array(
            "uploads" => ""
        );
        $req = new Request('POST', array('host'=>$host, 'path'=>$path, 'query'=>$querys), null, $bucket, $key,$this->conf);
        $req->Header['Content-Type'] = 'application/x-www-form-urlencoded';

        $client = new AuthHttpClient(null,$this->conf);
        return $client->UCloud_Client_Call($req);
    }

//@results: (tagList, err)
    public function UCloud_MUpload($bucket, $key, $file, $uploadId, $blkSize, $partNumber=0, $filename)
    {

        $err = Util::CheckConfig(ActionType::MUPLOAD,$this->conf);
        if ($err != null) {
            return array(null, $err);
        }

        $f = @fopen($file, "r");
        if (!$f) return array(null, new Error(-1, -1, "open $file error"));

        //global $UCLOUD_PROXY_SUFFIX;
        $UCLOUD_PROXY_SUFFIX = $this->conf->UCLOUD_PROXY_SUFFIX;

        $etagList = array();
        list($mimetype, $err) = Util::GetFileMimeType($filename);
        if ($err) {
            fclose($f);
            return array("", $err);
        }
        $client   = new AuthHttpClient(null,$this->conf);
        for(;;) {
            $host = $bucket . $UCLOUD_PROXY_SUFFIX;
            $path = $key;
            if (@fseek($f, $blkSize*$partNumber, SEEK_SET) < 0) {
                fclose($f);
                return array(null, new Error(0, -1, "fseek error"));
            }
            $content = @fread($f, $blkSize);
            if ($content == FALSE) {
                if (feof($f)) break;
                fclose($f);
                return array(null, new Error(0, -1, "read file error"));
            }

            $querys = array(
                "uploadId" => $uploadId,
                "partNumber" => $partNumber
            );
            $req = new Request('PUT', array('host'=>$host, 'path'=>$path, 'query'=>$querys), $content, $bucket, $key,$this->conf);
            $req->Header['Content-Type'] = $mimetype;
            $req->Header['Expect'] = '';
            list($data, $err) = $client->UCloud_Client_Call($req);
            if ($err) {
                fclose($f);
                return array(null, $err);
            }
            $etag = @$data['ETag'];
            $part = @$data['PartNumber'];
            if ($part != $partNumber) {
                fclose($f);
                return array(null, new Error(0, -1, "unmatch partnumber"));
            }
            $etagList[] = $etag;
            $partNumber += 1;
        }
        fclose($f);
        return array($etagList, null);
    }

    public function UCloud_MFinish($bucket, $key, $uploadId, $etagList, $newKey = '')
    {

        $err = Util::CheckConfig(ActionType::MFINISH,$this->conf);
        if ($err != null) {
            return array(null, $err);
        }

        //global $UCLOUD_PROXY_SUFFIX;
        $UCLOUD_PROXY_SUFFIX = $this->conf->UCLOUD_PROXY_SUFFIX;
        $host = $bucket . $UCLOUD_PROXY_SUFFIX;
        $path = $key;
        $querys = array(
            'uploadId' => $uploadId,
            'newKey' => $newKey,
        );

        $body = @implode(',', $etagList);
        $req = new Request('POST', array('host'=>$host, 'path'=>$path, 'query'=>$querys), $body, $bucket, $key,$this->conf);
        $req->Header['Content-Type'] = 'text/plain';

        $client = new AuthHttpClient(null,$this->conf);
        return $client->UCloud_Client_Call($req);
    }

    public function UCloud_MCancel($bucket, $key, $uploadId)
    {

        $err = Util::CheckConfig(ActionType::MCANCEL,$this->conf);
        if ($err != null) {
            return array(null, $err);
        }

        //global $UCLOUD_PROXY_SUFFIX;
        $UCLOUD_PROXY_SUFFIX = $this->conf->UCLOUD_PROXY_SUFFIX;
        $host = $bucket . $UCLOUD_PROXY_SUFFIX;
        $path = $key;
        $querys = array(
            'uploadId' => $uploadId
        );

        $req = new Request('DELETE', array('host'=>$host, 'path'=>$path, 'query'=>$querys), null, $bucket, $key,$this->conf);
        $req->Header['Content-Type'] = 'application/x-www-form-urlencoded';

        $client = new AuthHttpClient(null,$this->conf);
        return $client->UCloud_Client_Call($req);
    }

//------------------------------秒传------------------------------
    public function UCloud_UploadHit($bucket, $key, $file)
    {

        $err = Util::CheckConfig(ActionType::UPLOADHIT,$this->conf);
        if ($err != null) {
            return array(null, $err);
        }

        $f = @fopen($file, "r");
        if (!$f) return array(null, new Error(-1, -1, "open $file error"));

        $content = "";
        $fileSize = filesize($file);
        if ($fileSize != 0) {
            $content  = @fread($f, $fileSize);
            if ($content == FALSE) {
                fclose($f);
                return array(null, new Error(0, -1, "read file error"));
            }
        }

        list($fileHash, $err) = Util::UCloud_FileHash($file);

        if ($err) {
            fclose($f);
            return array(null, $err);
        }
        fclose($f);

        //global $UCLOUD_PROXY_SUFFIX;
        $UCLOUD_PROXY_SUFFIX = $this->conf->UCLOUD_PROXY_SUFFIX;
        $host = $bucket . $UCLOUD_PROXY_SUFFIX;
        $path = "uploadhit";
        $querys = array(
            'Hash' => $fileHash,
            'FileName' => $key,
            'FileSize' => $fileSize
        );

        $req = new Request('POST', array('host'=>$host, 'path'=>$path, 'query'=>$querys), null, $bucket, $key,$this->conf);
        $req->Header['Content-Type'] = 'application/x-www-form-urlencoded';

        $client = new AuthHttpClient(null,$this->conf);
        return $client->UCloud_Client_Call($req);
    }

//------------------------------删除文件------------------------------
    public function UCloud_Delete($bucket, $key)
    {

        $err = Util::CheckConfig(ActionType::DELETE,$this->conf);
        if ($err != null) {
            return array(null, $err);
        }

        //global $UCLOUD_PROXY_SUFFIX;
        $UCLOUD_PROXY_SUFFIX = $this->conf->UCLOUD_PROXY_SUFFIX;
        $host = $bucket . $UCLOUD_PROXY_SUFFIX;
        $path = "$key";

        $req = new Request('DELETE', array('host'=>$host, 'path'=>$path), null, $bucket, $key,$this->conf);
        $req->Header['Content-Type'] = 'application/x-www-form-urlencoded';

        $client = new AuthHttpClient(null,$this->conf);
        return $client->UCloud_Client_Call($req);
    }

//------------------------------生成公有文件Url------------------------------
// @results: $url
    public function UCloud_MakePublicUrl($bucket, $key,$host='')
    {
        if(empty($host)) {
            $UCLOUD_PROXY_SUFFIX = $this->conf->UCLOUD_PROXY_SUFFIX;
            return $bucket . $UCLOUD_PROXY_SUFFIX . "/" . rawurlencode($key);
        }else{
            return $host . '/' . rawurlencode($key);
        }
    }
//------------------------------生成私有文件Url------------------------------
// @results: $url
    public function UCloud_MakePrivateUrl($bucket, $key, $expires = 0,$host='')
    {

        $err = Util::CheckConfig(ActionType::GETFILE,$this->conf);
        if ($err != null) {
            return array(null, $err);
        }

        $UCLOUD_PUBLIC_KEY = $this->conf->UCLOUD_PUBLIC_KEY;

        $public_url = $this->UCloud_MakePublicUrl($bucket, $key,$host);
        $req = new Request('GET', array('path'=>$public_url), null, $bucket, $key,$this->conf);
        if ($expires > 0) {
            $req->Header['Expires'] = $expires;
        }

        $client = new AuthHttpClient(null,$this->conf);
        $temp = $client->Auth->SignRequest($req, null, QUERY_STRING_CHECK);
        $signature = substr($temp, -28, 28);
        $url = $public_url . "?UCloudPublicKey=" . rawurlencode($UCLOUD_PUBLIC_KEY) . "&Signature=" . rawurlencode($signature);
        if ('' != $expires) {
            $url .= "&Expires=" . rawurlencode($expires);
        }
        return $url;
    }

}