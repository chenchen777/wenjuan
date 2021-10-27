<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "global_rank".
 *
 * @property int $id
 * @property int $user_id 用户的ID
 * @property string $sku
 * @property int $client_type 查询条件中的入口  1表示电脑端 2微信端 3表示移动端
 * @property int $model 查询的模式 1表示指定商品 2表示指定店铺
 * @property string $keyword 查询的关键字  使用json格式  例如 2个关键字{“关键字1”,”关键字2”}
 * @property int $page_start 查询的开始区间
 * @property int $page_end 查询的结束区间
 * @property int $sort 查询的排列方式  1表示综合  2表示销量  3表示评论数  4表示新品 5价格升序 6价格降序
 * @property int $price_start 价格开始区间 单位元
 * @property int $price_end 价格结束区间 单位元
 * @property int $state 状态  1表示等待发送给后端查询  2表示等待后端返回结果  3表示后端返回结果查询结束
 * @property int $city_id 城市id
 * @property string $ip
 * @property string $time_pay 查询耗时
 * @property int $deleted
 * @property int $version
 * @property int $create_at 创建时间
 * @property int $update_at 最后一次更新时间
 */
class GlobalRank extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'global_rank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'state'], 'required'],
            [['user_id', 'client_type', 'model', 'page_start', 'page_end', 'sort', 'price_start', 'price_end', 'state', 'city_id','time_pay', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['sku'], 'string', 'max' => 32],
            [['keyword'], 'string', 'max' => 128],
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
            'client_type' => 'Client Type',
            'model' => 'Model',
            'keyword' => 'Keyword',
            'page_start' => 'Page Start',
            'page_end' => 'Page End',
            'sort' => 'Sort',
            'price_start' => 'Price Start',
            'price_end' => 'Price End',
            'state' => 'State',
            'city_id' => 'City ID',
            'time_pay' => 'Time Pay',
            'ip' => 'Ip',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }
}
