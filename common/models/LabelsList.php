<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "labels_list".
 *
 * @property int $id
 * @property string $uid  发起者
 * @property string $jd_user 受害者
 * @property string $labels_id 举报类型
 * @property int $version
 * @property int $deleted
 * @property int $create_at
 * @property int $update_at
 */
class LabelsList extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'labels_list';
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
            'uid' => '发起者',
            'jd_user' => '受害者',
            'labels_id' => '举报类型',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }
}
