<?php
/*
 * php version < 5.3
 *
 */

class APIConfig {
    //php sdk verssion
    const PHP_SDK_VERSION = 'PHP_2.3.11';
    //api version
    const API_VERSION = '2';

    const URI_BILL = 'rest/bill'; //支付;支付订单查询(指定id)
    const URI_TEST_BILL = 'rest/sandbox/bill';
    const URI_BILLS = 'rest/bills'; //订单查询
    const URI_TEST_BILLS = 'rest/sandbox/bills';
    const URI_BILLS_COUNT = 'rest/bills/count'; //订单总数查询
    const URI_TEST_BILLS_COUNT = 'rest/sandbox/bills/count';
    const URI_BC_GATEWAY_BANKS = 'rest/bc_gateway/banks'; //获取银行列表

    const URI_REFUND = 'rest/refund';		//退款;预退款批量审核;退款订单查询(指定id)
    const URI_REFUNDS = 'rest/refunds';		//退款查询
    const URI_REFUNDS_COUNT = 'rest/refunds/count'; //退款总数查询
    const URI_REFUND_STATUS = 'rest/refund/status'; //退款状态更新

    const URI_TRANSFERS = 'rest/transfers'; //批量打款 - 支付宝
    const URI_TRANSFER = 'rest/transfer';  //单笔打款 - 支付宝/微信
    const URI_BC_TRANSFER_BANKS = 'rest/bc_transfer/banks'; //BC企业打款 - 支持银行
    const URI_BC_TRANSFER = 'rest/bc_transfer'; //代付 - 银行卡
    const URI_CJ_TRANSFER = 'rest/cj_transfer'; //畅捷代付
    const URI_JD_TRANSFER = 'rest/bc_user_transfer'; //京东代付
    const URI_GATEWAY_TRANSFER = 'rest/gateway/bc_transfer'; //BeePay自动打款 - 打款到银行卡

    //确认支付
    const URI_PAY_CONFIRM = 'rest/bill/confirm';

    const URI_OFFLINE_BILL = 'rest/offline/bill'; //线下支付-撤销订单
    const URI_OFFLINE_BILL_STATUS = 'rest/offline/bill/status'; //线下订单状态查询
    const URI_OFFLINE_REFUND = 'rest/offline/refund'; //线下退款

    const URI_INTERNATIONAL_BILL = 'rest/international/bill';
    const URI_INTERNATIONAL_REFUND = 'rest/international/refund';

    //subscription
    const URI_SUBSCRIPTION = 'subscription';
    const URI_SUBSCRIPTION_PLAN = 'plan';
    const URI_SUBSCRIPTION_BANKS = 'subscription_banks';

    //发送验证码
    const URI_SMS = 'sms';

    //auth
    const URI_AUTH = 'auth';

    //代扣API
    const URI_CARD_CHARGE_SIGN = 'sign';

    //T1代付
    const URI_T1_EXPRESS_TRANSFER_BANKS = 'rest/t1express/transfer/banks';//代付银行列表接口
    const URI_T1_EXPRESS_TRANSFER = 'rest/t1express/transfer';//代付接口

    //user system
    const URI_USERSYS_USER = 'rest/user'; //单个用户注册接口
    const URI_USERSYS_MULTI_USERS = 'rest/users'; //批量用户导入接口／查询接口
    const URI_USERSYS_HISTORY_BILLS = 'rest/history_bills'; //历史数据补全接口（批量）

    //coupon
    const URI_COUPON = 'rest/coupon'; //发放卡券, 优惠券根据ID或其他条件查询
    const URI_COUPON_TEMP = 'rest/coupon/template'; //根据优惠券模板ID或其他条件查询

    const UNEXPECTED_RESULT = "非预期的返回结果:";
    const NEED_PARAM = "需要必填字段:";
    const NEED_VALID_PARAM = "字段值不合法:";
    const NEED_WX_JSAPI_OPENID = "微信公众号支付(WX_JSAPI) 需要openid字段";
    const NEED_RETURN_URL = "当channel参数为 ALI_WEB 或 ALI_QRCODE 或 UN_WEB 或JD_WAP 或 JD_WEB 或 BC_WX_WAP 或 BC_ALI_WEB时 return_url为必填";
    const NEED_IDENTITY_ID = "当channel参数为 YEE_WAP时 identity_id为必填";
    const BILL_TIMEOUT_ERROR = "当channel参数为 JD* 不支持bill_timeout";
    const NEED_QR_PAY_MODE = '当channel参数为 ALI_QRCODE时 qr_pay_mode为必填';
    const NEED_CARDNO = '当channel参数为 YEE_NOBANKCARD时 cardno为必填';
    const NEED_CARDPWD = '当channel参数为 YEE_NOBANKCARD时 cardpwd为必填';
    const NEED_FRQID = '当channel参数为 YEE_NOBANKCARD时 frqid为必填';
    const NEED_TOTAL_FEE = '当channel参数为 BC_EXPRESS时 total_fee单位分,最小金额100分';
    const VALID_BC_PARAM = 'APP ID,APP Secret参数值均不能为空,请重新设置';
    const VALID_SIGN_PARAM = 'APP ID, timestamp,APP(Master) Secret参数值均不能为空,请设置';
    const VALID_MASTER_SECRET = 'Master Secret参数值不能为空,请设置';
    const VALID_APP_SECRET = 'APP Secret参数值不能为空,请设置';

    const VALID_PARAM_RANGE = '参数 %s 不在限定的范围内, 请重新设置';

    /*
	 * bank_code(int 类型) for channel JD_B2B
		9102    中国工商银行      9107    招商银行
		9103    中国农业银行      9108    光大银行
		9104    交通银行         9109    中国银行
		9105    中国建设银行		9110 	 平安银行
	*/
    static function get_bank_code(){
        return array(9102, 9103, 9104, 9105, 9107, 9108, 9109, 9110);
    }

    /*
     * card_type(string 类型) for channel BC_GATEWAY
    */
    static function get_card_type($type = ''){
        $card_type = array(
            '1' => '1',
            '2' => '2'
        );
        if($type && !in_array($type, $card_type)){
            exit('卡类型: 1代表信用卡, 2代表借记卡');
        }
        if($type){
            return $card_type[$type];
        }
        return $card_type;
    }

    /*
     * bank(string 类型) for channel BC_GATEWAY
    */
    static function get_bank($type = ''){
        $banks = array(
            //信用卡
            '1' => array(
                '工商银行', '建设银行', '中国银行', '农业银行', '交通银行', '邮政储蓄银行', '招商银行', '中信银行', '浦发银行', '兴业银行', '民生银行',
                '光大银行', '平安银行', '华夏银行', '广发银行', '上海银行', '宁波银行', '杭州银行', '青岛银行', '北京银行', '浙江稠州银行',
            ),
            //借记卡
            '2' => array('工商银行', '建设银行', '中国银行', '农业银行', '交通银行', '邮政储蓄银行', '招商银行', '中信银行', '浦发银行', '兴业银行',
                '民生银行', '光大银行', '平安银行', '华夏银行', '北京银行', '广发银行', '上海银行', '北京农商行', '重庆农商行', '上海农商行',
                '南京银行', '宁波银行', '杭州银行', '成都银行', '青岛银行', '恒丰银行', '渤海银行', '厦门银行', '陕西信合', '浙江稠州银行',
                '贵州农信')
        );
        $card_type = self::get_card_type($type);
        if(!is_array($card_type)){
            return $banks[$card_type];
        }
        return $banks;
    }

