<?php
/***
 * 个人相关的控制器
 */

namespace api\modules\jd\controllers;

use common\models\CustomService;
use common\models\User;
use common\models\UserCustomService;
use common\models\UserLevel;
use common\models\UserLevelLog;
use common\models\UserLevelSuite;
use common\models\UserPayLog;
use common\models\UserShopAuth;
use Yii;
use api\modules\jd\Controller;
use yii\db\Exception;
use yii\web\Response;
class PersonalController extends Controller{
    /**
     * @return array
     * 用户会员续费、升级资金计算
     */
    public function actionMemFund(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $level = Yii::$app->request->post('level_id'); //对应会员等级表(user_level) id
        $month = Yii::$app->request->post('month');
        $result = UserLevelSuite::getMemberFund($level,$month);
        return $result;
    }

    /**
     * @return array
     * 可以购买的套餐
     */
    public function actionUserInfo(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest){
            return ['result'=>0,'msg'=>'用户未登录'];
        }
        $info = [];
        //查询用户最后一次充值信息
        $mem_month = User::getUserMem();
        $user = User::findOne(['id'=>Yii::$app->user->id,'deleted'=>0]);

//        $info['mem_month'] = $mem_month;
        $info['mem_month'] = 1;
        $info['level_id'] = $user->level_id;

        $user = User::findOne(['id'=>Yii::$app->user->id,'deleted'=>0]);
        $cur_level = empty($user->level_id) ? 0 : $user->level_id;
        if(!$user->level_fate){
            $cur_level = 1;
        }
        $user_levels = UserLevel::find()->where(['deleted'=>0])->andWhere(['<>','id',5]);
        if ($cur_level == 5 || $cur_level==1 || $cur_level==6 || $cur_level==7){
            $user_levels = $user_levels->andWhere(['>','id',1])->andWhere(['<=','id',4])->all();
        }else if($cur_level > 1 && $cur_level < 5){
            $user_levels = $user_levels->andWhere(['or',['>','id',$cur_level],['id'=>$cur_level]])->andWhere(['<=','id',4])->all();
        }

