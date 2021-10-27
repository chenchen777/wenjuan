<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_custom_service".
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $custom_service_id 客服id
 * @property int $status 是否加好友成功   1成功   0未成功
 * @property int $version
 * @property int $deleted
 * @property int $create_time
 * @property int $update_time
 */
class UserCustomService extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_custom_service';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'custom_service_id', 'status', 'version', 'deleted', 'create_at', 'update_at'], 'integer'],
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
            'custom_service_id' => 'Custom Service ID',
            'status' => 'Status',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create Time',
            'update_at' => 'Update Time',
        ];
    }
}
