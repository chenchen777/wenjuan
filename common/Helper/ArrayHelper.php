<?php
namespace common\Helper;

/**
 * 数组相关帮助类
 * @package common\helpers
 */
class ArrayHelper extends \yii\helpers\ArrayHelper {


    /**
     * 转成一维数组并去掉重复值
     * @param $arrList array
     * @return array
     */
    public static function toOneDim($arrList){
        if(empty($arrList)){
            return [];
        }
        $arr = [];
        foreach ($arrList as $value){
            if(is_string($value)){
                $arr[] = $value;
            }else if(is_array($value)){
                $arr = parent::merge($arr, self::toOneDim($value));
            }
        }
        return $arr;
    }

    /**
     * 二维数组错误转成字符串
     * @param $errArray array 数组
     * @return string
     */
    public static function errorArrayToString($errArray){
        $err = self::toOneDim($errArray);
        if(empty($err)){
            return '';
        }
        return implode(' ', $err);
    }
}