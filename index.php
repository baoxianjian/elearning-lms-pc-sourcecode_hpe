<?php
use common\helpers\TCacheFileHelper;

require(__DIR__ . '/common/config/mode-local.php');

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/common/config/bootstrap.php');
require(__DIR__ . '/frontend/config/bootstrap.php');

require(__DIR__ . '/common/config/autoload.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/common/config/main.php'),
    require(__DIR__ . '/common/config/main-local.php'),
    require(__DIR__ . '/frontend/config/main.php'),
    require(__DIR__ . '/frontend/config/main-local.php')
);

$application = new yii\web\Application($config);
$application->language = 'zh-CN';
$application->defaultRoute = 'startup';
$siteStatus = TCacheFileHelper::getCachedConfigValue('frontend_status');
if($siteStatus === 'offline') {
    $application->catchAll = ['site/offline', 'message' =>  Yii::t('common','contact-administrator')];
}
$application->run();
