<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jing_ask".
 *
 * @property int $id
 * @property int $user_id 用户的ID
 * @property string $sku
 * @property string $good_title 商品标题
 * @property string $price 价格
 * @property string $shop_name 店铺名称
 * @property string $good_img 商品图片
 * @property int $good_count 好评数
 * @property int $shop_type 0 非自营 1自营
 * @property string $ip
 * @property int $que_num  问答数量
 * @property int $time_pay 查询耗时
 * @property int $deleted
 * @property int $version
 * @property int $create_at 创建时间
 * @property int $update_at 最后一次更新时间
 */
class JingAsk extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jing_ask';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'shop_type', 'time_pay','que_num','good_count', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['price'], 'number'],
            [['sku', 'shop_name'], 'string', 'max' => 32],
            [['good_title','good_img'], 'string', 'max' => 128],
            [['ip'], 'string', 'max' => 25],
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
            'sku' => 'Sku',
            'good_title' => 'Good Title',
            'good_count' => 'Good Count',
            'price' => 'Price',
            'shop_name' => 'Shop Name',
            'good_img' => 'Good Img',
            'shop_type' => 'Shop Type',
            'ip' => 'Ip',
            'que_num' => 'Que Num',
            'time_pay' => 'Time Pay',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }
}