    /*
	 * 结算频率interval(string),
	 * 主要包含任一天，一周，一个月或一年。
	 */
    static function get_interval(){
        return array('day', 'week', 'month', 'year');
    }
}

class BCRESTUtil {
    static final public function getApiUrl() {
        return 'https://api.beecloud.cn';
    }

    static public function get_result($url, $type, $data, $timeout, $returnArr){
        $api_url = self::getApiUrl() . '/' . APIConfig::API_VERSION . '/'. $url;
        $httpResultStr = BCRESTUtil::request($api_url, $type, $data, $timeout);
        $result = json_decode($httpResultStr);
        if (!$result) {
            throw new Exception(APIConfig::UNEXPECTED_RESULT . $httpResultStr);
        }
        return $result;
    }

    static final public function post($api, $data, $timeout, $returnArray) {
        return self::get_result($api, 'post', $data, $timeout, $returnArray);
    }

    static final public function get($api, $data, $timeout, $returnArray, $type = true) {
        return self::get_result($api, $type ? 'get' : 'new_get', $data, $timeout, $returnArray);
    }

    static final public function put($api, $data, $timeout, $returnArray) {
        return self::get_result($api, 'put', $data, $timeout, $returnArray);
    }

    static public function delete($api, $data, $timeout, $returnArray) {
        return self::get_result($api, 'delete', $data, $timeout, $returnArray);
    }

    static final public function request($url, $method, array $data, $timeout) {
        try {
            $timeout = (isset($timeout) && is_int($timeout)) ? $timeout : 20;
            $ch = curl_init();
            /*支持SSL 不验证CA根验证*/
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            /*重定向跟随*/
            if (ini_get('open_basedir') == '' && !ini_get('safe_mode')) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            }
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

            //设置 CURLINFO_HEADER_OUT 选项之后 curl_getinfo 函数返回的数组将包含 cURL
            //请求的 header 信息。而要看到回应的 header 信息可以在 curl_setopt 中设置
            //CURLOPT_HEADER 选项为 true
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLINFO_HEADER_OUT, false);

            //fail the request if the HTTP code returned is equal to or larger than 400
            //curl_setopt($ch, CURLOPT_FAILONERROR, true);
            $header = array("Content-Type:application/json;charset=utf-8;", "Connection: keep-alive;");
            $methodIgnoredCase = strtolower($method);
            switch ($methodIgnoredCase) {
                case "post":
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); //POST数据
                    curl_setopt($ch, CURLOPT_URL, $url);
                    break;
                case "get":
                    curl_setopt($ch, CURLOPT_URL, $url."?para=".urlencode(json_encode($data)));
                    break;
                case "new_get":
                    curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($data));
                    break;
                case "put":
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); //POST数据
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                    curl_setopt($ch, CURLOPT_URL, $url);
                    break;
                case "delete":
                    curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($data));
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                    break;
                default:
                    throw new Exception('不支持的HTTP方式');
                    break;
            }

            $result = curl_exec($ch);
            if (curl_errno($ch) > 0) {
                throw new Exception(curl_error($ch));
            }
            curl_close($ch);
            return $result;
        } catch (Exception $e) {
            return "CURL EXCEPTION: ".$e->getMessage();
        }
    }
}

class BCRESTApi {

    //BeeCloud main pay params
    public static $app_id;
    public static $app_secret;
    public static $master_secret;
    public static $test_secret;

    //Test Model,只提供下单和支付订单查询的Sandbox模式
    public static $mode = false;

    static function getSandbox(){
        return self::$mode;
    }

    static function setSandbox($flag = false){
        self::$mode = $flag;
    }

    /*
	 * @param $app_id beecloud平台的APP ID
	 * @param $app_secret  beecloud平台的APP SECRET
	 * @param $master_secret  beecloud平台的MASTER SECRET
	 * @param $test_secret  beecloud平台的TEST SECRET
	 */
    static function registerApp($app_id, $app_secret, $master_secret = '', $test_secret = ''){
        if(empty($app_id) || empty($app_secret)){
            throw new Exception(APIConfig::VALID_BC_PARAM);
        }
        self::$app_id = $app_id;
        self::$app_secret = $app_secret;
        self::$master_secret = $master_secret;
        self::$test_secret = $test_secret;
    }

    static function get_sign($app_id, $timestamp, $secret){
        if(empty($app_id) || empty($timestamp) || empty($secret)){
            throw new Exception(APIConfig::VALID_SIGN_PARAM);
        }
        return md5($app_id.$timestamp.$secret);
    }

    /*
	 * 验证必填参数
	 */
    static public function verify_need_params($params, $data){
        if(is_string($params)){
            if(!isset($data[$params]) || empty($data[$params])){
                throw new Exception(APIConfig::NEED_PARAM . $params);
            }
        }else if(is_array($params)){
            foreach ($params as $field) {
                if(!isset($data[$field]) || empty($data[$field])){
                    throw new Exception(APIConfig::NEED_PARAM . $field);
                }
            }
        }
    }

    /*
	 * @desc 获取共同的必填参数app_id, app_sign, timestamp
	 * @param $data array
	 * @param $secret_type string
	 *  0: app_secret
	 * 	1: master_secret
	 *  2: test_secret
	 */
    static function get_common_params($data, $secret_type = '0'){
        $secret = '';
        switch($secret_type){
            case '1':
                $secret = self::$master_secret;
                break;
            case '2':
                $secret = self::$test_secret;
                break;
            case '0':
            default:
                $secret = self::$app_secret;
                break;
        }
        if(empty($secret)){
            throw new Exception(APIConfig::NEED_PARAM. 'APP(Master/Test) Secret, 请检查!');
        }
        $data["app_id"] = self::$app_id;
        if(!isset($data["timestamp"])){
            $data["timestamp"] = (int)(microtime(true) * 1000);
        }
        $data["app_sign"] = self::get_sign(self::$app_id, $data["timestamp"], $secret);
        self::verify_need_params(array('app_id', 'timestamp', 'app_sign'), $data);
        return $data;
    }


    /*
	  * @desc 发送短信验证码,返回验证码记录的唯一标识,并且手机端接收到验证码,二者供创建subscription使用
	 * @param array $data, 主要包含以下四个参数:
	 *  app_id string APP ID
	 *  timestamp long 时间戳
	 *  app_sign string 签名验证
	 *  phone string 手机号
	 * @return json:
	 * 	result_code string
	 *  result_msg string
	 *  err_detail string
	 *  sms_id string
	 */
    static public function sms($data){
        $data = self::get_common_params($data);
        self::verify_need_params('phone', $data);
        return BCRESTUtil::post(APIConfig::URI_SMS, $data, 30, false);
    }

