<?php
namespace api\controllers;


use common\services\framework\UserService;
use common\models\framework\FwUser;
use common\viewmodels\framework\Menu;
use common\base\BaseController;
use common\helpers\TNetworkHelper;
use components\widgets\ActiveForm;
use Yii;
use common\viewmodels\framework\LoginForm;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class IndexController extends BaseController
{
    public $layout = 'frame';

    public function behaviors()
    {
        $newBehaviors = [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'error', 'page', 'captcha', 'offline'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];

        return ArrayHelper::merge($newBehaviors, parent::behaviors());
    }

    /**
     * @return string 后台默认页面
     */
    public function actionIndex()
    {
        return $this->render('index');
    }


    public function actionHome()
    {
        return $this->render('home');
    }


    public function actionOffline($message = null)
    {
        $this->layout = false;
        return $this->render('offline', ['message' => $message]);
    }

}