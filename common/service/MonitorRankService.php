<?php
/**
 * Created by PhpStorm.
 * User: JYT
 * Date: 2020/8/12
 * Time: 16:33
 */
namespace common\service;

use Yii;
use yii\db\Exception;
use yii\helpers\Json;
use common\models\BaseGoods;
use common\Helper\Helper;
use common\models\form\RankForm;
use common\models\User;
use common\models\MonitorGoods;
use common\models\MonitorKeyword;
use common\models\MonitorKeywordResult;
use common\models\MonitorKeywordResultC;

/**
 * 监控查排名业务类
 * Class GetRandService
 * @package common\service
 */
class MonitorRankService extends BaseService{

    /**
     * @var MonitorKeywordResult|MonitorKeywordResultC
     */
    private $resultModel;

    private $pcResultArr = [];
    private $appResultArr = [];


    /**
     * 排名监控-手动更新
     * @param MonitorKeyword $mKeyword 监控关键词
     * @return array|bool
     * @throws
     */
    public function manualUpdate(MonitorKeyword $mKeyword){
        $mGoods = MonitorGoods::findOne(['deleted' => 0, 'status' => 1, 'id' => $mKeyword->monitor_goods_id]);
        if (empty($mGoods)){
            $this->errors = '未找到监控商品！';
            return false;
        }

        if ($mKeyword->is_category){
            // 更新类目
            if (!$this->rankCategory($mKeyword, $mGoods, true)){
                return false;
            }
            $keywordShow = $mKeyword->category ."[类目]";
        }else{
            // 更新关键词
            if (!$this->rankKeyword($mKeyword, $mGoods, true)){
                return false;
            }
            $keywordShow = $mKeyword->keyword;
        }

        $resultC = $this->resultModel;
        return [
            'keyword_id' => $mKeyword->id,
            'keyword' => $keywordShow,
            'sku' => $mGoods->sku,
            'good_pic' => $mGoods->good_pic,

            'page_pc' => $resultC->page_pc,
            'page_position_pc' => $resultC->page_position_pc,
            'page_order_pc' => $resultC->page_order_pc,
            'rank_change_pc' => $resultC->rank_change_pc,

            'page_app' => $resultC->page_app,
            'page_position_app' => $resultC->page_position_app,
            'page_order_app' => $resultC->page_order_app,
            'rank_change_app' => $resultC->rank_change_app,

            'last_update_at' => date('Y-m-d', $resultC->create_at)
        ];
    }


