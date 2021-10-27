<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_linshi".
 *
 * @property string $id
 * @property string $mphone 手机
 * @property string $version
 * @property string $update_at
 * @property string $create_at
 * @property int $deleted
 * @property int $is_update
 */
class UserLinshi extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_linshi';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mphone'], 'required'],
            [['version', 'update_at', 'create_at', 'deleted', 'is_update'], 'integer'],
            [['mphone'], 'string', 'max' => 11],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mphone' => 'Mphone',
            'version' => 'Version',
            'update_at' => 'Update At',
            'create_at' => 'Create At',
            'deleted' => 'Deleted',
            'is_update' => 'Is Update',
        ];
    }
}
