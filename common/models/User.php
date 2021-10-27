<?php

namespace common\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property int $up_user_id 上线用户
 * @property int $distributor_id 渠道id
 * @property int $type 0普通用户1渠道
 * @property int $level_id 用户等级
 * @property int $lecture_id 讲师id
 * @property int $lecture_level 讲师邀请层级  1直接邀请 >1间接邀请
 * @property int $org_id  机构id
 * @property int $admin_id
 * @property string $mphone 手机
 * @property string $nickname 昵称
 * @property string $password 密码
 * @property string $auth_key 长久登陆
 * @property string $balance 余额
 * @property string $balance_frozen 冻结金额
 * @property int $point 点数
 * @property int $point_frozen 冻结点数
 * @property int $level_start_at 等级开始时间
 * @property int $level_end_at 等级结束时间
 * @property int $shop_id 店铺id
 * @property string $shop_name 店铺名称
 * @property string $share_code 分享编号
 * @property int $is_own
 * @property string $point_code 积分码
 * @property int $point_status 积分码状态
 * @property string $last_login_ip
 * @property int $last_login_at
 * @property int $monitor_is_show
 * @property int $random_id
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 * @property int $is_update_ranking 是否手动更新 1不更新 0更新
 * @property int $update_ranking_is 是否已完成更新 0没有完成更新 1完成
 * @property int $level_fate VIP剩余天数
 */
