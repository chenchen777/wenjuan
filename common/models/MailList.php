<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "mail_list".
 *
 * @property int $id
 * @property string $account 邮箱用户名
 * @property string $password 密码
 * @property string $auth_code 授权码
 * @property int $use_num 使用次数，超过3次不可以
 * @property int $status 表示是否可用 1表示可用 0表示不可用
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class MailList extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mail_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['use_num', 'status', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['account', 'password', 'auth_code'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account' => 'Account',
            'password' => 'Password',
            'auth_code' => 'Auth Code',
            'use_num' => 'Use Num',
            'status' => 'Status',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }
}
