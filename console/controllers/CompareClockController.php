<?php
/**
 * Created by PhpStorm.
 * User: liuyaping
 * Date: 2018/2/1
 * Time: 上午11:44
 */

namespace console\controllers;

use common\Helper\Helper;
use common\models\CompareAds;
use common\models\CompareComment;
use common\models\CompareGoods;
use common\models\ComparePrice;
use common\models\CompareSaler;
use common\models\CompareTitle;
use common\models\MonitorKeyword;
use common\models\ServiceKeywordSearch;
use common\models\User;
use common\models\UserShopAuth;
use linslin\yii2\curl\Curl;
use yii\console\Controller;
use Yii;
use yii\helpers\Json;
use Exception;

/**
 * Class CompareClockController
 * @package console\controllers
 */
class CompareClockController extends Controller
{

    /**
     * 竞品中心信息,商品信息定时更新
     * @throws
     */
    public function actionDailyCompareGoods()
    {
        $nowTime = time();
        echo "开始更新：" . date('Y-m-d H:i:s', $nowTime) . "\n";

        $success = $fail = 0;
        $goodsQuery = CompareGoods::find()
            ->where([
                'user_id' => User::getActiveUserIds(),
                'deleted' => 0,
                'is_update' => 0
            ]);
        $goodsQuery1 = clone $goodsQuery;
        $sum = $goodsQuery1->select('id')->count('id');
        echo "更新总条数：" . $sum . "\n";
        foreach ($goodsQuery->each(50) as $model){
            /* @var $model CompareGoods*/
            echo "竞品【id-" . $model->id . "】【sku-" . $model->sku . "】";
            $result = $this->skuCollect($model->sku);
            if (empty($result)){
                $fail++;
                continue;
            }

            $titleChange =
            $adsChange =
            $saleChange =
            $priceChange =
            $commentsChange = false;

            $model->shop_name = $result['name'];
            $model->good_pic = $result['img'];
            $model->is_update = 1;

            if ($model->title != $result['title']){
                $model->title = $result['title'];
                $model->title_begin_at = time();
                $titleChange = true;
            }

            $ads = $result['ads'] ? Json::encode($result['ads']) : '';
            if ($model->ads != $ads){
                $model->ads = $ads;
                $model->ads_begin_at = time();
                $adsChange = true;
            }

            $salers = $result['proms'] ? Json::encode($result['proms']) : '';
            if ($model->salers != $salers){
                $model->salers = $salers;
                $model->salers_count = $result['proms'] ? count($result['proms']) : 0;
                $model->salers_begin_at = time();
                $saleChange = true;
            }

            $price = $result['price'] ? $result['price'] : 0;
            if ($model->price != $price){
                $model->price = $price;
                $model->price_begin_at = time();
                $priceChange = true;
            }

            $comments = $result['commentsCount'] ? Json::encode($result['commentsCount']) : '';
            if ($model->comments != $comments){
                $model->comments = $comments;
                $model->comment_begin_at = time();
                $commentsChange = true;
            }
            $condition = [
                'deleted' => 0,
                'is_change' => 1,
                'good_id' => $model->id
            ];

            $trans = Yii::$app->db->beginTransaction();
            try{
                // 标题记录
                if ($titleChange){
                    $titleModel = CompareTitle::find()
                        ->where($condition)
                        ->orderBy('id desc')
                        ->one();
                    if ($titleModel){
                        $titleModel->end_at = time();
                        $titleModel->save();
                    }
                    $title = CompareTitle::titleSave($model->id, $model->title, $model->user_id);
                    if ($title['result'] != 1){
                        throw new Exception('标题记录添加失败：'.$title['msg']);
                    }
                }

                // 广告词记录
                if ($adsChange){
                    $adModel = CompareAds::find()->where($condition)
                        ->orderBy('id desc')
                        ->one();
                    if ($adModel){
                        $adModel->end_at = time();
                        $adModel->save();
                    }
                    $ads = CompareAds::adsSave($model->id, $model->ads, $model->user_id);
                    if ($ads['result'] != 1){
                        throw new Exception('广告词添加失败：'.$ads['msg']);
                    }
                }

                // 促销信息记录
                if ($saleChange){
                    $salerModel = CompareSaler::find()
                        ->where($condition)
                        ->orderBy('id desc')
                        ->one();
                    if ($salerModel){
                        $salerModel->end_at = time();
                        $salerModel->save();
                    }
                    $saler = CompareSaler::salerSave($model->id, $model->salers, $model->user_id);
                    if ($saler['result'] != 1){
                        throw new Exception('促销信息记录添加失败：'.$saler['msg']);
                    }
                }

                //更新价格记录
                if ($priceChange){
                    $priceModel = ComparePrice::find()
                        ->where($condition)
                        ->orderBy('id desc')
                        ->one();
                    if ($priceModel){
                        $priceModel->end_at = time();
                        $priceModel->save();
                    }
                    // 价格记录
                    $compare_price = ComparePrice::priceSave($model->price, $model->id, intval($priceChange), $model->user_id);
                    if ($compare_price['result'] != 1){
                        throw new Exception('价格记录添加失败：'.$compare_price['msg']);
                    }
                }

                // 评论数量记录
                if ($commentsChange){
                    $commentModel = CompareComment::find()
                        ->where($condition)
                        ->orderBy('id desc')
                        ->one();
                    if ($commentModel){
                        $commentModel->end_at = time();
                        $commentModel->save();
                    }
                    $comment = CompareComment::commentSave($model->id, $result['commentsCount'], intval($commentsChange), $model->user_id);
                    if ($comment['result'] != 1) {
                        throw new Exception('评论记录添加失败：'.$comment['msg']);
                    }
                }

                if (!$model->save()){
                    throw new Exception('竞品数据保存失败：' . $model->getError());
                }
                $trans->commit();
                $success++;
            }catch (\Exception $e){
                $trans->rollBack();
                $fail++;
                echo $e->getMessage() . "\n";
            }
        }
        $useTime = time() - $nowTime;
        echo "更新完成！成功". $success ."条，失败".$fail."条 --- 用时".$useTime."秒" . "\n";
    }

