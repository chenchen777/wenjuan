<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_coupon".
 *
 * @property int $id
 * @property int $user_id
 * @property int $coupon_id
 * @property int $is_used 是否使用
 * @property int $is_expired 是否过期
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class UserCoupon extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_coupon';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'coupon_id'], 'required'],
            [['user_id', 'coupon_id', 'is_used', 'is_expired', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
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
            'coupon_id' => 'Coupon ID',
            'is_used' => 'Is Used',
            'is_expired' => 'Is Expired',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }
}
