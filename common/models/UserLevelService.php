<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_level_service".
 *
 * @property int $id
 * @property int $level_id 等级
 * @property int $service_id 服务
 * @property int $cost_point 该等级需消耗点数
 * @property int $day_limit 每日限制次数
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class UserLevelService extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_level_service';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'service_id', 'user_level_id','cost_point', 'day_limit'], 'required'],
            [[ 'service_id', 'user_level_id','cost_point', 'day_limit', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'service_id' => '对应权限',
            'user_level_id' => '用户等级',
            'cost_point' => 'Cost Point',
            'day_limit' => '次数限制(-1是不限次数,0是无权限)',
            'is_daily_reset' => '每日重置1需要   0不需要',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    public function getLevel(){
        return $this->hasOne(UserLevel::className(),['id'=>'user_level_id']);
    }

    public function getService(){
        return $this->hasOne(Service::className(),['id'=>'service_id']);
    }

    //获取用户的剩余监控次数
    public static  function  getSurplusNum(){
        $user_id = Yii::$app->user->id;
        $user_info = User::findOne(['id'=>$user_id]);

        $num = MonitorComment::find()->where(['user_id'=>$user_id,'status'=>1,'deleted'=>0])->count();
        $big =  UserLevelService::find()->where(['service_id'=>11,'user_level_id'=>$user_info->level_id])->one();
        if(empty($big)){
            return 0;
        }
        $num = $big->day_limit - $num;
        return $num;
    }
}
