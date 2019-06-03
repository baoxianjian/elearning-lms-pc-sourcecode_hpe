<?php

namespace app\base;

use common\models\framework\FwUser;
use common\services\framework\RbacService;
use Yii;
use common\base\BaseController;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;

use app\common\C;

class BaseAppController extends BaseController
{
    public $systemFlag = 'eln_app';

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [

            'baseAppFilter' => [
                'class' => BaseAppFilter::className(),
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
    
    /** 
     * 测试数据
     * 返回一个测试用户id
     * @return string
     */
    public function getTestUserId(){
    	if(C::IsDebug){
    		return 'C1FC8A78-E992-C62E-57CF-44B872A30E6C';
    	}
    }
    
    public function isDebug(){
    	return C::IsDebug;
    }
}