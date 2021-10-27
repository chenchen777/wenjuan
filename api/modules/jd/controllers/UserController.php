<?php
/***
 * 用户相关的控制器
 */

namespace api\modules\jd\controllers;
use common\models\FeedbackList;
use common\models\Message;
use common\models\User;
use Yii;
use api\modules\jd\Controller;
use yii\db\Exception;
use yii\web\Response;

class UserController extends Controller
{

    //退出登录
    public function actionLogaut(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $user_info = User::findOne(['id'=>Yii::$app->user->id]);
        if(!empty($user_info)){
            $user_info->access_token = '';
            $user_info->save();
        }
        return ['result'=>1,'data'=>(object)[],'msg'=>'退出成功'];
    }

    //修改密码
    public function actionAlterPassword(){
        $user = User::findOne(Yii::$app->user->id);
        //修改登录密码
        $password = Yii::$app->request->post('password');

        if(!empty($user->password)){
            if(md5($password)!= $user->password){
                return ['result' => -1, 'msg' => '原密码不正确'];
            }
        }else{
            return ['result' => -1, 'msg' => '您还未设置密码'];
        }
        $new_password = Yii::$app->request->post('new_password');
        if(empty($new_password)){
            return ['result' => 0, 'msg' => '新密码为空'];
        }
        if (strlen($new_password) >16 or strlen($new_password) < 6){
            return ['result'=>0,'msg'=>'密码长度在6-16位'];
        }
        $user->password = md5($new_password);
        $user->save();
        return ['result'=>1,'msg'=>'修改成功'];
    }

    /**
     * 消息
     * @return array
     */
    public function actionMyMessage(){
        $data['page'] = Yii::$app->request->post('page');
        $data['page_size'] = Yii::$app->request->post('page_size');
        $result = Message::getList($data);
        return $result;
    }

    /**
     * 消息详情
     * @return array
     */
    public function actionMyMessageIn(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data['id'] = Yii::$app->request->post('id');
        if(empty($data['id'])){
            return ['result'=>-1,'msg'=>'ID不能为空'];
        }
        $result = Message::getListIn($data['id']);
        return $result;
    }

    /**
     * 意见反馈
     * @return array
     */
    public function actionFeedback(){
        if(empty($content = Yii::$app->request->post('content'))){
            return ['result'=>-1,'msg'=>'反馈内容不能为空'];
        }
        $contact = Yii::$app->request->post('contact');
        $new = new FeedbackList();
        $new->user_id = Yii::$app->user->id;
        $new->status = 4;
        $new->content = $content;
        $new->contact = empty($contact) ? '' : $contact;
        if(!$new->save()){
            return ['result'=>0,'msg'=>'反馈失败'];
        }
        return ['result'=>1,'msg'=>'反馈成功'];
    }




}