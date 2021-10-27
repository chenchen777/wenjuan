<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2017/3/19
 * Time: 13:14
 */

namespace api\modules\jd;

use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\ContentNegotiator;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use Yii;

class Controller extends \yii\rest\Controller
{
    public $user_id;

    public function behaviors()
    {
        return array_merge(parent::behaviors(),[
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'authenticator' => [
                'class' => CompositeAuth::className(),
                'authMethods' => [
                    HttpBasicAuth::className(),
                    HttpBearerAuth::className(),
                    ["class"=>QueryParamAuth::className(),"tokenParam"=>'token'],
                ],
            ],
        ]);
    }

    public function beforeAction($action)
    {
        if(parent::beforeAction($action)) {
            $this->user_id = Yii::$app->user->id;
            return true;
        }
        return false;
    }

    public function afterAction($action, $result)
    {
//        $result = parent::afterAction($action, $result);
//        if(is_null($result)){
//            throw new NotFoundHttpException();
//        } elseif($result === false) {
//            $result = ['result' => -1, 'data' => new \stdClass()];
//        }elseif (empty($result) and is_array($result)){
//            $result = ['result' => 0, 'data' => []];
//        }elseif(empty($result) or $result === true){
//            $result = ['result' => true, 'data' => new \stdClass()];
//        }else{
//            $result = ['status' => true, 'data' => $result];
//        }
//        if(Yii::$app->getDb()->transaction) {
//            Yii::$app->getDb()->getTransaction()->commit();
//        }
        return $result;
    }

}