    /**
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    static public function bill($data, $method = 'post') {
        $data = self::$mode ? self::get_common_params($data, '2') : self::get_common_params($data, '0');
        self::channelCheck($data);
        if (isset($data["channel"])) {
            switch($data["channel"]){
                case 'ALI_WEB':
                case 'ALI_QRCODE':
                case 'UN_WEB':
                case 'JD_WAP':
                case 'JD_WEB':
                case 'JD_B2B':
                case "BC_GATEWAY":
                case "BC_WX_WAP":
                case "BC_ALI_WEB":
                    //case "BC_EXPRESS":
                    if (!isset($data["return_url"])) {
                        throw new Exception(APIConfig::NEED_RETURN_URL);
                    }
                    break;
            }

            switch ($data["channel"]) {
                case "BC_WX_JSAPI":
                case "WX_JSAPI":
                    if (!isset($data["openid"])) {
                        throw new Exception(APIConfig::NEED_WX_JSAPI_OPENID);
                    }
                    break;
                case "ALI_QRCODE":
                    if (!isset($data["qr_pay_mode"])) {
                        throw new Exception(APIConfig::NEED_QR_PAY_MODE);
                    }
                    break;
                case "JD_B2B":
                    if (!isset($data["bank_code"])) {
                        throw new Exception(APIConfig::NEED_PARAM.'bank_code');
                    }
                    if (!in_array($data["bank_code"], APIConfig::get_bank_code())) {
                        throw new Exception(APIConfig::NEED_VALID_PARAM.'bank_code');
                    }
                    break;
                case "YEE_WAP":
                    if (!isset($data["identity_id"])) {
                        throw new Exception(APIConfig::NEED_RETURN_URL);
                    }
                    break;
                case "YEE_NOBANKCARD":
                    if (!isset($data["cardno"])) {
                        throw new Exception(APIConfig::NEED_CARDNO);
                    }
                    if (!isset($data["cardpwd"])) {
                        throw new Exception(APIConfig::NEED_CARDPWD);
                    }
                    if (!isset($data["frqid"])) {
                        throw new Exception(APIConfig::NEED_FRQID);
                    }
                    break;
                case "JD_WEB":
                case "JD_WAP":
                    if (isset($data["bill_timeout"])) {
                        throw new Exception(APIConfig::BILL_TIMEOUT_ERROR);
                    }
                    break;
                case "KUAIQIAN_WAP":
                case "KUAIQIAN_WEB":
//                    if (isset($data["bill_timeout"])) {
//                        throw new Exception(APIConfig::BILL_TIMEOUT_ERROR);
//                    }
//                    break;
                case "BC_GATEWAY":
//                    self::verify_need_params(array('bank', 'card_type'), $data);
//                    if (!in_array($data["card_type"], APIConfig::get_card_type())) {
//                        throw new Exception(sprintf(APIConfig::VALID_PARAM_RANGE, 'card_type'));
//                    }
                    break;
                case "BC_EXPRESS" :
//                    if ($data["total_fee"] < 100 || !is_int($data["total_fee"])) {
//                        throw new Exception(APIConfig::NEED_TOTAL_FEE);
//                    }
                    break;
            }
        }
        $url = BCRESTApi::getSandbox() ? APIConfig::URI_TEST_BILL : APIConfig::URI_BILL;
        switch ($method) {
            case 'get'://支付订单查询
                if (!isset($data["id"])) {
                    throw new Exception(APIConfig::NEED_PARAM . "id");
                }
                $order_id = $data["id"];
                unset($data["id"]);
                return BCRESTUtil::get($url.'/'.$order_id, $data, 30, false);
                break;
            case 'post': // 支付
                //php sdk version
                $data['bc_analysis'] = (object)array('sdk_version' => APIConfig::PHP_SDK_VERSION);
                if (!isset($data["channel"])) {
                    throw new Exception(APIConfig::NEED_PARAM . "channel");
                }
                if (!isset($data["total_fee"])) {
                    throw new Exception(APIConfig::NEED_PARAM . "total_fee");
                } else if(!is_int($data["total_fee"]) || 1>$data["total_fee"]) {
                    throw new Exception(APIConfig::NEED_VALID_PARAM . "total_fee");
                }

                if (!isset($data["bill_no"])) {
                    throw new Exception(APIConfig::NEED_PARAM . "bill_no");
                }
                if (!preg_match('/^[0-9A-Za-z]{8,32}$/', $data["bill_no"])) {
                    throw new Exception(APIConfig::NEED_VALID_PARAM . "bill_no");
                }

                if (!isset($data["title"])) {
                    throw new Exception(APIConfig::NEED_PARAM . "title");
                }
                return BCRESTUtil::post($url, $data, 30, false);
                break;
            default :
                exit('No this method');
                break;
        }
    }

    static final public function bills(array $data) {
        $data = self::$mode ? self::get_common_params($data, '2') : self::get_common_params($data, '0');
        self::channelCheck($data);

        $url = BCRESTApi::getSandbox() ? APIConfig::URI_TEST_BILLS : APIConfig::URI_BILLS;
        //param validation
        return BCRESTUtil::get($url, $data, 30, false);
    }


    static final public function bills_count(array $data){
        $data = self::$mode ? self::get_common_params($data, '2') : self::get_common_params($data, '0');
        self::channelCheck($data);

        if (isset($data["bill_no"]) && !preg_match('/^[0-9A-Za-z]{8,32}$/', $data["bill_no"])) {
            throw new Exception(APIConfig::NEED_VALID_PARAM . "bill_no");
        }
        $url = BCRESTApi::getSandbox() ? APIConfig::URI_TEST_BILLS_COUNT : APIConfig::URI_BILLS_COUNT;
        return BCRESTUtil::get($url, $data, 30, false);
    }

    static final public function refund(array $data, $method = 'post') {
        $data = $method == 'get' ? self::get_common_params($data, '0') : self::get_common_params($data, '1');

        if (isset($data["channel"])) {
            switch ($data["channel"]) {
                case "ALI":
                case "UN":
                case "WX":
                case "JD":
                case "KUAIQIAN":
                case "YEE":
                case "BD":
                case "BC":
                    break;
                default:
                    throw new Exception(APIConfig::NEED_VALID_PARAM . "channel");
                    break;
            }
        }

        switch ($method){
            case 'put': //预退款批量审核
                if (!isset($data["channel"])) {
                    throw new Exception(APIConfig::NEED_PARAM . "channel");
                }
                if (!isset($data["ids"])) {
                    throw new Exception(APIConfig::NEED_PARAM . "ids");
                }
                if (!is_array($data["ids"])) {
                    throw new Exception(APIConfig::NEED_VALID_PARAM . "ids(array)");
                }
                if (!isset($data["agree"])) {
                    throw new Exception(APIConfig::NEED_PARAM . "agree");
                }
                return BCRESTUtil::put(APIConfig::URI_REFUND, $data, 30, false);
                break;
            case 'get'://退款订单查询
                if (!isset($data["id"])) {
                    throw new Exception(APIConfig::NEED_PARAM . "id");
                }
                $order_id = $data["id"];
                unset($data["id"]);
                return BCRESTUtil::get(APIConfig::URI_REFUND.'/'.$order_id, $data, 30, false);
                break;
            case 'post': //退款
            default :
                if (!isset($data["bill_no"])) {
                    throw new Exception(APIConfig::NEED_PARAM . "bill_no");
                }
                if (!preg_match('/^[0-9A-Za-z]{8,32}$/', $data["bill_no"])) {
                    throw new Exception(APIConfig::NEED_VALID_PARAM . "bill_no");
                }

                if (!isset($data["refund_no"])) {
                    throw new Exception(APIConfig::NEED_PARAM . "refund_no");
                }
                if (!preg_match('/^\d{8}[0-9A-Za-z]{3,24}$/', $data["refund_no"])) {
                    throw new Exception(APIConfig::NEED_VALID_PARAM . "refund_no");
                }

                if(!is_int($data["refund_fee"]) || 1>$data["refund_fee"]) {
                    throw new Exception(APIConfig::NEED_VALID_PARAM . "refund_fee");
                }
                return BCRESTUtil::post(APIConfig::URI_REFUND, $data, 30, false);
                break;
        }
    }


    static final public function refunds(array $data) {
        $data = self::get_common_params($data, '0');
        self::channelCheck($data);
        //param validation
        return BCRESTUtil::get(APIConfig::URI_REFUNDS, $data, 30, false);
    }

    static final public function refunds_count(array $data) {
        $data = self::get_common_params($data, '0');
        self::channelCheck($data);
        //param validation
        return BCRESTUtil::get(APIConfig::URI_REFUNDS_COUNT, $data, 30, false);
    }

    static final public function refundStatus(array $data) {
        $data = self::get_common_params($data, '0');

        switch ($data["channel"]) {
            case "WX":
            case "YEE":
            case "KUAIQIAN":
            case "BD":
                break;
            default:
                throw new Exception(APIConfig::NEED_VALID_PARAM . "channel");
                break;
        }

        if (!isset($data["refund_no"])) {
            throw new Exception(APIConfig::NEED_PARAM . "refund_no");
        }
        //param validation
        return BCRESTUtil::get(APIConfig::URI_REFUND_STATUS, $data, 30, false);
    }

    //单笔打款 - 支付宝/微信
    static final public function transfer(array $data) {
        $data = self::get_common_params($data, '1');
        switch ($data["channel"]) {
            case "WX_REDPACK":
                if (!isset($data['redpack_info'])) {
                    throw new Exception(APIConfig::NEED_PARAM . 'redpack_info');
                }
                break;
            case "WX_TRANSFER":
                break;
            case "ALI_TRANSFER":
                $aliRequireNames = array(
                    "channel_user_name",
                    "account_name"
                );

                foreach($aliRequireNames as $v) {
                    if (!isset($data[$v])) {
                        throw new Exception(APIConfig::NEED_PARAM . $v);
                    }
                }
                break;
            default:
                throw new Exception(APIConfig::NEED_VALID_PARAM . "channel = ALI_TRANSFER | WX_TRANSFER | WX_REDPACK");
                break;
        }

        $requiedNames = array(
            "transfer_no",
            "total_fee",
            "desc",
            "channel_user_id"
        );

        foreach($requiedNames as $v) {
            if (!isset($data[$v])) {
                throw new Exception(APIConfig::NEED_PARAM . $v);
            }
        }

        return BCRESTUtil::post(APIConfig::URI_TRANSFER, $data, 30, false);
    }

    //批量打款 - 支付宝
    static final public function transfers(array $data) {
        $data = self::get_common_params($data, '1');
        switch ($data["channel"]) {
            case "ALI":
                break;
            default:
                throw new Exception(APIConfig::NEED_VALID_PARAM . "channel only ALI");
                break;
        }

        if (!isset($data["batch_no"])) {
            throw new Exception(APIConfig::NEED_PARAM . "batch_no");
        }

        if (!isset($data["account_name"])) {
            throw new Exception(APIConfig::NEED_PARAM . "account_name");
        }

        if (!isset($data["transfer_data"])) {
            throw new Exception(APIConfig::NEED_PARAM . "transfer_data");
        }

        if (!is_array($data["transfer_data"])) {
            throw new Exception(APIConfig::NEED_VALID_PARAM . "transfer_data(array)");
        }

        return BCRESTUtil::post(APIConfig::URI_TRANSFERS, $data, 30, false);
    }

    //BC企业打款 - 支持bank
    static final public function bc_transfer_banks($data) {
        if (!isset($data["type"])) {
            throw new Exception(APIConfig::NEED_PARAM . "type");
        }

        if(!in_array($data['type'], array('P_DE', 'P_CR', 'C'))) throw new Exception(APIConfig::NEED_VALID_PARAM . 'type(P_DE, P_CR, C)');

        return BCRESTUtil::get(APIConfig::URI_BC_TRANSFER_BANKS, $data, 30, false);
    }

    //BC企业打款 - 银行卡
    static final public function bc_transfer(array $data) {
        $data = self::get_common_params($data, '1');
        $params = array(
            'total_fee', 'bill_no', 'title', 'trade_source', 'bank_fullname',
            'card_type', 'account_type', 'account_no', 'account_name'
        );
        foreach ($params as $v) {
            if (!isset($data[$v])) {
                throw new Exception(APIConfig::NEED_PARAM . $v);
            }
        }
        if(!in_array($data['card_type'], array('DE', 'CR'))) throw new Exception(APIConfig::NEED_VALID_PARAM . 'card_type(DE, CR)');
        if(!in_array($data['account_type'], array('P', 'C'))) throw new Exception(APIConfig::NEED_VALID_PARAM . 'account_type(P, C)');

        $url = APIConfig::URI_BC_TRANSFER;
        if(isset($data['channel']) &&  $data['channel'] == 'JD_TRANSFER'){
            $url = APIConfig::URI_JD_TRANSFER;
        }
        return BCRESTUtil::post($url, $data, 30, false);
    }

    //畅捷企业打款
    static final public function cj_transfer(array $data) {
        $data = self::get_common_params($data, '1');
        $params = array(
            'total_fee', 'bill_no', 'title', 'bank_name', 'bank_account_no', 'bank_branch', 'province', 'city',
            'card_type', 'card_attribute', 'account_name'
        );
        foreach ($params as $v) {
            if (!isset($data[$v])) {
                throw new Exception(APIConfig::NEED_PARAM . $v);
            }
        }
        if(!in_array($data['card_type'], array('DEBIT', 'CREDIT'))) throw new Exception(APIConfig::NEED_VALID_PARAM . 'card_type(DEBIT, CREDIT)');
        if(!in_array($data['card_attribute'], array('B', 'C'))) throw new Exception(APIConfig::NEED_VALID_PARAM . 'card_attribute(B, C)');

        return BCRESTUtil::post(APIConfig::URI_CJ_TRANSFER, $data, 30, false);
    }


    //BeePay自动打款 - 打款到银行卡
    function gateway_transfer($data){
        if(!isset($data['app_id'])){
            $data['app_id'] = self::$app_id;
        }
        /*
         * 对关键参数的签名，签名方式为MD5（32位小写字符）, 编码格式为UTF-8
         * 验签规则即：app_id + bill_no + withdraw_amount + bank_account_no + master_secret的MD5生成的签名
         * 其中master_secret为用户创建Beecloud App时获取的参数。
         */
        if(!isset($data['signature'])){
            $data['signature'] = md5($data["app_id"] . $data["bill_no"] . $data["withdraw_amount"] . $data["bank_account_no"] . self::$master_secret);
        }
        $params = array(
            'app_id', 'withdraw_amount', 'bill_no', 'transfer_type', 'bank_name',
            'bank_account_no', 'bank_account_name', 'bank_code', 'signature', 'note'
        );
        foreach ($params as $v) {
            if (!isset($data[$v])) {
                throw new Exception(APIConfig::NEED_PARAM . $v);
            }
        }
        if(!in_array($data['transfer_type'], array('1', '2'))) throw new Exception(APIConfig::NEED_VALID_PARAM . 'transfer_type(1, 2)');
        return BCRESTUtil::post(APIConfig::URI_GATEWAY_TRANSFER, $data, 30, false);
    }

    //T1代付接口
    function bct1_transfer($data){
        if(!isset($data['app_id'])){
            $data['app_id'] = self::$app_id;
        }
        $params = array(
            'app_id', 'total_fee', 'bill_no', 'bank_name', 'bank_account_no', 'bank_account_name', 'is_personal'
        );
        foreach ($params as $v) {
            if (!isset($data[$v])) {
                throw new Exception(APIConfig::NEED_PARAM . $v);
            }
        }
        /*
         * 对关键参数的签名，签名方式为MD5（32位小写字符）, 编码格式为UTF-8
         * 验签规则即：app_id + bill_no + total_fee + bank_account_no的MD5生成的签名
         */
        if(!isset($data['signature'])){
            $data['signature'] = md5($data["app_id"] . $data["bill_no"] . $data["total_fee"] . $data["bank_account_no"] . self::$master_secret);
        }
        if(!in_array($data['is_personal'], array('0', '1'))) throw new Exception(APIConfig::NEED_VALID_PARAM . 'is_personal(0, 1)');
        return BCRESTUtil::post(APIConfig::URI_T1_EXPRESS_TRANSFER, $data, 30, false);
    }


    static final public function offline_bill(array $data) {
        $data = self::get_common_params($data, '0');
        //php sdk version
        $data['bc_analysis'] = (object)array('sdk_version' => APIConfig::PHP_SDK_VERSION);
        if (isset($data["channel"])) {
            switch ($data["channel"]) {
                case "WX_SCAN":
                case "ALI_SCAN":
                case "BC_WX_SCAN":
                case "BC_ALI_SCAN":
                    if (!isset($data['method']) && !isset($data['auth_code'])) {
                        throw new Exception(APIConfig::NEED_PARAM . "auth_code");
                    }
                    break;
                case "WX_NATIVE":
                case "ALI_OFFLINE_QRCODE":
                case "BC_ALI_QRCODE":
                case "SCAN":
                    break;
                default:
                    throw new Exception(APIConfig::NEED_VALID_PARAM . "channel = WX_NATIVE | WX_SCAN | BC_WX_SCAN | ALI_OFFLINE_QRCODE | BC_ALI_QRCODE | ALI_SCAN | BC_ALI_SCAN | SCAN");
                    break;
            }
        }

        if (!isset($data["bill_no"])) {
            throw new Exception(APIConfig::NEED_PARAM . "bill_no");
        }
        if (!preg_match('/^[0-9A-Za-z]{8,32}$/', $data["bill_no"])) {
            throw new Exception(APIConfig::NEED_VALID_PARAM . "bill_no");
        }

        if (!isset($data['method'])) {
            if (!isset($data["channel"])) {
                throw new Exception(APIConfig::NEED_PARAM . "channel");
            }
            if (!isset($data["total_fee"])) {
                throw new Exception(APIConfig::NEED_PARAM . "total_fee");
            } else if(!is_int($data["total_fee"]) || 1>$data["total_fee"]) {
                throw new Exception(APIConfig::NEED_VALID_PARAM . "total_fee");
            }

            if (!isset($data["title"])) {
                throw new Exception(APIConfig::NEED_PARAM . "title");
            }
            return BCRESTUtil::post(APIConfig::URI_OFFLINE_BILL, $data, 30, false);
        }
        $bill_no = $data["bill_no"];
        unset($data["bill_no"]);
        return BCRESTUtil::post(APIConfig::URI_OFFLINE_BILL.'/'.$bill_no, $data, 30, false);
    }

    static final public function offline_bill_status(array $data) {
        $data = self::get_common_params($data, '0');

        if (isset($data["channel"])) {
            switch ($data["channel"]) {
                case "WX_SCAN":
                case "ALI_SCAN":
                case "WX_NATIVE":
                case "ALI_OFFLINE_QRCODE":
                    break;
                default:
                    throw new Exception(APIConfig::NEED_VALID_PARAM . "channel = WX_NATIVE | WX_SCAN | ALI_OFFLINE_QRCODE | ALI_SCAN");
                    break;
            }
        }

        if (!isset($data["bill_no"])) {
            throw new Exception(APIConfig::NEED_PARAM . "bill_no");
        }
        if (!preg_match('/^[0-9A-Za-z]{8,32}$/', $data["bill_no"])) {
            throw new Exception(APIConfig::NEED_VALID_PARAM . "bill_no");
        }
        return BCRESTUtil::post(APIConfig::URI_OFFLINE_BILL_STATUS, $data, 30, false);
    }

    static final public function offline_refund(array $data){
        $data = self::get_common_params($data, '1');
        if (isset($data['channel'])) {
            switch ($data["channel"]) {
                case "ALI":
                case "WX":
                case "BC":
                    break;
                default:
                    throw new Exception(APIConfig::NEED_VALID_PARAM . "channel = ALI | WX | BC");
                    break;
            }
        }

        if (!isset($data["refund_fee"])) {
            throw new Exception(APIConfig::NEED_PARAM . "refund_fee");
        } else if(!is_int($data["refund_fee"]) || 1>$data["refund_fee"]) {
            throw new Exception(APIConfig::NEED_VALID_PARAM . "refund_fee");
        }

        if (!isset($data["bill_no"])) {
            throw new Exception(APIConfig::NEED_PARAM . "bill_no");
        }
        if (!preg_match('/^[0-9A-Za-z]{8,32}$/', $data["bill_no"])) {
            throw new Exception(APIConfig::NEED_VALID_PARAM . "bill_no");
        }

        if (!isset($data["refund_no"])) {
            throw new Exception(APIConfig::NEED_PARAM . "refund_no");
        }
        if (!preg_match('/^\d{8}[0-9A-Za-z]{3,24}$/', $data["refund_no"]) || preg_match('/^\d{8}0{3}/', $data["refund_no"])) {
            throw new Exception(APIConfig::NEED_VALID_PARAM . "refund_no");
        }

        return BCRESTUtil::post(APIConfig::URI_OFFLINE_REFUND, $data, 30, false);
    }


    /**
     * @desc: 签约API
     *
     * @param $data
     *   mobile 手机号
     *   bank  银行名称
     *   id_no 身份证号
     *   name   姓名
     *   card_no 银行卡号(借记卡,不支持信用卡)
     *   sms_id  获取验证码接口返回验证码记录的唯一标识
     *   sms_code 手机端接收到验证码
     *
     * @return json
     * @author: jason
     * @since: 2016-09-01
     */
    static public function card_charge_sign($data){
        $data = self::get_common_params($data);
        self::verify_need_params(array('mobile', 'bank', 'id_no', 'name', 'card_no', 'sms_id', 'sms_code'), $data);
        return BCRESTUtil::post(APIConfig::URI_CARD_CHARGE_SIGN, $data, 30, false);
    }

    /**
     * @desc: 认证支付－确认支付
     *
     * @param $data
     *   token 渠道返回的token
     *   bc_bill_id  BeeCloud生成的唯一支付记录id
     *   verify_code 短信验证码
     *
     * @return json
     * @author: jason
     * @since: 2016-09-01
     */
    static public function confirm_bill_pay($data){
        $data = self::get_common_params($data);
        self::verify_need_params(array('token', 'bc_bill_id', 'verify_code'), $data);
        return BCRESTUtil::post(APIConfig::URI_PAY_CONFIRM, $data, 30, false);
    }

    static public function get_banks($data, $type = ''){
        $data = self::get_common_params($data);
        switch ($type){
            case 'BC_GATEWAY':
                self::verify_need_params(array('card_type'), $data);
                if(isset($data['pay_type']) && !in_array($data['pay_type'], array('B2C', 'B2B')))
                    throw new Exception(APIConfig::NEED_VALID_PARAM . 'pay_type(B2C, B2B)');
                return BCRESTUtil::get(APIConfig::URI_BC_GATEWAY_BANKS, $data, 30, false);
                break;
            case 'T1_EXPRESS_TRANSFER':
                $data = self::get_common_params($data);
                return BCRESTUtil::get(APIConfig::URI_T1_EXPRESS_TRANSFER_BANKS, $data, 30, false);
                break;
            default:
                break;
        }
    }

    static final private function channelCheck($data){
        if (isset($data["channel"])) {
            switch ($data["channel"]) {
                case "ALI":
                case "ALI_WEB":
                case "ALI_WAP":
                case "ALI_QRCODE":
                case "ALI_APP":
                case "ALI_OFFLINE_QRCODE":
                case "UN":
                case "UN_WEB":
                case "UN_APP":
                case "UN_WAP":
                case "WX":
                case "WX_JSAPI":
                case "WX_NATIVE":
                case "WX_APP":
                case "WX_WAP":
                case "WX_MINI":
                case "JD":
                case "JD_WEB":
                case "JD_WAP":
                case "JD_B2B":
                case "YEE":
                case "YEE_WAP":
                case "YEE_WEB":
                case "YEE_NOBANKCARD":
                case "KUAIQIAN":
                case "KUAIQIAN_WAP":
                case "KUAIQIAN_WEB":
                case "BD":
                case "BD_WAP":
                case "BD_WEB":
                case "PAYPAL":
                case "PAYPAL_SANDBOX":
                case "PAYPAL_LIVE":
                case "BC" :
                case "BC_GATEWAY" :
                case "BC_EXPRESS" :
                case "BC_APP" :
                case "BC_NATIVE" :
                case "BC_WX_WAP" :
                case "BC_WX_JSAPI" :
                case "BC_WX_SCAN" :
                case "BC_WX_MINI" :
                case "BC_CARD_CHARGE" :
                case "BC_ALI_QRCODE" :
                case "BC_ALI_SCAN" :
                case "BC_ALI_WAP":
                case "BC_ALI_WEB":
                case "BC_ALI_JSAPI":
                    break;
                default:
                    throw new Exception(APIConfig::NEED_VALID_PARAM . "channel");
                    break;
            }
        }
    }
}


