<?php
/**
 * Created by PhpStorm.
 * User: liuyaping
 * Date: 2018/2/6
 * Time: 下午5:09
 */

namespace console\controllers;

use common\Helper\Helper;
use common\models\form\RankForm;
use common\models\MonitorGoods;
use common\models\MonitorKeyword;
use common\models\MonitorKeywordResult;
use common\models\MonitorKeywordResultD;
use common\models\MonitorKeywordResultR;
use common\models\User;
use common\models\RandomList;
use common\service\MonitorRankService;
use linslin\yii2\curl\Curl;
use yii\console\Controller;
use yii\helpers\Json;
use Yii;
use yii\base\Exception;
use yii\redis\Connection;

/**
 * 排名监控定时更新脚本
 * Class MonitorClockController
 * @package console\controllers
 */
class MonitorClockController extends Controller
{

    /**
     * @var int 开启子进程数
     */
    private $forkNum = 4;

    /**
     * 每日待更新关键词,Redis入列
     */
    public function actionKeywordRedisIn(){
        $startTime = time();
        echo "待更新关键词Redis入库，开始执行---".date('Y-m-d H:i:s', $startTime)."\n";
        $redis = Yii::$app->redis;
        /* @var $redis Connection*/
        $redis->unlink('pc_success_ids', 'app_success_ids');

        $redis->select(1);
        $redis->move('pc_success_ids', 0);
        $redis->move('app_success_ids', 0);
        if ($redis->flushdb('async')){
            echo "Redis，DB1数据已清空\n";
        }

        $redis->select(0);
        $redis->move('pc_success_ids', 1);
        $redis->move('app_success_ids', 1);

        $redis->select(1);
        $this->getKeywordList($redis);
        $this->getKeywordList($redis, false);
        $redis->unlink('pc_success_ids', 'app_success_ids');

        $useTime = Helper::formatTime(time() - $startTime);
        $pcTotal = $redis->get('pc_keyword_total');
        $appTotal = $redis->get('app_keyword_total');
        echo "入库完成，\n
             用时【{$useTime}】，\n
             pc总数【{$pcTotal}】，\n
             app总数【{$appTotal}】\n";
    }

    /**
     * 查询入列
     * @param Connection $redis
     * @param bool $isApp
     */
    private function getKeywordList(Connection $redis, $isApp = true){
        $type = $isApp ? 'app' : 'pc';
        $todayTime = strtotime(date('Y-m-d'));
        $condition = [
            'a.user_id' => 11904, //User::getActiveUserIds()
            'a.is_category' => 0,
            'a.is_update_'.$type => 0,
            'a.monitor_close' => 0,
            'a.deleted' => 0,
            'g.status' => 1,
            'g.deleted' => 0
        ];
        $keywordQuery = MonitorKeyword::find()
            ->alias('a')
            ->select('a.id')
            ->indexBy('id')
            ->joinWith('good g')
            ->where($condition)
            ->andWhere(['<', 'a.create_at', $todayTime])
            ->orderBy(['a.id' => $isApp ? 'desc' : 'asc']);

        $total = (clone $keywordQuery)->count('a.id');

        $redis->set($type . "_keyword_total", $total);

        // 将全部关键词添加到set集合里
        $idAll = $type."_ids_all";
        foreach ($keywordQuery->each() as $keyword_id => $each){
            $redis->sadd($idAll, $keyword_id);
        }

        // 求交集：最多查询2次的关键词
        $redis->sinterstore($type . '_ids_twice', $idAll, $type . '_success_ids');

        // 求差集：最多查询1次的关键词
        $redis->sdiffstore($type . '_ids_once', $idAll, $type . '_success_ids');
    }

/*************************************APP端关键词排名每日更新脚本-start***********************************/

    /**
     * 1、脚本入口
     */
    public function actionDailyUpdateApp(){
        $this->startUpdate();
    }

    /**
     * 2、多线程执行关键词查询
     */
    public function actionForkUpdateApp()
    {
        $keyword_id = $_SERVER['argv'][2] ?? '';
        if (!$keyword_id){
            return;
        }
        $flag = $_SERVER['argv'][3] ?? '';
        if (empty($flag)){
            echo "【keyword_id-{$keyword_id}】flag参数丢失\n";
            return;
        }

        $mKeyword = MonitorKeyword::findOne([
                'id' => $keyword_id,
                'is_category' => 0,
                'is_update_app' => 0,
                'monitor_close' => 0,
                'deleted' => 0
            ]);
        if (!$mKeyword){
            echo "【keyword_id-{$keyword_id}】已更新，跳过更新\n";
            return;
        }
        $sku = $mKeyword->sku;
        $mGoods = MonitorGoods::findOne([
            'id' => $mKeyword->monitor_goods_id,
            'status' => 1,
            'deleted' => 0
        ]);
        if (!$mGoods){
            echo "【keyword_id-{$keyword_id}-{$sku}】监控商品状态错误\n";
            return;
        }

        $todayTime = strtotime(date('Y-m-d', time()));
        $model = MonitorKeywordResult::find()
            ->where(['deleted' => 0, 'keyword_id' => $mKeyword->id])
            ->andWhere(['>', 'create_at', $todayTime])
            ->one();
        /* @var $model MonitorKeywordResult*/
        if (!empty($model)){
            if ($model->page_order_app > 0){
                echo "【keyword_id-{$keyword_id}-{$sku}】APP端排名已更新\n";
                return;
            }
        }else{
            $model = new MonitorKeywordResult();
            $model->keyword_id = $mKeyword->id;
            $model->user_id = $mKeyword->user_id;
            $model->good_id = $mKeyword->monitor_goods_id;
            $model->sku = $mKeyword->sku;
        }

        $redis = Yii::$app->redis;
        $redis->select(1);
        /* @var $redis Connection*/
        try{
            $appMatch = $this->rankAppDaily($redis, $mKeyword, $mGoods, $flag);

            $this->appSaveResult($redis, $model, $mKeyword, $appMatch);
        }catch (Exception $e){
            echo date('H:i:s') . "【keyword_id-{$keyword_id}-{$sku}】" . $e->getMessage() . "\n";
            return;
        }
        return;
    }

