<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2016/3/14
 * Time: 11:02
 */
namespace frontend\controllers;

use common\models\framework\FwUser;
use common\models\social\SoAudience;
use common\models\treemanager\FwTreeType;
use common\services\framework\OrgnizationService;
use common\services\framework\TreeNodeService;
use common\services\framework\UserDomainService;
use common\services\framework\UserService;
use common\base\BaseActiveRecord;
use common\helpers\TFileModelHelper;
use Faker\Provider\Uuid;
use Yii;
use frontend\base\BaseFrontController;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\Response;
use components\widgets\TPagination;
use yii\helpers\ArrayHelper;
use common\services\social\AudienceManageService;

class AudienceManageController extends BaseFrontController
{
	public $layout = 'frame';

	/**
	 * 受众首页
	 * @return string
	 */
	public function actionIndex()
	{
		return $this->render('index', [

		]);
	}

	/**
	 * 受众列表
	 * @return string
	 */
	public function actionList(){
		$params = Yii::$app->request->getQueryParams();
		$params['defaultPageSize'] = $this->defaultPageSize;
		$params['ownerId'] = Yii::$app->user->getId();
		$params['companyId'] = Yii::$app->user->identity->company_id;
		$service = new AudienceManageService();
		$result = $service->getSoAudienceList($params);
		return $this->renderAjax('list', [
			'result' => $result,
			'params' => $params,
		]);
	}

	/**
	 * 复制
	 * @param $kid
	 * @return array
	 */
	public function actionCopy($kid){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$ownerId = Yii::$app->user->getId();
		$companyId = Yii::$app->user->identity->company_id;
		$service = new AudienceManageService();
		$result = $service->copyAudience($kid, $ownerId, $companyId);
		return $result;
	}

	/**
	 * 发布
	 * @param $kid
	 * @return mixed
	 */
	public function actionPublish($kid){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$service = new AudienceManageService();
		$result = $service->publishAudience($kid);
		return $result;
	}

	/**
	 * 启用
	 * @param $kid
	 * @return mixed
	 */
	public function actionStart($kid){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$service = new AudienceManageService();
		$result = $service->startAudience($kid);
		return $result;
	}

	/**
	 * 停用
	 * @param $kid
	 * @return mixed
	 */
	public function actionStop($kid){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$service = new AudienceManageService();
		$result = $service->stopAudience($kid);
		return $result;
	}

	/**
	 * 删除
	 * @param $kid
	 * @return array
	 */
	public function actionDeleted(){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$kid = Yii::$app->request->post('datalist');
		$companyId = Yii::$app->user->identity->company_id;
		$service = new AudienceManageService();
		$result = $service->deletedAudience($kid, $companyId);
		return $result;
	}

	/**
	 * 添加受众
	 * @return string
	 */
	public function actionAdd($kid = null, $view = null){
		$audience_batch = Uuid::uuid().rand(1,999999);
		$service = new AudienceManageService();
		$userId = Yii::$app->user->getId();
		$companyId = Yii::$app->user->identity->company_id;
		$service->deleteAudienceTempAll($userId, $companyId);
		$TreeNodeId = Yii::$app->request->get('TreeNodeId');
		if (empty($kid) && empty($TreeNodeId)){
			$this->redirect(Url::toRoute(['/audience-manage/index']));
			Yii::$app->end();
		}
		$model = empty($kid) ? new SoAudience() : SoAudience::findOne($kid);
		$userList = array();
		if (!empty($kid)){
			$service->createAudienceTemp($kid, $audience_batch);
			$TreeNodeId = $service->getCategoryIdToTreeNodeId($model->category_id);
			$userList = $service->getAudienceMember($kid);
		}

		return $this->render('add', [
			'audience_batch' => $audience_batch,
			'TreeNodeId' => $TreeNodeId,
			'model' => $model,
			'userList' => $userList,
			'view' => $view,
		]);
	}

	/**
	 * 添加人员
	 * @return string
	 */
	public function actionAddPerson(){
		$userId = Yii::$app->user->getId();
		$audience_batch = Yii::$app->request->get('audience_batch');
		return $this->renderAjax('add-person', [
			'audience_batch' => $audience_batch,
		]);
	}

	/**
	 * 获取组织用户列表
	 * @param null $format
	 * @return array|string
	 */
	public function actionGetOrgnizationUserList($format = null){
		if (!empty($format)) {
			Yii::$app->response->format = Response::FORMAT_JSON;
		}
		$params = Yii::$app->request->getQueryParams();
		$params['defaultPageSize'] = $this->defaultPageSize;

		$service = new AudienceManageService();
		$result = $service->getOrgnizationUser($params);

		if (!empty($format)){
			$data = array();
			if (!empty($result['data'])){
				foreach ($result['data'] as $item){
					$data[$item->kid] = array(
						'kid' => $item->kid,
						'real_name' => $item->real_name,
						'email' => $item->email,
						'mobile_no' => $item->mobile_no,
						'orgnization' => $item->orgnization_name,
						'position' => $item->position_name,
					);
				}
			}
			return ['result' => 'success', 'data' => $data];
		} else {
			return $this->renderAjax('get-orgnization-user-list', [
				'result' => $result,
				'params' => $params,
			]);
		}
	}

