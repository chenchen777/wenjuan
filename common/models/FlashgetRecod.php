<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "FlashgetRecod".
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $deleted
 * @property int $status 是否查询成功  1成功   0失败 
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class FlashgetRecod extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'flashget_recod';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'deleted', 'status', 'version', 'create_at', 'update_at'], 'integer'],
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
            'deleted' => 'Deleted',
            'search_ip' => 'search_ip',
            'status' => 'Status',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }
}
