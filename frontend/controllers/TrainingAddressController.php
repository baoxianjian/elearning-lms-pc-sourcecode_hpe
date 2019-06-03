<?php
/**
 * Created by PhpStorm.
 * User: 杨天坤
 * Date: 2016/2/23
 * Time: 9:27
 */

namespace frontend\controllers;

use yii;
use yii\db;
use frontend\base\BaseFrontController;
use common\models\learning\LnTrainingAddress;
use common\services\learning\TrainingAddressService;
use components\widgets\TPagination;

class TrainingAddressController extends BaseFrontController
{
	public $layout = 'frame';


	//主页渲染
	public function actionIndex(){
		$add = $_GET['add'] ? $_GET['add'] : '';

		return $this->render('index',['add'=>$add]);
    }
	//正文加载
	public function actionContent(){
		//基本数据获取
		$companyId = Yii::$app->user->identity->company_id;
		$userid = Yii::$app->user->getId();
		//get变量获取
		$keyword = isset($_GET['keyword'])?$_GET['keyword']:'';
		$page = $_GET['page'] ? $_GET['page'] : 1;
		$size = $this->defaultPageSize;
		if (Yii::$app->request->getQueryParam('PageSize') != null) {
			$size = Yii::$app->request->getQueryParam('PageSize');
		}
		$keyword = urldecode($keyword);
		$keyword = trim($keyword);
		//model初始化
		//数据读取
		$service = new TrainingAddressService();
		$count = $service->countData($keyword,$companyId);
		$data = $service->getData($keyword,$companyId,$size, $page);
		$pages = new TPagination(['defaultPageSize' => $size, 'totalCount' => $count]);
		//页面渲染

		return $this->renderAjax('place-table',['data'=>$data,'pages'=>$pages]);

	}
	//修改删除
	public function actionUpdate(){
		//get变量获取
		$title = isset($_GET['title'])?$_GET['title']:'';
		$code = isset($_GET['code'])?$_GET['code']:'';
		$description = isset($_GET['description'])?$_GET['description']:'';
		$type = isset($_GET['type'])?$_GET['type']:'';
		$id =  isset($_GET['kid'])?$_GET['kid']:'';
		//model初始化
		$service = new TrainingAddressService();
		$message = $service->updateByID($id,$type,$title,$description,$code);
		return $message;
	}
	//增加
	public function actionAdd(){
		//基本数据获取
		$companyId = Yii::$app->user->identity->company_id;
		//get变量获取
		$code = isset($_GET['code'])?$_GET['code']:'';
		$title = isset($_GET['title'])?$_GET['title']:'';
		$description = isset($_GET['description'])?$_GET['description']:'';

		//model初始化
		$service = new TrainingAddressService();
		$message = $service->createByUser($companyId,$code,$title,$description);
		return $message;
	}
	//增加
	public function actionView(){
		//基本数据获取
		$companyId = Yii::$app->user->identity->company_id;
		//get变量获取
		$title = isset($_GET['title'])?$_GET['title']:'';
		$description = isset($_GET['description'])?$_GET['description']:'';

		//model初始化
		$service = new TrainingAddressService();
		$message = $service->createbyuser($companyId,$title,$description);
		return $message;
	}
	public function actionIsset(){
		//基本数据获取
		$companyId = Yii::$app->user->identity->company_id;
		//get变量获取
		$kid = isset($_GET['kid'])?$_GET['kid']:'';
		$title = isset($_GET['title'])?$_GET['title']:'';
		$code = isset($_GET['code'])?$_GET['code']:'';
		$type = isset($_GET['type'])?$_GET['type']:'';
		//model初始化
		$service = new TrainingAddressService();
		$result = $service->getIsset($companyId,$title,$code,$type,$kid);
		$result['isnull'] = false;
		if(trim($title) == null){
			$result['isnull'] = true;
		}
		return json_encode($result);
	}

}