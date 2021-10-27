<?php

namespace common\models;

use frontend\models\User;
use Yii;

/**
 * This is the model class for table "user_level_suite".
 *
 * @property int $id
 * @property int $user_level_id 用户等级
 * @property string $name 套餐名称
 * @property int $num 数量
 * @property string $num_unit 单位
 * @property int $price 价格
 * @property int $promotion_price 促销价格
 * @property string $subhead 副标题
 * @property string $memo 备注说明
 * @property int $version
 * @property int $deleted
 * @property int $create_at
 * @property int $update_at
 */
class UserLevelSuite extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_level_suite';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_level_id', 'name', 'num', 'num_unit', 'price', 'promotion_price'], 'required'],
            [['user_level_id', 'num', 'version', 'deleted', 'create_at', 'update_at'], 'integer'],
            [['num_unit'], 'string'],
            [['name'], 'string', 'max' => 50],
            [['subhead'], 'string', 'max' => 150],
            [['memo'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_level_id' => 'User Level ID',
            'name' => '用户等级',
            'num' => '会员时长',
            'num_unit' => 'Num Unit',
            'price' => '原价',
            'promotion_price' => '折扣价',
            'subhead' => 'Subhead',
            'memo' => 'Memo',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    public static function getLevelInfo()
    {
        $user = User::findOne(['id'=>Yii::$app->user->id,'deleted'=>0]);
        if (empty($user)){
            return ['result'=>0,'msg'=>'获取用户信息失败'];
        }
        $levels = UserLevel::find()->where(['deleted'=>0])->all();
        if (empty($levels)){
            return ['result'=>0,'msg'=>'获取会员等级信息失败'];
        }

        $data = [];
        $data['account'] = $user->mphone;
        $i = 0;
        foreach ($levels as $level){
            $level_list = [];
            $level_list['level'] = $level->name;
            $level_list['subhead'] = $level->subhead;
            $level_suites = UserLevelSuite::find()->where(['user_level_id'=>$level->id,'deleted'=>0])->all();
            if (empty($level_suites)){
                continue;
            }
            $j = 0;
            foreach ($level_suites as $suite){
                $level_list['detail'][$j] = ['month'=>$suite->num,'price'=>$suite->price];
                $j++;
            }
            $data['level_suit'][$i] = $level_list;
            $i++;
        }
        return $data;
    }

    /**
     * @param $level_id
     * @param $month
     * @param $coupon_id
     * @return array
     */
    public static function getMemberFund($level_id, $month, $coupon_id)
    {
//        if (Yii::$app->user->isGuest){
//            return ['result'=>0,'msg'=>'用户未登录'];
//        }
        $userInfo = User::findOne(['id' => Yii::$app->user->id, 'deleted' => 0]);
        if ($level_id == 5){   //体验会员
            $level_id = 2;
            $month = 1;
        }
        // 套餐信息
        $levelSuite = UserLevelSuite::findOne(['deleted' => 0, 'user_level_id' => $level_id, 'num' => $month]);
        if (empty($levelSuite)){
            return ['result' => 0, 'msg' => '所选套餐已不存在'];
        }
        /* @var $levelSuite UserLevelSuite*/

        // 计算优惠券是否满足使用条件
        $couponAmount = 0;
        if ($coupon_id) {
            $coupon = Coupons::findOne(['deleted' => 0, 'id' => $coupon_id, 'lecture_id' => $userInfo->lecture_id]);
            if ($coupon->expired_time < time()) {
                return ['result' => 0, 'msg' => '此优惠券已过期'];
            }
            $userCoupon = UserCoupon::findOne(['deleted' => 0, 'coupon_id' => $coupon_id, 'user_id' => $userInfo->id]);
            if ($userCoupon) {
                return ['result' => 0, 'msg' => '此优惠券已使用'];
            }
            if ($coupon->limit_amount > $levelSuite->price) {
                return ['result' => 0, 'msg' => '当前所选优惠券不满足使用条件'];
            }
            $couponAmount = $coupon->amount;
        }

        if ($level_id == 1 ||
            $userInfo->level_id == $level_id ||
            $userInfo->level_id == 1 ||
            $userInfo->level_id == 6 ||
            $userInfo->level_id == 5){           // 续费或首次购买

            // 使用优惠券后的金额
            $finalPrice = $levelSuite->price - $couponAmount;
            return [
                'result' => 1,
                'balance' => 0,
                'is_dispark' => 0,
                'id' => $levelSuite->user_level_id,
                'price' => $finalPrice
            ];
        }
        // 升级:标准版升至尊版

        // 会员等级剩余天数，每天折合的单价
        $dayPrice = 1.9;

        $left_time = $userInfo->level_fate * 86400;
        $yearTime = 86400 * 30 * 12;
        // 超过一年，就按照一年来计算
        if($left_time > $yearTime){
            $left_time = $yearTime;
        }

        // 未使用天数
        $day_count = floor($left_time / 86400);
        // 剩余资金
        $left_amount = floor($dayPrice * $day_count);

        // 需再充值资金
        $finalPrice = $levelSuite->price - $left_amount - $couponAmount;
        $disparkNum = 0;
        if($finalPrice < 0){
            $levelUpgrade = self::actionUserInfo();
            foreach($levelUpgrade as $y){
                if($y['id'] < $levelSuite->user_level_id){
                    $disparkNum++;
                }
            }
        }

        return [
            'result' => 1,
            'balance' => 0,
            'is_dispark' => $disparkNum,
            'id' => $levelSuite->user_level_id,
            'coupon_amount' => $couponAmount,
            'price' => $finalPrice
        ];
    }


    public static function getMemberFunds($level_id,$month)
    {
        if (Yii::$app->user->isGuest){
            return ['result'=>0,'msg'=>'用户未登录'];
        }
        $user = User::findOne(['id'=>Yii::$app->user->id,'deleted'=>0]);
        if ($level_id == 5){   //体验会员
            $level_id = 2;
            $month = 1;
        }
        $user_suit = UserLevelSuite::find()->where(['deleted'=>0])->andWhere(['user_level_id'=>$level_id,'num'=>$month])->one();
        if ($level_id==1 or $user->level_id == 1 or $user->level_id == 6 or $user->level_id == $level_id or $user->level_id==5 || $user->is_count == 1){  //续费或首次购买
            if (empty($user_suit)){
                return ['result'=>0,'msg'=>'所选会员等级错误'];
            }
            return ['result'=>1,'price'=>$user_suit->price,'id'=>$user_suit->user_level_id];
        }


        //升级:计算已使用、未使用资金
        $level_log = UserLevelLog::find()->where(['user_id'=>Yii::$app->user->id,'deleted'=>0,'status'=>1])->orderBy('id desc')->one();
        $cur_suit = UserLevelSuite::findOne(['id'=>$level_log->user_level_suite_id,'deleted'=>0]);
        $cur_time = time();
        //每天平均资金(向下取整)
        $day_amount = round($cur_suit->price / ($cur_suit->num * 30),2);

//        $left_time = $user->level_end_at - $cur_time;
        $left_time = (time() + ($user->level_fate * 86400)) - $cur_time;
        if($left_time > 31104000){
            //超过一年，就按照一年来计算
            $left_time = 31104000;
        }

        //未使用天数
        $day_count = floor($left_time / (3600 * 24));
        //剩余资金
        $left_amount = floor($day_amount * $day_count);

        //需再充值资金
        $more_amount = $user_suit->price - $left_amount;

        return ['result'=>1,'price'=>$more_amount,'id'=>$user_suit->user_level_id];

    }
    public function getLevel()
    {
        return $this->hasOne(UserLevel::className(),['id'=>'user_level_id']);
    }
    /**
     * @return array
     * 可以购买的套餐
     */
    public static function actionUserInfo(){
//        Yii::$app->response->format = Response::FORMAT_JSON;
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
                $result = UserLevelSuite::getMemberFunds($level->id,1);
                if($result['price'] <0){
                    continue;
                }
                $level_upgrade['name'] = $level->name;
                $level_upgrade['id']  = $level->id;
//                $level_upgrade['subhead'] = $level->subhead;
                array_push($info['level_upgrade'],$level_upgrade);
            }
        }

        return $info['level_upgrade'];
    }
}
