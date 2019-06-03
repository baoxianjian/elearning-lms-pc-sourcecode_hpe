<?php


namespace backend\controllers;


use Yii;
use backend\base\BaseBackController;
use backend\services\ReportAfreshService;
use components\widgets\TPagination;
use common\models\framework\FwService;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class ReportAfreshController extends BaseBackController
{
	
	public $layout = 'frame';
	
	public function actionIndex()
	{
		return $this->render('index');
	}
	
	
	public function actionList()
	{
		$this->layout = 'list';
	
	
		$forceShowAll = 'False';
		$pageSize = $this->defaultPageSize;
	
		if (Yii::$app->request->getQueryParam('PageShowAll') != null && Yii::$app->request->getQueryParam('PageShowAll') == 'True') {
			$forceShowAll = 'True';
		}
		if (Yii::$app->request->getQueryParam('PageSize') != null) {
			$pageSize = Yii::$app->request->getQueryParam('PageSize');
		}
		 
		$service = new ReportAfreshService();
		$dataProvider = $service->search(Yii::$app->request->queryParams);
		$count = $dataProvider->totalCount;
		$page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
		$dataProvider->setPagination($page);
	
		return $this->render('list', [
				'page' => $page,
				'searchModel' => $service,
				'dataProvider' => $dataProvider,
				'pageSize' => $pageSize,
				'forceShowAll'=>$forceShowAll,
		]);
	}
	
	public function actionRestart($id)
	{
		return $this->renderAjax('restart', [
				'model' => $this->findModel($id),
		]);
	}
	
	protected function findModel($id)
	{
		if (($model = FwService::findOne($id)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException(Yii::t('common', 'data_not_exist'));
		}
	}
	
	public function actionRunRestart()
	{
		$params=Yii::$app->request->get();
		$service = new ReportAfreshService();
		$result=$service->runRestart($params);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
}