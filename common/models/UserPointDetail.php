<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_point_detail".
 *
 * @property int $id
 * @property int $user_id 用户
 * @property int $admin_id
 * @property string $type 类型
 * @property string $relate_mode 模型
 * @property int $relate_id 关联id
 * @property string $title 标题说明
 * @property int $point 积分
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 */
class UserPointDetail extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_point_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'relate_mode', 'relate_id', 'point'], 'required'],
            [['user_id','admin_id', 'relate_id', 'point','left_point', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['type'], 'string'],
            [['relate_mode'], 'string', 'max' => 255],
            [['title'], 'string', 'max' => 60],
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
            'admin_id' => 'Admin ID',
            'type' => 'Type',
            'relate_mode' => 'Relate Mode',
            'relate_id' => 'Relate ID',
            'title' => 'Title',
            'point' => 'Point',
            'left_point' => 'Left Point',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    /**
     * @return array
     * UserPointDetail commom\models\UserPointDetail
     */

    public static function getPointDetail($page,$page_size)
    {
        if (empty($page)){
            $page = 1;
        }
        if (empty($page_size)){
            $page_size = 20;
        }
        $data = [];
        $user = User::findOne(['id'=>Yii::$app->user->id,'deleted'=>0]);
        $results = UserPointDetail::find()->where(['user_id'=>Yii::$app->user->id,'deleted'=>0]);
        $jquery = clone $results;
        $total_count = $jquery->count();
        $total_page = ceil($total_count / $page_size);
        $offset = ($page - 1) * $page_size;
        $models = $results->orderBy('id desc')->offset($offset)->limit($page_size)->all();
        $data['result'] = 1;
        $data['total_count'] = $total_count;
        $data['total_page'] = $total_page;
        $data['page'] = $page;
        $data['page_size'] = $page_size;
        $i = 0;
        foreach ($models as $model){
            $point = 0;
            if ($model->type == 'pay'){
                $point = "-" . $model->point;
            }else{
                $point = '+' . $model->point;
            }
            $data['data'][$i] = [
                'index'=>$offset + 1 + $i,
                'account'=>$user->mphone,
                'time'=>date('Y-m-d H:i',$model->create_at),
                'point'=>$point,'detail'=>$model->title,
                'left_point'=>empty($model->left_point) ? 0 : $model->left_point];
            $i++;
        }
        return $data;

    }

    public static function pointSave($point,$type,$relate_model,$title,$user_id='',$relate_id,$admin_id='')
    {
        $user_id = empty($user_id) ? Yii::$app->user->id : $user_id;
        $user = User::findOne(['id'=>$user_id]);
        $user_detail = new UserPointDetail();
        $user_detail->point = $point;
        $user_detail->left_point = $user->point;
        $user_detail->user_id = $user->id;
        $user_detail->admin_id = empty($admin_id) ? 0 : $admin_id;
        $user_detail->type = $type;
        $user_detail->relate_mode = $relate_model;
        $user_detail->relate_id = $relate_id;
        $user_detail->title = $title;

        if (! $user_detail->save()){
            return ['result'=>0,'msg'=>$user_detail->getError()];
        }
        return ['result'=>1];
    }
}
