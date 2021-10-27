<?php

namespace common\models;

use Yii;
use common\models\Labels;
use common\models\LabelsList;
use common\models\User;
use common\models\DubiousList;

/**
 * This is the model class for table "jd_user".
 *
 * @property int $id
 * @property string $username  京东账号
 * @property string $mark_num 标记总数
 * @property string $accept_num 吐槽总数
 * @property string $label_at 标记时间
 * @property int $type 举报类型
 * @property int $version
 * @property int $deleted
 * @property int $create_at
 * @property int $update_at
 */
class JdUser extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jd_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['version', 'deleted', 'create_at', 'update_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'username',
            'mark_num' => 'mark_num',
            'accept_num' => 'accept_num',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'label_at' => 'label_at',
            'type' => 'type',
        ];
    }
    /**
     * @param $page
     * @param $page_size
     * @return array
     * 查询列表
     */
    public static function jdUserList($page,$page_size,$content){
        $temps = JdUser::find()->where(['deleted'=>0]);
        if($content){
            $temps->andWhere(['like','username',$content]);
        }else{
//            $temps->andWhere(['not in','label_at',0]);
        }
        $jquery = clone $temps;
        $total_count = $jquery->count();
        $total_page = ceil($total_count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = $temps->orderBy('label_at desc')->offset($offset)->limit($page_size)->all();
        if(empty($list)){
            return ['result'=>1056,'msg'=>'没有更多了'];
        }
        $data = [];
        $i = 0;
        foreach ($list as $key){
            $sql = "SELECT labels_id,COUNT(*) as count FROM labels_list WHERE jd_user=$key->id GROUP BY labels_id";
            $query = Yii::$app->db->createCommand($sql)->queryAll();
            if($query){
                foreach($query as $val){
                    $key_arrays[]=$val['count'];
                }
                if(count($query) != 1){
                    array_multisort($key_arrays,SORT_DESC,SORT_NUMERIC,$query);
                }
                $label_name = Labels::find()->where(['id'=>$query[0]['labels_id']])->one();
                $label_name = $label_name['label_name'];
            }else{
                $label_name = '';
            }

            $data[$i] = [
                'id' => $key->id,
                'username' => $key->username,
                'mark_num' => $key->mark_num,
                'accept_num' => $key->accept_num,
                'label_name' => empty($label_name) ? '' :$label_name,
                'label_at' => empty($key->label_at) ? '-': date('Y-m-d H:i:s',$key->label_at),
            ];
            $i++;
        }

        return ['result'=>1,'total_count'=>$total_count,'total_page'=>$total_page,'page'=>$page,'page_size'=>$page_size,'data'=>$data,'msg'=>'查询列表'];
    }
    public function sortByOneField($data, $filed, $type)
    {
        if (count($data) <= 0) {
            return $data;
        }
        foreach ($data as $key => $value) {
            $temp[$key] = $value[$filed];
        }
        array_multisort($temp, $type, $data);
        return $data;
    }
    // 总数相加  $mark_num 要处理的内容   $y 值
    public static function Count($mark_num,$y){
        $count = 0;
            foreach($mark_num as $key){
                $count += $key[$y];
            }
        return $count;
    }
    /**
     * @param $jd_user  更新的JD账号
     * @return array
     * 手动更新
     */
    public static function jdUpdate($jd_user,$page,$page_size,$type='',$distributor=''){
        $temps = JdUser::find()->where(['deleted'=>0]);
        if($type){
            $jd_user = explode(',',$jd_user);
            $id_s = [];
            $i = 0;
            foreach($jd_user as $key =>$y){
                $jd_users_d = JdUser::find()->select('id')->where(['deleted'=>0])->andWhere(['username'=>$y])->one();
                //  没有注册用户
                if(empty($jd_users_d)){
                    $new_s = new JdUser;
                    $new_s->username = $y;
                    $new_s->save();
                    $id_s[] = $new_s->id;
                }else{
                    $id_s[] = $jd_users_d->id;
                }
                $i++;
            }
            $jd_user = $id_s;
        }else{
            $jd_user = explode(',',$jd_user);
        }

            $temps->andWhere(['in','id',$jd_user]);
            $list = $temps->orderBy('id desc')->all();
            // 标记总数
            $mark_num = LabelsList::find()->where(['deleted'=>0])->andWhere(['in','jd_user',$jd_user])->count();
            // 吐槽总数
            $accept_num= DubiousList::find()->where(['deleted'=>0])->andWhere(['in','jd_uid',$jd_user])->count();

        $i = 0;
        $data = [];
        foreach($list as $key){
            // 是否吐槽过
            if($is_content = DubiousList::find()->where(['user_id'=>Yii::$app->session['jd_uid'],'jd_uid'=>$jd_user])->one()){
                $is_content = 1;
            }else{
                $is_content = 0;
            }
            $is_content_dd = LabelsList::find()->where(['jd_user'=>$key->id])->count();
            $is_content_ddsss = DubiousList::find()->where(['jd_uid'=>$key->id])->count();
            $data[$i] = [
                'id' => $key->id,
                'username' => $key->username,
                'mark_num' => $is_content_dd,
                'accept_num' => $is_content_ddsss,
                'create_at' => date('Y-m-d H:i:s',$key->create_at),
                'is_content' => $is_content,
            ];
            $i++;
        }
            return ['result'=>1,'mark_num'=>empty($mark_num) ? 0 : $mark_num,'accept_num'=>empty($accept_num)?0:$accept_num,'data'=>$data,'msg'=>'手动更新成功'];
    }
    /**
     * @return array
     * 最新互动
     */
    public static function jdNewestInteract(){
        $list = LabelsList::find()->where(['deleted'=>0])->orderBy('id desc')->limit(30)->all();
        if(empty($list)){
            return ['result'=>1056,'msg'=>'没有更多了'];
        }
        $data = [];
        $i = 0;
        foreach ($list as $key){
            if($key->uid == 0){

            }else{
                $uid_name = User::find()->select('mphone')->where(['id'=>$key->uid])->one();
            }
            $jd_uid_name = JdUser::find()->select('username')->where(['id'=>$key->jd_user])->one();
            $labels = Labels::find()->select('label_name')->where(['id'=>$key->labels_id])->one();
            $data[$i] = [
                    'uid_name' => empty($uid_name->mphone)? '系统':$uid_name->mphone,
                    'jd_uid_name' => $jd_uid_name->username,
                    'labels_name' => $labels->label_name,
                    'create_at' => date('Y-m-d H:i:s',$key->create_at),
            ];
            $i++;
        }
        return ['result'=>1,'data'=>$data,'msg'=>'最新互动'];
    }
    /**
     *  * *@param $jd_user 被标记的用户
     * @return array
     * 标记记录
     */
    public static function jdSignlog($jd_user,$page,$page_size){
        $temps = LabelsList::find()->where(['deleted'=>0,'jd_user'=>$jd_user]);
        $jquery = clone $temps;
        $total_count = $jquery->count();
        $total_page = ceil($total_count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = $temps->orderBy('id desc')->offset($offset)->limit($page_size)->all();
        if(empty($list)){
            return ['result'=>1056,'msg'=>'没有更多了'];
        }
        $data = [];
        $i = 0;
        $jd_name = JdUser::find()->select('username')->where(['id'=>$jd_user])->one();
        foreach ($list as $key){
            if($key->uid == 0){
                $uid_name = '系统';
            }else{
                $uid_name = User::find()->select('mphone')->where(['id'=>$key->uid])->one();
                $uid_name = substr_replace($uid_name->mphone,'******',3,6);
            }
            $jd_uid_name = JdUser::find()->select('username')->where(['id'=>$key->jd_user])->one();
            $labels = Labels::find()->select('label_name')->where(['id'=>$key->labels_id])->one();
            $data[$i] = [
                'uid_name' => $uid_name,
                'jd_uid_name' => $jd_uid_name->username,
                'labels_name' => $labels->label_name,
                'create_at' => date('Y-m-d H:i:s',$key->create_at),
            ];
            $i++;
        }
        return ['result'=>1,'total_count'=>$total_count,'total_page'=>$total_page,'page'=>$page,'page_size'=>$page_size,'jd_name'=>$jd_name->username,'data'=>$data,'msg'=>'标记记录'];
    }
    /**
     * *@param $jd_user 被标记的用户
     * *@param $labels_id 标签
     * @return array
     * 标记
     */
    public static function jdSign($jd_user,$labels_id,$type=''){
        if($type){
            $jd_user_id = JdUser::find()->where(['username'=> $jd_user])->one();
            if(empty($jd_user_id)){
                //  没有自动注册京东账号
                $new = new JdUser;
                $new->username = $jd_user;
                $new->save();
                $jd_user = $new->id;
                $new_s = new LabelsList();
                $new_s->labels_id = $labels_id;
                $new_s->jd_user = $jd_user;
                $new_s->uid = Yii::$app->session['jd_uid'];
                if(!$new_s->save()){
                    return ['result'=>0,'msg'=>'标记失败'];
                }
                if(LabelsList::find()->where(['uid'=>Yii::$app->session['jd_uid'],'jd_user'=>$jd_user])->count() == 1){
                        JdUser::updateAllCounters(['mark_num' => 1],['id'=>$jd_user]);
                    }
                $user_id = JdUser::find()->where(['id'=> $jd_user])->one();
                $user_id->label_at = time();
                $user_id->save();
                    return ['result'=>1,'msg'=>'标记成功'];
            }
            $jd_user = $jd_user_id->id;
        }
        $new = new LabelsList();
        $new->uid = Yii::$app->session['jd_uid'];
        $new->jd_user = $jd_user;
        $new->labels_id = $labels_id;
        if(!$new->save()){
            return ['result'=>0,'msg'=>'标记失败'];
        }

        if(LabelsList::find()->where(['uid'=>Yii::$app->session['jd_uid'],'jd_user'=>$jd_user])->count() == 1){
            JdUser::updateAllCounters(['mark_num' => 1],['id'=>$jd_user]);
            $user_id = JdUser::find()->where(['id'=> $jd_user])->one();
            $user_id->label_at = time();
            $user_id->save();
        }
        return ['result'=>1,'msg'=>'标记成功 '];
    }
    /**
     * @return array
     * 标记类型列表
     */
    public static function jdSignList($jd_user,$type,$token=''){

//        $token_gou =  self::token(Yii::$app->session['jd_uid']);
//        if($token_gou){
//            return ['result'=>1004,'msg'=>'Token过期 '];
//        }

        if($type){
            if($token){
                $uid = User::find()->where(['jd_token'=> $token])->one();
                Yii::$app->session['jd_uid'] =  $uid->id;
            }
            $jd_user_id = JdUser::find()->where(['username'=> $jd_user])->one();
            if($jd_user_id){
                $jd_user = $jd_user_id->id;
            }else{
                //  没有自动注册京东账号
                $new = new JdUser;
                $new->username = $jd_user;
                $new->save();
                $jd_user = $new->id;
            }
        }
        $list = Labels::find()->where(['deleted'=>0,'type'=>1])->all();
        if(empty($list)){
            return ['result'=>1056,'msg'=>'没有更多了'];
        }

        $i = 0;
        $sign_list = LabelsList::find()->select('labels_id')->where(['uid'=>Yii::$app->session['jd_uid'],'jd_user'=>$jd_user])->one();
        if($type == 1){
            if($token){
            }else{
                $sign_list = [];
            }
        }

        $data = [];
        foreach($list as $key){
            $data[$i]['id'] = $key->id;
            $data[$i]['label_name'] = $key->label_name;
            if(empty($sign_list)){
                $data[$i]['sign'] = 1;
            }
            if($sign_list){
                if($sign_list->labels_id == $key->id){
                    $data[$i]['sign'] = 0;
                }else{
                    $data[$i]['sign'] = 1;
                }
            }else{
                $data[$i]['sign'] = 1;
            }
            $data[$i]['mark_num'] = LabelsList::find()->where(['deleted'=>0,'labels_id'=>$key->id,'jd_user'=>$jd_user])->count();
            $i++;
        }
        return ['result'=>1,'data'=>$data,'msg'=>'标记类型列表 '];
    }
    //  判断是否过期
    public static function token($uid){
        $uid = User::find()->where(['id'=> $uid])->one();
        if(empty($uid)){
            return ['result'=>0,'msg'=>'请注册'];
        }
        // 京东TOKEN
        $jd_token = $uid->jd_token;
        $jd_token = base64_decode($jd_token);
        $jd_token = json_decode($jd_token,'TRUE');
        //  过期时间
        $dao_at = $jd_token['iat'] + $jd_token['exp'];
        if(time() > $dao_at ){
            return ['result'=>1004,'msg'=>'Token过期 '];
        }
    }
    /**
     * @return array
     * 吐槽
     */
    public static function jdDissatisfied($jd_user,$content,$type,$token=''){
        if($token){
            $uid = User::find()->where(['jd_token'=> $token])->one();
            Yii::$app->session['jd_uid'] =  $uid->id;
        }

        if($type){
            $jd_user_id = JdUser::find()->where(['username'=> $jd_user])->one();
            if(empty($jd_user_id)){
                $token_gou =  self::token(Yii::$app->session['jd_uid']);
                if($token_gou){
                    return ['result'=>1004,'msg'=>'Token过期 '];
                }
                //  没有自动注册京东账号
                $new = new JdUser;
                $new->username = $jd_user;
                $new->save();
                $jd_user = $new->id;
                if(empty($content)){
                    // 是否吐槽过
                    $is_content = DubiousList::find()->where(['user_id'=>Yii::$app->session['jd_uid'],'jd_uid'=>$jd_user])->one();
                    if($is_content){
                        return ['result'=>1,'msg'=>'吐槽获取','data'=>['content'=>$is_content->content]];
                    }else{
                        return ['result'=>1,'msg'=>'吐槽获取'];
                    }
                }else{
                    $new = new DubiousList();
                    $new->content = $content;
                    $new->jd_uid = $jd_user;
                    $new->user_id = Yii::$app->user->id;
                    if(!$new->save()){
                        return ['result'=>0,'msg'=>'吐槽失败'];
                    }
                    if(DubiousList::find()->where(['user_id'=>Yii::$app->session['jd_uid'],'jd_uid'=>$jd_user])->count() <= 1){
                        JdUser::updateAllCounters(['accept_num' => 1],['id'=>$jd_user]);
                    }
                    return ['result'=>1,'msg'=>'吐槽成功'];
                }
            }
            $jd_user = $jd_user_id->id;
        }
        // 是否吐槽过
        if($is_content = DubiousList::find()->where(['user_id'=>Yii::$app->session['jd_uid'],'jd_uid'=>$jd_user])->one()){
            return ['result'=>1,'msg'=>'吐槽获取','data'=>['content'=>$is_content->content]];
        }
        if(empty($content)){
            return ['result'=>1,'data'=>['content'=>''],'msg'=>'吐槽为空'];
        }
        $new = new DubiousList();
        $new->content = $content;
        $new->jd_uid = $jd_user;
        $new->user_id = Yii::$app->session['jd_uid'];
        if(!$new->save()){
            return ['result'=>0,'msg'=>'吐槽失败'];
        }
        if(DubiousList::find()->where(['user_id'=>Yii::$app->session['jd_uid'],'jd_uid'=>$jd_user])->count() <= 1){
            JdUser::updateAllCounters(['accept_num' => 1],['id'=>$jd_user]);
        }
        return ['result'=>1,'msg'=>'吐槽成功'];

    }
    /**
     * @return array
     * 吐槽列表
     */
    public static function jdDissatisfiedlist($jd_user,$page,$page_size){
        $temps = DubiousList::find()->where(['deleted'=>0,'jd_uid'=>$jd_user]);
        $jquery = clone $temps;
        $total_count = $jquery->count();
        $total_page = ceil($total_count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = $temps->orderBy('id desc')->offset($offset)->limit($page_size)->all();

        if(empty($list)){
            return ['result'=>1056,'msg'=>'没有更多了'];
        }

        $data = [];
        $i = 0;
        foreach ($list as $key ){

            $mphone = User::find()->select('mphone')->where(['id'=>$key->user_id])->one();
            $username = JdUser::find()->select('username')->where(['id'=>$key->jd_uid])->one();
            $data[$i] = [
                'id'=> $key->id,
                'content'=> $key->content,
                'jd_uid'=> $key->jd_uid,
                'jd_uid_name'=> $username->username,
                'user_id'=> $key->user_id,
                'user_id_name'=> substr_replace($mphone->mphone,'******',3,6),
                'create_at'=> date('Y-m-d H:i:s',$key->create_at),
            ];
            $i++;
        }

        return ['result'=>1,'total_count'=>$total_count,'total_page'=>$total_page,'page'=>$page,'page_size'=>$page_size,'data'=>$data,'msg'=>'吐槽列表'];
    }
}