class User extends Base  implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['up_user_id','distributor_id','invite_fate','level_fate','lecture_id','lecture_level','org_id', 'type', 'level_id','point_status', 'point', 'point_frozen', 'level_start_at', 'level_end_at', 'last_login_at','monitor_is_show', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['level_id', 'mphone', 'password', 'share_code','point_code'], 'required'],
            [['balance', 'balance_frozen'], 'number'],
            [['mphone'], 'string', 'max' => 11],
            [['nickname'], 'string', 'max' => 30],
            [['password','pay_password', 'share_code'], 'string', 'max' => 32],
            [['auth_key'], 'string', 'max' => 64],
            [['last_login_ip'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'up_user_id' => '上线',
            'distributor_id' => '渠道id',
            'type' => '渠道',
            'level_id' => '会员等级',
            'lecture_id' => 'Lecture Id',
            'org_id' => 'Org Id',
            'lecture_level' => 'Lecture Level',
            'mphone' => '用户名',
            'nickname' => 'Nickname',
            'password' => '登录密码',
            'pay_password' => '支付密码',
            'auth_key' => 'Auth Key',
            'balance' => '佣金',
            'balance_frozen' => 'Balance Frozen',
            'point' => '积分余额',
            'point_frozen' => 'Point Frozen',
            'level_start_at' => 'Level Start At',
            'level_end_at' => '会员到期时间',
            'share_code' => 'Share Code',
            'point_code' => 'Point Code',
            'point_status' => 'Point Status',
            'last_login_ip' => 'Last Login Ip',
            'last_login_at' => 'Last Login At',
            'deleted' => 'Deleted',
            'invite_level_id' =>'invite_level_id',
            'vip_start_time'  => 'vip_start_time',
            'vip_end_time'   => 'vip_end_time',
            'shop_id'   => 'shop_id',
            'is_friend' => '是否已经加好友了',
            'is_own'    => 'is_own',
            'shop_name'  => 'shop_name',
            'version' => 'Version',
            'is_count' => 'is_count',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'level_fate' => 'level_fate',
            'invite_fate' => 'invite_fate',
            'random_id' => 'Random Id',
            'jd_token' => 'jd_token',
            'client_app' => 'client_app',
            'client_name' => 'client_name',
            'access_token' => 'access_token',
            'app_login_at' => 'app_login_at',
            'province' => 'province',
        ];
    }

    /**
     * 根据给到的ID查询身份。
     *
     * @param string|integer $id 被查询的ID
     * @return IdentityInterface|null 通过ID匹配到的身份对象
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }
    /**
     * 根据 token 查询身份。
     *
     * @param string $token 被查询的 token
     * @return IdentityInterface|null 通过 token 得到的身份对象
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string 当前用户ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string 当前用户的（cookie）认证密钥
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @param string $authKey
     * @return boolean if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function getLecture()
    {
        return $this->hasOne(AdminLecture::className(),['id'=>'lecture_id']);
    }

    /**
     * 用户信息
     */
    public static function getUser()
    {

        $data = [];
        $level_start_at = '';
        $level_end_at = '';
        $data['result'] = false;
//        $data['info']['level_start_at'] = $level_start_at;
//        $data['info']['level_end_at'] = $level_end_at;
        $data['qq'] = Yii::$app->params['qq'];
        $data['wechat'] = Yii::$app->params['wechat'];
        $data['qq_url'] = "http://wpa.qq.com/msgrd?v=3&uin=" . Yii::$app->params['qq'] . '&site=qq&menu=yes';
        //todo... 后期修改为微信链接
        $data['wechat_url'] = Yii::$app->params['wechat_code'];

        if (Yii::$app->user->isGuest){
            return $data;
        }

        $data['result'] = true;
        $user = User::findOne(['id'=>Yii::$app->user->id,'deleted'=>0]);
        $level_start_at = empty($user->level_start_at) ? '' : date('Y-m-d H:i:s',$user->level_start_at);
        $level_end_at = empty($user->level_end_at) ? '' : date('Y-m-d H:i:s',$user->level_end_at);

        $levelService = UserLevelService::findOne(['user_level_id'=>$user->level_id,'service_id'=>2,'deleted'=>0]);
        //每日权重查询次数
        $curTime = strtotime(date('Y-m-d',time()));
        $count = ServiceKeywordSearch::find()->where(['user_id'=>$user->id,'type'=>2,'state'=>3,'deleted'=>0])
            ->andWhere(['>','create_at',$curTime])
            ->andWhere(['not exists',UserPointDetail::find()->where('relate_id = ' .
                ServiceKeywordSearch::tableName() . '.id')
                ->andWhere(['>','create_at',$curTime])])
            ->count();
        $left_limit = 0;
        if ($levelService->day_limit != 0){
            $left_limit = intval($levelService->day_limit - $count);
        }

        //查询用户最后一次充值信息
        $mem_month = 0;
        $level_log = UserLevelLog::find()->where(['user_id'=>Yii::$app->user->id,'status'=>1,'deleted'=>0])->orderBy('id desc')->one();
        if (! empty($level_log)){
            $level_suit = UserLevelSuite::findOne(['id'=>$level_log->user_level_suite_id,'deleted'=>0]);
            if (! empty($level_suit)){
                $mem_month = $level_suit->num;
            }
        }
        if ($user->level_id == 1 or $user->level_id == 5){
            $mem_month = 0;
        }

        //查询下线绑定店铺用户数
        $shopAuth_count = UserShopAuth::find()->where(['up_user_id'=>Yii::$app->user->id,'auth_status'=>1,'deleted'=>0])->count();

        //下线注册数量
        $cpsUser_count = User::find()->where(['up_user_id'=>Yii::$app->user->id,'deleted'=>0])->count();

        //查询获取总积分
        $point_sum = UserPointDetail::find()->where(['user_id'=>$user->id,'deleted'=>0])
                                            ->andWhere(['type'=>['reg','focus_wx','focus_open_wx','share']])->sum('point');
        $point_sum = empty($point_sum) ? 0 : $point_sum;
        //用户当前等级信息

        /**
         *
         *
        $info = [];
        $info['shopAuth_count'] = $shopAuth_count;
        $info['cpsUser_count'] = $cpsUser_count;
        $info['total_point'] = $point_sum;
        $info['point_paid'] = $user->point_status == 1 ? 1 : 0;
        $info['left_limit'] = $left_limit;
        $info['account'] = $user->mphone;
        $info['point'] = $user->point;
        $info['balance'] = $user->balance - $user->balance_frozen;
        $info['level_start_at'] = $level_start_at;
        $info['level_end_at'] = $level_end_at;
        $info['mem_month'] = $mem_month;
        $info['level'] = empty($user->level) ? '--' : $user->level->name;
        $info['share_url'] = Yii::$app->params['share_url'] . $user->share_code;
        $info['wechat_code'] = Yii::$app->params['wechat_code'];
        $info['pay_password_set'] = 0;
        if ( !empty($user->pay_password)){
        $info['pay_password_set'] = 1;
        }
         */

        $cur_level = empty($user->level) ? 0 : $user->level->id;
        $user_levels = UserLevel::find()->where(['deleted'=>0])->andWhere(['<>','id',5]);
        if ($cur_level == 5){
            $user_levels = $user_levels->andWhere(['>','id',1])->all();
        }else if ($cur_level <= 1){
            $user_levels = $user_levels->andWhere(['>','id',$cur_level])->all();
        }else{
            $user_levels = $user_levels->andWhere(['or',['>','id',$cur_level],['id'=>$cur_level]])->all();
        }

//        //用户可升级会员
//        $info['level_upgrade'] = [];  //可升级会员
//        if (! empty($user_levels)){
//            foreach ($user_levels as $level){
//                $level_upgrade = [];
//                $level_upgrade['name'] = $level->name;
//                $level_upgrade['id']  = $level->id;
//                $level_upgrade['subhead'] = $level->subhead;
//                array_push($info['level_upgrade'],$level_upgrade);
//            }
//        }

        //$data['info'] = (object)$info;
        return $data;

    }


    /*
     * 用户升级信息保存
     */
    public static function userSave($level_id,$left_amount,$month,$amount,$id='',$memo='',$operation_user='',$num_unit)
    {
        $id = empty($id) ? Yii::$app->user->id : $id;
        $user = User::findOne(['id'=>$id]);
        if ($user->level_id != $level_id){
            $user->level_start_at = 0;
        }

        $user->is_count = 0;
        $user->balance = $left_amount;
        if ($user->level_start_at == 0 || $user->level_end_at < time()){

            $user->level_start_at = time();
//            $user->level_end_at = $level_id==5?7 * 3600 * 24:time() + $month * 30 * 3600 * 24;
            //  购买时间 天数
            if($num_unit == 'd'){
                $tian = $month;
            }else{
                $tian = $level_id==5? 7 : $month*30;
            }

            if ($user->level_id != $level_id){
                if ($level_id==3){
////                $user->level_end_at += 7 * 3600 * 24;
                    if($num_unit == 'd'){
                        $user->level_fate = $month;
                    }else{
                        $user->level_fate = $level_id==5? 7 : $month*30;
                    }
//                    $user->level_fate += 7;
                }else{
                    $user->level_fate = $month*30;
//                $user->level_end_at += $month * 30 * 3600 * 24;
                }
            }else{
                $user->level_fate = $tian+$user->level_fate;
            }
            $user->level_id = $level_id;
        }else{
            $user->level_id = $level_id;
//            if ($level_id==3){
//                $user->level_end_at += 7 * 3600 * 24;
//                $user->level_fate += 7;
//            }else{
                $user->level_fate += $month*30;
//                $user->level_end_at += $month * 30 * 3600 * 24;
//            }
        }
        if($memo){
            $user->memo = $memo;
            $user->operation_user = $operation_user;
        }

        if (! $user->save()){
            return ['result'=>0,'msg'=>$user->getError()];
        }

        return ['result'=>1];



    }

    /**
     * @param $amount
     * @param string $id
     * @return array
     * 上线、渠道用户提成资金计算
     */
    public static function cpsSave($amount,$id='',$level_id)
    {

        $trans = Yii::$app->db->beginTransaction();
        try{

            $id = empty($id) ? Yii::$app->user->id : $id;
            $user = User::findOne(['id'=>$id]);
            //上级用户提成
            $up_User = User::findOne(['id'=>$user->up_user_id,'deleted'=>0]);
            $dis_user = User::findOne(['id'=>$user->distributor_id,'deleted'=>0]);

            if (! empty($up_User)){
                if ($up_User->type==1){  //上级用户即为渠道
                    $deduct_amount = floor($amount * Yii::$app->params['dis_percent']);
                    $up_User->balance += $deduct_amount;
                    $fund_result = self::upFundSave($deduct_amount,$up_User->id,$level_id);
                    if ($fund_result['result'] != 1){
                        return ['result'=>0,'msg'=>$fund_result['msg']];
                    }
                }else{  //上级用户非渠道
                    $deduct_amount = floor($amount * Yii::$app->params['up_percent']);
                    $up_User->balance += $deduct_amount;
                    $fund_result = self::upFundSave($deduct_amount,$up_User->id,$level_id);
                    if ($fund_result['result'] != 1){
                        return ['result'=>0,'msg'=>$fund_result['msg']];
                    }

                    if (! empty($dis_user)){
                        $dis_amount = floor($amount * Yii::$app->params['ind_percent']);
                        $dis_user->balance += $dis_amount;
                        $fund_result = self::upFundSave($dis_amount,$dis_user->id,$level_id);
                        if ($fund_result['result'] != 1){
                            return ['result'=>0,'msg'=>$fund_result['msg']];
                        }
//                        if (! empty($dis_user)){
                            if (! $dis_user->save()){
                                $trans->rollBack();
                                return ['result'=>0,'msg'=>$dis_user->getError()];
                            }
//                        }
                    }
                }
            }

            if (! empty($up_User)){
                if (! $up_User->save()){
                    $trans->rollBack();
                    return ['result'=>0,'msg'=>$up_User->getError()];
                }
            }




            $trans->commit();
            return ['result'=>1];

        }catch (\Exception $e){
            $trans->rollBack();
            return ['result'=>0,'msg'=>$e->getMessage() . 'aaaaa'];
        }

    }

    public static function upFundSave($amount,$id,$level_id)
    {
        $fund = new UserFundDetail();
        $fund->user_id = $id;

        try{
            $user = User::findOne(['id'=>$id,'deleted'=>0]);
            if (empty($user)){
                return ['result'=>0,'msg'=>'获取用户信息失败'];
            }
            $fund->title = '佣金提成';
            $fund->fund_type = 'recharge';
            $fund->relate_mode = 'user_level_log';
            $fund->relate_id = $level_id;
            $fund->amount = $amount;
            $fund->balance = $user->balance += $amount;
            if (! $fund->save()){
                return ['result'=>0,'msg'=>$fund->getError()];
            }
            return ['result'=>1];
        }catch (\Exception $e){
            return ['result'=>0,'msg'=>$e->getMessage()];
        }

    }
    /**
     * 优先使用
     */
    public static function priorityVip($uid){
        $user = User::find()->where(['id'=>$uid])->one();
        $invite_level_id = $user->invite_level_id;
        $level_id = $user->level_id;
        $user->is_update = 1;
        if(empty($invite_level_id) && empty($level_id)){
//            if (!$user->save()){
//                return ['result'=>0,'msg'=>$user->getError()];
//            }
        }

        if($user->level_id == 2 || $user->level_id == 3 || $user->level_id == 4){
            // 邀请会员大
            if($invite_level_id >= $level_id){
                if($user->invite_fate > 0){
                    $data['type'] = 2;
                    $data['level_id'] = $user->invite_level_id;
                    $data['level_fate'] = $user->invite_fate;
                    $data['vip_surplus'] = $user->level_fate;
                    return $data;
                }else{
                    if($user->level_fate > 0){
                        $data['type'] = 1;
                        $data['level_id'] = $user->level_id;
                        $data['level_fate'] = $user->level_fate;
                        return $data;
                    }
                }
            }else{
                if($user->level_fate > 0){
                    $data['type'] = 1;
                    $data['level_id'] = $user->level_id;
                    $data['level_fate'] = $user->level_fate;
                    return $data;
                }else{
                    if($user->invite_fate > 0){
                        $data['type'] = 2;
                        $data['level_id'] = $user->invite_level_id;
                        $data['level_fate'] = $user->invite_fate;
                        $data['vip_surplus'] = $user->level_fate;
                        return $data;
                    }
                }

            }

        }else{
            // 不是会员
            if($user->invite_fate > 0 && $user->invite_level_id != 0){
                $data['type'] = 2;
                $data['level_id'] = $user->invite_level_id;
                $data['level_fate'] = $user->invite_fate;
                return $data;
            }
            if($user->level_fate > 0){
                $data['type'] = 1;
                $data['level_id'] = $user->level_id;
                $data['level_fate'] = $user->level_fate;
                $data['vip_surplus'] = $user->level_fate;
                return $data;
            }
        }
        $data['type'] = 1;
        $data['level_id'] = $user->level_id;
        $data['level_fate'] = $user->level_fate;
        $data['vip_surplus'] = $user->level_fate;
        return $data;
    }
    /**
     * 设置支付密码
     */
    public static function payPasswordSet($pay_set,$password_login,$password_pay,$passwordPay_confirm)
    {
        $user = User::findOne(['id'=>Yii::$app->user->id]);
        $pass = md5($password_login);
        if ($pay_set != 1){
            if ($user->password != $pass){
                return ['result'=>0,'msg'=>'登录密码不正确'];
            }
        }else{
           if ($user->pay_password != $pass){
               return ['result'=>0,'msg'=>'原支付密码不正确'];
           }
        }

        if ($password_pay != $passwordPay_confirm){
            return ['result'=>0,'msg'=>'两次密码输入不一致'];
        }

        if(! is_numeric($password_pay)){
            return ['result'=>0,'msg'=>'支付密码应为数字'];
        }

        if (strlen($password_pay) != 6){
            return ['result'=>0,'msg'=>'支付密码应为6位数字'];
        }
        $user->pay_password = md5($password_pay);
        if (! $user->save()){
            return ['result'=>0,'msg'=>$user->getError()];
        }
        return ['result'=>1,'msg'=>'支付密码设置成功'];


    }

    public static function passwordReset($password,$pass_new,$pass_new_confirm)
    {
        $user = \frontend\models\User::findOne(['id'=>Yii::$app->user->id]);
        $passwordMd = md5($password);
        if ($user->password != $passwordMd){
            return ['result'=>0,'msg'=>'原登录密码不正确'];
        }
        if ($pass_new != $pass_new_confirm){
            return ['result'=>0,'msg'=>'两次密码输入不一致'];
        }
        if (strlen($pass_new) < 6 or strlen($pass_new_confirm) > 16){
            return ['result'=>0,'msg'=>'密码长度为6-16位'];
        }

        if(! ctype_alnum($pass_new)){
            return ['result'=>0,'msg'=>'密码只能包含数字和字母'];
        }
        $user->password = md5($pass_new);
        $user->generateAuthKey();
        if (! $user->save()){
            return ['result'=>0,'msg'=>$user->getError()];
        }
        return ['result'=>1,'msg'=>'密码重置成功'];

    }


    public static function pointCodeSet($point_code)
    {
        $user = User::findOne(['id'=>Yii::$app->user->id,'deleted'=>0]);
        if (empty($user)){
            return ['result'=>0,'msg'=>'用户失效'];
        }

        if ($user->point_code != $point_code){
            return ['result'=>0,'msg'=>'积分码无效'];
        }

        if ($user->point_status != 1){
            return ['result'=>0,'msg'=>'积分码已兑换,不可再次使用'];
        }

        $user->point += 50;
        $user->point_status = 0;
        if (! $user->save()){
            return ['result'=>0,'msg'=>$user->getError()];
        }

        return ['result'=>1,'msg'=>'积分码兑换成功'];

    }

    public static function userUp($start_date,$end_date)
    {
        if (empty($start_date)){
            $start_date = strtotime(date('Y-m-d',time())) - 3600 * 24 * 6;
            $end_date = strtotime(date('Y-m-d',time()));
        }else{
            $start_date = strtotime($start_date);
            $end_date = strtotime($end_date);
        }
        $i = 0;
        $dates = [];
        $day_nums = [];
        $total_nums = [];
        do{
            $start_time = $start_date + 3600 * 24 * $i;
            $end_time = $start_time + 3600 * 24;
            $dates[$i] = date('Y-m-d',$start_time);
            $user_count = User::find()->where(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time])
                                        ->andWhere(['deleted'=>0])->count();
            $total_count = User::find()->where(['<','create_at',$end_time])->andWhere(['deleted'=>0])->count();
            $day_nums[$i] = empty($user_count) ? 0 : $user_count;
            $total_nums[$i] = empty($total_count) ? 0 : $total_count;
            $i++;
        }while($start_time < $end_date);

        $data['dates'] = $dates;
        $data['day_nums'] = $day_nums;
        $data['total_nums'] = $total_nums;

        return $data;
    }
    public static function active_userUp_type($start_date,$end_date,$type='',$user_ss=''){
        if (empty($start_date)){
            $start_date = strtotime(date('Y-m-d',time())) - 3600 * 24 * 6;
            $end_date = strtotime(date('Y-m-d',time()));
        }else{
            $start_date = strtotime($start_date);
            $end_date = strtotime($end_date);
        }
        if($user_ss){
            $sql = "AND user_id IN ($user_ss)";
        }else{
            $sql = "";
        }
        $i = 0;
        do{
            $start_time = $start_date + 3600 * 24 * $i;
            $end_time = $start_time + 3600 * 24;
            $dates[$i] = date('Y-m-d',$start_time);
            switch($type){
                case 1:
                    $user_count = $user_s = Yii::$app->db->createCommand("SELECT COUNT(id) as count FROM `service_keyword_search` WHERE create_at BETWEEN $start_time AND $end_time AND type=1 AND client_type=1 $sql AND deleted=0")->queryOne()['count'];
                    break;
                case 2:
                    $user_count = $user_s = Yii::$app->db->createCommand("SELECT COUNT(id) as count FROM `service_keyword_search` WHERE create_at BETWEEN $start_time AND $end_time AND type=1 AND client_type=2 $sql AND deleted=0")->queryOne()['count'];
                    break;
                case 3:
                    $user_count = $user_s = Yii::$app->db->createCommand("SELECT COUNT(id) as count FROM `service_keyword_search` WHERE create_at BETWEEN $start_time AND $end_time AND type=1 AND client_type=3 $sql AND deleted=0")->queryOne()['count'];
                    break;
                case 4:
                    $user_count = $user_s = Yii::$app->db->createCommand("SELECT COUNT(id) as count FROM `service_keyword_search` WHERE create_at BETWEEN $start_time AND $end_time AND type=2 $sql AND deleted=0")->queryOne()['count'];
                    break;
                case 5:
                    $user_count = $user_s = Yii::$app->db->createCommand("SELECT COUNT(id) as count FROM `comment` WHERE create_at BETWEEN $start_time AND $end_time $sql AND deleted=0")->queryOne()['count'];
                    break;
                case 6:
                    $user_count = $user_s = Yii::$app->db->createCommand("SELECT COUNT(id) as count FROM `sales_record` WHERE create_at BETWEEN $start_time AND $end_time $sql AND deleted=0")->queryOne()['count'];
                    break;
                case 7:
                    $user_count = $user_s = Yii::$app->db->createCommand("SELECT COUNT(id) as count FROM `sku_record` WHERE create_at BETWEEN $start_time AND $end_time $sql AND deleted=0")->queryOne()['count'];
                    break;
                case 8:
                    $user_count = $user_s = Yii::$app->db->createCommand("SELECT COUNT(id) as count FROM `flashget_recod` WHERE create_at BETWEEN $start_time AND $end_time $sql AND deleted=0")->queryOne()['count'];
                    break;
            }
            $day_nums[$i] = empty($user_count) ? 0 : $user_count;
            $i++;
        }while($start_time < $end_date);
        $data['dates'] = $dates;
        $data['day_nums'] = $day_nums;
        return $data;
    }
    public static function userUp_type($start_date,$end_date,$type='')
    {
        if (empty($start_date)){
            $start_date = strtotime(date('Y-m-d',time())) - 3600 * 24 * 6;
            $end_date = strtotime(date('Y-m-d',time()));
        }else{
            $start_date = strtotime($start_date);
            $end_date = strtotime($end_date);
        }
        $i = 0;
        $dates = [];
        $day_nums = [];
        $total_nums = [];
        do{
            $start_time = $start_date + 3600 * 24 * $i;
            $end_time = $start_time + 3600 * 24;
            $dates[$i] = date('Y-m-d',$start_time);
            switch($type){
                case 1:
                    $user_count = User::find()->where(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time])
                        ->andWhere(['deleted'=>0])->count();
                    break;
                case 2:
                    $user_count = UserPayLog::find()->where(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time])
                        ->andWhere(['deleted' => 0,'pay_status'=>1])->sum('pay_fee');
                    $user_count = empty($user_count) ? 0: $user_count;
                    break;
                case 3:
                    $user_count = User::find()->where(['>','last_login_at',$start_time])->andWhere(['<','last_login_at',$end_time])
                        ->andWhere(['deleted'=>0])->count();
                    break;
                case 4:
                    $user_count = User::find()->where(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time])
                        ->andWhere(['deleted'=>0])->andWhere(['<>','up_user_id',''])->count();
                    break;
                case 5:
                    $user_count = UserShopAuth::find()->where(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time])
                        ->andWhere(['deleted' => 0,'auth_status'=>1])->count();
                    break;
                case 6:
                        $user_count = User::find()->where(['<','last_login_at',strtotime(date('Y-m-d',strtotime('-7 days')) )])
                            ->andWhere(['deleted'=>0])->count();
                    break;
                case 7:
                        $user_count = User::find()->where(['<','last_login_at',strtotime(date('Y-m-d',strtotime('-15 days')) )])
                            ->andWhere(['deleted'=>0])->count();
                    break;
                case 8:
                case 9:
                    if($i>=1){
                        $user_count = $day_nums[0];
                    }else{
                        $user_count = User::find()->where(['<','last_login_at',strtotime(date('Y-m-d',strtotime('-30 days')) )])
                            ->andWhere(['deleted'=>0])->count();
                    }
                    break;
                case 11:
                    $user_count = MonitorKeywordResult::find()->where(['>','update_at',$start_time])->andWhere(['<','update_at',$end_time])
                        ->count();
                    break;
                case 12:
                    $user_count = ServiceKeywordSearch::find()->where(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time])
                        ->andWhere(['type' => 2])->count();
                    break;
                case 13:
                    $user_count = SalesRecord::find()->where(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time])
                        ->count();
                    break;
                case 14:
                    $user_count = SkuRecord::find()->where(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time])
                        ->count();
                    break;
                case 15:
                    $user_count = Comment::find()->where(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time])
                        ->count();
                    break;
                case 16:
                    $user_count = FlashgetRecod::find()->where(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time])
                        ->count();
                    break;
            }
