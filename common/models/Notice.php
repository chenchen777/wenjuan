<?php

namespace common\models;

use Yii;
use common\models\Base;


/**
 * This is the model class for table "notice".
 *
 * @property int $id
 * @property string $title 标题
 * @property string $content 公告内容 
 * @property string $pic 图片地址
 * @property int $create_at
 * @property int $update_at
 */
class Notice extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notice';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content'], 'required'],
            [['content'], 'string'],
            [['create_at', 'update_at','deleted', 'version'], 'integer'],
            [['title', 'pic'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '公告id',
            'title' => '标题',
            'content' => '内容',
            'pic' => '图片',
            'create_at' => '发布时间',
            'update_at' => '更新时间',
            'deleted' => 'Deleted',
            'version' => 'Version',
        ];
    }
}
