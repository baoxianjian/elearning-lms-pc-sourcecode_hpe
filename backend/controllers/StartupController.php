<?php
/**
 * Created by PhpStorm.
 * User: t62539
 * Date: 3/22/2016
 * Time: 10:35 PM
 */

namespace backend\controllers;


use backend\base\BaseBackController;
use Yii;

class StartupController extends BaseBackController
{
    public function actionIndex()
    {
        $action = parent::getStartupAction();
        $url = Yii::$app->urlManager->createUrl($action);
        $this->redirect($url);
    }
}