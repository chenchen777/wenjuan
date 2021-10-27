<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "cps_user".
 *
 * @property int $id
 * @property int $up_user_id 上级
 * @property int $up_user_type 上线用户类型
 * @property int $user_id 新注册用户
 * @property int $reward_point 奖励积分
 * @property int $draw_rate 提成比例
 * @property int $status 状态 1已成功
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class CpsUser extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cps_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['up_user_id', 'up_user_type', 'user_id', 'reward_point', 'draw_rate'], 'required'],
            [['up_user_id', 'up_user_type', 'user_id', 'reward_point', 'draw_rate', 'status', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'up_user_id' => '上级',
            'up_user_type' => '上线用户类型',
            'user_id' => '新注册用户',
            'reward_point' => '奖励积分',
            'draw_rate' => '提成比例',
            'status' => '状态 1已成功',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    public static function getCpsList($page, $page_size)
    {
        if (empty($page)){
            $page = 1;
        }
        if (empty($page_size)){
            $page_size = 20;
        }
        $data = [];

        $cps = User::find()->where(['up_user_id'=>Yii::$app->user->id,'deleted'=>0]);
        $jquery = clone $cps;
        $total_count = $jquery->count();
        $total_page = ceil($total_count / $page_size);
        $offset = ($page - 1) * $page_size;
        $results = $cps->orderBy('id desc')->offset($offset)->limit($page_size)->all();
        $data['result'] = 1;
        $data['total_count'] = $total_count;
        $data['give_day'] = empty(InviteUser::find()->where(['up_user_id'=>Yii::$app->user->id,'status_invite'=>1])->sum('days')) ? 0 : InviteUser::find()->where(['up_user_id'=>Yii::$app->user->id,'status_invite'=>1])->sum('days');
        $data['total_page'] = $total_page;
        $data['page'] = $page;
        $data['page_size'] = $page_size;
        $data['share_code'] = User::find()->select('share_code')->where(['id'=>Yii::$app->user->id,'deleted'=>0])->one()['share_code'];

        if (empty($results)){
            return $data;
        }

        $i = 0;
        foreach ($results as $result){
            $data['data'][$i] = [
                'index'  =>$offset + 1 + $i,
                'time'   =>date('Y-m-d H:i',$result->create_at),
                'phone' => $result->mphone,
                'days' => !empty($days= InviteUser::find()->where(['up_user_id'=>Yii::$app->user->id,'user_id'=>$result->id,'status_invite'=>1])->one()) ? $days->days : 0,
                'is_pay' => !empty(UserLevelLog::find()->where(['user_id'=>$result->id,'pay_type'=>2,'status'=>1])->one()) ? 1 : 0,
                'is_shop_name' => empty($result->shop_name) ? '否' : '是',
                'give_balance' => empty($result->shop_name) ? '--' : '100',
            ];

            $i++;
        }
        return $data;
    }

    public function getUser()
    {
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }
}
