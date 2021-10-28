<?php
/**
 * Created by PhpStorm.
 * User: lisk
 * Date: 2018年10月27日
 * Time: 14点06分
 */
namespace api\controllers;

use common\models\Investigation;
use yii\web\Response;
use yii\base\Controller;
use yii\filters\ContentNegotiator;

/**
 * Class RankingController
 * @package api\controllers
 */
header("Access-Control-Allow-Origin: *");
class InvestigationController extends Controller{

    public $enableCsrfValidation =false;

    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ]
        ];
    }

    public function actionAdd()
    {
        $param = \Yii::$app->request->post();
        if (is_array($param['solve_problem'])){
            $param['solve_problem'] = implode(',', $param['solve_problem']);
        }
        if (is_array($param['platform'])){
            $param['platform'] = implode(',', $param['platform']);
        }
        if (is_array($param['error'])){
            $param['error'] = implode(',', $param['error']);
        }
        $investigationObj = new Investigation();
        $investigationObj->setAttributes($param, false);
        if($investigationObj->save($param)){
            return ['result' => 1, 'msg' => '提交成功'];
        }else{
            return ['result' => 0, 'msg' => '提交失败'];
        }
    }
}