<?php
/**
 * Created by PhpStorm.
 * User: JYT
 * Date: 2020/8/19
 * Time: 14:52
 */

namespace common\service;

use Yii;
use common\Helper\Helper;
use common\models\form\RankForm;
use common\models\ServiceKeywordSearch;
use common\models\ServiceKeywordSearchResult;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * 单品查排名业务类
 * Class RankSearchService
 * @package common\service
 */
class RankSearchService extends BaseService{

    public $mainSku;

    public $errorMap = '';

    /**
     * 关键词排名查询
     * @param ServiceKeywordSearch $model
     * @return bool
     * @throws
     */
    public function getRank(ServiceKeywordSearch $model){
        try{
            if (empty($userInfo = $model->user)){
                throw new Exception('未找到用户信息');
            }

            // 移动端curl  city参数
            $pcCity = strtr($model->city, ',', '-') . '-0';

            $rankForm = new RankForm($userInfo);
            $rankForm->searchModel = $model->model;
            $rankForm->sortType = $model->result_sort;
            $rankForm->keyword = $model->keyword;
            $rankForm->sku = $model->sku;
            $rankForm->skuList = Json::decode($model->sku_list);
            $rankForm->pageStart = $model->page_start;
            $rankForm->pageEnd = $model->page_end;
            $rankForm->priceMin = $model->price_min;
            $rankForm->priceMax = $model->price_max;
            $rankForm->city = $pcCity;
            $rankForm->searchId = $model->id;
            $temp_count = 1;
            switch ($model->client_type){
                case ServiceKeywordSearch::SEARCH_ENTRANCE_PC:
                    $resultArr = $this->rankPc($rankForm);
                    break;
                case ServiceKeywordSearch::SEARCH_ENTRANCE_APP:
                case ServiceKeywordSearch::SEARCH_ENTRANCE_M:
                    $resultArr = $this->rankApp($rankForm);
                    // todo.. 临时处理,查询多次看下效果(2021.03.19)
//                    if (empty($resultArr)){
//                        $resultArr = $this->rankApp($rankForm);
//                        $temp_count = 2;
//                        if (empty($resultArr)){
//                            $resultArr = $this->rankApp($rankForm);
//                            $temp_count = 3;
//                        }
//                    }
                    break;
                default:
                    // 参数错误
                    throw new exception('查询入口：参数错误');
                    break;
            }

            if (empty($resultArr)){
                $this->errorMap = 'resultArr为空' . $temp_count;
                throw new Exception('未搜索到排名信息');
            }

            $resultLog = new ServiceKeywordSearchResult();
            $resultLog->sericve_keyword_search_id = $model->id;
            $resultLog->user_id = $model->user_id;
            $resultLog->keyword = $model->keyword;
            $resultLog->create_at = $this->nowTime;
            $resultLog->update_at = $this->nowTime;
            $resultLog->search_type = 1;  // 1查排名 2查权重

            // 开启事务
            $trans = $this->beginTransaction();
            foreach ($resultArr as $resultData){
                $saveModel = clone $resultLog;
                $saveModel->sku = $resultData['sku'];
                $saveModel->good_title = $resultData['good_title'];
                $saveModel->good_img_url = $resultData['good_img_url'];
                $saveModel->good_url = $resultData['good_url'];
                $saveModel->promotion_logo = $resultData['promotion_logo'];
                $saveModel->type = $resultData['type'];
                $saveModel->price = $resultData['price'];

                $saveModel->page = $resultData['page'];
                $saveModel->page_position = $resultData['page_position'];
                $saveModel->page_order = $resultData['page_order'];

                $saveModel->weight = intval($resultData['weight']);
                $saveModel->title_weight = intval($resultData['title_weight']);

                $saveModel->comment = (string)$resultData['comment'];
                $saveModel->is_ad = $resultData['is_ad'];

                $saveModel->is_double = $resultData['is_double'];
                $saveModel->double_price = $resultData['double_price'];

                $saveModel->api_result_json = $resultData['api_result_json'];
                if (!$saveModel->save()){
                    $trans->rollBack();
                    $this->errorMap = 'ServiceKeywordSearchResult：' . $saveModel->getError();
                    throw new Exception('查询结果入库失败');
                }
            }
            
            $model->state = 3;
            if (!$model->save()){
                $trans->rollBack();
                $this->errorMap = 'service_keyword_search：' . $model->getError();
                throw new Exception('查询结果保存失败');
            }
            $trans->commit();
        }catch (\Throwable $e){
            $this->errors = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 查询电脑端
     * @param RankForm $rankForm
     * @return mixed
     * @throws
     */
    private function rankPc(RankForm $rankForm){
        $params = $rankForm->getPcParams();
        if (!$params){
            throw new Exception($rankForm->errors);
        }
        $url = Yii::$app->params['rank_pc'] .'v1/order';
        $result = Helper::curlPost($url, $params);

        $result_result = $result['result'] ?? [];
        $result_result = array_filter($result_result);      // 去除数组空元素

        if (empty($result_result)){
            $this->errorMap = '接口查询结果：'.Json::encode($result);
            throw new Exception('未查询到结果');
        }

        $list = [];
        foreach ($result_result as $data){
            if (empty($data)){
                continue;
            }
            // 请求成功
            if ($data['code'] == 1){
                $this->mainSku = $data['main_sku'];

                // 处理图片链接
                $good_img_url = $data['good_img_url'];
                if (strstr($good_img_url,'!q50')){
                    $good_img_url = strstr($good_img_url,'!q50',true);
                }
                $list[] = [
                    'sku'             => $data['sku'],
                    'good_title'      => $data['good_title'],
                    'good_img_url'    => $good_img_url,
                    'good_url'        => $data['good_url'],
                    'promotion_logo'  => $data['promotion_logo'],
                    'type'            => $data['result_type'],    // 1自营 2非自营
                    'price'           => empty($data['good_price']) ? 0 : $data['good_price'],

                    'page'            => $data['result_page'],
                    'page_position'   => $data['result_page_order'] < 0 ? $data['result_page_order'] + 30 : $data['result_page_order'],
                    'page_order'      => $data['result_order'] < 0 ? $data['result_order'] + 30 : $data['result_order'],

                    'weight'          => $data['result_weight_score'] < 0 ? $data['result_weight_score'] + 30 : $data['result_weight_score'],
                    'title_weight'    => $data['result_title_weight_score'] < 0 ? $data['result_title_weight_score'] + 30 : $data['result_title_weight_score'],

                    'comment'         => $data['result_comment'],
                    'is_ad'           => intval($data['is_ad']),

                    'is_double'       => intval($data['is_double11']),
                    'double_price'    => empty($data['double11_price']) ? '' : $data['double11_price'],

                    'api_result_json' => Json::encode($data)
                ];
                continue;
            }

            // 错误码code处理
            $errorMsg = $data['error_message'] ?? '';
//          $error_map = $data['error_map'] ?? '';
            $this->errorMap = 'code：'.$data['code'].'，msg：'.$errorMsg;
            switch ($data['code']){
                case 3:         // 请求参数错误|代理ip过期|搜索结果未在搜索范围内|搜索了不相关的关键词+SKU
                    throw new Exception('系统繁忙请稍后再试');
                    break;
                case 2:
                    throw new Exception('结果不在搜索范围内');
                    break;
                default:        // 未知错误
                    throw new Exception('未知错误');
                    break;
            }
        }
        return $list;
    }

    /**
     * 查询移动端
     * @param RankForm $rankForm
     * @return mixed
     * @throws
     */
    private function rankApp(RankForm $rankForm){
        $params = $rankForm->getAppParams();
        if (!$params){
            throw new Exception($rankForm->errors);
        }
        $searchModel = $rankForm->searchModel;
        if($searchModel == ServiceKeywordSearch::SEARCH_MODEL_GOODS){
            $url = Yii::$app->params['go_app_ranking'];
        }else{
            $url = Yii::$app->params['go_appshop_ranking'];
        }
        $result = Helper::curlPost($url, $params, false);
//        if(Yii::$app->user->id == 47){
//            echo '<pre>';
//            var_dump($result) . PHP_EOL;
//        }
        $result_success = $result['success'] ?? 101;
        $result_result = $result['result'] ?? [];
        $result_result = array_filter($result_result);      // 去除数组空元素

        // 验证请求结果
        if(empty($result) || empty($result_result) || $result_success == 101){
            $this->errorMap = '接口查询结果：'.Json::encode($result);
            throw new Exception('系统繁忙请稍后再试');
        }

        $list = [];
        if($result_success == 200){
            foreach($result_result as $data){
                if (empty($data) || empty($good_img_url = $data['good_img_url'])){
                    continue;
                }
                $data_success = $data['success'] ?? 0;
                if ($data_success == 100 || empty($data['page_order_app'])){    // 查询了不相关的关键词和SKU
                    $this->errorMap = 'result[0]：'.Json::encode($data);
                    throw new Exception('未搜索到SKU排名信息');
                }
                if ($data['sku'] == $rankForm->sku){
                    $this->mainSku = $data['main_sku'];
                }
                // 处理图片链接
                if (strstr($good_img_url,'!q50')){
                    $good_img_url = strstr($good_img_url,'!q50',true);
                }
                $is_id = $data['is_ad'] ?? 0;

                $list[] = [
                    'sku'             => $data['sku'],
                    'good_title'      => $data['good_title'],
                    'good_img_url'    => $good_img_url,
                    'good_url'        => $data['good_url'],
                    'promotion_logo'  => '',
                    'type'            => empty($data['result_type']) ? 1 : 2,    // 1自营 2非自营
                    'price'           => empty($data['good_price']) ? 0 : $data['good_price'],

                    'page'            => empty($data['page_app']) ? 0 : $data['page_app'],
                    'page_position'   => empty($data['page_position_app']) ? 0 : $data['page_position_app'],
                    'page_order'      => empty($data['page_order_app']) ? 0 : $data['page_order_app'],

                    'weight'          => intval($data['result_weight_score']),
                    'title_weight'    => $data['result_title_weight_score'] ?? 0,

                    'comment'         => intval($data['result_comment']),
                    'is_ad'           => intval($is_id),

                    'is_double'       => 0,
                    'double_price'    => '',

                    'api_result_json' => Json::encode($data)
                ];
            }
        }else{
            $this->errorMap = '接口查询结果'.Json::encode($result);
            throw new Exception('当前sku不在所查页数范围内');
        }
        return $list;
    }

}