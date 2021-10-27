<?php

namespace common\models;

use linslin\yii2\curl\Curl;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "jing_ask_log".
 *
 * @property int $id
 * @property int $user_id 用户的ID
 * @property int $ask_id jing_ask表关联id
 * @property int $page 页码
 * @property string $que_data 页码内所有信息
 * @property int $deleted
 * @property int $version
 * @property int $create_at 创建时间
 * @property int $update_at 最后一次更新时间
 */
class JingAskLog extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jing_ask_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'ask_id', 'page', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['que_data'], 'string'],
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
            'ask_id' => 'Ask ID',
            'page' => 'Page',
            'que_data' => 'Que Data',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }


    /**
     * @param $parama
     * @param $ask_id
     * 京问答采集入库
     */
    public static function jingAskSave($parama,$ask_id)
    {
        $cur_time = time();
        $url = CommentContent::actionRedis('');
        $url = $url . 'que';
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

        //商品信息入库
        $ask = JingAsk::findOne(['id'=>$ask_id]);
        $ask->good_title = $result->goods_name;
        $ask->good_img = $result->master_img;
        $ask->price = $result->price;
        $ask->shop_name = $result->shop_name;
        $ask->que_num = $result->que_num;
        $ask->good_count = $result->goodCount;
        $ask->shop_type = $result->shop_type=="pop" ? 0 : 1;
        $ask->time_pay = time() - $cur_time;
        if (!$ask->save()){
            return ['result'=>0,'msg'=>$ask->getError()];
        }

        //问答信息根据页码排序
        $questions = $result->questions;
        $lists = [];
        if (count($questions) != 0){
            foreach ($questions as $question){
                $lists[$question->page][$question->place] = $question;
            }
        }
        ksort($lists);
        //将排序后的问答信息处理为可入库格式
        $data_list = [];
        $first_list = [];
        foreach ($lists as $list){
            ksort($list);
            if (count($first_list)==0){  //将第一页数据返回给前端
                $first_list = $list;
            }
            $temp = [];
            $temp['page'] = $list[1]->page;
            $temp['user_id'] = Yii::$app->user->id;
            $temp['ask_id'] = $ask_id;
            $temp['que_data'] = Json::encode($list);
            $temp['update_at'] = time();
            $temp['create_at'] = time();
            array_push($data_list,$temp);
        }


        $columes = ['page','user_id','ask_id','que_data','update_at','create_at'];

        try{
            if (count($lists) != 0){
                $result = \Yii::$app->db->createCommand()->batchInsert('jing_ask_log', $columes, $data_list)->execute();
                if (!$result){
                    return ['result'=>0,'msg'=>'批量入库失败...'];
                }
            }


        }catch (\Exception $e){
            return ['result'=>0,'msg'=>'入库失败: ' . $e->getMessage()];
        }
        unset($list);
        $total_page = count($data_list);
        $push_data['img_url'] = $ask->good_img;
        $push_data['good_title'] = $ask->good_title;
        $push_data['price'] = $ask->price;
        $push_data['good_comment'] = $ask->good_count;
        $push_data['id'] = $ask->id;
        $push_data['que_num'] = $ask->que_num;
        $push_data['total_page'] = $total_page;
        $push_data['list'] = $first_list;

        $push_data['result'] = 1;

        return $push_data;

    }
}
