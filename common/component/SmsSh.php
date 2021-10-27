<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2017/7/24
 * Time: 16:55
 */

namespace common\component;

use linslin\yii2\curl\Curl;
use Yii;
use yii\base\Exception;
use yii\helpers\Json;

class SmsSh implements Sms
{
    private $api_send_url = 'http://smssh1.253.com/msg/send/json';
    private $api_balance_query_url = 'http://smssh1.253.com/msg/balance/json';
    private $api_account = 'N3237073';
    private $api_password = 'i80faKcIvoa252';
    protected $msg_content = '';

    private $send_err_msg = array(
        0 => '提交成功',
        101 => '无此用户',
        102 => '密码错',
        103 => '提交过快（提交速度超过流速限制',
        104 => '系统忙（因平台侧原因，暂时无法处理提交的短信）',
        105 => '敏感短信（短信内容包含敏感词）',
        106 => '消息长度错（>536或<=0）',
        107 => '包含错误的手机号码',
        108 => '手机号码个数错（群发>50000或<=0;单发>200或<=0）',
        109 => '无发送额度（该用户可用短信数已使用完）',
        110 => '不在发送时间内',
        111 => '超出该账户当月发送额度限制',
        112 => '无此产品，用户没有订购该产品',
        113 => 'extno格式错（非数字或者长度不对）',
        115 => '自动审核驳回',
        116 => '签名不合法，未带签名（用户必须带签名的前提下）',
        117 => 'IP地址认证错,请求调用的IP地址不是系统登记的IP地址',
        118 => '用户没有相应的发送权限',
        119 => '用户已过期',
        120 => '违反防盗用策略(日发送限制)',
        123 => '发送类型错误',
        124 => '白模板匹配错误',
        125 => '匹配驳回模板，提交失败',
        127 => '定时发送时间格式错误',
        128 => '内容编码失败',
        129 => 'JSON格式错误',
        130 => '请求参数错误（缺少必填参数）',

    );

    public function __construct($config = [])
    {
        $common_config = Params::getParams('SMS_SH', []);
        $config = array_merge($common_config, $config);

        isset($config['api_account']) and $this->api_account = Yii::$app->params['SMS_SH']['api_account'];
        isset($config['api_password']) and $this->api_password = Yii::$app->params['SMS_SH']['api_password'];
        isset($config['api_send_url']) and $this->api_send_url = Yii::$app->params['SMS_SH']['api_send_url'];
        isset($config['api_balance_url']) and $this->api_balance_query_url = Yii::$app->params['SMS_SH']['api_balance_url'];
    }

    public function send($mobile, $code, $expire = 10)
    {
        $curl = new Curl();
        $msg = '您的验证码为' . $code . ',请在' . $expire . '分钟内输入,如果不是本人操作请忽略本消息';
        if (Yii::$app->params['sms_sign'] == '京侦探'){
            $msg = '【京侦探】您的验证码为' . $code . ',请在' . $expire . '分钟内输入,如果不是本人操作请忽略本消息';
        }
        $curl->setRequestBody(Json::encode([
            'account' => $this->api_account,
            'password' => $this->api_password,
            'msg' => $msg,
            'phone' => $mobile,
            'extend'=>'157055',
        ]));
        $curl->setHeaders(['Content-Type' => 'application/json']);
        $res = $curl->post($this->api_send_url, false);
        if(!isset($res['code'])){
            throw new Exception('请求失败',1003);
        }
        if (isset($res['code']) and $res['code'] != 0) {
            if(empty($res['errorMsg'])){
                $res['errorMsg'] = '';
            }
            throw new Exception($this->get_err_msg($res['code']). ';' . $res['errorMsg'],1003);
        }
    }

    public function sendmsg($mobile, $msg, $expire = 10)
    {
        $curl = new Curl();
        $curl->setRequestBody(Json::encode([
            'account' => 'M6803698',
            'password' => 'CysaIxqt0',
            'msg' => $msg,
            'phone' => $mobile,
            'extend'=>'145450',
        ]));
        $curl->setHeaders(['Content-Type' => 'application/json']);
        $res = $curl->post($this->api_send_url, false);
        if(!isset($res['code'])){
            throw new Exception('请求失败',1003);
        }
        if (isset($res['code']) and $res['code'] != 0) {
            if(empty($res['errorMsg'])){
                $res['errorMsg'] = '';
            }
            throw new Exception($this->get_err_msg($res['code']). ';' . $res['errorMsg'],1003);
        }
    }

    public function get_err_msg($code)
    {
        if (isset($this->send_err_msg[$code])) {
            $msg = $this->send_err_msg[$code];
            //$msg = '发送失败';
        } else {
            $msg = '未知错误';
        }

        return $msg;
    }

    //短信内容
    public function getContent()
    {
        return $this->msg_content;
    }

    public function setContent(array $template)
    {
        $this->msg_content = '您的验证码为' . $template['code'] . ',请在' . $template['expire'] . '分钟内输入,如果不是本人操作请忽略本消息';
    }
}