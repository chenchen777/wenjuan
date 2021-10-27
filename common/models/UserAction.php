<?php

namespace backend\models;

use common\models\Base;
use Yii;

/**
 * This is the model class for table "user_action".
 *
 * @property int $id
 * @property string $mphone 用户名
 * @property string $shop_name 店铺名
 * @property string $keyword 关键词
 * @property string $sku sku
 * @property int $create_at
 * @property int $update_at
 * @property int $version
 * @property int $deleted
 */
class UserAction extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_action';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_at', 'update_at', 'version', 'deleted'], 'integer'],
            [['mphone'], 'string', 'max' => 11],
            [['shop_name', 'keyword'], 'string', 'max' => 128],
            [['sku'], 'string', 'max' => 64],
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
            'shop_name' => 'Shop Name',
            'keyword' => 'Keyword',
            'sku' => 'Sku',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'version' => 'Version',
            'deleted' => 'Deleted',
        ];
    }
}
