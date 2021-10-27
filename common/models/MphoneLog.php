<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "mphone_log".
 *
 * @property string $id
 * @property string $version
 * @property int $deleted
 * @property string $create_at
 * @property string $update_at
 * @property string $mphone
 */
class MphoneLog extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mphone_log';
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
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'mphone' => 'Mphone',
        ];
    }
}
