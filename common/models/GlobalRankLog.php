<?php

namespace common\models;

use linslin\yii2\curl\Curl;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "global_rank_log".
 *
 * @property int $id
 * @property int $rank_id
 * @property int $user_id
 * @property string $sku
 * @property int $page 排名页数
 * @property int $page_position 位数
 * @property int $page_order 总排名
 * @property string $comment 评论数
 * @property int $is_ad 是否广告 1表示广告 0表示非广告
 * @property string $price 价格
 * @property int $type 1自营 2非自营
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
 */
class GlobalRankLog extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'global_rank_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rank_id', 'user_id', 'page', 'page_position', 'page_order', 'is_ad', 'type', 'is_double', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['user_id', 'good_url'], 'required'],
            [['price'], 'number'],
            [['good_url'], 'string'],
            [['sku'], 'string', 'max' => 32],
            [['comment', 'good_title', 'good_img_url', 'promotion_logo'], 'string', 'max' => 255],
            [['double_price'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rank_id' => 'Rank ID',
            'user_id' => 'User ID',
            'sku' => 'Sku',
            'page' => 'Page',
            'page_position' => 'Page Position',
            'page_order' => 'Page Order',
            'comment' => 'Comment',
            'is_ad' => 'Is Ad',
            'price' => 'Price',
            'type' => 'Type',
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
        ];
    }

    /**
     * @param $parama
     * @param $mode   1指定商品 2指定店铺
     * @param $client_type  1电脑端 2微信端 3移动端
     *
     */
    public static function globalRankSave($parama,$mode,$client_type,$rand_id,$sku,$keyword)
    {
        $path = self::getSearchPath($mode,$client_type);
        $url = CommentContent::actionRedis('');
        $url = $url .$path;
        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT,90);
        $postString = $curl->setRequestBody(Json::encode($parama))
                            ->setHeader('content-type', 'application/json')->post($url);
        $data = Json::decode($postString,false);
        $code = $data->return_code;
        $err_msg = $data->msg;
        if ($code != 200){
            return ['result'=>0,'msg'=>$err_msg];
        }

        $result = $data->result;
        if ($mode==1){
            $querys[0] = $result;
        }else {
            $querys = $result;
        }

        $rank_list = [];   //入库使用
        $datas = [];       //前端返回使用

        foreach ($querys as $query){
            $temp = [];
            $data = [];
            $list = isset($query->relevance) ? $query->relevance : [];

            $temp['page'] = $query->page;
            $temp['page_position'] = $query->place;
            $temp['page_order'] = $query->rank;
            $temp['good_title'] = $query->goods_name;
            $cur_sku = $query->sku;
            $temp['good_url'] = "https://item.jd.hk/" . $cur_sku . ".html";  //todo...商品链接
            $temp['rank_id'] = $rand_id;
            $temp['user_id'] = Yii::$app->user->id;
            $temp['sku'] = $cur_sku;
            $temp['type'] = $query->shop_type=="pop" ? 0 : 1;
            $temp['price'] = $query->price;
            $temp['img_url'] = $query->master_img;

            //查找匹配sku
            foreach ($list as $li){
                if ($li->relevance_sku==$sku){
                    $temp['price'] = $li->relevance_price;
                    $temp['img_url'] = $li->relevance_img;
                }
            }
            $temp['update_at'] = time();
            $temp['create_at'] = time();

            $data = $temp;
            $data['keyword'] = $keyword;
            array_push($rank_list,$temp);
            array_push($datas,$data);
        }

        $columes = ['page','page_position','page_order','good_title','good_url','rank_id','user_id','sku','type','price','good_img_url','update_at','create_at'];

        try{
            $result = \Yii::$app->db->createCommand()->batchInsert('global_rank_log', $columes, $rank_list)->execute();
            if (!$result){
                return ['result'=>0,'msg'=>'批量入库失败...'];
            }

        }catch (\Exception $e){
            return ['result'=>0,'msg'=>'入库失败: ' . $e->getMessage()];
        }

        return ['result'=>1,'data'=>$datas];

    }

    //全球购排名接口分类
    public static function getSearchPath($mode,$client_type)
    {
        $paths = [
            '1' => [    //指定商品
              '1' => "global_shopping",    //pc
              "2" => "wx_global_shopping",    //微信
              "3" => "app_global_shopping",    //移动端
            ],
            '2' => [  //指定店铺
                '1' => "global_shopping_shop",
                "2" => "wx_global_shopping_shop",
                "3" => "app_global_shopping_shop",
            ],
        ];

        return $paths[$mode][$client_type];
    }


    public function getRank()
    {
        return $this->hasOne(GlobalRank::className(),['id'=>'rank_id']);
    }

}