    /**
     * 3、查询结果
     * @param Connection $redis
     * @param MonitorKeyword $mKeyword
     * @param MonitorGoods $mGoods
     * @param string $flag
     * @return array|mixed
     * @throws Exception
     */
    private function rankAppDaily(Connection $redis, MonitorKeyword $mKeyword, MonitorGoods $mGoods, $flag){
        $userInfo = User::findOne($mKeyword->user_id);
        $sku_list = Json::decode($mGoods->sku_list);
        $search_type = $mGoods->search_type;

        // 获取pc端和app端接口查询所需参数
        $rankForm = new RankForm($userInfo);
        $rankForm->keyword = $mKeyword->keyword;
        $rankForm->sku = $mKeyword->sku;
        $rankForm->skuList = $sku_list;
        $rankForm->searchId = $mKeyword->id;
        $rankResult = $this->curlForAppRank($rankForm, $redis);

        $appMatch = [];
        $pageOrderFlag = $rankResult[0]['page_order_app'] ?? 0;
        foreach($rankResult as $appResult){
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

        if (empty($appMatch)){
            switch ($flag){
                case "twice":
                    $redis->sadd('app_redo_ids', $mKeyword->id);
                    throw new Exception('高频关键词。第一次查询未查询到结果，等待第二次查询');
                    break;
                case "once":
                    echo date('H:i:s') . "【keyword_id-{$mKeyword->id}-{$mKeyword->sku}】低频关键词未查询到结果。跳过该关键词，并标记为已更新\n";
                    break;
                case "redo":
                    echo date('H:i:s') . "【keyword_id-{$mKeyword->id}-{$mKeyword->sku}】高频关键词。第二次查询未查询到结果。跳过该关键词，并标记为已更新\n";
                    break;
            }
        }else{
            // 第一次查询就查询成功的标记为“高频关键词”
            if (in_array($flag, ['twice', 'once'])){
                $redis->sadd('app_success_ids', $mKeyword->id);
            }
        }
        return $appMatch;
    }

    /**
     * 3.1、curl执行查询
     * @param RankForm $rankForm
     * @param Connection $redis
     * @return array
     * @throws
     */
    private function curlForAppRank(RankForm $rankForm, Connection $redis){
        $url = Yii::$app->params['go_app_ranking'];
        $params = $rankForm->getAppParams();
        if (!$params){
            echo date('H:i:s')."【keyword_id-{$rankForm->searchId}-{$rankForm->sku}】".$rankForm->errors;
            sleep(5);
            $params = $rankForm->getAppParams();
            if (!$params){
                throw new Exception("第二次获取参数错误：".$rankForm->errors);
            }
        }
        $curlResult = Helper::curlPost($url, $params,false);
        $redis->incr('app_query_times');

        // 结果处理
        $curlResultArr = $curlResult['result'] ?? [];
        $firstRankData = $curlResult['result'][0]['page_app'] ?? 0;

        if ($curlResult['success'] != 200 || empty($curlResultArr) || empty($firstRankData)){
            $curlResultArr = [];
        }
        return $curlResultArr;
    }

    /**
     * 4、保存结果
     * @param Connection $redis
     * @param MonitorKeywordResult $model
     * @param MonitorKeyword $mKeyword
     * @param array $appMatch
     * @throws Exception
     */
    private function appSaveResult(Connection $redis, MonitorKeywordResult $model, MonitorKeyword $mKeyword, $appMatch){
        $trans = Yii::$app->db->beginTransaction();
        try{
            $todayTime = strtotime(date('Y-m-d', time()));

            $model->page_app = intval($appMatch['page_app'] ?? 0);
            $model->page_order_app = intval($appMatch['page_order_app'] ?? 0);
            $model->page_position_app = intval($appMatch['page_position_app'] ?? 0);
            $model->weight = intval($appMatch['result_weight_score'] ?? 0);
            $model->app_search_at = time();

            if ($model->page_order_app > 0){
                $redis->incr('app_query_success');
            }
            $mKeyword->is_update_app = 1;
            if (!$mKeyword->save()){
                throw new Exception($mKeyword->getError());
            }

            // 计算排名变化
            $nowShow = MonitorKeywordResult::find()
                ->where([
                    'user_id' => $mKeyword->user_id,
                    'good_id' => $mKeyword->monitor_goods_id,
                    'keyword_id' => $mKeyword->id,
                    'sku' => $mKeyword->sku,
                    'deleted' => 0
                ])
                ->andWhere(['<', 'create_at', $todayTime])
                ->orderBy('id desc')
                ->one();
            /* @var $nowShow MonitorKeywordResult*/
            $rank_change_app = $weight_change = 0;
            if (!empty($nowShow) &&
                !empty($appMatch) &&
                !empty($nowShow->page_order_app)){
                $rank_change_app = intval($nowShow->page_order_app) - intval($model->page_order_app);
                $weight_change = intval($model->weight) - intval($nowShow->weight);
            }
            $model->rank_change_app = $rank_change_app;
            $model->weight_change = $weight_change;
            if (!$model->save()){
                throw new Exception($model->getError());
            }
            $redis->incr('app_save_success');
            $trans->commit();
        }catch (Exception $e){
            $trans->rollBack();
            throw new Exception("-appSaveResult-".$e->getMessage());
        }
    }


/*************************************APP端关键词排名每日更新脚本-end***********************************/


    /**
     * 每日监控排名更新入口
     * @param bool $isAppRank
     */
    private function startUpdate($isAppRank = true){
        ini_set('default_socket_timeout', -1);
        ini_set('memory_limit', '512M');
        $type = $isAppRank ? 'app' : 'pc';
        $nowTime = time();
        $redis = Yii::$app->redis;
        /* @var $redis Connection*/
        $redis->select(1);
        $total = $redis->get($type . "_keyword_total");
        $twiceNum = $redis->scard($type . '_ids_twice');
        $onceNum = $redis->scard($type . '_ids_once');

        echo "共【{$total}个】关键词。高频关键词【{$twiceNum}个】，低频关键词【{$onceNum}个】\n";
        echo $type.'开始更新 --- '. date('Y-m-d H:m:s', $nowTime) ."\n";

        $forkNum = $this->forkNum;
        $pidArr = [];
        for ($fork_id = 1; $fork_id <= $forkNum; $fork_id++){
            $pid = pcntl_fork();
            if ($pid == -1) { // 进程创建失败
                die('fork child process failure!');
            } elseif ($pid) { // 父进程处理逻辑
                $pidArr[] = $pid;
            } else {        // 子进程处理逻辑
                /**
                 * 1、对“高频关键词”做第一次查询
                 * 查询成功：保存到query_success_ids标记为“高频关键词”
                 * 查询失败：保存到redo再查一次
                 */
                echo date('H:i:s') . "--------【fork_".$fork_id."】twice开始执行\n";
                $redisKey = $type . "_ids_twice";
                while (1){
                    $keyword_id = $redis->spop($redisKey);
                    if (!$keyword_id || $keyword_id == 'nil'){
                        break;
                    }
                    system('cd /data/wwwroot/jdcha && php ./yii monitor-clock/fork-update-' . $type . ' ' . $keyword_id . ' ' . 'twice');
                }
                echo date('H:i:s') . "--------【fork_".$fork_id."】twice执行结束\n";
                unset($redisKey);

                /**
                 * 2、对“低频关键词”做第一次查询。且无论成功、失败，只查询这一次
                 * 对查询成功的关键词标记为“高频关键词”
                 */
                echo date('H:i:s') . "--------【fork_".$fork_id."】once开始执行\n";
                $redisKey = $type . "_ids_once";
                while (1){
                    $keyword_id = $redis->spop($redisKey);
                    if (!$keyword_id || $keyword_id == 'nil'){
                        break;
                    }
                    system('cd /data/wwwroot/jdcha && php ./yii monitor-clock/fork-update-' . $type . ' ' . $keyword_id . ' ' . 'once');
                }
                echo date('H:i:s') . "--------【fork_".$fork_id."】once执行结束\n";
                unset($redisKey);

                /**
                 * 3、对第一次查询失败的“高频关键词”做第二次查询
                 * 无论查询成功与否，都不做任何标记
                 */
                echo date('H:i:s') . "--------【fork_".$fork_id."】redo开始执行\n";
                $redisKey = $type . "_redo_ids";
                while (1){
                    $keyword_id = $redis->spop($redisKey);
                    if (!$keyword_id || $keyword_id == 'nil'){
                        break;
                    }
                    system('cd /data/wwwroot/jdcha && php ./yii monitor-clock/fork-update-' . $type . ' ' . $keyword_id . ' ' . 'redo');
                }

                echo date('H:i:s') . "--------【fork_".$fork_id."】执行完毕\n";
                unset($popArr, $redisKey, $keyword_id);
                exit(0);
            }
        }

        foreach ($pidArr as $p_id) {
            pcntl_waitpid($p_id, $status);
        }

        echo "更新完成！" . date('Y-m-d H:i:s') . "\n";

        // 查询结果输出
        $useTime = Helper::formatTime(time() - $nowTime);
        $queryTimes = $redis->get($type.'_query_times');
        $querySuccess = $redis->get($type.'_query_success');
        $saveSuccess = $redis->get($type.'_save_success');
        $successPercent = $total ? round($querySuccess / $total * 100, 2) : 0;

        echo "用时【{$useTime}】，\n
        本次更新关键词总数【{$total}条】\n
        执行查询【{$queryTimes}次】，查询成功【{$querySuccess}次】，\n
        结果保存成功【{$saveSuccess}条】，\n
        查询成功率【{$successPercent}%】\n";
    }


/*************************************PC端关键词排名每日更新脚本-start***********************************/

    /**
     * 1、脚本入口
     */
    public function actionDailyUpdatePc(){
        $this->startUpdate(false);
    }

    /**
     * 2、多线程执行单个关键词查询
     */
    public function actionForkUpdatePc()
    {
        $keyword_id = $_SERVER['argv'][2] ? $_SERVER['argv'][2] : "";
        if (!$keyword_id){
            return;
        }
        $flag = $_SERVER['argv'][3] ?? '';
        if (empty($flag)){
            echo "【keyword_id-{$keyword_id}】flag参数丢失\n";
            return;
        }

        $mKeyword = MonitorKeyword::findOne([
                'id' => $keyword_id,
                'is_update_pc' => 0,
                'is_category' => 0,
                'monitor_close' => 0,
                'deleted' => 0
            ]);
        $sku = $mKeyword->sku;
        if (!$mKeyword){
            echo "【keyword_id-{$keyword_id}-{$sku}】已更新，跳过更新\n";
            return;
        }
        $mGoods = MonitorGoods::findOne([
            'id' => $mKeyword->monitor_goods_id,
            'status' => 1,
            'deleted' => 0
        ]);
        if (!$mGoods){
            echo "【keyword_id-{$keyword_id}-{$sku}】监控商品状态错误\n";
            return;
        }

        $todayTime = strtotime(date('Y-m-d', time()));
        $model = MonitorKeywordResult::find()
            ->where(['deleted' => 0, 'keyword_id' => $mKeyword->id])
            ->andWhere(['>', 'create_at', $todayTime])
            ->one();
        /* @var $model MonitorKeywordResult*/
        if (!empty($model)){
            if ($model->page_order_pc > 0){
                echo "【keyword_id-{$keyword_id}-{$sku}】PC端排名已更新\n";
                return;
            }
        }else{
            $model = new MonitorKeywordResult();
            $model->keyword_id = $mKeyword->id;
            $model->user_id = $mKeyword->user_id;
            $model->good_id = $mKeyword->monitor_goods_id;
            $model->sku = $mKeyword->sku;
        }

        $redis = Yii::$app->redis;
        $redis->select(1);
        /* @var $redis Connection*/
        try{
            $pcMatch = $this->rankPcDaily($redis, $mKeyword, $mGoods, $flag);

            $this->pcSaveResult($redis, $model, $mKeyword, $pcMatch);
        }catch (Exception $e){
            echo date('H:i:s') . "【keyword_id-{$keyword_id}-{$sku}】" . $e->getMessage() . "\n";
            return;
        }
        return;
    }

    /**
     * 3、查询结果
     * @param Connection $redis
     * @param MonitorKeyword $mKeyword
     * @param MonitorGoods $mGoods
     * @param string $flag
     * @return array|mixed
     * @throws Exception
     */
    private function rankPcDaily(Connection $redis, MonitorKeyword $mKeyword, MonitorGoods $mGoods, $flag){
        $userInfo = User::findOne($mKeyword->user_id);
        $sku_list = Json::decode($mGoods->sku_list);
        $search_type = $mGoods->search_type;

        // 获取pc端和app端接口查询所需参数
        $rankForm = new RankForm($userInfo);
        $rankForm->keyword = $mKeyword->keyword;
        $rankForm->sku = $mKeyword->sku;
        $rankForm->skuList = $sku_list;
        $rankForm->searchId = $mKeyword->id;
        $rankResult = $this->curlForPcDaily($rankForm, $redis);

        $pcMatch = [];
        $pageOrderFlag = $rankResult[0]['result_order'] ?? 0;
        foreach($rankResult as $pcResult){
            if ($search_type == 1){     // 当前sku
                if ($pcResult['sku'] == $mKeyword->sku){
                    $pcMatch = $pcResult;
                    break;
                }
            }else{                     // 最高sku
                if ($pcResult['result_order'] <= $pageOrderFlag){
                    $pageOrderFlag = $pcResult['result_order'];
                    $pcMatch = $pcResult;
                }
            }
        }

        if (empty($pcMatch)){
            switch ($flag){
                case "twice":
                    $redis->sadd('pc_redo_ids', $mKeyword->id);
                    throw new Exception('高频关键词。第一次查询未查询到结果，等待第二次查询');
                    break;
                case "once":
                    echo date('H:i:s') . "【keyword_id-{$mKeyword->id}-{$mKeyword->sku}】低频关键词未查询到结果。跳过该关键词，并标记为已更新\n";
                    break;
                case "redo":
                    echo date('H:i:s') . "【keyword_id-{$mKeyword->id}-{$mKeyword->sku}】高频关键词。第二次查询未查询到结果。跳过该关键词，并标记为已更新\n";
                    break;
            }
        }else{
            // 第一次查询就查询成功的标记为“高频关键词”
            if (in_array($flag, ['twice', 'once'])){
                $redis->sadd('pc_success_ids', $mKeyword->id);
            }
        }
        return $pcMatch;
    }

    /**
     * 3.1、curl执行查询
     * @param RankForm $rankForm
     * @param Connection $redis
     * @return array|bool
     * @throws
     */
    private function curlForPcDaily(RankForm $rankForm, Connection $redis){
        $url = Yii::$app->params['rank_pc'] .'v1/order';
        $params = $rankForm->getPcParams();
        if (!$params){
            echo date('H:i:s') . "【keyword_id-{$rankForm->searchId}-{$rankForm->sku}】".$rankForm->errors;
            sleep(5);
            $params = $rankForm->getPcParams();
            if (!$params){
                throw new Exception("第二次获取参数错误：".$rankForm->errors);
            }
        }
        $curlResult = Helper::curlPost($url, $params);
        $redis->incr('pc_query_times');

        // 结果处理
        $curlCode = $curlResult['code'] ?? '';
        $curlResultArr = $curlResult['result'] ?? [];
        $firstRowCode = $curlResult['result'][0]['code'] ?? 0;

        if ($curlCode == 0 || empty($curlResultArr) || $firstRowCode != 1){
            $curlResultArr = [];
        }
        return $curlResultArr;
    }

    /**
     * 4、保存结果
     * @param Connection $redis
     * @param MonitorKeywordResult $model
     * @param MonitorKeyword $mKeyword
     * @param $pcMatch
     * @throws
     */
    private function pcSaveResult(Connection $redis, MonitorKeywordResult $model, MonitorKeyword $mKeyword, $pcMatch){
        $trans = Yii::$app->db->beginTransaction();
        try{
            $todayTime = strtotime(date('Y-m-d', time()));

            $model->page_pc = intval($pcMatch['result_page'] ?? 0);
            $model->page_order_pc = intval($pcMatch['result_order'] ?? 0);
            $model->page_position_pc = intval($pcMatch['result_page_order'] ?? 0);
            $model->pc_search_at = time();
            if (!empty($model->page_order_pc)){
                $redis->incr('pc_query_success');
            }

            $mKeyword->is_update_pc = 1;
            if (!$mKeyword->save()){
                throw new Exception("关键词保存出错：".$mKeyword->getError());
            }

            // 计算排名变化
            $nowShow = MonitorKeywordResult::find()
                ->where([
                    'user_id' => $mKeyword->user_id,
                    'good_id' => $mKeyword->monitor_goods_id,
                    'keyword_id' => $mKeyword->id,
                    'sku' => $mKeyword->sku,
                    'deleted' => 0
                ])
                ->andWhere(['<', 'create_at', $todayTime])
                ->orderBy('id desc')
                ->one();
            /* @var $nowShow MonitorKeywordResult*/
            $rank_change_pc = 0;
            if (!empty($nowShow) && !empty($pcMatch) && !empty($nowShow->page_order_pc)){
                $rank_change_pc = intval($nowShow->page_order_pc) - intval($model->page_order_pc);
            }
            $model->rank_change_pc = $rank_change_pc;
            if (!$model->save()){
                throw new Exception("Result保存出错：".$model->getError());
            }
            $redis->incr('pc_save_success');
            $trans->commit();
        }catch (Exception $e){
            $trans->rollBack();
            throw new Exception("-pcSaveResult-".$e->getMessage());
        }
    }

/*************************************PC端关键词排名每日更新脚本-end***********************************/


    public function actionStatusUpdate()
    {
        // 评价监控零点更新查询状态
        RandomList::updateAll(['is_update'=>0],RandomList::find()->where(['is_update'=>1,'deleted'=>0])->where);
        echo date('Y-m-d H:i',time()) . "uuid更新状态成功\n";
    }
    public function actionAddRandom(){
        $url = 'http://120.132.7.47:8181/jingdong-server-1909005-0.0.1-SNAPSHOT/jdmohe/uuid';
        $parama['num'] = 10000;
        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT,90);
        $postString = $curl->setPostParams($parama)->post($url);
        $result = Json::decode($postString,true);
        if($result['code'] != 200){
            echo "接口调用失败" . date('Y-m-d H:i',time()) . "\n";
        }
        echo "接口调用开始===========" . date('Y-m-d H:i',time()) . "\n";
        foreach($result['data'] as $k => $y){
            $new = new RandomList();
            $new->random = $y;
            $new->save();
        }
        echo "接口调用完成===========" . date('Y-m-d H:i',time()) . "\n";
    }
    // 测试随机数
    public function actionRandom(){
        $index = 0;
        do{
            $count = RandomList::find()->where(['deleted'=>0])->count();
            if ($count==0){
                echo "更新完成\n";
                return;
            }
            $querys = RandomList::find()->where(['deleted'=>0])->limit(200)->orderBy('id desc')->all();
            \Yii::$app->db->close();
            $cmds = [];
            if (!$querys){
                echo "查询完毕" . date('Y-m-d H:i',time()) . "\n";
                return;
            }
            $i = 0;
            foreach ($querys as $query){
                $cmds[$i][] = $query->id;
                $i++;
                if ($i==1){
                    $i=0;
                }
            }
            foreach ($cmds as $cmd) {
                $pid = pcntl_fork();
                if ($pid == - 1) { // 进程创建失败
                    die('fork child process failure!');
                } elseif ($pid) { // 父进程处理逻辑
                    $procs[] = $pid;
                } else {
                    // 子进程处理逻辑
                    foreach ($cmd as $line) {
                        system('cd /data/wwwroot/jdcha && ' . 'php ' . './yii monitor-clock/aaaa ' . $line);
                        echo $line . " done!\n";
                    }
                    exit(0);
                }
            }
            foreach ($procs as $proc) {
                pcntl_waitpid($proc, $status);
            }
            unset($pid);
            $pid = NULL;
            unset($procs);
            $procs = NULL;
        }while($count>0);
    }

