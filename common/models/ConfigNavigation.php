<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "config_navigation".
 *
 * @property string $id
 * @property string $title 内容
 * @property string $url
 * @property int $is_new
 * @property string $sort 排序
 * @property string $version
 * @property string $update_at
 * @property string $create_at
 * @property int $deleted
 */
class ConfigNavigation extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'config_navigation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['is_new', 'sort', 'version', 'update_at', 'create_at', 'deleted'], 'integer'],
            [['title'], 'string', 'max' => 11],
            [['url'], 'string', 'max' => 255],
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
            'url' => 'Url',
            'is_new' => 'Is New',
            'sort' => 'Sort',
            'version' => 'Version',
            'update_at' => 'Update At',
            'create_at' => 'Create At',
            'deleted' => 'Deleted',
        ];
    }
}
