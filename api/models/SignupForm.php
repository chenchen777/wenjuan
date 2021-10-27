<?php
namespace api\models;

use common\models\CpsUser;
use common\models\CustomService;
use common\models\UserCustomService;
use common\models\UserPointDetail;
use Pheanstalk\Exception;
use yii;
use yii\base\Model;
use frontend\models\User;
use common\models\Saler;
use common\models\SysSmsLog;
use frontend\models\Config;
use common\models\UserLevel;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $mphone;
    public $validate_code;  //手机验证码
    public $password;
    public $confirm_password;
    public $share_code; //邀请码
    public $auth_key;
    public $verifyCode; //图片验证码


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
          
            ['mphone', 'trim'],
            ['mphone', 'required'],
            ['mphone', 'match', 'pattern' => '/^1[0-9]{10}$/', 'message' => '{attribute}格式不正确'],
            ['mphone', 'validateMphone'],
            
            [['password'], 'required'],
            [['password'], 'string', 'min' => 6, 'max' => 32],
            [['confirm_password'], "compare", "compareAttribute" => "password", "message" => "两次密码不一致"],
            
//            [['confirm_password'], 'required', 'message' => '确认密码不能为空'],
//            [['confirm_password'], 'string', 'min' => 6, 'max' => 32, 'message' => '确认密码不正确'],
            
            ['validate_code', 'required'],
//            ['validate_code', 'checkValidateCode'],
            
            ['share_code','trim'],
//            ['verifyCode', 'captcha'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'mphone' => '手机号',
            'password' => '密码',
            'confirm_password' => '确认密码',
            'validate_code' => '手机验证码',
            'share_code' => '邀请码',
            'verifyCode' => '图片验证码',
        ];
    }
    
    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup($share_code)
    {
//        if (!$this->validate()) {
//            return null;
//        }

//        $errorFile = fopen(Yii::getAlias('@frontend') .  '/signupError.txt','a');


        $trans = Yii::$app->db->beginTransaction();

        try{
            /*
         * user表记录
         */
            $user = new User();
            $user->level_id = 6;  // 试用会员
            $user->level_start_at = time();
            $user->level_end_at = time() + (365 * 86400);

            //file_put_contents('/data/wwwroot/jdcha/log/share_code.txt',$share_code);
            $upUser = User::findOne(['share_code'=>$share_code,'deleted'=>0]);
            $user->up_user_id = 0;
            if (! empty($upUser)){
                //file_put_contents('/data/wwwroot/jdcha/log/11111.txt',$share_code);
                $user->up_user_id = $upUser->id;
                if ($upUser->type == 1){
                    $user->distributor_id = $upUser->id;
                }
                if (! empty($upUser->distributor_id)){
                    $user->distributor_id = $upUser->distributor_id;
                }
            }

            //fwrite($errorFile,"正常进行2\n");

            $user->type = 0;
            $user->mphone = $this->mphone;
            $user->nickname = '';
            $user->password = md5($this->password);
            $user->generateAuthKey();
            $user->point = $user->point + 50;
            $user->share_code = $user->generateShareCode();
            $user->point_code = rand(10000000,99999999);
            $user->last_login_ip =Yii::$app->request->userIP;
            $user->last_login_at = time();
           // fwrite($errorFile,"正常进行3\n");

            if ( !$user->save()){
                //fwrite($errorFile,$user->getError() .'user');
              //  fclose($errorFile);
                $trans->rollBack();
                return null;
            }

            //fwrite($errorFile,"正常进行4\n");


            /*
             * user_point_detail表记录
             */
            $point = new UserPointDetail();
            $point->user_id = $user->id;
            $point->type = 'reg';
            $point->relate_mode = 'User';
            $point->relate_id  = $user->id;
            $point->point = 50;
            $point->title = '注册';
            $point->left_point = $user->point;
            if (! $point->save()){
              //  fwrite($errorFile,$point->getError() .'points');
               // fclose($errorFile);
                $trans->rollBack();
                return null;
            }

            //绑定客服关系
            $info = CustomService::find()->orderBy('total ASC')->one();
            $user_custom_service = new UserCustomService();
            $user_custom_service->user_id = $user->id;
            $user_custom_service->custom_service_id = $info->id;
            $user_custom_service->status = 0;
            $user_custom_service->save();

            //增长客服的绑定人数
            $obj = CustomService::findOne($info->id);

            $obj->total += 1;
            $obj->save();


           // fwrite($errorFile,"正常进行5\n");

            /**
             * 上级用户表记录
             */
            if (! empty($upUser)){

                //fwrite($errorFile,"正常进行6\n");

                $upUser->point += 50;
                if (! $upUser->save()){
                  //  fwrite($errorFile,$upUser->getError() .'upuser');
                   // fclose($errorFile);
                    $trans->rollBack();
                    return null;
                }

                //fwrite($errorFile,"正常进行7\n");

                $upPoint = new UserPointDetail();
                $upPoint->user_id = $user->up_user_id;
                $upPoint->type = 'share';
                $upPoint->relate_mode = 'User';
                $upPoint->relate_id = $user->id;
                $upPoint->point = 50;
                $upPoint->title = '分享';
                $upPoint->left_point = $upUser->point;
                if (! $upPoint->save()){
                   // fwrite($errorFile,$upPoint->getError() .'point');
                   // fclose($errorFile);
                    $trans->rollBack();
                    return null;
                }

                //fwrite($errorFile,"正常进行8\n");

            }

            //fwrite($errorFile,"正常进行9\n");

            /**
             * 推广表记录 cps_user
             */
          //  fwrite($errorFile,"share_code9:" . $share_code . "\n");
            if (! empty($upUser)){
                $cps_user = new CpsUser();
                $cps_user->up_user_id = $user->up_user_id;
                $cps_user->up_user_type = $user->type;
                $cps_user->user_id = $user->id;
                $cps_user->reward_point = 50;
                $cps_user->draw_rate = 15;
                if ($upUser->type==1){
                    $cps_user->draw_rate = 40;
                }

              //  fwrite($errorFile,"正常进行10\n");

                $cps_user->status = 1;

                if (! $cps_user->save()){
                  //  fwrite($errorFile,$cps_user->getError() .'cps');
                   // fclose($errorFile);
                    $trans->rollBack();
                    return null;
                }
             //   fwrite($errorFile,"正常进行11\n");
            }
         //   fclose($errorFile);
            $trans->commit();
            return $user;
        }catch (\Exception $e){
            $trans->rollBack();
         //   fwrite($errorFile,$e->getMessage() . "\n");
        //    fclose($errorFile);
            return null;
        }
    }
    
    //*****验证手机号码规则，实现商家和模特可以用同一个手机分别注册
    public function validateMphone($attribute, $params)
    {
        $_user = User::findOne(['mphone' => trim($this->mphone)]);
        if ($_user) {
            $this->addError($attribute, '该手机号已被占用');
        }
    }
    
    //*****验证手机号码规则，实现商家和模特可以用同一个手机分别注册
    public function checkValidateCode($attribute, $params)
    {
        if(YII_DEBUG) {
            return true;
        }
        if (empty($this->validate_code)) {
            $this->addError($attribute, '验证码不正确!');
            return false;
        }
        if (empty($this->mphone)) {
            $this->addError($attribute, '请输入手机号码!');
            return false;
        }
        $sms = new SysSmsLog();
        if (!$sms->check($this->validate_code, $this->mphone)) {
            $this->addError($attribute, '验证码不正确!');
            return false;
        }
        return true;
    }


    public function changeVip($shareCode){

    }
    

}
