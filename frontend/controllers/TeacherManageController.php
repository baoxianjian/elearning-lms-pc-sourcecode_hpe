<?php


namespace frontend\controllers;


use common\models\learning\LnTeacher;
use common\base\BaseActiveRecord;
use Yii;
use frontend\base\BaseFrontController;

use yii\data\Pagination;
use common\models\framework\FwUser;

use yii\web\Response;
use components\widgets\TPagination;

use common\helpers\TTimeHelper;

use yii\helpers\ArrayHelper;
use common\services\learning\CertificationService;
use common\models\learning\LnUserCertification;
use common\services\learning\TeacherManageService;




class TeacherManageController extends BaseFrontController
{
	public $layout = 'frame';
	
	public function actionIndex()
	{
		$teacherManageService=new TeacherManageService();
		$teacherLevels=$teacherManageService->getTeacherLevels();

		return $this->render('index',['teacherLevels'=>$teacherLevels]);
	}
	
	public function actionViewMain($id)
	{
		return $this->renderAjax('view_main',['id'=>$id,]);
	}
	
	
	public function actionView($id)
	{
		$teacherManageService=new TeacherManageService();
		$teacher=$teacherManageService->findTeacher($id);
		
		if(!$teacher->teacher_thumb_url){
			$thumbUrl = '/static/common/images/man.jpeg';
			if ($teacher->gender == Yii::t('common', 'gender_female')) {
				$thumbUrl = '/static/common/images/woman.jpeg';
			}
			$teacher->teacher_thumb_url = $thumbUrl;
		}

		$teacherLevels=$teacherManageService->getteacherLevels();
		$teacherTypes=$teacherManageService->getTeacherTypes();

		$teacherLevelsSelected=array($teacher['teacher_level']=>'selected="selected"');
		$teacherTypesSelected=array($teacher['teacher_type']=>'selected="selected"');
		
		$uinfo=$teacherManageService->findUserInfo($teacher->user_id);
		
		return $this->renderAjax('view',[
			'teacher'=>$teacher,
			'uinfo'=>$uinfo,
			'teacher'=>$teacher,
			"uinfo"=>$uinfo,
			'teacherLevels'=>$teacherLevels,
			'teacherTypes'=>$teacherTypes,
			'teacherLevelsSelected'=>$teacherLevelsSelected,
			'teacherTypesSelected'=>$teacherTypesSelected
		]);
	}
	
	public function actionCourseInfo($id)
	{
		$teacherManageService=new TeacherManageService();
		$courses=$teacherManageService->findTeacherCourse($id);
		return $this->renderAjax('course_info',['courses'=>$courses,]);
	}
	
	public function actionCourseRate($id)
	{
		$teacherManageService=new TeacherManageService();
		$times=$teacherManageService->getTimeList();
		return $this->renderAjax('course_rate',['id'=>$id,'times'=>$times]);
	}
	
