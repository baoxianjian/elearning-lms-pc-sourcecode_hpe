<?php

namespace frontend\controllers;

use Yii;
use frontend\base\BaseFrontController;
use yii\data\Pagination;
use common\models\framework\FwUser;
use yii\web\Response;
use components\widgets\TPagination;
use common\helpers\TTimeHelper;
use yii\helpers\ArrayHelper;
use common\services\learning\CourseService;
use common\services\learning\InvestigationService;
use common\services\learning\InvestigationResultService;
use common\models\learning\LnModRes;
use common\models\learning\LnRelatedUser;

class InvestigationResultController extends BaseFrontController
{
	public $layout = 'frame';
	const SIZE =10;
	
	const INVESTIGATION="investigation";
	const COURSE_CONST="course";
	
	public function actionOnlineCourseInvestigationDetail()
	{
	
		$id = Yii::$app->request->getQueryParam('modId');
		
		//$params=Yii::$app->request->get();
		$investigationResultService=new InvestigationResultService();
		$v_result=$investigationResultService->getOnLineInvestigation($id);
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $v_result];
	
	}
	
	
	public function actionCourseSurveyManageResultVote()
	{
	
		$id = Yii::$app->request->getQueryParam('id');
		$params=Yii::$app->request->get();
	
		$investigationService=new InvestigationService();
		$v_result_1=$investigationService->getVoteStResult($id,$params);
		$v_result=$this->getSelectUser($v_result_1, $params['course_id'],InvestigationResultController::COURSE_CONST);
		
		$type=$params['course_type'];
	
		if($v_result['answer_type']==Yii::t('frontend', 'vote_real_name')){
			return $this->render('survey_manage_result_vote_sm',
					['results'=>$v_result,'is_course'=>'yes',
					  "id"=>$params['course_id'],'iid'=>$id,
					  'course_type'=>$type,	
					]);
		}else{
			return $this->render('survey_manage_result_vote_nm',
					['results'=>$v_result,'is_course'=>'yes',
					 "id"=>$params['course_id'],'iid'=>$id,
					 'course_type'=>$type,
					]);
		}
	
	}
	
	public function actionCourseSurveyManageResultSurvey()
	{
	
		$id = Yii::$app->request->getQueryParam('id');
		$params=Yii::$app->request->get();
	
		$params['is_content_flag']="true";
		$investigationService=new InvestigationService();
		
		$type=$params['course_type'];
		
		$v_result_1=$investigationService->getSurveyStResult($id,$params);
		$v_result=$this->getSelectUser($v_result_1, $params['course_id'],InvestigationResultController::COURSE_CONST);
	
		if($v_result['answer_type']==Yii::t('frontend', 'v_real_quest')){
			return $this->render('survey_manage_result_survey_sm',
					['results'=>$v_result,'is_course'=>'yes',
					 "id"=>$params['course_id'],'iid'=>$id,
					 'course_type'=>$type,
					]);
		}else{
			return $this->render('survey_manage_result_survey_nm',
					['results'=>$v_result,'is_course'=>'yes',
					 "id"=>$params['course_id'],'iid'=>$id,
					 'course_type'=>$type,
					]);
		}
	
	}
	
	public function actionSurveyManageResultVote()
	{
		
		$id = Yii::$app->request->getQueryParam('id');
		$params=Yii::$app->request->get();
		$params['is_manag']=true;
		
		$investigationService=new InvestigationService();
		$v_result_1=$investigationService->getSingleVoteStResult($id,$params);
		
		$v_result=$this->getSelectUser($v_result_1, $id,InvestigationResultController::INVESTIGATION);
		
		if($v_result['answer_type']==Yii::t('frontend', 'vote_real_name')){
			return $this->render('survey_manage_result_vote_sm',['results'=>$v_result,'iid'=>$id]);
		}else{
			return $this->render('survey_manage_result_vote_nm',['results'=>$v_result,'iid'=>$id]);
		}
		
	}
	
	public function actionSurveyManageResultSurvey()
	{
	
		$id = Yii::$app->request->getQueryParam('id');
		$params=Yii::$app->request->get();
	
		$params['is_content_flag']="true";
		$investigationService=new InvestigationService();
		$v_result_1=$investigationService->getSingleSurveyStResult($id,$params);
	
		$v_result=$this->getSelectUser($v_result_1, $id,InvestigationResultController::INVESTIGATION);
	
		if($v_result['answer_type']==Yii::t('frontend', 'v_real_quest')){
			return $this->render('survey_manage_result_survey_sm',['results'=>$v_result,'iid'=>$id]);
		}else{
			return $this->render('survey_manage_result_survey_nm',['results'=>$v_result,'iid'=>$id]);
		}
	
	}
	
	public function actionVSmList()
	{
		$this->layout = 'list';
		$id = Yii::$app->request->getQueryParam('iid');
		
		$rel_users_type = Yii::$app->request->getQueryParam('rel_users_type');
		$all_user_count_is_0 = Yii::$app->request->getQueryParam('all_user_count_is_0');
		
		$params=Yii::$app->request->get();
	
		$params['size']=InvestigationResultController::SIZE;
		$params['rel_users_type']=$rel_users_type;
		$params['all_user_count_is_0']=$all_user_count_is_0;
		
		$investigationResultService=new InvestigationResultService();
		if(!Yii::$app->request->getQueryParam('course_id')){		
			$pinfolist=$investigationResultService->getSingleVoteStUserInfoResult($id,$params);
		}else{
			$course_id = Yii::$app->request->getQueryParam('course_id');
			$params['course_id']=$course_id;
			$pinfolist=$investigationResultService->getCourseVoteStUserInfoResult($id,$params);
		}
		
		return $this->render('v_sm_list', $pinfolist);
	}
	
	public function actionVNmList()
	{
		$this->layout = 'list';
		$id = Yii::$app->request->getQueryParam('iid');
		
		$rel_users_type = Yii::$app->request->getQueryParam('rel_users_type');
		$all_user_count_is_0 = Yii::$app->request->getQueryParam('all_user_count_is_0');
	
		$params['size']=InvestigationResultController::SIZE;
		$params['rel_users_type']=$rel_users_type;
		$params['all_user_count_is_0']=$all_user_count_is_0;
		
		$investigationResultService=new InvestigationResultService();
		if(!Yii::$app->request->getQueryParam('course_id')){		
			$pinfolist=$investigationResultService->getSingleVoteStUserInfoResult($id,$params);
		}else{
			$course_id = Yii::$app->request->getQueryParam('course_id');
			$params['course_id']=$course_id;
			$pinfolist=$investigationResultService->getCourseVoteStUserInfoResult($id,$params);
		}
	
	
		return $this->render('v_nm_list', $pinfolist);
	}
	
	public function actionSSmList()
	{
		$this->layout = 'list';
		$id = Yii::$app->request->getQueryParam('iid');
		
		$params=Yii::$app->request->get();
	
		$rel_users_type = Yii::$app->request->getQueryParam('rel_users_type');
		$all_user_count_is_0 = Yii::$app->request->getQueryParam('all_user_count_is_0');
	
		$params['size']=InvestigationResultController::SIZE;
		$params['rel_users_type']=$rel_users_type;
		$params['all_user_count_is_0']=$all_user_count_is_0;
		
		$investigationResultService=new InvestigationResultService();
		if(!Yii::$app->request->getQueryParam('course_id')){		
			$pinfolist=$investigationResultService->getSingleVoteStUserInfoResult($id,$params);
		}else{
			$course_id = Yii::$app->request->getQueryParam('course_id');
			$params['course_id']=$course_id;
			$pinfolist=$investigationResultService->getCourseVoteStUserInfoResult($id,$params);
		}
	
	
		return $this->render('s_sm_list', $pinfolist);
	}
	
	public function actionSNmList()
	{
		$this->layout = 'list';
		$id = Yii::$app->request->getQueryParam('iid');
	
		$rel_users_type = Yii::$app->request->getQueryParam('rel_users_type');
		$all_user_count_is_0 = Yii::$app->request->getQueryParam('all_user_count_is_0');
	
		$params['size']=InvestigationResultController::SIZE;
		$params['rel_users_type']=$rel_users_type;
		$params['all_user_count_is_0']=$all_user_count_is_0;
		
		$investigationResultService=new InvestigationResultService();
		if(!Yii::$app->request->getQueryParam('course_id')){		
			$pinfolist=$investigationResultService->getSingleVoteStUserInfoResult($id,$params);
		}else{
			$course_id = Yii::$app->request->getQueryParam('course_id');
			$params['course_id']=$course_id;
			$pinfolist=$investigationResultService->getCourseVoteStUserInfoResult($id,$params);
		}
	
	
		return $this->render('s_nm_list', $pinfolist);
	}
	
	
	
	
	public function actionStSurvey()
	{
		$investigation_id = Yii::$app->request->getQueryParam('investigation_id');
		$user_id = Yii::$app->request->getQueryParam('user_id');
		
		return $this->renderAjax('st_survey',['id'=>$investigation_id,'user_id'=>$user_id]);
	}
	
	public function actionStCourseSurvey()
	{
		$investigation_id = Yii::$app->request->getQueryParam('investigation_id');
		$user_id = Yii::$app->request->getQueryParam('user_id');
		$course_id = Yii::$app->request->getQueryParam('course_id');
		return $this->renderAjax('st_course_survey',['id'=>$investigation_id,'user_id'=>$user_id,'course_id'=>$course_id]);
	}
	
	public function actionGetSingleSubSurveyResult()
	{
		$id = Yii::$app->request->getQueryParam('id');
		$params=Yii::$app->request->get();
		$investigationService=new InvestigationService();
		$user_id = Yii::$app->request->getQueryParam('user_id');
		$v_result=$investigationService->getSingleSurveySubResult($id,$params,$user_id);
	
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $v_result];
	}
	
	public function actionGetCourseSubSurveyResult()
	{
		$id = Yii::$app->request->getQueryParam('id');
		$params=Yii::$app->request->get();
		$investigationService=new InvestigationService();
		$user_id = Yii::$app->request->getQueryParam('user_id');
		$v_result=$investigationService->getSurveySubResult($id,$params,$user_id);
	
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $v_result];
	}
	
	
	public function getSelectUser($v_result,$id,$type){
		
		$investigationResultService=new InvestigationResultService();
		$company_id=Yii::$app->user->identity->company_id;
		$all_user_count=$investigationResultService->getAllUsersCount($company_id,$type,
				$id);
		
		if($all_user_count==0){
			$v_result['all_user_count_is_0']="true";
		}
		
		$no_submit_user=$all_user_count-$v_result['sub_result_arr_num'];
		$v_result['all_user_count']=$all_user_count;
		$v_result['no_submit_user']=$no_submit_user;
		return $v_result;
	}
	
	
	
	
}