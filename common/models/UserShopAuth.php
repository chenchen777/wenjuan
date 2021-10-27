<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_shop_auth".
 *
 * @property int $id
 * @property int $user_id
 * @property int $up_user_id 上线用户
 * @property string $shop_code 店铺码
 * @property string $auth_url
 * @property int $auth_status 0 等待审核 1已认证 -1审核失败
 * @property string $err_msg 审核失败信息
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class UserShopAuth extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_shop_auth';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'auth_status'], 'required'],
            [['user_id','up_user_id', 'auth_status', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['shop_code'], 'string', 'max' => 32],
            [['auth_url','err_msg'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'up_user_id' => 'Up User Id',
            'shop_code' => 'Shop Code',
            'auth_url' => 'Auth Url',
            'auth_status' => 'Auth Status',
            'err_msg' => 'Err Msg',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }
    public static function makeCode($num=3) {
        $re = '';
        $s = 'abcdefghijklmnopqrstuvwxyz';
        $strlen = strlen($s);
        $re = '';
        for($i=0;$i<$num;$i++) {
            $re .= substr($s,rand(0,$strlen),1);
        }
        return $re;
    }
    public static function authSave($code,$good_url)
    {
        if (empty($auth)){
            $auth = new UserShopAuth();
        }
        // 本人
        $user = User::findOne(Yii::$app->user->id);
        $trans = Yii::$app->db->beginTransaction();
        $auth->up_user_id = $user->up_user_id;
        $auth->user_id = $user->id;
        $auth->shop_code = $code;
        $auth->auth_url = $good_url;
        $auth->auth_status = 1;
        $invite_level_id_user = $user->getInviteLevelId(Yii::$app->user->id);

        if (! $auth->save()){
            return ['result'=>0,'msg'=>$auth->getError()];
        }

        //修改用户等级，变成普通会员，会员期是一年
        if($user->level_id != 2 && $user->level_id != 3 && $user->level_id != 4){
            $user->level_id = 1;
            //在这里对上级用户的vip等级进行结算,计算出该上级用户已经邀请的人数
            $invite_level_id = $user->getInviteLevelId($user->id);
            if($invite_level_id > 1){
//                $user->invite_level_id = $invite_level_id;
            }
            $user->level_start_at = time();
            $user->level_end_at = time() + 365 * 86400;
            if (! $user->save()){
                $trans->rollBack();
                return ['result'=>0,'msg'=>$user->getError()];
            }
        }

        //假如用户的上级没有绑定店铺，直接跳过下一步
        $upUser = User::findOne(['id' => $user->up_user_id]);
//        if (!empty($user->up_user_id) && !empty($user->shop_id) ){
        if (!empty($user->up_user_id) && !empty($upUser->shop_id) ){
            //在这里对上级用户的vip等级进行结算,计算出该上级用户已经邀请的人数
            $invite_level_id = $user->getInviteLevelId($user->up_user_id);

            if($upUser->invite_level_id <= $invite_level_id){
                //  修改绑定店铺状态
                 Yii::$app->db->createCommand("UPDATE `user_shop_auth` SET version=1 WHERE id=$auth->id")->execute();

                //修改邀请vip等级
                $upUser->invite_level_id = $invite_level_id;
                $upUser->vip_start_time = time();
                if(empty($upUser->vip_end_time)){
                    $upUser->vip_end_time = time() + (30 * 86400);
                }else{
                    $upUser->vip_end_time +=  (30 * 86400);
                }
                //延长当前等级的vip时长
                $upUser->invite_fate += 30;
            }

            $upUser->point += 200;
            if (! $upUser->save()){
                return ['result'=>0,'msg'=>$upUser->getError()];
            }
        }
        $trans->commit();
        if($invite_level_id_user > 0){
            $user_s = User::findOne(Yii::$app->user->id);
            if($user_s->invite_level_id == 0){
                $user_s->invite_level_id = $invite_level_id_user;
                $user_s->update_at = time();
                $num = UserShopAuth::find()->where(['up_user_id'=>Yii::$app->user->id,'auth_status'=>1,'version'=>0])->count();
                if($user_s->invite_fate == 0){
                    $user_s->invite_fate = $num*30;
                }
                $user_s->save();
            }else{
                $user_s->invite_level_id = $invite_level_id_user;
                $user_s->update_at = time();
                $num = UserShopAuth::find()->select('id')->where(['up_user_id'=>Yii::$app->user->id,'auth_status'=>1,'version'=>0])->asArray()->all();
                $count_num = count($num);
                if($num){
                    foreach($num as $k => $y){
                        $uid =  $y['id'];
                        Yii::$app->db->createCommand("UPDATE `user_shop_auth` SET version=1 WHERE id=$uid")->execute();
                    }
                }
                if($user_s->invite_fate == 0){
                    $user_s->invite_fate = $count_num*30;
                }else{
                    $user_s->invite_fate = ($count_num*30)+$user_s->invite_fate;
                }
                $user_s->save();
            }
        }
        return ['result'=>1,'msg'=>'认证通过'];
    }

    public static function authSave_s($code,$good_url)
    {
        if (empty($auth)){
            $auth = new UserShopAuth();
        }

        $user = User::findOne(Yii::$app->user->id);
        $trans = Yii::$app->db->beginTransaction();
        $auth->up_user_id = $user->up_user_id;
        $auth->user_id = $user->id;
        $auth->shop_code = $code;
        $auth->auth_url = $good_url;
        $auth->auth_status = 1;

        if (! $auth->save()){
            return ['result'=>0,'msg'=>$auth->getError()];
        }
        //修改用户等级，变成普通会员，会员期是一年
        if($user->level_id != 2 && $user->level_id != 3 && $user->level_id != 4){
            $user->level_id = 1;
            //在这里对上级用户的vip等级进行结算,计算出该上级用户已经邀请的人数
            $invite_level_id = $user->getInviteLevelId($user->id);
            if($invite_level_id > 1){
                $user->level_id = $invite_level_id;
            }
        }
        $user->level_start_at = time();
        $user->level_end_at = time() + 365 * 86400;
        if (! $user->save()){
            $trans->rollBack();
            return ['result'=>0,'msg'=>$user->getError()];
        }
        //假如用户的上级没有绑定店铺，直接跳过下一步
        $upUser = User::findOne(['id' => $user->up_user_id]);
        if (!empty($user->up_user_id) && !empty($user->shop_id) ){
            //在这里对上级用户的vip等级进行结算,计算出该上级用户已经邀请的人数
            $invite_level_id = $user->getInviteLevelId($user->up_user_id);
            if($upUser->invite_level_id < $invite_level_id){
                //修改邀请vip等级
                $upUser->invite_level_id = $invite_level_id;
                $upUser->vip_start_time = time();
                $upUser->vip_end_time = time() + (365 * 86400);
                if($upUser->level_id == $invite_level_id){
                    //延长当前等级的vip时长
                    $upUser->level_end_at += (365 * 86400);
                }elseif ($upUser->level_id == 6 || $upUser->level_id < $invite_level_id || $upUser->level_id == 5){
                    //修改当前用户的vip等级和时长
                    if($upUser->is_count == 1 || $upUser->level_id == 6 || $upUser->level_id == 1 || $upUser->level_id == 5){
                        //证明之前已经将付费的部分进行折算了，此处直接覆盖即可
                        $upUser->level_start_at = time();
                        $upUser->level_end_at = time() + (365 * 86400);
                    }else{
                        //查出当前等级的单价
                        $user_level_suite = UserLevelSuite::find()->where(['user_level_id'=>$upUser->level_id,'num'=>1])->one();
                        $current_level_price = $user_level_suite->price;

                        //查出即将升级的vip的单价
                        $higher_user_level_suite = UserLevelSuite::find()->where(['user_level_id'=>$invite_level_id,'num'=>1])->one();
                        $higher_level_price = $higher_user_level_suite->price;
                        //将付费的部分这算成升级高版本的时间相互叠加,单价取一个月的单价
                        $diff_time = $upUser->level_end_at - time();
                        $money = (ceil($diff_time / 86400) / 30) * $current_level_price; //剩余的钱
                        //用剩余的钱购买高等级的vip的时长
                        $time = ceil($money / $higher_level_price * 30 * 86400);
                        $upUser->level_start_at = time();
                        $upUser->level_end_at = time() + (365 * 86400) + $time;
                        $upUser->is_count = 1;//设置成已经结算过了
                    }
                    $upUser->level_id = $invite_level_id;
                }
            }
            $upUser->point += 200;
            if (! $upUser->save()){
                return ['result'=>0,'msg'=>$upUser->getError()];
            }

        }
        $trans->commit();
        return ['result'=>1,'msg'=>'认证通过'];
    }
    public function getUser()
    {
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }
}
