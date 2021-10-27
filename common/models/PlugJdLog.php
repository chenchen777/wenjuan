<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "plug_jd_log".
 *
 * @property int $id
 * @property string $username 京东账号
 * @property string $order_no 订单号
 * @property int $mphone 手机号
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class PlugJdLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'plug_jd_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            [['mphone', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
//            [['username'], 'string', 'max' => 200],
//            [['order_no'], 'string', 'max' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'order_no' => 'Order No',
            'mphone' => 'Mphone',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'name' => 'name',
            'name_site' => 'name_site',
        ];
    }
}