	public function actionCourses($id)
	{
		$teacherManageService=new TeacherManageService();
		
		$time=0;
		if (Yii::$app->request->getQueryParam('time') != null){
			$time=Yii::$app->request->getQueryParam('time');
		}else{
			$times=$teacherManageService->getTimeList();
			$time=$times[0]['YEAR'];
		}
		
		$courses=$teacherManageService->getCourseRate($id,$time);
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $courses];
	}
	
	
	public function actionGetUser($user_name)
	{
		$teacherKid=trim(Yii::$app->request->getQueryParam('tid'));

		$teacherManageService=new TeacherManageService();
		$user=$teacherManageService->getUser($user_name);

		$canBind=$teacherManageService->getInnerTeacherCanBindFromUser($user['user_id'],$teacherKid);

		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $user,'can_bind'=>$canBind];
	}

	public function actionSynUser($user_name)
	{      
        Yii::$app->response->format = Response::FORMAT_JSON;
        
		if(!$user_name){return ['result' => false];}
		$teacherKid=trim(Yii::$app->request->getQueryParam('tid'));

		$user=FwUser::findByUsername($user_name);

		if($user->gender=='male')
		{
			$user->gender=Yii::t('common', 'gender_male');
		}
		else if($user->gender=='female')
		{
			$user->gender=Yii::t('common', 'gender_female');
		}
        
        $teacherManageService=new TeacherManageService();
        $canBind=$teacherManageService->getInnerTeacherCanBindFromUser($user['kid'],$teacherKid); 

		
		return ['result' => $user,'can_bind'=>$canBind];
	}
	
	public function actionNewTeacher()
	{
		$teacherManageService=new TeacherManageService();
		$teacherLevels=$teacherManageService->getteacherLevels();
		$teacherTypes=$teacherManageService->getTeacherTypes();
        $data_range=date('Y-m-d');
		return $this->renderAjax('new_teacher',['teacherLevels'=>$teacherLevels,'teacherTypes'=>$teacherTypes,'data_range'=>$data_range]);
	}
	
	public function actionEditTeacher($id)
	{
		$teacherManageService=new TeacherManageService();
		$teacher=$teacherManageService->findTeacher($id);
		
		if(!$teacher->teacher_thumb_url){
			$thumbUrl = '/static/common/images/man.jpeg';
			if ($teacher->gender == Yii::t('common', 'gender_female')) {
				$thumbUrl = '/static/common/images/woman.jpeg';
			}
			$teacher->teacher_thumb_url = $thumbUrl;
		}
		
		$data_range=date('Y-m-d');
		
		$uinfo=$teacherManageService->findUserInfo($teacher->user_id);

		$teacherLevels=$teacherManageService->getteacherLevels();
		$teacherTypes=$teacherManageService->getTeacherTypes();

		$teacherLevelsSelected=array($teacher['teacher_level']=>'selected="selected"');
		$teacherTypesSelected=array($teacher['teacher_type']=>'selected="selected"');

		return $this->renderAjax('edit_teacher',
		[
			'teacher'=>$teacher,
			'data_range'=>$data_range,	
			"uinfo"=>$uinfo,
			'teacherLevels'=>$teacherLevels,
			'teacherTypes'=>$teacherTypes,
			'teacherLevelsSelected'=>$teacherLevelsSelected,
			'teacherTypesSelected'=>$teacherTypesSelected
		]);
	}
	
	public function actionSaveTeacher()
	{
		$user_id = Yii::$app->user->getId();
		$params=Yii::$app->request->post();

		$teacherManageService=new TeacherManageService();
		$teacherManageService->saveTeacher($params);
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success'];
	}
	
	public function actionUpdateTeacher()
	{
		$user_id = Yii::$app->user->getId();
		$params=Yii::$app->request->post();

		$teacherManageService=new TeacherManageService();

		$teacherManageService->updateTeacher($params);
	
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
		$service = new TeacherManageService();
		$dataProvider = $service->search(Yii::$app->request->queryParams);
		$count = $dataProvider->totalCount;
		$page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
		$dataProvider->setPagination($page);
	
	
		return $this->renderAjax('list', [
				'page' => $page,
				'searchModel' => $service,
				'dataProvider' => $dataProvider,
				'pageSize' => $pageSize
		]);
	}
	
	public function actionDeleteOne(){
	
		$id=Yii::$app->request->getQueryParam('id');
	
		$service = new TeacherManageService();
		if ($service->deleteTeacher($id)) {
		}
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success'];
	
	}
	
	
	/**
	 * @裁剪头像
	 */
	public function actionCutPic()
	{
		if (Yii::$app->request->isAjax) {
			Yii::$app->response->format = Response::FORMAT_JSON;
	
			$yiiBasePath = Yii::$app->basePath . "/..";
			$srcLogicalPath = "/upload/temp/";
			$destPhysicalPath = Yii::$app->basePath . "/../upload/thumb/";
			$destLogicalPath = "/upload/thumb/";
	
			$targ_w = $targ_h = 150;
			$jpeg_quality = 100;
			$src = Yii::$app->request->post('f');
			$src = $yiiBasePath . $src;//真实的图片路径
	
			$type = getimagesize($src)["mime"];
	
			$extension = "jpg";
			switch ($type) {
				case "image/jpg":
					$img_r = imagecreatefromjpeg($src);
					break;
				case "image/jpeg":
					$img_r = imagecreatefromjpeg($src);
					break;
				case "image/pjpeg":
					$img_r = imagecreatefromjpeg($src);
					break;
				case "image/x-png":
					$img_r = imagecreatefrompng($src);
					break;
				case "image/png":
					$img_r = imagecreatefrompng($src);
					break;
				case "image/gif":
					$img_r = imagecreatefromgif($src);
					break;
				case "image/bmp":
					$img_r = $this->ImageCreateFromBMP($src);
					break;
				case "image/x-ms-bmp":
					$img_r = $this->ImageCreateFromBMP($src);
					break;
				case "image/x-bmp":
					$img_r = $this->ImageCreateFromBMP($src);
					break;
				default:
					$img_r = imagecreatefromjpeg($src);
					break;
			}
	
			// $img_r = imagecreatefrompng($src);
			$ext = $destLogicalPath . time() . "." . $extension;//生成的引用路径
			$dst_r = ImageCreateTrueColor($targ_w, $targ_h);
	
			imagecopyresampled($dst_r, $img_r, 0, 0, Yii::$app->request->post('x'), Yii::$app->request->post('y'),
			$targ_w, $targ_h, Yii::$app->request->post('w'), Yii::$app->request->post('h'));
	
			$img = $yiiBasePath . $ext;//真实的图片路径
	
			if (imagejpeg($dst_r, $img, $jpeg_quality)) {
				$id = Yii::$app->user->getId();
				//更新用户头像
				//$user = FwUser::findOne($id);
				//$user->thumb = $ext;
				//$user->save();
				$arr['status'] = 1;
				$arr['data'] = $ext;
				$arr['info'] = Yii::t('common', 'crop_ok');
				//                echo json_encode($arr);
				return $arr;
			} else {
				$arr['status'] = 0;
				//                echo json_encode($arr);
				return $arr;
			}
		}
	}
	
	public function actionClearPic()
	{
		if (Yii::$app->request->isAjax) {
			Yii::$app->response->format = Response::FORMAT_JSON;
			
			$params=Yii::$app->request->post();
			$id = $params['id'];
			
			$service = new TeacherManageService();
			$service->clearPic($id);
			return ['result' => 'success'];
		}
	}

	
}
