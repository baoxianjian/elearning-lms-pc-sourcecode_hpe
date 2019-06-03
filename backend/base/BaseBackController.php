<?php

namespace backend\base;

use common\helpers\TBaseHelper;
use common\models\framework\FwUser;
use common\services\framework\RbacService;
use Yii;
use common\base\BaseController;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;

class BaseBackController extends BaseController
{
    public $systemFlag = 'eln_backend';

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
            'access' => [
                'class' => AccessControl::className(),
//                'except' => ['login', 'error', 'page', 'captcha', 'offline', 'signup', 'no-authority', 'request-password-reset', 'reset-password'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
//                    'logout' => ['post'],
                    'delete' => ['post'],
                ],
            ],
            'baseBackFilter' => [
                'class' => BaseBackFilter::className(),
            ],
//            'timestamp' => [
//                'class' => TimestampBehavior::className(),
//                'attributes' => [
//                    ActiveRecord::EVENT_BEFORE_INSERT => ['CREATE_DATE', 'UPDATE_DATE'],
//                    ActiveRecord::EVENT_BEFORE_UPDATE => ['UPDATE_DATE'],
//                ],
//            ],

//			'verbs' => [
//				'class' => VerbFilter::className(),
//				'actions' => [
//					'logout' => ['post'],
//				],
//			],
		];
	}


    public function beforeAction($action)
    {
        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();
//            $user = new FwUser();

            $controllerId = $action->controller->id;
            $actionId = $action->id;

            $actionUrl = $controllerId . '/' . $actionId;


            $urlArray = [];
            array_push($urlArray,$actionUrl);
//        $parameter = json_decode($this->action_parameter,true);

//        if (isset($parameter) && $parameter != null) {
//            $urlArray = array_merge($urlArray, $parameter);
//        }
            $url = Url::toRoute($urlArray);

            $rbacService = new RbacService();
            $canAction = $rbacService->canAction($userId, $this->systemFlag,$actionUrl);

            $canUrl = $rbacService->canUrl($userId, $this->systemFlag,$url);
        }
        else
        {
            $userId = null;
            $user = null;
            $canAction = true;
            $canUrl = true;
        }

        $this->setTheme("backend");

        if ($canAction && $canUrl) {
            if (parent::beforeAction($action)) {
                return true;
            } else {
                return false;
            }
        }
        else
        {
            $url = Url::toRoute(['index/no-authority']);
            $this->redirect($url);
        }
    }

    public function getStartupAction() {
        $defaultAction = "index/login";
        if (!Yii::$app->user->isGuest) {
            $defaultAction = "index/index";
        }

        if (isset(Yii::$app->params["backend-startup"]) && !empty(Yii::$app->params["frontend-startup"])) {
            $action = Yii::$app->params["backend-startup"];
        }
        else {
            $action = $defaultAction;
        }

        return $action;
    }
}