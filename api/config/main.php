<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules' => [
        'client' => [
            'class' => 'api\modules\client\Module',
        ],
        'collect' => [
            'class' => 'api\modules\collect\Module',
        ],
        'jd' => [
            'class' => 'api\modules\jd\Module',
        ],
        'star' => [
            'class' => 'api\modules\star\Module',
        ],
    ],
    'controllerNamespace' => 'api\controllers',
    'components' => [
        'request' => [
            'enableCsrfCookie' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
            'on beforeLogin' => function($event) {
//                $user = $event->identity; //这里的就是User Model的实例了
//                $user->last_login_at = time();
//                $user->save();
            },
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER'],
                    'logFile' => '@app/runtime/logs/err_'.date('Y-m-d').'.log',
                ],
            ],
        ],
        'errorHandler' => [
            'class'=>'api\ErrorHandler',
            'errorAction' => 'site/error',
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'format'=> 'json',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                /*
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'client/jobs',
                        'client/queue',
                    ],

                ],**/
                '<module:\w+>/<controller:\w+>/<id:\d+>' => '<module>/<controller>/view',
                '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/' => '<controller>/index',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
];
