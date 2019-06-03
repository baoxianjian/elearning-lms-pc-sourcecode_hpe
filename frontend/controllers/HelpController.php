<?php
/**
 * 帮助控制器
 * author: Alex Liu
 * date: 2016/3/4
 * time: 17:35
 */

namespace frontend\controllers;

use frontend\base\BaseFrontController;
use yii;
use yii\db;

class HelpController extends BaseFrontController
{
    public $layout = 'frame';

    //主页渲染
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionSharePlugin()
    {
        $hostUrl = Yii::$app->request->getHostInfo();

        return $this->render('share-plugin', [
            'hostUrl' => $hostUrl,
        ]);
    }
}