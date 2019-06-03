<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'filemanager' => [
            'class' => 'backend\modules\filemanager\FileManagerModule',
        ],
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
            'loginUrl' => ['index/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'index/error',
        ],
        'view' => [
            'class' => 'backend\base\BaseBackView',
//            'theme' => [
//                'pathMap' => ['@app/views' => '@app/themes/default'],
//                'baseUrl' => '@web/themes/default',
//            ],
        ],
    ],
    'params' => $params,
];
