<?php

namespace common\models;

use backend\models\Admin;
use Yii;

/**
 * This is the model class for table "login_ip".
 *
 * @property int $id
 * @property int $admin_id
 * @property int $saler_id
 * @property int $user_id
 * @property int $login_at 登录时间
 * @property string $ip
 * @property int $version 版本
 * @property int $deleted 已删除
 * @property int $create_at 添加时间
 * @property int $update_at 修改时间
 */
class LoginIp extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'login_ip';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_id', 'saler_id', 'user_id', 'login_at', 'version', 'deleted', 'create_at', 'update_at'], 'integer'],
            [['ip'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_id' => 'Admin ID',
            'saler_id' => 'Saler ID',
            'user_id' => 'User ID',
            'login_at' => 'Login At',
            'ip' => 'Ip',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    public function getAdmin()
    {
        return $this->hasOne(Admin::className(),['id' => 'admin_id']);
    }


}