    public function actionAaaa(){
        $sku = '56204729464';
        $keyword = '罗技';
        $id = $_SERVER['argv'][2]?$_SERVER['argv'][2]:"";
        $models = RandomList::find()->where(['id'=>$id,'deleted'=>0])->all();
        foreach($models as $k => $y){

            $time = time();
            $url = Yii::$app->params['sku_collectForMohe'] . $sku . "&price=0&stock=0&comment=1";
            $curl = new Curl();
            $collectString = $curl->get($url);
            $time_one = time() - $time;
            if (strstr($collectString,"502 Bad Gateway")){
                return ['result'=>0,'msg'=>'采集商品信息超时,请稍后重新查询.'];
            }
            $data = Json::decode($collectString,false);
            if ($data->result != 1){
                return ['result'=>0,'msg'=>'商品链接或sku不正确.'];
            }
            $colors = $data->data->page_config->product->colorSize;
            $sku_list = [];
            foreach ($colors as $color){
//                    array_push($sku_list,(string)$color->skuId);
            }

            $parama = [
                'user_id'    => intval(20),
                'id'         => 1,
                'sku'        => $sku,
                'sku_list'   => $sku_list,
                'type'       => 1,
                'entrance_type'       => "",
                'client_type'=> intval(2),
                'model'      => intval(1),
                'keyword'    => $keyword,
                'page_start' => intval(1),
                'page_end'   => intval(50),
                'result_sort'=> intval(1),
                'price_min'  => 0,
                'price_max'  => 0,
                'random' => $y->random,
            ];

            //  调用接口
            $url = Yii::$app->params['app_rank_url'] .'v1/order';
            $curl = new Curl();
            $curl->setOption(CURLOPT_TIMEOUT,90);
            $postString = $curl->setRequestBody(Json::encode($parama))->post($url);
            $result = Json::decode($postString,true);



            if($result['result']){
                echo    "随机数ID有++++++++结果".$y->random . "\n";
                $result = $result['result'][0];
                echo    "随机数ID排名在--++---结果".$result['result_order'] . "\n";
                if($result['result_order'] > 250 && $result['result_order'] < 290){
                    $y->deleted = 2;
                    //  入库到远程数据库
//                    Yii::$app->jdrqds->createCommand("insert into random_list(random, create_at, update_at) values ('$y->random',$y->create_at,$y->update_at)")->execute();
                }else{
                    $y->deleted = 3;
                }
            }else{
                $y->deleted = 1;
                echo    "随机数ID没有-------结果". "\n";
            }

            if($y->save()){
                echo    "随机数ID成功-------结果" . "\n";
            }else{
                echo    "随机数ID失败-------结果".$y->getErrors() . "\n";
            }
        }
    }
    public function actionAaaa2(){
        $id = $_SERVER['argv'][2]?$_SERVER['argv'][2]:"";
        $models = MonitorGoods::find()->where(['id'=>$id,'deleted'=>0])->one();
        $is = MonitorKeyword::find()->where(['monitor_goods_id'=>$models->id])->one();
        $models->version =2 ;
        if(empty($is)){
            $models->deleted =1 ;
            $models->save();
            echo    $id ."没有". "\n";
        }else{
            $models->deleted =1 ;
            $models->save();
            echo    $id ."有". "\n";
        }
    }
    // 更新状态
    public function actionUpdateComparison(){
        Yii::$app->db->createCommand()->truncateTable('monitor_keyword_result_d')->execute();
        Yii::$app->db->createCommand("UPDATE `monitor_keyword_result_r` SET status=0 WHERE id>0")->execute();
    }

