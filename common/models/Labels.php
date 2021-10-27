<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "labels".
 *
 * @property int $id
 * @property string $type   1举报标签
 * @property string $label_name 标签名
 * @property int $version
 * @property int $deleted
 * @property int $create_at
 * @property int $update_at
 */
class Labels extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'labels';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['version', 'deleted', 'create_at', 'update_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'type',
            'label_name' => 'label_name',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }
}
