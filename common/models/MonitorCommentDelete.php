<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "monitor_comment_delete".
 *
 * @property string $id
 * @property int $user_id
 * @property string $comment_id
 * @property string $sku 商品编号
 * @property string $nick_name 用户名
 * @property string $user_level_name 用户级别
 * @property int $score 评分
 * @property string $content 评语
 * @property string $buy_at 下单时间
 * @property string $creation_at 评论时间
 * @property int $deleted
 * @property int $version
 * @property string $create_at
 * @property string $update_at
 */
class MonitorCommentDelete extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'monitor_comment_delete';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'comment_id', 'score', 'buy_at', 'creation_at', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['sku'], 'string', 'max' => 32],
            [['nick_name'], 'string', 'max' => 50],
            [['user_level_name'], 'string', 'max' => 15],
            [['content'], 'string', 'max' => 255],
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
            'nick_name' => 'Nick Name',
            'user_level_name' => 'User Level Name',
            'score' => 'Score',
            'content' => 'Content',
            'buy_at' => 'Buy At',
            'creation_at' => 'Creation At',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }
}
