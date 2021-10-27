<?php
/**
 * Created by PhpStorm.
 * User: lisk
 * Date: 2018年10月27日
 * Time: 14点06分
 */
namespace api\modules\jd\controllers;
use common\models\ApiCityList;
use common\models\ApiCityLists;
use common\models\BaseGoods;
use common\models\CpsUser;
use common\models\IpLog;
use common\models\RandomList;
use common\models\SalesRecord;
use common\models\ServiceKeywordSearch;
use common\models\ServiceKeywordSearchResult;
use common\models\TimeList;
use common\models\User;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use api\modules\jd\Controller;
use Yii;
use yii\helpers\Json;
use yii\web\Response;
use linslin\yii2\curl\Curl;
use yii\filters\ContentNegotiator;
class RankingController extends Controller{

    public $enableCsrfValidation =false;
    /**
     * @return array
     * 历史记录
     */
    public function actionHistoryLog(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $type = Yii::$app->request->post('type',1);
        $page = Yii::$app->request->post('page',1);
        $page_size = Yii::$app->request->post('page_size',20);
        if(empty($type)){
            return ['result'=>0,'msg'=>'参数不正确'];
        }
        $user_id = Yii::$app->user->id;
        switch ($type){
            //  查排名
            case 1:
                $cps = ServiceKeywordSearch::find()->alias('a')->where(['a.user_id'=>Yii::$app->user->id,'a.type'=>$type,'a.deleted'=>0])->andWhere(['<>','s.id','null'])->andWhere(['<>','s.page_order',0])->andWhere(['!=','model',2])->innerjoin('service_keyword_search_result as s', 's.sericve_keyword_search_id=a.id');
                $jquery = clone $cps;
                $total_count = $jquery->count();
                $total_page = ceil($total_count / $page_size);
                $offset = ($page - 1) * $page_size;
                $results = $cps->orderBy('a.id desc')->offset($offset)->limit($page_size)->all();
                if(empty($results)){
                    return ['result'=>1065,'msg'=>'没有更多了'];
                }
                $data = [];
                foreach($results as $k =>$y){
                    $is_data = ServiceKeywordSearchResult::getRankData($y['id'],$y['sku'],$y['city_id'],$y['sku'],$y['model']);
                    unset($is_data['result']);
                    $is_data = $is_data['data'];
                    unset($is_data['create_at']);
                    unset($is_data['sku_self']);
                    unset($is_data['main_sku']);
                    $service_id = $is_data['service_id'];
                    if(empty($is_data['result'])){
                        continue;
                    }
                    $is_data = $is_data['result'][0];
                    $is_data->service_id = $service_id;
                    $data[] = $is_data;
                }
                break;
            // 查权重
            case 2:
                $cps = ServiceKeywordSearch::find()->alias('a')->where(['a.user_id'=>Yii::$app->user->id,'a.type'=>$type,'a.deleted'=>0])->andWhere(['<>','s.id','null'])->andWhere(['<>','s.page_order',0])->innerjoin('service_keyword_search_result as s', 's.sericve_keyword_search_id=a.id');
                $jquery = clone $cps;
                $total_count = $jquery->count();
                $total_page = ceil($total_count / $page_size);
                $offset = ($page - 1) * $page_size;
                $results = $cps->orderBy('a.id desc')->offset($offset)->limit($page_size)->all();

                if(empty($results)){
                    return ['result'=>1065,'msg'=>'没有更多了'];
                }
                $data = [];
                foreach($results as $k =>$y){
                    $is_data = ServiceKeywordSearchResult::weightLog($y['id'],$y['sku']);
                    $data[] = $is_data;
                }
                break;
            // 查销量
            case 3:
                $cps = SalesRecord::find()->where(['user_id'=>Yii::$app->user->id,'deleted'=>0])->andWhere(['!=','sku','']);
                $jquery = clone $cps;
                $total_count = $jquery->count();
                $total_page = ceil($total_count / $page_size);
                $offset = ($page - 1) * $page_size;
                $results = $cps->orderBy('id desc')->offset($offset)->limit($page_size)->all();
                if(empty($results)){
                    return ['result'=>1065,'msg'=>'没有更多了'];
                }
                $data = [];
                foreach($results as $k =>$y){
                    $data_s['id'] = $y->id;
                    $data_s['user_id'] = $y->user_id;
                    $data_s['title'] = $y->title;
                    $data_s['sku'] = $y->sku;
                    $data_s['specification'] = $y->specification;
                    $data_s['create_at'] = date('m-d H:i',$y->create_at);
                    $data_s['result_json'] = json_decode($y['result_json'],true);
                    $data[] = $data_s;
                }
                break;
        }

        return ['result'=>1,'data'=>$data,'page'=>$page,'page_size'=>$page_size,'total_count'=>$total_count,'total_page'=>$total_page,'msg'=>'历史记录'];
    }

