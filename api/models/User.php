<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/12
 * Time: 下午5:09
 */


namespace api\models;

use common\component\Params;
use common\models\SysSmsLog;
use yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;
//use common\models\UserInfo;
use yii\base\NotSupportedException;


class User extends \common\models\User implements IdentityInterface
{

    /** @var $_token SysSmsLog **/
    private static $_token;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [

        ]);
    }

//    public function getInfo()
//    {
//        return $this->hasOne(UserInfo::className(), ['userid' => 'id'])->inverseOf('user');
//    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'deleted' => 0]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds adin by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByMphone($mphone)
    {
        return static::findOne([
            'mphone' => trim($mphone),
            'deleted' => 0,
        ]);
    }

    /**
     * Finds user by password reset token
     *
     * @param SysSmsLog $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken(SysSmsLog $token,$validate_code)
    {
        if (!static::isPasswordResetTokenValid($token,$validate_code)) {
            return null;
        }

        static::$_token = $token;

        return static::findOne([
            'mphone' => $token->phone,
//            'status' => 1,
        ]);
    }


    /**
     * Finds out if password reset token is valid
     *
     * @param SysSmsLog $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token,$validate_code)
    {
        if (empty($token)) {
            return false;
        }

        if (empty($validate_code) or $validate_code != $token->code)
        {
            return false;
        }
        $timestamp = $token->create_at;
        $expire = Params::getParams('user.passwordResetTokenExpire', 600);//Yii::$app->params['user.passwordResetTokenExpire'];

        return $timestamp + $expire >= time();
    }


    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function generateShareCode()
    {
        return uniqid().sprintf("%03s", mt_rand(0, 100));
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        throw new NotSupportedException();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        static::$_token->status = 0;
        static::$_token->save();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

}