/**
 * paypal pay
 */
class BCRESTInternational extends BCRESTApi{

    static public function bill($data, $method = 'post') {
        $data = parent::get_common_params($data, '0');
        parent::verify_need_params('currency', $data);
        switch ($data["channel"]) {
            case "PAYPAL_PAYPAL":
                parent::verify_need_params('return_url', $data);
                break;
            case "PAYPAL_CREDITCARD":
                self::verify_need_params('credit_card_info', $data);
                break;
            case "PAYPAL_SAVED_CREDITCARD":
                self::verify_need_params('credit_card_id', $data);
                break;
            default:
                throw new Exception(APIConfig::NEED_VALID_PARAM . "channel");
                break;
        }

        self::verify_need_params(array('total_fee', 'bill_no', 'title'), $data);

        if(!is_int($data["total_fee"]) || $data["total_fee"] < 1) {
            throw new Exception(APIConfig::NEED_VALID_PARAM . "total_fee");
        }
        return BCRESTUtil::post(APIConfig::URI_INTERNATIONAL_BILL, $data, 30, false);
    }
}


class Subscriptions extends BCRESTApi{

    /*
	  * @desc 获取支持银行列表
	 * @param array $data, 主要包含以下三个参数:
	 * 	app_id string APP ID
	 * 	timestamp long 时间戳
	 * 	app_sign string 签名验证
	 * @return json:
	 * 	result_code string
	 *  result_msg string
	 *  err_detail string
	 *  banks list
	 *  common_banks list
	 */
    static public function banks($data){
        $data = parent::get_common_params($data);
        return BCRESTUtil::get(APIConfig::URI_SUBSCRIPTION_BANKS, $data, 30, false, false);
    }