    public function upvip($level_id,$level_fate,$invite_fate){
        $tian = 0;
        $level_id_new = 0;
        switch ($level_id){
            case 4:
                if($level_fate >0){
                    $tian+=$level_fate;
                }
                if($invite_fate>0){
                    $tian+=$invite_fate;
                }
                $level_id_new = 4;
                break;
            case 3:
                if($level_fate >0){
                    $tian+=$level_fate;
                }
                if($invite_fate>0){
                    $tian+=$invite_fate;
                }
                $level_id_new = 3;
                break;
            case 2:
                if($level_fate >0){
                    $tian+=$level_fate;
                }
                if($invite_fate>0){
                    $tian+=$invite_fate;
                }
                if($tian>2){
                    $tian = round($tian/2,0);
                }else{
                    $tian = 0;
                }
                $level_id_new = 3;
                break;
            case 1:
                $tian = 7;
                $level_id_new = 3;
                break;
        }
        $data['tian'] = $tian;
        $data['level_id_new'] = $level_id_new;
        return $data;
    }

    //充值会员重新计算
    public function actionVip(){
        $index = 0;
        do{
            $count = User::find()->where(['deleted'=>0,'is_vip_count'=>0])->count();
            if ($count==0){
                echo "更新完成\n";
                return;
            }
            $models = User::find()->where(['deleted'=>0,'is_vip_count'=>0])
                ->limit(10)->all();

            foreach ($models as $model){
                $index = $model->id;

                $level_id = $model->level_id;
                $level_fate = $model->level_fate;

                $invite_level_id = $model->invite_level_id;
                $invite_fate = $model->invite_fate;
                //  等级对比
                //  充值大
                if($level_id > $invite_level_id){
                    if($level_fate>0 && in_array($level_id,[2,3,4])){
                        $data = self::upvip($level_id,$level_fate,$invite_fate);
                        $model->level_id = $data['level_id_new'];
                        $model->level_fate = $data['tian'];
                    }else if($invite_fate>0 && in_array($invite_level_id,[2,3,4])) {
                        $data = self::upvip($invite_level_id,$invite_fate,$level_fate);
                        $model->level_id = $data['level_id_new'];
                        $model->level_fate = $data['tian'];
                    }else{
                        if(empty($model->shop_name)){
                            $model->level_id = 6;
                            $model->level_fate = 0;
                        }else{
                            $model->level_id = 3;
                            $model->level_fate = 7;
                        }
                    }
                }else{
                    //  邀请大
                    if($invite_fate>0 && in_array($invite_level_id,[2,3,4])){
                        $data = self::upvip($invite_level_id,$invite_fate,$level_fate);
                        $model->level_id = $data['level_id_new'];
                        $model->level_fate = $data['tian'];
                    }else if($level_fate>0 && in_array($level_id,[2,3,4])) {
                        $data = self::upvip($level_id,$level_fate,$invite_fate);
                        $model->level_id = $data['level_id_new'];
                        $model->level_fate = $data['tian'];
                    }else{
                        if(empty($model->shop_name)){
                            $model->level_id = 6;
                            $model->level_fate = 0;
                        }else{
                            $model->level_id = 3;
                            $model->level_fate = 7;
                        }
                    }
                }
//                echo "更新" . $count .'个'.date('Y-m-d H:i:s',time()). "\n";

                $model->is_vip_count = 1;
                $model->invite_level_id = 0;
                $model->invite_fate = 0;
                if (!$model->save()){
                    echo "更新失败:" . $model->getError();
                    continue;
                }else{
                    echo "更新成功:" . $index;
                }
            }

        }while($count>0);
    }

