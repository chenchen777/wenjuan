<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "custom_service".
 *
 * @property int $id
 * @property string $wx_img 微信二维码图片
 * @property int $total 已经加的好友人数
 * @property string $code 口令
 * @property int $version
 * @property int $deleted
 * @property int $create_time
 * @property int $update_time
 */
class CustomService extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'custom_service';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['total', 'version', 'deleted', 'create_at', 'update_at'], 'integer'],
            [['wx_img'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'wx_img' => 'Wx Img',
            'total' => 'Total',
            'code' => 'Code',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create Time',
            'update_at' => 'Update Time',
        ];
    }
}
