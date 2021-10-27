<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "random_list2".
 *
 * @property int $id 随机数
 * @property string $random
 * @property int $is_type 使用类型 0 登录 1不登录
 * @property int $global_can_use 全球购是否可用-1失效 0待定 1可用
 * @property int $version
 * @property int $deleted
 * @property int $create_at
 * @property int $update_at
 */
class RandomList2 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'random_list2';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['random'], 'required'],
            [['is_type', 'global_can_use', 'version', 'deleted', 'create_at', 'update_at'], 'integer'],
            [['random'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'random' => 'Random',
            'is_type' => 'Is Type',
            'global_can_use' => 'Global Can Use',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }
}
