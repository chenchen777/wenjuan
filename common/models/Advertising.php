<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "advertising".
 *
 * @property int $id
 * @property string $title 标题
 * @property string $pic 图片地址
 * @property string $url 链接地址
 * @property int $type 类型  1：顶部广告 2：右侧广告 
 * @property int $create_at
 * @property int $update_at
 * @property int $deleted
 * @property int $version
 * @property int $start_at
 * @property int $end_at
 * @property int $status
 */
class Advertising extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'advertising';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'create_at', 'update_at', 'deleted', 'version', 'start_at', 'end_at', 'status'], 'integer'],
            [['title', 'pic', 'url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'pic' => 'Pic',
            'url' => '跳转地址',
            'type' => 'Type',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'start_at' => 'Start At',
            'end_at' => 'End At',
            'status' => '状态',
        ];
    }
}
