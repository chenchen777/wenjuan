<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "service_keyword_search_jx".
 *
 * @property int $id
 * @property int $user_id 用户的ID
 * @property string $sku
 * @property string $sku_list
 * @property int $type 查询的类型  1表示查排名 2表示差权重
 * @property int $client_type 查询条件中的入口  1表示电脑端 2表示移动端
 * @property int $model 查询的模式 1表示制定商品 2表示制定店铺
 * @property string $keyword 查询的关键字  使用json格式  例如 2个关键字{“关键字1”,”关键字2”}
 * @property int $page_start 查询的开始区间
 * @property int $page_end 查询的结束区间
 * @property int $result_sort 查询的排列方式  1表示综合  2表示销量  3表示评论数  4表示新品 5表示价格
 * @property int $price_min 价格开始区间 单位分 1元使用100
 * @property int $price_max 价格结束区间 单位分 1元使用100
 * @property int $weight_min 查询权重的开始区间
 * @property int $weight_max 查询权重的结束区间
 * @property int $state 状态  1表示等待发送给后端查询  2表示等待后端返回结果  3表示后端返回结果查询结束
 * @property string $search_ip
 * @property int $city_id 城市id
 * @property string $user_memo 用户添加备注
 * @property int $deleted
 * @property int $version
 * @property int $create_at 创建时间
 * @property int $update_at 最后一次更新时间
 * @property string $ip
 * @property int $entrance_type 查询模式  1：极速 2：高级
 * @property int $status 状态  0未完成 1已完成
 * @property string $city 查询指定城市
 */
class ServiceKeywordSearchJx extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service_keyword_search_jx';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'keyword', 'state'], 'required'],
            [['user_id', 'type', 'client_type', 'model', 'page_start', 'page_end', 'result_sort', 'price_min', 'price_max', 'weight_min', 'weight_max', 'state', 'city_id', 'deleted', 'version', 'create_at', 'update_at', 'entrance_type', 'status'], 'integer'],
            [['sku_list', 'keyword'], 'string'],
            [['sku', 'search_ip'], 'string', 'max' => 32],
            [['user_memo'], 'string', 'max' => 255],
            [['ip'], 'string', 'max' => 25],
            [['city'], 'string', 'max' => 100],
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
            'sku_list' => 'Sku List',
            'type' => 'Type',
            'client_type' => 'Client Type',
            'model' => 'Model',
            'keyword' => 'Keyword',
            'page_start' => 'Page Start',
            'page_end' => 'Page End',
            'result_sort' => 'Result Sort',
            'price_min' => 'Price Min',
            'price_max' => 'Price Max',
            'weight_min' => 'Weight Min',
            'weight_max' => 'Weight Max',
            'state' => 'State',
            'search_ip' => 'Search Ip',
            'city_id' => 'City ID',
            'user_memo' => 'User Memo',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'ip' => 'Ip',
            'entrance_type' => 'Entrance Type',
            'status' => 'Status',
            'city' => 'City',
        ];
    }

    public function keywordSave()
    {

        $this->state = 1;
        $this->user_memo = '';
        if ($this->type == 1){
            $this->weight_min = 0;
            $this->weight_max = 0;
        }else{
            $this->page_start = 0;
            $this->page_end = 0;
            $this->client_type = 0;
            $this->model = 0;
            $this->result_sort = 0;
        }
        if (! $this->save()){
            return ['result'=>0,'msg'=>$this->getError()];
        }
        return ['result'=>1];

    }
}
