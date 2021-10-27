<?php
namespace api\models;

use Yii;
use yii\base\Model;
use frontend\models\User;
use common\models\SysSmsLog;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $mphone;
    public $validate_code;      //手机验证码
    public $password;       //新的密码
    public $password_confirm;   //确认新密码


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['mphone', 'trim'],
            [['mphone', 'validate_code', 'password', 'password_confirm'], 'required'],
            ['mphone', 'exist',
                'targetClass' => '\common\models\User',
                'filter' => ['deleted' => 0],
                'message' => '该手机不存在.'
            ],
            [['mphone', 'validate_code'], 'number'],
            [['password', 'password_confirm'], 'string', 'min' => 6, 'max' => 32],
            [['password_confirm'], 'compare', "compareAttribute" => "password", "message" => "两次密码不一致"],
        ];
    }

    public function attributeLabels()
    {
        return [
            'mphone' => '手机号',
            'validate_code' => '验证码',
            'password' => '密码',
            'password_confirm' => '确认密码',
        ];

    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return boolean | User   whether the email was send
     */
    public function passwordreset()
    {
        /* @var $user User */
        $user = User::findOne([
            'mphone' => $this->mphone,
        ]);

        if (!$user) {
            $this->addError('mphone', '手机号不存在');
            return false;
        }
        if (!$this->validate()){
            return false;
        }
        //todo....验证手机号和验证码是否有效,参数 $this->mphone $this->validate_code

        if (!User::findByPasswordResetToken(SysSmsLog::findByMphone($this->mphone), $this->validate_code)) {
            $this->addError('validate_code','验证码错误');
            return false;
        }
        $user->password = md5($this->password);
        if (!$user->save()) {
            $error = $user->getErrors('password');
            if (empty($error)) {
                $this->addError('password', '密码修改失败');
            } else {
                $this->addError('password', $error[0]);
            }
            return false;
        }
        return $user;


    }
}
