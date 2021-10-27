<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_data".
 *
 * @property int $id
 * @property int $register_num 当日注册数
 * @property int $pay_num 付费人数
 * @property string $pay_amount 付费金额
 * @property string $level_data 购买等级人数及金额信息
 * @property int $statis_date 统计日期
 * @property int $type 1 注册总数据2邀请用户 3自然用户数据
 * @property int $create_at 创建时间
 * @property int $update_at
 * @property int $deleted
 * @property int $version
 */
class UserData extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_data';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['register_num', 'pay_num', 'statis_date', 'type', 'create_at', 'update_at', 'deleted', 'version'], 'integer'],
            [['pay_amount'], 'number'],
            [['level_data'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'register_num' => 'Register Num',
            'pay_num' => 'Pay Num',
            'pay_amount' => 'Pay Amount',
            'level_data' => 'Level Data',
            'statis_date' => 'Statis Date',
            'type' => 'Type',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'deleted' => 'Deleted',
            'version' => 'Version',
        ];
    }
}
