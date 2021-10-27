<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_level_log".
 *
 * @property int $id
 * @property int $user_id 用户
 * @property int $admin_id
 * @property int $user_level_suite_id 套餐
 * @property int $lecture_id 讲师id
 * @property int $org_id 讲师id
 * @property int $is_up 是否升级 0新购买 1升级 2赠送
 * @property int $price 套餐价格
 * @property string $pay_amount 实际支付金额
 * @property string $up_detail 升级详情json：'up_days'=7,'up_month'=2
 * @property int $pay_at 支付时间
 * @property int $pay_type 支付方式0佣金 1银行卡
 * @property int $status 状态0待付款1已支付
 * @property int $coupon_id 优惠券id
 * @property string $memo
 * @property int $version
 * @property int $deleted
 * @property int $create_at
 * @property int $update_at
 * @property int $operation_user 操作人
 * @property int $ago_level 升级之前等级
 * @property int $type 0会员 1邀请会员
 * @property int $invite_day 邀请赠送天数
 */
class UserLevelLog extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_level_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_level_suite_id', 'price', 'pay_amount'], 'required'],
            [['user_id','admin_id', 'user_level_suite_id','lecture_id','org_id', 'is_up', 'pay_at','pay_type', 'status','coupon_id', 'version', 'deleted', 'create_at', 'update_at','operation_user','ago_level','type'], 'integer'],
            [['up_detail'], 'string', 'max' => 200],
            [['memo'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户',
            'admin_id' => 'Admin Id',
            'user_level_suite_id' => '套餐',
            'lecture_id' => 'Lecture Id',
            'org_id' => 'Org Id',
            'is_up' => '是否升级0新购买1升级',
            'price' => '套餐价格',
            'pay_amount' => '支付金额',
            'up_detail' => '升级详情json：\'up_days\'=7,\'up_month\'=2',
            'pay_at' => '支付时间',
            'pay_type' => '支付方式',
            'status' => '状态0待付款1已支付',
            'coupon_id' => '优惠券id',
            'memo' => 'Memo',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'operation_user' =>'操作人',
            'invite_day' =>'invite_day',
        ];
    }

    public static function bankRechargeSave()
    {

    }


    public static function userLevelSave($coupon_id, $id,$amount,$type=1,$month,$memo = '',$use_id='',$operation_user='',$tag=0)
    {

        //$type 1 银行卡购买  2佣金购买  3第三方、支付宝购买
        $level_suit = UserLevelSuite::findOne(['deleted'=>0,'user_level_id'=>$id,'num'=>$month]);
        $user_id = empty($use_id) ? Yii::$app->user->id : $use_id;
        $user = User::findOne(['id'=>$user_id]);
        if (empty($level_suit)){
            return ['result'=>0,'msg'=>'等级获取失败'];
        }
        $level_log = new UserLevelLog();
        $level_log->user_id = $user_id;
        $level_log->lecture_id = $user->lecture_id;
        $level_log->org_id = $user->org_id;
        $level_log->admin_id = Yii::$app->user->id;
        $level_log->user_level_suite_id = $level_suit->id;
        $level_log->is_up = 0;
        if ($user->level_id != $level_suit->user_level_id){
            $level_log->is_up = 1;
        }
        $level_log->up_detail = '';
        if ($user->level_id != $level_suit->user_level_id){
            $level_log->is_up = 1;
            $level_log->ago_level = $user->level_id;
            $level_log->up_detail = serialize(['up_days'=>'0','up_month'=>$level_suit->num]);
        }
        $level_log->price = $level_suit->price;
        $level_log->pay_amount = $amount;
        $level_log->pay_at = time();
        $level_log->status = 0;
        $level_log->coupon_id = $coupon_id;
        $level_log->pay_type = 1;
        if ($type==2){
            $level_log->status = 1;
            $level_log->pay_type = 0;
            $level_log->operation_user = $operation_user;
        }else if ($type==3){
            $level_log->pay_type = 2;
            $level_log->admin_id = 0;
        }
        if($tag == 1){
            $level_log->status = 1;
        }
        $level_log->memo = empty($memo) ? '' : $memo;
        if (! $level_log->save()){
            return ['result'=>0,'msg'=>$level_log->getErrors()];
        }
        $msg = '等待审核';
        if ($type ==2){
            $msg = '会员购买成功';
        }
        return ['result'=>1,'msg'=>$msg,'id'=>$level_log->id];
    }

    public static function upgradeDetail($page,$page_size)
    {
        if (empty($page)){
            $page = 1;
        }
        if (empty($page_size)){
            $page_size = 20;
        }
        $data = [];

        $models = UserLevelLog::find()->where(['user_id'=>Yii::$app->user->id,'status'=>1,'deleted'=>0])->andWhere(['!=','pay_type',3]);
        $jquery = clone  $models;
        $total_count = $jquery->count();
        $total_page = ceil($total_count / $page_size);
        $offset = ($page - 1) * $page_size;
        $results = $models->orderBy('id desc')->offset($offset)->limit($page_size)->all();

        $data['result'] = 1;
        $data['total_count'] = $total_count;
        $data['total_page'] = $total_page;
        $data['page'] = $page;
        $data['page_size'] = $page_size;

        if (empty($results)){
            return $data;
        }
        $i = 0;
        foreach ($results as $result){
            $level = UserLevel::findOne(['id'=>$result->levelSuit->user_level_id,'deleted'=>0]);
            $data['data'][$i] = [
                'id' =>$result->id,
                'index'    => $offset + 1 + $i,
                'time'     => date('Y-m-d H:i',$result->create_at),
                'content'  => empty($level) ? '-' : $level->name,
                'month'    => $result->levelSuit->num,
                'type'     => $result->pay_type==0 ? '佣金支付' : '支付宝',
            ];

            $i++;
        }
        return $data;

    }

    public static function balanceDetail($page,$page_size)
    {
        if (empty($page)){
            $page = 1;
        }
        if (empty($page_size)){
            $page_size = 20;
        }
        $data = [];

        $models = UserPointDetail::find()->where(['user_id'=>Yii::$app->user->id,'deleted'=>0,'type'=>'reg','title'=>'邀请']);
        $jquery = clone  $models;
        $total_count = $jquery->count();
        $total_page = ceil($total_count / $page_size);
        $offset = ($page - 1) * $page_size;
        $results = $models->orderBy('id desc')->offset($offset)->limit($page_size)->all();

        $data['result'] = 1;
        $data['total_count'] = $total_count;
        $data['total_page'] = $total_page;
        $data['page'] = $page;
        $data['page_size'] = $page_size;

        if (empty($results)){
            return $data;
        }
        $i = 0;
        foreach ($results as $result){
//            $level = UserLevel::findOne(['id'=>$result->levelSuit->user_level_id,'deleted'=>0]);
            $data['data'][$i] = [
                'id' =>$result->id,
                'index'    => $offset + 1 + $i,
                'time'     => date('Y-m-d H:i',$result->create_at),
//                'content'  => empty($level) ? '-' : $level->name,
//                'month'    => $result->levelSuit->num,
                'type'     => $result->title=='邀请' ? '邀请好友' : '订阅会员',
                'income_point' => $result->title=='邀请' ? $result->point : '0.00',
                'expend_point' => $result->title=='邀请' ? '0.00' : $result->point,
            ];

            $i++;
        }
        return $data;

    }

    public function getLevelSuit()
    {
        return $this->hasOne(UserLevelSuite::className(),['id'=>'user_level_suite_id']);
    }
    public function getUser()
    {
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }
    public function getLecture()
    {
        return $this->hasOne(AdminLecture::className(),['id'=>'lecture_id']);
    }
    public function getLectureFund()
    {
        return $this->hasOne(LectureFund::className(),['lecture_id'=>'lecture_id','level_log_id'=>'id']);
    }
}
