<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "comment_log".
 *
 * @property int $id
 * @property int $user_id
 * @property string $sku
 * @property int $cost_point 消耗积分
 * @property int $create_at
 * @property int $update_at
 * @property int $version
 * @property int $deleted
 */
class CommentLog extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comment_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'cost_point', 'create_at', 'update_at', 'version', 'deleted'], 'integer'],
            [['sku'], 'string', 'max' => 128],
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
            'sku' => 'Sku',
            'cost_point' => 'Cost Point',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'version' => 'Version',
            'deleted' => 'Deleted',
        ];
    }

    public static function commentSave($sku)
    {
        try{
            $commentLog = new CommentLog();
            $commentLog->sku = $sku;
            $commentLog->cost_point = 0;
            $commentLog->user_id = Yii::$app->user->id;

            if (! $commentLog->save()){
                return ['result'=>0,'msg'=>$commentLog->getError()];
            }

            return ['result'=>1];
        }catch (\Exception $e){
            return ['result'=>0,'msg'=>$e->getMessage()];
        }

    }
}