    // 对比监控
    public function actionSearchComparison(){
        ini_set ('memory_limit', '512M');
        do{
            // 获取时间
            $count = MonitorKeywordResultR::find()->where(['deleted'=>0,'status'=>0])->count();
            if($count == 0){
                echo "更新完成\n";
                return;
            }

            $models = MonitorKeywordResultR::find()->where(['deleted'=>0,'status'=>0])->orderBy('id desc')->limit('1')->all();
            foreach($models as $k => $y){
                // 时间
//                $day = '-'.$y->day;
                $start7_at = strtotime(date('Y-m-d',strtotime("-7 days")));
                $end7_at = $start7_at + 86400;
                $start15_at = strtotime(date('Y-m-d',strtotime("-15 days")));
                $end15_at = $start15_at + 86400;

                $end_at = strtotime(date('Y-m-d',time()));
                //  昨天开始时间
                $zday_at = $end_at - 86400;
                do{
                    $count_1 = MonitorKeywordResult::find()->where(['BETWEEN','create_at',$zday_at,$end_at])->andWhere(['<','version',5])->count();
                    $MonitorKeywordResult = MonitorKeywordResult::find()->where(['BETWEEN','create_at',$zday_at,$end_at])->andWhere(['<','version',5])->limit('5000')->all();
                    foreach($MonitorKeywordResult as $k1 => $y1){
                        $new = new MonitorKeywordResultD();
                        //  判断7天是否有数据
                        $start7_app_count = MonitorKeywordResult::find()->where(['keyword_id'=>$y1->keyword_id,'good_id'=>$y1->good_id])->andwhere(['BETWEEN','create_at',$start7_at,$end_at])->andWhere(['>','page_order_app',0])->count();
                        if($start7_app_count >= 6){
                            $start7_at_list = MonitorKeywordResult::find()->where(['keyword_id'=>$y1->keyword_id,'good_id'=>$y1->good_id])->andwhere(['BETWEEN','create_at',$start7_at,$end7_at])->one();
                            if(!empty($start7_at_list->page_order_app)){
                                $rank_change_app = $start7_at_list?intval($start7_at_list->page_order_app) - intval($y1->page_order_app):0;
                                $new->rank_change_app_7 = $rank_change_app?$rank_change_app:0;
                            }else{
                                $start7_at_list = MonitorKeywordResult::find()->where(['keyword_id'=>$y1->keyword_id,'good_id'=>$y1->good_id])->andwhere(['BETWEEN','create_at',$start7_at-86400,$end7_at-86400])->one();
                                if(!empty($start7_at_list->page_order_app)){
                                    $rank_change_app = $start7_at_list?intval($start7_at_list->page_order_app) - intval($y1->page_order_app):0;
                                    $new->rank_change_app_7 = $rank_change_app?$rank_change_app:0;
                                }
                            }
                        }
                        $start7_pc_count = MonitorKeywordResult::find()->where(['keyword_id'=>$y1->keyword_id,'good_id'=>$y1->good_id])->andwhere(['BETWEEN','create_at',$start7_at,$end_at])->andWhere(['>','page_order_pc',0])->count();
                        if($start7_pc_count >= 6){
                                $start7_at_list = MonitorKeywordResult::find()->where(['keyword_id'=>$y1->keyword_id,'good_id'=>$y1->good_id])->andwhere(['BETWEEN','create_at',$start7_at,$end7_at])->one();
                                if(!empty($start7_at_list->page_order_pc)){
                                    $rank_change_app = $start7_at_list?intval($start7_at_list->page_order_pc) - intval($y1->page_order_pc):0;
                                    $new->rank_change_pc_7 = $rank_change_app?$rank_change_app:0;
                                }else{
                                    $start7_at_list = MonitorKeywordResult::find()->where(['keyword_id'=>$y1->keyword_id,'good_id'=>$y1->good_id])->andwhere(['BETWEEN','create_at',$start7_at-86400,$end7_at-86400])->one();
                                    if(!empty($start7_at_list->page_order_pc)){
                                        $rank_change_app = $start7_at_list?intval($start7_at_list->page_order_app) - intval($y1->page_order_app):0;
                                        $new->rank_change_pc_7 = $rank_change_app?$rank_change_app:0;
                                    }
                                }
                        }
                        if(empty($new->rank_change_pc_7) && empty($new->rank_change_app_7)){
                            $query = Yii::$app->db->createCommand("UPDATE `monitor_keyword_result` SET version=5 WHERE id=$y1->id")->execute();
                            continue;
                        }

                        //  判断15天是否有数据
                        $start15_app_count = MonitorKeywordResult::find()->where(['keyword_id'=>$y1->keyword_id,'good_id'=>$y1->good_id])->andwhere(['BETWEEN','create_at',$start15_at,$end_at])->andWhere(['>','page_order_app',0])->count();
                        if($start15_app_count >= 14){
                            $start15_at_list = MonitorKeywordResult::find()->where(['keyword_id'=>$y1->keyword_id,'good_id'=>$y1->good_id])->andwhere(['BETWEEN','create_at',$start15_at,$end15_at])->one();
                            if(!empty($start15_at_list->page_order_app)){
                                $rank_change_app = $start15_at_list?intval($start15_at_list->page_order_app) - intval($y1->page_order_app):0;
                                $new->rank_change_app_15 = $rank_change_app?$rank_change_app:0;
                            }else{
                                $start15_at_list = MonitorKeywordResult::find()->where(['keyword_id'=>$y1->keyword_id,'good_id'=>$y1->good_id])->andwhere(['BETWEEN','create_at',$start15_at-86400,$end15_at-86400])->one();
                                if(!empty($start15_at_list->page_order_app)){
                                    $rank_change_app = $start15_at_list?intval($start15_at_list->page_order_app) - intval($y1->page_order_app):0;
                                    $new->rank_change_app_15 = $rank_change_app?$rank_change_app:0;
                                }
                            }
                        }else{
                            $start15_at_list = MonitorKeywordResult::find()->where(['keyword_id'=>$y1->keyword_id,'good_id'=>$y1->good_id])->andwhere(['BETWEEN','create_at',$start15_at,$end15_at])->one();
                            $rank_change_app = $start15_at_list?intval($start15_at_list->page_order_app) - intval($y1->page_order_app):0;
                            $new->rank_change_app_15 = $rank_change_app?$rank_change_app:0;
                        }

                        $start15_pc_count = MonitorKeywordResult::find()->where(['keyword_id'=>$y1->keyword_id,'good_id'=>$y1->good_id])->andwhere(['BETWEEN','create_at',$start15_at,$end_at])->andWhere(['>','page_order_pc',0])->count();
                        if($start15_pc_count >= 14){
                            $start15_pc_count = MonitorKeywordResult::find()->where(['keyword_id'=>$y1->keyword_id,'good_id'=>$y1->good_id])->andwhere(['BETWEEN','create_at',$start15_at,$end15_at])->one();
                            if(!empty($start15_pc_count->page_order_pc)){
                                $rank_change_app = $start15_pc_count?intval($start15_pc_count->page_order_pc) - intval($y1->page_order_pc):0;
                                $new->rank_change_pc_15 = $rank_change_app?$rank_change_app:0;
                            }else{
                                $start15_pc_count = MonitorKeywordResult::find()->where(['keyword_id'=>$y1->keyword_id,'good_id'=>$y1->good_id])->andwhere(['BETWEEN','create_at',$start15_at-86400,$end15_at-86400])->one();
                                if(!empty($start15_pc_count->page_order_pc)){
                                    $rank_change_app = $start15_pc_count?intval($start15_pc_count->page_order_pc) - intval($y1->page_order_pc):0;
                                    $new->rank_change_pc_15 = $rank_change_app?$rank_change_app:0;
                                }
                            }
                        }else{
                            $start15_at_list = MonitorKeywordResult::find()->where(['keyword_id'=>$y1->keyword_id,'good_id'=>$y1->good_id])->andwhere(['BETWEEN','create_at',$start15_at,$end15_at])->one();
                            $rank_change_app = $start15_at_list?intval($start15_at_list->page_order_pc) - intval($y1->page_order_pc):0;
                            $new->rank_change_pc_15 = $rank_change_app?$rank_change_app:0;
                        }


                        $new->user_id = $y1->user_id;
                        $new->keyword_id = $y1->keyword_id;
                        $new->good_id = $y1->good_id;
                        $new->sku = $y1->sku;
                        $new->page_app = $y1->page_app;
                        $new->page_position_app = $y1->page_position_app;
                        $new->page_order_app = $y1->page_order_app;
                        $new->page_pc = $y1->page_pc;
                        $new->page_position_pc = $y1->page_position_pc;
                        $new->page_order_pc = $y1->page_order_pc;
                        $new->rank_change_app = $y1->rank_change_app;
                        $new->rank_change_pc = $y1->rank_change_pc;
                        $new->weight = $y1->weight;
                        $new->weight_change = $y1->weight_change;
                        $new->title_weight = $y1->title_weight;
                        $new->app_search_at = $y1->app_search_at;
                        $new->pc_search_at = $y1->pc_search_at;
                        $new->err_msg = $y1->err_msg;
                        $new->create_at = $y1->create_at;
                        $new->update_at = $y1->update_at;
                        //  获取7天数数据

//                        $rank_change_pc = $start7_at_list?intval($start7_at_list->page_order_pc) - intval($y1->page_order_pc):0;
//                        $new->rank_change_pc_7 = $rank_change_pc?$rank_change_pc:0;

                        //  获取15数数据
//                        $start7_at_list = MonitorKeywordResult::find()->where(['keyword_id'=>$y1->keyword_id,'good_id'=>$y1->good_id])->andwhere(['BETWEEN','create_at',$start15_at,$end15_at])->one();
//                        $rank_change_app = $start7_at_list?intval($start7_at_list->page_order_app) - intval($y1->page_order_app):0;
//                        $new->rank_change_app_15 = $rank_change_app?$rank_change_app:0;
//                        $rank_change_pc = $start7_at_list?intval($start7_at_list->page_order_pc) - intval($y1->page_order_pc):0;
//                        $new->rank_change_pc_15 = $rank_change_pc?$rank_change_pc:0;
                        $query = Yii::$app->db->createCommand("UPDATE `monitor_keyword_result` SET version=5 WHERE id=$y1->id")->execute();
                        if(!$new->save()){
                            return ['result'=>0,'msg'=>$new->getError()];
                            continue;
                        }
                        echo "记录".$y1->id."更新完成\n";
                    }
                    $MonitorKeywordResultR = MonitorKeywordResultR::find()->where(['id'=>$y->id])->one();
                    $MonitorKeywordResultR->status =1;
                    $MonitorKeywordResultR->save();
                    echo "任务记录".$y->id."更新完成\n";
                }while($count_1>0);
            }
        }while($count>0);
    }

