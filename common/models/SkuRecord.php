<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sku_record".
 *
 * @property string $id
 * @property string $user_id 用户id
 * @property int $deleted
 * @property string $search_ip 用户的ip地址
 * @property int $status 是否查询成功  1成功   0失败 
 * @property int $version
 * @property string $create_at
 * @property string $update_at
 */
class SkuRecord extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sku_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'deleted', 'status', 'version', 'create_at', 'update_at'], 'integer'],
            [['search_ip'], 'string', 'max' => 32],
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
            'search_ip' => 'Search Ip',
            'status' => 'Status',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'good_title' => 'good_title',
            'good_img' => 'good_img',
            'price' => 'price',
            'shop_type' => 'shop_type',
            'shop_name' => 'shop_name',
            'data' => 'data',
            'sku_start_at' => 'sku_start_at',
            'sku_end_at' => 'sku_end_at',
            'sku' => 'sku',
        ];
    }
}
