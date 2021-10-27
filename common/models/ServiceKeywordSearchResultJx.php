<?php

namespace common\models;

use Yii;
use linslin\yii2\curl\Curl;
use Pheanstalk\Exception;
/**
 * This is the model class for table "service_keyword_search_result_jx".
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
 * @property string $result_json 处理后的数据文本
 * @property string $good_title 商品标题
 * @property string $good_img_url 商品图片的url
 * @property string $promotion_logo
 * @property string $good_url 商品的URL
 * @property int $is_double 是否是双11商品
 * @property string $double_price 双11价格（改为价格）
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 * @property string $specification 规格
 * @property int $page_start
 * @property int $page_end
 */
class ServiceKeywordSearchResultJx extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service_keyword_search_result_jx';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sericve_keyword_search_id', 'user_id', 'weight', 'title_weight', 'page', 'page_position', 'page_order', 'is_ad', 'type', 'search_type', 'is_double', 'deleted', 'version', 'create_at', 'update_at', 'page_start', 'page_end'], 'integer'],
            [['user_id', 'weight', 'title_weight', 'page', 'page_position', 'price', 'search_type', 'api_result_json', 'good_url'], 'required'],
            [['price'], 'number'],
            [['api_result_json', 'result_json', 'good_url'], 'string'],
            [['keyword', 'comment', 'good_title', 'good_img_url', 'promotion_logo'], 'string', 'max' => 255],
            [['sku'], 'string', 'max' => 32],
            [['double_price'], 'string', 'max' => 50],
            [['specification'], 'string', 'max' => 100],
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
            'user_id' => 'User ID',
            'keyword' => 'Keyword',
            'sku' => 'Sku',
            'weight' => 'Weight',
            'title_weight' => 'Title Weight',
            'page' => 'Page',
            'page_position' => 'Page Position',
            'page_order' => 'Page Order',
            'comment' => 'Comment',
            'is_ad' => 'Is Ad',
            'price' => 'Price',
            'type' => 'Type',
            'search_type' => 'Search Type',
            'api_result_json' => 'Api Result Json',
            'result_json' => 'Result Json',
            'good_title' => 'Good Title',
            'good_img_url' => 'Good Img Url',
            'promotion_logo' => 'Promotion Logo',
            'good_url' => 'Good Url',
            'is_double' => 'Is Double',
            'double_price' => 'Double Price',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'specification' => 'Specification',
            'page_start' => 'Page Start',
            'page_end' => 'Page End',
        ];
    }

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

    public static function HttpGetApp($paramaa,$type=''){
        if($type == 'shop'){
            $url1 = Yii::$app->params['go_app_ranking_jx_shop'];
        }else{
            $url1 = Yii::$app->params['go_app_ranking_jx'];
        }
        $page_end = $paramaa['page_end'];
        $paramaa['page_end'] =$paramaa['page_end'];
        $paramaa['page'] ="1";
        $paramaa['sort'] = $paramaa['sort'];

        //  使用ip
        if($oneIP  = Yii::$app->jdrqds->createCommand("SELECT id,ip,port FROM ip_list WHERE is_app_pass=1 AND is_app_use=0 order by rand() limit $page_end")->queryAll()){}else{
        }

        if(empty($oneIP)){
            return self::array_to_object(['success'=>101,'list'=>'','msg'=>'系统繁忙请稍后再试','time_sort'=>time(),'time_err'=>'系统繁忙请稍后再试' . '..','err_map' => '系统繁忙请稍后再试']);
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


        //  处理空格（多种空格  16进制不一样）
        $str = str_replace('C2A0','20',self::StrToBin($paramaa['keyword']));
        $paramaa['keyword'] = self::hexToStr($str);
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

    public static function StrToBin($str){
        $hex="";
        for($i=0;$i<strlen($str);$i++)
            $hex.=dechex(ord($str[$i]));
        $hex=strtoupper($hex);
        return $hex;
    }
    public static  function hexToStr($hex){
        $str="";
        for($i=0;$i<strlen($hex)-1;$i+=2)
            $str.=chr(hexdec($hex[$i].$hex[$i+1]));
        return $str;
    }

    /**
     * @return array
     * 排名 信息查询
     */
    public static function resultOrderSave($parama,$service_id)
    {

        $time = time();
        //  电脑端 轮循服务IP
        if($parama['client_type'] == 1){
            return ['result'=>0,'msg'=>'占不支持此类型'];
        }else{
            if($parama['model'] == 1){
                $time_temp = time() - $time;

                $paramaa['sku'] =$parama['sku'];
                $paramaa['keyword'] =$parama['keyword'];
                $paramaa['sort'] = $parama['result_sort'];
                $paramaa['page_end'] = $parama['page_end'];
                $paramaa['price_min'] = $parama['price_min'] == '0' ? '' : $parama['price_min'];
                $paramaa['price_max'] = $parama['price_max'] ==  '0' ? '' : $parama['price_max'];
                $response = self::HttpGetApp($paramaa);
                if(empty($response->result[0]->page_position_app)){
                    $response = self::HttpGetApp($paramaa);
                }

                if($response->success == 101){
                    return ['result'=>0,'msg'=>'系统繁忙请稍后再试'];
                }
                //请求返回正确数据 存表

                $datas = $response->result;
                $list = [];
                $main_sku = '';
                $err_msg = '';
                $time_err = '';
                $err_map = '';
                if($response->success == 200){
                    foreach($datas as $k => $y){
                        if(empty($y->good_img_url)){
                            return ['result'=>0,'msg'=>'当前sku不在所查页数范围内'];
                        }
                        $good_img_url = $y->good_img_url;
                        $main_sku = $y->main_sku;
                        //处理移动端图片
                        if (strstr($good_img_url,'!q50')){
                            $good_img_url = strstr($good_img_url,'!q50',true);
                        }
                        $temp = [
                            'sericve_keyword_search_id' => $parama['id'],
                            'user_id'                   => $parama['user_id'],
                            'keyword'                   => $y->keyword,
                            'sku'                       => $y->sku,
                            'weight'                    => empty($y->result_weight_score) ? 0 : $y->result_weight_score,
                            'title_weight'              => isset($y->result_title_weight_score) ? $y->result_title_weight_score : 0,
                            'page'                      => empty($y->page_app) ? 0 : $y->page_app,
                            'page_position'             => empty($y->page_position_app) ? 0 : $y->page_position_app,
                            'page_order'                => empty($y->page_order_app) ? 0 : $y->page_order_app,
                            'comment'                   => empty($y->result_comment) ? 0 : $y->result_comment,
                            'is_ad'                     => 0,
                            'price'                     => empty($y->good_price) ? 0 : $y->good_price,
                            'type'                      => empty($y->result_type) ? 1 : 2,    // 1自营 2非自营
                            'search_type'               => $parama['type'],
                            'api_result_json'           => '',
                            'good_title'                => $y->good_title,
                            'good_img_url'              => $good_img_url,
                            'good_url'                  => $y->good_url,
                            'promotion_logo'            => '',
                            'is_double'                 => 0,
                            'double_price'              => '',
                            'create_at'                 => time(),
                            'update_at'                 => time(),
                        ];
                        array_push($list,$temp);
                    }

                }else{
                    return ['result'=>10,'msg'=>'当前sku不在所查页数范围内'];
                }
            }else{
                $time_temp = time() - $time;
                $paramaa['page_end'] =$parama['page_end'];
                $paramaa['shop_name'] =$parama['sku'];
                $paramaa['keyword'] =$parama['keyword'];
                $paramaa['sort'] = $parama['result_sort'];
                $paramaa['price_min'] = $parama['price_min'] == 0 ? '' : $parama['price_min'];
                $paramaa['price_max'] = $parama['price_max'] == 0 ? '' : $parama['price_max'];
                $response = self::HttpGetApp($paramaa,'shop');

                if($response->success == 101){
                    return ['result'=>0,'msg'=>'系统繁忙请稍后再试'];
                }

                $datas = $response->result;
                $list = [];
                $main_sku = '';
                $err_msg = '';
                $time_err = '';
                $err_map = '';
                if($response->success == 200){
                    foreach($datas as $k => $y){
                        if(empty($y)){
                            continue;
                        }
                        if(empty($y->good_img_url)){
                            continue;
                        }
                        $good_img_url = $y->good_img_url;
                        //处理移动端图片
                        if (strstr($good_img_url,'!q50')){
                            $good_img_url = strstr($good_img_url,'!q50',true);
                        }
                        $temp = [
                            'sericve_keyword_search_id' => $parama['id'],
                            'user_id'                   => $parama['user_id'],
                            'keyword'                   => $parama['keyword'],
                            'sku'                       => $y->sku,
                            'weight'                    => empty($y->result_weight_score) ? 0 : $y->result_weight_score,
                            'title_weight'              => empty($y->result_title_weight_score) ? 0 : $y->result_title_weight_score,
                            'page'                      => empty($y->page_app) ? 0 :$y->page_app,
                            'page_position'             =>empty($y->page_position_app) ? 0 :  $y->page_position_app,
                            'page_order'                => empty($y->page_order_app) ? 0 : $y->page_order_app,
                            'comment'                   => empty($y->result_comment) ? 0 : $y->result_comment,
                            'is_ad'                     => 0,
                            'price'                     => empty($y->good_price) ? 0 : $y->good_price,
                            'type'                      => empty($y->result_type) ? 1 : 2,    // 1自营 2非自营
                            'search_type'               => $parama['type'],
                            'api_result_json'           => '',
                            'good_title'                => $y->good_title,
                            'good_img_url'              => $good_img_url,
                            'good_url'                  => $y->good_url,
                            'promotion_logo'            => '',
                            'is_double'                 => 0,
                            'double_price'              => '',
                            'create_at'                 => time(),
                            'update_at'                 => time(),
                        ];
                        array_push($list,$temp);
                    }
                    if(empty($list)){
                        return ['result'=>0,'msg'=>'当前sku不在所查页数范围内'];
                    }
                }else{
                    return ['result'=>10,'msg'=>'当前sku不在所查页数范围内'];
                }
            }


            if (empty($list)){
                return ['result'=>0,'msg'=>$err_msg,'time_sort'=>time() - $time,'time_err'=>$time_err . '..','err_map' => $err_msg];
            }
        }
        $list[0]['double_price'] = '';

        $_cloumns = ['sericve_keyword_search_id','user_id','keyword','sku','weight','title_weight','page','page_position',
            'page_order','comment','is_ad','price','type','search_type','api_result_json','good_title','good_img_url','good_url','promotion_logo','is_double','double_price','create_at','update_at'];
        $trans = Yii::$app->db->beginTransaction();
        try{
            $result = Yii::$app->db->createCommand()->batchInsert('service_keyword_search_result_jx', $_cloumns, $list)->execute();
            if (!$result) {
                $trans->rollBack();
                return ['result'=>0,'msg'=>'服务器响应超时,请重试','time_sort'=>$time_temp];
            }

            $service = ServiceKeywordSearchJx::findOne(['id'=>$service_id,'deleted'=>0]);
            $service->state = 3;
            if (! $service->save()){
                $trans->rollBack();
                return ['result'=>0,'msg'=>$service->getError(),'time_sort'=>$time_temp];
            }

            $trans->commit();
        }catch (Exception $e){
            $trans->rollBack();
            return ['result'=>0,'msg'=>'服务器响应超时,请重试...','time_sort'=>$time_temp];
        }

        return ['result'=>1,'main_sku' => $main_sku,'time_sort' => $time_temp,'time_err'=>$time_err,'err_map' => $err_map];
    }

    /**
     * @param $service_id
     * @return array
     * 排名数据
     * $pas_city_name  1 地区为空
     */

    public static function getRankData($service_id,$sku,$city_id,$main_sku,$model='',$pas_city_name='')
    {
        if($city_id){
            $city = ApiCityList::findOne(['deleted'=>0,'id' => $city_id]);
        }else{
            $city = ApiCityList::findOne(['deleted'=>0,'id' => 1]);
            $city->name = '';
        }
        $data = self::dataCheck($service_id,$sku,$city->name,$main_sku,$model,$pas_city_name);
        return $data;
    }

    public static function dataCheck($service_id,$sku,$cityName,$main_sku,$model,$pas_city_name='')
    {
        $data = [];
        $model_type = $model;

        if (empty($service_id)){
            return ['result'=>1,'data'=>$data];
        }
        $user_id = empty(Yii::$app->user->id) ? 0: Yii::$app->user->id;
        $models = ServiceKeywordSearchResultJx::find()->where(['deleted'=>0,'user_id'=>$user_id]);
        if($pas_city_name == 1){
            $order = 'id desc';
        }else{
            $order = 'id asc';
        }
        $temps =  $models->andWhere(['sericve_keyword_search_id'=>$service_id])
            ->orderBy($order) ->all();

        if(empty($temps)){
            return ['result'=>1,'data'=>$data];
        }

        $data['service_id'] = $service_id;
        $data['create_at'] = date('m-d H:i',time());
        $result = [];
        $i = 0;
        $data['sku_self'] = ['id'=>0,'sku' => '0'];
        $data['main_sku'] = ['exist'=>0];
        foreach ($temps as $model){
            $weight = 0;
            $title_weight = 0;
            if ($sku == $model->sku){
                $data['sku_self'] = ['id'=>$model->id,'sku' => $sku];
                $is_host = 1;
                $is_zj = 1;
            }else{
                $is_host = 0;
                $is_zj = 0;
            }
            if($pas_city_name == 1){
                $cityName = '';
            }
            if(is_array($main_sku) && count($main_sku) > 6){
                if ($main_sku['sku'] == $model->sku){
                    $data['main_sku'] = ['exist'=>1,'main_sku' => $main_sku['sku']];
                }
                if(empty($model->good_url)){
                    continue;
                }
                $client_type = ServiceKeywordSearchJx::find()->where(['id'=>$model->sericve_keyword_search_id])->one();
//                if(Yii::$app->user->id == 30544){
                if ($main_sku['sku'] == $model->sku){
                    $sku_result = (object)[
                        'specification'=>$model['double_price'],
                        'page_start'=>empty($main_sku['page_start']) ? 0 :$main_sku['page_start'],
                        'page_end'=>empty($main_sku['page_end'])?0 :$main_sku['page_end'],
                        'result_sort'=>$main_sku['result_sort'],
                        'price_min'=>$main_sku['price_min'],
                        'price_max'=>$main_sku['price_max'],
                        'weight_min'=>$main_sku['weight_min'],
                        'weight_max'=>$main_sku['weight_max'],
                        'sku'=>$model->sku,
                        'is_host'=>$is_host,'client_type'=>$client_type->client_type,'id'=>$model->id,'good_img_url'=>$model->good_img_url,'good_title'=>$model->good_title,'good_url'=>$model->good_url,
                        'type'=>$model->type,'keyword'=>$model->keyword,'page'=>$model->page,'page_position'=>$model->page_position,
                        'page_order'=>$model->page_order,'weight'=>$model->weight,'title_weight'=>$model->title_weight,'comment'=>$model->comment,'is_ad'=>$model->is_ad==1 ? '1' : '0',
                        'is_end'=>'0','is_double11' => $model->is_double,'double_price' => $model->double_price,'promotion_logo'=>$model->promotion_logo,'price'=>$model->price,'cityName'=>$cityName,'create_at'=>date('m-d H:i',$model->create_at),'model'=>$model_type];
                    array_unshift($result,$sku_result);
                    continue;
                }
//                }
                $result[$i] = (object)[
                    'specification'=>$model['double_price'],
                    'page_start'=>empty($main_sku['page_start']) ? 0 :$main_sku['page_start'],
                    'page_end'=>empty($main_sku['page_end'])?0 :$main_sku['page_end'],
                    'result_sort'=>$main_sku['result_sort'],
                    'price_min'=>$main_sku['price_min'],
                    'price_max'=>$main_sku['price_max'],
                    'weight_min'=>$main_sku['weight_min'],
                    'weight_max'=>$main_sku['weight_max'],
                    'sku'=>$model->sku,
                    'is_host'=>$is_host,'client_type'=>$client_type->client_type,'id'=>$model->id,'good_img_url'=>$model->good_img_url,'good_title'=>$model->good_title,'good_url'=>$model->good_url,
                    'type'=>$model->type,'keyword'=>$model->keyword,'page'=>$model->page,'page_position'=>$model->page_position,
                    'page_order'=>$model->page_order,'weight'=>$model->weight,'title_weight'=>$model->title_weight,'comment'=>$model->comment,'is_ad'=>$model->is_ad==1 ? '1' : '0',
                    'is_end'=>'0','is_double11' => $model->is_double,'double_price' => $model->double_price,'promotion_logo'=>$model->promotion_logo,'price'=>$model->price,'cityName'=>$cityName,'create_at'=>date('m-d H:i',$model->create_at),'model'=>$model_type];

            }else{
                if ($main_sku == $model->sku){
                    $data['main_sku'] = ['exist'=>1,'main_sku' => $main_sku];

                }
                $client_type = ServiceKeywordSearchJx::find()->where(['id'=>$model->sericve_keyword_search_id])->one();
                if($pas_city_name == 2){
                    if ($sku == $model->sku){
                        $sku_result = (object)[
                            'specification'=>$model['double_price'],
                            'is_relation'=>$is_zj,'sku'=>$model->sku,'is_host'=>$is_host,'client_type'=>$client_type->client_type,'id'=>$model->id,'good_img_url'=>$model->good_img_url,'good_title'=>$model->good_title,'good_url'=>$model->good_url,
                            'type'=>$model->type,'keyword'=>$model->keyword,'page'=>$model->page,'page_position'=>$model->page_position,
                            'page_order'=>$model->page_order,'weight'=>$model->weight,'title_weight'=>$model->title_weight,'comment'=>$model->comment,'is_ad'=>$model->is_ad==1 ? '1' : '0',
                            'is_end'=>'0','is_double11' => $model->is_double,'double_price' => $model->double_price,'promotion_logo'=>$model->promotion_logo,'price'=>$model->price,'cityName'=>$cityName,'create_at'=>date('m-d H:i',$model->create_at),'model'=>$model_type];
                        array_unshift($result,$sku_result);
                        continue;
                    }
                }
                $result[$i] = (object)[
                    'specification'=>$model['double_price'],
                    'is_relation'=>$is_zj,'sku'=>$model->sku,'is_host'=>$is_host,'client_type'=>$client_type->client_type,'id'=>$model->id,'good_img_url'=>$model->good_img_url,'good_title'=>$model->good_title,'good_url'=>$model->good_url,
                    'type'=>$model->type,'keyword'=>$model->keyword,'page'=>$model->page,'page_position'=>$model->page_position,
                    'page_order'=>$model->page_order,'weight'=>$model->weight,'title_weight'=>$model->title_weight,'comment'=>$model->comment,'is_ad'=>$model->is_ad==1 ? '1' : '0',
                    'is_end'=>'0','is_double11' => $model->is_double,'double_price' => $model->double_price,'promotion_logo'=>$model->promotion_logo,'price'=>$model->price,'cityName'=>$cityName,'create_at'=>date('m-d H:i',$model->create_at),'model'=>$model_type];
            }
            $i++;
        }
        $data['result'] = $result;

        return ['result'=>1,'data'=>$data];
    }
}
