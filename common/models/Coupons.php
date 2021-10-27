<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "coupons".
 *
 * @property int $id
 * @property int $amount 优惠券金额
 * @property int $limit_amount 满减条件
 * @property int $expired_time 过期时间
 * @property int $check_status 审核状态
 * @property int $is_sys 是否系统发放 0 不是 1 是
 * @property int $lecture_id 讲师id
 * @property int $create_at
 * @property int $update_at
 * @property int $version
 * @property int $deleted
 */
class Coupons extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'coupons';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['amount', 'limit_amount','check_status','is_sys','expired_time','lecture_id', 'create_at', 'update_at', 'version', 'deleted'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'amount' => '金额',
            'limit_amount' => '满减条件',
            'expired_time' => '过期时间',
            'check_status' => 'Check Status',
            'is_sys' => 'Is Sys',
            'lecture_id' => 'Lecture Id',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'version' => 'Version',
            'deleted' => 'Deleted',
        ];
    }
    public function getLecture()
    {
        return $this->hasOne(AdminLecture::className(),['id'=>'lecture_id']);
    }
}
