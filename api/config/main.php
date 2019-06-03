<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'api\controllers',
    'modules' => [
        'v1' => [
            'basePath' => '@api/modules/v1',
            'class' => 'api\modules\v1\ApiModule',
        ],
        'v2' => [
            'basePath' => '@api/modules/v2',
            'class' => 'api\modules\v2\ApiModule',
        ],
    ],
    'components' => [
        'user' => [
            'identityClass' => 'common\models\framework\FwUser',
            'enableAutoLogin' => false,
            'enableSession' => true,
            'loginUrl' => null
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
//            'enableStrictParsing' => true,
            'showScriptName' => false,
            'suffix' => false,
//            'rules' => [
//                [
//                    'class' => 'yii\rest\UrlRule',
//                    'controller' => ['v1/user'],
//                    'tokens' => [
//                        '{id}' => '<id:[?a-zA-Z0-9]{8}-[?a-zA-Z0-9]{4}-[?a-zA-Z0-9]{4}-[?a-zA-Z0-9]{4}-[?a-zA-Z0-9]{12}>'
//                    ],
//                    'pluralize' => false,
//                ],
//            ],
        ],
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'text/json' => 'yii\web\JsonParser',
            ]
        ],
        'errorHandler' => [
            'errorAction' => 'index/error',
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null && $response->format != "html" ) {
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'data' => $response->data,
                    ];
                    $response->statusCode = 200;
                }
            },
        ],
    ],
    'params' => $params,
];
