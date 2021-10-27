<?php

namespace common\models;

use common\models\Base;
use Yii;

/**
 * This is the model class for table "compare_ads".
 *
 * @property int $id
 * @property int $user_id
 * @property int $good_id
 * @property string $good_ads 广告词内容
 * @property int $is_change 是否有变动 0无 1有
 * @property int $begin_at 开始时间
 * @property int $end_at 结束时间
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class CompareAds extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'compare_ads';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'good_id', 'is_change', 'begin_at', 'end_at', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
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
            'good_ads' => 'Good Ads',
            'is_change' => 'Is Change',
            'begin_at' => 'Begin At',
            'end_at' => 'End At',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    public static function adsSave($good_id,$good_ads,$user_id='')
    {
        $ads = new CompareAds();
        $ads->user_id = $user_id?$user_id:Yii::$app->user->id;
        $ads->good_id = $good_id;
        $ads->good_ads = $good_ads;
        $ads->is_change = 1;
        $ads->begin_at = time();

        if (!$ads->save()){
            return ['result'=>0,'msg'=>$ads->getError()];
        }
        return ['result'=>1];
    }
}
