<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/21
 * Time: 上午12:31
 */

namespace UCloud;

class Conf
{
    const SDK_VER = "1.0.8";

//空间域名后缀,请查看控制台上空间域名再配置此处
    public $UCLOUD_PROXY_SUFFIX = '.ufile.ucloud.cn';
    public $CURL_TIMEOUT = 5;

    public $UCLOUD_PUBLIC_KEY = '';//'paste your public key here';
    public $UCLOUD_PRIVATE_KEY = '';//'paste your private key here';

    public function __construct($conf = [])
    {
        if(!is_array($conf)){
            return;
        }
        if(isset($conf['public_key'])){
            $this->UCLOUD_PUBLIC_KEY = $conf['public_key'];
        }
        if(isset($conf['private_key'])){
            $this->UCLOUD_PRIVATE_KEY = $conf['private_key'];
        }
        if(isset($conf['timeout'])){
            $this->CURL_TIMEOUT = $conf['timeout'];
        }
        if(isset($conf['suffix'])){
            $this->UCLOUD_PROXY_SUFFIX = $conf['suffix'];
        }
    }

}
