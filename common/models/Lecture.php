<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "lecture".
 *
 * @property int $id
 * @property string $name 名称
 * @property string $phone 手机
 * @property string $position 职位
 * @property string $remark 备注
 * @property int $create_at 创建时间
 * @property int $update_at
 * @property int $deleted
 */
class Lecture extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'lecture';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'phone'], 'required'],
            [['create_at', 'update_at', 'deleted'], 'integer'],
            [['name'], 'string', 'max' => 45],
            [['position', 'remark'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'phone' => 'Phone',
            'position' => 'Position',
            'remark' => 'Remark',
            'create_at' => 'Creat At',
            'update_at' => 'Update At',
            'deleted' => 'Deleted',
            'location' => 'location',
        ];
    }
}
