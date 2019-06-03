<?php
namespace app\controllers;


use app\base\BaseAppController;
use backend\services\MenuService;
use backend\services\PermissionService;
use common\services\framework\UserService;
use common\models\framework\FwUser;
use common\viewmodels\framework\Menu;
use common\base\BaseController;
use common\helpers\TNetworkHelper;
use components\widgets\ActiveForm;
use Yii;
use backend\base\BaseBackController;
use common\viewmodels\framework\LoginForm;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

use common\models\social\SoRecord;
use common\models\social\SoShare;
use common\models\social\SoQuestion;
use common\models\social\SoUserAttention;
use common\services\social\UserAttentionService;
use common\services\message\MessageService;

class IndexController extends BaseController
{
    public $layout = 'main';

    public function behaviors()
    {
        $newBehaviors = [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login','error', 'page', 'captcha', 'offline'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['index','logout','get-dynamic-message'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];

        return ArrayHelper::merge($newBehaviors, parent::behaviors());
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
    
    
    /**
     * 登陆后的主界面
     * 待完成
     * @return Ambigous <string, string>
     */
    public function actionIndex()
    {
    	/* $id = Yii::$app->user->getId();
    	 $user = FwUser::findOne($id);
    	 $model = new SoUserAttention();
    	 $model->user_id = $id;
    	 $model->attention_id = $id;
    	  
    	 //启用指定的用户关注
    	 $uaService = new UserAttentionService();
    	 $uaService->startRelationship($model);
    
    	 $questionModel = new SoQuestion();
    
    	 $shareModel = new SoShare();
    
    	 $recordModel = new SoRecord(); */
    
    	return $this->render('index');
    }
    
    
    public function actionGetDynamicMessage($type, $page, $time = null)
    {
    	$this->layout = 'none';
    	$id = Yii::$app->user->getId();
    	$size = 10;
    	$view = 'tab-student-course';
    	 
    	$msgService = new MessageService();
    	$time=null;
    	$data = $msgService->getMessageByUid($id, $type, $time, $size, $size < 1 ? 0 : ((int)$page - 1) * $size);
    	if($data)
    		return $this->render($view, ['data' => $data['data'],]);
		else 
			return null;
    }
    
    /**
     * 登录
     * @return \yii\web\static|Ambigous <string, string>
     */
    public function actionLogin()
    {
    	$this->layout = 'loginLayout';
    
    	$model = new LoginForm();
        $commonUserService = new UserService();
    	if ($model->load(Yii::$app->request->post()) && $commonUserService->loginCheck($model)) {
    		//            return $this->goHome();
			return  Yii::$app->getResponse()->redirect(['index']);
//            echo "<script type=text/javascript>";
//            echo "window.location.href='js-call://success'";
//            echo "</script>";exit;
    	} else {
    		return $this->render('login', [
    				'model' => $model,
    		]);
    	}
    }
    
    /**
     * 注销
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();
            $userModel = FwUser::findOne($userId);

            $userModel->online_status = FwUser::ONLINE_STATUS_OFFLINE;
            $userModel->save();

            Yii::$app->user->logout();
        }
        return $this->goHome();
    }

}