//            $total_count = User::find()->where(['<','create_at',$end_time])->andWhere(['deleted'=>0])->count();
            $day_nums[$i] = empty($user_count) ? 0 : $user_count;

//            $total_nums[$i] = empty($total_count) ? 0 : $total_count;
            $i++;
        }while($start_time < $end_date);

        $data['dates'] = $dates;
        $data['day_nums'] = $day_nums;
//        $data['total_nums'] = $total_nums;

        return $data;
    }
    public function getLevel()
    {
        return $this->hasOne(UserLevel::className(),['id'=>'level_id']);
    }
    public function getLevelLog()
    {
        return $this->hasOne(UserLevelLog::className(),['user_id'=>'id']);
    }
    public function getUpUser()
    {
        return $this->hasOne(User::className(),['id'=>'up_user_id']);
    }
    public function getDistributor()
    {
        return $this->hasOne(User::className(),['id'=>'distributor_id']);
    }




    //重新计算会员的vip等级 返回等级 用户
    public function getInviteLevelIdU($id){
        $num = UserShopAuth::find()->where(['up_user_id'=>$id,'auth_status'=>1])->count();
        //1人是vip1  3人是vip2    10人是vip3
        if($num>=1 && $num < 3){
            $invite_level_id = 2;
        }elseif ($num >=3 && $num < 10){
            $invite_level_id = 3;
        }elseif ($num>=10){
            $invite_level_id = 4;
        }else{
            $invite_level_id = 0;
        }
        // 获取未使用用户
        $uid_s = User::find()->where(['up_user_id'=>$id,'is_invite_count'=>0])->all();
        if(!empty($uid_s)){
            foreach($uid_s as $k => $y){
                $uid[] = $y['id'];
            }
        }else{
            $uid = [];
        }
        $data['uid'] = $uid;
        $data['invite_level_id'] = $invite_level_id;
        return $data;
    }

    //重新计算会员的vip等级
    public function getInviteLevelId($id){
        $num = UserShopAuth::find()->where(['up_user_id'=>$id,'auth_status'=>1])->count();
        //1人是vip1  3人是vip2    10人是vip3
        if($num>=1 && $num < 3){
            $invite_level_id = 2;
        }elseif ($num >=3 && $num < 10){
            $invite_level_id = 3;
        }elseif ($num>=10){
            $invite_level_id = 4;
        }else{
            $invite_level_id = 0;
        }
        return $invite_level_id;
    }

    /**
     * 获取某个权限剩余的次数
     * author lkp
     * @param $id 代表权限的id
     */
    public static function getLeftTimesResult($id){
        if (!\Yii::$app->user->isGuest) {
            //是会员，去判断对应的会员等级，所对应的权限
            $user = User::findOne(Yii::$app->user->id);
            $level_id = $user->level_id; //当前用户的会员等级
            if($is_vip = User::priorityVip(Yii::$app->user->id)){
                if($is_vip['type'] == 1){
                    $level_id = $is_vip['level_id'];
                }else{
                    $level_id = $is_vip['level_id'];
                }
            }

            if($id == 1 && $user->is_friend == 1){
                return ['result'=>1]; //如果是好友关系，则查排名没有限制
            }
            $level_info = UserLevelService::find()->where(['service_id'=>$id,'user_level_id'=>$level_id])->one();
            if(empty($level_info)){
                //证明没有权限，直接返回
                if ($id==3){
                    return ['result'=>0,'msg'=>'权限不足,请购买高等级会员!'];
                }
                return ['result'=>0,'msg'=>'没有权限'];

            }
            if($id != 1 || $id != 2 || $id != 3){
                if($user->level_fate == 0){
                    //证明没有权限，直接返回
//                    return ['result'=>0,'msg'=>'没有权限'];
                }
            }
            $times = User::getLeftTimesById($id,$level_id);
        }else{
            //按照网页访客的权限来限制
            $level_id = 7; //代表着网页客户
            $times = User::getLeftTimesByIp($id,$level_id);
        }
        //根据权限id，用户当前的会员等级来返回用户对该接口的使用权限
        $level_info = UserLevelService::find()->where(['service_id'=>$id,'user_level_id'=>$level_id])->one();
        $level_info ? $day_limit = $level_info->day_limit : $day_limit=0;

        if (!\Yii::$app->user->isGuest) {
            $user = User::findOne(Yii::$app->user->id);
            //判断是否是自己的内部账号
            if($user->is_own == 1){
                //是自己的内部账号则无限制
                return ['result'=>1];
            }

        }

        if($day_limit == 0){
            //证明没有权限，直接返回
            return ['result'=>0,'msg'=>'没有权限'];
        }

        if($day_limit<= $times && $day_limit != -1){
            if(in_array($id, [1, 5])){
                return ['result'=>0,'msg'=>'次数达上限，可充值会员增加次数'];
            }else{
                return ['result'=>0,'msg'=>'当日查询达到上限'];
            }
        }
        return ['result'=>1];
    }

    //获取用户的剩余次数
    public static function getLeftTimesById($id,$level_id){
        //24小时的起止时间
        $start_time = strtotime(date('Y-m-d',time()));
        $end_time = $start_time + 86400;

        //判断当前等级的该权限是否需要每日重置
        $user_level = UserLevelService::findOne(['user_level_id'=>$level_id,'service_id'=>$id]);
        //查询出该权限当前已经使用过的次数
        switch ($id){
            case 1 ://查排名
                $times = ServiceKeywordSearch::find()
                    ->where(['type'=>1,'user_id'=>Yii::$app->user->id,'state'=>3]);
                if($user_level->is_daily_reset == 1){
                    $times->andWhere(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time]);
                }
                $result = $times->count();//已使用次数

                break;
            case 2 ://查权重
                $times = ServiceKeywordSearch::find()
                    ->where(['type'=>2,'user_id'=>Yii::$app->user->id,'state'=>3]);
                   if($user_level->is_daily_reset == 1){
                       $times->andWhere(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time]);
                   }
                $result = $times->count();//已使用次数

                break;
            case 3 ://查销量
                $times = SalesRecord::find()
                    ->where(['user_id'=>Yii::$app->user->id,'status'=>1]);
                   if($user_level->is_daily_reset == 1){
                       $times->andWhere(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time]);
                   }
                $result = $times->count();//已使用次数

                break;
            case 4 ://查评价
                $times = Comment::find()
                    ->where(['user_id'=>Yii::$app->user->id]);
                   if($user_level->is_daily_reset == 1){
                       $times->andWhere(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time]);
                   }
                $result = $times->count();//已使用次数
                break;
            case 5 ://排名监控
                $times = MonitorGoods::find()
                    ->where(['user_id'=>Yii::$app->user->id,'deleted'=>0,'status'=>1,'is_competitor'=>0]);
                     if($user_level->is_daily_reset == 1){
                         $times->andWhere(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time]);
                     }
                $result = $times->count();//已使用次数

                break;
            case 6 ://竞品监控
                $times = CompareGoods::find()
                    ->where(['user_id'=>Yii::$app->user->id,'monitor_close'=>0]);
                     if($user_level->is_daily_reset == 1){
                         $times->andWhere(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time]);
                     }
                    $result = $times->count();
                break;
            case 7 ://sku占比

                $times = SkuRecord::find()
                    ->where(['user_id'=>Yii::$app->user->id,'status'=>1]);
                   if($user_level->is_daily_reset == 1){
                       $times->andWhere(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time]);
                   }
                $result = $times->count();//已使用次数
                break;
            case 8: //快车查询
                $times = FlashgetRecod::find()
                    ->where(['user_id'=>Yii::$app->user->id,'status'=>1]);
                if($user_level->is_daily_reset == 1){
                    $times->andWhere(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time]);
                }
                $result = $times->count();//已使用次数
                break;
            case 11 ://评论监控
                $times = MonitorComment::find()
                    ->where(['user_id'=>Yii::$app->user->id,'deleted'=>0,'status'=>1]);
                if($user_level->is_daily_reset == 1){
                    $times->andWhere(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time]);
                }
                $result = $times->count();//已使用次数
                break;
            default:$result = 0;break;
        }
        return $result;
    }
    //获取网页游客剩余次数
    public static function getLeftTimesByIp($id,$level_id){
        //24小时的起止时间
        $start_time = strtotime(date('Y-m-d',time()));
        $end_time = $start_time + 86400;
        //判断当前等级的该权限是否需要每日重置
        $user_level = UserLevelService::findOne(['user_level_id'=>$level_id,'service_id'=>$id]);
        //查询出该权限当前已经使用过的次数
        switch ($id){
            case 1 ://查排名
                $times = ServiceKeywordSearch::find()
                    ->where(['type'=>1,'user_id'=>Yii::$app->user->id,'state'=>3]);
                if($user_level->is_daily_reset == 1){
                    $times->andWhere(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time]);
                }
                $result = $times->count();//已使用次数
                break;
            case 2 ://查权重
                $times = ServiceKeywordSearch::find()
                    ->where(['type'=>2,'user_id'=>Yii::$app->user->id,'state'=>3]);
                if($user_level->is_daily_reset == 1){
                    $times->andWhere(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time]);
                }
                $result = $times->count();//已使用次数
                break;
            case 4 ://查评价
                $times = Comment::find()
                    ->where(['user_id'=>Yii::$app->user->id]);
                if($user_level->is_daily_reset == 1){
                    $times->andWhere(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time]);
                }
                $result = $times->count();//已使用次数
                break;
            default:$result = 0;break;
        }
        return $result;
    }



    //获取当前用户所能重置的月份
    public static function getUserMem(){
        $user = User::findOne(['id'=>Yii::$app->user->id,'deleted'=>0]);
        if($user->level_id !=1 &&  $user->level_id !=5 && $user->level_id !=6 && $user->level_id !=7){
            $diff = ceil(($user->level_end_at - time()) / 86400 /30);//还剩下的月数
            if($diff == 1){
                $mem = 1;
            }elseif ($diff > 1 && $diff < 3){
                $mem = 3;
            } elseif ($diff > 3 && $diff < 6){
                $mem = 6;
            }elseif ($diff > 6){
                $mem = 12;
            }else{
                $mem = 0;
            }
        }else{
            $mem = 0;
        }
        return $mem;
    }

    /**
     * 检查并更新用户随机数
     * @param int $type 更新条件 0随机数为空则更新 1更新下一个可用随机数
     */
    public function updateUserRandom($type = 0){
        $this->random_id = '';
//        switch ($type){
//            case 0:
//                if(empty($this->random_id)){
//                    $randomData = RandomList::findOne([
//                        'deleted' => 0,
//                        'is_type' => 0
//                    ]);
//                    $this->random_id = $randomData->random;
//                }
//                break;
//            case 1:
//                RandomList::updateAll(['deleted' => 1, 'random' => $this->random_id]);
//                $nowRandom = RandomList::findOne(['random' => $this->random_id]);
//                $nextId = RandomList::find()->where([
//                        'deleted' => 0,
//                        'is_type' => 0
//                    ])
//                    ->andWhere(['>' , 'id', $nowRandom->id])
//                    ->min('id');
//                $nextRandom = RandomList::findOne($nextId);
//                $this->random_id = $nextRandom->random;
//                break;
//        }
        $this->random_id = '';
        $this->save();
    }

    /**
     * @param int $activeDays 活跃用户最近登陆天数
     * @return array
     */
    public static function getActiveUserIds($activeDays = 15){
        $activeTimeAgo = time() - 3600 * 24 * $activeDays;
        return User::find()
            ->select('id')
            ->where(['deleted' => 0])
            ->andWhere(['>','last_login_at', $activeTimeAgo])
            ->column();
    }
}
