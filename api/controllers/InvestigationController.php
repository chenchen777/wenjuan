<?php
/**
 * Created by PhpStorm.
 * User: lisk
 * Date: 2018年10月27日
 * Time: 14点06分
 */
namespace api\controllers;

use common\models\Investigation;
use common\models\ApiCityList;
use common\models\ApiCityLists;
use common\models\BaseGoods;
use common\models\IpLog;
use common\models\MonitorGoods;
use common\models\RandomList;
use common\models\ServiceKeywordSearch;
use common\models\ServiceKeywordSearchResult;
use common\models\TimeList;
use common\models\User;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use Yii;
use yii\helpers\Json;
use yii\web\Response;
use yii\base\Controller;
use yii\filters\ContentNegotiator;

/**
 * Class RankingController
 * @package api\controllers
 */
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
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods:* ');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
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