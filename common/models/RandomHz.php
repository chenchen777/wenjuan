<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "random_hz".
 *
 * @property int $id
 * @property int $version
 * @property int $deleted
 * @property int $create_at
 * @property int $update_at
 * @property int $pc_hz_1
 */
class RandomHz extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'random_hz';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['version', 'deleted', 'create_at', 'update_at', 'pc_hz_1'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'pc_hz_1' => 'pc_hz_1',
        ];
    }

    public static function todayCount(){
        $todayTime = strtotime(date('Y-m-d',time()));
        $hzLog = RandomHz::find()->where(['>', 'create_at', $todayTime])->one();
        /* @var $hzLog RandomHz*/
        if(empty($hzLog)){
            $hzLog = new RandomHz();
            $hzLog->create_at = time();
            $hzLog->update_at = time();
            $hzLog->pc_hz_1 = 0;
        }
        $hzLog->pc_hz_1++;
        $hzLog->save();
    }
}
