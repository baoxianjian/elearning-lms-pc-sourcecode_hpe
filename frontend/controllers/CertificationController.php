<?php


namespace frontend\controllers;


use common\models\learning\LnCertification;
use Yii;
use frontend\base\BaseFrontController;

use yii\data\Pagination;
use common\models\framework\FwUser;

use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use components\widgets\TPagination;

use common\helpers\TTimeHelper;

use yii\helpers\ArrayHelper;
use common\services\learning\CertificationService;
use common\models\learning\LnUserCertification;




class CertificationController extends BaseFrontController
{
	public $layout = 'frame';
	
	
	
	public function actionIndex()
	{
		return $this->render('index');
	}
	
	public function actionNewCertification()
	{
		$certificationService=new CertificationService();
		$temps=$certificationService->getTemplates();
		
		return $this->renderAjax('new_certification',['temps'=>$temps]);
	}
	
	public function actionPublishCertification($id,$p_page)
	{
		$certificationService=new CertificationService();
		$certification=$certificationService->getCertification($id);
		return $this->renderAjax('publish_certification',['certi'=>$certification,'p_page'=>$p_page]);
	}
	
	public function actionPublishCertificationUsers()
	{
		
		$params=Yii::$app->request->post();
		
		$certificationService=new CertificationService();
		$certificationService->saveCertificationUsers($params);
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success'];
	}
	
	public function actionEditCertificationUi($id)
	{
		$certificationService=new CertificationService();
		$temps=$certificationService->getTemplates();
		//$certification=$certificationService->getCertification($id);
		return $this->renderAjax('edit_certification',['temps'=>$temps,'id'=>$id]);
	}
	
	public function actionViewCertification($id)
	{
		$certificationService=new CertificationService();
		$certification=$certificationService->getCertification($id);
		
		if($certification['is_email_user']=='1'){
			$certification['is_email_user']=Yii::t('frontend', 'pick_up_people');  
			$certification['is_email']=Yii::t('frontend', 'pick_up_people');
		}else{
			$certification['is_email_user']='';
			$certification['is_email']='';
		}
		
		if($certification['is_email_teacher']=='1'){
			$certification['is_email_teacher']=Yii::t('common', 'lecturer') ;
			if($certification['is_email']!=''){
				$certification['is_email']=$certification['is_email'].','.$certification['is_email_teacher'];
			}else{
				$certification['is_email']=$certification['is_email_teacher'];
			}
		}else{
			$certification['is_email_teacher']='';
		}
		
		if($certification['is_email']==''){
			$certification['is_email']=Yii::t('frontend', 'not_use');
		}
		
		if($certification['is_print_score']=='1'){
			$certification['is_print_score']=Yii::t('frontend', 'yes'); 
		}else{
			$certification['is_print_score']=Yii::t('frontend', 'no');
		}
		
		if($certification['is_auto_certify']=='1'){
			$certification['is_auto_certify']=Yii::t('frontend', 'yes');
		}else{
			$certification['is_auto_certify']=Yii::t('frontend', 'no');
		}
		
		//天数
		if($certification['expire_time_type']=='0'){
			$certification['expire_time']=$certification['expire_time'].Yii::t('frontend', 'day');
		}else if($certification['expire_time_type']=='1'){
			//日历
			$certification['expire_time']=date("Y年m月d日",strtotime($certification['expire_time']));
		}else if($certification['expire_time_type']=='2'){
			$certification['expire_time']=Yii::t('frontend', 'forever_valid');
		}
		
		
		return $this->renderAjax('view_certification',['certi'=>$certification]);
	}
	
