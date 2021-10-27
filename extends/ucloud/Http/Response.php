<?php
namespace UCloud\Http;
use UCloud\Conf;

/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/20
 * Time: 下午11:46
 */
class Response
{
    public $StatusCode;
    public $Header;
    public $ContentLength;
    public $Body;
    public $Timeout;

    public function __construct($code, $body,$conf)
    {
        $this->StatusCode = $code;
        $this->Header = array();
        $this->Body = $body;
        $this->ContentLength = strlen($body);

        $CURL_TIMEOUT = $conf->CURL_TIMEOUT;
        if ($CURL_TIMEOUT == null) {
            $CURL_TIMEOUT = 10;
        }
        $this->Timeout = $CURL_TIMEOUT;
    }
}