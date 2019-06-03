<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-app',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'app\controllers',
    'bootstrap' => ['log'],
    'components' => [
        'user' => [
            'identityClass' => 'common\models\framework\FwUser',
            'enableAutoLogin' => true,
            'enableSession' => true,
            'loginUrl' => null
        ],
        'urlManager' => [
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v1/user','user'],
                    'tokens' => [
                        '{id}' => '<id:[?a-zA-Z0-9]{8}-[?a-zA-Z0-9]{4}-[?a-zA-Z0-9]{4}-[?a-zA-Z0-9]{4}-[?a-zA-Z0-9]{12}>'
                    ],
                    'pluralize' => false,
                ],
            ],
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
