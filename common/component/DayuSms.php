<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/11/17
 * Time: 下午2:52
 */

namespace common\component;

use AlibabaAliqinFcSmsNumSendRequest;
use TopClient;
use yii\base\Exception;

class DayuSms implements Sms
{
    protected $appKey;
    protected $secret;

    protected $error   = '';
    protected $errorNo = 0;
    protected $subCode = 0;
    protected $msg_content='';
    protected $product = '';

    private $smsTempalteid;
    private $variables;
    private $signName;

    public function __construct($appKey = '', $secret = '')
    {
        $this->appKey = Params::getParams('Dayu_SMS_APP_KEY','');//C('SMS_APP_KEY') : '';
        $this->secret = Params::getParams('Dayu_SMS_APP_SECRET','');//C('SMS_APP_SECRET') : '';
        $this->product = Params::getParams('Dayu_SMS_PRODUCT_NAME','平台服务');
        empty($appKey) ? '' : $this->appKey = $appKey;
        empty($secret) ? '' : $this->secret = $secret;

        $this->signName = '身份验证';
        $this->smsTempalteid = 'SMS_26210088';
        //$this->variables = '身份验证';
    }

    public function register($phone, $code)
    {
        $c = new TopClient;
        $c->appkey = $this->appKey;
        $c->secretKey = $this->secret;
        $c->format = 'json';
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        //$req->setExtend("");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName("注册验证");
        $req->setSmsParam("{code:'$code',product:'$this->product'}");
        $req->setRecNum($phone);
        $req->setSmsTemplateCode("SMS_11061031");
        $resp = $c->execute($req);
        return $this->parse($resp);
    }

    protected function parse($response)
    {
        if (isset($response->result) and $response->result->err_code == 0) {
            return true;
        }
        if (!isset($response->code)) {
            $this->errorNo = 999;
            $this->error = '请求失败';
            return false;
        }
        if ($response->code != 0) {
            $this->errorNo = $response->code;
            $this->error = isset($response->sub_msg)?$response->sub_msg:'';
            $this->subCode = isset($response->sub_code)?$response->sub_code:0;
            return false;
        }
        return true;
    }

    public function confirm($phone, $code)
    {
        $c = new TopClient;
        $c->appkey = $this->appKey;
        $c->secretKey = $this->secret;
        $c->format = 'json';
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        //$req->setExtend("");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName($this->product);
        $req->setSmsParam("{code:'$code',product:'$this->product'}");
        $req->setRecNum($phone);
        $req->setSmsTemplateCode("SMS_11061035");
        $resp = $c->execute($req);
        return $this->parse($resp);
    }

    public function getError()
    {
        return $this->error;
    }

    public function getMessage()
    {
        $error = array(
            'isv.permission-ip-whitelist-limit'=>'发送服务器ip不在业务ip白名单中',//ip错误
            'isv.OUT_OF_SERVICE'              => '业务停机',  //业务停机
            'isv.PRODUCT_UNSUBSCRIBE'         => '产品服务未开通', //产品服务未开通
            'isv.ACCOUNT_NOT_EXISTS'          => '账户信息不存在', //账户信息不存在
            'isv.ACCOUNT_ABNORMAL'            => '账户信息异常', //账户信息异常
            'isv.SMS_TEMPLATE_ILLEGAL'        => '模板不合法', //模板不合法
            'isv.SMS_SIGNATURE_ILLEGAL'       => '签名不合法', //签名不合法
            'isv.MOBILE_NUMBER_ILLEGAL'       => '手机号码格式错误', //手机号码格式错误
            'isv.MOBILE_COUNT_OVER_LIMIT'     => '手机号码数量超过限制', //手机号码数量超过限制
            'isv.TEMPLATE_MISSING_PARAMETERS' => '短信模板变量缺少参数', //短信模板变量缺少参数
            'isv.INVALID_PARAMETERS'          => '检查参数是否合法', //检查参数是否合法
            'isv.BUSINESS_LIMIT_CONTROL'      => '发送过于频繁', //触发业务流控限制
            'isv.INVALID_JSON_PARAM'          => 'JSON参数不合法', //JSON参数不合法
            'isv.BLACK_KEY_CONTROL_LIMIT'     => '模板变量中存在黑名单关键字', //模板变量中存在黑名单关键字。如：阿里大鱼
            'isv.PARAM_NOT_SUPPORT_URL'       => '不支持url为变量', //不支持url为变量
            'isv.PARAM_LENGTH_LIMIT'          => 'PARAM_LENGTH_LIMIT', //PARAM_LENGTH_LIMIT
            'isv.SYSTEM_ERROR'                => '服务器错误', //
        );
        if (isset($error[$this->subCode])) {
            return $error[$this->subCode];
        }
        return empty($this->error)?'未知错误':$this->error;
    }

    public function getErrorNo()
    {
        return $this->errorNo;
    }

    public function send($mobile, $code, $expire = 10)
    {
        $c = new TopClient;
        $c->appkey = $this->appKey;
        $c->secretKey = $this->secret;
        $c->format = 'json';
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        //$req->setExtend("");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName($this->signName);
        if(empty($this->variables)) {
            $req->setSmsParam("{code:'$code',product:'$this->product'}");
        }else{
            $req->setSmsParam(json_encode($this->variables,JSON_UNESCAPED_UNICODE));
        }
        $req->setRecNum($mobile);
        $req->setSmsTemplateCode($this->smsTempalteid);
        $resp = $c->execute($req);
        if(!$this->parse($resp)){
            throw new Exception($this->getMessage(),1003);
        }
    }

    public function setTempalte($smsTempalteid,$variables,$signName)
    {
        $this->signName = $signName;
        $this->smsTempalteid = $smsTempalteid;
        $this->variables = $variables;
    }
    public function sendDayu($mobile,$smsTempalteid,$variables) {
      $c = new TopClient;
      $c->appkey = $this->appKey;
      $c->secretKey = $this->secret;
      $c->format = 'json';
      $req = new AlibabaAliqinFcSmsNumSendRequest;
      //$req->setExtend("");
      $req->setSmsType("normal");
      $req->setSmsFreeSignName("买家秀");
      $req->setSmsParam(json_encode($variables,JSON_UNESCAPED_UNICODE));
      $req->setRecNum($mobile);
      $req->setSmsTemplateCode($smsTempalteid);
      $resp = $c->execute($req);
      if(!$this->parse($resp)){
        throw new Exception($this->getMessage(),1003);
      }
    }

    public function getContent()
    {
        return $this->msg_content;
    }

    public function setContent(array $code)
    {
        $this->msg_content = "验证码".$code['code']."，您正在进行".$this->product. "身份验证，打死不要告诉别人哦！";
    }
}