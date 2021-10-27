<?php

/**
 * Created by PhpStorm.
 * User: liuyaping
 * Date: 17/9/6
 * Time: 下午10:01
 */
namespace console\controllers;

use common\component\SmsSh;
use common\models\BaseGoods;
use common\models\CustomService;
use common\models\Mac;
use common\models\ServiceKeywordSearch;
use common\models\StatisData;
use common\models\UserCustomService;
use common\models\UserLevelSuite;
use yii\console\Controller;
use common\models\User;

class LevelClockController extends Controller
{

    //    恢复天数
    public function actionRecover(){
        $query = \Yii::$app->jdrqds->createCommand("SELECT
        id,user_id,user_level_suite_id,pay_at,unix_timestamp(now()) as at,
        (SELECT level_id FROM `user` WHERE id=user_id) as level_id
        FROM user_level_log WHERE
        pay_type =2 AND
        status=1 AND deleted=0 AND pay_at<1559318400 AND pay_at>1551456000
        AND user_level_suite_id  NOT IN (5,6,7,1,2,9,10,11,13,4,3)
        GROUP BY user_id ORDER BY id DESC")->queryAll();
        foreach ($query as $k => $y){
            $time = $y['pay_at'] + (86400 *360);
            $day = (int)round(($time - $y['at'])/86400,0);
            switch($y['user_level_suite_id']){
                case 8;
                    $level_id = 3;
                    break;
                case 12;
                    $level_id = 4;
                    break;
            }

            echo "用户".$y["user_id"]."应该剩余天数：".$day."+++套餐id：".$y["user_level_suite_id"]."\n";
            echo "用户".$y["user_id"]."当前等级：".$y['level_id']."------------------\n";
            $user_save = User::find()->where(['id'=>$y["user_id"]])->one();
//            var_dump($y["user_id"]);exit();
            $user_save->level_fate = $day;
            $user_save->level_id = $level_id;
            $user_save->save();
        }
        exit();
//        var_dump($day);exit();
    }

    //  每日统计数据
    public function  actionStatistics(){
        $time = strtotime(date('Y-m-d', time())) + 2;
        $is_save = StatisData::find()->where(['>','create_at',$time])->one();
        if(empty($is_save)){
            $zuo_time = strtotime(date('Y-m-d', strtotime('-1 day')));
            $zuo_time_end = $zuo_time+86399;
            $newStatisData = new StatisData();
            // 用户总量
            $newStatisData->user_all = User::find()->count();
            // 昨日用户总量
            $newStatisData->past_user_all = User::find()->where(['>','create_at',$zuo_time])->andWhere(['<','create_at',$zuo_time_end])->count();
            // 查询总数
            $newStatisData->inquire_all = ServiceKeywordSearch::find()->where(['type'=>1])->count();
            // 昨日查询总数
            $newStatisData->past_inquire_all = ServiceKeywordSearch::find()->where(['type'=>1])->andWhere(['>','create_at',$zuo_time])->andWhere(['<','create_at',$zuo_time_end])->count();
            // 查询总数pc
            $newStatisData->inquire_all_pc = ServiceKeywordSearch::find()->where(['type'=>1,'client_type'=>1])->count();
            // 昨日查询总数pc
            $newStatisData->past_inquire_all_pc = ServiceKeywordSearch::find()->where(['type'=>1,'client_type'=>1])
                ->andWhere(['>','create_at',$zuo_time])->andWhere(['<','create_at',$zuo_time_end])
                ->count();
            // 查询总数app
            $newStatisData->inquire_all_app = ServiceKeywordSearch::find()->where(['type'=>1,'client_type'=>2])->count();
            // 昨日查询总数app
            $newStatisData->past_inquire_all_app = ServiceKeywordSearch::find()->where(['type'=>1,'client_type'=>2])
                ->andWhere(['>','create_at',$zuo_time])->andWhere(['<','create_at',$zuo_time_end])
                ->count();
            //  登录用户查询
            $newStatisData->login_inquire_all = ServiceKeywordSearch::find()->where(['type'=>1])->andWhere(['>','user_id',0])->count();
            // 昨日登录用户查询
            $newStatisData->past_login_inquire_all = ServiceKeywordSearch::find()->where(['type'=>1])->andWhere(['>','user_id',0])
                ->andWhere(['>','create_at',$zuo_time])->andWhere(['<','create_at',$zuo_time_end])
                ->count();
            //  未登录用户查询
            $newStatisData->no_login_inquire_all = ServiceKeywordSearch::find()->where(['type'=>1])->andWhere(['user_id'=>0])->count();
            // 昨日未登录用户查询
            $newStatisData->past_no_login_inquire_all = ServiceKeywordSearch::find()->where(['user_id'=>0])
                ->andWhere(['>','create_at',$zuo_time])->andWhere(['<','create_at',$zuo_time_end])
                ->count();
            // vip查排名总数
            $newStatisData->vip_cha_all = \Yii::$app->db->createCommand("SELECT COUNT(*) FROM `service_keyword_search` WHERE user_id in (
	SELECT id FROM `user` WHERE level_id in (2,3,4) AND level_fate>0
)")->queryOne()['COUNT(*)'];
            // 昨日vip查排名总数
            $newStatisData->past_vip_cha_all = \Yii::$app->db->createCommand("SELECT COUNT(*) FROM `service_keyword_search` WHERE user_id in (
	SELECT id FROM `user` WHERE level_id in (2,3,4) AND level_fate>0
) AND create_at > $zuo_time AND create_at < $zuo_time_end ")->queryOne()['COUNT(*)'];
            //  查权重总数
            $newStatisData->weight_all = ServiceKeywordSearch::find()->where(['type'=>2])->count();
            //  昨日查权重总数
            $newStatisData->past_weight_all = ServiceKeywordSearch::find()->where(['type'=>2])
                ->andWhere(['>','create_at',$zuo_time])->andWhere(['<','create_at',$zuo_time_end])
                ->count();
            if(!$newStatisData->save()){
                echo "失败:" . $newStatisData->getError();
            }
        }
        echo "完成\n";
    }
    // vip is_update状态定时更新  测试
    public function actionUpdateVip(){
        User::updateAll(['is_update'=>0],User::find()->where(['deleted'=>0,'is_update'=>1])->where);
        echo "更新完成\n";
        return;
    }

    public function  actionDddd(){
        $index = 0;
        do{
            $count = User::find()->where(['deleted'=>0,'is_ask_count'=>0])->andWhere(['!=','shop_id',0])->count();
            if ($count==0){
                echo "更新完成\n";
                return;
            }
            $models = User::find()->where(['deleted'=>0,'is_ask_count'=>0])->andWhere(['!=','shop_id',0])->orderBy('id desc')->limit('10')->all();
            foreach ($models as $model){
                $user = User::find()->where(['id'=> $model->id])->one();
                $invite_level_id_user = $user->getInviteLevelId($user->id);
                $user->invite_level_id = $invite_level_id_user;
                $user->is_ask_count = 1;
                echo "更新前".$user->invite_level_id."等级\n";
                if (!$user->save()){
                    echo "更新".$model->id."用户失败:" . $user->getError();
                    continue;
                }else{
                    echo "更新".$model->id."用户成功:" . $index;
                }
            }
        }while($count>0);
    }
    public function DifferTime($time1= '',$time2 = ''){
        $Date_1 = date("Y-m-d");
        $d1 = strtotime($Date_1);
        $d2 = $time2;
        $day =(int)round(($d2-$d1)/3600/24);
        return $day;
    }
    // 脚本 处理充值vip 改为天数
    public function actionFateSave(){
        $index = 0;
        do{
            $count = User::find()->where(['deleted'=>0,'is_vip_count'=> 0])->andWhere(['>','level_end_at',time()])->count();
            if ($count==0){
                echo "更新完成\n";
                return;
            }

            $models = User::find()->where(['deleted'=>0,'is_vip_count'=> 0])->andWhere(['>','level_end_at',time()])->orderBy('id desc')->limit('10')->all();
            foreach ($models as $model){
                $user = User::find()->where(['id'=> $model->id])->one();
                $day = $this->DifferTime(time(),$model->level_end_at);
                $day = empty($model->level_fate) ? $day : $model->level_fate+$day;
                // 剩余的天数 （天）
                $day = $this->DifferTime(time(),$model->level_end_at);
                $user->level_fate = $day;
                $user->is_vip_count = 1;
                $index = $user->id;

                if (!$user->save()){
                    echo "更新用户失败:" . $user->getError();
                    continue;
                }else{
                    echo "更新用户成功:" . $index;
                }
            }

        }while($count>0);
    }

    // 脚本 处理邀请vip 改为天数
    public function actionAskSave(){
        $index = 0;
        do{
            $count = User::find()->where(['deleted'=>0,'is_ask_count'=> 0])->andWhere(['>','vip_end_time',time()])->count();
            if ($count==0){
                echo "更新完成\n";
                return;
            }

            $models = User::find()->where(['deleted'=>0,'is_ask_count'=> 0])->andWhere(['>','vip_end_time',time()])->orderBy('id desc')->limit('10')->all();
            foreach ($models as $model){
                $user = User::find()->where(['id'=> $model->id])->one();
                // 剩余的天数 （天）
                $day = $this->DifferTime(time(),$model->vip_end_time);
                $day = empty($model->invite_fate) ? $day : $model->invite_fate+$day;

                $user->invite_fate = $day;
                $user->is_ask_count = 1;
                $index = $user->id;

                if (!$user->save()){
                    echo "更新用户失败:" . $user->getError();
                    continue;
                }else{
                    echo "更新用户成功:" . $index;
                }
            }

        }while($count>0);
    }
    // 每天减去一天
    public function actionMinusDay(){
        $index = 0;
        $lockfile = '/tmp/mytest.lock';
        if(file_exists($lockfile)){
            exit;
        }else{
            file_put_contents($lockfile,1,true);
        }
        do{
            $count = User::find()->where(['deleted'=>0,'is_update'=>0])->orderBy('id desc')->count();
            if ($count==0){
                echo "更新完成" .date('Y-m-d H:i',time()) . "\n";
                unlink($lockfile);
                return;
            }
            $models = User::find()->where(['deleted'=>0,'is_update'=>0])->orderBy('id desc')->limit('500')->all();
            foreach ($models as $model){
                $user = User::find()->where(['id'=>$model->id])->one();
                $level_id = $model->level_id;
                $user->is_update = 1;
                if( empty($level_id)){
                    if (!$user->save()){
                        return ['result'=>0,'msg'=>$user->getError()];
                    }
                    continue;
                }
                if($model->level_fate >= 1){
                    $user->level_fate = $user->level_fate-1;
                    if (!$user->save()){
                        return ['result'=>0,'msg'=>$user->getError()];
                    }
                    echo "用户减天数".$model->id."更新完成" .date('Y-m-d H:i',time()) . "\n";
                    continue;
                }else{
                    echo "用户降级——————————".$model->id."更新完成" .date('Y-m-d H:i',time()) . "\n";
                    $user->level_id = 6;
                }

                if (!$user->save()){
                    return ['result'=>0,'msg'=>$user->getError()];
                }
                echo "用户完成------".$model->id."更新完成" .date('Y-m-d H:i',time()) . "\n";
            }
        }while($count>0);
    }
    // 每天减去一天
    public function actionMinusDay1(){
        $index = 0;

        do{
            $count = User::find()->where(['deleted'=>0,'is_update'=>0])->orderBy('id desc')->count();
            if ($count==0){
                echo "更新完成\n";
                return;
            }
            $models = User::find()->where(['deleted'=>0,'is_update'=>0])->orderBy('id desc')->limit('1000')->all();
//            \Yii::$app->db->close();  // 关闭当前的活动DB链接
            foreach ($models as $model){
                $user = User::find()->where(['id'=>$model->id])->one();
//                $invite_level_id = $model->invite_level_id;
//                $level_id = $model->level_id;
                $user->is_update = 1;
//                if(empty($invite_level_id) && empty($level_id)){
//                    if (!$user->save()){
//                        return ['result'=>0,'msg'=>$user->getError()];
//                    }
//                    continue;
//                }
                if($model->level_id == 3 || $model->level_id == 4){
                            if($model->level_fate > 0){
                                $user->level_fate = $user->level_fate-1;
                                if (!$user->save()){
                                    return ['result'=>0,'msg'=>$user->getError()];
                                }
                                echo "用户充值".$model->id."更新完成\n";
                                continue;
                            }
                }
                if (!$user->save()){
                    return ['result'=>0,'msg'=>$user->getError()];
                }
                echo "用户到期".$model->id."更新完成\n";
            }
        }while($count>0);
    }

    public function actionIndex()
    {
//            User::updateAll(['level_id'=>1],User::find()
//                ->where(['=','level_fate',$curTime])
//                ->andWhere(['!=','level_id',6])->where);
//

        $curTime = 0;
        User::updateAll(['level_id'=>3],User::find()->where(['=','level_fate',$curTime])->andWhere(['!=','level_id',6])->where);
        echo "处理成功\n";
    }
    //遍历老用户的数据，确定老用户的邀请人数，计算老用户当前应该得什么会员
    public function actionChangeVip(){
        $user_list = User::find()->where(['deleted'=>0])->asArray()->all();
        for($i=0;$i<count($user_list);$i++){
            $user_id = $user_list[$i]['id'];
            if($user_id == 254){
                $user = User::findOne($user_id);
                $invite_level_id = $user->getInviteLevelId($user_id);
                if($user_list[$i]['invite_level_id'] < $invite_level_id){
                    //修改邀请vip等级
                    $user->invite_level_id = $invite_level_id;
                    $user->vip_start_time = time();
                    $user->vip_end_time = time() + (365 * 86400);
                    if($user_list[$i]['level_id'] == $invite_level_id){
                        //延长当前等级的vip时长
                        $user->level_end_at += (365 * 86400);
                    }elseif ($user_list[$i]['level_id'] == 6 || $user_list[$i]['level_id'] < $invite_level_id || $user_list[$i]['level_id'] == 5){

                        //修改当前用户的vip等级和时长
                        if($user_list[$i]['is_count'] == 1 || $user_list[$i]['level_id'] == 6 || $user_list[$i]['level_id']==5 || $user_list[$i]['level_id'] ==1){
                            //证明之前已经将付费的部分进行折算了，此处直接覆盖即可
                            $user->level_start_at = time();
                            $user->level_end_at = time() + (365 * 86400);
                        }else{
                            //查出当前等级的单价
                            $user_level_suite = UserLevelSuite::find()->where(['user_level_id'=>$user_list[$i]['level_id'],'num'=>1])->one();
                            $current_level_price = $user_level_suite->price;
                            //查出即将升级的vip的单价
                            $higher_user_level_suite = UserLevelSuite::find()->where(['user_level_id'=>$invite_level_id,'num'=>1])->one();
                            $higher_level_price = $higher_user_level_suite->price;

                            //将付费的部分这算成升级高版本的时间相互叠加,单价取一个月的单价
                            $diff_time = $user->level_end_at - time();
                            $money = (ceil($diff_time / 86400) / 30) * $current_level_price; //剩余的钱

                            //用剩余的钱购买高等级的vip的时长
                            $time = ceil($money / $higher_level_price * 30 * 86400);
                            $user->level_start_at = time();
                            $user->level_end_at = time() + (365 * 86400) + $time;
                            $user->is_count = 1;//设置成已经结算过了
                        }
                        $user->level_id = ($invite_level_id) ? $invite_level_id : 1;
                    }
                    $user->save();
                }
            }
        }
    }
    //遍历老用户的数据，确定老用户（体验会员）的邀请人数，计算老用户当前应该得什么会员
    public function actionChangeExperienceVip(){
        $user_list = User::find()->where(['deleted'=>0,'level_id'=>1])
            ->andWhere('create_at < 1527504812')->asArray()->all();
        for($i=0;$i<count($user_list);$i++){
            $user_id = $user_list[$i]['id'];
            $user = User::findOne($user_id);
            $invite_level_id = $user->getInviteLevelId($user_id);
            if($user_list[$i]['level_id'] == 1){
                if($user_list[$i]['invite_level_id'] <= $invite_level_id){
                    //修改邀请vip等级
                    $user->invite_level_id = $invite_level_id;
                    $user->vip_start_time = time();
                    $user->vip_end_time = time() + (365 * 86400);
                    if($user_list[$i]['level_id'] == $invite_level_id){
                        //延长当前等级的vip时长
                        $user->level_end_at += (365 * 86400);
                    }elseif ($user_list[$i]['level_id'] == 6 || $user_list[$i]['level_id'] < $invite_level_id || $user_list[$i]['level_id'] == 5){

                        //修改当前用户的vip等级和时长
                        if($user_list[$i]['is_count'] == 1 || $user_list[$i]['level_id'] == 6 || $user_list[$i]['level_id']==5 || $user_list[$i]['level_id'] ==1){
                            //证明之前已经将付费的部分进行折算了，此处直接覆盖即可
                            $user->level_start_at = time();
                            $user->level_end_at = time() + (365 * 86400);
                        }else{
                            //查出当前等级的单价
                            $user_level_suite = UserLevelSuite::find()->where(['user_level_id'=>$user_list[$i]['level_id'],'num'=>1])->one();
                            $current_level_price = $user_level_suite->price;
                            //查出即将升级的vip的单价
                            $higher_user_level_suite = UserLevelSuite::find()->where(['user_level_id'=>$invite_level_id,'num'=>1])->one();
                            $higher_level_price = $higher_user_level_suite->price;

                            //将付费的部分这算成升级高版本的时间相互叠加,单价取一个月的单价
                            $diff_time = $user->level_end_at - time();
                            $money = (ceil($diff_time / 86400) / 30) * $current_level_price; //剩余的钱

                            //用剩余的钱购买高等级的vip的时长
                            $time = ceil($money / $higher_level_price * 30 * 86400);
                            $user->level_start_at = time();
                            $user->level_end_at = time() + (365 * 86400) + $time;
                            $user->is_count = 1;//设置成已经结算过了
                        }
                        $user->level_id = $invite_level_id;
                    }
                    $user->save();
                }
            }
        }
    }
    public function actionUser()
    {
        $index = 103;
        do{
            echo "执行开始" . $index . "\n";
            $users = User::find()->where(['deleted'=>0])->andWhere(['>','id',$index])->limit(50)->all();
            $count = User::find()->where(['deleted'=>0])->andWhere(['>','id',$index])->count();
            echo "循环数据\n";
            $data = [];
            $i = 0;
            foreach ($users as $user){
                echo "获取关键词" . $i . "\n";
                $service = ServiceKeywordSearch::find()->where(['user_id' => $user->id])->orderBy('id desc')->one();

                echo "获取店铺\n";
                if ($service){
                    $shop = BaseGoods::findOne(['sku' => $service->sku]);
                }

                $result = [
                    'mphone'    => $user->mphone,
                    'shop_name' => empty($shop) ? '' : $shop->shop_name,
                    'keyword'   => empty($service) ? '' : $service->keyword,
                    'sku'       => empty($service) ? '' : $service->sku,
                    'create_at' => time(),
                    'update_at' => time(),
                ];
                array_push($data,$result);
                $index = $user->id;
                $i++;
            }
            $_cloumns = ['mphone','shop_name','keyword','sku','create_at','update_at'];
            echo "存入数据库\n";
            $tran = \Yii::$app->db->beginTransaction();
            try{
                $result = \Yii::$app->db->createCommand()->batchInsert('user_action', $_cloumns, $data)->execute();
                if (!$result) {
                    $tran->rollBack();
                    var_dump($result);
                    return;
                }
            }catch (\Exception $e){
                $tran->rollBack();
                var_dump('错误3' . $e->getMessage());
                return;
            }
            echo "执行结束\n";
            $tran->commit();

        }while($count > 0);
    }

    public function actionMsgSend()
    {
        $index = 3500;
        do{
            $count = User::find()->where(['deleted'=>0])->andWhere(['>','id',$index])->count();

            $users = User::find()->where(['deleted'=>0])->andWhere(['>','id',$index])->limit(10)->all();

            if ($users){
                foreach ($users as $user){
                    $sys = new SmsSh();
                    $msg = '【京东魔盒】“查评价”功能上线，一键查询、下载评价内容，限时免费，快拉上小伙伴一起试试吧！点击链接查看详情 jdmohe.com';
                    $sys->sendmsg($user->mphone,$msg);

                    echo $user->id . "\n";

                    $index = $user->id;
                }
            }

            echo "完成\n";

        }while($count>0);
    }



    //绑定用户和客服之间的关系
    public function actionBindUserAndCs(){
        $user_list = User::find()->select(['id'])->where(['level_id' => 5])->orWhere(['level_id'=>6])->asArray()->all();
        for($i=0;$i<count($user_list);$i++){
            $trans = \Yii::$app->db->beginTransaction();
            //找出已经加好友人数最少的客服
            $info = CustomService::find()->orderBy('total ASC')->one();
            $user_custom_service = new UserCustomService();
            $user_custom_service->user_id = $user_list[$i]['id'];
            $user_custom_service->custom_service_id = $info->id;
            $user_custom_service->status = 0;
            $user_custom_service->save();
            if(!$user_custom_service->save()){
                $trans->rollBack();
            }
            //增加客户的绑定人数
            $custom_service = CustomService::findOne($info->id);
            $custom_service->total = $custom_service->total+1;
            $custom_service->save();
            if(!$custom_service->save()){
                $trans->rollBack();
            }
            $trans->commit();
        }
    }
}