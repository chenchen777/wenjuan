<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'sourceLanguage' => 'en-US',
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Chongqing',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=106.75.218.64;dbname=wenjuan',
            'username' => 'root',
            'password' => 'W8FXG3zTn74EEhQA',
            'charset' => 'utf8',
        ],
        'beanstalk' => [
            'class' => 'udokmeci\yii2beanstalk\Beanstalk',
            'host' => '127.0.0.1',
            'port' => 11300,
        ],
        'formatter' => [
            'datetimeFormat' => 'Y-MM-dd H:m:s',
            'dateFormat' => 'Y-MM-dd',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
        ],
//        'cache' => [
//            'class' => 'yii\redis\Cache',
//            'redis' => [
//                'hostname' => '127.0.0.1',
//                'port' => 6379,
//                'database' => 0,
//                        //'password' => 'W8FXG3zTn74EEhQA',
//            ],
//        ],
//        'redis' => [
//            'class' => 'yii\redis\Connection',
//            'hostname' => '127.0.0.1', //localhost
//            'port' => 6379,
//            'database' => 0,
//            //'password' => 'W8FXG3zTn74EEhQA'
//        ],
    ],
];