    /*
	  * @desc 发送短信验证码,返回验证码记录的唯一标识,并且手机端接收到验证码,二者供创建subscription使用
	 * @param array $data, 主要包含以下四个参数:
	 *  app_id string APP ID
	 *  timestamp long 时间戳
	 *  app_sign string 签名验证
	 *  phone string 手机号
	 * @return json:
	 * 	result_code string
	 *  result_msg string
	 *  err_detail string
	 *  sms_id string
	 */
    static public function sms($data){
        $data = parent::get_common_params($data);
        parent::verify_need_params('phone', $data);
        return BCRESTUtil::post(APIConfig::URI_SMS, $data, 30, false);
    }

    /*
	  * @desc 创建订阅计划plan
	 * @param array $data,主要包含参数:
	 *  fee int 单位分(必填), fee必须不小于 150分, 不大于5000000分
	 *  interval string 结算频率(必填), 主要包含任一天(day)/一周(week)/一个月(month)/一年(year)
	 *  name string 订阅计划的名称(必填)
	 *	currency string, 对照表请参考:https://github.com/beecloud/beecloud-rest-api/tree/master/international
	 *	interval_count 	int 每个订阅结算之间的时间间隔数。默认值1
	 * 		eg: 时间间隔=月，interval_count=3即每3个月。允许一年一次（1年，12个月或52周）的最大值。
	 *	trial_days 	int 指定试用期天数（整数）,默认是0
	 *  optional json格式
	 * @return json
	 */
    static public function plan($data){
        $data = parent::get_common_params($data);
        if(!in_array($data["interval"], APIConfig::get_interval())){
            throw new Exception(sprintf(APIConfig::VALID_PARAM_RANGE, "interval"));
        }
        parent::verify_need_params(array('fee', 'name'), $data);
        if(!is_int($data["fee"])){
            throw new Exception(APIConfig::NEED_VALID_PARAM);
        }
        return BCRESTUtil::post(APIConfig::URI_SUBSCRIPTION_PLAN, $data, 30, false);
    }

