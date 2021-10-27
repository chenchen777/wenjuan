<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2017/4/25
 * Time: 16:47
 */

namespace common\component\proxy;


use common\models\IpjlLog;
use linslin\yii2\curl\Curl;
use yii\base\Component;
use yii\base\UserException;

class IpJlComponent extends Component implements Proxy

{
    public $api = 'http://open.ipjldl.com/index.php/api/entry';
    public $getParams = [];

    public function init()
    {
        $this->getParams = [
            'method' => 'proxyServer.ipinfolist',
            'quantity' => 1,
            'anonymous' => 1,
            'ms' => 1,
            'service' => 0,
            'protocol' => 1,
            'wdsy' => 'on',
            'data_area' => true,
            'distinct' => true,
            'format' => 'json',
            'separator' => 1,
        ];
    }

    public function getProxy($city, $province, $type = 'http')
    {
        if (!($type == 'http' or $type == 'https' or $type == 'sock')) {
            return [];
        }
        $curl = new Curl();
        $num = IpjlLog::getNum($province, $city);
        if ($num >= 100) {
            $num = 99;
        }
        $curl->setGetParams($this->getParams);
        $curl->setGetParams(['province' => $province, 'city' => $city]);
        $curl->setGetParams(['quantity' => $num + 1]);

        $res = $curl->get($this->api, false);
        if (!empty($res['ret']) and $res['ret'] == 200 and !empty($res['data']) and isset($res['data']['code']) and $res['data']['code'] == 0) {
            if (!empty($res['data']['list']) and !empty($res['data']['list']['ProxyIpInfoList'])) {
                $list = $res['data']['list']['ProxyIpInfoList'];
                if(isset($list[$num])){
                    $one = $list[$num];
                }else {
                    $one = $list[count($list)-1];
                }
                IpjlLog::add(['province'=>$province,'city'=>$city,'ip'=>$one['IP'],'port'=>$one['Port']]);
                return ['type' => $type, 'ip' => $one['IP'], 'port' => $one['Port']];
            } else {
                return [];
            }
        } else {
            return [];
        }
    }
}