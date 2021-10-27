<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "message".
 *
 * @property int $id
 * @property string $title 全称
 * @property string $content 内容
 * @property int $version
 * @property int $deleted
 * @property int $create_time
 * @property int $update_time
 */
class Message extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content'], 'required'],
            [['content'], 'string'],
            [['version', 'deleted', 'create_at', 'update_at'], 'integer'],
            [['title'], 'string', 'max' => 40],
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
            'content' => 'Content',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create Time',
            'update_at' => 'Update Time',
        ];
    }
    /**
     * 公告 列表
     */
    public static function getList($data){
        $offset = ($data['page']-1)*$data['page_size'];
        $query = Message::find()->where(['type'=>1, 'deleted'=>0,'user_id'=>0])->select("id,title,content,create_at");
        $list = $query->offset($offset)->limit($data['page_size'])->asArray()->all();
        $count = $query->count();
        if(empty($list)){
            return ['result'=>1056,'msg'=>'没有更多了'];
        }else{
            foreach ($list as $k => $y){
                $list[$k]['create_at'] = date('Y-m-d',$y['create_at']);
            }
        }
        return ['result'=>1,'data'=>$list, 'count'=>$count];
    }
    /**
     * 消息详情
     */
    public static function getListIn($id){
        $query = Message::find()->where(['id'=>$id, 'deleted'=>0])->select("id,title,content,create_at")->one();
        $query['create_at'] = date('Y-m-d',$query['create_at']);
        return ['result'=>1,'data'=>$query];
    }
}
