<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/24
 * Time: 22:51
 */

namespace frontend\assets;


use yii\web\AssetBundle;

class TJuiAsset extends AssetBundle{
    public $sourcePath = '@bower/jquery-ui';
    public $js = [
        'jquery-ui.js',
    ];
    public $css = [

    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}