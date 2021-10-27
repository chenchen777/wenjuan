<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/21
 * Time: 下午3:27
 */

namespace common\component;
use yii;

class Params
{
    public static function getParams($key,$default=null)
    {
        if(strpos($key,'.')){
            $keys = explode('.',$key);
            $params = Yii::$app->params;
            foreach ($keys as $key){
                if(isset($params[$key])) {
                    $params = $params[$key];
                }else{
                    $params = null;
                    break;
                }
            }
        }else {
            if (isset(Yii::$app->params[$key])) {
                $params = Yii::$app->params[$key];
            } else {
                $params = null;
            }
        }
        if(empty($params)){
            $params = $default;
        }
        return $params;
    }
}