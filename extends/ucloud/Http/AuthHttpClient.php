<?php
namespace UCloud\Http;
use UCloud\Canonicalize;
use UCloud\Conf;

/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/20
 * Time: 下午11:47
 */
class AuthHttpClient extends HttpClient
{
    public $Auth;
    public $Type;
    public $MimeType;

    public function __construct($auth, Conf $conf,$mimetype = null, $type = HEAD_FIELD_CHECK)
    {
        $this->Type = $type;
        $this->MimeType = $mimetype;
        $this->Auth = Canonicalize::makeAuth($auth, $conf, $type);
    }

    //@results: ($resp, $error)
    public function RoundTrip($req,$type)
    {
        if ($this->Type === HEAD_FIELD_CHECK) {
            $token = $this->Auth->SignRequest($req, $this->MimeType, $this->Type);
            $req->Header['Authorization'] = $token;
        }
        return Http::UCloud_Client_Do($req);
    }
}