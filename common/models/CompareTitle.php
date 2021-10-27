<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "compare_title".
 *
 * @property int $id
 * @property int $user_id
 * @property int $good_id
 * @property string $good_title 商品标题
 * @property int $is_change 版本是否变动 0未变 1有变动
 * @property int $begin_at 开始时间
 * @property int $end_at 结束时间
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class CompareTitle extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'compare_title';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'good_id', 'is_change', 'begin_at', 'end_at', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['good_title'], 'string', 'max' => 128],
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
            'good_title' => 'Good Title',
            'is_change' => 'Is Change',
            'begin_at' => 'Begin At',
            'end_at' => 'End At',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    public static function titleSave($good_id,$title,$user_id='')
    {
        $model = new CompareTitle();
        $model->user_id = $user_id?$user_id:Yii::$app->user->id;
        $model->good_id = $good_id;
        $model->good_title = $title;
        $model->is_change = 1;
        $model->begin_at = time();
        if (!$model->save()){
            return ['result'=>0,'msg'=>$model->getError()];
        }

        return ['result'=>1];
    }
}
