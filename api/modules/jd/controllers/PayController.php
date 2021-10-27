<?php
/**
 * Created by PhpStorm.
 * User: lkp
 * Date: 18-6-9
 * Time: 下午3:11
 */
namespace api\modules\jd\controllers;

use common\models\UserLevel;
use common\models\UserLevelLog;
use common\models\UserLevelSuite;
use common\models\UserPayLog;
use common\models\AlipayModel;
use yii\web\Response;
use Yii;
use api\modules\jd\Controller;
class PayController extends Controller{
    /**
     * 获取支付信息的接口
     * creator lkp
     * @return array
     */
    public function actionGetPayInfo(){
        try{
            Yii::$app->response->format = Response::FORMAT_JSON;
            $level = Yii::$app->request->post('level_id'); //对应会员等级表(user_level) id
            $month = Yii::$app->request->post('month');
            $pre_amount = Yii::$app->request->post('amount');
            $route = Yii::$app->request->post('route');
            if ($level == 5){
                $level = 2;
                $month = 1;
            }
            $amount_result = UserLevelSuite::getMemberFund($level,$month);

            if ($amount_result['result']==0){
                return $amount_result;
            }
            if ($amount_result['price'] != $pre_amount){
                return ['result'=>0,'msg'=>'金额与会员等级不匹配'];
            }

            $app_id = Yii::$app->params['app_id'];
            $app_secret = Yii::$app->params['app_secret'];
            $title = "查排名";
            //为了测试
            if(Yii::$app->user->id == 2){
                $pre_amount = 0.01;
            }
            $amount = intval($pre_amount);//支付总价   单位:分

            $out_trade_no = "bc" . time();//订单号，需要保证唯一性
            //1.生成sign
            $sign = md5($app_id . $title . $amount . $out_trade_no . $app_secret);

//            $trans = Yii::$app->db->beginTransaction();
//            try{
//                //预升级记录
//                $level_result = UserLevelLog::userLevelSave($level,$pre_amount,$type=3,$month);
//                if ($level_result['result'] !=1){
//                    $trans->rollBack();
//                    return ['result'=>0,'msg'=>$level_result['msg']];
//                }
//                // 预支付订单记录
//                $result = UserPayLog::payLogSave($pre_amount,$out_trade_no,$level_result['id']);
//                if (! $result){
//                    $trans->rollBack();
//                    return ['result'=>0,'msg'=>'支付记录失败'];
//                }
//                $trans->commit();
//
//            }catch (\Exception $e){
//                $trans->rollBack();
//                return ['result'=>0,'msg'=>'数据处理失败,请稍后再试'];
//            }

            $option = ['out_trade_no'=>$out_trade_no];
            $data = ['title'=>$title,'amount'=>$amount,'out_trade_no'=>$out_trade_no,'sign'=>$sign,
                'return_url'=>Yii::$app->params['domain'] . $route,
                'debug'=>'false',
                'optional'=>$option,
                'instant_channel'=>'ali',
                'level'=>$level,
                'pre_amount'=>$pre_amount,
                'month'=>$month,
            ];
            $result = AlipayModel::getAlipayInfo($data['pre_amount'],1,$sign,Yii::$app->user->id,$data);

            return ['result'=>1,'data'=>$result];
        }catch(\yii\Base\Exception $e){
//            log_file($e->getMessage(),'pay_error');

            return ['result'=>-1,'data'=>'网络不太好'];
        }
    }
}