    /*
	 * @desc 通过ID查询订阅计划
	 * @param $objectid string 订阅记录的唯一标识(必填)
	 * @param $data array()
	 *  timestamp long 时间戳(必填)
	 *
	 * @desc 按条件查询订阅计划
	 * @param $data array()
	 *  name_with_substring string 按照订阅计划的名称模糊查询
	 *  interval string 结算频率, 主要包含任一天(day)/一周(week)/一个月(month)/一年(year)
	 *	interval_count 	int 每个订阅结算之间的时间间隔数。默认值1
	 * 		eg: 时间间隔=月，interval_count=3即每3个月。允许一年一次（1年，12个月或52周）的最大值。
	 *	trial_days 	int 指定试用期天数（整数）,默认是0
	 *  timestamp long 时间戳(必填)
	 */
    static function query_plan($data, $objectid = ''){
        if(!empty($objectid)){
            $url = APIConfig::URI_SUBSCRIPTION_PLAN.'/'.$objectid;
        }else{
            $url = APIConfig::URI_SUBSCRIPTION_PLAN;
        }
        $data = parent::get_common_params($data);
        return BCRESTUtil::get($url, $data, 30, false, false);
    }

    /*
	 * @desc 更新订阅计划
     * @param $objectid string 订阅记录的唯一标识(必填)
	 * @param $data array()
	 *  timestamp long 时间戳(必填)
	 *
	 *  name string 订阅计划的名称
	 *  optional json
	 */
    static function update_plan($data, $objectid){
        if(empty($objectid)){
            throw new Exception('请设置plan的唯一标识objectid');
        };
        $data = parent::get_common_params($data);
        return BCRESTUtil::put(APIConfig::URI_SUBSCRIPTION_PLAN.'/'.$objectid, $data, 30, false);
    }

