<?php
/**
 * Created by PhpStorm.
 * User: JYT
 * Date: 2020/8/12
 * Time: 16:29
 */

namespace common\service;

use Yii;
use common\Helper\ArrayHelper;

/**
 * Class BaseService
 * @package common\service
 */
abstract class BaseService{

    /**
     * @var array 错误信息
     */
    protected $errors;

    /**
     * @var int 错误码
     */
    protected $errCode;

    public $nowTime;

    public function __construct(){
        $this->nowTime = time();
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return is_string($this->errors) ? [$this->errors] : ArrayHelper::toOneDim($this->errors);
    }

    public function getErrorString()
    {
        $errArr = $this->getErrors();
        return implode(' ', $errArr);
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * 开启事务
     * @return \yii\db\Transaction
     */
    public function beginTransaction()
    {
        return Yii::$app->db->beginTransaction();
    }

}