<?php
/**
 * Created by PhpStorm.
 * User: t62539
 * Date: 3/22/2016
 * Time: 10:35 PM
 */

namespace mobile\controllers;

use mobile\base\BaseMobileController;
use yii;
use yii\web\Response;

class ListController extends BaseMobileController
{
    public $layout = 'main';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['except'] = ['login', 'error', 'index', 'page', 'captcha', 'offline', 'no-authority'];

        return $behaviors;
    }

    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['result','true'];
    }
}