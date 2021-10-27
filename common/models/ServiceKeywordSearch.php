<?php

namespace common\models;

use yii;

/**
 * This is the model class for table "service_keyword_search".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $sku
 * @property string $sku_list
 * @property int $type 查询类型  1查排名 2查权重
 * @property int $client_type 查询入口  1电脑端 2移动端 4M端
 * @property int $model 查询模式 1指定商品 2指定店铺
 * @property string $keyword 关键字 使用json格式  例如 2个关键字{“关键字1”,”关键字2”}
 * @property int $page_start 页码开始区间
 * @property int $page_end 页码结束区间
 * @property int $result_sort 排列方式  1综合  2销量  3评论数  4新品 5价格
 * @property int $price_min 价格开始区间 单位分 1元使用100
 * @property int $price_max 价格结束区间 单位分 1元使用100
 * @property int $weight_min 查询权重的开始区间
 * @property int $weight_max 查询权重的结束区间
 * @property int $state 状态  1等待发送给后端查询  2等待后端返回结果  3后端返回结果查询结束
 * @property int $search_ip
 * @property int $city_id
 * @property int $deleted
 * @property int $version
 * @property int $create_at 创建时间
 * @property int $update_at 最后一次更新时间
 * @property string $ip
 * @property int $entrance_type 查询模式 1极速 2高级
 * @property int $status 状态 0未完成 1已完成
 * @property string $city 查询城市
 *
 * @property User $user 关联用户信息
 * @property ServiceKeywordSearchResult $result
 */
class ServiceKeywordSearch extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service_keyword_search';
    }

    const SEARCH_TYPE_RANK = 1;
    const SEARCH_TYPE_WEIGHT = 2;
    /**
     * @var array 查询类型
     */
    public static $searchTypeArr = [
        self::SEARCH_TYPE_RANK => '查排名',
        self::SEARCH_TYPE_WEIGHT => '查权重'
    ];

    const SEARCH_MODEL_GOODS = 1;
    const SEARCH_MODEL_STORE = 2;
    /**
     * @var array 查询模式
     */
    public static $searchModelArr = [
        self::SEARCH_MODEL_GOODS => '指定商品',
        self::SEARCH_MODEL_STORE => '指定店铺'
    ];

    const SEARCH_ENTRANCE_PC = 1;
    const SEARCH_ENTRANCE_APP = 2;
    const SEARCH_ENTRANCE_M = 4;
    /**
     * @var array 查询入口
     */
    public static $searchEntranceArr = [
        self::SEARCH_ENTRANCE_PC => '电脑端',
        self::SEARCH_ENTRANCE_APP => '移动端',
        self::SEARCH_ENTRANCE_M => 'M端'
    ];

    const SORT_TYPE_COMMON = 1;
    const SORT_TYPE_SALES = 2;
    const SORT_TYPE_COMMENTS_NUM = 3;
    const SORT_TYPE_NEW = 4;
    const SORT_TYPE_PRICE = 5;
    /**
     * @var array 排序方式
     */
    public static $sortTypeArr = [
        self::SORT_TYPE_COMMON => '综合',
        self::SORT_TYPE_SALES => '销量',
        self::SORT_TYPE_COMMENTS_NUM => '评论数',
        self::SORT_TYPE_NEW => '新品',
        self::SORT_TYPE_PRICE => '价格'
    ];


    const SEARCH_STATUS_UNFINISHED = 0;
    const SEARCH_STATUS_DONE = 1;
    /**
     * @var array 查询状态
     */
    public static $searchStatusArr = [
        self::SEARCH_STATUS_UNFINISHED => '未完成',
        self::SEARCH_STATUS_UNFINISHED => '已完成'
    ];


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'keyword', 'state','city'], 'required'],
            [['user_id', 'type', 'client_type', 'model', 'page_start', 'page_end', 'result_sort',
                'price_min', 'price_max', 'weight_min', 'weight_max', 'state', 'deleted','city_id',
                'version', 'create_at', 'update_at', 'entrance_type', 'status'], 'integer'],
            [['keyword','user_memo', 'ip', 'city'], 'string'],
            [['sku','search_ip'], 'string', 'max' => 32],
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
            'user_memo' => '用户添加备注',
            'state' => 'State',
            'search_ip' => 'Search Ip',
            'city_id'  => 'City Id',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'ip' => 'ip',
            'city' => 'city',
            'entrance_type' => 'entrance_type',
            'status' => 'status',
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
            return ['result'=>0, 'msg'=>$this->getError()];
        }
        return ['result'=>1];

    }

    public static function searchStatic($start_date,$end_date,$type)
    {
        if (empty($start_date)){
            $start_date = strtotime(date('Y-m-d',time())) - 3600 * 24 * 6;
            $end_date = strtotime(date('Y-m-d',time()));
        }else{
            $start_date = strtotime($start_date);
            $end_date = strtotime($end_date);
        }
        //  优化  删除deleted=0 
        $search = ServiceKeywordSearch::find();
        if (! empty($type)){
            $search->andWhere(['type'=>$type]);
        }

        $i = 0;
        $data = [];
        $total_count = [];
        $day_count = [];
        $dates = [];

        $querys = Yii::$app->db->createCommand("select FROM_UNIXTIME(create_at,'%Y-%m-%d')as time, count(id) as id from service_keyword_search where create_at BETWEEN $start_date and $end_date
                                                  group by FROM_UNIXTIME(create_at,'%Y-%m-%d')")->queryAll();
        // 总次数
        $cha_count = Yii::$app->db->createCommand("select count(id) from service_keyword_search where create_at BETWEEN 1 and $start_date")->queryScalar();

        foreach ($querys as $k =>$y){
            $day_count[$k] = $y['id'];
            $cha_count += $day_count[$k];
            $total_count[$k] =$cha_count;
            $dates[$k] = $y['time'];

        }
        
        $data['total_count'] = $total_count;
        $data['day_count'] = $day_count;
        $data['dates'] = $dates;

        return $data;

    }

    public function getUser()
    {
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }

    public function getResult()
    {
        return $this->hasOne(ServiceKeywordSearchResult::className(),['sericve_keyword_search_id'=>'id','sku'=>'sku']);
    }

}