    //多线程执行查询(排名监控  京东)
    public function actionSearchJd()
    {
        ini_set ('memory_limit', '512M');
        do{
            $count =0;
            try{
                $user = User::find()->where(['deleted' => 0,'is_update_ranking' => 0]);
                $count = $user->count();
                if ($count==0){
                    echo "更新完成" .date('Y-m-d H:i',time()) . "\n";
                    return;
                }


                $procs = array();
                $model = User::find()->where(['deleted' => 0,'is_update_ranking' =>0]);
                $querys = $model->limit(50)->all();
                \Yii::$app->db->close();
                $cmds = [];
                if (!$querys){
                    echo "查询完毕" . date('Y-m-d H:i',time()) . "\n";
                    return;
                }
                $i = 0;
                foreach ($querys as $query){
                    $cmds[$i][] = $query->id;
                    $i++;
                    if ($i==15){
                        $i=0;
                    }
                }

                foreach ($cmds as $cmd) {
                    $pid = pcntl_fork();
                    if ($pid == - 1) { // 进程创建失败
                        die('fork child process failure!');
                    } elseif ($pid) { // 父进程处理逻辑
                        $procs[] = $pid;
                    } else {
                        // 子进程处理逻辑
                        foreach ($cmd as $line) {
                            system('cd /data/wwwroot/jdcha && ' . 'php ' . './yii monitor-clock/key-update ' . $line);
                            echo $line . " done!\n";
                        }
                        exit(0);
                    }
                }
                foreach ($procs as $proc) {
                    pcntl_waitpid($proc, $status);
                }
                unset($pid);
                $pid = NULL;
                unset($procs);
                $procs = NULL;
            }catch (\Exception $e){
                echo $e->getMessage() . "11" . "\n";
            }


        }while($count>0);

    }


