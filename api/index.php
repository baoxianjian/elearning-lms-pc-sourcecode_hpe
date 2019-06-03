<?php
use common\helpers\TCacheFileHelper;

require(__DIR__ . '/../common/config/mode-local.php');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../common/config/bootstrap.php');
require(__DIR__ . '/config/bootstrap.php');

require(__DIR__ . '/config/autoload.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../common/config/main.php'),
    require(__DIR__ . '/../common/config/main-local.php'),
    require(__DIR__ . '/config/main.php'),
    require(__DIR__ . '/config/main-local.php')
);

$application = new yii\web\Application($config);
$application->language='zh-CN';
$application->defaultRoute='index';
$siteStatus = TCacheFileHelper::getCachedConfigValue('api_status');
if($siteStatus === 'offline') {
    $application->catchAll = ['index/offline', 'message' =>  Yii::t('common','contact-administrator')];
}


$application->run();
