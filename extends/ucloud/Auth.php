<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/20
 * Time: 下午11:42
 */

namespace UCloud;


use UCloud\Http\Http;

class Auth
{
    public $PublicKey;
    public $PrivateKey;

    public function __construct($publicKey, $privateKey)
    {
        $this->PublicKey = $publicKey;
        $this->PrivateKey = $privateKey;
    }

    public function Sign($data)
    {
        $sign = base64_encode(hash_hmac('sha1', $data, $this->PrivateKey, true));
        return "UCloud " . $this->PublicKey . ":" . $sign;
    }

    //@results: $token
    public function SignRequest($req, $mimetype = null, $type = HEAD_FIELD_CHECK)
    {
        $url = $req->URL;
        $url = parse_url($url['path']);
        $data = '';
        $data .= strtoupper($req->METHOD) . "\n";
        $data .= Http::UCloud_Header_Get($req->Header, 'Content-MD5') . "\n";
        if ($mimetype)
            $data .=  $mimetype . "\n";
        else
            $data .= Http::UCloud_Header_Get($req->Header, 'Content-Type') . "\n";
        if ($type === HEAD_FIELD_CHECK)
            $data .= Http::UCloud_Header_Get($req->Header, 'Date') . "\n";
        else
            $data .= Http::UCloud_Header_Get($req->Header, 'Expires') . "\n";
        $data .= Canonicalize::CanonicalizedUCloudHeaders($req->Header);
        $data .= Canonicalize::CanonicalizedResource($req->Bucket, $req->Key);
        return $this->Sign($data);
    }
}