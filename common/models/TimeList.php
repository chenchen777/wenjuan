<?php

namespace common\models;

use common\models\Base;
use Yii;

/**
 * This is the model class for table "time_list".
 *
 * @property int $id
 * @property int $sku_time
 * @property int $user_id
 * @property string $sku
 * @property string $keyword
 * @property int $ip_id
 * @property int $ip_time
 * @property int $sku_save
 * @property int $sort_time
 * @property int $full_time
 * @property string $err_msg
 * @property string $err_map
 * @property string $type
 * @property int $create_at
 * @property int $update_at
 * @property int $version
 * @property int $deleted
 */
class TimeList extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'time_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku_time','user_id','ip_id', 'ip_time', 'sku_save', 'sort_time', 'full_time','type', 'create_at', 'update_at', 'version', 'deleted'], 'integer'],
            [['err_msg'], 'string', 'max' => 128],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku_time' => 'Sku Time',
            'user_id' => 'User Id',
            'sku' => 'Sku',
            'keyword' => 'Keyword',
            'ip_id' => 'Ip ID',
            'ip_time' => 'Ip Time',
            'sku_save' => 'Sku Save',
            'sort_time' => 'Sort Time',
            'full_time' => 'Full Time',
            'err_msg' => 'Err Msg',
            'err_map' => 'Err Map',
            'type' => 'Type',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'version' => 'Version',
            'deleted' => 'Deleted',
        ];
    }
}
