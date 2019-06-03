<?php
/**
 * Created by PhpStorm.
 * User: t62539
 * Date: 3/22/2016
 * Time: 10:17 PM
 */

namespace frontend\controllers;

use frontend\base\BaseFrontController;
use Yii;

class StartupController extends BaseFrontController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['except'] = ['index'];

        return $behaviors;
    }

    public function actionIndex()
    {
        $action = parent::getStartupAction();
        $url = Yii::$app->urlManager->createUrl($action);
        $this->redirect($url);
    }
}