	public function actionGetCertification($id)
	{
		$certificationService=new CertificationService();
		$certification=$certificationService->getCertification($id);
		$certification["description"] = Html::decode($certification["description"]);
		$certification["certification_name"] = Html::decode($certification["certification_name"]);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $certification];
	}
	
	
	
	
	public function actionSaveCertification()
	{
		//$user_id = Yii::$app->user->getId();
		if (Yii::$app->request->isPost) {
			$params = Yii::$app->request->post();
			Yii::$app->response->format = Response::FORMAT_JSON;
			$certificationService = new CertificationService();
			$error = [];
			if ($certificationService->saveCertification($params,$error)) {
				return ['result' => 'success'];
			}
			else {
				return ['result' => 'failure', 'message' => $error];
			}
		}
	}


	public function actionPreview($id)
	{
		$this->layout = 'none';
		$service = new CertificationService();
		$model =  $this->findModel($id);
		$printOrientation = $model->print_orientation;
		$html = $service->getCertificationContent($model);
		if (empty($html)) {
			$message = Yii::t('frontend', 'certifi_message'); 
		}
		else {
			$message = null;
		}
		return $this->render('/common/certification-preview', [
			'message'=>$message,
			'html' => $html,
			'printOrientation' => $printOrientation,
		]);
	}

	/**
	 * Finds the LnCertificationTemplate model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param string $id
	 * @return LnCertification the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		if (($model = LnCertification::findOne($id)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException(Yii::t('common', 'data_not_exist'));
		}
	}
	
	public function actionEditCertification()
	{
		$params=Yii::$app->request->post();
		
		$certificationService=new CertificationService();
		$certificationService->editCertification($params);
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success'];
	}
	
	public function actionList()
	{
	
		$pageSize = $this->defaultPageSize;
	
		if (Yii::$app->request->getQueryParam('PageSize') != null) {
			$pageSize = Yii::$app->request->getQueryParam('PageSize');
		}
	
		$page_num=Yii::$app->request->getQueryParam('page');
		$service = new CertificationService();
		$dataProvider = $service->search(Yii::$app->request->queryParams);
		$count = $dataProvider->totalCount;
		$page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
		$dataProvider->setPagination($page);
	
		if(!$page_num){
			$page_num=1;
		}
	
		
		return $this->renderAjax('list', [
				'page' => $page,
				'page_num'=>$page_num,
				'searchModel' => $service,
				'dataProvider' => $dataProvider,
				'pageSize' => $pageSize
		]);
	}
	
	public function actionPubList_bak()
	{
	
		$pageSize = $this->defaultPageSize;
	
		if (Yii::$app->request->getQueryParam('PageSize') != null) {
			$pageSize = Yii::$app->request->getQueryParam('PageSize');
		}
	
		$service = new CertificationService();
		$dataProvider = $service->search_pub(Yii::$app->request->queryParams);
		$count = $dataProvider->totalCount;
		$page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
		$dataProvider->setPagination($page);
	
	
		return $this->renderAjax('pub_list', [
				'page' => $page,
				'searchModel' => $service,
				'dataProvider' => $dataProvider,
				'pageSize' => $pageSize
		]);
	}
	
	public function actionPubList()
	{
	
		$pageSize = $this->defaultPageSize;
	
		if (Yii::$app->request->getQueryParam('PageSize') != null) {
			$pageSize = Yii::$app->request->getQueryParam('PageSize');
		}
		
	
	
		$service = new CertificationService();
		$pinfolist = $service->search_pub(Yii::$app->request->queryParams);
		
		
		return $this->renderAjax('pub_list', $pinfolist);
	}
	
	
	public function actionCertificationNameValidate()
	{
		$params=Yii::$app->request->get();
		$certificationService=new CertificationService();
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		if($certificationService->nameValidate($params)){
			return ['result' => 'yes'];
		}else{
			return ['result' => 'no'];
		}
	
	}
	
	public function actionGetUsers()
	{
		$params=Yii::$app->request->get();
		$certificationService=new CertificationService();
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		$tmp=$certificationService->getUsers();
		$arr_title=[];
		foreach ($tmp as $r){
			 $t['title']=$r['real_name']."/".$r['orgnization_name'];
			 $t['uid']=$r['kid'];
			 Array_push($arr_title,$t); 
		}
		$result['results']=$arr_title;
		$result['success']=true;
		return $result;
	
	}
	
	public function actionDeleteOne(){
	
		$id=Yii::$app->request->getQueryParam('id');
	
		$service = new CertificationService();
		$service->deleteCertification($id);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success'];
	
	}
	
	public function actionCanelCertificationUser(){
		$cuid=Yii::$app->request->getQueryParam('cuid');
		$mission_id=Yii::$app->request->getQueryParam('mission_id');
		$service = new CertificationService();
		$service->cancelCertificationUser($cuid,$mission_id);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success'];
		
	}
	

}
