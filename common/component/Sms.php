<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/11/17
 * Time: 下午3:03
 */

namespace common\component;


interface Sms
{
    public function send($mobile, $code, $expire = 10);

    //短信内容
    public function getContent();
    public function setContent(array $array);

}