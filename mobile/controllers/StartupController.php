<?php
/**
 * Created by PhpStorm.
 * User: t62539
 * Date: 3/22/2016
 * Time: 10:35 PM
 */

namespace mobile\controllers;


use mobile\base\BaseMobileController;
use Yii;

class StartupController extends BaseMobileController
{
    public function actionIndex()
    {
        $action = parent::getStartupAction();
        $url = Yii::$app->urlManager->createUrl($action);
        $this->redirect($url);
    }
}