<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/24
 * Time: 22:51
 */

namespace frontend\assets;


use yii\web\AssetBundle;

class TYiiAsset extends AssetBundle
{
    public $sourcePath = '@yii/assets';
    public $js = [
        'yii.js',
    ];
    public $depends = [
    ];
}