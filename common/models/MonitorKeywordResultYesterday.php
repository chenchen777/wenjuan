<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "monitor_keyword_result_yesterday".
 *
 * @property string $id
 * @property int $user_id
 * @property int $keyword_id
 * @property int $good_id
 * @property string $sku
 * @property int $page_pc pc端页码
 * @property int $deleted
 * @property int $version
 * @property string $create_at
 * @property string $update_at
 */
class MonitorKeywordResultYesterday extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'monitor_keyword_result_yesterday';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'keyword_id', 'good_id', 'page_pc', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['sku'], 'string', 'max' => 32],
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
            'keyword_id' => 'Keyword ID',
            'good_id' => 'Good ID',
            'sku' => 'Sku',
            'page_pc' => 'Page Pc',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }
}
