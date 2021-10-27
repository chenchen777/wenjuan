<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "statis_data".
 *
 * @property string $id
 * @property int $user_all 用户总量
 * @property int $past_user_all 昨日用户总量
 * @property int $inquire_all 查询总数
 * @property int $past_inquire_all 昨日查询总数
 * @property int $inquire_all_pc 查询总数pc
 * @property int $past_inquire_all_pc 昨日查询总数pc
 * @property int $inquire_all_app 查询总数app
 * @property int $past_inquire_all_app 昨日查询总数app
 * @property int $login_inquire_all 登录用户查询
 * @property int $past_login_inquire_all 昨日登录用户查询
 * @property int $no_login_inquire_all 未登录用户查询
 * @property int $past_no_login_inquire_all 昨日未登录用户查询
 * @property int $version
 * @property int $deleted
 * @property string $create_at
 * @property string $update_at
 */
class StatisData extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'statis_data';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_all', 'past_user_all', 'inquire_all', 'past_inquire_all', 'inquire_all_pc', 'past_inquire_all_pc', 'inquire_all_app', 'past_inquire_all_app', 'login_inquire_all', 'past_login_inquire_all', 'no_login_inquire_all', 'past_no_login_inquire_all', 'version', 'deleted', 'create_at', 'update_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_all' => 'User All',
            'past_user_all' => 'Past User All',
            'inquire_all' => 'Inquire All',
            'past_inquire_all' => 'Past Inquire All',
            'inquire_all_pc' => 'Inquire All Pc',
            'past_inquire_all_pc' => 'Past Inquire All Pc',
            'inquire_all_app' => 'Inquire All App',
            'past_inquire_all_app' => 'Past Inquire All App',
            'login_inquire_all' => 'Login Inquire All',
            'past_login_inquire_all' => 'Past Login Inquire All',
            'no_login_inquire_all' => 'No Login Inquire All',
            'past_no_login_inquire_all' => 'Past No Login Inquire All',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'vip_cha_all' => 'vip_cha_all',
            'past_vip_cha_all' => 'past_vip_cha_all',
            'weight_all' => 'weight_all',
            'past_weight_all' => 'past_weight_all',
        ];
    }
}
