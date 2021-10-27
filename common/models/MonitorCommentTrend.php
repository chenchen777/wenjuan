<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "monitor_comment_trend".
 *
 * @property string $id
 * @property int $user_id
 * @property string $comment_id
 * @property string $sku 商品编号
 * @property string $show_comment_num 显示评论数
 * @property string $omit_comment_num 忽略评价数
 * @property string $delete_comment_num 删除评论数
 * @property int $deleted
 * @property int $version
 * @property string $create_at
 * @property string $update_at
 */
class MonitorCommentTrend extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'monitor_comment_trend';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'comment_id', 'show_comment_num', 'omit_comment_num', 'delete_comment_num', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
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
            'comment_id' => 'Comment ID',
            'sku' => 'Sku',
            'show_comment_num' => 'Show Comment Num',
            'omit_comment_num' => 'Omit Comment Num',
            'delete_comment_num' => 'Delete Comment Num',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }
}
