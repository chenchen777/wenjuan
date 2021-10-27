<?php

namespace common\models;

use common\models\Base;
use Yii;

/**
 * This is the model class for table "compare_comment".
 *
 * @property int $id
 * @property int $user_id
 * @property int $good_id
 * @property int $comments_all 评论总数
 * @property int $comments_high 好评
 * @property int $comments_middle 中评
 * @property int $comments_bad 差评
 * @property int $comments_image 带图评价
 * @property int $is_change
 * @property int $begin_at
 * @property int $end_at
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class CompareComment extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'compare_comment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'good_id', 'comments_all', 'comments_high', 'comments_middle', 'comments_bad','comments_image','is_change','begin_at','end_at', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
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
            'good_id' => 'Good ID',
            'comments_all' => 'Comments All',
            'comments_high' => 'Comments High',
            'comments_middle' => 'Comments Middle',
            'comments_bad' => 'Comments Bad',
            'comments_image' => 'Comments Image',
            'is_change' => 'Is Change',
            'begin_at' => 'Begin At',
            'end_at' => 'End At',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    public static function commentSave($good_id,$comments,$is_change=1,$user_id='')
    {
        $comment = new CompareComment();
        $comment->user_id = $user_id?$user_id:Yii::$app->user->id;
        $comment->good_id = $good_id;
        $comment->comments_all = $comments->total?$comments->total:0;
        $comment->comments_high = $comments->good?$comments->good:0;
        $comment->comments_middle = $comments->general?$comments->general:0;
        $comment->comments_bad = $comments->poor?$comments->poor:0;
        $comment->comments_image =  isset($comments->image)?$comments->image:0;
        $comment->is_change = $is_change?$is_change:0;
        $comment->begin_at = time();

        if (! $comment->save()){
            return ['result'=>0,'msg'=>$comment->getError()];
        }

        return ['result'=>1];
    }
}
