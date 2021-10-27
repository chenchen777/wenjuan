<?php

namespace common\models;

use common\models\Base;
use Yii;

/**
 * This is the model class for table "feedback_list".
 *
 * @property int $id
 * @property int $user_id
 * @property int $status 反馈类型 1新功能 2错误信息 3产品建议 4吐槽 5其他
 * @property string $content 反馈内容
 * @property string $contact 联系方式
 * @property int $create_at
 * @property int $update_at
 * @property int $version
 * @property int $deleted
 */
class FeedbackList extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'feedback_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'status', 'create_at', 'update_at', 'version', 'deleted'], 'integer'],
            [['content'], 'string'],
            [['contact'], 'string', 'max' => 32],
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
            'status' => 'Status',
            'content' => 'Content',
            'contact' => 'Contact',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'version' => 'Version',
            'deleted' => 'Deleted',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }
}
