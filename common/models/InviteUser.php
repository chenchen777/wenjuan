<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "invite_user".
 *
 * @property string $id
 * @property int $up_user_id 上级
 * @property string $user_id 新注册用户
 * @property int $deleted
 * @property string $version
 * @property int $create_at
 * @property int $update_at
 * @property int $level_id 等级
 * @property int $days 赠送时长
 * @property int $status_pay
 * @property int $status_invite 状态 1已成功邀请  -1店铺重复 -2 ip重复
 */
class InviteUser extends Base
{

    const INVITE_STATUS_SUCCESS = 1;
    const INVITE_STATUS_STORE_REPEAT = -1;
    const INVITE_STATUS_IP_REPEAT = -2;
    /**
     * @var array 邀请状态
     */
    public static $inviteStatusArr = [
        self::INVITE_STATUS_SUCCESS => '成功邀请',
        self::INVITE_STATUS_STORE_REPEAT => '店铺重复',
        self::INVITE_STATUS_IP_REPEAT => 'ip重复',
    ];


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'invite_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['up_user_id', 'user_id', 'deleted', 'version', 'create_at', 'update_at', 'level_id', 'days', 'status_invite'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'up_user_id' => 'Up User ID',
            'user_id' => 'User ID',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'level_id' => 'Level ID',
            'days' => 'Days',
            'status_invite' => 'Status Invite',
        ];
    }
}
