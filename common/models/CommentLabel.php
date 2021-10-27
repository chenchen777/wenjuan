<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "comment_label".
 *
 * @property string $id
 * @property int $user_id
 * @property string $sku
 * @property string $create_at
 * @property string $update_at
 * @property string $version
 * @property int $deleted
 * @property string $label_id 京东标签ID
 * @property int $label_count 标签数量
 * @property string $label_name 标签名称
 */
class CommentLabel extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comment_label';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'create_at', 'update_at', 'version', 'deleted', 'label_count'], 'integer'],
            [['create_at', 'update_at'], 'required'],
            [['sku'], 'string', 'max' => 128],
            [['label_id', 'label_name'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'sku' => 'Sku',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'label_id' => 'Label ID',
            'label_count' => 'Label Count',
            'label_name' => 'Label Name',
            'comment_id' => 'comment_id',
        ];
    }
}
