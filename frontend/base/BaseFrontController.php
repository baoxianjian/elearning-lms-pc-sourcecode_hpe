<?php

namespace frontend\base;

use common\models\framework\FwCompany;
use common\models\message\MsMessage;
use common\models\message\MsMessageUser;
use common\services\framework\RbacService;
use common\services\message\MessageService;
use common\services\framework\UserService;
use common\helpers\TBaseHelper;
use Yii;
use common\base\BaseController;
use yii\base\Theme;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\framework\FwUser;
use yii\helpers\Url;
use yii\log\Logger;


class BaseFrontController extends BaseController
{

    public $systemFlag = 'eln_frontend';

    public $have_remind = null;
    public $have_news = null;
    public $todo_data = null;
    public $ms_setting = null;

    public $courseMessageCount = 0;
    public $qaMessageCount = 0;
    public $newsMessageCount = 0;
    public $socialMessageCount = 0;


    public function init()
    {
        parent::init();

        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();
            $service = new MessageService();

            $from = Yii::$app->request->get('from');
            if (!empty($from) && $from === 'message') {
                $msg_id = Yii::$app->request->get('msg_id');
                $msg_type = Yii::$app->request->get('msg_type');
                if (!empty($msg_id) && ($msg_type === MsMessageUser::TYPE_NORMAL || $msg_type === MsMessageUser::TYPE_SPECIAL)) {
                    $service->SetReceive($userId, $msg_id, $msg_type);
                }
            }

            $uService = new UserService();

//            $this->courseMessageCount = $service->getMessageCountByUid($userId, MsMessage::TYPE_TODO);
//            $this->qaMessageCount = $service->getMessageCountByUid($userId, MsMessage::TYPE_QA);
//            $this->newsMessageCount = $service->getNewsMessageCountByUid($userId);
//            $this->socialMessageCount = $service->getMessageCountByUid($userId, MsMessage::TYPE_SOCIAL);

            $this->ms_setting = $uService->getSubscribeSetting($userId);
        }
    }


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
            'baseFrontFilter' => [
                'class' => BaseFrontFilter::className(),
            ],
        ];
    }

    public function beforeAction($action)
    {
        // 通过header强制指定浏览器用最新模式
        Yii::$app->response->headers->add('X-UA-Compatible','IE=Edge');
		
//        Yii::getLogger()->log("beforeAction", Logger::LEVEL_ERROR);
        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();
//            $user = new FwUser();
//            $companyId = Yii::$app->user->identity->company_id;

            $controllerId = $action->controller->id;
            $actionId = $action->id;

            $actionUrl = $controllerId . '/' . $actionId;

            if ($actionUrl !== "site/change-password" && Yii::$app->user->identity->need_pwd_change === FwUser::NEED_PWD_CHANGE_YES) {
                $returnLoginPage = false;
                $canAction = true;
                $canUrl = true;
                $url = Url::toRoute(['site/change-password']);
                $this->redirect($url);
            }
            else {
                $urlArray = [];
                array_push($urlArray, $actionUrl);
//        $parameter = json_decode($this->action_parameter,true);

//        if (isset($parameter) && $parameter != null) {
//            $urlArray = array_merge($urlArray, $parameter);
//        }
                $url = Url::toRoute($urlArray);

                $rbacService = new RbacService();
                $canAction = $rbacService->canAction($userId, $this->systemFlag, $actionUrl);

                $canUrl = $rbacService->canUrl($userId, $this->systemFlag, $url);

                if ($actionUrl != "site/logout") {
                    $returnLoginPage = $rbacService->isSpecialUser($userId);
                } else {
                    $returnLoginPage = false;
                }
            }
        } else {
            $userId = null;
            $user = null;
            $canAction = true;
            $canUrl = true;
            $returnLoginPage = false;
        }

        $this->setTheme("frontend");

        if ($returnLoginPage) {
            $url = Url::toRoute(['site/logout']);
            $this->redirect($url);
        } else {
            if ($canAction && $canUrl) {
                if (parent::beforeAction($action)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                $url = Url::toRoute(['site/no-authority']);
                $this->redirect($url);
            }
        }
    }

    public function getStartupAction()
    {
        $defaultAction = "site/login";
        if (!Yii::$app->user->isGuest) {
            $defaultAction = TBaseHelper::getHomePage('action');
        }

        if (isset(Yii::$app->params["frontend-startup"]) && !empty(Yii::$app->params["frontend-startup"])) {
            $action = Yii::$app->params["frontend-startup"];
        } else {
            $action = $defaultAction;
        }

        return $action;
    }
}