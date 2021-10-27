<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "ip_white_list".
 *
 * @property int $id
 * @property string $ip ip
 * @property int $state 0 未使用 1已使用
 * @property string $version
 * @property int $deleted
 * @property string $create_at
 * @property string $update_at
 */
class IpWhiteList extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ip_white_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'version', 'deleted', 'create_at', 'update_at'], 'integer'],
            [['ip'], 'string', 'max' => 64],
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
            'status' => '状态',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }
}
