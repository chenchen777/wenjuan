<?php

namespace common\models;

use Exception;

/**
 * This is the model class for table "weight_search_log".
 *
 * @property string $id
 * @property string $keyword        查询关键词
 * @property int $search_id         查询结果ID(service_keyword_search表)
 * @property int $create_at         查询时间
 *
 * @property ServiceKeywordSearch $keywordSearch 关键词搜索记录
 */
class WeightSearchLog extends Base
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'weight_search_log';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeywordSearch(){
        return $this->hasOne(ServiceKeywordSearch::className(), ['id' => 'search_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['search_id', 'create_at'], 'integer'],
            [['keyword'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'keyword' => '查询关键词',
            'search_id' => '查询ID',
            'create_at' => '查询时间'
        ];
    }

    /**
     * @param $keyword
     * @param $searchId
     * @return WeightSearchLog
     * @throws
     */
    public static function addLog($keyword, $searchId){
        try{
            $log = new self();
            $log->keyword = $keyword;
            $log->search_id = $searchId;
            $log->create_at = time();

            $log->save();
        }catch (\Exception $e){
            throw new Exception($e);
        }
        return $log;
    }

    /**
     * @param $keyword
     * @param int $limitHour
     * @return WeightSearchLog|array
     */
    public static function getRecentLog($keyword, $limitHour = 2){
        $limitTime = time() - $limitHour * 300;
        $log = self::find()
            ->where([
                'keyword' => $keyword
            ])
            ->andWhere(['>', 'create_at', $limitTime])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        /* @var self $log*/
        if (!empty($log)){
            return $log;
        }
        return [];
    }
}