    /**
     * 查询竞品排名数据
     * @param $sku
     * @return array
     */
    private function skuCollect($sku){
        static $count = 0;
        $url = Yii::$app->params['sku_collect'] . $sku . "&cap=1&price=1";
        $resultArr = Helper::curlGet($url);

        $resultData = $resultArr['data'] ?? [];
        $resultResult = $resultArr['result'] ?? 0;
        if ($resultResult != 1 || empty($resultData)){
            $err = $resultArr["err"] ?? '';
            echo "查询失败【".$url."】【error：".$err."】\n";
            if (++$count < 2){
                $this->skuCollect($sku);
            }
        }else{
            echo "查询成功！\n";
        }
        $count = 0;
        unset($count);
        return $resultData;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function actionStatusUpdate()
    {
        //竞品中心零点更新查询状态
        Yii::$app->db->createCommand("UPDATE `compare_goods` SET is_update=0,search_count=0  WHERE deleted=0 AND  monitor_close=0")->execute();
        echo date('Y-m-d H:i',time()) . "竞品中心更新状态成功\n";
        //排名监控零点更新查询状态
        Yii::$app->db->createCommand("UPDATE `monitor_keyword` SET is_update_pc=0,is_update_app=0  WHERE deleted=0 AND  monitor_close=0")->execute();
        echo date('Y-m-d H:i',time()) . "排名监控更新状态成功\n";
    }
    public function actionStatusUpdate1()
    {
        //竞品中心零点更新查询状态
        CompareGoods::updateAll(['is_update'=>0,'search_count'=>0],CompareGoods::find()->where(['deleted'=>0,'monitor_close'=>0])->where);
        echo date('Y-m-d H:i',time()) . "竞品中心更新状态成功\n";

        //排名监控零点更新查询状态
        MonitorKeyword::updateAll(['is_update_pc'=>0,'is_update_app'=>0],MonitorKeyword::find()->where(['deleted'=>0,'monitor_close'=>0])->where);
        echo date('Y-m-d H:i',time()) . "排名监控更新状态成功\n";
    }

    public function actionTemp()
    {

        $

        //绑定店铺用户数
        $querys = UserShopAuth::find()->select('user_id')->distinct('user_id')->where(['deleted'=>0,'auth_status'=>1])->all();

        $data = [];
        foreach ($querys as $query){
            $count = User::find()->where(['deleted'=>0,'up_user_id'=>$query->user_id])->count();
            $data[$count] = isset($data[$count])?$data[$count]+1:1;
        }
        print_r($data);
    }

    public function actionTempTwo()
    {
        //近7天使用过功能用户数
        $datas= [];
        $services  = ServiceKeywordSearch::find()->select('user_id')->distinct('user_id')->where(['deleted'=>0])
                ->andWhere(['<>','user_id',0])->andWhere(['>','create_at',time() - 3600 * 24 * 7])->all();
        foreach ($services as $service){
            $datas[] = $service->user_id;
        }

        $monitors = MonitorKeyword::find()->select('user_id')->distinct('user_id')->where(['deleted'=>0])
                ->andWhere(['<>','user_id',0])->andWhere(['>','create_at',time() - 3600*24*7])->all();
        foreach ($monitors as $monitor){
            if (in_array($monitor->user_id,$datas,false)){

            }else{
                $datas[] = $monitor->user_id;
            }
        }

        $compares = CompareGoods::find()->select('user_id')->distinct('user_id')->where(['deleted'=>0])
                    ->andWhere(['<>','user_id',0])->andWhere(['>','create_at',time() - 3600*24*7])->all();
        foreach ($compares as $compare){
            if (in_array($compare->user_id,$datas,false)){

            }else{
                $datas[] = $compare->user_id;
            }
        }

        echo "7天内使用用户:" . count($datas) . "\n";

        //7天内使用绑定店铺数
        $data_uses = [];
        foreach ($datas as $data){
            $model = UserShopAuth::find()->where(['deleted'=>0,'user_id'=>$data,'auth_status'=>1])->one();
            if ($model){
                $data_uses[] = $model->user_id;
            }
        }
        echo "7天内使用用户绑定店铺数:" . count($data_uses) . "\n";
        echo "7天内使用用户未绑定店铺数:" . (count($datas) - count($data_uses)) . "\n";


        $lists = [];
        foreach ($data_uses as $data_use){
            $count = User::find()->where(['deleted'=>0,'up_user_id'=>$data_use])->count();
            $lists[$count] = isset($lists[$count])?$lists[$count]+1:1;
        }
        print_r($lists);

    }

    /**
     * 竞品中心信息固定时间采集-弃置
     * @throws
     */
    public function actionIndex()
    {

        $date_h = date('H',time());
        if ($date_h > 23){
            echo "23点，停止查询\n";
            return;
        }

        echo "更新时间" . date('Y-m-d H:i',time()) . "\n";

        $index = 0;
        do{
            $count_all = CompareGoods::find()
                ->where(['deleted'=>0,'is_update'=>0])
                ->andWhere(['<','search_count',3])
                ->count();
            if ($count_all == 0){
                echo "更新完成1.." . date('Y-m-d H:i',time()) . "\n";
                return;
            }

            $count = CompareGoods::find()
                ->where(['deleted'=>0,'is_update'=>0])
                ->andWhere(['>','id',$index])
                ->andWhere(['<','search_count',3])
                ->count();
            if ($count_all > 0 && $count == 0){
                $index = 0;
                $count = CompareGoods::find()
                    ->where(['deleted'=>0,'is_update'=>0])
                    ->andWhere(['>','id',$index])
                    ->andWhere(['<','search_count',3])
                    ->count();
            }
            $models = CompareGoods::find()
                ->where(['deleted'=>0,'is_update'=>0])
                ->andWhere(['>','id',$index])
                ->andWhere(['<','search_count',3])
                ->limit(50)
                ->all();

            foreach ($models as $model){
                /* @var $model CompareGoods*/
                $trans = Yii::$app->db->beginTransaction();

                $index = $model->id;
                $sku = $model->sku;
                try{
                    sleep(2);
                    $url = Yii::$app->params['sku_collect'] . $sku . "&cap=1&price=1";
                    $curl = new Curl();
                    $collectString = $curl->get($url);
                    if (strstr($collectString,"502 Bad Gateway")){
                        $trans->rollBack();
                        echo "商品id:" . $model->id . "查询失败，等待再次查询\n";
                        continue;
                    }
                    $data = Json::decode($collectString,false);

                    if ($data->result != 1){
                        $trans->rollBack();
                        $model->search_count += 1;
                        $model->save();
                        continue;
                    }


                    $result = $data->data?$data->data:'';
                    if (! $result){
                        $trans->rollBack();
                        $model->search_count += 1;
                        $model->save();
                        continue;
                    }

                    $model->shop_name = $result->name;
                    $model->good_pic = $result->img;
                    $title_change = 0;
                    if ($result->title != $model->title){
                        $model->title = $result->title;
                        $model->title_begin_at = time();
                        $title_change = 1;
                    }
                    $ads_change = 0;
                    $ads = $result->ads?Json::encode($result->ads):'';
                    if ($ads != $model->ads){
                        $model->ads = $ads;
                        $model->ads_begin_at = time();
                        $ads_change = 1;
                    }

                    $salers_change = 0;
                    $salers = $result->proms?Json::encode($result->proms):'';
                    if ($salers != $model->salers){
                        $model->salers = $salers;
                        $model->salers_count = $result->proms?count($result->proms):0;
                        $model->salers_begin_at = time();
                        $salers_change = 1;
                    }

                    $price_change = 0;
                    $price = $result->price?$result->price:0;
                    if ($price != $model->price){
                        $model->price = $price;
                        $model->price_begin_at = time();
                        $price_change = 1;
                    }

                    $comments_change = 0;
                    $comments = $result->commentsCount?Json::encode($result->commentsCount):'';
                    if ($comments != $model->comments){
                        $model->comments = $comments;
                        $model->comment_begin_at = time();
                        $comments_change = 1;
                    }
                    $model->is_update = 1;

                    //更新上次状态(结束时间)
                    if ($ads_change == 1){
                        $adModel = CompareAds::find()->where(['deleted'=>0,'is_change'=>1,'good_id'=>$model->id])->orderBy('id desc')->one();
                        if ($adModel){
                            $adModel->end_at = time();
                            $adModel->save();
                        }

                        //广告词记录
                        $ads = CompareAds::adsSave($model->id,$model->ads,$model->user_id);
                        if ($ads['result'] != 1){
                            $trans->rollBack();
                            $model->search_count += 1;
                            $model->save();
                            continue;
                        }
                    }

                    //评论数量记录
                    if ($comments_change == 1){
                        $commentModel = CompareComment::find()->where(['deleted'=>0,'is_change'=>1,'good_id'=>$model->id])->orderBy('id desc')->one();
                        $commentModel->end_at = time();
                    }
                    $comment = CompareComment::commentSave($model->id,$result->commentsCount,$comments_change,$model->user_id);
                    if ($comment['result'] != 1){
                        $trans->rollBack();
                        $model->search_count += 1;
                        $model->save();
                        continue;
                    }

                    //更新价格记录
                    if ($price_change == 1){
                        $priceModel = ComparePrice::find()->where(['deleted'=>0,'is_change'=>1,'good_id'=>$model->id])->orderBy('id desc')->one();
                        $priceModel->end_at = time();
                        $priceModel->save();
                    }
                    //价格记录
                    $compare_price = ComparePrice::priceSave($model->price,$model->id,$price_change,$model->user_id);
                    if ($compare_price['result'] != 1){
                        $trans->rollBack();
                        $model->search_count += 1;
                        $model->save();
                        continue;
                    }

                    if ($salers_change == 1){
                        $salerModel = CompareSaler::find()->where(['deleted'=>0,'is_change'=>1,'good_id'=>$model->id])->orderBy('id desc')->one();
                        $salerModel->end_at = time();
                        $salerModel->save();

                        //促销信息记录
                        $saler = CompareSaler::salerSave($model->id,$model->salers,$model->user_id);
                        if ($saler['result'] != 1){
                            $trans->rollBack();
                            $model->search_count += 1;
                            $model->save();
                            continue;
                        }
                    }

                    if ($title_change == 1){
                        $titleModel = CompareTitle::find()->where(['deleted'=>0,'is_change'=>1,'good_id'=>$model->id])->orderBy('id desc')->one();
                        $titleModel->end_at = time();
                        $titleModel->save();
                        //标题记录
                        $title = CompareTitle::titleSave($model->id,$model->title,$model->user_id);
                        if ($title['result'] != 1){
                            $trans->rollBack();
                            $model->search_count += 1;
                            $model->save();
                            continue;
                        }
                    }

                    $model->search_count += 1;
                    if (!$model->save()){
                        $trans->rollBack();
                        echo 'save_err:' . $model->getError() . "\n";
                        continue;
                    }

                    echo "循环" . $index . "\n";
                    $trans->commit();
                }catch (\Exception $e){
                    $trans->rollBack();
                    echo $e->getMessage() . "\n";
                    $model->search_count += 1;
                    $model->save();
                    continue;
                }
            }

        }while($count > 0);
        echo "更新完成" . date('Y-m-d H:i',time()) . "\n";

    }
}