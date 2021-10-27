<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "monitor_ip".
 *
 * @property int $id
 * @property string $ip ip
 * @property string $port 端口
 * @property string $account 账号
 * @property string $password 密码
 * @property int $is_use 0未使用 1已使用
 * @property string $area
 * @property int $version
 * @property int $deleted
 * @property int $create_at
 * @property int $update_at
 */
class MonitorIp extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'monitor_ip';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_use', 'version', 'deleted', 'create_at', 'update_at'], 'integer'],
            [['ip', 'port', 'account', 'password'], 'string', 'max' => 64],
            [['area'], 'string', 'max' => 128],
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
            'area' => 'Area',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }
}