    /**
     * 删除记录
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionDeleteLog(){
        Yii::$app->response->format = Response::FORMAT_JSON;

        $type = Yii::$app->request->post('type',1);
        $type_s = Yii::$app->request->post('type_s',1);
        $id = Yii::$app->request->post('id');
        if(empty($id)){
            return ['result'=>0,'msg'=>'参数不正确'];
        }

        if($type_s == 2){
            $name = 'service_keyword_search';
        }else{
            $name = 'service_keyword_search';
        }
        switch ($type){
            case 1:
            case 2:
                if(!$querys = Yii::$app->db->createCommand("UPDATE $name SET deleted=1 WHERE id in ($id)")->execute()){
                    return ['result'=>0,'msg'=>'操作失败'];
                }
                break;
            case 3:
                if(!$querys = Yii::$app->db->createCommand("UPDATE `sales_record` SET deleted=1 WHERE id in ($id)")->execute()){
                    return ['result'=>0,'msg'=>'操作失败'];
                }
                break;
        }
        return ['result'=>1,'msg'=>'操作成功'];
    }

    /**
     * 邀请列表
     * @return array
     */
    public function actionCpsDetail()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $page = Yii::$app->request->post('page',1);
        $page_size = Yii::$app->request->post('page_size',20);
        $result = CpsUser::getCpsList($page,$page_size);
        return $result;
    }

    /**
     * 搜索
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionSeek(){
        $writeFile = fopen(Yii::getAlias('@api') .  '/payInfo2.txt','a');
        // 打印回调信息


        Yii::$app->response->format = Response::FORMAT_JSON;
//        Yii::getLogger()->log("your site has been hacked", 111,$category = 'application');
        $type = Yii::$app->request->post('type',1);
        $content = Yii::$app->request->post('content');
        $entrance = Yii::$app->request->post('entrance');
        $cap_type = Yii::$app->request->post('cap_type');
        $page = Yii::$app->request->post('page',1);
        $page_size = Yii::$app->request->post('page_size',10);
        $user_id = Yii::$app->user->id;

        $offset = ($page-1)*$page_size;
        if($offset == 0){
            $limit_sql = "LIMIT $page_size";
        }else{
            $limit_sql = "LIMIT $page_size OFFSET $offset";
        }

        switch($type){
            case 1:
                if(Yii::$app->user->id == 30808){
//                    var_dump(Yii::$app->request->post)
                    fwrite($writeFile,json_encode(Yii::$app->request->post()) . "\n");
                }
                //  查排名
                $query = ServiceKeywordSearch::find()->where(['type'=>1,'user_id'=>Yii::$app->user->id,'deleted'=>0]);
                if($cap_type == 1){
                    $entrance = '';
                }
                    if($content){
                        $content_sql = "AND (`keyword` LIKE '%$content%')";
                        $keyword_sql = "ORDER BY CONVERT( keyword USING gbk ) COLLATE gbk_chinese_ci ASC,`create_at` DESC";
                    }else{
                        $content_sql = "";
                    }

                    if($entrance){
                        if($entrance == 4){
                            $entrance_sql = "AND client_type between 1  and 3";
                        }else{
                            $entrance_sql = "AND client_type = $entrance";
                        }
                    }else{
                        $entrance_sql = "AND client_type between 1 and 3";
                    }

                    if($cap_type == 1){
                        $keyword_sql = "ORDER BY  CONVERT( keyword USING gbk ) COLLATE gbk_chinese_ci ASC";
                        $keyword_sql_1 = "GROUP BY sku,keyword HAVING COUNT(*) >1 ORDER BY count DESC";
                    }

                    if($cap_type == 2){
                        $keyword_sql = "ORDER BY `create_at` DESC";
                    }
                $keyword_sql = empty($keyword_sql) ? $keyword_sql="" : $keyword_sql;

                if($cap_type == 3){
                        if($content){
                            $querys = Yii::$app->db->createCommand("SELECT sku,keyword,city_id,COUNT(id) as count FROM `service_keyword_search` WHERE ((`user_id`=$user_id)  AND (`type`=1)  AND  (`deleted`=0) AND (`keyword` LIKE '%$content%') AND (`model`!= 2))  GROUP BY sku,keyword,city_id HAVING COUNT(*) >1 ORDER BY count DESC")->queryAll();
                            if($querys){
                                $data = [];
                                foreach ($querys as $k => $y){
                                    $sku = $y['sku'];
                                    $keyword = $y['keyword'];
                                    $city_id = $y['city_id'];
                                    $querys_s = Yii::$app->db->createCommand("SELECT * FROM `service_keyword_search` WHERE `sku`=$sku AND `keyword` = '$keyword' AND `city_id` = $city_id AND  `type`=1 AND `user_id`=$user_id AND `deleted`=0 $keyword_sql")->queryAll();
                                    $page_count = $page*$page_size;
                                    foreach($querys_s as $k1 =>$y1){
                                        $list_count = count($data);
                                        if($list_count <= $page_count-1){
                                            $is_data = ServiceKeywordSearchResult::getRankData($y1['id'],$y1['sku'],$y1['city_id'],$y1,$y1['model'],1);
                                            unset($is_data['result']);
                                            $is_data = $is_data['data'];
                                            if(empty($is_data['result'])){
                                                continue;
                                            }
                                            $data[] = $is_data;
                                        }else{
                                            break;
                                        }
                                    }
                                    if($list_count > $page_count-1){
                                        break;
                                    }
                                }
                                $data = array_slice($data,$offset,$page_count);
                                if(empty($data)){
                                    return ['result'=>1065,'msg'=>'没有更多了'];
                                }
                            }else{
                                return ['result'=>1065,'msg'=>'没有更多了'];
                            }
                        }else{
                            $querys_s = Yii::$app->db->createCommand("SELECT * FROM (SELECT *,COUNT(id) as count FROM `service_keyword_search` WHERE ((`type`=1) AND (`user_id`=$user_id) AND (`deleted`=0) AND (`model`!= 2))  GROUP BY sku,keyword ORDER BY (user_memo+0) DESC) as s  ORDER BY create_at DESC $limit_sql")->queryAll();
                                $data = [];
                                    foreach($querys_s as $k1 =>$y1){
                                            $is_data = ServiceKeywordSearchResult::getRankData($y1['id'],$y1['sku'],$y1['city_id'],$y1,$y1['model'],1);
                                            unset($is_data['result']);
                                            $is_data = $is_data['data'];
                                            if(empty($is_data['result'])){
                                                continue;
                                            }
                                            $data[] = $is_data;
                                    }
                            if(empty($data)){
                                return ['result'=>1065,'msg'=>'没有更多了'];
                            }
                        }
                    }else{
                        if($entrance){
                            // 入口类型
                            $querys_s = Yii::$app->db->createCommand("SELECT * FROM (SELECT  max(id) as id,sku,user_id,keyword,user_memo,create_at,sku_list,city_id,model,result_sort,price_min,price_max,weight_min,weight_max,client_type FROM `service_keyword_search` WHERE  `type`=1 $content_sql AND `user_id`=$user_id AND `deleted`=0 AND  `model`!= 2   $entrance_sql GROUP BY sku,keyword  ORDER BY id DESC ) as s GROUP BY sku,keyword  ORDER BY id DESC $limit_sql")->queryAll();

                            $data = [];
                            if(empty($querys_s)){
                                return ['result'=>1065,'msg'=>'没有更多了'];
                            }
                            foreach ($querys_s as $k => $y){

                                    $is_data = ServiceKeywordSearchResult::getRankData($y['id'],$y['sku'],$y['city_id'],$y,$y['model'],1);
                                    unset($is_data['result']);
                                    $is_data = $is_data['data'];
                                    if(empty($is_data['result'])){
                                        continue;
                                    }
                                    array_push($data,$is_data);

                            }
                            if(empty($data)){
                                return ['result'=>1065,'msg'=>'没有更多了'];
                            }
                        }else if($cap_type == 1){
//                            if(Yii::$app->user->id == 30544){
//                                var_dump("SELECT * FROM (SELECT max(id) as id,sku,user_id,keyword,user_memo,create_at,sku_list,city_id,model,result_sort,price_min,price_max,weight_min,weight_max,client_type FROM `service_keyword_search` WHERE `type`=1 AND `user_id`=$user_id AND `deleted`=0 	GROUP BY sku,keyword   ORDER BY (user_memo+0) DESC,id DESC )as s GROUP BY sku,keyword ORDER BY (user_memo+0) DESC ,id DESC $limit_sql");exit;
//                            }
                            //  排序关键词 高 低
                            $querys_s = Yii::$app->db->createCommand("SELECT * FROM (SELECT max(id) as id,sku,user_id,keyword,user_memo,create_at,sku_list,city_id,model,result_sort,price_min,price_max,weight_min,weight_max,client_type FROM `service_keyword_search` WHERE `type`=1 AND `user_id`=$user_id AND `deleted`=0 	GROUP BY sku,keyword   ORDER BY (user_memo+0) DESC,id DESC )as s GROUP BY sku,keyword ORDER BY (user_memo+0) DESC ,id DESC $limit_sql")->queryAll();
                            if(empty($querys_s)){
                                return ['result'=>1065,'msg'=>'没有更多了'];
                            }

                            $data = [];
                                foreach ($querys_s as $k1 =>$y1){
                                        $is_data = ServiceKeywordSearchResult::getRankData($y1['id'],$y1['sku'],$y1['city_id'],$y1,$y1['model'],1);
                                        unset($is_data['result']);
                                        $is_data = $is_data['data'];
                                        if(empty($is_data['result'])){
                                            continue;
                                        }
                                        array_push($data,$is_data);
                                }
                            if(empty($data)){
                                return ['result'=>1065,'msg'=>'没有更多了'];
                            }
                        }else if($cap_type == 2){
                            //  时间
                            $querys_s = Yii::$app->db->createCommand("SELECT * FROM (SELECT * FROM `service_keyword_search` ORDER BY create_at DESC) as s WHERE  `type`=1 $content_sql AND `model`!= 2 AND `user_id`=$user_id  $entrance_sql AND `deleted`=0 ORDER BY create_at DESC ")->queryAll();
                            $data = [];
                            if(empty($querys_s)){
                                return ['result'=>1065,'msg'=>'没有更多了'];
                            }
                            foreach ($querys_s as $k => $y){
                                $page_count = $page*$page_size;
                                $list_count = count($data);
                                if($list_count <= $page_count-1){
                                    // pas_city_name 地区质空
                                    $is_data = ServiceKeywordSearchResult::getRankData($y['id'],$y['sku'],$y['city_id'],$y,$y['model'],1);
                                    unset($is_data['result']);
                                    $is_data = $is_data['data'];
                                    $data[] = $is_data;
                                }else{
                                    break;
                                }
                            }
                            if(empty($data)){
                                return ['result'=>1065,'msg'=>'没有更多了'];
                            }
                        }
                    }
                break;
            case 2:

                    //  查权重
                    if($cap_type == 1){
                        $keyword_sql = "ORDER BY CONVERT( keyword USING gbk ) COLLATE gbk_chinese_ci ASC,`create_at` DESC";
                        $keyword_sql_1 = "GROUP BY sku,keyword HAVING COUNT(*) >1 ORDER BY count DESC";
                    }

                    if($content){
                        $content_sql = "AND (`keyword` LIKE '%$content%')";
                        $keyword_sql = "ORDER BY CONVERT( keyword USING gbk ) COLLATE gbk_chinese_ci ASC,`create_at` DESC";
                    }else{
                        $content_sql = "";
                    }

                    if($cap_type == 2){
                        $keyword_sql = "ORDER BY `create_at` DESC";
                    }else{
                        $keyword_sql = "";
                    }

                    if($cap_type == 1){
                        //  排序关键词 高 低
                        $querys_s = Yii::$app->db->createCommand("SELECT * FROM `service_keyword_search` WHERE user_id=$user_id AND type=2  $content_sql  GROUP BY sku,keyword ORDER BY (user_memo+0) DESC ,id DESC $limit_sql")->queryAll();
//                        $querys = Yii::$app->db->createCommand("SELECT id,sku,city_id,keyword,client_type,COUNT(*) as count FROM `service_keyword_search` WHERE ((`type`=2) AND (`model`!= 2) AND (`user_id`=$user_id) AND (`deleted`=0)) $content_sql $keyword_sql_1")->queryAll();
                        if(empty($querys_s)){
                            return ['result'=>1065,'msg'=>'没有更多了'];
                        }

                        $data = [];
                            foreach ($querys_s as $k1 =>$y1) {
                                    $is_data = ServiceKeywordSearchResult::getRankData($y1['id'],$y1['sku'],$y1['city_id'],$y1);

                                    unset($is_data['result']);
                                    if(empty($is_data['data'])){
                                        continue;
                                    }
                                    $is_data = $is_data['data'];
                                    if(empty($is_data['result'])){
                                        continue;
                                    }

                                    array_push($data,$is_data);
                            }
                        if(empty($data)){
                            return ['result'=>1065,'msg'=>'没有更多了'];
                        }
                    }else if($cap_type == 2){

                        //  时间
                        $querys_s = Yii::$app->db->createCommand("SELECT * FROM `service_keyword_search` WHERE  `type`=2  AND `model`!= 2 AND `user_id`=$user_id AND `deleted`=0 $content_sql ORDER BY create_at DESC")->queryAll();
                        $data = [];

                        if(empty($querys_s)){
                            return ['result'=>1065,'msg'=>'没有更多了'];
                        }

                        foreach ($querys_s as $k => $y){
                            $page_count = $page*$page_size;
                            $list_count = count($data);

                            if($list_count <= $page_count-1){
                                $is_data = ServiceKeywordSearchResult::getRankData($y['id'],$y['sku'],$y['city_id'],$y);

                                unset($is_data['result']);
                                if(empty($is_data['data'])){
                                    continue;
                                }
                                $is_data = $is_data['data'];
                                if(empty($is_data['result'])){
                                    continue;
                                }
                                array_push($data,$is_data);
                            }else{
                                continue;
                            }
                        }
                        if(empty($data)){
                            return ['result'=>1065,'msg'=>'没有更多了'];
                        }
                        $data = array_slice($data,$offset,$page_count);
                    }else if($cap_type == 3){
                        if($content){
                            $querys_s = Yii::$app->db->createCommand("SELECT * FROM (SELECT *,COUNT(id) as count FROM `service_keyword_search` WHERE ((`keyword` LIKE '%$content%') AND (`type`=2) AND (`model`!= 2) AND (`user_id`=$user_id) AND (`deleted`=0)) GROUP BY sku,keyword ORDER BY (user_memo+0) DESC) as s  ORDER BY create_at DESC $limit_sql ")->queryAll();
                                $data = [];
                                if(empty($querys_s)){
                                    return ['result'=>1065,'msg'=>'没有更多了'];
                                }
                                    foreach($querys_s as $k1 =>$y1){
                                            $is_data = ServiceKeywordSearchResult::getRankData($y1['id'],$y1['sku'],$y1['city_id'],$y1);
                                            unset($is_data['result']);
                                            $is_data = $is_data['data'];
                                            if(empty($is_data['result'])){
                                                continue;
                                            }
                                            if(empty($is_data['result'])){
                                                continue;
                                            }
                                            array_push($data,$is_data);
                                    }
                                    if(empty($data)){
                                        return ['result'=>1065,'msg'=>'没有更多了'];
                                    }
                        }else{
                            $querys_s = Yii::$app->db->createCommand("SELECT * FROM (SELECT * FROM `service_keyword_search` WHERE ((`type`=2) AND (`user_id`=$user_id) AND (`deleted`=0)  AND (`model`!= 2)) GROUP BY sku,keyword ORDER BY (user_memo+0) DESC ) as s ORDER BY create_at DESC $limit_sql")->queryAll();
//                            var_dump("SELECT * FROM (SELECT * FROM `service_keyword_search` WHERE ((`type`=2) AND (`user_id`=$user_id) AND (`deleted`=0)  AND (`model`!= 2)) GROUP BY sku,keyword ORDER BY (user_memo+0) DESC ) as s ORDER BY create_at DESC $limit_sql");exit();
                                $data = [];
                                if(empty($querys_s)){
                                    return ['result'=>1065,'msg'=>'没有更多了'];
                                }
                                    foreach($querys_s as $k1 =>$y1){
                                            $is_data = ServiceKeywordSearchResult::getRankData($y1['id'],$y1['sku'],$y1['city_id'],$y1);
                                            unset($is_data['result']);
                                            $is_data = $is_data['data'];
                                            if(empty($is_data['result'])){
                                                continue;
                                            }

                                        $data[] = $is_data;
                                    }
                            if(empty($data)){
                                return ['result'=>1065,'msg'=>'没有更多了'];
                            }
                        }
                    }
                break;
            case 3:
                //  销量
                if($cap_type == 2){
                    $keyword_sql = "ORDER BY `create_at` DESC";
                }else{
                    $keyword_sql = "";
                }

                if($content){
                    $content_sql = "AND (`title` LIKE '%$content%')";
                    $keyword_sql = "ORDER BY CONVERT( title USING gbk ) COLLATE gbk_chinese_ci ASC,`create_at` DESC";
                }else{
                    $content_sql = "";
                }

                if($cap_type == 2){
                    //  时间
                    $querys_s = Yii::$app->db->createCommand("SELECT * FROM `sales_record` WHERE  `user_id`=$user_id AND `sku`!='' AND `deleted`=0 $content_sql $keyword_sql $limit_sql")->queryAll();
                    if(empty($querys_s)){
                        return ['result'=>1065,'msg'=>'没有更多了'];
                    }
                    foreach($querys_s as $k => $y){
                        $querys_s[$k]['create_at'] = date('m-d H:i',$y['create_at']);
                        $querys_s[$k]['result_json'] = json_decode($y['result_json'],TRUE);
                    }
                    $data = $querys_s;
                }else if($cap_type == 3){
                    // 频率
                    if($content){
                        $querys = Yii::$app->db->createCommand("SELECT *,COUNT(id) as count FROM `sales_record` WHERE `user_id`=$user_id AND `deleted`=0 AND `sku` is not null $content_sql GROUP BY sku HAVING COUNT(*) >1 ORDER BY count DESC")->queryAll();
                        if($querys){
                            $data = [];
                            foreach ($querys as $k => $y){
                                $sku = $y['sku'];
                                $querys_s = Yii::$app->db->createCommand("SELECT * FROM `sales_record` WHERE `sku`=$sku AND `user_id`=$user_id AND `deleted`=0 $content_sql ORDER BY create_at DESC")->queryAll();
                                $page_count = $page*$page_size;
                                foreach($querys_s as $k1 =>$y1){
                                    $list_count = count($data);
                                    $querys_s[$k1]['create_at'] = date('m-d H:i',$y1['create_at']);
                                    if($list_count <= $page_count-1){
                                        $querys_s[$k1]['result_json'] = json_decode($y1['result_json'],TRUE);
                                    }else{
                                        break;
                                    }
                                    $data[] = $querys_s[$k1];
                                }
                                if($list_count > $page_count-1){
                                    break;
                                }
                            }
                            if(empty($data)){
                                return ['result'=>1065,'msg'=>'没有更多了'];
                            }
                            $data = array_slice($data,$offset,$page_count);
                        }else{
                            return ['result'=>1065,'msg'=>'没有更多了'];
                        }
                    }else{
                        $querys = Yii::$app->db->createCommand("SELECT *,COUNT(id) as count FROM `sales_record` WHERE `user_id`=$user_id AND `deleted`=0 AND `sku` >1 $content_sql GROUP BY sku HAVING COUNT(*) >1 ORDER BY count DESC")->queryAll();
                        if($querys){
                            $data = [];
                            foreach ($querys as $k => $y){
                                $sku = $y['sku'];
                                $querys_s = Yii::$app->db->createCommand("SELECT * FROM `sales_record` WHERE `sku`=$sku AND `user_id`=$user_id AND `deleted`=0 $content_sql ORDER BY create_at DESC")->queryAll();
                                $page_count = $page*$page_size;
                                foreach($querys_s as $k1 =>$y1){
                                    $list_count = count($data);
                                    $querys_s[$k1]['create_at'] = date('m-d H:i',$y1['create_at']);
                                    if($list_count < $page_count-1){
                                        $querys_s[$k1]['result_json'] = json_decode($y1['result_json'],TRUE);
                                    }else{
                                        break;
                                    }
                                    $data[] = $querys_s[$k1];
                                }

                                if($list_count > $page_count-1){
                                    break;
                                }
                            }
                            if(empty($data)){
                                return ['result'=>1065,'msg'=>'没有更多了'];
                            }
                            $data = array_slice($data,$offset,$page_count);
                        }else{
                            return ['result'=>1065,'msg'=>'没有更多了'];
                        }
                    }
                }
                break;
        }
        return ['result'=>1,'msg'=>'搜索','data'=>$data];
    }

    /**
     * 排名
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionRankSearch()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        //判断当前用户的查排名的次数是否足够
        $limit = User::getLeftTimesResult(1);
        if($limit['result'] == 0 ){
            return $limit;
        }
//        if(!isset($_SERVER['HTTP_REFERER']) ||
//            (!stripos($_SERVER['HTTP_REFERER'],'jdmohe.com') && !stripos($_SERVER['HTTP_REFERER'],'chaojids.com'))) {
//            return array('result'=>0,  'msg'=>'非法请求');
//        }
        if (! Yii::$app->request->post()){
            return ['result'=>0,'msg'=>'非法请求'];
        }
        $city = Yii::$app->request->post('city','0,0');
        $city = ApiCityLists::Joint($city);
//        $writeFile = fopen(Yii::getAlias('@api') .  '/payInfo1.txt','a');
        // 打印回调信息
//        fwrite($writeFile,Yii::$app->request->post('city_id') . "\n");
        $times = time();
        $model = new ServiceKeywordSearch();
        $model->model = Yii::$app->request->post('type');
        $sku = Yii::$app->request->post('sku');
        if ($model->model == 1){
            if (! is_numeric($sku)){
                $sku = preg_match('/([0-9]{5,30})/',$sku,$a) ? $a[1] : 0;
            }
            if (empty($sku)){
                return ['result'=>0,'msg'=>'商品链接或sku不正确'];
            }
        }
        $model->search_ip = Yii::$app->request->userIP;
        $model->client_type = Yii::$app->request->post('entrance');
        $model->type = 1;
        $model->keyword = Yii::$app->request->post('keyword');
        $model->city_id = Yii::$app->request->post('city_id');

        $model->user_id = 0;
        if (!Yii::$app->user->isGuest){
            $model->user_id = Yii::$app->user->id;
        }
        $model->sku = $sku;
        $model->page_start = Yii::$app->request->post('page');
        $model->page_end = Yii::$app->request->post('page_size');
        $model->result_sort = Yii::$app->request->post('sort');
        $model->price_min = empty(Yii::$app->request->post('price_min')) ? 0 : Yii::$app->request->post('price_min');
        $model->price_max = empty(Yii::$app->request->post('price_max')) ? 0 : Yii::$app->request->post('price_max');
        $model->sku_list = '';
        $model->city = $city;
        if(!preg_match("/^[0-9][0-9]*$/",$model->price_min) or !preg_match("/^[0-9][0-9]*$/",$model->price_max)){
            return ['result'=>0,'msg'=>'价格格式不正确'];
        }
        //微信查询店铺时获取shop_id
        $shop_ids = [];
        if ($model->client_type==3 and $model->model==2){
            $shop_result = BaseGoods::shopInfo($sku);
            if ($shop_result['result'] != 1){
                return ['result'=>0,'msg'=>$shop_result['msg']];
            }
            $shop_ids[0] = $shop_result['shop_id'];
        }elseif($model->model==2){
            $shop_result = BaseGoods::shopInfo($sku);
            if ($shop_result['result'] != 1){
                return ['result'=>0,'msg'=>$shop_result['msg']];
            }
            $shop_ids[0] = $shop_result['shop_id'];
        }

        //城市为空时随机获取id
        if (empty($model->city_id)){
//            $city_id = ApiCityList::getCityIp();
            $model->city_id = 0;
        }

        //spu等信息采集
        $sku_list = [];


        $time_id = 0;
        if ($model->model == 1){     //指定商品
            $goods = BaseGoods::findOne(['deleted'=>0,'sku'=>$sku]);
            if ($goods and (time() - $goods->update_at) < 86400 * 3){
                $sku_list = Json::decode($goods->sku_list);
                $result = BaseGoods::timeSave(time() - $times,time() - $times);
                $time_id = $result['id'];
            }else{
                $good_result = BaseGoods::goodsSave($sku);
                if ($good_result['result'] == 1){
                    $sku_list = $good_result['sku_list'];
                    $time_id = $good_result['time_id'];
                }else{
                    return ['result'=>0,'msg'=>$good_result['msg']];
                }
            }

        }
        $time_one = time();
        $model->sku_list = Json::encode($sku_list);
        $model->ip = Yii::$app->getRequest()->getUserIP();
        $result = $model->keywordSave();
        if ($result['result'] != 1){
            return ['result'=>0,'msg'=>'查询终止:' . $result['msg']];
        }
        $service_id = $model->id;


        $timeModel = TimeList::findOne(['id' => $time_id]);
        if (empty($timeModel)){
            $timeModel = new TimeList();
        }
        $timeModel->user_id = $model->user_id;
        $timeModel->sku = $sku;
        $timeModel->keyword = $model->keyword;



        //todo.. 舍弃芝麻 全部改用华科
//        $ipLog = IpLog::findOne(['deleted'=>0]);
//        if (! $ipLog){
//            IpLog::updateAll(['is_use'=>0],['deleted'=>0,'is_use'=>1]);
//            $ipLog = IpLog::findOne(['deleted'=>0,'is_use'=>0]);
//        }
//        $ipLog->is_use = 1;
//        if (! $ipLog->save()){
//
//        }
//        $timeModel->ip_id = $ipLog->id;
//        $type = 1;
//        $time_two = time() - $time_one;
//        if ($time_two){
//            $timeModel->ip_time = intval($time_two);
//        }
//        $timeModel->type = $type;

        // 随机数
        if(Yii::$app->user->id){
            $is_user = User::find()->where(['id'=>Yii::$app->user->id])->one();
            if(empty($is_user['random_id'])){
                $querys = Yii::$app->db->createCommand("SELECT * FROM random_list WHERE deleted=0 AND is_type = 0 GROUP BY id ASC limit 1")->queryOne();
                $is_user->random_id = $querys['random'];
                $is_user->save();
            }
        }else{
            $is_user = [];
            $querys = Yii::$app->db->createCommand("SELECT * FROM random_list WHERE deleted=0 AND is_type = 1 limit 1")->queryOne();
            $is_user['random_id'] = $querys['random'];
        }
        $parama = [
            'ip'         => '',
            'port'       => 20,
            'proxy_username' => '',
            'proxy_password' => '',
            'user_id'    => intval($model->user_id),
            'id'         => $model->id,
            'sku'        => $model->sku,
            'sku_list'   => $sku_list,
            'type'       => 1,
            'client_type'=> intval($model->client_type),
            'model'      => intval($model->model),
            'keyword'    => $model->keyword,
            'page_start' => intval($model->page_start),
            'page_end'   => intval($model->page_end),
            'result_sort'=> intval($model->result_sort),
            'price_min'  => empty($model->price_min) ? 0 : $model->price_min * 100,
            'price_max'  => empty($model->price_max) ? 0 : $model->price_max * 100,
            'random' => empty($is_user->random_id) ? $is_user['random_id'] : $is_user->random_id,
            'city' =>  $city,
            'shop_id' => empty($shop_ids) ? '' : $shop_ids[0],
        ];

        if ($model->client_type==3 and $model->model==2){
            $parama['shop_ids'] = $shop_ids;
        }

        $sort_result = ServiceKeywordSearchResult::resultOrderSave($parama,$model->id);

        if($sort_result['result'] == 10){
            if(!empty(Yii::$app->user->id)){
                $id_param = RandomList::find()->where(['random'=>$is_user->random_id])->one();
//                $querys = Yii::$app->db->createCommand("update `random_list` set deleted=1,create_at=unix_timestamp() where id=$id_param->id")->execute();
                $querys = Yii::$app->db->createCommand("select * from random_list where id=(select min(id) from random_list where deleted=0 AND is_type=0 AND id> $id_param->id)")->queryOne();
                $user_d = User::find()->where(['id'=>Yii::$app->user->id])->one();
                $user_d->random_id = $querys['random'];
                $user_d->save();
                $is_user = User::find()->select('random_id')->where(['id'=>Yii::$app->user->id])->one();
            }else{
                $id_param = RandomList::find()->where(['random'=>$is_user['random_id']])->one();
//                $querys = Yii::$app->db->createCommand("update `random_list` set deleted=1,create_at=unix_timestamp() where id=$id_param->id")->execute();
                $querys = Yii::$app->db->createCommand("select * from random_list where id=(select min(id) from random_list  where deleted=0 AND is_type=1 AND id> $id_param->id)")->queryOne();
            }
            $parama = [
                'ip'         => '',
                'port'       => 20,
                'proxy_username' => '',
                'proxy_password' => '',
                'user_id'    => intval($model->user_id),
                'id'         => $model->id,
                'sku'        => $model->sku,
                'sku_list'   => $sku_list,
                'type'       => 1,
                'client_type'=> intval($model->client_type),
                'model'      => intval($model->model),
                'keyword'    => $model->keyword,
                'page_start' => intval($model->page_start),
                'page_end'   => intval($model->page_end),
                'result_sort'=> intval($model->result_sort),
                'price_min'  => empty($model->price_min) ? 0 : $model->price_min * 100,
                'price_max'  => empty($model->price_max) ? 0 : $model->price_max * 100,
                'random' => empty($is_user->random_id) ? $querys['random'] : $is_user->random_id,
                'city' =>  $city,
                'shop_id' => empty($shop_ids) ? '' : $shop_ids[0],
            ];

            if ($model->client_type==3 and $model->model==2){
                $parama['shop_ids'] = $shop_ids;
            }
            $sort_result = ServiceKeywordSearchResult::resultOrderSave($parama,$model->id);
        }

        //  值显示外框内容
        if ($sort_result['result'] == 0){
            return ['result'=>0,'msg'=>$sort_result['msg']];
            //  获取查询省份
//            $city_id = ServiceKeywordSearch::find()->where(['id'=>$service_id])->one();
//            $data = ServiceKeywordSearchResult::getRankData($service_id,$city_id->sku,$city_id->city_id,$parama='');
//
//            //  拼采集接口  商品的规格存储
//            $sku = $city_id->sku;
//            $url = Yii::$app->params['sku_collect'] ."$sku";
//            $curl = new Curl();
//            $collectString = $curl->get($url);
//            $data_s = Json::decode($collectString,false);
//            if($data_s->result == 0){
                $timeModel->err_msg = isset($sort_result['time_err']) ?  $sort_result['time_err'] : '';
                $timeModel->full_time = time() - $times;
                $timeModel->sort_time = $sort_result['time_sort'];
                $timeModel->err_map = isset($sort_result['err_map']) ? $sort_result['err_map'] : '';
                return ['result'=>0,'msg'=>$sort_result['msg']];
//            }
////            var_dump($data_s);exit();
//            //  获取参数
//            $can = $data_s->data->page_config->product;
//            //  商品标题
//            $result = [];
//            $result['good_title'] = $can->name;
//            //  商品地址
//            $result['good_url'] = $can->href;
//            //  商品图片链接
//            $result['good_url'] = $data_s->data->img;
//            //  时间
//
//            $result['create_at'] = $data['data']['create_at'];
//            //  地区名称
//            $city = ApiCityList::findOne(['deleted'=>0,'id' => $city_id]);
//            $result['id'] = 0;
//            $result['keyword'] = $city_id->keyword;
//            $result['type'] = $city_id->type;
//            $result['page'] = '';
//            $result['page_position'] = '';
//            $result['page_order'] = '';
//            $result['weight'] = '';
//            $result['title_weight'] = '';
//            $result['cityName'] = $city->name;
//            $result['comment'] = $can->commentVersion;
//            $result['is_ad'] = 0;
//            $result['is_end'] = 0;
//            $result['is_double11'] = 0;
//            $result['double_price'] = '';
//            $result['promotion_logo'] = '';
//            $result['price'] = '';
//            // 规格
//            //            //  获取参数
//            $specification_can = $data_s->data->page_config->product->colorSize;
//
//            if(empty($specification_can)){
//                $result['specification'] = '';
//            }else{
//                $specification = '';
//                foreach ($specification_can as $k => $y){
//                    if($y->skuId == $sku){
//                        $specification_list = [];
//                        foreach ($y as  $k1 => $y1){
//                            if($k1 == 'skuId'){
//                                continue;
//                            }else{
//                                array_push($specification_list,$y1);
//                            }
//                        }
//                        $specification = implode('',$specification_list);
//                    }else{
//                        continue;
//                    }
//                }
//                $result['specification'] = $specification;
//            }
//
//
//            $data['data']['result'][] = $result;
////            return $data;
//
//            $timeModel->err_msg = isset($sort_result['time_err']) ?  $sort_result['time_err'] : '';
//            $timeModel->full_time = time() - $times;
//            $timeModel->sort_time = $sort_result['time_sort'];
//            $timeModel->err_map = isset($sort_result['err_map']) ? $sort_result['err_map'] : '';
//            if (! $timeModel->save()){
//
//            }
//            return $data;
//            return ['result'=>0,'msg'=>$sort_result['msg']];
        }

        //  修改数量 重复数
//        if(Yii::$app->user->id == 3276){
            if(strpos($model->keyword,"'")){
                $keyword = str_replace("'","%",$model->keyword);
                $keyword = "AND keyword like '%$model->keyword%'";
//                    var_dump($keyword);exit();
            }else{
                $keyword =  "AND keyword='$model->keyword'";
            }
            $uid = Yii::$app->user->id;
            $querys_ss = Yii::$app->db->createCommand("select count(id) from  `service_keyword_search` WHERE sku='$model->sku'AND  user_id=$uid AND type=1 $keyword")->queryScalar();
            $querys_ss = Yii::$app->db->createCommand("UPDATE `service_keyword_search` SET user_memo=$querys_ss WHERE sku='$model->sku'AND user_id = $uid $keyword   AND type=1")->execute();
//        }

        $timeModel->err_msg = $sort_result['time_err'];
        $timeModel->sort_time = 0;
        $timeModel->err_map = '';

        $data = ServiceKeywordSearchResult::getRankData($service_id,$model->sku,$model->city_id,$sort_result['main_sku'],'',2);

        $timeModel->full_time = time() - $times;

        if (! $timeModel->save()){

        }

        return $data;

    }

    /**
     * 权重
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionWeightSearch()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        //判断当前用户的查排名的次数是否足够
        $limit = User::getLeftTimesResult(2);
        if($limit['result'] == 0 ){
            return $limit;
        }

        if (Yii::$app->user->isGuest){
            return ['result'=>0,'msg'=>'用户未登录'];
        }

        if (! Yii::$app->request->post()){
            return ['result'=>0,'msg'=>'非法请求'];
        }

        $model = new ServiceKeywordSearch();

        $sku = Yii::$app->request->post('sku');
        if (! is_numeric($sku)){

            $sku = preg_match('/([0-9]{5,30})/',$sku,$a) ? $a[1] : 0;
        }
        if (empty($sku)){
            return ['result'=>0,'msg'=>'商品链接或sku不正确'];
        }
        $city = Yii::$app->request->post('city','1,72,2799');
        $model->search_ip = Yii::$app->request->userIP;
        $model->sku = $sku;
        $model->type = 2;
        $model->keyword = Yii::$app->request->post('keyword');
        $model->user_id = Yii::$app->user->id ? Yii::$app->user->id : 0;
        $model->weight_min = Yii::$app->request->post('weight_min');
        $model->weight_max = Yii::$app->request->post('weight_max');
        $model->price_min = empty(Yii::$app->request->post('price_min')) ? 0 : Yii::$app->request->post('price_min');
        $model->price_max = empty(Yii::$app->request->post('price_max')) ? 0 : Yii::$app->request->post('price_max');
        if(!preg_match("/^[0-9][0-9]*$/",$model->price_min) or !preg_match("/^[0-9][0-9]*$/",$model->price_max)){
            return ['result'=>0,'msg'=>'价格格式不正确'];
        }
        $model->city = $city;
        //权重随机获取城市
        $city_id = ApiCityList::getCityIp();
        $model->city_id = $city_id;


        //spu等信息采集

        $sku_list = [];
        $goods = BaseGoods::findOne(['deleted'=>0,'sku'=>$sku]);
        if ($goods and (time() - $goods->update_at) < 86400 * 3){
            $sku_list = Json::decode($goods->sku_list);
        }else{
            $good_result = BaseGoods::goodsSave($sku,$type=2);
            if ($good_result['result'] == 1){
                $sku_list = $good_result['sku_list'];
            }
        }

        $model->sku_list = Json::encode($sku_list);
        $result = $model->keywordSave();
        if ($result['result'] != 1){
            return ['result'=>0,'msg'=>'查询终止:' . $result['msg']];
        }
        $service_id = $model->id;

        //todo.. 舍弃芝麻 全部改用华科
        $ipLog = IpLog::findOne(['deleted'=>0,'is_use'=>0]);
        if (! $ipLog){
            IpLog::updateAll(['is_use'=>0],['deleted'=>0,'is_use'=>1]);
            $ipLog = IpLog::findOne(['deleted'=>0,'is_use'=>0]);
        }
        $ipLog->is_use = 1;
        if (! $ipLog->save()){

        }
        // 随机数
        if(Yii::$app->user->id){
            $is_user = User::find()->where(['id'=>Yii::$app->user->id])->one();
            if(empty($is_user['random_id'])){
                $querys = Yii::$app->db->createCommand("SELECT * FROM random_list WHERE deleted=0 AND is_type = 0 GROUP BY id ASC limit 1")->queryOne();
                $is_user->random_id = $querys['random'];
                $is_user->save();
            }
        }else{
            $is_user = [];
            $querys = Yii::$app->db->createCommand("SELECT * FROM random_list WHERE deleted=0 AND is_type = 1 limit 1")->queryOne();
            $is_user['random_id'] = $querys['random'];
        }
        $parama = [
            'ip'         => $ipLog->ip,
            'port'       => intval($ipLog->port),
            'proxy_username' => isset($ipLog->account)?$ipLog->account:'',
            'proxy_password' => isset($ipLog->password)?$ipLog->password:'',
            'user_id'    => intval($model->user_id),
            'id'         => $model->id,
            'sku'        => $model->sku,
            'sku_list'   => $sku_list,
            'type'       => 2,
            'keyword'    => $model->keyword,
            'price_min'  => empty($model->price_min) ? 0 : $model->price_min * 100,
            'price_max'  => empty($model->price_max) ? 0 : $model->price_max * 100,
            'weight_min' => intval($model->weight_min),
            'weight_max' => intval($model->weight_max),
            'random' => empty($is_user->random_id) ? $is_user['random_id'] : $is_user->random_id,
        ];
        $weight_result = ServiceKeywordSearchResult::resultWeightSave($parama,$sku,$service_id,$model->keyword);
        if ($weight_result['result'] == 10){
            if(Yii::$app->user->id){
                $id_param = RandomList::find()->where(['random'=>$is_user->random_id])->one();
//                $querys = Yii::$app->db->createCommand("update `random_list` set deleted=1,create_at=unix_timestamp() where id=$id_param->id")->execute();
                $querys = Yii::$app->db->createCommand("select * from random_list where id=(select min(id) from random_list where deleted=0 AND is_type=0 AND id> $id_param->id)")->queryOne();
            }else{
                $id_param = RandomList::find()->where(['random'=>$is_user['random_id']])->one();
//                $querys = Yii::$app->db->createCommand("update `random_list` set deleted=1,create_at=unix_timestamp() where id=$id_param->id")->execute();
                $querys = Yii::$app->db->createCommand("select * from random_list where id=(select min(id) from random_list  where deleted=0 AND is_type=1 AND id> $id_param->id)")->queryOne();
            }
            $user_d = User::find()->where(['id'=>Yii::$app->user->id])->one();
            $user_d->random_id = $querys['random'];
            $user_d->save();
            $is_user = User::find()->select('random_id')->where(['id'=>Yii::$app->user->id])->one();

            $parama = [
                'ip'         => $ipLog->ip,
                'port'       => intval($ipLog->port),
                'proxy_username' => isset($ipLog->account)?$ipLog->account:'',
                'proxy_password' => isset($ipLog->password)?$ipLog->password:'',
                'user_id'    => intval($model->user_id),
                'id'         => $model->id,
                'sku'        => $model->sku,
                'sku_list'   => $sku_list,
                'type'       => 2,
                'keyword'    => $model->keyword,
                'price_min'  => empty($model->price_min) ? 0 : $model->price_min * 100,
                'price_max'  => empty($model->price_max) ? 0 : $model->price_max * 100,
                'weight_min' => intval($model->weight_min),
                'weight_max' => intval($model->weight_max),
                'random' => empty($is_user->random_id) ? $querys['random'] : $is_user->random_id,
            ];
            $weight_result = ServiceKeywordSearchResult::resultWeightSave($parama,$sku,$service_id,$model->keyword);
        }
        if ($weight_result['result'] == 0){
            return ['result'=>0,'msg'=>$weight_result['msg']];
        }
        //  修改数量 重复数
//        if(Yii::$app->user->id == 3276){
            if(strpos($model->keyword,"'")){
                $keyword = str_replace("'","%",$model->keyword);
                $keyword = "AND keyword like '%$model->keyword%'";
    //                    var_dump($keyword);exit();
            }else{
                $keyword =  "AND keyword='$model->keyword'";
            }
            $uid = Yii::$app->user->id;
            $querys_ss = Yii::$app->db->createCommand("select count(id) from  `service_keyword_search` WHERE sku='$model->sku'AND user_id=$uid AND type=2 $keyword")->queryScalar();
            $querys_ss = Yii::$app->db->createCommand("UPDATE `service_keyword_search` SET user_memo=$querys_ss WHERE sku='$model->sku'AND user_id = $uid $keyword   AND type=2")->execute();
//        }
//        $data = ServiceKeywordSearchResult::getWeightData($service_id,$model->weight_min,$model->weight_max,$model->sku);
        $data = ServiceKeywordSearchResult::weightInfo($service_id,$model->sku,'WeightSearch');
        return $data;

    }
}
?>