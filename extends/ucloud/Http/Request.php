<?php
namespace UCloud\Http;
use UCloud\Conf;
use UCloud\Utils\ActionType;
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/20
 * Time: 下午11:45
 */
class Request
{
    public $URL;
    public $RawQuerys;
    public $Header;
    public $Body;
    public $UA;
    public $METHOD;
    public $Params;      //map
    public $Bucket;
    public $Key;
    public $Timeout;
    public $Conf;

    public function __construct($method, $url, $body, $bucket, $key, Conf $conf,$action_type = ActionType::NONE)
    {
        $this->URL = $url;
        if (isset($url["query"])) {
            $this->RawQuerys = $url["query"];
        }
        $this->Header = array();
        $this->Body = $body;
        $this->UA = HttpClient::UCloud_UserAgent();
        $this->METHOD = $method;
        $this->Bucket = $bucket;
        $this->Key = $key;

        $this->Conf = $conf;

        $CURL_TIMEOUT = $conf->CURL_TIMEOUT;
        //$UFILE_ACTION_TYPE = Conf::UFILE_ACTION_TYPE;
        if ($CURL_TIMEOUT == null && $action_type !== ActionType::PUTFILE
            && $action_type !== ActionType::POSTFILE
        ) {
            $CURL_TIMEOUT = 10;
        }
        $this->Timeout = $CURL_TIMEOUT;
    }

    public function EncodedQuery()
    {
        if ($this->RawQuerys != null) {
            $q = "";
            foreach ($this->RawQuerys as $k => $v) {
                $q = $q . "&" . rawurlencode($k) . "=" . rawurlencode($v);
            }
            return $q;
        }
        return "";
    }
}