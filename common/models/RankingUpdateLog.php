<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "RankingUpdateLog".
 *
 * @property int $id
 * @property string $user_id 用户id
 * @property int $create_at
 * @property int $update_at
 */
class RankingUpdateLog extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ranking_update_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            [['name', 'validity_month'], 'required'],
//            [['price', 'promotion_price'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户id',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

//    public function getService(){
//        return $this->hasMany(User::className(),['user_id'=>'id']);
//    }


}
