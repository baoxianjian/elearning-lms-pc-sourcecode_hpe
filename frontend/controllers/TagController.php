<?php
/**
 * Created by PhpStorm.
 * User: 杨天坤
 * Date: 2015/8/27
 * Time: 9:27
 */

namespace frontend\controllers;

use yii;
use yii\db;
use frontend\base\BaseFrontController;
use common\models\framework\FwTag;
use common\services\framework\TagService;
use components\widgets\TPagination;

class TagController extends BaseFrontController
{
	public $layout = 'frame';

	//主页渲染
	public function actionIndex(){

		return $this->render('index');
    }
	//正文加载
	public function actionContent(){
		//基本数据获取
		$companyId = Yii::$app->user->identity->company_id;
		$userid = Yii::$app->user->getId();
		//get变量获取
		$category = isset($_GET['category'])?$_GET['category']:'course';
		$keyword = isset($_GET['keyword'])?$_GET['keyword']:'';
		$page = $_GET['page'] ? $_GET['page'] : 1;
		$size = $this->defaultPageSize;
		if($category == 'knowledge') $category='examination_question-knowledge-point';
		if (Yii::$app->request->getQueryParam('PageSize') != null) {
			$size = Yii::$app->request->getQueryParam('PageSize');
		}
		$keyword = urldecode($keyword);
		$keyword = trim($keyword);
		//model初始化
		$service = new TagService();
		//数据读取
		$count = $service->countTagsByCategoryNew($category,$keyword,$companyId);
		$data = $service->getTagsByCategoryNew($category,$keyword,$companyId,$size, $page);
		$pages = new TPagination(['defaultPageSize' => $size, 'totalCount' => $count]);
		//页面渲染
		return $this->renderAjax('tag-table',['data'=>$data,'pages'=>$pages]);

	}
	//修改删除标签
	public function actionTagupdate(){
		//get变量获取
		$tagname = isset($_GET['name'])?$_GET['name']:'';
		$type = isset($_GET['type'])?$_GET['type']:'';
		$id =  isset($_GET['kid'])?$_GET['kid']:'';
		//model初始化
		$service = new TagService();
		$message = $service->updateTagByID($id,$type,$tagname);
		die($message);
	}
	//增加标签
	public function actionTagadd(){
		//基本数据获取
		$companyId = Yii::$app->user->identity->company_id;
		//get变量获取
		$tagname = isset($_GET['name'])?$_GET['name']:'';
		$category = isset($_GET['category'])?$_GET['category']:'';
		if($category == 'knowledge') $category='examination_question-knowledge-point';
		//model初始化
		$service = new TagService();
		$message = $service->createTagByUser($companyId,$category,$tagname);
		die($message);
	}
	public function actionTagisset(){
		//基本数据获取
		$companyId = Yii::$app->user->identity->company_id;
		//get变量获取
		$tagname = isset($_GET['name'])?$_GET['name']:'';
		$category = isset($_GET['category'])?$_GET['category']:'';
		if($category == 'knowledge') $category='examination_question-knowledge-point';
		if($tagname == null){
			die('null');
		}
		if(strlen($tagname)>61){
			die('long');
		}
		//model初始化
		$service = new TagService();
		$count = $service->getTagByValueOnKind($companyId,$tagname,$category);
		if($count > 0 ){
			die('isset');
		}
		die('success');
	}

}