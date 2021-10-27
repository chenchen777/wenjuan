<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "compare_price".
 *
 * @property int $id
 * @property int $user_id
 * @property int $good_id
 * @property string $price 价格
 * @property int $is_change 版本是否变动 0未变 1有变动
 * @property int $begin_at 开始时间
 * @property int $end_at 结束时间
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class ComparePrice extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'compare_price';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'good_id', 'is_change', 'begin_at', 'end_at', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['price'], 'string', 'max' => 128],
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
            'price' => 'Price',
            'is_change' => 'Is Change',
            'begin_at' => 'Begin At',
            'end_at' => 'End At',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    public static function priceSave($price,$good_id,$is_change=1,$user_id='')
    {
        $model = new ComparePrice();
        $model->user_id = $user_id?$user_id:Yii::$app->user->id;
        $model->good_id = $good_id;
        $model->price = $price;
        $model->begin_at = time();
        $model->is_change = $is_change?$is_change:0;

        if (! $model->save()){
            return ['result'=>0,'msg'=>$model->getError()];
        }
        return ['result'=>1];
    }
}
