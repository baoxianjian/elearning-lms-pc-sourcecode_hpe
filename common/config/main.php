<?php


return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'assetManager' => [
            'basePath' => '@static/assets',//physicalPath
            'baseUrl'=>'/static/assets',//virtualPath
            'bundles' => [
                // you can override AssetBundle configs here
            ],
            //'linkAssets' => true,
            // ...
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'suffix' => '.html',
//            'rules' => [
//                //标准的控制器/方法显示
//                '<lang:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>'=>'<controller>/<action>'
//            ]
        ],
        'formatter' => [
            'dateFormat' => 'yyyy-MM-dd',
            'decimalSeparator' => '.',
            'thousandSeparator' => ',',
            'currencyCode' => 'CNY',
        ],
//        'authManager' => [
//            'class' => 'yii\rbac\DbManager',
//        ],
        'i18n' => [
            'translations' => [
//                'app*' => [
//                    'class' => 'yii\i18n\PhpMessageSource',
//                    'sourceLanguage' => 'en-US',
//                    'basePath' => '@app/messages',
//                    'fileMap' => [
//                        'app' => 'app.php',
//                        'app/error' => 'error.php',
//                    ],
////                    'on missingTranslation' => ['app\components\TranslationEventHandler', 'handleMissingTranslation']
//                ],
                'common*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'zh',
                    'basePath' => '@common/messages',
                    'fileMap' => [
                        'common' => 'common.php',
                    ],
                ],
                'data*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'zh',
                    'basePath' => '@common/messages',
                    'fileMap' => [
                        'data' => 'data.php',
                    ],
                ],
                'frontend*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'zh',
                    'basePath' => '@common/messages',
                    'fileMap' => [
                        'frontend' => 'frontend.php',
                    ],
                ],
                'system*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'zh',
                    'basePath' => '@common/messages',
                    'fileMap' => [
                        'system' => 'system.php',
                    ],
                ],
                'backend*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'zh',
                    'basePath' => '@common/messages',
                    'fileMap' => [
                        'backend' => 'backend.php',
                    ],
                ],
                'api*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'zh',
                    'basePath' => '@common/messages',
                    'fileMap' => [
                        'api' => 'api.php',
                    ],
                ],
                'mobile*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'zh',
                    'basePath' => '@common/messages',
                    'fileMap' => [
                        'mobile' => 'mobile.php',
                    ],
                ],
            ],
        ],

    ],
];