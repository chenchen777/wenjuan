<?php

namespace common\models;

use linslin\yii2\curl\Curl;
//use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "monitor_keyword_result".
 *
 * @property int $id
 * @property int $user_id
 * @property int $keyword_id
 * @property int $good_id
 * @property string $sku
 * @property int $page_app app端页码
 * @property int $page_position_app app端位数
 * @property int $page_order_app app端总排名
 * @property int $page_pc pc端页码
 * @property int $page_position_pc pc端位数
 * @property int $page_order_pc pc端总排名
 * @property int $rank_change_app app排名变化
 * @property int $rank_change_pc pc排名变化
 * @property int $weight 权重分
 * @property int $weight_change 权重分变化
 * @property int $title_weight 标题权重分
 * @property int $app_search_at app查询时间
 * @property int $pc_search_at pc查询时间
 * @property string $err_msg
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class MonitorKeywordResult extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'monitor_keyword_result';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'keyword_id', 'good_id', 'page_app', 'page_position_app', 'page_order_app', 'page_pc', 'page_position_pc', 'page_order_pc', 'rank_change_app', 'rank_change_pc', 'weight', 'weight_change', 'title_weight', 'app_search_at', 'pc_search_at', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['sku'],'string'],
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
            'keyword_id' => 'Keyword ID',
            'good_id' => 'Good ID',
            'sku' => 'Sku',
            'page_app' => 'Page App',
            'page_position_app' => 'Page Position App',
            'page_order_app' => 'Page Order App',
            'page_pc' => 'Page Pc',
            'page_position_pc' => 'Page Position Pc',
            'page_order_pc' => 'Page Order Pc',
            'rank_change_app' => 'Rank Change App',
            'rank_change_pc' => 'Rank Change Pc',
            'weight' => 'Weight',
            'weight_change' => 'Weight Change',
            'title_weight' => 'Title Weight',
            'app_search_at' => 'App Search At',
            'pc_search_at' => 'Pc Search At',
            'err_msg' => 'Err Msg',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    //  $is_history 是否有7天内的数据
    public static function server($parama='',$is_history=''){
        $time = time();
        $country_info = [];

        //  获取ip
        if($oneIP = \Yii::$app->jdrqds->createCommand("SELECT id,ip,port FROM `ip_list` WHERE is_use=0 AND deleted=0 ORDER BY RAND() LIMIT ".$parama['page_end'])->queryAll()){}else{}
        if(empty($oneIP)){
            return ['list'=>'','msg'=>'系统繁忙请稍后再试','time_sort'=>time() - $time,'time_err'=>'系统繁忙请稍后再试' . '..','err_map' => '系统繁忙请稍后再试'];
        }
        $ip_list = [];
        $ip_id = [];
        foreach($oneIP as $k => $y){
            $ip_id[] = $y['id'];
            $ip = implode(':',$y);
            array_push($ip_list,$ip);
        }
//        $user='proxy';
//        $orderId = "O19120413453507425206";					#用户编号
//        $secret = "91fc04028c4eafb067457685d0c58fa7";			#秘钥
//        $timestamp=time();		#当前时间戳
//        $sign = strtolower(md5('orderId='.$orderId.'&secret='.$secret.'&time='.$timestamp));
//        $password = implode('&', [
//            'orderId=' . $orderId,
//            'time=' . $timestamp,
//            'sign=' . $sign,
//        ]);
//
//        $parama['proxy_list'] = $user.':'.$password.'@flow.hailiangip.com:'.'14223';
//        $i =0;
//        $ip_list = [];
//        do{
//            $ip_list[] = $parama['proxy_list'];
//            $i++;
//        }while($i< 50);
        $parama['proxy_list'] = $ip_list;
//        $ip_id = implode(',',$ip_id);
//        \Yii::$app->db->createCommand("UPDATE  `ip_list` set is_use = 1  WHERE id in ($ip_id)")->execute();

        $ip_list = Yii::$app->params['rank_pc'] .'v1/order';
        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT,90);
        $postString = $curl->setRequestBody(Json::encode($parama))->post($ip_list);
        try{
            $country_info[] = Json::decode($postString);
        }catch (\Exception $e){

        }
        return $country_info;

    }

    /**
     * 商品排名查询
     * @param string $search_type 查询方式 1查询当前sku 2查询最高
     * @param $good_id
     * @param $sku_list
     * @param $city
     * @return array
     */
    public static function rankSearch($search_type, $good_id, $sku_list, $city = '')
    {
//        try{
            $count = MonitorKeyword::find()
                ->where(['deleted'=>0,'monitor_close'=>0,'monitor_goods_id'=>$good_id,'is_update_pc'=>0])
                ->count();

            if (!$count){
                return ['result'=>1];
            }
            $cur_time = strtotime(date('Y-m-d',time()));

            $keywords = MonitorKeyword::find()
                ->where(['deleted'=>0,'monitor_close'=>0,'monitor_goods_id'=>$good_id,'is_update_pc'=>0])
                ->andWhere(['>','create_at',$cur_time])
                ->all();

            foreach ($keywords as $keyword){
                /* @var $keyword MonitorKeyword*/
                //pc查询
                $mode = 1;
                $parama = self::paramaGet($keyword->id, $keyword->sku, $keyword->keyword, $mode, $search_type, $sku_list,'pc');
                $parama['city'] = strtr($city, ',', '-').'-0';

//                try{
//                    $time_start = time()-(3600*24*7);
//                    $is_history = MonitorKeywordResult::find()->where(['sku'=>$parama['sku']])->andWhere(['>','create_at',$time_start])->orderBy('id desc')->one();
                $list = self::server($parama,$is_history='');
//                }catch (\Exception $e){
//                    return ['result'=>0,'msg'=>'排名查询超时，请稍后重试'];
//                }

                //初次查找，新增数据
                $model = new MonitorKeywordResult();
                $model->user_id = Yii::$app->user->id;
                $model->keyword_id = $keyword->id;
                $model->good_id = $keyword->monitor_goods_id;
                $model->pc_search_at = time();
                //请求 查找为空
//                if (empty($list)){
//                    $model->err_msg = '结果不在搜索范围内.';
//                }

                if($list){
                    $list_s = [];
                    foreach($list as $k => $y){
                        //请求返回正确数据 存表
//                        $data = $y['result'][0];  //search_type为1时；
                        $lists = $y['result'];
                        if(!$lists){
                            return ['result'=>1,'msg'=>'结果不在搜索范围内'];
                        }
                        foreach($lists as $k1 => $y1){
                            if($y1['code'] == 1){
                                $temp = [
                                    'sku' => $y1['sku'],
                                    'page_pc' => $y1['result_page'],
                                    'page_order_pc' => $y1['result_order'] <0 ? $y1['result_order']+30 : $y1['result_order'],
                                    'page_position_pc' => $y1['result_page_order'] <0 ? $y1['result_page_order']=30 : $y1['result_page_order'],
                                ];
                                array_push($list_s,$temp);
                            }else if($y1['code'] == 2){
                                if (strpos($y1['error_message'],'结果不在') !==false) {
//                                    $model->err_msg = $y1['error_message'];
                                }
                            } else{
                                continue;
                            }
                        }
                    }

                    if(empty($list_s)){
                        $model->err_msg = '结果不在搜索范围内.';
                    }
                    if(empty($list_s)){
                        $model->err_msg = '结果不在搜索范围内.';
//                        return ['result'=>0,'msg'=>'结果不在搜索范围内'];
                    }else{
                        $model->sku = $list_s[0]['sku'];
                        $model->page_pc = $list_s[0]['page_pc'];
                        $model->page_order_pc = $list_s[0]['page_order_pc'] < 0 ? $list_s[0]['page_order_pc']+30 : $list_s[0]['page_order_pc'];
                        $model->page_position_pc = $list_s[0]['page_position_pc'] < 0 ? $list_s[0]['page_position_pc']+30 : $list_s[0]['page_position_pc'];

                        $keyword->is_update_pc = 1;
                        $keyword->save();
                    }
                    if (!$model->save()){
                        print_r($model->getError());exit();
                    }
                }else{
                    return ['result'=>1,'msg'=>'结果不在搜索范围内'];
                }

            }

        return ['result'=>1];
//        }catch (\Exception $e){
//            return ['result'=>0,'msg'=>$e->getMessage()];
//        }
    }

    public static function HttpGetApp($paramaa){
        $url1 = Yii::$app->params['go_app_ranking'];
        $paramaa['page'] ="1";
        $paramaa['page_end'] ="50";
        $paramaa['sku_list'] =$paramaa['sku'];
        $paramaa['sort'] = $paramaa['sort'];
        //  使用ip
        if($oneIP  = Yii::$app->jdrqds->createCommand("SELECT id,ip,port FROM ip_list WHERE is_app_pass=1 AND is_app_use=0 order by rand() limit 50")->queryAll()){}else{
        }

        if(empty($oneIP)){
            return ['list'=>'','msg'=>'系统繁忙请稍后再试','time_sort'=>time(),'time_err'=>'系统繁忙请稍后再试' . '..','err_map' => '系统繁忙请稍后再试'];
        }
        $ip_list = [];
        $ip_id = [];
        foreach($oneIP as $k => $y){
            $ip_id[] = $y['id'];
            $ip = $y['ip'];
            $port = $y['port'];
            $ip = $ip.':'.$port;
            array_push($ip_list,$ip);
        }
        $ip_id = implode(",",$ip_id);
        $ip_list = implode(",",$ip_list);

        //  更新ip状态
        \Yii::$app->jdrqds->createCommand("UPDATE  `ip_list` set is_app_use = 1  WHERE id in ($ip_id)")->execute();
        $paramaa['ip'] = $ip_list;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $paramaa);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);

        curl_close($ch);
        $response = json_decode($response);
        return $response;
    }
    public static function goodInfo($proxy,$url,$headers){
        $cUrl = curl_init($url);
        curl_setopt($cUrl, CURLOPT_URL, $url);
        curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cUrl, CURLOPT_TIMEOUT, 10);
        curl_setopt($cUrl, CURLOPT_HTTPHEADER,$headers);
        curl_setopt($cUrl, CURLOPT_PROXY, $proxy); //做代理的ip和端口
        curl_setopt($cUrl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($cUrl, CURLOPT_SSL_VERIFYHOST, FALSE);

        $pageContent = curl_exec($cUrl);
        $err = curl_error($cUrl);
        if(!empty($err)){
            return ['code'=>-1];
        }
        curl_close($cUrl);
        $goodsresult = Json::decode($pageContent,true);
        return $goodsresult;
    }
    //商品排名、权重查询(app)
    public static function weightSearch($search_type,$good_id,$sku_list,$city = '')
    {

        try{

            $count = MonitorKeyword::find()
                ->where(['deleted'=>0,'monitor_close'=>0,'monitor_goods_id'=>$good_id,'is_update_app'=>0])->count();

//            if (!empty($count)){
//                return ['result'=>1];
//            }

            $cur_time = strtotime(date('Y-m-d',time()));
            $keywords = MonitorKeyword::find()
                ->where(['deleted'=>0,'monitor_close'=>0,'monitor_goods_id'=>$good_id,'is_update_app'=>0])
                ->andWhere(['>','create_at',$cur_time])->all();

            if(Yii::$app->user->id == 30544){
//                var_dump($good_id);exit();
            }
            foreach ($keywords as $keyword){

                //app查询
                $mode = 2;
                $parama = self::paramaGet($keyword->id,$keyword->sku,$keyword->keyword,$mode,$search_type,$sku_list);
                $parama['city'] = strtr($city, ',', '_');

                // // 查看选择什么类型接口  1新版 0老版
//                    $redis = \Yii::$app->redis;
//                    $is_type = $redis->get('AppRanking');
                $is_type = 0;
                if($is_type == 1){
                    $paramaa['sku'] =$parama['sku'];
                    $paramaa['keyword'] =$parama['keyword'];
                    $paramaa['sort'] = 1;
                    $response = self::HttpGetApp($paramaa);
                    if(empty($response->result->page_position_app)){
                        $response = self::HttpGetApp($paramaa);
                    }
                    $datas = $response->result[0];
                    $model = MonitorKeywordResult::findOne(['deleted'=>0,'keyword_id'=>$keyword->id]);
                    if(empty($model)){
                        //初次查找，新增数据
                        $model = new MonitorKeywordResult();
                        $model->user_id = Yii::$app->user->id;
                        $model->keyword_id = $keyword->id;
                        $model->good_id = $keyword->monitor_goods_id;
                    }
                    $model->app_search_at = time();
                    if ($response->success == 200){
                        $model->sku = $datas->sku;
                        $model->page_app = empty($datas->page_app) ? 0 : $datas->page_app;
                        $model->page_order_app = empty($datas->page_order_app) ?0 : $datas->page_order_app;
                        $model->page_position_app = empty($datas->page_position_app) ? 0 : $datas->page_position_app;
                        $model->weight = isset($datas->result_weight_score) ? 0 : $datas->result_weight_score;
                    }else{
                        return ['result'=>0,'msg'=>'查询失败，重新查询'];
                    }

                    if (!$model->save()){
                        print_r($model->getError());exit();
                    }

                    $keyword->is_update_app = 1;
                    $keyword->save();

                }else{
                    if(Yii::$app->params['JDBS'] == 1){

                        $time = time();
                        $user_agent = Yii::$app->db->createCommand("SELECT user_agent FROM sys_user_agent order by rand() limit 1")->queryAll();
                        $url = "https://www.jdboshi.com/api/queryModel/queryRankingNew.bs?keyword=".urlencode($parama['keyword'])."&sku=".$parama['sku']."&sort=".'0'."&equipment=1";
                        $oneIP = \Yii::$app->db->createCommand("SELECT ip,port FROM `ip_list` WHERE is_app_use=0 and is_app_pass=1 AND deleted=0 ORDER BY RAND() LIMIT 1")->queryAll();
                        if(empty($oneIP)){
                            return ['list'=>'','msg'=>'系统繁忙请稍后再试','time_sort'=>time() - $time,'time_err'=>'系统繁忙请稍后再试' . '..','err_map' => '系统繁忙请稍后再试'];
                        }
                        $ip_list = [];
                        foreach($oneIP as $k => $y){
                            $ip = implode(':',$y);
                            array_push($ip_list,$ip);
                        }
                        $proxy = $ip_list[0];
                        $headers = [
                            "Content-type:application/json;charset=utf-8",
                            "Accept:application/json",
                            "User-Agent:".$user_agent[0]['user_agent'],
                            "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
                            "Accept-Encoding:gzip, deflate, br",
                            "Accept-Language:zh-CN,zh;q=0.9",
                            "Connection:keep-alive",
                            "Host:www.jdboshi.com",
                            "Sec-Fetch-Dest:document",
                            "Sec-Fetch-Mode:navigate",
                            "Sec-Fetch-Site:none",
                            "Upgrade-Insecure-Requests:1"
                        ];
                        $nameUrl = "https://www.jdboshi.com/api/index/recordPageName.bs?pageName=%E6%8E%92%E5%90%8D%E6%9F%A5%E8%AF%A2";
                        $goodresult = self::goodInfo($proxy,$nameUrl,$headers);
                        $result = self::goodInfo($proxy,$url,$headers);

                        if($result['code'] == -1){
//                                return ['result'=>0,'msg'=>'当前sku不在所查页数范围内','time_sort'=>0];
//                                $datas = $result['data'];
                            $model = MonitorKeywordResult::findOne(['deleted'=>0,'keyword_id'=>$keyword->id]);
                            if(empty($model)){
                                //初次查找，新增数据
                                $model = new MonitorKeywordResult();
                                $model->user_id = Yii::$app->user->id;
                                $model->keyword_id = $keyword->id;
                                $model->good_id = $keyword->monitor_goods_id;
                            }

//                                $result = $result['data']['app'];
                            $model->app_search_at = time();
                            $model->sku = $parama['sku'];
                            $model->page_app = 0;
                            $model->page_order_app = 0;
                            $model->page_position_app = 0;
                            $model->weight = 0;
                        }elseif($result['code'] != 200){
                            return ['result'=>0,'msg'=>'当前sku不在所查页数范围内','time_sort'=>0];
                        }else{
                            $datas = $result['data'];
                            $model = MonitorKeywordResult::findOne(['deleted'=>0,'keyword_id'=>$keyword->id]);
                            if(empty($model)){
                                //初次查找，新增数据
                                $model = new MonitorKeywordResult();
                                $model->user_id = Yii::$app->user->id;
                                $model->keyword_id = $keyword->id;
                                $model->good_id = $keyword->monitor_goods_id;
                            }

                            $result = $result['data']['app'];
                            $model->app_search_at = time();
                            $model->sku = $parama['sku'];
                            $model->page_app = (int)ceil($result['ranking']/10);
                            $model->page_order_app = (int)$result['ranking'];
                            $model->page_position_app = (int)$result['ranking']%10 == 0 ? 10: (int)$result['ranking']%10;
                            $model->weight = 0;
                        }

                        if (!$model->save()){
                            print_r($model->getError());exit();
                        }
                    }else{
                        $time =time();
                        $parama['page_end'] = empty($parama['page_end']) ? 50 : $parama['page_end'];
                        if($oneIP = \Yii::$app->db->createCommand("SELECT id,ip,port FROM `ip_list` WHERE is_app_use=0 and is_app_pass=1 AND deleted=0 ORDER BY RAND() LIMIT ".$parama['page_end'])->queryAll()){}else{}
                        if(empty($oneIP)){
                            return ['list'=>'','msg'=>'系统繁忙请稍后再试','time_sort'=>time() - $time,'time_err'=>'系统繁忙请稍后再试' . '..','err_map' => '系统繁忙请稍后再试'];
                        }

                        $ip_list = [];
                        $ip_id = [];
                        foreach($oneIP as $k => $y){
                            $ip_id[] = $y['id'];
                            $ip = implode(':',$y);
                            array_push($ip_list,$ip);
                        }

                        $ip_id = implode(',',$ip_id);
//              更新ip状态
                        \Yii::$app->db->createCommand("UPDATE  `ip_list` set is_use = 1  WHERE id in ($ip_id)")->execute();
                        $parama['proxy_list'] = $ip_list;
                        try{
                            $url = Yii::$app->params['app_rank_url'] . 'v1/order';
                            $curl = new Curl();
                            $curl->setOption(CURLOPT_TIMEOUT,90);
                            $postString = $curl->setRequestBody(Json::encode($parama))->post($url);
                            $result = Json::decode($postString,false);
                        }catch (\Exception $e){
                            return ['result'=>0,'msg'=>'客户端排名查询超时，请稍后重试'];
                        }

                        $model = MonitorKeywordResult::findOne(['deleted'=>0,'keyword_id'=>$keyword->id]);
                        if(empty($model)){
                            //初次查找，新增数据
                            $model = new MonitorKeywordResult();
                            $model->user_id = Yii::$app->user->id;
                            $model->keyword_id = $keyword->id;
                            $model->good_id = $keyword->monitor_goods_id;
                        }
                        $model->app_search_at = time();
                        //请求 查找为空
                        if ($result->code == 0){
                            $model->err_msg = '指定页面未找到商品';
                        }
                        //请求返回正确数据 存表
                        $data = $result->result[0];  //search_type为1时；
                        if ($data->code == 3){
                            return ['result'=>10,'msg'=>'当前sku不在所查页数范围内'];
                        }
                        $lists = $result->result;
                        if ($search_type==2){        //search_type为2时,找出排名最高的;
                            $index = 0;
                            foreach ($lists as $list){
                                if ($index==0){
                                    $index++;
                                    continue;
                                }
                                $index++;
                                if ($list->code !=1){
                                    continue;
                                }
                                if (empty($data->result_order) or $data->result_order > $list->result_order){
                                    $data = $list;
                                }

                            }
                        }

                        if ($data->code == 1){
                            $model->sku = $data->sku;
                            $model->sku = $data->sku;
                            $model->page_app = $data->result_page;
                            $model->page_order_app = $data->result_order;
                            $model->page_position_app = $data->result_page_order;
                            $model->weight = $data->result_weight_score;
                        }else if ($data->code == 2){
                            if (strpos($data->error_message,'结果不在') !==false){
                                $model->err_msg = $data->error_message;
                            }else{
                                return ['result'=>0,'msg'=>'排名查询失败，请重新查询'];
                            }
                        }else{
                            return ['result'=>0,'msg'=>'查询失败，重新查询'];
                        }

                        if (!$model->save()){
                            print_r($model->getError());exit();
                        }
                    }

                    $keyword->is_update_app = 1;
                    $keyword->save();
                }
            }
            return ['result'=>1];

        }catch (\Exception $e){
            return ['result'=>0,'msg'=>$e->getMessage()];
        }
    }

    public static function paramaGet($keyword_id,$sku,$keyword,$mode,$search_type,$list,$type_pc='')
    {
        $ipLog = IpLog::findOne(['deleted'=>0,'is_use'=>0]);
        if (! $ipLog){
            IpLog::updateAll(['is_use'=>0],['deleted'=>0,'is_use'=>1]);
            $ipLog = IpLog::findOne(['deleted'=>0,'is_use'=>0]);
        }
        $ipLog->is_use = 1;
        if (! $ipLog->save()){

        }
        $sku_list = [];
        if($type_pc == 'pc'){
//            $sku_list = $list;
            if ($search_type==2 && $list){
                if(count($list) == 1){
                    $sku_list = Json::decode($list,true);
                }else{
                    $sku_list = $list;
                }
            }elseif ($search_type==1 && $list){

            }
        }else{
            if ($search_type==2 && $list){
                if(count($list) == 1){
                    $sku_list = Json::decode($list,true);
                }else{
                    $sku_list = $list;
                }
            }elseif ($search_type==1 && $list){

            }
        }

        // 随机数
        $userInfo = User::findOne(Yii::$app->user->id);
        $userInfo->updateUserRandom();

        $parama = [
            //  协议关闭
            'ip'         => $ipLog->ip,
            'port'       => intval($ipLog->port),
            'proxy_username' => isset($ipLog->account)?$ipLog->account:'',
            'proxy_password' => isset($ipLog->password)?$ipLog->password:'',
            'user_id'    => intval(Yii::$app->user->id),
            'id'         => intval($keyword_id),
            'sku'        => $sku,
            'sku_list'   => $sku_list,
            'type'       => 1,
            'client_type'=> $mode,  //1电脑端 2移动端
            'model'      => 1,
            'keyword'    => $keyword,
            'page_start' => 1,
            'page_end'   => 50,
            'result_sort'=> 1,
            'price_min'  => 0,
            'price_max'  => 0,
            'random' => $userInfo->random_id,
            'time_at' => time(),
        ];
        $user_agent = Yii::$app->db->createCommand("SELECT * FROM sys_user_agent order by rand() limit 1")->queryOne()['user_agent'];
        $parama['ua'] = $user_agent;
        return $parama;
    }

    public static function categoryGet($keyword_id,$good_id,$sku,$cat,$user_id='')
    {
        try{
            $url = Yii::$app->params['rank_url'] . 'v1/cat';

            $ipLog = IpLog::getOneIp();
            $params = [
                'ip' => $ipLog->ip,
                'port' => intval($ipLog->port),
                'proxy_username' => $ipLog->account,
                'proxy_password' => $ipLog->password,
                'sku' => $sku,
                'cat' => $cat
            ];

            $curl = new Curl();
            $curl->setOption(CURLOPT_TIMEOUT, 90);
            $postString = $curl->setRequestBody(Json::encode($params))->post($url);
            $data = Json::decode($postString, false);

            $model = new MonitorKeywordResult();
            if ($data->code != 1){
                $model->err_msg = '结果不在查询范围内';
            }else{
                $result = $data->result[0];
                $model->page_pc = $result->result_page?$result->result_page:0;
                $model->page_position_pc = $result->result_page_order?$result->result_page_order:0;
                $model->page_order_pc = $result->result_order?$result->result_order:0;
                $model->weight = $result->result_weight_score?$result->result_weight_score:0;
                $model->title_weight = $result->result_title_weight_score?$result->result_title_weight_score:0;
            }
            $model->user_id = $user_id ? $user_id : Yii::$app->user->id;
            $model->keyword_id = $keyword_id;
            $model->good_id = $good_id;
            if (!$model->save()){
                return ['result'=>0,'msg'=>$model->getError()];
            }
            return ['result'=>1];
        }catch (\Exception $e){
            return ['result'=>0,'msg'=>$e->getMessage()];
        }
    }

    public function getUser()
    {
        return $this->hasOne(User::className(),['id' => 'user_id']);
    }

    public function getGood()
    {
        return $this->hasOne(MonitorGoods::className(),['id'=>'good_id']);
    }
    public function getKeyword()
    {
        return $this->hasOne(MonitorKeyword::className(),['id'=>'keyword_id']);
    }

    // 获取浏览器
    // 获取用户系统
    public static function getOs(){
        $agent = $_SERVER['HTTP_USER_AGENT'];

        //window系统
        if (stripos($agent, 'window')) {
            $os = 'Windows';
            $equipment = '电脑';
            if (preg_match('/nt 6.0/i', $agent)) {
                $os_ver = 'Vista';
            }elseif(preg_match('/nt 10.0/i', $agent)) {
                $os_ver = '10';
            }elseif(preg_match('/nt 6.3/i', $agent)) {
                $os_ver = '8.1';
            }elseif(preg_match('/nt 6.2/i', $agent)) {
                $os_ver = '8.0';
            }elseif(preg_match('/nt 6.1/i', $agent)) {
                $os_ver = '7';
            }elseif(preg_match('/nt 5.1/i', $agent)) {
                $os_ver = 'XP';
            }elseif(preg_match('/nt 5/i', $agent)) {
                $os_ver = '2000';
            }elseif(preg_match('/nt 98/i', $agent)) {
                $os_ver = '98';
            }elseif(preg_match('/nt/i', $agent)) {
                $os_ver = 'nt';
            }else{
                $os_ver = '';
            }
//            if (preg_match('/x64/i', $agent)) {
//                $os .= '(x64)';
//            }elseif(preg_match('/x32/i', $agent)){
//                $os .= '(x32)';
//            }
        }
        elseif(stripos($agent, 'linux')) {
            if (stripos($agent, 'android')) {
                preg_match('/android\s([\d\.]+)/i', $agent, $match);
                $os = 'Android';
                $equipment = 'Mobile phone';
                $os_ver = $match[1];
            }else{
                $os = 'Linux';
            }
        }
        elseif(stripos($agent, 'unix')) {
            $os = 'Unix';
        }
        elseif(preg_match('/iPhone|iPad|iPod/i',$agent)) {
            preg_match('/OS\s([0-9_\.]+)/i', $agent, $match);
            $os = 'IOS';
            $os_ver = str_replace('_','.',$match[1]);
            if(preg_match('/iPhone/i',$agent)){
                $equipment = 'iPhone';
            }elseif(preg_match('/iPad/i',$agent)){
                $equipment = 'iPad';
            }elseif(preg_match('/iPod/i',$agent)){
                $equipment = 'iPod';
            }
        }
        elseif(stripos($agent, 'mac os')) {
            preg_match('/Mac OS X\s([0-9_\.]+)/i', $agent, $match);
            $os = 'Mac OS X';
            $equipment = '电脑';
            $os_ver = str_replace('_','.',$match[1]);
        }
        else {
            $os = 'Other';
        }
        return ['os'=>$os, 'os_ver'=>$os_ver, 'equipment'=>$equipment];
    }

    // 获取客户端浏览器以及版本号
    public static function getBrowse(){
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $browser = '';
        $browser_ver = '';
        if (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs)) {
            $browser = 'OmniWeb';
            $browser_ver = $regs[2];
        }
        if (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'Netscape';
            $browser_ver = $regs[2];
        }
        if (preg_match('/safari\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'Safari';
            $browser_ver = $regs[1];
        }
        if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs)) {
            $browser = 'Internet Explorer';
            $browser_ver = $regs[1];
        }
        if (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs)) {
            $browser = 'Opera';
            $browser_ver = $regs[1];
        }
        if (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs)) {
            $browser = '(Internet Explorer '.$browser_ver.') NetCaptor';
            $browser_ver = $regs[1];
        }
        if (preg_match('/Maxthon/i', $agent, $regs)) {
            $browser = '(Internet Explorer '.$browser_ver.') Maxthon';
            $browser_ver = '';
        }
        if (preg_match('/360SE/i', $agent, $regs)) {
            $browser = '(Internet Explorer '.$browser_ver.') 360SE';
            $browser_ver = '';
        }
        if (preg_match('/SE 2.x/i', $agent, $regs)) {
            $browser = '(Internet Explorer '.$browser_ver.') 搜狗';
            $browser_ver = '';
        }
        if (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'FireFox';
            $browser_ver = $regs[1];
        }
        if (preg_match('/Lynx\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'Lynx';
            $browser_ver = $regs[1];
        }
        if (preg_match('/Chrome\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'Chrome';
            $browser_ver = $regs[1];
        }
        if (preg_match('/MicroMessenger\/([^\s]+)/i', $agent, $regs)) {
            $browser = '微信浏览器';
            $browser_ver = $regs[1];
        }
        if ($browser != '') {
            return ['browser'=>$browser, 'browser_ver'=>$browser_ver];
        } else {
            return ['browser'=>'未知','browser_ver'=> ''];
        }
    }
}
