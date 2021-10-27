<?php
/**
 * Created by PhpStorm.
 * User: lkp
 * Date: 18-6-13
 * Time: 下午8:18
 */
namespace common\models;
use common\alipay\aop\AopClient;
use common\alipay\aop\request\AlipayTradeAppPayRequest;
use common\alipay\aop\request\AlipayFundTransToaccountTransferRequest;

use Yii;
class AlipayModel{

    /**
     * 获取支付宝的支付信息-app支付 (已不用，启用需重新检查、修改)
     * @param $money
     * @param int $type
     * creator lkp
     * @return string
     */
    public static function getAlipayInfo($money,$type,$sign,$user_id,$data){
        $aop = new AopClient();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = Yii::$app->params['alipay']['app_id'];
        $aop->rsaPrivateKey = Yii::$app->params['alipay']['merchant_private_key'];
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = Yii::$app->params['alipay']['alipay_public_key'];
//        $out_trade_no 	= date('Ymdhis') . '_'.$type.'_'.$order_id.'_'.$time.'_'.$user_address_id.'_'.$user_id;
        $out_trade_no 	= $sign;
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new AlipayTradeAppPayRequest();
//var_dump($data);exit();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        if($type == 1){
            $trans = Yii::$app->db->beginTransaction();
            try{
                //预升级记录
                $level_result = UserLevelLog::userLevelSave($data['level'],$data['pre_amount'],$type=3,$data['month']);
                if ($level_result['result'] !=1){
                    $trans->rollBack();
                    return ['result'=>0,'msg'=>$level_result['msg']];
                }
                // 预支付订单记录
                $result = UserPayLog::payLogSave($data['pre_amount'],$out_trade_no,$level_result['id']);
                if (! $result){
                    $trans->rollBack();
                    return ['result'=>0,'msg'=>'支付记录失败'];
                }
                $trans->commit();

            }catch (\Exception $e){
                $trans->rollBack();
                return ['result'=>0,'msg'=>'数据处理失败,请稍后再试'];
            }
        }
        $arr = array(
            'body' => $data['title'],
            'subject' => '京东魔盒',
            'out_trade_no' => $out_trade_no,
            'timeout_express' => '30m',
            'total_amount' => $money,
            'product_code' => 'QUICK_MSECURITY_PAY',
        );
        //支付回调地址
        $url = Yii::$app->request->hostInfo.'/site/app-pay-response';

//        log_file($url,'ali_NotifyUrl');
        $request->setNotifyUrl($url);
        $request->setBizContent(json_encode($arr));
//这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
//htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        return $response;//就是orderString 可以直接给客户端请求，无需再做处理。
    }


}