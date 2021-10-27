<?php
return [
    'components' => [
//        'db' => [
//            'class' => 'yii\db\Connection',
//            'dsn' => 'mysql:host=120.132.12.117;dbname=pingduoduo',
//            'username' => 'dev',
//            'password' => 'W8FXG3zTn74EEhQA',
//            'charset' => 'utf8',
//        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=106.75.218.64;dbname=wenjuan',
            'username' => 'root',
            'password' => 'W8FXG3zTn74EEhQA',
            'charset' => 'utf8',
        ],
        'jdrqds' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.23.103.61;dbname=jdrqds',
            'username' => 'root',
            'password' => 'Yaoxiu_2019',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
    ],
];
