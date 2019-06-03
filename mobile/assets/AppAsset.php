<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace mobile\assets;

use yii\web\AssetBundle;


class AppAsset extends AssetBundle
{
    public $basePath = '@static/mobile/';
    public $baseUrl = '/mobile/weApp';
    public $css = [
        'proto/assets/css/amazeui.flat.css',
        'assets/css/we.css',
        'proto/assets/css/app.css',
    ];
    public $js = [
        'proto/assets/js/amazeui.min.js',
        'proto/assets/js/fastclick.js',
        'lib/main.js',
        'lib/weapp.js',
        'lib/tpl.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];


}
