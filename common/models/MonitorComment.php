<?php

namespace common\models;

use Yii;
use linslin\yii2\curl\Curl;
use yii\helpers\Json;
/**
 * This is the model class for table "monitor_comment".
 *
 * @property string $id
 * @property int $user_id
 * @property string $shop_name 店铺名
 * @property string $good_name 商品名称
 * @property string $good_pic 商品图片
 * @property string $price 价格
 * @property int $status 添加状态 0未成功 1添加成功
 * @property int $deleted
 * @property int $version
 * @property string $create_at
 * @property string $update_at
 * @property string $sku 商品编号
 * @property string $comment_count 评论数
 * @property string $good_comment_count 好评数
 * @property string $good_rate 好评率
 */
class MonitorComment extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'monitor_comment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'status', 'deleted', 'version', 'create_at', 'update_at', 'comment_num', 'good_comment_num'], 'integer'],
            [['price', 'good_rate'], 'number'],
            [['shop_name', 'good_name', 'good_pic'], 'string', 'max' => 128],
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
            'good_name' => 'Good Name',
            'good_pic' => 'Good Pic',
            'price' => 'Price',
            'status' => 'Status',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'sku' => 'Sku',
            'comment_num' => 'Comment Count',
            'good_comment_num' => 'Good Comment Count',
            'good_rate' => 'Good Rate',
            'is_update' => 'is_update',
            'type' => 'type',
            'date' => 'date',
        ];
    }

    //  获取商品信息
    public static function getGoodInfo($parama){
        $url = Yii::$app->params['good_particulars'];
        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT,90);

        $collectString = $curl->setRequestBody(Json::encode($parama))
            ->setHeader('content-type', 'application/json')->post($url);
        if (strstr($collectString,"500 Internal Server Error")){
            return ['result'=>0,'msg'=>'商品链接或sku不正确'];
        }
        $data = Json::decode($collectString,true);
        if ($data['return_code'] != 200){
            return ['result'=>0,'msg'=>'商品链接或sku不正确.'];
        }

        $info = $data['result'];
        $data = [];
        $data['sku'] = $info['sku'];
        $data['shop_name'] = $info['shop_name'];
        $data['good_name'] = $info['title'];
        $data['good_pic'] = $info['master_img'];
        $data['comment_num'] = $info['CommentCount'];
        $data['good_comment_num'] = $info['goodCount'];
        $data['good_rate'] = number_format($info['good_rate'] * 100,2);
        return ['result'=>1,'msg'=>'','data'=>$data];
    }

    //  删除数
    public static function deleteComment($parama,$id,$uid){
        $url = Yii::$app->params['comm_rank_monitor'];
        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT,90);
        $postString = $curl->setRequestBody(Json::encode($parama))->setHeader('content-type', 'application/json')->post($url);
        $postString = json_decode($postString,true);

        if ($postString['return_code'] != 200){
            return ['result'=>0,'msg'=>'商品链接或sku不正确..'];
        }
        $info = $postString['result'];
        $comment_id = [];
        foreach($info as $k => $y){
            $comment_id[] = $y['comment_id'];
        }

        //  取出现有的
        $model_comment = [];
        $list = MonitorCommentShow::find()->where(['comment_id'=>$id])->andFilterWhere(['BETWEEN','creation_at',$parama['time_interval_start'],$parama['time_interval_end']])->asArray()->all();
        foreach ($list as $k => $y){
            $model_comment[] = $y['jd_comment_id'];
        }

        $result = array_diff($comment_id,$model_comment);

        $save_id = [];
        foreach ($result as $k => $y){
            array_push($save_id,$y);
        }
        //  更新删除

        if($save_id){
               $save_id = implode(",", $save_id);
               \Yii::$app->db->createCommand("UPDATE  `monitor_comment_show` SET type=1 WHERE jd_comment_id in ($save_id)")->execute();
        }

        $add_num = count($result);

        $start_at  = strtotime(date('Y-m-d',strtotime('-1 day')));
        $end_at  = $start_at + 86400;
        if($saveModel = MonitorCommentTrend::find()->where(['comment_id'=>$id])->andFilterWhere(['BETWEEN','create_at',$start_at,$end_at])->one()){
            $saveModel->delete_comment_num = $add_num;
            $saveModel->save();
        }
        return ;
    }

    //  评论
    public static function addComment($parama,$id,$type='',$uid=''){
        $url = Yii::$app->params['comm_rank_monitor'];
        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT,90);
        $postString = $curl->setRequestBody(Json::encode($parama))->setHeader('content-type', 'application/json')->post($url);
        $postString = json_decode($postString,true);

        if ($postString['return_code'] != 200){
            return ['result'=>0,'msg'=>'商品链接或sku不正确..'];
        }
        $info = $postString['result'];

        // 批量插入
        $ipFields = ['user_id','comment_id','sku','nick_name','user_level_name','score','content','buy_at','creation_at','create_at','update_at','jd_comment_id'];
        $ipValues = [];
        if($type == 'script'){
            $user_id = $uid;
        }else{
            $user_id = Yii::$app->user->id;
        }
        $day_start =  strtotime(date('Y-m-d',time()));
        $day_end = $day_start + 86400;
        $i = 0;

        foreach($info as $k => $y){
                $ipArray = [
                    $user_id,
                    $id,
                    $parama['sku'],
                    $y['user_name'],
                    $y['user_level_name'],
                    $y['comment_score'],
                    $y['comment_data'],
                    strtotime($y['buy_date']),
                    strtotime($y['comment_date']),
                    time(),
                    time(),
                    $y['comment_id']
                ];

                if($ipArray[8] > $day_start) {
                    $i++;
                }
                array_push($ipValues,$ipArray);
        }

        $status = \Yii::$app->db->createCommand()->batchInsert(MonitorCommentShow::tableName(), $ipFields, $ipValues)->execute();

        if(empty($status)){
            echo "显示评价入库失败".$id.'个数'.count($ipValues).'-------' . date('Y-m-d H:i',time()) . "\n";
        }
        //  开始时间
        $start_at = strtotime(date('Y-m-d',time()));
        $end_at = $start_at + 86400;
        //  记录新增数
        if($model = MonitorCommentTrend::find()->where(['comment_id'=>$id])->andFilterWhere(['BETWEEN','create_at',$start_at,$end_at])->one()){
            echo "趋势入库有了".$id.'-------' . date('Y-m-d H:i',time()) . "\n";
            $model->show_comment_num = $i;
        }else{
            $model = new MonitorCommentTrend();
            $model->user_id = $user_id;
            $model->comment_id = $id;
            $model->sku = $parama['sku'];
            $model->show_comment_num = $i;
        }
        if(!$model->save()){
            echo "趋势入库失败".$id.'-------' . date('Y-m-d H:i',time()) . "\n";
        }
        return;
    }

    // 折叠评论
    public static function addCommentOmit($parama,$id,$type='',$uid=''){
        $url = Yii::$app->params['sku_collect_fold'];
        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT,90);
        $postString = $curl->setRequestBody(Json::encode($parama))->setHeader('content-type', 'application/json')->post($url);
        $postString = json_decode($postString,true);
        if ($postString['return_code'] != 200){
            return ['result'=>0,'msg'=>'商品链接或sku不正确...'];
        }
        if($type == 'script'){
            $user_id = $uid;
        }else{
            $user_id = Yii::$app->user->id;
        }
        $info = $postString['result'];
        // 批量插入
        $ipFields = ['user_id','comment_id','sku','nick_name','user_level_name','score','content','buy_at','creation_at','create_at','update_at','jd_comment_id'];
        $ipValues = [];
        $user_id = $user_id;
        $info = $info['comment_list'];
        $day_start =  strtotime(date('Y-m-d',time()));
        $day_end = $day_start + 86400;
        $i = 0;

        foreach($info as $k => $y){
            $ipArray = [
                $user_id,
                $id,
                $parama['sku'],
                $y['user_name'],
                $y['user_level_name'],
                $y['comment_score'],
                $y['comment_data'],
                strtotime($y['buy_date']),
                strtotime($y['comment_date']),
                time(),
                time(),
                $y['comment_id']
            ];
            if($ipArray[8] > $day_start) {
                $i++;
            }
            array_push($ipValues,$ipArray);
        }
        $status = \Yii::$app->db->createCommand()->batchInsert(MonitorCommentOmit::tableName(), $ipFields, $ipValues)->execute();
//        if(empty($status)){
//            return ['result'=>0,'msg'=>'入库失败'];
//        }

        $start_at = strtotime(date('Y-m-d',time()));
        $end_at = $start_at + 86400;
        if($i>0){
            $model = MonitorCommentTrend::find()->where(['comment_id'=>$id])->andFilterWhere(['BETWEEN','create_at',$start_at,$end_at])->one();
            if(empty($model)){
                $model = new MonitorCommentTrend();
                $model->user_id = $user_id;
                $model->comment_id = $id;
                $model->sku = $parama['sku'];
                $model->omit_comment_num = $i;
            }else{
                $model->omit_comment_num = $i;
            }
            $model->save();
        }
        return;
    }
}
