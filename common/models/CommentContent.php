<?php

namespace common\models;

use linslin\yii2\curl\Curl;
use Yii;
use yii\helpers\Json;
/**
 * This is the model class for table "comment_content".
 *
 * @property string $id
 * @property int $user_id
 * @property string $sku
 * @property string $create_at
 * @property string $update_at
 * @property string $version
 * @property int $deleted
 */
class CommentContent extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comment_content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'create_at', 'update_at', 'version', 'deleted'], 'integer'],
            [['create_at', 'update_at'], 'required'],
            [['sku'], 'string', 'max' => 128],
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
            'sku' => 'Sku',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'version' => 'Version',
            'deleted' => 'Deleted',
        ];
    }

    public static function actionRedis($type=''){
        $zrange = 'servers_comment';
        //  从队列取值
        $redis = Yii::$app->redis;

        $servers_list_count = $redis->zcard($zrange);
        $servers_list = $redis->zrange($zrange, 0, 0, 'WITHSCORES');

        if(empty($servers_list)){
            $url = Yii::$app->params['comment_rank_url'];
            foreach($url as $k =>$y){
                $redis->zadd($zrange,time(),$k);
            }
        }else{
            $servers_list = $servers_list[0];
        }
        // 删除
        $redis->zrem($zrange,$servers_list);
        $ip_list = Yii::$app->params['comment_rank_url'][$servers_list];
        //  插入
        $redis->zadd($zrange,time(),$servers_list);
        return $ip_list;
    }

    //  折叠查询
    public static function foldComment($parama,$model){
        $url = Yii::$app->params['sku_collect_fold'];
        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT,90);
        $postString = $curl->setRequestBody(Json::encode($parama))->setHeader('content-type', 'application/json')->post($url);
        $postString = json_decode($postString,true);
        $content = $postString['result'];
        CommentContent::addContent(json_encode($content),$model->id);
        $log = CommentContent::find()->where(['comment_id'=>$model->id])->all();
        $list['label_list'] = [];
        $dataList = [];
        foreach ($log as $k => $y){
            $d_list = [];
            $dataLista = Json::decode($y->data);
            $ii = 0;
            foreach($dataLista as $k1 => $y1){
                $d_list[$ii] = $y1;
                $ii++;
            }
            $dataList[] = $d_list;
        }
        $list['list'] = $dataList;
        $list['comment_id'] = $model->id;
        $list['goods_info'] = [
            'goods_info' => $model->good_title,
            'goods_title' => $model->good_title,
            'shop_name' => $model->shop_name,
            'goods_price' => $model->price,
            'goods_pic' => $model->good_img,
            'goods_comment_sum' => $model->good_comment_sum,
        ];
        return $list;
    }

    // 发送任务
    public static function skuAssignment($parama){
        $url = CommentContent::actionRedis('comment').'comment';
        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT,90);
        $parama['source'] = \Yii::$app->request->hostInfo;
        $postString = $curl->setRequestBody(Json::encode($parama))->setHeader('content-type', 'application/json')->post($url);
        return;
    }

    //  评论记录
    public static function  addContent($date,$logId){
        $data = Json::decode($date,true);
        $saveComment = Comment::find()->where(['id'=>$logId])->one();
        if(empty($saveComment)){
            return ['result'=>0,'msg'=>'id不存在'];
        }
        $dataResult = $data;
        //  标签
        $labelList = [];
        if(!empty($dataResult['label_list'])){
            $questions = $dataResult['label_list'];
            $rank_list = [];   //入库使用
            foreach($questions as $k => $y){
                $temp = [];
                $data = [];
                $data['label_id'] = $y['label_id'];
                $data['label_name'] = $y['label_name'];
                $data['label_count'] = $y['label_count'];

                $temp['label_id'] = $y['label_id'];
                $temp['label_name'] = $y['label_name'];
                $temp['label_count'] = $y['label_count'];
                $temp['comment_id'] = $logId;
                $temp['user_id'] = empty(Yii::$app->user->id) ? 0 : Yii::$app->user->id;
                $temp['create_at'] = time();
                $temp['update_at'] = time();
                array_push($rank_list,$temp);
                array_push($labelList,$data);
            }
            $columes = ['label_id','label_name','label_count','comment_id','user_id','create_at','update_at'];
            $result = \Yii::$app->db->createCommand()->batchInsert('comment_label', $columes, $rank_list)->execute();
        }


        //  全部评价
        $first_list = [];
        $rank_list = [];   //入库使用
        if(!empty($dataResult['comment_list'])){
            $questions = $dataResult['comment_list'];
            $lists = [];
            if (count($questions) != 0){
                foreach ($questions as $question){
                    $lists[$question['page']][$question['place']] = $question;
                }
            }
            ksort($lists);

            foreach($lists as $k => $y){
                if (count($first_list)==0){  //将第一页数据返回给前端
                    $first_list = $y;
                }
                $temp = [];

                $temp['page'] = $y[1]['page'];
                $temp['user_id'] = empty(Yii::$app->user->id) ? 0 : Yii::$app->user->id;
                $temp['comment_id'] = $logId;
                $temp['data'] = Json::encode($y);
                $temp['label_id'] = '';
                $temp['create_at'] = time();
                $temp['update_at'] = time();
                array_push($rank_list,$temp);
            }

            $columes = ['page','user_id','comment_id','data','label_id','update_at','create_at'];
            try{
                $result = \Yii::$app->db->createCommand()->batchInsert('comment_content', $columes, $rank_list)->execute();
                if (!$result){
                    return ['result'=>0,'msg'=>'批量入库失败...'];
                }

            }catch (\Exception $e){
                return ['result'=>0,'msg'=>'入库失败: ' . $e->getMessage()];
            }
        }


        // 类型评论
//        if(isset($dataResult['label_comment_item'])){
//            $questions = $dataResult['label_comment_item'];
//            $lists = [];
//            if (count($questions) != 0){
//                foreach ($questions as $question){
//                    $lists[$question['page']][$question['place']] = $question;
//                }
//            }
//            ksort($lists);
//            $rank_list = [];   //入库使用
//            foreach($lists as $k => $y){
////                ksort($list);
//                $temp = [];
//                $temp['page'] = $y[1]['page'];
//                $temp['user_id'] = empty(Yii::$app->user->id) ? 0 : Yii::$app->user->id;
//                $temp['comment_id'] = $logId;
//                $temp['data'] = Json::encode($y);
//                $temp['label_id'] = $y['maidianInfo'];
//                $temp['create_at'] = time();
//                $temp['update_at'] = time();
//                array_push($rank_list,$temp);
//            }
//            $columes = ['page','user_id','comment_id','data','label_id','update_at','create_at'];
//            $result = \Yii::$app->db->createCommand()->batchInsert('comment_content', $columes, $rank_list)->execute();
//        }
        $saveComment->status = 1;
        if(!$saveComment->save()){
            return ['result'=>0, 'msg'=>'入库失败'];
        }
//        $total_page = count($rank_list);
//        $date = [];
//        $date['label_list'] = $labelList;
//        $date['total_page'] = $total_page;
//        $date['list'] = $first_list;
//        $date['result'] = 1;
        return ['result'=>1, 'msg'=>'入库成功'];
    }
}
