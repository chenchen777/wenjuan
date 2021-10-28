<?php
/**
 * Created by PhpStorm.
 * User: lisk
 * Date: 2018年10月27日
 * Time: 14点06分
 */
namespace api\controllers;

use common\models\Investigation;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\base\Controller;
use yii\filters\ContentNegotiator;
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Authorization,Content-Type,Accept,Origin,User-Agent,DNT,Cache-Control,X-Mx-ReqToken,Keep-Alive,X-Requested-With,If-Modified-Since");
/**
 * Class RankingController
 * @package api\controllers
 */

class InvestigationController extends Controller{

    public $enableCsrfValidation =false;

    public function behaviors()
    {
//        return [
//            'contentNegotiator' => [
//                'class' => ContentNegotiator::className(),
//                'formats' => [
//                    'application/json' => Response::FORMAT_JSON,
//                ],
//            ]
////            'cors' => [
////                'Origin' => ['*'],
////                'Access-Control-Request-Method' => ['GET', 'POST'],
////                'Access-Control-Request-Headers'=>['*']
////            ],
//        ];
        $behaviors = [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [],
            ],
            'corsFilter' => [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Origin' => ['*'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 86400,
                    'Access-Control-Expose-Headers' => [],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ]
        ];
        if (Yii::$app->getRequest()->getMethod() !== 'OPTIONS') {
            $behaviors['authenticator'] = [
                'class' => HttpBearerAuth::className(),
                'optional' => [
                    'login',
                    'signup'
                ],
            ];
        }

        return $behaviors;
    }

    public function actionAdd()
    {
        $param = \Yii::$app->request->get();
//        if (is_array($param['solve_problem'])){
//            $param['solve_problem'] = implode(',', $param['solve_problem']);
//        }
//        if (is_array($param['platform'])){
//            $param['platform'] = implode(',', $param['platform']);
//        }
//        if (is_array($param['error'])){
//            $param['error'] = implode(',', $param['error']);
//        }
        $investigationObj = new Investigation();
        $investigationObj->setAttributes($param, false);
        if($investigationObj->save($param)){
            return ['result' => 1, 'msg' => '提交成功'];
        }else{
            return ['result' => 0, 'msg' => '提交失败'];
        }
    }
}