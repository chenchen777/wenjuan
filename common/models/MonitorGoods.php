<?php

namespace common\models;

use linslin\yii2\curl\Curl;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "monitor_goods".
 *
 * @property int $id
 * @property int $user_id
 * @property string $shop_name  店铺名
 * @property string $good_name 商品名称
 * @property string $good_pic 商品图片
 * @property string $types 类目
 * @property string $cat  类目代码
 * @property string $sku
 * @property string $sku_list
 * @property int $search_type 查询方式  1当前sku  2最高sku
 * @property string $main_sku_id spu
 * @property number $price 价格
 * @property int $is_top 是否置顶 0否 1是
 * @property int $is_competitor 是否竞品 0否 1是
 * @property int $compare_good_id 竞品关联的商品id
 * @property int $status 0添加未成功 1添加成功
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 * @property string $city
 * @property string $city_show
 */
class MonitorGoods extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'monitor_goods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'is_top', 'is_competitor', 'compare_good_id','search_type', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['shop_name','good_name', 'good_pic','types','cat','city'], 'string', 'max' => 128],
            [['sku','main_sku_id'], 'string', 'max' => 32],
            [['sku_list'],'string'],
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
            'shop_name' => '店铺名',
            'good_name' => 'Good Name',
            'good_pic' => 'Good Pic',
            'types' => 'Types',
            'cat' => 'Cat',
            'price' => '价格',
            'search_type' => 'Search Type',
            'sku' => 'Sku',
            'sku_list' => 'Sku List',
            'main_sku_id' => 'Main Sku Id',
            'is_top' => 'Is Top',
            'is_competitor' => 'Is Competitor',
            'compare_good_id' => 'Compare Good ID',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'city' => 'city',
            'city_show' => 'city_show',
        ];
    }

    public static function getGoodInfoH($search_type,$sku,$type=0,$good_id=0)
    {
        try{
            $url = Yii::$app->params['sku_collect'] . $sku . "&price=1&stock=0&price=0";
            $curl = new Curl();
            $collectString = $curl->get($url);

            if (strstr($collectString,"502 Bad Gateway")){
                return ['result'=>0,'msg'=>'采集商品信息超时,请稍后重新查询.'];
            }
            $data = Json::decode($collectString,false);
            if ($data->result != 1){
                return ['result'=>0,'msg'=>'商品链接或sku不正确.'];
            }

            $info = $data->data;

            $colors = $info->page_config->product->colorSize;
            $sku_list = [];
            foreach ($colors as $color){
                array_push($sku_list,(string)$color->skuId);
            }

            $types = '';
            if ($info->types){
                $types = implode(',',$info->types);
            }

            $cat = '';
            if (isset($info->page_config->product->cat)){
                if ($info->page_config->product->cat){
                    $cat = implode(',',$info->page_config->product->cat);
                }
            }
            return ['result'=>1,'cat'=>$cat,'types'=>$types,'sku_list'=>$sku_list];

        }catch (\Exception $e){
            return ['result'=>0,'msg'=>$e->getMessage()];
        }
    }

    public static function getGoodInfo($search_type,$sku,$type=0,$good_id=0,$city='')
    {
        try{
            $url = Yii::$app->params['sku_collect'] . $sku . "&stock=0&price=0";
            $curl = new Curl();
            $collectString = $curl->get($url);

            if (strstr($collectString,"502 Bad Gateway")){
                return ['result'=>0,'msg'=>'采集商品信息超时,请稍后重新查询.'];
            }
            $data = Json::decode($collectString,false);
            if ($data->result != 1){
                return ['result'=>0,'msg'=>'商品链接或sku不正确.'];
            }

            $info = $data->data;

            $colors = $info->page_config->product->colorSize;
            $sku_list = [];
            foreach ($colors as $color){
                array_push($sku_list,(string)$color->skuId);
            }

            $types = '';
            if ($info->types){
                $types = implode(',',$info->types);
            }

            $cat = '';
            if (isset($info->page_config->product->cat)){
                if ($info->page_config->product->cat){
                    $cat = implode(',',$info->page_config->product->cat);
                }
            }


            $model = new MonitorGoods();
            $model->sku = $sku;
            $model->sku_list = Json::encode($sku_list);
            $model->main_sku_id = $info->main_sku_id;
            $model->search_type = $search_type;
            $model->shop_name = $info->name;
            $model->user_id = Yii::$app->user->id;
            $model->good_name = $info->title;
            $model->good_pic = $info->img;
            $model->types = $types;
            $model->cat = $cat;
            $model->price = $info->price;
            $model->city = $city;
            if ($type ==1){
                $model->is_competitor = 1;
                $model->compare_good_id = $good_id;
            }
            if (!$model->save()){
                return ['result'=>0,'msg'=>$model->getError()];
            }

            return ['result'=>1,'good_id' => $model->id,'cat'=>$cat,'types'=>$types,'sku_list'=>$sku_list];

        }catch (\Exception $e){
            return ['result'=>0,'msg'=>$e->getMessage()];
        }

    }

    public function getUser()
    {
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }

}
