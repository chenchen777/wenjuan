<?php
namespace api\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $mphone;
    public $password;
    public $rememberMe = true;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // mphone and password are both required
            [['mphone', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'mphone'  => '手机号',
            'password' => '密码',
            'rememberMe' => '记住我',
        ];
    }
    
    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if(!$user) {
                $this->addError($attribute, '账号不存在.');
                return false;
            }
//            if($this->distributor_id != $user->distributor_id || $this->agent_id != $user->agent_id) {
//                $this->addError($attribute, '不允许跨站登录.');
//                return false;
//            }
            if ($user->password != md5($this->password)) {
                $this->addError($attribute, '账号密码不正确.');
                return false;
            }
//             $user->auth_key = Yii::$app->security->generateRandomString();
//             $user->save();
        }
    }

    /**
     * Logs in a user using the provided mphone and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[mphone]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findBymphone($this->mphone);
        }
        return $this->_user;
    }
}
