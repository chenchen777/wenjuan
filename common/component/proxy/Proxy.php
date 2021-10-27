<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2017/4/25
 * Time: 17:05
 */

namespace common\component\proxy;


interface Proxy
{
    public function getProxy($city,$province,$type);
}