    /**
     * 查询监控关键词排名信息
     * @param MonitorKeyword $mKeyword
     * @param MonitorGoods $mGoods
     * @param boolean $isUpdate 是否为更新数据 true手动更新 false添加监控
     * @param boolean $saveAnyhow 无论如何都保存结果（即使查询结果都为0） 默认false否
     * @return bool
     * @throws
     */
    public function rankKeyword(MonitorKeyword $mKeyword, MonitorGoods $mGoods, $isUpdate = false, $saveAnyhow = false){
        $userInfo = User::findOne($mKeyword->user_id);

        $sku_list = Json::decode($mGoods->sku_list);
        if (empty($sku_list)){
            $goods = BaseGoods::findOne(['deleted' => 0, 'sku' => $mKeyword->sku]);
            if ($goods && ($this->nowTime - $goods->update_at) < 86400 * 3){
                $sku_list = Json::decode($goods->sku_list);
            }else{
                $good_result = BaseGoods::goodsSave($mKeyword->sku);
                if ($good_result['result'] == 1){
                    $sku_list = $good_result['sku_list'];
                }
            }
        }

        // 获取pc端和app端接口查询所需参数
        $rankForm = new RankForm($userInfo);
        $rankForm->keyword = $mKeyword->keyword;
        $rankForm->sku = $mKeyword->sku;
        $rankForm->skuList = $sku_list;
        $rankForm->searchId = $mKeyword->id;

        // 查询
        $appRank = $this->rankApp($rankForm);
        $pcRank = $this->rankPc($rankForm);

        if (!$pcRank && !$appRank && !$saveAnyhow){
            return false;
        }
        if (empty($this->pcResultArr) && empty($this->appResultArr) && !$saveAnyhow){
            $this->errors = '未查询到排名信息！';
            return false;
        }

        // 保存结果
        if (!$this->saveResult($mKeyword, $mGoods->search_type, $isUpdate)){
            return false;
        }

        $resultModel = $this->resultModel;
        if (empty($resultModel->page_order_pc) && empty($resultModel->page_order_app) && !$saveAnyhow){
            $this->errors = '未查询到排名结果！';
            return false;
        }

        $trans = Yii::$app->db->beginTransaction();
        try{
            if (!$resultModel->save()){
                throw new Exception('系统繁忙！');
            }

            if (!empty($resultModel->page_order_pc)){
                $mKeyword->is_update_pc = 1;
            }
            if (!empty($resultModel->page_order_app)){
                $mKeyword->is_update_app = 1;
            }
            if (!$mKeyword->save()){
                throw new Exception('监控关键词信息保存失败！');
            }
            $trans->commit();
        }catch (\Exception $e){
            $trans->rollBack();
            $this->errors = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 查询类目监控排名信息
     * @param MonitorKeyword $mKeyword
     * @param MonitorGoods $mGoods
     * @param boolean $isUpdate 是否为更新数据 true手动更新 false添加监控
     * @return bool
     * @throws
     */
    public function rankCategory(MonitorKeyword $mKeyword, MonitorGoods $mGoods, $isUpdate = false){
        if (empty($mGoods->cat)){
            $this->errors = '相关类目查找失败';
            return false;
        }

        // 查询类目排名
        $params = [
            'sku' => $mGoods->sku,
            'cat' => $mGoods->cat
        ];
        $url = Yii::$app->params['go-jdcha-api'] . 'v1/cat';
        $curlResult = Helper::curlPost($url, $params);

        $resultResult = $curlResult['result'][0] ?? [];
        $resultCode = $curlResult['code'] ?? 0;
        if ($resultCode != 1){
            $this->errors = '结果不在查询范围内';
            return false;
        }

        $trans = $this->beginTransaction();
        try{
            $mKeyword->is_update_app = 1;
            $mKeyword->is_update_pc = 1;
            if (!$mKeyword->save()){
                throw new Exception('类目监控添加失败');
            }

            if ($isUpdate){
                $kResult = new MonitorKeywordResultC();
            }else{
                $kResult = new MonitorKeywordResult();
            }
            $kResult->keyword_id       = $mKeyword->id;
            $kResult->user_id          = $mKeyword->user_id;
            $kResult->good_id          = $mKeyword->monitor_goods_id;
            $kResult->sku              = $mKeyword->sku;

            $kResult->page_pc          = intval($resultResult['result_page'] ?? 0);
            $kResult->page_position_pc = intval($resultResult['result_page_order'] ?? 0);
            $kResult->page_order_pc    = intval($resultResult['result_order'] ?? 0);

            $kResult->page_app         = 0;
            $kResult->page_position_app= 0;
            $kResult->page_order_app   = 0;

            $kResult->weight           = intval($resultResult['result_weight_score'] ?? 0);
            $kResult->title_weight     = intval($resultResult['result_title_weight_score'] ?? 0);

            $this->resultModel = $kResult;
            if ($isUpdate){
                $this->calRankChange($mKeyword);
            }

            if (!$this->resultModel->save()){
                throw new Exception('未查询到结果');
            }
            $trans->commit();
        }catch (\Exception $e){
            $trans->rollBack();
            $this->errors = $e->getMessage();
            return false;
        }
        return true;
    }


    /**
     * @param RankForm $rankForm
     * @return bool
     */
    private function rankPc(RankForm $rankForm){
        static $pcCount = 1;
        $url = Yii::$app->params['rank_pc'] .'v1/order';
        $params = $rankForm->getPcParams();
        if (!$params){
            $params = $rankForm->getPcParams();
            if (!$params){
                $this->errors = $rankForm->errors;
                return false;
            }
        }
        //todo.. pc端排名暂时不查(基本失败,优化后重新开启)

//        $curlResult = Helper::curlPost($url, $params);
        // 结果处理
//        $curlCode = $curlResult['code'] ?? '';
//        $curlResultArr = $curlResult['result'] ?? [];
//        $firstRowCode = $curlResult['result'][0]['code'] ?? 0;
        $curlCode = 0;
        $curlResultArr = [];
        $firstRowCode = 0;

        if ($curlCode == 0 || empty($curlResultArr) || $firstRowCode != 1){
            if ($pcCount < 2){
                $pcCount++;
                return $this->rankPc($rankForm);
            }
            $this->errors = '未查询到电脑端排名';
            return false;
        }else{
            $this->pcResultArr = $curlResultArr;
        }
        return true;
    }

    /**
     * @param RankForm $rankForm
     * @return bool
     */
    private function rankApp(RankForm $rankForm){
        static $appCount = 0;
        $url = Yii::$app->params['go_app_ranking'];
        $params = $rankForm->getAppParams();
        if (!$params){
            $params = $rankForm->getAppParams();
            if (!$params){
                $this->errors = $rankForm->errors;
                return false;
            }
        }
        $curlResult = Helper::curlPost($url, $params,false);

        $resultSuccess = $curlResult['success'] ?? 101;
        $curlResultArr = $curlResult['result'] ?? [];
        $curlResultArr = array_filter($curlResultArr);      // 去除数组空元素
        $firstRankData = $curlResultArr[0]['page_app'] ?? 0;

        // 结果处理
        if ($resultSuccess == 101 || empty($curlResultArr) || empty($firstRankData)){
            if ($appCount < 2){
                $appCount++;
                return $this->rankApp($rankForm);
            }
            $this->errors = '未查询到移动端排名';
            return false;
        }else{
            $this->appResultArr = $curlResultArr;
        }
        return true;
    }

    /**
     * @param MonitorKeyword $mKeyword
     * @param $search_type
     * @param $isUpdate
     * @return bool
     */
    private function saveResult(MonitorKeyword $mKeyword, $search_type, $isUpdate){
        $pcResultArr = $this->pcResultArr;
        $appResultArr = $this->appResultArr;
        $pageOrderFlag = $pcResultArr[0]['result_order'] ?? 0;
        $sku = $mKeyword->sku;

        // 匹配结果
        $pcMatch = $appMatch = [];
        foreach($pcResultArr as $pcResult){
            if ($search_type == 1){     // 当前sku
                if ($pcResult['sku'] == $mKeyword->sku){
                    $pcMatch = $pcResult;
                    break;
                }
            }else{                     // 最高sku
                if ($pcResult['result_order'] <= $pageOrderFlag){
                    $sku = $pcResult['sku'];
                    $pageOrderFlag = $pcResult['result_order'];
                    $pcMatch = $pcResult;
                }
            }
        }

        $pageOrderFlag = $appResultArr[0]['page_order_app'] ?? 0;
        foreach($appResultArr as $appResult){
            if ($search_type == 1){     // 当前sku
                if ($appResult['sku'] == $mKeyword->sku){
                    $appMatch = $appResult;
                    break;
                }
            }else{                     // 最高sku
                if ($appResult['page_order_app'] <= $pageOrderFlag){
                    $pageOrderFlag = $appResult['page_order_app'];
                    $appMatch = $appResult;
                }
            }
        }

        // 记录查询结果
        if ($isUpdate){
            $model = new MonitorKeywordResultC();
        }else{
            $model = new MonitorKeywordResult();
        }
        $model->user_id = $mKeyword->user_id;
        $model->keyword_id = $mKeyword->id;
        $model->sku = $sku;
        $model->good_id = $mKeyword->monitor_goods_id;

        $model->page_pc = intval($pcMatch['result_page'] ?? 0);
        $model->page_order_pc = intval($pcMatch['result_order'] ?? 0);
        $model->page_position_pc = intval($pcMatch['result_page_order'] ?? 0);
        $model->pc_search_at = time();

        $model->page_app = intval($appMatch['page_app'] ?? 0);
        $model->page_order_app = intval($appMatch['page_order_app'] ?? 0);
        $model->page_position_app = intval($appMatch['page_position_app'] ?? 0);
        $model->weight = intval($appMatch['result_weight_score'] ?? 0);
        $model->app_search_at = time();
        $this->resultModel = $model;

        // 计算排名变化数据
        if ($isUpdate){
            $this->calRankChange($mKeyword);
        }
        return true;
    }

    /**
     * 计算排名变化数据
     * @param MonitorKeyword $mKeyword
     */
    private function calRankChange(MonitorKeyword $mKeyword){
        $resultModel = $this->resultModel;
        $nowShow = MonitorKeywordResult::find()
            ->where([
                'user_id' => $mKeyword->user_id,
                'good_id' => $mKeyword->monitor_goods_id,
                'keyword_id' => $mKeyword->id,
                'sku' => $mKeyword->sku,
                'deleted' => 0
            ])
            ->orderBy('id desc')
            ->one();
        /* @var $nowShow MonitorKeywordResult*/
        $rank_change_pc = $rank_change_app = $weight_change = 0;

        if (!empty($nowShow)){
            if (!empty($nowShow->page_order_pc)){
                $rank_change_pc = intval($nowShow->page_order_pc) - intval($resultModel->page_order_pc);
            }

            if (!empty($nowShow->page_order_app)){
                $rank_change_app = intval($nowShow->page_order_app) - intval($resultModel->page_order_app);
                $weight_change = intval($resultModel->weight) - intval($nowShow->weight);
            }
        }

        $resultModel->rank_change_pc = $rank_change_pc;
        $resultModel->rank_change_app = $rank_change_app;
        $resultModel->weight_change = $weight_change;
    }

}