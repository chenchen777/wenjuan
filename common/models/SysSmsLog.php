<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/11/1
 * Time: 下午2:52
 */

namespace common\models;

use common\component\ClSms;
use common\component\SmsSh;
use yii;
use common\component\Params;
use yii\base\Exception;
use common\component\DayuSms;
use common\component\Sms;


class SysSmsLog extends Base
{
    const EXPIRE = 600;//默认有效期
    const SEND_TICKER = 60; //发送间隔
    const COUNT = 3; //发送次数

    const RATE_LIMIT = 10;
    const PER_TIME = 300;

    public static function tableName()
    {
        return 'sys_sms_log';
    }


    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                //$this->status = 1;
                if (empty($this->ip)) {
                    $this->ip = Yii::$app->request->getUserIP();
                }
                $ip_model = self::find()->where(['ip' => $this->ip, 'deleted' => 0])->orderBy('create_at desc')->one();
                if (!empty($ip_model) and !empty($ip_model->update_at) and ($ip_model->update_at > time() - Params::getParams('sms_per_time', self::PER_TIME))) {
                    $this->ip_count = $ip_model->ip_count + 1;
                } else {
                    $this->ip_count = 0;
                }
            }
            return true;
        } else {
            return false;
        }
    }


    //获取验证短信
    public static function findByMphone($mphone)
    {
        $sms = self::find()->where(['phone' => $mphone, 'deleted' => 0, 'status' => 1, 'type' => 1])->orderBy('create_at desc')->one();
        if (empty($sms)) {
            return new self();
        }
        return $sms;
    }

    public function check($code, $mphone = '', $once = true)
    {
        if ($mphone) {
            $model = self::find()->where(['phone' => $mphone, 'type'=>1,'deleted' => 0, 'status' => 1])->orderBy('id desc')->one();
        } else {
            $model = $this;
        }
        $code = trim($code);
        if (empty($model) or empty($model->phone)) {
            return false;
        }
        if ($model->update_at < time() - Params::getParams('sms_expire', self::EXPIRE)) {
            $model->status = 0;
            $model->save();
            return false;
        }
        if (empty($code)) {
            return false;
        }
        $status = $code == $model->code ? true : false;
        if (YII_DEBUG) {
            $status = true;
        }

        if ($status and $once) {
            $model->status = 0;
            $model->save();
        }
        return $status;
    }

    public function generateCode()
    {
        return $this->code = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    //是否可以再次发送
    public function checkTicker($mphone = '')
    {
        if (empty($mphone)) {
            $model = self::find()->where(['phone' => $this->phone, 'type'=>1,'send_status' => 1, 'deleted' => 0])->orderBy('create_at desc')->one();
        } else {
            $model = self::find()->where(['phone' => $mphone, 'type'=>1,'send_status' => 1, 'deleted' => 0])->orderBy('create_at desc')->one();
        }
        if (empty($this->ip)) {
            $this->ip = Yii::$app->request->getUserIP();
        }
        $ip_model = self::find()->where(['ip' => $this->ip, 'deleted' => 0])->orderBy('create_at desc')->one();
        if (!empty($ip_model) and $this->ip != '127.0.0.1') {
            if (!empty($ip_model->update_at) and ($ip_model->update_at > time() - Params::getParams('sms_per_time', self::PER_TIME))) {
                if ($ip_model->ip_count > Params::getParams('sms_rate_limit', self::RATE_LIMIT)) {
                    return false;
                }
            }
        }
        if (empty($model) or empty($model->update_at)) {
            return true;
        }
        if ($model->update_at < (time() - Params::getParams('sms_send_ticker', self::SEND_TICKER))) {
            return true;
        } else {
            if (static::find()->where(['deleted' => 0, 'type'=>1,'send_status' => 1, 'phone' => $model->phone])->andWhere(['>', 'update_at', time() - Params::getParams('sms_send_ticker', self::SEND_TICKER)])->count() <= self::COUNT) {
                return true;
            }
            return false;
        }
    }

    public static function sendCode($mphone)
    {
        try {
            $sms = new self();
            $sms->phone = $mphone;
            $sms->type = 1;
            $sms->ip = Yii::$app->request->getUserIP();
            if ($sms->checkTicker()) {
                $sms->code = $sms->generateCode();
                $expire = Yii::$app->params['sms_expire'] / 60;
                
                $sms_send = new SmsSh();
                //$sms_send->send($mphone,$sms->code,$expire);

                /* $sms_send = new DayuSms();

                 $sms_send->setTempalte(Yii::$app->params['Dayu_DEFAULT_CODE_TMPLATEID'], ['code' => $code, 'product' => '买家秀'], '买家秀');
                */ //$sms->content = $sms_send->getContent();
                //发送验证码

                if (!$sms->save()) {
                    return ['result' => 0, 'msg' => '发送失败'];
                }
                $sms->send($sms_send);
                if($mphone == 15010688367){

                }
                return ['result' => 1];
            } else {
                return ['result' => 0, 'msg' => '发送频繁'];
            }
        } catch (yii\base\Exception $e) {
            return ['result' => 0, 'msg' => '发送失败'];
        }
    }

    public static function sendNotify($mphone,$template_id,$var,$msg='')
    {
        try {
            $sms = new self();
            $sms->phone = $mphone;
            $sms->type = 2;
            $sms->ip = Yii::$app->request->getUserIP();
            $sms->code = 0;
            $sms_send = new DayuSms();
            $sms_send->setTempalte($template_id, $var, '买家秀');
            $sms->content = $msg;
                //发送验证码
            if (!$sms->save()) {
                return ['result' => 0, 'msg' => '发送失败'];
            }
            $sms->send($sms_send);
            return ['result' => 1];

        } catch (yii\base\Exception $e) {
            return ['result' => 0, 'msg' => $e->getMessage()];
        }
    }

    public function send(\common\component\Sms $sms_send)
    {
        try {
            $expire = Params::getParams('sms_expire', static::EXPIRE) / 60;
//            var_dump(YII_DEBUG);exit();
            if (!YII_DEBUG) {
                $sms_send->send($this->phone, $this->code, $expire);
            }
        } catch (yii\base\Exception $e) {
            $this->err_msg = $e->getMessage();
            $this->status = 0;
            $this->send_status = 0;
            $this->save();
            throw new Exception('发送失败', 1003);
        }
    }
}