	/**
	 * 批量插入受众临时数据
	 */
	public function actionSetTemp()
	{
		Yii::$app->response->format = Response::FORMAT_JSON;
		$params = Yii::$app->request->post();
		$service = new AudienceManageService();
		$service->betchInsertAudeniceTemp($params);
		return ['result' => 'success'];
	}

	/**
	 * 批量删除
	 * @return array
	 */
	public function actionRemoveAudienceTemp(){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$kid = Yii::$app->request->post('kid');
		$service = new AudienceManageService();
		$service->deleteAudienceTemp($kid);
		$params = Yii::$app->request->getQueryParams();
		$service->setAudienceBatchUserName($params);
		return ['result' => 'success'];
	}

	/**
	 * 临时列表
	 * @return string
	 */
	public function actionAudienceTemp(){
		$params = Yii::$app->request->getQueryParams();
		$params['defaultPageSize'] = $this->defaultPageSize;
		$service = new AudienceManageService();
		$result = $service->getAudienceTemp($params);
		$service->setAudienceBatchUserName($params);
		return $this->renderAjax('audience-temp', [
			'data' => $result,
			'view' => $params['view'],
		]);
	}

	/**
	 * 保存
	 * @return array
	 */
	public function actionSave(){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$params = Yii::$app->request->getQueryParams();
		$params['audience_title'] = Yii::$app->request->post('audience_title');
		$params['description'] = Yii::$app->request->post('description');
		$params['TreeNodeId']  = Yii::$app->request->post('TreeNodeId');
		$params['status']  = Yii::$app->request->post('status');
		$params['owner_id'] = Yii::$app->user->getId();
		$params['company_id'] = Yii::$app->user->identity->company_id;
		$service = new AudienceManageService();
		$result = $service->saveAudienceTemp($params);
		return $result;
	}

	/**
	 * 检测是否存在相同的受众名称
	 * @param $userId
	 * @param $companyId
	 * @param $audienceName
	 * @return array
	 */
	public function actionIsExistsAudienceName($userId, $companyId, $audienceName){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$service = new AudienceManageService();
		$res = $service->isExistsAudienceName($userId, $companyId, $audienceName);
		if ($res){
			return ['result' => 'success', 'errmsg' => 'yes'];
		}else{
			return ['result' => 'success', 'errmsg' => 'no'];
		}
	}

	/**
	 * 受众导入弹窗
	 * @return string
	 */
	public function actionAddImport(){
		$audience_batch = Yii::$app->request->get('audience_batch');

		return $this->renderAjax('add-import', [
			'audience_batch' => $audience_batch,
		]);
	}
	/**
	 * 受众导入CSV
	 * @return string
	 * @throws \PHPExcel_Exception
	 * @throws \yii\base\ExitException
	 */
	public function actionImportFile(){
		//Yii::$app->response->format = Response::FORMAT_JSON;
		$TFileModelHelper = new TFileModelHelper();
		$extension = ['xls','xlsx'];
		$result = $TFileModelHelper->importExaminationQuestionFile($_FILES['myfile'], $extension);
		return json_encode($result);
		Yii::$app->end();
	}

	/**
	 * 确认提交
	 */
	public function actionImportSubmit(){
		Yii::$app->response->format = Response::FORMAT_JSON;
		if (Yii::$app->request->isAjax && Yii::$app->request->isPost){
			$audience_batch = Yii::$app->request->post('audience_batch');
			$file = Yii::$app->request->post('file');
			$fileName = Yii::$app->request->post('fileName');
			$fileMd5 = Yii::$app->request->post('fileMd5');
			if (!file_exists(Yii::$app->basePath.'/../'.$file)){
				return ['result' => 'fail', 'errmsg' => Yii::$app->basePath.'/../'.$file];
			}else{
				$TFileModelHelper = new TFileModelHelper();
				$TFileModelHelper->copyAudienceFile($file, $fileName);
				$service = new AudienceManageService();
				$result = $service->readAudienceFile($audience_batch, $file, $fileName, $fileMd5);
				return $result;
			}
		}else{
			return ['result' => 'fail', 'errmsg' => ''];
		}
	}

	/**
	 * @return string
	 */
	public function actionGetImportData(){
		$params = Yii::$app->request->getQueryParams();
		$params['defaultPageSize'] = $this->defaultPageSize;
		$service = new AudienceManageService();
		$data = $service->getSessionImportData($params);

		return $this->renderAjax('previews-list', [
			'data' => $data,
		]);
	}

	/**
	 * 确认导入正确数据
	 * @param $fileMd5
	 * @return string
	 */
	public function actionInsertImportData(){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$params = Yii::$app->request->getQueryParams();
		$service = new AudienceManageService();
		$result = $service->betchImportAudeniceTemp($params);
		return $result;
	}

	/**
	 * 删除分类
	 * @param $tree_node_id
	 * @return array
	 */
	public function actionDeleteCategory(){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$tree_node_id = Yii::$app->request->post('datalist');
		if (empty($tree_node_id)) return ['result'=>'failure','errmsg'=> Yii::t('frorntend', 'audience_params_error')];
		$categoryService = new AudienceManageService();
		$categoryService->deleteRelateData($tree_node_id);
		return ['result' => 'success', 'errmsg' => ''];
	}
}
