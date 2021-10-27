<?php
namespace api\controllers;

use yii\base\Controller;
use yii\filters\ContentNegotiator;
use yii\web\Response;

/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2017/3/18
 * Time: 14:29
 */
class BaseController extends Controller
{
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

    public function afterAction($action, $result){
        return parent::afterAction($action, $result);
    }
}