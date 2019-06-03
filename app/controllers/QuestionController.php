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

use common\models\social\SoQuestion;
use common\services\social\QuestionService;
use common\services\message\MessageService;
use common\services\framework\TagService;

class QuestionController extends BaseAppController
{
	public $layout = 'main';

	public function behaviors()
	{
		$newBehaviors = [
				'access' => [
						'class' => AccessControl::className(),
						'rules' => [
								[
										'actions' => ['index-tab-question','get-tag'],
										'allow' => true,
								],
						],
				]
		];

		return ArrayHelper::merge($newBehaviors, parent::behaviors());
	}

 /**
     * 提问
     * @return multitype:string |string
     */
    public function actionIndexTabQuestion()
    {
    	$model = new SoQuestion();
    	if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
    		Yii::$app->response->format = Response::FORMAT_JSON;
    		$id = Yii::$app->user->getId();
    		$id=$this->getTestUserId();
    		$user = FwUser::findOne($id);
    		//var_dump($user->company_id);
    		$service = new QuestionService();
    
    		$model->tags = Yii::$app->request->post('tags');
    
    		if ($service->CreateQuestion($model, $user)) {
    			$messageService = new MessageService();
    			$messageService->pushMessageByQuestion($user, $model);
    			return ['result' => 'success'];
    
    			//return ['result' => 'success'];
    		} else {
    			$errors = array_values($model->getErrors());
    			$message = '';
    			for ($i = 0; $i < count($errors); $i++) {
    				$message .= $errors[$i][0] . '<br />';
    			}
    
    			return ['result' => 'other', 'message' => $message];
    		}
    	} else {
    		return $this->renderAjax('index-tab-question', ['data' => $model,]);
    	}
    }
    
    /**
     * 获取话题TAG
     * @return Ambigous <string, unknown>
     */
    public function actionGetTag()
    {
    	Yii::$app->response->format = Response::FORMAT_JSON;
    	$companyId = Yii::$app->user->identity->company_id;
    
    	$tagService = new TagService();
    	$word = Yii::$app->request->post('val');
    
    	$tags = $tagService->getTagValueListWithTagValue($companyId, 'conversation', $word);
    	return $tags ? $tags : '';
    }
}