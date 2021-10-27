<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "config".
 *
 * @property int $id
 * @property string $version
 * @property int $deleted
 * @property string $create_at
 * @property string $update_at
 * @property string $name 配置名称
 * @property string $value 配置值
 * @property int $operator_id 操作人
 * @property string $title 配置说明
 */
class Config extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['version', 'deleted', 'create_at', 'update_at', 'operator_id'], 'integer'],
            [['value'], 'string'],
            [['name'], 'string', 'max' => 30],
            [['title'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'name' => 'Name',
            'value' => 'Value',
            'operator_id' => 'Operator ID',
            'title' => 'Title',
        ];
    }
}
