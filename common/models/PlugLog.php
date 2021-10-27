<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "plug_log".
 *
 * @property int $id
 * @property int $user_id
 * @property int $plug_name 名称
 * @property int $create_at
 * @property int $update_at
 * @property int $version
 * @property int $deleted
 */
class PlugLog extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'plug_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'create_at', 'update_at', 'version', 'deleted'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'plug_name' => 'plug_name',
            'user_id' => 'User ID',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'version' => 'Version',
            'deleted' => 'Deleted',
        ];
    }
}
