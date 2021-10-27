<?php

namespace common\models;

use Yii;
use yii\web\IdentityInterface;
use yii\base\NotSupportedException;

/**
 * This is the model class for table "admin_lecture".
 *
 * @property int $id
 * @property string $username 帐号
 * @property string $password 密码
 * @property string $auth_key 记住我
 * @property string $truename 名称
 * @property string $balance 余额
 * @property int $type 0个人讲师  1机构 2机构下讲师
 * @property int $up_id 所属机构id
 * @property int $direct_into 直接分成
 * @property int $indirect_into 间接分成
 * @property string $invite_code 邀请码
 * @property string $lecture_code  分享码
 * @property int $is_close 是否停止使用
 * @property string $last_login_ip 最后登录ip
 * @property int $last_login_time 最后登录时间
 * @property int $version 版本
 * @property int $deleted 已删除
 * @property int $create_at 添加时间
 * @property int $update_at 修改时间
 */
class AdminLecture extends Base implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_lecture';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password','truename'], 'required'],
            [['balance'], 'number'],
            [['type', 'up_id', 'direct_into', 'indirect_into','is_close', 'last_login_time', 'version', 'deleted', 'create_at', 'update_at'], 'integer'],
            [['username', 'truename'], 'string', 'max' => 30],
            [['password'], 'string', 'max' => 32],
            [['invite_code'], 'string', 'max' => 16],
            [['auth_key'], 'string', 'max' => 64],
            [['last_login_ip'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => '手机号',
            'password' => 'Password',
            'auth_key' => 'Auth Key',
            'truename' => '姓名',
            'balance' => 'Balance',
            'type' => '讲师类型',
            'up_id' => '所属机构',
            'direct_into' => '直接分成',
            'indirect_into' => '间接分成',
            'invite_code' => '邀请码',
            'lecture_code' => '分享码',
            'is_close' => 'Is Close',
            'last_login_ip' => 'Last Login Ip',
            'last_login_time' => 'Last Login Time',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    /**
     * Finds an identity by the given ID.
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'deleted' => 0]);
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        // TODO: Implement getId() method.
        return $this->getPrimaryKey();
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }


    public function getLectureCode()
    {
        return uniqid().sprintf("%02s", mt_rand(0, 100));
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return bool whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
        return $this->getAuthKey() === $authKey;
    }

    public function getusername()
    {
        return $this->username;
    }

    public function getOrganizaion()
    {
        return $this->hasOne(AdminLecture::className(),['up_id' => 'id']);
    }


}
