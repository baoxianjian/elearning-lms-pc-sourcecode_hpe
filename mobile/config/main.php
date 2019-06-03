<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-mobile',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'mobile\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'gridview' =>  [
            'class' => '\kartik\grid\Module'
            // enter optional module parameters below - only if you need to
            // use your own export download action or custom translation
            // message source
            // 'downloadAction' => 'gridview/export/download',
            // 'i18n' => []
        ]
    ],
    'components' => [
        'user' => [
            'identityClass' => 'common\models\framework\FwUser',
            'enableAutoLogin' => true,
            'loginUrl' => ['wechat/auth/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'index/error',
        ],
        'view' => [
            'class' => 'mobile\base\BaseMobileView',
//            'theme' => [
//                'pathMap' => ['@app/views' => '@app/themes/default'],
//                'baseUrl' => '@web/themes/default',
//            ],
        ],
    ],
    'params' => $params,
];
