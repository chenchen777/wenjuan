<?php

namespace common\models;

use linslin\yii2\curl\Curl;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "base_goods".
 *
 * @property int $id
 * @property string $sku
 * @property int $user_id
 * @property string $sku_main 主sku
 * @property string $spu
 * @property string $sku_list spu下所有sku
 * @property string $title 商品标题
 * @property string $shop_name 店铺名称
 * @property resource $shopid 店铺id
 * @property string $cat_name 类目json
 * @property int $cat 类目id
 * @property string $brand 品牌
 * @property int $brandid 品牌id
 * @property resource $venderid 供应商
 * @property string $pic 商品图片
 * @property string $price 商品价格
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class BaseGoods extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'base_goods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku'], 'required'],
            [['shopid', 'venderid'], 'string'],
            [['cat','user_id', 'brandid', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['price'], 'number'],
            [['sku', 'sku_main', 'spu'], 'string', 'max' => 32],
            [['title', 'pic'], 'string', 'max' => 255],
            [['shop_name'], 'string', 'max' => 100],
            [['cat_name'], 'string', 'max' => 150],
            [['brand'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'Sku',
            'user_id' => 'User ID',
            'sku_main' => '主sku',
            'spu' => 'Spu',
            'sku_list' => 'spu下所有sku',
            'title' => '商品标题',
            'shop_name' => '店铺名称',
            'shopid' => '店铺id',
            'cat_name' => '类目json',
            'cat' => '类目id',
            'brand' => '品牌',
            'brandid' => '品牌id',
            'venderid' => '供应商',
            'pic' => '商品图片',
            'price' => '商品价格',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    public static function goodsSave($sku)
    {
        try{
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
                array_push($sku_list,(string)$color->skuId);
            }
//            if(empty($sku_list)){
//                $sku_list[] = $sku;
//            }
            $goods = BaseGoods::findOne(['deleted'=>0,'sku'=>$sku]);
            if (empty($goods)){
                $goods = new BaseGoods();
            }
            $goods->sku = $sku;
            $goods->user_id = empty(Yii::$app->user->id) ? 0 : Yii::$app->user->id;
            $goods->sku_main = empty($data->data->main_sku_id) ? '' : $data->data->main_sku_id;
            $goods->sku_list = Json::encode($sku_list);
            $goods->spu = '';
            $goods->title = empty($data->data->title) ? '' : $data->data->title;
            $goods->shop_name = empty($data->data) ? '' : $data->data->name;
            $goods->shopid = empty($data->data) ? '' : $data->data->shop_id;
            $goods->cat_name = '';
            $goods->cat = 0;
            $goods->brand = '';
            $goods->brandid = isset($data->data->page_config->product->brand) ? $data->data->page_config->product->brand : 0;
            $goods->venderid = isset($data->data->page_config->product->venderId) ? (string)$data->data->page_config->product->venderId : "0";
            $goods->pic = $data->data->img;
            if (! $goods->save()){
                return ['result'=>0,'msg'=>'保存商品信息失败,请重试'];
            }

            $time_two = time() - $time - $time_one;

            $result = self::timeSave($time_one,$time_two,$sku);

            return ['result'=>1,'sku_list'=>$sku_list,'time_id'=>$result['id'],'goods'=>$goods];
        }catch (\Exception $e){
            return ['result'=>0,'msg'=>'服务器响应失败,请稍后重试..'];
        }

    }


    public static function timeSave($time_one,$time_two,$sku='')
    {
        try{
            $timeModel = new TimeList();
            $timeModel->sku_time = $time_one;
            $timeModel->sku = $sku;
            $timeModel->sku_save = $time_two;
            if (! $timeModel->save()){

            }
            return ['id'=>$timeModel->id];
        }catch (\Exception $e){
            return ['id'=>0];
        }

    }

    public static function shopInfo($shop_name)
    {
        try{
            $url = Yii::$app->params['shop_collect'] . urlencode($shop_name);
            $curl = new Curl();
            $collectString = $curl->get($url);

            if (strstr($collectString,"502 Bad Gateway")){
                return ['result'=>0,'msg'=>'采集店铺信息超时,请稍后重新查询.'];
            }
            $data = Json::decode($collectString,false);
            if ($data->code !=1){
                return ['result'=>0,'msg'=>$data->msg];
            }
            $shop_id = isset($data->data->shop_id)?$data->data->shop_id:'';
            return ['result'=>1,'shop_id'=>$shop_id];

        }catch (\Exception $e){
            return ['result'=>0,'msg'=>$e->getMessage()];
        }
    }
}
