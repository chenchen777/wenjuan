<?php

namespace common\models;

use common\models\Base;
use Yii;

/**
 * This is the model class for table "monitor_keyword".
 *
 * @property int $id
 * @property int $user_id
 * @property int $monitor_goods_id
 * @property int $keyword_id 竞品关联的关键词id
 * @property string $keyword 关键词
 * @property string $category 类目
 * @property int $is_category 是否类目监控
 * @property string $sku
 * @property int $monitor_close 是否取消监控 0否 1是
 * @property int $is_update_pc pc是否已更新 0 否 1 是
 * @property int $is_update_app app是否已更新
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 *
 * @property User $user
 * @property MonitorGoods $good
 */
class MonitorKeyword extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'monitor_keyword';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'monitor_goods_id','keyword_id', 'is_category', 'monitor_close','is_update_pc','is_update_app', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['category'], 'string', 'max' => 128],
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
            'monitor_goods_id' => 'Monitor Goods ID',
            'keyword_id' => 'Keyword Id',
            'keyword' => 'Keyword',
            'category' => 'Category',
            'is_category' => 'Is Category',
            'sku' => 'Sku',
            'monitor_close' => 'Monitor Close',
            'is_update_pc' => 'Is Update Pc',
            'is_update_app' => 'Is Update App',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }


    //竞品关键词添加
    public static function keywordSave($keyword,$good_id,$sku,$keyword_id,$types,$is_category)
    {

        $model = new MonitorKeyword();
        $model->user_id = Yii::$app->user->id;
        $model->monitor_goods_id = $good_id;
        $model->keyword_id = $keyword_id;
        $model->keyword = $keyword;
        $model->sku = $sku;
        if ($is_category){
            $model->is_category = 1;
            $model->category = $types;
            $model->is_update_pc = 1;
        }

        if (!$model->save()){
            return ['result'=>0,'msg'=>'关键词保存失败'];
        }

        return ['result'=>1,'rival_keyword_id'=>$model->id];
    }

    //监控商品关键词添加
    public static function keywordsAllSave($keywords, $good_id, $sku)
    {
        $data = [];
        foreach ($keywords as $keyword){
            $model = MonitorKeyword::findOne(['deleted' => 0, 'monitor_close'=>0, 'monitor_goods_id' => $good_id, 'keyword' => $keyword]);
            $result = [];
            if (!$model){
                $result = [
                    'user_id'    => Yii::$app->user->id,
                    'monitor_goods_id' => $good_id,
                    'keyword'   => $keyword,
                    'sku'       => $sku,
                    'create_at' => time(),
                    'update_at' => time()
                ];
                array_push($data,$result);
            }

        }
        if (!$data){
            return ['result'=>1];
        }

        $_cloumns = ['user_id','monitor_goods_id','keyword','sku','create_at','update_at'];

        $tran = \Yii::$app->db->beginTransaction();
        try{
            $result = \Yii::$app->db->createCommand()->batchInsert('monitor_keyword', $_cloumns, $data)->execute();
            if (!$result) {
                $tran->rollBack();
                return ['result'=>0,'msg'=>'关键词保存失败'];
            }
        }catch (\Exception $e){
            $tran->rollBack();
            return ['result'=>0,'msg'=>$e->getMessage()];
        }
        $tran->commit();

        return ['result'=>1];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(),['id' => 'user_id']);
    }

    public function getGood()
    {
        return $this->hasOne(MonitorGoods::className(),['id'=>'monitor_goods_id']);
    }
    public function getMonitorKeywordResultD()
    {
        return $this->hasOne(MonitorKeywordResultD::className(),['keyword_id'=>'id']);
    }
}
