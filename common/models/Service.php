<?php

namespace common\models;
use common\models\Base;
use Yii;

/**
 * This is the model class for table "service".
 *
 * @property int $id
 * @property string $name 服务名称
 * @property int $cost_point 服务花费点数
 * @property int $is_vip 是否vip专享1是
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class Service extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'cost_point'], 'required'],
            [['cost_point', 'is_vip', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['name'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '权限名称',
            'cost_point' => 'Cost Point',
            'is_vip' => 'Is Vip',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'is_daily_reset' => '是否需要每日重置',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }
}
