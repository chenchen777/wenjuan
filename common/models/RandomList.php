<?php

namespace common\models;

use backend\models\Admin;
use common\models\RandomSku;
use Yii;

/**
 * This is the model class for table "random_list".
 *
 * @property int $id
 * @property int $random  随机数
 * @property int $is_type 使用类型 0登录 1不登录
 * @property int $global_can_use 全球购是否可用 0不可以  1可用
 * @property int $version 版本
 * @property int $deleted 已删除
 * @property int $create_at 添加时间
 * @property int $update_at 修改时间
 * @property int $is_update 是否已更新
 */
class RandomList extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'random_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['deleted', 'create_at', 'update_at','is_type','global_can_use'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'random' => 'random',
            'is_type' => 'Is Type',
            'global_can_use' => 'Global Can Use',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'is_update' => 'is_update',
        ];
    }


}
