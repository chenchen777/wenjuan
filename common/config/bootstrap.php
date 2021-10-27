<?php
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@root', dirname(dirname(__DIR__)));
Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('@front', dirname(dirname(__DIR__)) . '/front');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('@api', dirname(dirname(__DIR__)) . '/api');
Yii::setAlias('@lectureb', dirname(dirname(__DIR__)) . '/lectureb');

//aliDayu sdk
//require Yii::getAlias('@root') . '/extends/Dayu/TopSdk.php';
//ucloud sdk
require __DIR__ . '/../../extends/ucloud/autoload.php';
