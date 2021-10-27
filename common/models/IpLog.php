<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "ip_log".
 *
 * @property int $id
 * @property string $ip ip
 * @property string $port 端口
 * @property string $account 账号
 * @property string $password 密码
 * @property int $is_use 0未使用 1已使用
 * @property int $version
 * @property int $deleted
 * @property int $create_at
 * @property int $update_at
 */
class IpLog extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ip_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['version', 'deleted', 'create_at', 'update_at','is_use'], 'integer'],
            [['ip', 'port', 'account', 'password'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'Ip',
            'port' => 'Port',
            'account' => 'Account',
            'password' => 'Password',
            'is_use' => 'Is Use',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    /**
     * 获取一条记录并标记为已使用
     * @return IpLog
     */
    public static function getOneIp(){
        $model = self::findOne(['deleted' => 0, 'is_use' => 0]);
        if (!$model){
            self::updateAll(['is_use' => 0], ['deleted' => 0, 'is_use' => 1]);
            $model = self::findOne(['deleted' => 0, 'is_use' => 0]);
        }
        $model->is_use = 1;
        $model->save();
        return $model;
    }
}
