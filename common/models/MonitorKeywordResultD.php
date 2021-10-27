<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "monitor_keyword_result_d".
 *
 * @property string $id
 * @property int $user_id
 * @property int $keyword_id
 * @property int $good_id
 * @property string $sku
 * @property int $page_app app端页码
 * @property int $page_position_app app端位数
 * @property int $page_order_app app端总排名
 * @property int $page_pc pc端页码
 * @property int $page_position_pc pc端位数
 * @property int $page_order_pc pc端总排名
 * @property int $rank_change_app app排名变化
 * @property int $rank_change_pc pc排名变化
 * @property string $weight 权重分
 * @property int $weight_change 权重分变化
 * @property int $title_weight 标题权重分
 * @property string $app_search_at app查询时间
 * @property string $pc_search_at pc查询时间
 * @property string $err_msg 查询结果
 * @property int $deleted
 * @property int $version
 * @property string $create_at
 * @property string $update_at
 * @property int $rank_change_app_15 app排名变化15天
 * @property int $rank_change_app_7 app排名变化7天
 * @property int $rank_change_pc_15 pc排名变化15天
 * @property int $rank_change_pc_7 pc排名变化7天
 */
class MonitorKeywordResultD extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'monitor_keyword_result_d';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'keyword_id', 'good_id', 'page_app', 'page_position_app', 'page_order_app', 'page_pc', 'page_position_pc', 'page_order_pc', 'rank_change_app', 'rank_change_pc', 'weight', 'weight_change', 'title_weight', 'app_search_at', 'pc_search_at', 'deleted', 'version', 'create_at', 'update_at', 'rank_change_app_15', 'rank_change_app_7', 'rank_change_pc_15', 'rank_change_pc_7'], 'integer'],
            [['sku'], 'string', 'max' => 32],
            [['err_msg'], 'string', 'max' => 128],
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
            'keyword_id' => 'Keyword ID',
            'good_id' => 'Good ID',
            'sku' => 'Sku',
            'page_app' => 'Page App',
            'page_position_app' => 'Page Position App',
            'page_order_app' => 'Page Order App',
            'page_pc' => 'Page Pc',
            'page_position_pc' => 'Page Position Pc',
            'page_order_pc' => 'Page Order Pc',
            'rank_change_app' => 'Rank Change App',
            'rank_change_pc' => 'Rank Change Pc',
            'weight' => 'Weight',
            'weight_change' => 'Weight Change',
            'title_weight' => 'Title Weight',
            'app_search_at' => 'App Search At',
            'pc_search_at' => 'Pc Search At',
            'err_msg' => 'Err Msg',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'rank_change_app_15' => 'Rank Change App 15',
            'rank_change_app_7' => 'Rank Change App 7',
            'rank_change_pc_15' => 'Rank Change Pc 15',
            'rank_change_pc_7' => 'Rank Change Pc 7',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(),['id' => 'user_id']);
    }

    public function getGood()
    {
        return $this->hasOne(MonitorGoods::className(),['id'=>'monitor_goods_id']);
    }
}
