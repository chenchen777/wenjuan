<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "dubious_list".
 *
 * @property int $id
 * @property string $content  吐槽内容
 * @property string $jd_uid 京东账号
 * @property string $user_id 发起者
 * @property int $version
 * @property int $deleted
 * @property int $create_at
 * @property int $update_at
 */
class DubiousList extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dubious_list';
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
            'user_id' => '发起者',
            'jd_uid' => '受害者',
            'content' => '吐槽内容',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }
}
