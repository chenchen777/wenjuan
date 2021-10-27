<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "ip_list_java".
 *
 * @property int $id
 * @property string $ip ip
 * @property string $port 端口
 * @property string $account 账号
 * @property string $password 密码
 * @property int $is_use 0 未使用 1已使用
 * @property string $version
 * @property int $deleted
 * @property string $create_at
 * @property string $update_at
 * @property int $past_at 过期时间
 * @property string $area 地区
 * @property string $inspect_at 检查时间
 */
class IpListJava extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ip_list_java';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_use', 'version', 'deleted', 'create_at', 'update_at', 'past_at', 'inspect_at'], 'integer'],
            [['ip', 'port', 'account', 'password'], 'string', 'max' => 64],
            [['area'], 'string', 'max' => 30],
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
            'past_at' => 'Past At',
            'area' => 'Area',
            'inspect_at' => 'Inspect At',
        ];
    }
}
