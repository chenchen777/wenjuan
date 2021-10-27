<?php

namespace common\models;

use linslin\yii2\curl\Curl;
//use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "monitor_keyword_result_c".
 *
 * @property int $id
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
 * @property int $weight 权重分
 * @property int $weight_change 权重分变化
 * @property int $title_weight 标题权重分
 * @property int $app_search_at app查询时间
 * @property int $pc_search_at pc查询时间
 * @property string $err_msg
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class MonitorKeywordResultC extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'monitor_keyword_result_c';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'keyword_id', 'good_id', 'page_app', 'page_position_app', 'page_order_app', 'page_pc', 'page_position_pc', 'page_order_pc', 'rank_change_app', 'rank_change_pc', 'weight', 'weight_change', 'title_weight', 'app_search_at', 'pc_search_at', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['sku'],'string'],
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
        ];
    }

}