    /*
	 * @desc 删除订阅计划
	 * @param $data array()
	 * 	objectid string 订阅计划的唯一标识
	 *  timestamp long 时间戳
	 */
    static function del_plan($data, $objectid){
        if(empty($objectid)){
            throw new Exception('请设置plan的唯一标识objectid');
        };
        $data = parent::get_common_params($data);
        return BCRESTUtil::delete(APIConfig::URI_SUBSCRIPTION_PLAN.'/'.$objectid, $data, 30, false);
    }

    /*
	  * @desc 创建订阅记录subscription
	 * @param array $data, 主要包含参数:
	 *  buyer_id string 订阅的buyer ID(必填)，可以是用户email，也可以是商户系统中的用户ID
	 *  plan_id string  订阅计划的唯一标识(必填)
	 *  card_id string  用于该订阅记录的的card
	 *	bank_name string 订阅用户银行名称（支持列表可参考API获取支持银行列表,即获取方法subscription_banks)
	 *	card_no string 	订阅用户银行卡号
	 *	id_name string 	订阅用户身份证姓名
	 *	id_no 	string 	订阅用户身份证号
	 *	mobile 	string 	订阅用户银行预留手机号
	 *  amount double 	金额用于正在创建的订阅,默认值1.0
	 *  coupon_id string 应用到该订阅的优惠券ID
	 *  trial_end long Unix时间戳表示试用期，客户将被指控的第一次之前拿到的结束。
	 * 		如果设置trial_end将覆盖客户预订了计划的默认试用期。特殊值现在可以提供立即停止客户的试用期。
	 *  optional json
	 * @remark:
	 *  1.card_id 与 {bank_name, card_no, id_name, id_no, mobile} 二者必填其一
	 *  2.card_id 为订阅成功时webhook返回里带有的字段，商户可保存下来下次直接使用
	 *  3.bank_name可参考下述API获取支持银行列表，选择传入
	 * @return json
	 */
    static public function subscription($data){
        $data = parent::get_common_params($data);
        parent::verify_need_params(array('buyer_id', 'plan_id'), $data);
        if(isset($data['card_id']) && !empty($data['card_id'])){

        }else{
            parent::verify_need_params(array('bank_name', 'card_no', 'id_name', 'id_no', 'mobile'), $data);
        }
        return BCRESTUtil::post(APIConfig::URI_SUBSCRIPTION, $data, 30, false);
    }

    /*
	 * @desc 通过ID查询订阅记录
     * @param $objectid string 订阅记录的唯一标识(必填)
	 * @param $data array()
	 *  timestamp long 时间戳(必填)
	 *
	 * @desc 按条件查询订阅
	 * @param $data array()
	 *  buyer_id string 订阅的buyer ID，可以是用户email，也可以是商户系统中的用户ID
	 *  plan_id string  订阅计划的唯一标识(必填)
	 *  card_id string  用于该订阅记录的的card
	 *  timestamp long 时间戳(必填)
	 */
    static function query_subscription($data, $objectid = ''){
        if(!empty($objectid)){
            $url = APIConfig::URI_SUBSCRIPTION.'/'.$objectid;
        }else{
            $url = APIConfig::URI_SUBSCRIPTION;
        }
        $data = parent::get_common_params($data);
        return BCRESTUtil::get($url, $data, 30, false, false);
    }


    /*
	 * @desc 更新订阅
     * @param $objectid string 订阅记录的唯一标识(必填)
	 * @param $data array()
	 *  timestamp long 时间戳(必填)
	 *
	 *  buyer_id string 订阅的buyer ID，可以是用户email，也可以是商户系统中的用户ID
	 *  plan_id string  订阅计划的唯一标识
	 *  card_id string  用于该订阅记录的的card
	 *  amount double 	金额用于正在创建的订阅,默认值1.0
	 *  coupon_id string 应用到该订阅的优惠券ID
	 *  trial_end long Unix时间戳表示试用期，客户将被指控的第一次之前拿到的结束。
	 * 		如果设置trial_end将覆盖客户预订了计划的默认试用期。特殊值现在可以提供立即停止客户的试用期。
	 *  optional json
	 */
    static function update_subscription($data, $objectid){
        if(empty($objectid)){
            throw new Exception('请设置subscription的唯一标识objectid');
        };
        $data = parent::get_common_params($data);
        return BCRESTUtil::put(APIConfig::URI_SUBSCRIPTION.'/'.$objectid, $data, 30, false);
    }

    /*
	 * @desc 取消订阅
     * @param $objectid string 订阅记录的唯一标识
	 * @param $data array()
	 *  timestamp long 时间戳
	 *  at_period_end boolean 默认false,设置为true将推迟预订的取消，直到当前周期结束。
	 */
    static function cancel_subscription($data, $objectid){
		if(empty($objectid)){
			throw new Exception('请设置subscription的唯一标识objectid');
		};
        $data = parent::get_common_params($data);
        return BCRESTUtil::delete(APIConfig::URI_SUBSCRIPTION.'/'.$objectid, $data, 30, false);
    }
}


