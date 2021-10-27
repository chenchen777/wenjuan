<?php
/**
 * Created by PhpStorm.
 * User: JYT
 * Date: 2020/8/18
 * Time: 11:58
 */

namespace common\models\form;

use common\models\BaseGoods;
use common\models\IpList;
use common\models\MonitorIp;
use common\models\ServiceKeywordSearch;
use common\models\SysUserAgent;
use common\models\User;
use Yii;
use yii\db\Exception;
use yii\db\Expression;
use yii\helpers\StringHelper;

/**
 * curl查询排名表单
 * Class RankForm
 * @package common\models\form
 *
 * @property int $searchModel 查询模式 1指定商品 2指定店铺
 * @property int $searchType 查询方式  1当前sku 2最高sku
 * @property int $sortType 排序方式 1综合 2销量 3评论数 4新品 5价格
 * @property string $keyword 查询关键词
 * @property string $sku 商品SKU
 * @property array $skuList 商品SKU数组
 * @property int $pageStart 页码区间
 * @property int $pageEnd 页码区间
 * @property int $priceMin 价格区间
 * @property int $priceMax 价格区间
 * @property string $city 省市区信息
 * @property int $searchId 查询id
 */
class RankForm{

    /**
     * @var int 查询模式 1指定商品 2指定店铺
     */
    public $searchModel = ServiceKeywordSearch::SEARCH_MODEL_GOODS;

    /**
     * @var int 排序方式 1综合 2销量 3评论数 4新品 5价格
     */
    public $sortType = 1;

    /**
     * @var string 查询关键词
     */
    public $keyword = '';

    /**
     * @var string 商品SKU
     */
    public $sku = '';

    /**
     * @var array 商品SKU数组
     */
    public $skuList = [];

    /**
     * @var int 页码区间
     */
    public $pageStart = 1;

    /**
     * @var int 页码区间
     */
    public $pageEnd = 50;

    /**
     * @var int 价格区间
     */
    public $priceMin = 0;

    /**
     * @var int 价格区间
     */
    public $priceMax = 0;

    /**
     * @var string 省市区信息
     */
    public $city = '';

    /**
     * @var int 查询id
     */
    public $searchId = 0;


    /**
     * @var string 错误信息
     */
    public $errors;

    private $cacheShopId = 0;


    /**
     * @var User
     */
    public $userInfo;

    /**
     * RankForm constructor.
     * @param User $userInfo
     */
    public function __construct(User $userInfo){
        $userInfo->updateUserRandom();
        $this->userInfo = $userInfo;
    }


    /**
     * 查排名-pc端请求参数
     *
     * 必须参数
     * @params string $sku
     * @params array $skuList
     * @params string $keyword
     * @params int $searchId 查询id
     *
     * @return array|bool
     */
    public function getPcParams(){
        try{
            // 获取UA
            $ua = SysUserAgent::randGetUaList(20);

            // 获取代理
            $space = $this->pageEnd - $this->pageStart + 1;
            if(empty($proxyList = IpList::pcGetProxyArr($space))){
                $proxyList = [];
            }

            // 获取代理IP
            $randomIP = MonitorIp::find()
                ->where(['deleted' => 0])
                ->orderBy(new Expression('rand()'))
                ->one();
            if (empty($randomIP)){
                throw new Exception('无可用代理IP');
            }
            /* @var $randomIP MonitorIp*/

            // 获取shop_id信息
            $shop_id = $this->getShopId($this->sku);
            $shop_ids = [$shop_id];
        }catch (\Throwable $e){
            $this->errors = $e->getMessage();
            return false;
        }

        return [
            'client_type'=> 1,
            'type'       => 1,

            'id'         => $this->searchId,
            'keyword'    => $this->keyword,
            'sku'        => $this->sku,
            'sku_list'   => $this->skuList,
            'city'       => $this->city,
            'model'      => intval($this->searchModel),

            'ip'         => $randomIP->ip,
            'port'       => intval($randomIP->port),
            'proxy_username' => $randomIP->account,
            'proxy_password' => $randomIP->password,

            'user_id'    => $this->userInfo->id,
            'random'     => $this->userInfo->random_id,
            'proxy_list' => $proxyList,
            'ua'         => $ua,

//            'entrance_type' => $model->entrance_type,
            'shop_id'    => $shop_id,
            'shop_ids'   => $shop_ids,

            'result_sort'=> intval($this->sortType),
            'page_start' => intval($this->pageStart),
            'page_end'   => intval($this->pageEnd),
            'price_min'  => intval($this->priceMin),
            'price_max'  => intval($this->priceMax)
        ];
    }

    /**
     * 查排名-app端请求参数
     * @return array|bool
     */
    public function getAppParams(){
        try{
            // 获取IP
            $space = $this->pageEnd - $this->pageStart + 1;

            //2021.03.26修改,代理改为采集端获取,此处不需要传代理
            if (empty($ipArr = IpList::appGetProxyArr($space))){
//                throw new Exception('系统繁忙请稍后再试');
                $ipArr = [];
            }

            // 获取shop_id信息
            $shop_id = $this->getShopId($this->sku);
        }catch (\Throwable $e){
            $this->errors = $e->getMessage();
            return false;
        }

        if ($this->searchModel == ServiceKeywordSearch::SEARCH_MODEL_GOODS){
            $skuList = empty($this->skuList) ? $this->sku : implode(',',$this->skuList);
            $params = [
                'ip'        => $ipArr,
                'keyword'   => $this->keyword,

                'sku'       => $this->sku,
                'sku_list'  => $skuList,

                'sort'      => $this->sortType,
                'page'      => intval($this->pageStart),
                'page_end'  => intval($this->pageEnd),
                'price_min' => intval($this->priceMin),
                'price_max' => intval($this->priceMax)
            ];
        }else{
            $params = [
                'ip'        => $ipArr,
                'keyword'   => $this->keyword,

                'shop_id'   => $shop_id,

                'sort'      => $this->sortType,
                'page'      => intval($this->pageStart),
                'page_end'  => intval($this->pageEnd),
                'price_min' => empty($this->priceMin) ? '' : $this->priceMin,
                'price_max' => empty($this->priceMax) ? '' : $this->priceMax
            ];
        }
        return $params;
    }

    /**
     * @param $sku
     * @return int
     * @throws Exception
     */
    private function getShopId($sku){
        $shop_id = 0;
        if ($this->searchModel == ServiceKeywordSearch::SEARCH_MODEL_STORE && empty($shop_id = $this->cacheShopId)){
            $shop_result = BaseGoods::shopInfo($sku);
            if ($shop_result['result'] != 1){
                throw new Exception($shop_result['msg']);
            }
            $shop_id = $shop_result['shop_id'] ?? 0;
            $this->cacheShopId = $shop_id;
        }
        return strval($shop_id);
    }
}