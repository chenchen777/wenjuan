<?php
/**
 * 创蓝短信服务
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/25
 * Time: 下午4:58
 */

namespace common\component;

use yii\base\Exception;
use Yii;

class ClSms implements Sms
{
    private $api_send_url          = '';
    private $api_balance_query_url = '';
    private $api_account           = '';
    private $api_password          = '';
    protected $msg_content = '';

    private $send_err_msg  = array(
        0   => '提交成功',
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
        120 => '短信内容不在白名单中此用户',

    );
    private $query_err_msg = array(
        0=>'成功',
        101=>'无此用户',
        102=>'密码错',
        103=>'查询过快(30秒查询一次)',
    );

    public function __construct($config=[])
    {
        //parent::__construct();
        $common_config = Params::getParams('CL_SMS',[]);
        $config = array_merge($common_config,$config);

        isset($config['api_account']) and $this->api_account = Yii::$app->params['CL_SMS']['api_account'];
        isset($config['api_password']) and $this->api_password = Yii::$app->params['CL_SMS']['api_password'];
        isset($config['api_send_url']) and $this->api_send_url = Yii::$app->params['CL_SMS']['api_send_url'];
        isset($config['api_balance_url']) and $this->api_balance_query_url = Yii::$app->params['CL_SMS']['api_balance_url'];
    }

    public function get_err_msg($code,$type='send')
    {
        if($type=='send') {
            if (isset($this->send_err_msg[ $code ])) {
                $msg = $this->send_err_msg[ $code ];
                //$msg = '发送失败';
            } else {
                $msg = '未知错误';
            }
        }else if($type=='query')
        {
            if (isset($this->query_err_msg[ $code ])) {
                $msg = $this->query_err_msg[ $code ];
                //$msg = '发送失败';
            } else {
                $msg = '未知错误';
            }
        }else{
            $msg = '未知错误';
        }
        return $msg;
    }


    /**
     * 发送短信
     *
     * @param string $mobile 手机号码
     * @param        $code
     * @param int $expire
     * @param string $needstatus 是否需要状态报告
     * @param string $product 产品id，可选
     * @param string $extno 扩展码，可选
     * @return mixed
     * @throws Exception
     * @internal param string $msg 短信内容
     */
    public function send($mobile, $code, $expire = 10, $needstatus = 'false', $product = '', $extno = '')
    {
       /* if(empty($this->msg_content)){
            $this->setContent('您的验证码为' . $code . ',请在' . $expire . '分钟内输入,如果不是本人操作请忽略本消息');
        }
        $msg = $this->getContent();*/
        $msg = '您的验证码为' . $code . ',请在' . $expire . '分钟内输入,如果不是本人操作请忽略本消息';
        //创蓝接口参数
        $postArr = array(
            'account'    => $this->api_account,
            'pswd'       => $this->api_password,
            'msg'        => $msg,
            'mobile'     => $mobile,
            'needstatus' => $needstatus,
            'product'    => $product,
            'extno'      => $extno,
        );
        $result = $this->curlPost($this->api_send_url, $postArr);
        $data = $this->execResult($result);
        $code = false;
        !isset($data) or $code = $data[1];
        if(!($code===0 or $code==='0'))
        {
            throw new Exception($this->get_err_msg($code),1003);
        }
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

    //发送通知消息
    public function sendNotify($mobile,$msg ,$needstatus = 'false', $product = '', $extno = '')
    {
        if(empty($msg))
            return false;
            //创蓝接口参数
            $postArr = array(
                'account'    => $this->api_account,
                'pswd'       => $this->api_password,
                'msg'        => trim($msg),
                'mobile'     => $mobile,
                'needstatus' => $needstatus,
                'product'    => $product,
                'extno'      => $extno,
            );
            $result = $this->curlPost($this->api_send_url, $postArr);
            $data = $this->execResult($result);
            $code = false;
            !isset($data) or $code = $data[1];
            if(!($code===0 or $code==='0'))
            {
                //return false;
                throw new Exception($this->get_err_msg($code),1003);
            }
            
            return true;
    }

    /**
     * 查询额度
     *
     *  查询地址
     */
    public function queryBalance()
    {
        //查询参数
        $postArr = array(
            'account' => $this->api_account,
            'pswd'    => $this->api_password,
        );
        $result  = $this->curlPost($this->api_balance_query_url, $postArr);
        return $this->execResult($result);
    }

    /**
     * 处理返回值
     *
     */
    public function execResult($result)
    {
        $result = preg_split("/[,\r\n]/", $result);
        return $result;
    }
    public function get_code($result)
    {
        if(isset($result[1]))
        {
            return $result[1];
        }else
        {
            return 500;
        }
    }

    /**
     * 通过CURL发送HTTP请求
     * @param string $url        //请求URL
     * @param array  $postFields //请求参数
     * @return mixed
     */
    private function curlPost($url, $postFields)
    {
        $postFields = http_build_query($postFields);
        $ch         = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    //魔术获取
    public function __get($name)
    {
        return $this->$name;
    }

    //魔术设置
    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}