<?php

namespace common\models;

use common\Helper\Helper;
use linslin\yii2\curl\Curl;
use Pheanstalk\Exception;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "service_keyword_search_result".
 *
 * @property int $id
 * @property int $sericve_keyword_search_id
 * @property int $user_id
 * @property string $keyword 关键词
 * @property string $sku
 * @property int $weight 权重分
 * @property int $title_weight 标题权重分
 * @property int $page 排名页数
 * @property int $page_position 位数
 * @property int $page_order 总排名
 * @property string $comment 评论数
 * @property int $is_ad 是否广告 1表示广告 0表示非广告
 * @property string $price 价格
 * @property int $type 1自营 2非自营
 * @property int $search_type 1查排名 2查权重
 * @property string $api_result_json 接口返回的文本内容
 * @property string $result_json  处理后的文本
 * @property string $good_title 商品标题
 * @property string $good_img_url 商品图片的url
 * @property string $promotion_logo
 * @property string $good_url 商品的URL
 * @property int $is_double 是否双11商品
 * @property string $double_price 双11价格
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 * @property int $specification 规格
 * @property int $page_start
 * @property int $page_end
 */
class ServiceKeywordSearchResult extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service_keyword_search_result';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sericve_keyword_search_id','user_id', 'weight', 'title_weight', 'page', 'page_position', 'is_ad', 'type','search_type','is_double', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['weight', 'title_weight', 'page', 'page_position','page_order', 'price','user_id'], 'required'],
            [['price'], 'number'],
            [['api_result_json','keyword','sku','double_price'], 'string'],
            [['good_title', 'good_img_url','keyword','comment'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sericve_keyword_search_id' => 'Sericve Keyword Search ID',
            'user_id' => '',
            'keyword' =>'关键词',
            'sku' =>   'Sku',
            'weight' => 'Weight',
            'title_weight' => 'Title Weight',
            'page' => 'Page',
            'page_position' => 'Page Position',
            'page_order' => '总排名',
            'comment' =>'评论数',
            'is_ad' => 'Is Ad',
            'price' => 'Price',
            'type' => 'Type',
            'api_result_json' => 'Api Result Json',
            'result_json' => 'Result Json',
            'search_type' => 'Search Type',
            'good_title' => 'Good Title',
            'good_img_url' => 'Good Img Url',
            'good_url' => 'Good Url',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'double_price' => '规格',
            'page_start' => 'page_start',
            'page_end' => 'page_end',
        ];
    }

    /**
     * 获取排名数据
     * @param $service_id
     * @param $sku
     * @param $city_id
     * @param $main_sku
     * @param string $model
     * @param string $pas_city_name 1 地区为空
     * @return array
     */
    public static function getRankData($service_id, $sku, $city_id, $main_sku, $model = '', $pas_city_name = '')
    {
        $cityName = '';
        if($city_id){
            $city = ApiCityList::findOne(['deleted' => 0, 'id' => $city_id]);
            $cityName = $city->name;
        }
        return self::dataCheck($service_id, $sku, $cityName, $main_sku, $model, $pas_city_name);
    }

    /**
     * @param $service_id
     * @return array
     * 权重数据
     */

    public static function getWeightData($service_id,$weight_min,$weight_max,$sku)
    {

        $user = User::findOne(['deleted'=>0,'id'=>Yii::$app->user->id]);
        $levelService = UserLevelService::findOne(['user_level_id'=>$user->level_id,'service_id'=>2,'deleted'=>0]);
        if (empty($levelService)){
            return ['result'=>0,'msg'=>'查询用户等级失败'];
        }
        $curTime = time();
        $cost_point = 0;
        if ($weight_max <= 100){
            $cost_point = 10;
        }else if ($weight_max > 100 and $weight_max <= 200) {
            $cost_point = 20;
        }else if ($weight_max > 200 and $weight_max <= 300){
            $cost_point = 30;
        }else if ($weight_max > 300 and $weight_max <= 400){
            $cost_point = 40;
        }else{
            $cost_point = 50;
        }
//        if ($levelService->cost_point != 0 or $curTime > $user->level_end_at){  //普通用户、会员到期用户、消耗积分
//
//            if ($user->point < $cost_point){
//                return ['result'=>0,'msg'=>'可用积分不足'];
//            }
//
//            $point_result = self::pointUpdate($relate_model='ServiceKeywordSearch',$service_id,$cost_point,$user->id);
//            if (! $point_result){
//                return ['result'=>0,'msg'=>"积分更新失败"];
//            }
//
//        }else{
//            //当前用户今日查询权重次数
//            $curTime = strtotime(date('Y-m-d',time()));
//            $count = ServiceKeywordSearch::find()->where(['user_id'=>$user->id,'type'=>2,'state'=>3,'deleted'=>0])
//                                                ->andWhere(['>','create_at',$curTime])
//                                                ->andWhere(['not exists',UserPointDetail::find()->where('relate_id = ' .
//                                                    ServiceKeywordSearch::tableName() . '.id')
//                                                ->andWhere(['>','create_at',$curTime])])
//                                                ->count();
//            if ($count > $levelService->day_limit){
//                if ($user->point < $cost_point){
//                    return ['result'=>0,'msg'=>'今日查询次数已用完'];
//                }
//                $point_result = self::pointUpdate($relate_model='ServiceKeywordSearch',$service_id,$cost_point,$user->id);
//                if (! $point_result){
//                    return ['result'=>0,'msg'=>"积分更新失败"];
//                }
//            }
//
//        }
        $data = self::weightInfo($service_id,$sku);
        return $data;
    }

    public static function weightInfo($service_id,$sku,$type='')
    {
        $data = [];
        if (empty($service_id)){
            return ['result'=>1,'data'=>$data];
        }
        $models = ServiceKeywordSearchResult::findOne(['sericve_keyword_search_id' => $service_id, 'deleted' => 0]);
        if (empty($models)){
            return ['result'=>0,'msg'=>'查询无结果'];
        }
        $results = Json::decode($models->result_json,false);

        $data['service_id'] = $service_id;
        $data['create_at'] = date('m-d H:i',time());
        $data['sku_self'] = [];
        $i = 0;
        $result_arr = [];
        if(empty($results)){
            return ['result'=>1,'data'=>[]];
        }
        foreach ($results as $result){
            $id = 0;
            if ($sku == $result->sku){
                $id = $models->id;
                $data['sku_self'] = [
                    'id'=>$models->id,
                    'sku'=>$models->sku,
                    'good_img_url'=>$result->good_img_url,
                    'good_title'=>$result->good_title,
                    'good_url'=>$result->good_url,
                    'type'=>$result->type,
                    'keyword'=>$result->keyword,
                    'page'=>$result->page,
                    'price' => $result->price,
                    'page_position'=>$result->page_position,
                    'page_order'=>$result->page_order,
                    'weight'=>$result->weight,
                    'title_weight'=>$result->title_weight,
                    'comment'=>$result->comment,
//                    'specification'=>$models->specification,
                    'is_ad'=>$result->is_ad==1 ? '1' : '0'];
            }
//            $index = intval($result->page_order);
            $result_arr[$i] = (object)[
                'id'=>$id,
                'sku'=>$result->sku,
                'good_img_url'=>$result->good_img_url,
                'good_title'=>$result->good_title,'good_url'=>$result->good_url,
                'type'=>$result->type,'keyword'=>$result->keyword,'page'=>$result->page,'page_position'=>$result->page_position,
                'page_order'=>$result->page_order,'weight'=>$result->weight,'title_weight'=>$result->title_weight,'comment'=>$result->comment,'is_ad'=>$result->is_ad==1 ? '1' : '0',
                'is_end'=>'0','price'=>$result->price];
            $i++;
        }
        ksort($result_arr);
        if(empty($data['sku_self'])){
            $data['sku_self'] = NULL;
        }
        $data['result'] = $result_arr;
        //  没有对应的结果  调用采集
//        if($type == 'WeightSearch' && empty($data['sku_self'])){
//            $url = Yii::$app->params['sku_collectForMohe'] ."$sku";
//            $curl = new Curl();
//            $collectString = $curl->get($url);
//            $data_s = Json::decode($collectString,false);
//            $data['sku_self'] = [
//                'id'=>$models->id,
//                'good_img_url'=>$data_s->data->img,
//                'good_title'=>$data_s->data->title,
//                'good_url'=>$data_s->data->page_config->product->href,
//                'type'=>$models['type'],
//                'keyword'=>$models['keyword'],
//                'page'=>$models['page'],
//                'price' => $models['price'],
//                'page_position'=>$models['page_position'],
//                'page_order'=>$models['page_order'],
//                'weight'=>0,
//                'title_weight'=>0,
//                'comment'=>$data_s->data->page_config->product->commentVersion,
//                'is_ad'=>$models['is_ad']==1 ? '1' : '0'
//                ];
//
//        }
        if (empty($data['sku_self'])){
//            return ['result'=>0,'msg'=>'查询无结果'];
        }
        return ['result' => 1, 'data' => $data];
    }

    public static function weightInfog($service_id,$sku,$main_sku)
    {
        $data = [];
        if (empty($service_id)){
            return ['result'=>1,'data'=>$data];
        }
        $models = ServiceKeywordSearchResult::findOne(['sericve_keyword_search_id'=>$service_id,'deleted'=>0]);
        if (empty($models)){
            return ['result'=>0,'msg'=>'查询无结果'];
        }
        $results = Json::decode($models->result_json,false);

        $data['service_id'] = $service_id;
        $data['sku_self'] = [];
        $i = 0;
        if(empty($results)){
            return ['result'=>1,'data'=>[]];
        }
        foreach ($results as $result){
            if ($sku == $result->sku){
                $id = $models->id;
                $data['sku_self'] = [
                    'page_start'=>$main_sku['page_start'],
                    'page_end'=>$main_sku['page_end'],
                    'result_sort'=>$main_sku['result_sort'],
                    'price_min'=>$main_sku['price_min'],
                    'price_max'=>$main_sku['price_max'],
                    'weight_min'=>$main_sku['weight_min'],
                    'weight_max'=>$main_sku['weight_max'],
                    'id'=>$models->id,
                    'good_img_url'=>$result->good_img_url,
                    'good_title'=>$result->good_title,
                    'good_url'=>$result->good_url,
                    'type'=>$result->type,
                    'keyword'=>$result->keyword,
                    'page'=>$result->page,
                    'price' => $result->price,
                    'page_position'=>$result->page_position,
                    'page_order'=>$result->page_order,
                    'weight'=>$result->weight - 5,
                    'title_weight'=>$result->title_weight - 5,
                    'comment'=>$result->comment,
                    'sku'=>$result->sku,
                    'create_at'=>date('m-d H:i',$result->create_at),
                    'is_ad'=>$result->is_ad==1 ? '1' : '0'];
            }else{
                continue;
            }
            $i++;
        }
        if($data['sku_self']){
            $data['sku_self']['specification'] = $models->specification;
        }
        return ['result'=>1,'data'=>$data];
    }

    public static function weightLog($service_id,$sku){
        $models = ServiceKeywordSearchResult::findOne(['sericve_keyword_search_id'=>$service_id,'deleted'=>0]);
        if (empty($models)){
            return ['result'=>0,'msg'=>'查询无结果'];
        }
        unset($models->result_json);
        $data['id'] = $models->id;
        $data['service_id'] = $service_id;
        $data['good_img_url'] = $models->good_img_url;
        $data['good_title'] = $models->good_title;
        $data['good_url'] = $models->good_url;
        $data['type'] = $models->type;
        $data['keyword'] = $models->keyword;
        $data['page'] = $models->page;
        $data['price'] = $models->price;
        $data['page_position'] = $models->page_position;
        $data['page_order'] = $models->page_order;
        $data['weight'] = $models->weight;
        $data['title_weight'] = $models->title_weight;
        $data['comment'] = $models->comment;
        $data['sku'] = $models->sku;
        $data['create_at'] = date('m-d H:i',$models->create_at);
        $data['is_ad'] = $models->is_ad==1 ? '1' : '0';
        $data['specification'] = $models->double_price;
        return $data;
    }

    public static function dataCheck($service_id, $sku, $cityName, $main_sku, $model, $pas_city_name = '')
    {
        $data = [];
        $model_type = $model;

        if (empty($service_id)){
            return ['result' => 1, 'data' => $data];
        }
        $user_id = empty(Yii::$app->user->id) ? 0: Yii::$app->user->id;
        $models = ServiceKeywordSearchResult::find()->where(['deleted'=>0,'user_id'=>$user_id]);
        if($pas_city_name == 1){
            $order = 'keyword,page_order desc';
        }else{
            $order = 'keyword,page_order asc';
        }
        $temps = $models->andWhere(['sericve_keyword_search_id' => $service_id])->orderBy($order)->all();

        if(empty($temps)){
            return ['result' => 1, 'data' => $data];
        }

        $data['service_id'] = $service_id;
        $data['create_at'] = date('m-d H:i', time());
        $data['sku_self'] = ['id' => 0, 'sku' => '0'];
        $data['main_sku'] = ['exist' => 0];

        $result = [];
        $i = 0;
        foreach ($temps as $model){
            /* @var $model ServiceKeywordSearchResult*/
            if ($sku == $model->sku){
                if ($data['sku_self']['id'] == 0){
                    $data['sku_self'] = ['id'=>$model->id,'sku' => $sku];
                    $is_host = 1;
                    $is_zj = 1;
                }else {
                    continue;
                }
                
            }else{
                $is_host = 0;
                $is_zj = 0;
            }
            if($pas_city_name == 1){
                $cityName = '';
            }
            if(is_array($main_sku) && count($main_sku) > 6){
                if ($main_sku['sku'] == $model->sku){
                    $data['main_sku'] = ['exist' => 1, 'main_sku' => $main_sku['sku']];
                }
                if(empty($model->good_url)){
                    continue;
                }
                $client_type = ServiceKeywordSearch::findOne(['id' => $model->sericve_keyword_search_id]);
                if ($main_sku['sku'] == $model->sku){
                    $sku_result = (object)[
                        'id' => $model->id,
                        'sku' => $model->sku,
                        'good_img_url' => $model->good_img_url,
                        'good_title' => $model->good_title,
                        'good_url' => $model->good_url,
                        'type' => $model->type,
                        'keyword' => $model->keyword,
                        'page' => $model->page,
                        'page_position' => $model->page_position,
                        'page_order' => $model->page_order,
                        'weight' => $model->weight,
                        'title_weight' => $model->title_weight,
                        'comment' => $model->comment,
                        'is_ad' => $model->is_ad==1 ? '1' : '0',
                        'is_double11' => $model->is_double,
                        'specification' => $model->double_price,
                        'double_price' => $model->double_price,
                        'promotion_logo' => $model->promotion_logo,
                        'price' => $model->price,
                        'create_at' => date('m-d H:i', $model->create_at),

                        'page_start' => empty($main_sku['page_start']) ? 0 : $main_sku['page_start'],
                        'page_end' => empty($main_sku['page_end']) ? 0 : $main_sku['page_end'],
                        'result_sort' => $main_sku['result_sort'],
                        'price_min' => $main_sku['price_min'],
                        'price_max' => $main_sku['price_max'],
                        'weight_min' => $main_sku['weight_min'],
                        'weight_max' => $main_sku['weight_max'],

                        'is_end' => '0',
                        'is_host' => $is_host,
                        'client_type' => $client_type->client_type,
                        'cityName' => $cityName,
                        'model' => $model_type
                    ];
                    array_unshift($result, $sku_result);
                    continue;
                }
                $result[$i] = (object)[
                    'id' => $model->id,
                    'sku' => $model->sku,
                    'good_img_url' => $model->good_img_url,
                    'good_title' => $model->good_title,
                    'good_url' => $model->good_url,
                    'type' => $model->type,
                    'keyword' => $model->keyword,
                    'page' => $model->page,
                    'page_position' => $model->page_position,
                    'page_order' => $model->page_order,
                    'weight' => $model->weight,
                    'title_weight' => $model->title_weight,
                    'comment' => $model->comment,
                    'is_ad' => $model->is_ad==1 ? '1' : '0',
                    'is_double11' => $model->is_double,
                    'double_price' => $model->double_price,
                    'specification' => $model->double_price,
                    'promotion_logo' => $model->promotion_logo,
                    'price' => $model->price,
                    'create_at' => date('m-d H:i',$model->create_at),

                    'page_start' => empty($main_sku['page_start']) ? 0 :$main_sku['page_start'],
                    'page_end' => empty($main_sku['page_end'])?0 :$main_sku['page_end'],
                    'result_sort' => $main_sku['result_sort'],
                    'price_min' => $main_sku['price_min'],
                    'price_max' => $main_sku['price_max'],
                    'weight_min' => $main_sku['weight_min'],
                    'weight_max' => $main_sku['weight_max'],

                    'is_end' => '0',
                    'is_host' => $is_host,
                    'client_type' => $client_type->client_type,
                    'cityName' => $cityName,
                    'model' => $model_type
                ];

            }else{
                if ($main_sku == $model->sku){
                    $data['main_sku'] = ['exist' => 1, 'main_sku' => $main_sku];
                }
                $client_type = ServiceKeywordSearch::findOne(['id' => $model->sericve_keyword_search_id]);
                if($pas_city_name == 2){
                    if ($model->sku == $sku){
                        $sku_result = (object)[
                            'id' => $model->id,
                            'sku' => $model->sku,
                            'good_img_url' => $model->good_img_url,
                            'good_title' => $model->good_title,
                            'good_url' => $model->good_url,
                            'type' => $model->type,
                            'keyword' => $model->keyword,
                            'page' => $model->page,
                            'page_position' => $model->page_position,
                            'page_order' => $model->page_order,
                            'weight' => $model->weight,
                            'title_weight' => $model->title_weight,
                            'comment' => $model->comment,
                            'is_ad' => $model->is_ad == 1 ? '1' : '0',
                            'is_double11' => $model->is_double,
                            'double_price' => $model->double_price,
                            'specification' => $model->double_price,
                            'promotion_logo' => $model->promotion_logo,
                            'price' => $model->price,
                            'create_at' => date('m-d H:i', $model->create_at),

                            'client_type' => $client_type->client_type,

                            'is_relation' => $is_zj,
                            'is_host' => $is_host,
                            'is_end' => '0',
                            'cityName' => $cityName,
                            'model' => $model_type
                        ];
                        array_unshift($result, $sku_result);
                        continue;
                    }
                }
                $result[$i] = (object)[
                    'id' => $model->id,
                    'sku' => $model->sku,
                    'good_img_url' => $model->good_img_url,
                    'good_title' => $model->good_title,
                    'good_url' => $model->good_url,
                    'type' => $model->type,
                    'keyword' => $model->keyword,
                    'page' => $model->page,
                    'page_position' => $model->page_position,
                    'page_order' => $model->page_order,
                    'weight' => $model->weight,
                    'title_weight' => $model->title_weight,
                    'comment' => $model->comment,
                    'is_ad' => $model->is_ad==1 ? '1' : '0',
                    'is_double11' => $model->is_double,
                    'double_price' => $model->double_price,
                    'specification' => $model->double_price,
                    'promotion_logo' => $model->promotion_logo,
                    'price' => $model->price,
                    'create_at' => date('m-d H:i', $model->create_at),

                    'client_type' => $client_type->client_type,

                    'is_end' => '0',
                    'cityName' => $cityName,
                    'is_relation' => $is_zj,
                    'is_host' => $is_host,
                    'model' => $model_type
                ];
            }
            $i++;
        }
        $data['result'] = $result;

        return ['result' => 1, 'data' => $data];
    }

    /**
     * 用户积分更新
     */
    public static function pointUpdate($relate_model,$relate_id,$cost_point,$user_id)
    {
        $trans = Yii::$app->db->beginTransaction();
        try{
            $user = User::findOne(['id'=>$user_id]);
            $user->point -= $cost_point;

            $user_detail = new UserPointDetail();
            $user_detail->point = $cost_point;
            $user_detail->left_point = $user->point;
            $user_detail->user_id = $user_id;
            $user_detail->type = 'pay';
            $user_detail->relate_mode = $relate_model;
            $user_detail->relate_id = $relate_id;
            $user_detail->title = '查权重';

            if (! $user_detail->save()){
                $trans->rollBack();
                Yii::error('积分详情更新失败');
                return false;
            }
            if (! $user->save()){
                $trans->rollBack();
                Yii::error('用户积分更新失败');
                return false;
            }
            $trans->commit();
            return true;

        }catch (Exception $e){
            $trans->rollBack();
            return false;
        }
    }

    /**
     * 查排名显示权重
     */
    public static function weightCheck($id,$search_id,$sku,$keyword,$parama)
    {
        $model = ServiceKeywordSearchResult::findOne(['id'=>$id,'deleted'=>0]);
        if (empty($model)){
            return ['result'=>0,'msg'=>'未找到相应记录'];
        }
        $user = User::findOne(['deleted'=>0,'id'=>Yii::$app->user->id]);
        $levelService = UserLevelService::findOne(['user_level_id'=>$user->level_id,'service_id'=>2,'deleted'=>0]);
        if (empty($levelService)){
            return ['result'=>0,'msg'=>'查询用户等级失败'];
        }

        $curTime = time();
        $cost_point = 10;
        $type = 0; // 消耗次数为0 消耗积分为1
        if ($levelService->cost_point != 0 or $curTime > $user->level_end_at){  //普通用户、会员到期用户、消耗积分
            if ($user->point < $cost_point){
                return ['result'=>0,'msg'=>'可用积分不足'];
            }
            $type = 1;

        }else{
            //当前用户今日查询次数
            $curTime = strtotime(date('Y-m-d',time()));
            $count = ServiceKeywordSearch::find()->where(['user_id'=>$user->id,'type'=>2,'state'=>3,'deleted'=>0])
                ->andWhere(['>','create_at',$curTime])
                ->andWhere(['not exists',UserPointDetail::find()->where('relate_id = ' . ServiceKeywordSearch::tableName() . '.id')
                    ->andWhere(['>','create_at',$curTime])])->count();
            if ($count >= $levelService->day_limit){
                if ($user->point < $cost_point){
                    return ['result'=>0,'msg'=>'今日查询次数或积分已用完'];
                }
                $type = 1;
            }

        }

        //查询此商品在app端的权重分(pc端无权重分)

        $result = self::getSortWeight($parama,$sku,$search_id,$keyword);
        if ($result['result'] != 1){
            return ['result'=>0, 'msg'=>'未查询到相应权重分'];
        }
        $model->weight = $result['weight'];
        $model->title_weight = $result['title_weight'];

        if ($type == 1){
            $point_result = self::pointUpdate($relate_model='ServiceKeywordSearch',$search_id,$cost_point,$user->id);
            if (! $point_result){
                return ['result'=>0,'msg'=>"积分更新失败"];
            }
        }

        if (! $model->save()){
            return ['result'=>0,'msg'=>$model->getError()];
        }

        $data = ['weight'=>$model->weight - 5,'title_weight'=>$model->title_weight];
        return ['result'=>1,'data'=>$data];

    }

    public function getSortWeight($parama,$sku,$search_id,$keyword)
    {
        $url = Yii::$app->params['rank_url'] . 'v1/weight';
        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT,60);
        $postString = $curl->setRequestBody(Json::encode($parama))->post($url);
        $result = Json::decode($postString,false);
        //请求 查找为空
        if ($result->code == 0){
            return ['result'=>0];
        }

        //请求 查找出错
        if ($result->code != 1){
            return ['result'=>0];
        }

        $trans = Yii::$app->db->beginTransaction();

        try{
            $model = new ServiceKeywordSearchResult();
            $model->sericve_keyword_search_id = $search_id;
            $model->sku = $sku;
            $model->user_id = Yii::$app->user->id;
            $model->keyword = $keyword;
            $model->weight = 0;
            $model->title_weight = 0;
            $model->page = 0;
            $model->page_position = 0;
            $model->page_order = 0;
            $model->comment = '';
            $model->is_ad = 0;
            $model->price = 0;
            $model->type = 0;
            $model->search_type = 2;
            $model->api_result_json = '';
            $model->result_json = '';
            $model->good_title = '';
            $model->good_img_url = '';
            $model->good_url = '';


            $datas = $result->result;
            if (empty($datas)){
                return ['result'=>0];
            }
            $result_weight_score = 0;
            $result_title_weight_score = 0;
            foreach ($datas as $data){
                $good_img_url = $data->good_img_url;
                if (strstr($good_img_url,'!q50')){
                    $good_img_url = strstr($good_img_url,'!q50',true);
                }
                if ($sku == $data->sku){
                    $result_weight_score = $data->result_weight_score;
                    $result_title_weight_score = $data->result_title_weight_score;
                    $model->weight = $result_weight_score;
                    $model->title_weight = $result_title_weight_score;
                    $model->page = $data->result_page;
                    $model->page_position = $data->result_page_order;
                    $model->page_order = $data->result_order;
                    $model->comment = $data->result_comment;
                    $model->is_ad = empty($data->is_ad) ? 0 : $data->is_ad;
                    $model->price = empty($data->good_price) ? 0 : $data->good_price;
                    $model->type = $data->result_type;
                    $model->search_type = $data->search_type;
                    $model->good_title = $data->good_title;
                    $model->good_img_url = $good_img_url;
                    $model->good_url = $data->good_url;
                    break;
                }
            }
            if (! $model->save()){
                $trans->rollBack();
                return ['result'=>0];
            }

            $search = ServiceKeywordSearch::findOne(['id'=>$search_id,'deleted'=>0]);
            $search->state = 3;
            if (empty($result_weight_score)){
                $search->state = 1;
            }
            if (! $search->save()){
                $trans->rollBack();
                return ['result'=>0];
            }

            $trans->commit();
            if (empty($result_weight_score)){
                return ['result'=>0];
            }

            return ['result'=>1,'weight'=>$result_weight_score,'title_weight'=>$result_title_weight_score];

        }catch (Exception $e){
            $trans->rollBack();
            return ['result'=>0];
        }


    }

    public static function searchList()
    {
        $models = ServiceKeywordSearch::find()->where(['deleted'=>0])->orderBy('id desc')->limit(10)->all();
        $result = [];
        $i = 0;
        foreach ($models as $model){
            $result[$i] = ['keyword'=>$model->keyword,'time'=>'1分钟前'];
            $i++;
        }

        return $result;
    }

    /**
     * @param $date_start
     * @param $date_end
     * @param $page
     * @param $page_size
     * @param $type
     * @return array
     * 用户搜索记录
     */
    public static function selfSearchList($date_start,$date_end,$page,$page_size,$type)
    {

        $start_time = strtotime($date_start);
        $end_time = strtotime($date_end) + 3600 * 24;
        $cur_time = strtotime(date('Y-m-d',time()));

        $temps = ServiceKeywordSearchResult::find()->where(['user_id'=>Yii::$app->user->id,'deleted'=>0])
                                                    ->andWhere(['>','create_at',$start_time])
                                                    ->andWhere(['<','create_at',$end_time]);
        if ($type != 3){
            $results = $temps->andWhere(['search_type'=>$type]);
        }else{
            $results = $temps;
        }
        if (empty($results)){
            return ['result'=>0,'msg'=>'查询无记录'];
        }
        if (empty($page)){
            $page = 1;
        }
        if (empty($page_size)){
            $page_size = 20;
        }
        $jquery = clone $results;
        $total_count = $jquery->count();
        $total_page = ceil($total_count / $page_size);
        $offset = ($page - 1) * $page_size;
        $results = $results->orderBy('id desc')->offset($offset)->limit($page_size)->all();
        $i = 0;
        $data = [];
        foreach ($results as $result){
            $weight_min = '';
            $weight_max = '';
            $result_weight = 0;
            $result_title_weight = 0;
            if (empty($result->search)){
                continue;
            }

            if ($result->search->type == 2){
                $weight_min = $result->search->weight_min;
                $weight_max = $result->search->weight_max;
                $result_weight = $result->weight;
                $result_title_weight = $result->title_weight;
            }
//            $shop_name = MonitorGoods::find()->where(['id'=>$result->good_id])->one();
            $data[$i] = [
                'id' => $result->id,
                'index'=>$offset + 1 + $i,
                'time'=> date('Y-m-d H:i',$result->create_at),
                'type'=>$result->search->type==1 ? '查排名' : '查权重',
                'sku'=>$result->sku,
//                'shop_name'=>$shop_name->shop_name,
                'shop_name'=>$result->search->model==2 ? $result->search->sku : '-',
                'keyword'=>$result->keyword,
                'page_order'=>$result->page_order,
                'page'=>$result->page,
                'page_position'=>$result->page_position,
                'price_min'=>$result->search->price_min,
                'price_max'=>$result->search->price_max,
                'entrance'=>$result->search->client_type,
                'model'=>$result->search->model,
                'page_start'=>$result->search->page_start,
                'page_end'=>$result->search->page_end,
                'weight_min'=>$weight_min,
                'weight_max'=>$weight_max,
                'result_weight'=>$result_weight - 5,
                'result_title_weight'=>$result_title_weight - 5,
            ];

            $i++;
        }

        return ['result'=>1,'total_count'=>$total_count,'total_page'=>$total_page,'page'=>$page,'page_size'=>$page_size,'data'=>$data];
    }

    public static function searchCheck($id)
    {
        $result = ServiceKeywordSearchResult::findOne(['id'=>$id,'deleted'=>0]);
        if (empty($result)){
            return ['result'=>0,'msg'=>'查询无记录'];
        }

        $weight_min = '';
        $weight_max = '';
        $result_weight = 0;
        $result_title_weight = 0;

        if ($result->search->type == 2){
            $weight_min = $result->search->weight_min;
            $weight_max = $result->search->weight_max;
            $result_weight = $result->weight;
            $result_title_weight = $result->title_weight;
        }
        $data = [
            'time'=> date('Y-m-d H:i:s',$result->create_at),
            'type'=>$result->search->type==1 ? '查排名' : '查权重',
            'sku'=>$result->search->sku,
            'keyword'=>$result->keyword,
            'page_order'=>$result->page_order,
            'page'=>$result->page,
            'page_position'=>$result->page_position,
            'price_min'=>$result->search->price_min,
            'price_max'=>$result->search->price_max,
            'entrance'=>$result->search->client_type==1 ? '电脑端' : '移动端',
            'result_sort' => Yii::$app->params['result_sort'][$result->search->result_sort],
            'good_img_url' => $result->good_img_url,
            'good_title' => $result->good_title,
            'good_url' => $result->good_url,
            'model'=>$result->search->model==1 ? '指定商品' : '指定店铺',
            'page_start'=>$result->search->page_start,
            'page_end'=>$result->search->page_end,
            'weight_min'=>$weight_min,
            'weight_max'=>$weight_max,
            'result_weight'=>$result_weight - 5,
            'result_title_weight'=>$result_title_weight - 5,
        ];
        return ['result'=>1,'data'=>$data];
    }

    //  查询数据入库
    public static function addRankSearch($id,$content,$type){
        if($type == 1){
            $country_info = json_decode($content,true);
            $list = [];
            $ci = 0;
            if($country_info['code'] == 1){
                $datas = $country_info['result'];
                foreach ($datas as $data){
                    if($data['code'] == 1 || $data['code'] ==2){
                        $li = Yii::$app->cache->set($id, 'true');
                        $good_img_url = $data['good_img_url'];
                        $main_sku = $data['main_sku'];

                        //处理移动端图片
                        if (strstr($good_img_url,'!q50')){
                            $good_img_url = strstr($good_img_url,'!q50',true);
                        }
                        $temp = [
                            'sericve_keyword_search_id' => $id,
                            'user_id'                   => $data['user_id'],
                            'keyword'                   => $data['keyword'],
                            'sku'                       => $data['sku'],
                            'weight'                    => $data['result_weight_score'],
                            'title_weight'              => isset($data['result_title_weight_score']) ? $data['result_title_weight_score'] : 0,
                            'page'                      => $data['result_page'],
                            'page_position'             => $data['result_page_order'],
                            'page_order'                => $data['result_order'],
                            'comment'                   => $data['result_comment'],
                            'is_ad'                     => empty($data['is_ad']) ? 0 : $data['is_ad'],
                            'price'                     => empty($data['good_price']) ? 0 : $data['good_price'],
                            'type'                      => $data['result_type'],    // 1自营 2非自营
                            'search_type'               => $data['search_type'],
                            'api_result_json'           => '',
                            'good_title'                => $data['good_title'],
                            'good_img_url'              => $good_img_url,
                            'good_url'                  => $data['good_url'],
                            'promotion_logo'            => $data['promotion_logo'],
                            'is_double'                 => $data['is_double11'] ? 1 : 0,
                            'double_price'              => empty($data['double11_price']) ? '' : $data['double11_price'],
                            'create_at'                 => time(),
                            'update_at'                 => time(),
                        ];
                        array_push($list,$temp);
                    }else{
                        if ($data['code'] == 2){
                            $err_msg = '结果不在搜索范围内';
                            $err_map = isset($data['error_map']) ? Json::encode($data['error_map']) : '';
                        }else if($data['code'] == 3){
                            $ci++;
                            $err_msg = '服务器响应超时,请稍后重试!';
                        }else{
                            $err_msg = "服务器响应超时,请稍后重试";
                            if ($data['code'] == 6){
                                $err_msg = $data['error_message'];
                            }
                        }
                        $time_err = $data['error_message'];
                    }

                }
                $_cloumns = ['sericve_keyword_search_id','user_id','keyword','sku','weight','title_weight','page','page_position',
                    'page_order','comment','is_ad','price','type','search_type','api_result_json','good_title','good_img_url','good_url','promotion_logo','is_double','double_price','create_at','update_at'];
                $result = Yii::$app->db->createCommand()->batchInsert('service_keyword_search_result', $_cloumns, $list)->execute();
                //  修改任务状态
                \Yii::$app->db->createCommand("UPDATE `service_keyword_search` set status=1 WHERE id = $id ")->execute();
                return ['result' =>1,'msg'=>'入库成功'];
//                if (!$result) {
//                    $trans->rollBack();
//                    return ['result'=>0,'msg'=>'服务器响应超时,请重试','time_sort'=>$time_temp];
//                }
            }
        }
        else{
            if($content['code'] == 200){
                if($datas = $content['result']){
                    $list = [];
                    $page_start = $content['page_start'];
                    $page_end = $content['page_end'];
                    foreach($datas as  $data){
                        $temp = [
                            'sericve_keyword_search_id' => $data['use_search_log_id'],
                            'page_start'                   => $page_start,
                            'page_end'                   => $page_end,
                            'user_id'                   => $data['user_id'],
                            'keyword'                   => $data['keyword'],
                            'sku'                       => $data['sku'],
                            'weight'                    => $data['result_weight_score'],
                            'title_weight'              => isset($data['result_title_weight_score']) ? $data['result_title_weight_score'] : 0,
                            'page'                      => $data['result_page'],
                            'page_position'             => $data['result_page_order'],
                            'page_order'                => $data['result_order'],
                            'comment'                   => $data['result_comment'],
                            'is_ad'                     => empty($data['is_ad']) ? 0 : $data['is_ad'],
                            'price'                     => empty($data['good_price']) ? 0 : $data['good_price'],
                            'type'                      => $data['result_type'] == '自营' ? '1' : '2',    // 1自营 2非自营
                            'search_type'               => $data['search_type'],
                            'api_result_json'           => '',
                            'good_title'                => $data['good_title'],
                            'good_img_url'              => $data['good_img_url'],
                            'good_url'                  => $data['good_url'],
                            'promotion_logo'            => $data['promotion_logo'],
                            'is_double'                 => $data['is_double11'] ? 1 : 0,
                            'double_price'              => empty($data['double11_price']) ? '' : $data['double11_price'],
                            'create_at'                 => time(),
                            'update_at'                 => time(),
                        ];
                        $sericve_keyword_search_id = $data['use_search_log_id'];
                        array_push($list,$temp);
                    }
                    $saveId = ServiceKeywordSearch::find()->where(['id'=>$sericve_keyword_search_id])->one();
                    $saveId->status = 1;
                    $saveId->save();
                    $_cloumns = ['sericve_keyword_search_id','page_start','page_end','user_id','keyword','sku','weight','title_weight','page','page_position',
                        'page_order','comment','is_ad','price','type','search_type','api_result_json','good_title','good_img_url','good_url','promotion_logo','is_double','double_price','create_at','update_at'];
                    $result = Yii::$app->db->createCommand()->batchInsert('service_keyword_search_result_log', $_cloumns, $list)->execute();
                    if(!$result){
                        return  ['result'=>0,'msg'=>'失败'];
                    }
                }
                return  ['result'=>1,'msg'=>'成功'];
            }else{
                return  ['result'=>0,'msg'=>$content['msg']];
            }
        }
    }

    public static function  server_py($parama='',$is_history=''){
        $time = time();
        $trans = Yii::$app->getDb()->beginTransaction();
        try {

//            $redis = Yii::$app->redis;
//            $array_key = 'servers_sa_py';
//            $servers_list_count = $redis->zcard($array_key);
//            $suite_count = 1;
//            // 有一组
//            if($servers_list_count >= $suite_count) {
                //  获取ip
                $end_at = time() + (60*4);
                $ip = IpList::find()->where(['>','past_at',$end_at])->one();
                $parama['ip'] = $ip->ip;
                $parama['port'] = $ip->port;
                $ip->is_use = 1;
                $ip->deleted = 1;
                $ip->save();
                $parama['type'] = 4;

                $ip_list = 'http://106.75.209.210:5000/' .'search_rank';
                //  插入
//                $redis->zadd('servers_sa',time(),$servers_list);
                $curl = new Curl();
                $curl->setOption(CURLOPT_TIMEOUT,90);
                $postString = $curl->setRequestBody(Json::encode($parama))->setHeader('content-type', 'application/json')->post($ip_list);
                $country_info = Json::decode($postString);
                $country_info = '{"id": "456", "content": {"code": "200", "msg": "\u5904\u7406\u6210\u529f", "page_start": "21", "page_end": "30", "result": [{"sku": "", "result_page": 0, "result_page_order": 0, "result_order": 0, "result_type": 0, "result_comment": "", "good_price": "", "good_url": "", "good_img_url": "", "good_title": "", "keyword": "", "user_id": "123", "use_search_log_id": "456", "main_sku": "", "search_sku": "", "search_type":0, "result_weight_score": 0, "result_title_weight_score": 0, "is_ad": "", "code": "", "error_message": "", "error_map": "", "double11_price": "", "is_double11": "", "promotion_logo": ""}]}, "type": 2}';
//                var_dump(json_decode($country_info,true));exit();
                $list = [];
                $main_sku = '';
                $err_msg = '';
                $time_err = '';
                $err_map = '';

//                $country_info = $country_info['result'];

                $url = Yii::$app->params['good_particulars'];
            $url = 'http://106.75.209.210:5000/goods_detail';
                $curl = new Curl();
                $curl->setOption(CURLOPT_TIMEOUT,90);
            $postString = $curl->setRequestBody(Json::encode($parama))->setHeader('content-type', 'application/json')->post($url);
//            var_dump($postString);exit();
            $postString = '{"return_code": "200", "msg": "处理成功", "result": {"price": "5999.00", "sku": "5089253", "title": "Apple iPhone X (A1865) 64GB 深空灰色 移动联通电信4G手机", "master_img": "https://img13.360buyimg.com/n1/s450x450_jfs/t10675/253/1344769770/66891/92d54ca4/59df2e7fN86c99a27.jpg", "shop_name": "Apple产品京东自营旗舰店", "shop_type": "自营", "goodCount": 1260492}}';
            $postString = json_decode($postString,true);
            $time_temp = time() - $time;
            if($postString['return_code'] == 200){
                $data = $postString['result'];
                $list['price'] = $data['price'];
                $list['sku'] = $data['sku'];
                $list['good_title'] = $data['title'];
                $list['good_img_url'] = $data['master_img'];
                $list['shop_name'] = $data['shop_name'];
                $list['comment'] = $data['goodCount'];
                return ['main_sku'=>$main_sku,'time_err'=>'','time_sort' => $time_temp,'err_map'=>'','list'=>$list,'msg'=>''];
                return $date;
            }else{
                return ['list'=>'','msg'=>'系统繁忙请稍后再试','time_sort'=>time() - $time,'time_err'=>'系统繁忙请稍后再试' . '..','err_map' => '系统繁忙请稍后再试'];
            }
//            var_dump($postString);exit();
//                foreach($country_info as $k => $y){
//                    if($y['code'] == 200){
//                        $data = $y;
//                        if(empty($data)){
//                            $err_msg = '结果不在搜索范围内';
//                        }else{
////                            foreach ($datas as $data){
////                                if($data['code'] == 1){
//                                $good_img_url = $data['good_img_url'];
//                                $main_sku = $data['main_sku'];
//
//                                //处理移动端图片
//                                if (strstr($good_img_url,'!q50')){
//                                    $good_img_url = strstr($good_img_url,'!q50',true);
//                                }
//                                $temp = [
//                                    'sericve_keyword_search_id' => $data['use_search_log_id'],
//                                    'user_id'                   => $data['user_id'],
//                                    'keyword'                   => $data['keyword'],
//                                    'sku'                       => $data['sku'],
//                                    'weight'                    => $data['result_weight_score'],
//                                    'title_weight'              => isset($data['result_title_weight_score']) ? $data['result_title_weight_score'] : 0,
//                                    'page'                      => $data['result_page'],
//                                    'page_position'             => $data['result_page_order'],
//                                    'page_order'                => $data['result_order'],
//                                    'comment'                   => $data['result_comment'],
//                                    'is_ad'                     => empty($data['is_ad']) ? 0 : $data['is_ad'],
//                                    'price'                     => empty($data['good_price']) ? 0 : $data['good_price'],
//                                    'type'                      => $data['result_type'] == '自营' ? '1' : '2',    // 1自营 2非自营
//                                    'search_type'               => $data['search_type'],
//                                    'api_result_json'           => '',
//                                    'good_title'                => $data['good_title'],
//                                    'good_img_url'              => $good_img_url,
//                                    'good_url'                  => $data['good_url'],
//                                    'promotion_logo'            => $data['promotion_logo'],
//                                    'is_double'                 => $data['is_double11'] ? 1 : 0,
//                                    'double_price'              => empty($data['double11_price']) ? '' : $data['double11_price'],
//                                    'create_at'                 => time(),
//                                    'update_at'                 => time(),
//                                ];
//                                array_push($list,$temp);
//                                }else{
//                                    if ($data['code'] == 2){
//                                        //$err_msg = $data->error_message;
//                                        $err_msg = '结果不在搜索范围内';
//                                        $err_map = isset($data['error_map']) ? Json::encode($data['error_map']) : '';
//                                    }else if($data['code'] == 3){
//                                        return ['result'=>10,'msg'=>'当前sku不在所查页数范围内'];
//                                    }else{
//                                        $err_msg = "服务器响应超时,请稍后重试";
//                                        if ($data['code'] == 6){
//                                            $err_msg = $data['error_message'];
//                                        }
//                                    }
//                                    $time_err = $data['error_message'];
//                                }

//                            }
//                        }
//                    }else{
//                        continue;
//                    }
//                }

//                }
//                $trans->commit();
//                $time_temp = time() - $time;
//                if($list){
//                    return ['main_sku'=>$main_sku,'time_err'=>'','time_sort' => $time_temp,'err_map'=>'','list'=>$list,'msg'=>''];
//                }else{
                return ['main_sku'=>$main_sku,'time_err'=>$time_err,'time_sort' => '','err_map'=>$err_map,'list'=>$list,'msg'=>$err_msg];
//                }
//            }else{
//                return ['list'=>'','msg'=>'系统繁忙请稍后再试','time_sort'=>time() - $time,'time_err'=>'系统繁忙请稍后再试' . '..','err_map' => '系统繁忙请稍后再试'];
//            }
        }catch (\Exception $e) {
            $trans->rollBack();
//                        file_put_contents('time.log', $this->user_id . ' ---- PHP执行出错 信息：' . $e->getMessage() . '---类型 ：' . $client->type . PHP_EOL, FILE_APPEND);
            if (!empty($key)) {
//                            $redis->lpush($key, $job_id);
            }
            return ['code' => 1010];
        }
    }

    /**
     * 单品查排名-电脑端-API结果
     * @param array $curlParams
     * @return array
     * @throws \yii\db\Exception
     */
    public static function rankPcResult($curlParams){
        $time = time();
        $trans = Yii::$app->getDb()->beginTransaction();
        try {
            // 获取UA
            $curlParams['ua'] = SysUserAgent::randGetUaList(20);

            // 获取代理
            if(empty($proxyList = IpList::pcGetProxyArr($curlParams['page_end']))){
                return [
                    'list' => '',
                    'time_sort' => time() - $time,
                    'msg' => '系统繁忙请稍后再试',
                    'time_err' =>'系统繁忙请稍后再试' . '..',
                    'err_map' => '系统繁忙请稍后再试'
                ];
            }
            $curlParams['proxy_list'] = $proxyList;

            $rankPcUrl = Yii::$app->params['rank_pc'] .'v1/order';
//            $curl = new Curl();
//            $curl->setOption(CURLOPT_TIMEOUT, 90);
//            $postString = $curl->setRequestBody(Json::encode($curlParams))->post($rankPcUrl);
//            $curlResult = json_decode($postString,true);
            $curlResult = Helper::curlPost($rankPcUrl, $curlParams);

            $list = [];
            $main_sku = '';
            $err_msg = '';
            $time_err = '';
            $err_map = '';
            $ci = 0;

            if($curlResult['code'] == 1){
                $resultData = $curlResult['result'];
                $flag = true;
                foreach ($resultData as $data){
                    if($flag && $data['code'] == 1 && $curlParams['sku'] == $data['sku']){
                        $flag = false;

                        $good_img_url = $data['good_img_url'];
                        $main_sku = $data['main_sku'];

                        //处理移动端图片
                        if (strstr($good_img_url,'!q50')){
                            $good_img_url = strstr($good_img_url,'!q50',true);
                        }

                        $list[] = [
                            'sericve_keyword_search_id' => $data['use_search_log_id'],
                            'user_id'                   => $data['user_id'],
                            'keyword'                   => $data['keyword'],
                            'sku'                       => $data['sku'],
                            'weight'                    => $data['result_weight_score'],
                            'title_weight'              => $data['result_title_weight_score'] ?? 0,
                            'page'                      => $data['result_page'],
                            'page_position'             => $data['result_page_order'] < 0 ? $data['result_page_order'] + 30 : $data['result_page_order'],
                            'page_order'                => $data['result_order'] < 0 ? $data['result_order'] + 30 : $data['result_order'],
                            'comment'                   => $data['result_comment'],
                            'is_ad'                     => empty($data['is_ad']) ? 0 : $data['is_ad'],
                            'price'                     => empty($data['good_price']) ? 0 : $data['good_price'],
                            'type'                      => $data['result_type'],    // 1自营 2非自营
                            'search_type'               => $data['search_type'],
                            'api_result_json'           => json_encode($data),
                            'good_title'                => $data['good_title'],
                            'good_img_url'              => $good_img_url,
                            'good_url'                  => $data['good_url'],
                            'promotion_logo'            => $data['promotion_logo'],
                            'is_double'                 => $data['is_double11'] ? 1 : 0,
                            'double_price'              => empty($data['double11_price']) ? '' : $data['double11_price'],
                            'create_at'                 => time(),
                            'update_at'                 => time()
                        ];
                    }else{
                        switch ($data['code']){
                            case 2:
                                $err_msg = '结果不在搜索范围内';
                                $err_map = isset($data['error_map']) ? Json::encode($data['error_map']) : '';
                                break;
                            case 3:
                                $ci++;
                                $err_msg = '服务器响应超时,请稍后重试!';
//                                return ['result'=>10,'msg'=>'当前sku不在所查页数范围内'];
                                break;
                            case 6:
                                $err_msg = $data['error_message'];
                                break;
                            default:
                                $err_msg = "服务器响应超时,请稍后重试";
                                break;
                        }
                        $time_err = $data['error_message'];
                    }
                }
            }
            $trans->commit();
        }catch (\Exception $e) {
            $trans->rollBack();
//            file_put_contents('time.log', $this->user_id . ' ---- PHP执行出错 信息：' . $e->getMessage() . '---类型 ：' . $client->type . PHP_EOL, FILE_APPEND);
            return ['code' => 1010];
        }
        $timeSort = time() - $time;

        return [
            'list' => $list,
            'main_sku' => $main_sku,
            'time_err' => $time_err,
            'time_sort' => $timeSort,
            'err_map' => $err_map,
            'msg' => $err_msg
        ];
    }

    /**
     * @return array
     * 整治  流量用户
     */
    public static function govern($ip=''){
        $govern = 0;
        $end_at = time();
        $start_at = time()-60;
        $end_at_5 = time();
        $start_at_5 = time() - 300;
        $end_at_24 = time();
        $start_at_24 = time() - 86400;

        $i_1 = ServiceKeywordSearch::find()->select('id')->andFilterWhere(['BETWEEN','create_at',$start_at,$end_at])->where(['user_id'=>Yii::$app->user->id,'ip'=>"$ip"])->count();
        if($i_1 > 40){
            $govern = 1;
            return $govern;
        }

        $i_5 = ServiceKeywordSearch::find()->andFilterWhere(['BETWEEN','create_at',$start_at_5,$end_at_5])->where(['user_id'=>Yii::$app->user->id,'ip'=>"$ip"])->count();
        if($i_5 > 200){
            $govern = 1;
            return $govern;
        }

        $h_24 = ServiceKeywordSearch::find()->andFilterWhere(['BETWEEN','create_at',$start_at_24,$end_at_24])->where(['user_id'=>Yii::$app->user->id,'ip'=>"$ip"])->count();
        if($h_24 > 6000){
            $govern = 1;
            return $govern;
        }
        return $govern;
    }

    //  ip白名单
    public static function ipAccept(){
        $redis = \Yii::$app->redis;
        $ip_list = $redis->get('ip_white_list');
        if($ip_list){
            $ip_list = json_decode($ip_list,true);
            $ip_list_s = [];
            foreach($ip_list as $k => $y){
                $ip_list_s[] = $y['ip'];
            }

            if(in_array(Yii::$app->getRequest()->getUserIP(),$ip_list_s)){
                $is_pass = 0;
            }else{
                $is_pass = ServiceKeywordSearchResult::govern(Yii::$app->getRequest()->getUserIP());
                if($is_pass > 0){
                    return  ['result' => 0, 'msg'=>'结果不在搜索范围内..'];
                }
            }
        }
        return  ['result' => 1];
    }

    public static function actionIpList($count){
        //  获取ip
        $url = \Yii::$app->params['obtain_ip'];
        $curl = new Curl();
        $collectString = $curl->get($url);
        $collectString = explode(PHP_EOL,$collectString);
        //批量插入field
        $ipFields = ['ip','port','is_use','past_at','area','create_at','update_at'];
        $ipValues = [];
        foreach($collectString as $k => $y){
            if($y){
                $ip = explode(' -> ',$y);
                $area = $ip[1];
                $ip = $ip[0];
                $ip = explode(':',$ip);
                $port = $ip[1];
                $ip = $ip[0];

                $ipArray = [
                    $ip,
                    $port,
                    0,
                    time() + (3600 *3),
                    $area,
                    time(),
                    time()
                ];
                array_push($ipValues,$ipArray);
            }
        }
        $stat = \Yii::$app->db->createCommand()->batchInsert(IpList::tableName(), $ipFields, $ipValues)->execute();
        //  获取ip
        $ipList = IpList::find()->select('id,ip,port')->where(['is_use'=>0,'deleted'=>0])->limit($count)->asArray()->all();
        return $ipList;
    }

    /**
     * @param $arr
     * @return object|void
     */
    public static function array_to_object($arr) {
        if (gettype($arr) != 'array') {
            return;
        }
        foreach ($arr as $k => $v) {
            if (gettype($v) == 'array' || getType($v) == 'object') {
                $arr[$k] = (object)array_to_object($v);
            }
        }

        return (object)$arr;
    }

    /**
     * curl查询app排名
     * @param array $curlParam
     * @param string $type
     * @return array|mixed
     */
    public static function HttpGetApp($curlParam, $type=''){
        // 获取IP
        if (empty($ipArr = IpList::appGetProxyArr($curlParam['page_end']))){
            return [
                'success' => 101,
                'list' => '',
                'msg' => '系统繁忙请稍后再试',
                'time_sort' => time(),
                'time_err' => '系统繁忙请稍后再试' . '..',
                'err_map' => '系统繁忙请稍后再试'
            ];
        }

        $curlParam['ip'] = $ipArr;
        $curlParam['page'] = "1";

        if($type == 'shop'){
            $url = Yii::$app->params['go_appshop_ranking'];
        }else{
            $url = Yii::$app->params['go_app_ranking'];
        }

        return Helper::curlPost($url, $curlParam, false);
    }

    public static function commonNum($sku)
    {
        try{
            $url = Yii::$app->params['sku_collect'] . $sku . "&cap=1&price=1";
            $curl = new Curl();
            $collectString = $curl->get($url);

            if (strstr($collectString,"502 Bad Gateway")){
                return ['result'=>0,'msg'=>'采集信息超时,请稍后重新查询.'];
            }
            $data = Json::decode($collectString,false);

            if ($data->result !=1){
                return ['result'=>0,'msg'=>$data->msg];
            }
            $result = $data->data?$data->data:'';
            if (! $result){
                return ['result'=>0,'msg'=>'信息获取失败，请稍后重试'];
            }
            $commentsCount = $result->commentsCount;
            return ['result'=>1,'commentsCount'=>$commentsCount];

        }catch (\Exception $e){
            return ['result'=>0,'msg'=>$e->getMessage()];
        }
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

    /**
     * 排名 信息查询
     * @param $parama
     * @param $service_id
     * @return array
     * @throws
     */
    public static function resultOrderSave($parama, $service_id)
    {
        $time = time();

        //  电脑端 轮循服务IP
        if($parama['client_type'] == ServiceKeywordSearch::SEARCH_ENTRANCE_PC){
            $apiResult = self::rankPcResult($parama);
            $main_sku = $apiResult['main_sku'];
            $time_sort = $apiResult['time_sort'];
            $time_err = $apiResult['time_err'];
            $err_map = $apiResult['err_map'];
            $time_temp = 0;

            if (empty($list = $apiResult['list'])){
                return [
                    'result' => 0,
                    'msg' => $apiResult['msg'] ?? '',
                    'time_sort' => $apiResult['time_sort'] ?? '',
                    'time_err' => $apiResult['time_err'] ?? '',
                    'err_map' => $apiResult['err_map'] ?? ''
                ];
            }
        }
        elseif($parama['client_type'] == ServiceKeywordSearch::SEARCH_ENTRANCE_APP){
            unset($parama['ip']);
            unset($parama['port']);
            unset($parama['proxy_username']);
            unset($parama['proxy_password']);
            if($parama['model'] == 1 && Yii::$app->params['JDBS'] == 1){
                $userAgent = SysUserAgent::randGet();
                $sort = 0;
                switch($parama['result_sort']){
                    case 1:
                        $sort = 0;
                        break;
                    case 2:
                        $sort = 3;
                        break;
                    case 3:
                        $sort = 4;
                        break;
                    case 4:
                        $sort = 5;
                        break;
                    case 5:
                        $sort = 1;
                        break;
                }
                // 采集商品详情
                $goods_url = 'https://www.jdboshi.com/api/queryModel/queryGoodsInfo.bs?sku='.$parama['sku'];
                $url = "https://www.jdboshi.com/api/queryModel/queryRankingNew.bs?keyword=".urlencode($parama['keyword'])."&sku=".$parama['sku']."&sort=".$sort."&equipment=1";
                $oneIP = IpList::randGetIpList([
                    'is_app_use' => 0,
                    'is_app_pass' => 1
                ],1, 'ip,port');
                if(empty($oneIP)){
                    return [
                        'list' => '',
                        'msg' => '系统繁忙请稍后再试',
                        'time_sort' => time() - $time,
                        'time_err' => '系统繁忙请稍后再试' . '..',
                        'err_map' => '系统繁忙请稍后再试'
                    ];
                }
                $proxy = $oneIP[0]['ip'].':'.$oneIP[0]['port'];
                $headers = [
                    "Content-type:application/json;charset=utf-8",
                    "Accept:application/json",
                    "User-Agent:".$userAgent[0]['user_agent'],
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
                $goodresult = self::goodInfo($proxy,$goods_url,$headers);
                if($goodresult['code'] != 200){
                    return ['result'=>0,'msg'=>'当前sku不在所查页数范围内!!','time_sort'=>0];
                }
                $title = $goodresult['data']['skuName'];
                $img = $goodresult['data']['image'];
                $price = $goodresult['data']['price'];
                $goods_type = 0;
                if(strpos($goodresult['data']['shopName'],'自营')!==false){
                    $goods_type = 1;
                }
                // 获取评论数
                $commonNum = self::commonNum($parama['sku']);
                if($commonNum['result'] != 1){
                    return ['result'=>0,'msg'=>'当前sku不在所查页数范围内!!!!','time_sort'=>0];
                }
                $commonNum = empty($commonNum['commentsCount']->total) ? 0 : $commonNum['commentsCount']->total;

                $result = self::goodInfo($proxy,$url,$headers);
                if($result['code'] != 200){
                    return ['result'=>0,'msg'=>'当前sku不在所查页数范围内!!!!!!','time_sort'=>0];
                }

                $datas = $result['data'];
                $err_map = '';
                $time_err = '';
                $time_temp = time();
                $main_sku = $parama['sku'];
                $list = [];

                foreach ($datas as $key=> $data){
//                if($data->code == 1){
                    if($key == 'app'){
                        $good_img_url = 'https://item.jd.com/'.$parama['sku'].'.html';
                        //处理移动端图片
                        if (strstr($good_img_url,'!q50')){
                            $good_img_url = strstr($good_img_url,'!q50',true);
                        }
                        $temp = [
                            'sericve_keyword_search_id' => $parama['id'],
                            'user_id'                   => $parama['user_id'],
                            'keyword'                   => $parama['keyword'],
                            'sku'                       => $parama['sku'],
                            'weight'                    => 0,
                            'title_weight'              => 0,
                            'page'                      => intval((int)$data['ranking']/10)+1,
                            'page_position'             => (int)$data['ranking']%10 == 0 ? 10: (int)$data['ranking']%10,
                            'page_order'                => $data['ranking'],
                            'comment'                   => $commonNum,
                            'is_ad'                     => 0,
                            'price'                     => $price,
                            'type'                      => $goods_type,    // 1自营 2非自营
                            'search_type'               =>1,
                            'api_result_json'           => '',
                            'good_title'                => $title,
                            'good_img_url'              => $img,
                            'good_url'                  => $good_img_url,
                            'promotion_logo'            => '',
                            'is_double'                 => 0,
                            'double_price'              => 0,
                            'create_at'                 => time(),
                            'update_at'                 => time(),
                        ];
                        array_push($list,$temp);
                    }else{
                        continue;
                    }
                }
            }
            else{
                // 获取代理
                if(empty($proxyList = IpList::pcGetProxyArr($parama['page_end']))){
                    return [
                        'list' => '',
                        'time_sort' => time() - $time,
                        'msg' => '系统繁忙请稍后再试',
                        'time_err' =>'系统繁忙请稍后再试' . '..',
                        'err_map' => '系统繁忙请稍后再试'
                    ];
                }
                $parama['proxy_list'] = $proxyList;
                $parama['price_min'] = (int)$parama['price_min'];
                $parama['price_max'] = (int)$parama['price_max'];

                $url = Yii::$app->params['app_rank_url'] .'v1/order';

                $curl = new Curl();
                $curl->setOption(CURLOPT_TIMEOUT, 90);
                $postString = $curl->setRequestBody(Json::encode($parama))->post($url);
                $result = Json::decode($postString,false);

                $paramaa['sku'] =$parama['sku'];
                $paramaa['keyword'] =$parama['keyword'];
                $time_temp = time() - $time;

                // 请求 查找为空
                if ($result->code == 0){
                    return ['result' => 0, 'msg' => '当前sku不在所查页数范围内', 'time_sort' => $time_temp];
                }

                // 请求 查找出错
                if ($result->code != 1){
                    return ['result' => 0, 'msg' => '', 'time_sort' => $time_temp];
                }

                if ($result->code == 3){
                    return ['result' => 10, 'msg' => '当前sku不在所查页数范围内'];
                }

                //请求返回正确数据 存表
                $datas = $result->result;
                $list = [];
                $main_sku = '';
                $err_msg = '';
                $time_err = '';
                $err_map = '';

                foreach ($datas as $data){
                    if($data->code == 1){
                        $good_img_url = $data->good_img_url;
                        $main_sku = $data->main_sku;
                        //处理移动端图片
                        if (strstr($good_img_url,'!q50')){
                            $good_img_url = strstr($good_img_url,'!q50',true);
                        }
                        $list[] = [
                            'sericve_keyword_search_id' => $data->use_search_log_id,
                            'user_id'                   => $data->user_id,
                            'keyword'                   => $data->keyword,
                            'sku'                       => $data->sku,
                            'weight'                    => $data->result_weight_score,
                            'title_weight'              => isset($data->result_title_weight_score) ? $data->result_title_weight_score : 0,
                            'page'                      => $data->result_page,
                            'page_position'             => $data->result_page_order,
                            'page_order'                => $data->result_order,
                            'comment'                   => $data->result_comment,
                            'is_ad'                     => empty($data->is_ad) ? 0 : $data->is_ad,
                            'price'                     => empty($data->good_price) ? 0 : $data->good_price,
                            'type'                      => $data->result_type,    // 1自营 2非自营
                            'search_type'               => $data->search_type,
                            'api_result_json'           => '',
                            'good_title'                => $data->good_title,
                            'good_img_url'              => $good_img_url,
                            'good_url'                  => $data->good_url,
                            'promotion_logo'            => $data->promotion_logo,
                            'is_double'                 => $data->is_double11 ? 1 : 0,
                            'double_price'              => empty($data->double11_price) ? '' : $data->double11_price,
                            'create_at'                 => time(),
                            'update_at'                 => time()
                        ];
                    }else{
                        if ($data->code == 2){
//                        return ['result'=>10,'msg'=>'当前sku不在所查页数范围内'];
                            $err_msg = '结果不在搜索范围内';
                            $err_map = isset($data->error_map) ? Json::encode($data->error_map) : '';
                        }else if($data->code == 3){
                            return ['result'=>10,'msg'=>'当前sku不在所查页数范围内'];
                        }else{
                            $err_msg = "服务器响应超时,请稍后重试";
                            if ($data->code == 6){
                                $err_msg = $data->error_message;
                            }
                        }
                        $time_err = $data->error_message;
                    }
                }
            }
        }
        elseif($parama['client_type'] == ServiceKeywordSearch::SEARCH_ENTRANCE_M) {
            if($parama['model'] == 1){          // 指定商品
                $skuList = empty($parama['sku_list']) ? $parama['sku'] : implode(',',$parama['sku_list']);
                $curlParams = [
                    'sku_list' => $skuList,
                    'sku' => $parama['sku'],
                    'keyword' => $parama['keyword'],
                    'sort' => $parama['result_sort'],
                    'page_end' => $parama['page_end'],
                    'price_min' => $parama['price_min'] == '0' ? '' : $parama['price_min'],
                    'price_max' => $parama['price_max'] ==  '0' ? '' : $parama['price_max']
                ];

                $response = self::HttpGetApp($curlParams);
//                if(empty($response->result[0]->page_position_app)){
//                    $response = self::HttpGetApp($curlParams);
//                }

                $time_temp = time() - $time;

                if($response['success'] == 101){
                    return ['result' => 0, 'msg' => '系统繁忙请稍后再试'];
                }
                //请求返回正确数据 存表
                $datas = $response['result'];
                $list = [];
                $main_sku = '';
                $err_msg = '';
                $time_err = '';
                $err_map = '';
                if($response['success'] == 200){
                    $flag = false;
                    foreach($datas as $res){
                        if(empty($good_img_url = $res['good_img_url'])){
                            return ['result' => 0, 'msg' => '当前sku不在所查页数范围内'];
                        }
                        if($res['sku'] == $parama['sku']){
                            if ($flag){
                                continue;
                            }
                            $flag = true;
                        }

                        $main_sku = $res['main_sku'];
                        //处理移动端图片
                        if (strstr($good_img_url,'!q50')){
                            $good_img_url = strstr($good_img_url,'!q50',true);
                        }
                        $list[] = [
                            'sericve_keyword_search_id' => $parama['id'],
                            'user_id'                   => $parama['user_id'],
                            'keyword'                   => $res['keyword'],
                            'sku'                       => $res['sku'],
                            'weight'                    => empty($res['result_weight_score']) ? 0 : $res['result_weight_score'],
                            'title_weight'              => isset($res['result_title_weight_score']) ? $res['result_title_weight_score'] : 0,
                            'page'                      => empty($res['page_app']) ? 0 : $res['page_app'],
                            'page_position'             => empty($res['page_position_app']) ? 0 : $res['page_position_app'],
                            'page_order'                => empty($res['page_order_app']) ? 0 : $res['page_order_app'],
                            'comment'                   => empty($res['result_comment']) ? 0 : $res['result_comment'],
                            'is_ad'                     => 0,
                            'price'                     => empty($res['good_price']) ? 0 : $res['good_price'],
                            'type'                      => empty($res['result_type']) ? 1 : 2,    // 1自营 2非自营
                            'search_type'               => $parama['type'],
                            'api_result_json'           => '',
                            'good_title'                => $res['good_title'],
                            'good_img_url'              => $good_img_url,
                            'good_url'                  => $res['good_url'],
                            'promotion_logo'            => '',
                            'is_double'                 => 0,
                            'double_price'              => '',
                            'create_at'                 => time(),
                            'update_at'                 => time()
                        ];
                    }

                }else{
                    return ['result' => 10, 'msg' => '当前sku不在所查页数范围内'];
                }
            }
            else{
                $time_temp = time() - $time;
                $paramaa['page_end'] =$parama['page_end'];
                $paramaa['shop_id'] =$parama['shop_id'];
                $paramaa['keyword'] =$parama['keyword'];
                $paramaa['sort'] = $parama['result_sort'];
                $paramaa['price_min'] = $parama['price_min'] == '0' ? '' : $parama['price_min'];
                $paramaa['price_max'] = $parama['price_max'] ==  '0' ? '' : $parama['price_max'];
                $response = self::HttpGetApp($paramaa,'shop');

                if($response['success'] == 101){
                    return ['result' => 0, 'msg' => '系统繁忙请稍后再试'];
                }

                $datas = $response['result'];
                $list = [];
                $main_sku = '';
                $err_msg = '';
                $time_err = '';
                $err_map = '';
                if($response['success'] == 200){
                    foreach($datas as $res){
                        if(empty($res)){
                            continue;
                        }
                        if(empty($res['good_img_url'])){
                            continue;
                        }
                        $good_img_url = $res['good_img_url'];
                        //处理移动端图片
                        if (strstr($good_img_url,'!q50')){
                            $good_img_url = strstr($good_img_url,'!q50',true);
                        }
                        $temp = [
                            'sericve_keyword_search_id' => $parama['id'],
                            'user_id'                   => $parama['user_id'],
                            'keyword'                   => $parama['keyword'],
                            'sku'                       => $res['sku'],
                            'weight'                    => empty($res['result_weight_score']) ? 0 : $res['result_weight_score'],
                            'title_weight'              => isset($res['result_title_weight_score']) ? $res['result_title_weight_score'] : 0,
                            'page'                      => empty($res['page_app']) ? 0 : $res['page_app'],
                            'page_position'             => empty($res['page_position_app']) ? 0 : $res['page_position_app'],
                            'page_order'                => empty($res['page_order_app']) ? 0 : $res['page_order_app'],
                            'comment'                   => empty($res['result_comment']) ? 0 : $res['result_comment'],
                            'is_ad'                     => 0,
                            'price'                     => empty($res['good_price']) ? 0 : $res['good_price'],
                            'type'                      => empty($res['result_type']) ? 1 : 2,    // 1自营 2非自营
                            'search_type'               => $parama['type'],
                            'api_result_json'           => '',
                            'good_title'                => $res['good_title'],
                            'good_img_url'              => $good_img_url,
                            'good_url'                  => $res['good_url'],
                            'promotion_logo'            => '',
                            'is_double'                 => 0,
                            'double_price'              => '',
                            'create_at'                 => time(),
                            'update_at'                 => time()
                        ];
                        array_push($list,$temp);
                    }
                }else{
                    return ['result' => 10, 'msg' => '当前sku不在所查页数范围内'];
                }
            }
        }

        if (empty($list)){
            return [
                'result' => 0,
                'msg' => $err_msg,
                'time_sort' => time() - $time,
                'time_err' => $time_err,
                'err_map' => $err_msg
            ];
        }

        $list[0]['double_price'] = '';

        $_cloumns = [
            'sericve_keyword_search_id','user_id','keyword','sku','weight','title_weight','page','page_position',
            'page_order','comment','is_ad','price','type','search_type','api_result_json','good_title','good_img_url',
            'good_url','promotion_logo','is_double','double_price','create_at','update_at'
        ];
        $trans = Yii::$app->db->beginTransaction();
        try{
            $result = Yii::$app->db->createCommand()->batchInsert('service_keyword_search_result', $_cloumns, $list)->execute();
            if (!$result) {
                $trans->rollBack();
                return ['result' => 0, 'msg' => '服务器响应超时,请重试', 'time_sort' => $time_temp];
            }

            $service = ServiceKeywordSearch::findOne(['id' => $service_id, 'deleted' => 0]);
            $service->state = 3;
            if (!$service->save()){
                $trans->rollBack();
                return ['result' => 0, 'msg' => $service->getError(), 'time_sort' => $time_temp];
            }

            $trans->commit();
        }catch (Exception $e){
            $trans->rollBack();
            return ['result'=>0, 'msg'=>'服务器响应超时,请重试', 'time_sort' => $time_temp];
        }

        return [
            'result' => 1,
            'main_sku' => $main_sku,
            'time_sort' => $time_temp,
            'time_err' => $time_err,
            'err_map' => $err_map
        ];
    }


    /**
     * @param $params
     * @return array|mixed
     */
    public static function HttpGetAppWeight($params){
        $count = (int)$params['weight_max']/10;
        if ($params['weight_max'] % 10 > 0){
            $count +=1;
        }
        $count = (int)$count;
        $params['weight_max'] = $count;

        $ipList = IpList::appGetProxyArr(50);
        if (empty($ipList)){
            return [
                'list' => '',
                'msg' => '系统繁忙请稍后再试',
                'time_sort' => time(),
                'time_err' => '系统繁忙请稍后再试' . '..',
                'err_map' => '系统繁忙请稍后再试'
            ];
        }

        $params['ip'] = $ipList;
        $params['sort'] = 1;

        $weightUrl = Yii::$app->params['go_appweight_ranking'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $weightUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response);
        return $response;
    }

    /**
     * 权重查询
     * @param $params
     * @param $sku
     * @param $service_id
     * @param $keyword
     * @return array
     * @throws \yii\db\Exception
     */
    public static function resultWeightSave($params, $sku, $service_id, $keyword)
    {
        $weightMax = (int)$params['weight_max']/10;
        if ($params['weight_max'] % 10 > 0){
            $weightMax +=1;
        }
        $weightMax = (int)$weightMax;

        $ipList = IpList::appGetProxyArr($weightMax);
        if (empty($ipList)){
            return [
                'result' => 0,
                'msg' => '系统繁忙请稍后再试',
            ];
        }

        $curlParams = [
            'sort' => 1,
            'sku' => $params['sku'],
            'keyword' => $params['keyword'],
            'weight_min' => 1,
            'weight_max' => $weightMax,
            'price_min' => $params['price_min'],
            'price_max' => $params['price_max'],
            'ip' => $ipList
        ];
        $weightUrl = Yii::$app->params['go_appweight_ranking'];
        $curlResult = Helper::curlPost($weightUrl, $curlParams, false);
        $trans = Yii::$app->db->beginTransaction();
        try{
            $model = new ServiceKeywordSearchResult();
            $model->user_id = Yii::$app->user->id ? Yii::$app->user->id : 0;
            $model->sericve_keyword_search_id = $service_id;
            $model->keyword = $keyword;
            $model->sku = $sku;
            $model->search_type = 2;
            $model->weight = 0;
            $model->title_weight = 0;
            $model->page = 0;
            $model->page_position = 0;
            $model->page_order = 0;
            $model->comment = '';
            $model->is_ad = 0;
            $model->price = 0;
            $model->type = 0;
            $model->api_result_json = '';
            $model->result_json = '';
            $model->good_title = '';
            $model->good_img_url = '';
            $model->good_url = '';

            //请求返回正确数据 存表
            $resultResult = $curlResult['result'] ?? [];
            $resultResult = array_filter($resultResult);
            if (empty($resultResult)){
                return [
                    'result' => 0,
                    'msg' => '未查询到结果！',
                ];
            }

            $weight_process = $resultResult[count($resultResult)-1]['result_weight_score'] ?? 0;
            $list = [];
            foreach ($resultResult as $data){
                if(empty($data) || empty($data['sku'])){
                    continue;
                }

                // 处理数据格式
                $weight = $weight_process <= 0 ? 150 + $data['result_weight_score'] : $data['result_weight_score'];
                $titleWeight = $data['result_title_weight_score'] ?? 0;
                if (empty($titleWeight)){
                    $titleWeight = 0;
                }
                $goodsPrice = intval($data['good_price']);
                $goodsType = empty($data['result_type']) ? 1 : 2;    // 1自营 2非自营

                $good_img_url = $data['good_img_url'];
                if (strstr($good_img_url,'!q50')){
                    $good_img_url = strstr($good_img_url,'!q50',true);
                }
                $list[] = [
                    'sericve_keyword_search_id' => $params['id'],
                    'user_id'                   => $params['user_id'],
                    'keyword'                   => $params['keyword'],
                    'sku'                       => $data['sku'],
                    'good_title'                => $data['good_title'],
                    'good_url'                  => $data['good_url'],
                    'page'                      => $data['page_app'],
                    'page_position'             => $data['page_position_app'],
                    'page_order'                => $data['page_order_app'],
                    'comment'                   => $data['result_comment'],
                    'search_type'               => $params['type'],
                    'is_ad'                     => 0,
                    'weight'                    => $weight,
                    'title_weight'              => $titleWeight,
                    'price'                     => $goodsPrice,
                    'type'                      => $goodsType,
                    'good_img_url'              => $good_img_url,
                    'create_at'                 => time(),
                    'update_at'                 => time()
                ];
                if ($data['sku'] == $sku){
                    $model->good_title = $data['good_title'];
                    $model->good_url = $data['good_url'];
                    $model->page = $data['page_app'];
                    $model->page_position = $data['page_position_app'];
                    $model->page_order = $data['page_order_app'];
                    $model->comment = $data['result_comment'];
                    $model->search_type = $params['type'];
                    $model->is_ad = 0;
                    $model->weight = $weight;
                    $model->title_weight = $titleWeight;
                    $model->price = $goodsPrice;
                    $model->type = $goodsType;
                    $model->good_img_url = $good_img_url;
                }
            }

            if (!empty($list)){
                $model->api_result_json = Json::encode($curlResult);
                $model->result_json = Json::encode($list);
            }

            if(empty($model->good_img_url)){
                //  拼采集接口  商品的规格存储
                $sku = $list[0]['sku'] ?? 0;
                $url = Yii::$app->params['sku_collect'] ."$sku"."&price=0";
                $curl = new Curl();
                $collectString = $curl->get($url);
                $data = Json::decode($collectString,false);

                if ($data->result = 1 && isset($data->data)){
                    $data = $data->data;
                    //  获取参数
                    $can = $data->page_config->product->colorSize ?? [];
                    $specification = '';
                    foreach ($can as $k => $y){
                        if($y->skuId == $sku){
                            $specification_list = [];
                            foreach ($y as $k1 => $y1){
                                if($k1 == 'skuId'){
                                    continue;
                                }else{
                                    array_push($specification_list, $y1);
                                }
                            }
                            $specification = implode('',$specification_list);
                        }
                    }
                    $model->double_price = $specification;
                    $model->good_img_url = $data->img ?? '';
                    $model->good_title = $data->page_config->product->name ?? '';
                    $model->good_url = $data->page_config->product->href ?? '';
                }else{
                    $model->double_price = '';
                    $model->good_img_url = '';
                    $model->good_title = '';
                    $model->good_url = '';
                }
            }

            if (!$model->save()){
                return ['result' => 0, 'msg' => $model->getError()];
            }

            $service = ServiceKeywordSearch::findOne(['id'=>$service_id,'deleted'=>0]);
            $service->state = 3;
            if (! $service->save()){
                $trans->rollBack();
                return ['result'=>0,'msg'=>$service->getError()];
            }

            // 添加查权重接口查询记录
            if (!empty($model->result_json)){
                WeightSearchLog::addLog($model->keyword, $service_id);
            }

            $trans->commit();
            return ['result'=>1];
        }catch (\Exception $e){
            $trans->rollBack();
//            return ['result'=>0,'msg'=> $e->getFile().$e->getLine().$e->getMessage()];
            return ['result'=>0,'msg'=>'服务器响应超时,请重试...'];
        }


    }
//    //  规格拼接 采集
//    public function getCha(){
//
//    }
    public function getSearch()
    {
        return $this->hasOne(ServiceKeywordSearch::className(),['id'=>'sericve_keyword_search_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }
}
