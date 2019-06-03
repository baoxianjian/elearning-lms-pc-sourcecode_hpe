<?php
namespace mobile\base;

use common\services\framework\RbacService;
use Yii;
use common\base\BaseController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use mobile\services\WechatAuthBehavior;
use yii\helpers\ArrayHelper;
use mobile\services\WechatUserEvent;

class BaseMobileController extends BaseController
{
    public $systemFlag = 'eln_mobile';
    
    public $userId;
    public $companyId;
    
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
        return ArrayHelper::merge(parent::behaviors(),[
            'wechatAuth' => WechatAuthBehavior::className(),
//            'access' => [
//                'class' => AccessControl::className(),
////                'except' => ['login', 'error', 'page', 'captcha', 'offline', 'signup', 'no-authority', 'request-password-reset', 'reset-password'],
//                'rules' => [
//                    [
//                        'allow' => true,
//                        'roles' => ['@'],
//                    ],
//                ],
//            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
//                    'logout' => ['post'],
                    'delete' => ['post'],
                ],
            ],
            'baseMobileFilter' => [
                'class' => BaseMobileFilter::className(),
            ]
        ]);
	}


	public function beforeAction($action){
        
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
            
            $this->userId = $userId;
            $this->companyId = Yii::$app->user->identity->company_id;
        }
        else
        {
            $userId = null;
            $user = null;
            $canAction = true;
            $canUrl = true;
        }

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
    


    public function getViewPath()
    {
        return dirname(__FILE__).'/../views/weapp/';
    }

    public function getAssetsPath($prefix = '/static/mobile') {
        return Yii::$app->request->getHostInfo().$prefix;
    }
    
    public function findViewFile() {

    }

    public function getStartupAction() {
        $defaultAction = "index/index";
        if (isset(Yii::$app->params["mobile-startup"]) && !empty(Yii::$app->params["mobile-startup"])) {
            $action = Yii::$app->params["mobile-startup"];
        }
        else {
            $action = $defaultAction;
        }

        return $action;
    }

    public function isWechat() {
        $ua = Yii::$app->request->getHeaders()->get('User-Agent');
        return !!preg_match("/MicroMessenger/i",$ua);
    }
}