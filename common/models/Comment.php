<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "comment".
 *
 * @property int $id
 * @property int $user_id
 * @property string $sku
 * @property int $type 评价类型 0全部 1差评 2中评 3好评 4晒图
 * @property int $sort 5 推荐排序 6时间排序
 * @property int $only 1只看当前商品评价 2查看全部
 * @property int $page_start 起始页码
 * @property int $page_end 结束页码
 * @property int $create_at
 * @property int $update_at
 * @property int $version
 * @property int $deleted
 */
class Comment extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'sort', 'only', 'fold', 'page_start', 'page_end', 'create_at', 'update_at', 'version', 'deleted'], 'integer'],
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
            'type' => 'Type',
            'search_ip' => 'search_ip',
            'sort' => 'Sort',
            'only' => 'Only',
            'fold' => 'Fold',
            'page_start' => 'Page Start',
            'page_end' => 'Page End',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'good_comment_sum' => 'good_comment_sum',
            'status' => 'status',
        ];
    }
}
