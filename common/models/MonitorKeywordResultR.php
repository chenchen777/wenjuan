<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "monitor_keyword_result_r".
 *
 * @property int $id
 * @property int $day 更新天数
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 * @property int $status 是否执行过 1：完毕 0：待准备
 */
class MonitorKeywordResultR extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'monitor_keyword_result_r';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['day', 'deleted', 'version', 'create_at', 'update_at', 'status'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'day' => 'Day',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'status' => 'Status',
        ];
    }
}
