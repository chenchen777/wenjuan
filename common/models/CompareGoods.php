<?php

namespace common\models;

use linslin\yii2\curl\Curl;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "compare_goods".
 *
 * @property int $id
 * @property int $user_id
 * @property string $shop_name 店铺名称
 * @property string $good_pic 商品图片
 * @property string $sku
 * @property string $title 标题
 * @property string $ads 广告词
 * @property string $salers 促销信息
 * @property int $salers_count 促销数量
 * @property string $price 价格
 * @property string $comments 评价
 * @property int $is_update 是否已更新 0否 1是
 * @property int $title_begin_at 标题更新时间
 * @property int $ads_begin_at 广告词更新时间
 * @property int $salers_begin_at 促销更新时间
 * @property int $price_begin_at 价格更新时间
 * @property int $comment_begin_at 评论更新时间
 * @property int $monitor_close 是否取消监控 0否 1是
 * @property int $search_count 每日查询次数
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class CompareGoods extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'compare_goods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'salers_count', 'title_begin_at', 'ads_begin_at', 'salers_begin_at', 'price_begin_at', 'comment_begin_at', 'monitor_close','is_update','search_count', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['salers'], 'string'],
            [['price'], 'number'],
            [['shop_name', 'good_pic', 'title', 'comments'], 'string', 'max' => 128],
            [['sku'], 'string', 'max' => 32],
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
            'shop_name' => 'Shop Name',
            'good_pic' => 'Good Pic',
            'sku' => 'Sku',
            'title' => 'Title',
            'ads' => 'Ads',
            'salers' => 'Salers',
            'salers_count' => 'Salers Count',
            'price' => 'Price',
            'comments' => 'Comments',
            'title_begin_at' => 'Title Begin At',
            'ads_begin_at' => 'Ads Begin At',
            'salers_begin_at' => 'Salers Begin At',
            'price_begin_at' => 'Price Begin At',
            'comment_begin_at' => 'Comment Begin At',
            'monitor_close' => 'Monitor Close',
            'is_update' => 'Is Update',
            'search_count' => 'Search Count',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    public static function compareSave($sku)
    {
        $trans = Yii::$app->db->beginTransaction();
        try{
            $url = Yii::$app->params['sku_collect'] . $sku . "&cap=1&price=1";
            $curl = new Curl();
            $collectString = $curl->get($url);
            if (strstr($collectString,"502 Bad Gateway")){
                $trans->rollBack();
                return ['result'=>0,'msg'=>'采集商品信息超时,请稍后重新查询.'];
            }
            $data = Json::decode($collectString,false);

            if ($data->result != 1){
                $trans->rollBack();
//                return ['result'=>0,'msg'=>'商品链接或sku不正确.'];
                return ['result'=>0,'msg'=>'查询超时.'];
            }

            $good = CompareGoods::findOne(['deleted'=>0,'sku'=>$sku,'user_id'=>Yii::$app->user->id]);
            if ($good){
                $trans->rollBack();
                return ['result'=>0,'msg'=>'同种竞品信息已存在，请勿重复添加'];
            }

            $result = $data->data?$data->data:'';
            if (! $result){
                $trans->rollBack();
                return ['result'=>0,'msg'=>'竞品信息获取失败，请稍后重试'];
            }

            $model = new CompareGoods();
            $model->user_id = Yii::$app->user->id;
            $model->shop_name = $result->name;
            $model->good_pic = $result->img;
            $model->sku = $sku;
            $model->title = $result->title;
            $model->ads = $result->ads?Json::encode($result->ads):'';
            $model->salers = $result->proms?Json::encode($result->proms):'';
            $model->salers_count = $result->proms?count($result->proms):0;
            $model->price = $result->price?$result->price:0;
            $model->comments = $result->commentsCount?Json::encode($result->commentsCount):'';
            $model->title_begin_at = time();
            $model->ads_begin_at = time();
            $model->salers_begin_at = time();
            $model->price_begin_at = time();
            $model->comment_begin_at = time();
            $model->is_update = 1;

            if (!$model->save()){
                $trans->rollBack();
                return ['result'=>0,'msg'=>$model->getError()];
            }

            //广告词记录
            $ads = CompareAds::adsSave($model->id,$model->ads);
            if ($ads['result'] != 1){
                $trans->rollBack();
                return ['result'=>0,'msg'=>$ads['msg']];
            }

            //评论数量记录
            $comment = CompareComment::commentSave($model->id,$result->commentsCount);
            if ($comment['result'] != 1){
                $trans->rollBack();
                return ['result'=>0,'msg'=>$comment['msg']];
            }


            //价格记录
            $compare_price = ComparePrice::priceSave($model->price,$model->id);
            if ($compare_price['result'] != 1){
                $trans->rollBack();
                return ['result'=>0,'msg'=>$compare_price['msg']];
            }

            //促销信息记录
            $saler = CompareSaler::salerSave($model->id,$model->salers);
            if ($saler['result'] != 1){
                return ['result'=>0,'msg'=>$saler['msg']];
            }

            //标题记录
            $title = CompareTitle::titleSave($model->id,$model->title);
            if ($title['result'] != 1){
                $trans->rollBack();
                return ['result'=>0,'msg'=>$title['msg']];
            }

            $trans->commit();
            return ['result'=>1];

        }catch (\Exception $e){
            $trans->rollBack();
            return ['result'=>0,'msg'=>$e->getMessage()];
        }
    }

    public function getUser()
    {
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }
}