class Auths extends BCRESTApi{
    /*
	 * @desc 二要素, 三要素, 四要素鉴权, 如果鉴权成功，会自动在全局的card表中创建一条card记录
     * 二要素: (name, id_no)
     * 三要素: (name, id_no, card_no)
     * 四要素: (name, id_no, card_no, mobile)
	 * @param array $data, 主要包含以下四个参数:
	 * 	name string 身份证姓名(必填)
	 *  id_no string 身份证号(必填)
	 *  card_no string 用户银行卡卡号
	 *  mobile string 手机号
	 * @return json
	 *  "card_id": "xxx", 要素认证成功返回card_id(即该记录的唯一标识)
	 *  "auth_result": true, 要素认证是否成功
	 *  "auth_msg": "xxx不匹配", 返回给用户的直接让用户能看懂的鉴权结果消息
	 */
    static public function auth($data){
        $data = parent::get_common_params($data);
        parent::verify_need_params(array('name', 'id_no'), $data);
        return BCRESTUtil::post(APIConfig::URI_AUTH, $data, 30, false);
    }
}

Class Usersys extends BCRESTApi{
    /*
     * @desc 单个用户注册接口
     * @params
     *    buyer_id string (必填)	商户为自己的用户分配的ID。可以是email、手机号、随机字符串等。最长32位。在商户自己系统内必须保证唯一
     * @return json
     *    result_code int 	返回码，0为正常
     *    result_msg 	string 	返回信息， OK为正常
     *    err_detail 	string 	具体错误信息
     */
    static public function register($data){
        $data = parent::get_common_params($data);
        parent::verify_need_params(array('buyer_id'), $data);
        return BCRESTUtil::post(APIConfig::URI_USERSYS_USER, $data, 30, false);
    }

    /*
     * @desc 批量用户导入接口
     * @params
     *    email string (必填) 用户账号
     *    buyer_ids array (必填) 商户为自己的多个用户分配的IDs。每个ID可以是email、手机号、随机字符串等；最长32位；在商户自己系统内必须保证唯一。
     * @return json
     *    result_code int 	返回码，0为正常
     *    result_msg 	string 	返回信息， OK为正常
     *    err_detail 	string 	具体错误信息
     */
    static public function import_users($data){
        $data = parent::get_common_params($data);
        parent::verify_need_params(array('email', 'buyer_ids'), $data);
        return BCRESTUtil::post(APIConfig::URI_USERSYS_MULTI_USERS, $data, 30, false);
    }

    /*
     * @desc 商户用户批量查询接口
     * @params
     *    email string (非必填) 用户账号
     *    start_time int (非必填) 起始时间。该接口会返回此时间戳之后创建的用户。毫秒时间戳, 13位
     *    end_time int (非必填) 结束时间。该接口会返回此时间戳之前创建的用户。毫秒时间戳, 13位
     *    注意：如果传入email, 就查询该email下的用户;如果不传email，就查询注册时使用该app_id注册的用户
     * @return json
     *    result_code int 	返回码，0为正常
     *    result_msg 	string 	返回信息， OK为正常
     *    err_detail 	string 	具体错误信息
     *    users 	array 	获取到的用户信息列表
     */
    static public function query_users($data){
        $data = parent::get_common_params($data);
        return BCRESTUtil::get(APIConfig::URI_USERSYS_MULTI_USERS, $data, 30, false);
    }

    /*
     * @desc 历史数据补全接口（批量）。该接口要求用户传入订单号与用户ID的对应关系，该接口会将历史数据中，属于该用户ID的订单数据进行标识。
     * @params
     *    bill_info string (必填), json字符串key为buyer_id，value是订单列表
     *      eg: {"aaa@bb.com":["20170302005"], "xxx@bb.com":["20170302001","20170302002","20170302011"]}
     * @return json
     *      result_code int 	返回码，0为正常
     *      result_msg 	string 	返回信息， OK为正常
     *      err_detail 	string 	具体错误信息
     *      如果更新失败会返回以下信息：
     *          failed_bills array 更新失败的订单信息,可能是部分信息。key是buyer_id, value是隶属于该buyer_id的订单列表
     *      注意：重试时，请依据更新失败返回的失败订单信息进行重试，以避免重复更新历史订单信息
     */
    static public function supply_bills($data){
        $data = parent::get_common_params($data);
        parent::verify_need_params(array('bill_info'), $data);
        return BCRESTUtil::post(APIConfig::URI_USERSYS_HISTORY_BILLS, $data, 30, false);
    }
}


Class Coupons extends BCRESTApi  {

    /*
     * desc: 根据优惠券模板ID或者其他条件查询
     * @params $objectid string
     *  返回coupon_template，即优惠券模板详情
     *
     * @params $data array:
     *  name string 模板名（如果提供则限制模板名）
     *  created_before long 毫秒数时间戳（如果提供则限制创建时间戳>=该时间戳的模板）
     *  created_after long	毫秒数时间戳（如果提供则限制创建时间戳<该时间戳的模板）
     *  skip long	查询起始位置，默认为0
     *  limit long	查询的条数，默认为10
     *  返回coupon_templates，即优惠券模板列表，此处返回的列表都属于验证签名所用应用app_id下
     *
     */
    public static function query_coupon_temp($data, $objectid = ''){
        $data = parent::get_common_params($data);
        if(!empty($objectid)){
            $url = APIConfig::URI_COUPON_TEMP.'/'.$objectid;
        }else{
            $url = APIConfig::URI_COUPON_TEMP;
        }
        return BCRESTUtil::get($url, $data, 30, false);
    }

    /*
     * desc: 根据优惠券ID或者其他条件查询
     * @params $objectid string
     *  返回coupon，即优惠券详情
     *
     * @params $data array:
     *  user_id string 用户ID（如果提供则限制领券的用户ID）
     *  template_id string 优惠券的模板ID（如果提供则限制优惠券的模板ID）
     *  status	int	优惠券的状态（如果提供则限制优惠券的状态，0表示未使用，1表示已使用（核销））
     *  limit_fee int 一般传入订单金额，返回满足限额的优惠券，比如传入11000，返回满100元减10元的优惠券
     *  created_before int 毫秒数时间戳（如果提供则限制创建时间戳>=该时间戳的优惠券）
     *  created_after int	毫秒数时间戳（如果提供则限制创建时间戳<该时间戳的优惠券）
     *  skip int	查询起始位置，默认为0
     *  limit int	查询的条数，默认为10
     *  返回coupons，即优惠券列表，此处返回的列表都属于验证签名所用应用app_id下
     *
     */
    public static function query_coupon($data, $objectid = ''){
        if(!empty($objectid)){
            $url = APIConfig::URI_COUPON.'/'.$objectid;
        }else{
            $url = APIConfig::URI_COUPON;
        }
        $data = parent::get_common_params($data);
        return BCRESTUtil::get($url, $data, 30, false);
    }

    /*
     * 发放卡券
     * @params $data array:
     *  user_id string 用户ID
     *  template_id string 优惠券的模板ID
     */
    public static function coupon($data){
        $data = parent::get_common_params($data);
        parent::verify_need_params(array('template_id', 'user_id'), $data);
        return BCRESTUtil::post(BCRESTUtil::URI_COUPON, $data, 30, false);
    }
}