        //用户可升级会员
        $info['level_upgrade'] = [];  //可升级会员
        if (! empty($user_levels)){
            foreach ($user_levels as $level){
                $level_upgrade = [];
                $result = UserLevelSuite::getMemberFund($level->id,1);
                if($result['price'] <0){
                    continue;
                }
                $level_upgrade['name'] = $level->name;
                $level_upgrade['id']  = $level->id;
//                $level_upgrade['subhead'] = $level->subhead;
                array_push($info['level_upgrade'],$level_upgrade);
            }
        }
        $info['result'] = 1;
        return $info;
    }

    /**
     * @return array
     * 支付宝升级购买会员 (废弃 启用需重新检查、修改)
     */
    public function actionAliPay()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $level = Yii::$app->request->post('level_id'); //对应会员等级表(user_level) id
        $month = Yii::$app->request->post('month');
        $pre_amount = Yii::$app->request->post('amount');
        $route = Yii::$app->request->post('route','');
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
        //$pre_amount = 0.01;
        $amount = intval($pre_amount * 100);//支付总价   单位:分




        $out_trade_no = "bc" . time();//订单号，需要保证唯一性
        //1.生成sign
        $sign = md5($app_id . $title . $amount . $out_trade_no . $app_secret);

        $trans = Yii::$app->db->beginTransaction();

        try{
            //预升级记录
            $level_result = UserLevelLog::userLevelSave($level,$pre_amount,$type=3,$month);

            if ($level_result['result'] !=1){
                $trans->rollBack();
                return ['result'=>0,'msg'=>$level_result['msg']];
            }
            // 预支付订单记录
            $result = UserPayLog::payLogSave($pre_amount,$out_trade_no,$level_result['id']);

            if (! $result){
                $trans->rollBack();
                return ['result'=>0,'msg'=>'支付记录失败'];
            }
            $trans->commit();

        }catch (\Exception $e){
            $trans->rollBack();
            return ['result'=>0,'msg'=>'数据处理失败,请稍后再试'];
        }

        $option = ['out_trade_no'=>$out_trade_no];
        $data = ['title'=>$title,'amount'=>$amount,'out_trade_no'=>$out_trade_no,'sign'=>$sign,
            'return_url'=>Yii::$app->params['domain'] . $route,
            'debug'=>'false',
            'optional'=>$option,
            'instant_channel'=>'ali',
        ];
        return ['result'=>1,'data'=>$data];
    }

    //  个人中心 信息
    public function actionGetUserInfo(){
//        sleep(1);
        Yii::$app->response->format = Response::FORMAT_JSON;
        $writeFile = fopen(Yii::getAlias('@api') .  '/payInfo1.txt','a');
        // 打印回调信息
        fwrite($writeFile,json_encode(2) . "\n");
//            sleep(2);
//            Yii::getLogger()->log("your site has been hacked", 111,$category = 'application');
//        if (Yii::$app->user->isGuest){
//            return ['result'=>0,'msg'=>'用户未登录'];
//        }
        $user_info = User::find()
            ->select(['mphone','level_id','level_end_at','shop_name','share_code','vip_end_time','invite_level_id','create_at','level_fate','invite_fate'])
            ->where(['id'=>Yii::$app->user->id])->asArray()->one();

        $user_save = new User();
        $user_save->last_login_at = time();
        $user_save->id = Yii::$app->user->id;
        $user_save->save();

        if($user_info['level_id'] == 5 || $user_info['level_id'] == 6 ){
            $data = UserCustomService::find()->where(['user_id'=>Yii::$app->user->id])->one();
            $custom_service_info = CustomService::findOne($data->custom_service_id);
            $user_info['is_show_alert'] = ($data->status == 1) ? 0 : 1;
            $user_info['custom_service_url'] = ($data->status == 1) ?  '' : $custom_service_info->wx_img;
        }else{
            $user_info['is_show_alert'] = 0;
            $user_info['custom_service_url'] = '';
        }

        $user_auth_info = UserShopAuth::find()->select(['auth_status'])->where(['user_id'=>Yii::$app->user->id])->orderBy('create_at DESC')->one();
        ($user_auth_info) ? $user_info['auth_status'] = (string)$user_auth_info->auth_status : $user_info['auth_status'] = 2;
        if($is_vip = User::priorityVip(Yii::$app->user->id)){
            if($is_vip['type'] == 1){
                $user_info['level_end_at'] = $user_info['invite_fate'];
                $user_info['vip_type'] = $is_vip['type'];
                $user_info['level_id'] = $is_vip['level_id'];
                $user_info['vip_surplus'] = $is_vip['level_fate'];
            }else{
                $user_info['level_end_at'] = $user_info['invite_fate'];
                $user_info['vip_type'] = $is_vip['type'];
                $user_info['level_id'] = $is_vip['level_id'];
                $user_info['vip_surplus'] = $user_info['level_fate'];
            }
        }
        //查询用户对应的等级

        $user_level = UserLevel::findOne($user_info['level_id']);
        $user_info['level_name'] = $user_level->name;


        //当前邀请注册的人数
        $user_info['invite_total_num'] = User::find()->where(['up_user_id'=>Yii::$app->user->id])->count();

        //当前已经邀请的人数（已经注册）
        $user_info['invite_bind_num'] = UserShopAuth::find()->where(['up_user_id'=>Yii::$app->user->id,'auth_status'=>1])->count();

        switch ($user_info['level_id']){
            case 1:
                $next_level_name = 'VIP1';
                $num = 1;
                break;
            case 5:
                $next_level_name = 'VIP1';
                $num = 1;
                break;
            case 6:
                $next_level_name = 'VIP1';
                $num = 1;
                break;
            case 7:
                $next_level_name = 'VIP1';
                $num = 1;
                break;
            case 2:
                $next_level_name = 'VIP2';
                $num = 3;
                break;
            case 3:
                $next_level_name = 'VIP3';
                $num = 10;
                break;
            case 4:
                $next_level_name = '';
                $num = '0';
                break;
        }
        $user_info['next_level_name'] = $next_level_name ? $next_level_name : '';

        $user_info['num'] = $num ? (string)$num - $user_info['invite_bind_num']: '0';

        $user_info['share_url'] = Yii::$app->params['share_url'] . $user_info['share_code'];

        return ['result'=>1,'data'=>$user_info];

    }
}