    /**
     * 类目监控排名，每日更新
     */
    public function actionCatSearch(){
        $startTime = time();
        echo "开始更新：" . date('Y-m-d H:i:s', $startTime) . "\n";

        // 查询活跃用户的类目监控记录
        $keywordQuery = MonitorKeyword::find()
            ->where([
                'deleted' => 0,
                'monitor_close' => 0,
                'is_category' => 1,
                'user_id' => User::getActiveUserIds()
            ]);
        $cloneQuery = clone $keywordQuery;
        $sum = $cloneQuery->select('id')->count('id');
        echo "更新总条数：" . $sum . "\n";

        $service = new MonitorRankService();
        $success = $fail = 0;
        foreach($keywordQuery->each() as $mKeyword){
            /* @var $mKeyword MonitorKeyword*/
            if (empty($mGoods = MonitorGoods::findOne($mKeyword->monitor_goods_id))){
                continue;
            }
            if (!$service->rankCategory($mKeyword, $mGoods, true)){
                echo "类目【".$mKeyword->category."】-id【".$mKeyword->id."】更新失败:". $service->getErrorString() . "\n";
                $fail++;
            }else{
                $success++;
            }
        }
        $useTime = time() - $startTime;
        echo "更新完成！成功". $success ."条，失败".$fail."条 --- 用时".$useTime."秒" . "\n";
    }
}
