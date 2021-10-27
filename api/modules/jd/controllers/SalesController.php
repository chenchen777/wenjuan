<?php
/**
 * Created by PhpStorm.
 * User: lisk
 * Date: 2018年10月27日
 * Time: 14点06分
 */
namespace api\modules\jd\controllers;

use linslin\yii2\curl\Curl;
use api\modules\jd\Controller;
use common\models\SalesRecord;
use common\models\User;
use Yii;
use yii\helpers\Json;
use yii\web\Response;
use yii\filters\ContentNegotiator;
class SalesController extends Controller{

    public $enableCsrfValidation =false;

    /**
     * @return array
     * 销量
     */
    public function actionData()
    {
//         if(!isset($_SERVER['HTTP_REFERER']) || !stripos($_SERVER['HTTP_REFERER'],'jdmohe.com')) {
//             return array('result'=>0,  'msg'=>'系统错误');
//         }
        Yii::$app->response->format = Response::FORMAT_JSON;

        //判断当前用户的查排名的次数是否足够
        $limit = User::getLeftTimesResult(3);
        if($limit['result'] == 0 ){
            return $limit;
        }

        $sku = Yii::$app->request->post('sku');
        if (! is_numeric($sku)){
            $sku = preg_match('/([0-9]{5,30})/',$sku,$a) ? $a[1] : 0;
        }
        if (empty($sku)){
            return ['result'=>0,'msg'=>'商品链接或sku不正确'];
        }
        if (Yii::$app->user->id) {
            $type = 1;
        } else {
            $type = 2;
        }
        $key = 'SALES:'.$sku.'_'.$type;
//         if ($data = $redis->get($key)) {
//             $data = json_decode($data);
//         } else {
        $data = [];
//            $collectUrl = Yii::$app->params['sales_collect']."$sku";
        $collectUrl = "http://cj.chaojids.com/sales/"."$sku";
        $header=array(
            "Accept: application/json",
            "Content-Type: application/json;charset=utf-8",
        );

        // 发送请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $collectUrl);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($ch, CURLOPT_POST, 1);
        //设置post数据
        if ($type == 1) {   //满足条件
            $post_data = array(
                "durations" => ["1","2","3","4"],
            );
        } else {
            $post_data = array(
                "durations" => ["1","4"],
            );
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        $res = curl_exec($ch);

        $data = json_decode($res,true);
        if(Yii::$app->user->id == 30544){
//    var_dump(json_encode($post_data));exit();
        }
        curl_close($ch);
//             echo'<pre>';var_dump($data);exit;

        //存储销量记录
        $sale_record = new SalesRecord();
        if ($data['code'] == 1) {
            foreach($data['data'] as $k => $y){
                $sale_record->title = $y['title'];
                $sale_record->result_json =json_encode($y);
            }
        } else {
            return array('result'=>0,  'data'=>[], 'msg'=>$data['msg']);
        }
//         }

//         $result = [];
//         $result->uv = $data->totalUV;
//         $result->t_pv = $data->impPV;
//         $result->t_uv = $data->impUV;
//         $result->tr = $data->ctr;
//         $result->vr = $data->cvr;

        $sale_record->sku = $sku;
        $sale_record->user_id = Yii::$app->user->id;
        $sale_record->search_ip = Yii::$app->request->userIP;
        $sale_record->status = 1;
        $sale_record->create_at = time();
        //  拼采集接口  商品的规格存储
//            $sale_record->specification = self::cai($sku);
        $list = $data['data'];
//        var_dump($list);exit();
        foreach($list as $k => $y){
            $list_a = [];
            foreach($y['data'] as $k1 => $y1){

                $y1["impUV"] = $y1["totalUV"];
                $y1["impPV"] = $y1["visitor"];
                //                var_dump($y1);exit();
                $list_a[] = $y1;
            }
            $list[$k]['data'] = self::arraySort($list_a,'impUV','asc');
        }
        return array('result'=>1,  'data'=>$list);
    }
    public static function arraySort($arr, $keys, $type = 'asc') {

        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v){
            $keysvalue[$k] = $v[$keys];
        }
        $type == 'asc' ? asort($keysvalue) : arsort($keysvalue);
        reset($keysvalue);


        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        $new_array = array_values($new_array);
        return $new_array;
    }

    //  获取商品指定sku 规格
    public function cai($sku = ''){
        $url = Yii::$app->params['sku_collectForMohe'] ."$sku";
        $curl = new Curl();
        $collectString = $curl->get($url);
        $data_a = Json::decode($collectString,false);
        if ($data_a->result =1){
            $data_a = $data_a->data;
            //  获取参数
            $can = $data_a->page_config->product->colorSize;
            $specification = '';
            foreach ($can as $k => $y){
                if($y->skuId == $sku){
                    $specification_list = [];
                    foreach ($y as  $k1 => $y1){
                        if($k1 == 'skuId'){
                            continue;
                        }else{
                            array_push($specification_list,$y1);
                        }
                    }
                    $specification = implode('',$specification_list);
                }else{
                    continue;
                }
            }
            return $specification;
        }else{
            return $specification='';
        }
    }
}