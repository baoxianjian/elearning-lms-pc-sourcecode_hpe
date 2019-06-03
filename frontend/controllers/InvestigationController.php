<?php


namespace frontend\controllers;

use Yii;
use frontend\base\BaseFrontController;
use yii\data\Pagination;
use common\models\framework\FwUser;
use yii\filters\AccessControl;
use yii\web\Response;
use components\widgets\TPagination;
use common\helpers\TTimeHelper;
use yii\helpers\ArrayHelper;
use common\services\learning\CourseService;
use common\services\learning\InvestigationService;
use common\services\message\TimelineService;
use common\services\learning\RecordService;
use common\models\message\MsTimeline;
use common\services\framework\PointRuleService;
use common\models\learning\LnRelatedUser;

class InvestigationController extends BaseFrontController
{
	
	public $layout = 'frame';
	
	const LEARNING_DURATION = "30";//太快会影响性能


	public function behaviors()
	{
		$baseBehaviors = parent::behaviors();
		$newBehaviors = [
			'access' => [
				'class' => AccessControl::className(),
				'except' => ['investigation-player','get-single-play-investigation-submit-result','get-vote','get-survey','course-play-survey-result','single-course-play-survey-result','course-play-vote-result',
				'single-course-play-vote-result','get-single-sub-vote-result','single-investigation-submit-result'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
		];

		$finalBehaviors = array_merge($baseBehaviors,$newBehaviors);
		return $finalBehaviors;
	}
	
	
	public function actionPlay()
	{
		
		
		$id = Yii::$app->request->getQueryParam('id');
		$investigationService=new InvestigationService();
		
		//是否推送过
		$params=[];
		$params['user_id']=Yii::$app->user->getId();
		$params['learning_object_id']=$id;
		$flag=$investigationService->getRelUser($params);
		
		if(!$flag){
			return $this->render("/student/index");
		}
		
		$model=$investigationService->getInvest($id);
		
		return $this->render('play', [
				'name'=>$model['title'],
				'investigation_type'=>$model['investigation_type'],
				'id'=>$id,
				'componentCode'=>'investigation',
				'duration' => self::LEARNING_DURATION
		]);
	
	}
	
	public function actionInvestigationPlayer($investigation_id , $investigation_type, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW,$user_id=null)
	{


			$componentCode = "investigation";
			$attempt=1;
			if ($investigation_type == InvestigationService::INVESTIGATION_TYPE_SURVEY) {
				return $this->renderAjax('/investigation/single_course_play_survey', [
						"id" =>$investigation_id,
						'componentCode' => $componentCode,
						'attempt' => $attempt,
						"mode" => $mode,
					"user_id" => $user_id
				]);
			} else {
				return $this->renderAjax('/investigation/single_course_play_vote', [
						"id" => $investigation_id,
						'componentCode' => $componentCode,
						'attempt' => $attempt,
						"mode" => $mode,
					"user_id" => $user_id
				]);
			}
			
		
	}
	
	public function actionIndex()
	{
		return $this->render('index');
	}
	
	public function actionNewvote()
	{
		return $this->renderAjax('new_vote');
	}
	
	public function actionEditVoteUi()
	{
		$id = Yii::$app->request->getQueryParam('id');
		return $this->renderAjax('edit_vote',['id'=>$id]);
	}
	
	public function actionEditSurveyUi()
	{
		$id = Yii::$app->request->getQueryParam('id');
		return $this->renderAjax('edit_survey',['id'=>$id]);
	}
	
	
	public function actionNewsurvey()
	{
		return $this->renderAjax('new_survey');
	}
	
	public function actionAddchoice()
	{
		return $this->renderAjax('addChoice');
	}
	
	
	
	public function actionAddquestion()
	{
		return $this->renderAjax('addQuestion');
	}
	
	public function actionAddEditChoice()
	{
		return $this->renderAjax('editChoice');
	}
	
	public function actionAddEditQuestion()
	{
		return $this->renderAjax('editQuestion');
	}
	
	
	public function actionSurveyPreview()
	{
		$params=Yii::$app->request->post();
		if($params['start_at']){
			$params['start_at']=date("Y年m月d日",strtotime($params['start_at']));
			$params['end_at']=date("Y年m月d日",strtotime($params['end_at']));
		}else{
			$params['start_at']='--';
			$params['end_at']='--';
		}
		
		if($params['answer_type']==0){
			$params['answer_type_show']=Yii::t('frontend', 'preview_real_quest');
		}else{
			$params['answer_type_show']=Yii::t('frontend', 'preview_anony_quest');
		}
		
		return $this->renderAjax('survey_preview',["result"=>$params]);
	}
	
	public function actionVotePreview()
	{
		$params=Yii::$app->request->post();
	
		if($params['start_at']){
			$params['start_at']=date("Y年m月d日",strtotime($params['start_at']));
			$params['end_at']=date("Y年m月d日",strtotime($params['end_at']));
		}else{
			$params['start_at']='--';
			$params['end_at']='--';
		}
		
		if($params['answer_type']==0){
			$params['answer_type_show']=Yii::t('frontend', 'preview_real_vote');
		}else{
			$params['answer_type_show']=Yii::t('frontend', 'preview_anony_vote');
		}
	
	
		return $this->renderAjax('vote_preview',["result"=>$params]);
	}
	
	public function actionPreviewSurvey($id)
	{
		
		return $this->renderAjax('preview_survey',["id"=>$id]);
	}
	
// 	public function actionCoursePlayInvestigation($modResId,$courseId,$courseCompleteFinalId,$courseCompleteProcessId,$courseRegId)
// 	{
// 		$investigationService=new InvestigationService();
		
// 		//$course_reg_id=$investigationService->getCourseRegId($courseId,$courseCompleteProcessId,$modResId);
// 		$investigation=$investigationService->getInvestigationInfoByModResId($modResId);
// 		if($investigation['investigation_type']==InvestigationService::INVESTIGATION_TYPE_SURVEY){
// 			 return $this->renderAjax('course_play_survey',[
// 					"id"=>$investigation['kid'],
// 					"modResId"=>$modResId,
// 					"courseId"=>$courseId,
// 					"courseCompleteFinalId"=>$courseCompleteFinalId,					
// 					"courseCompleteProcessId"=>$courseCompleteProcessId,		
// 					"mod_id"=>$investigation['mod_id'],
// 			 		"course_reg_id"=>$courseRegId,
// 					"courseactivity_id"=>$investigation['courseactivity_id'],
// 					"component_id"=>$investigation['component_id'],
// 			]);
// 		}
		
// 	}
	
	public function actionPreviewVote($id)
	{
		
		return $this->renderAjax('preview_vote',["id"=>$id]);
	}
	
	public function actionDyAddChoice()
	{
		$params=Yii::$app->request->post();
	
		return $this->renderAjax('dy_add_choice',["result"=>$params]);
	}
	
	public function actionDyAddQuestion()
	{
		$params=Yii::$app->request->post();
	
		return $this->renderAjax('dy_add_question',["result"=>$params]);
	}
	
	public function actionDyEditChoice()
	{
		$params=Yii::$app->request->post();
	
		return $this->renderAjax('dy_edit_choice',["result"=>$params]);
	}
	
	public function actionDyEditQuestion()
	{
		$params=Yii::$app->request->post();
	
		return $this->renderAjax('dy_edit_question',["result"=>$params]);
	}
	
	public function actionGetVote()
	{
		$id = Yii::$app->request->getQueryParam('id');
		
		$investigationService=new InvestigationService();
		$v_result=$investigationService->getVote($id);
	
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $v_result];
	}
	
	public function actionGetSingleSubVoteResult()
	{
		$id = Yii::$app->request->getQueryParam('id');
		$params=Yii::$app->request->get();
		$investigationService=new InvestigationService();
		$v_result=$investigationService->getSingleVoteSubResult($id,$params);
	
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $v_result];
	}
	
	public function actionGetSubVoteResult()
	{
		$id = Yii::$app->request->getQueryParam('id');
		$params=Yii::$app->request->get();
		$investigationService=new InvestigationService();
		$v_result=$investigationService->getVoteSubResult($id,$params);
	
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $v_result];
	}
	
	
	public function actionCoursePlaySurveyResult()
	{
		$id = Yii::$app->request->getQueryParam('id');
		$params=Yii::$app->request->get();
		
		$investigationService=new InvestigationService();
		$v_result=$investigationService->getSurveyStResult($id,$params);
		return $this->renderAjax('course_play_survey_result',['results'=>$v_result]);
	}
	
	public function actionSingleCoursePlaySurveyResult()
	{
		$id = Yii::$app->request->getQueryParam('id');
		$params=Yii::$app->request->get();
	
		$investigationService=new InvestigationService();
		$v_result=$investigationService->getSingleSurveyStResult($id,$params);
		return $this->renderAjax('course_play_survey_result',['results'=>$v_result]);
	}
	
	public function actionCoursePlayVoteResult()
	{
		$id = Yii::$app->request->getQueryParam('id');
		$params=Yii::$app->request->get();
		
		$investigationService=new InvestigationService();
		$v_result=$investigationService->getVoteStResult($id,$params);
		
		return $this->renderAjax('course_play_vote_result',['results'=>$v_result]);
	}
	
	public function actionSingleCoursePlayVoteResult()
	{
		$id = Yii::$app->request->getQueryParam('id');
		$params=Yii::$app->request->get();
	
		$investigationService=new InvestigationService();
		$v_result=$investigationService->getSingleVoteStResult($id,$params);
	
		return $this->renderAjax('course_play_vote_result',['results'=>$v_result]);
	}
	
	public function actionGetSubSurveyResult()
	{
		$id = Yii::$app->request->getQueryParam('id');
		$params=Yii::$app->request->get();
		$investigationService=new InvestigationService();
		$v_result=$investigationService->getSurveySubResult($id,$params);
	
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $v_result];
	}
	
	public function actionGetSingleSubSurveyResult()
	{
		$id = Yii::$app->request->getQueryParam('id');
		$params=Yii::$app->request->get();
		$investigationService=new InvestigationService();
		$user_id = Yii::$app->user->getId();
		$v_result=$investigationService->getSingleSurveySubResult($id,$params,$user_id);
	
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $v_result];
	}
	
	public function actionVoteNameValidate()
	{
		$params=Yii::$app->request->get();
		$investigationService=new InvestigationService();
		
		Yii::$app->response->format = Response::FORMAT_JSON;

		$companyId = Yii::$app->user->identity->company_id;
		if($investigationService->nameValidate($params,$companyId)){
			return ['result' => 'yes'];
		}else{
			return ['result' => 'no'];
		}
		
	}
	
	public function actionGetSinglePlayInvestigationSubmitResult()
	{
		$params=Yii::$app->request->get();
		$investigationService=new InvestigationService();
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		if($investigationService->getSinglePlaySurveySubmitResult($params)){
			return ['result' => 'yes'];
		}else{
			return ['result' => 'no'];
		}
	
	}
	
	public function actionGetPlayInvestigationSubmitResult()
	{
		$params=Yii::$app->request->get();
		$investigationService=new InvestigationService();
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		if($investigationService->getPlaySurveySubmitResult($params)){
			return ['result' => 'yes'];
		}else{
			return ['result' => 'no'];
		}
	
	}
	
	public function actionPlayInvestigationResComplete()
	{
		$params=Yii::$app->request->get();
		$courseComplete=false;
		$getCetification=false;
		$courseId=null;
		$certificationId=null;
		$investigationService=new InvestigationService();
		$investigationService->addResCompleteDoneInfo($params,$courseComplete,$getCetification,$courseId,$certificationId);
        
        //edit by baoxianjian 11:27 2016/3/31
        $pointRuleService=new PointRuleService();
        $pointResult=$pointRuleService->countCourseAndCetificationPoint($courseComplete,$getCetification,$courseId,$certificationId);

		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success','pointResult'=>$pointResult];
	
	}
	
	
	
	public function actionGetSurvey()
	{
		$id = Yii::$app->request->getQueryParam('id');
	
		$investigationService=new InvestigationService();
		$s_result=$investigationService->getSurvey($id);
	
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $s_result];
	}
	
	public function actionSaveVote()
	{
		$user_id = Yii::$app->user->getId();
		$params=Yii::$app->request->post();
		
		if($params['start_at']){
			$params['start_at']=strtotime($params['start_at']);
			$end_tmp=strtotime($params['end_at']);
			$params['end_at']=$end_tmp+86399;
		}
		
		
		$investigationService=new InvestigationService(); 
		$investigationService->saveVote($params);
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success'];
	}
	
	public function actionInvestigationSubmitResult()
	{
		$user_id = Yii::$app->user->getId();
		$params=Yii::$app->request->post();
	
		
		$investigationService=new InvestigationService();
		$investigationService->surveySubmitResult($params);
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success'];
	}
	
	public function actionSingleInvestigationSubmitResult()
	{
		$user_id = Yii::$app->user->getId();
		$params=Yii::$app->request->post();
		if(empty($user_id)){
			$user_id = $params['user_id'];
		}

		$investigationService=new InvestigationService();
		$investigationService->singleSurveySubmitResult($params);
		
		$param=$params['param'][0];

		$pointRuleService=new PointRuleService();
		if($params['investigation_type']=='survey'){
			$pointResult = $pointRuleService->curUserCheckActionForPoint('Complete-Questionare','Learning-Portal',$param['investigation_id']);
		}else if($params['investigation_type']=='vote'){
			$pointResult = $pointRuleService->curUserCheckActionForPoint('Complete-Investigation','Learning-Portal',$param['investigation_id']);
		}
		
		
		$timelineService=new TimelineService();
		$timelineService->setComplete($param['investigation_id'],MsTimeline::OBJECT_TYPE_SURVEY,MsTimeline::TIMELINE_TYPE_TODO,$user_id);
		
		$recordService=new RecordService();
		$recordService->addByCompletedSurvey($user_id,$param['investigation_id']);
	

		Yii::$app->response->format = Response::FORMAT_JSON;
		
		return ['result' => 'success', 'pointResult' => $pointResult];
	}
	
	public function actionEditVote()
	{
		$params=Yii::$app->request->post();
	
// 		if($params['start_at']){
// 			$params['start_at']=strtotime($params['start_at']);
// 			$params['end_at']=strtotime($params['end_at']);
// 		}
		
		if($params['start_at']){
			$params['start_at']=strtotime($params['start_at']);
			$end_tmp=strtotime($params['end_at']);
			$params['end_at']=$end_tmp+86399;
		}
		
	
		$investigationService=new InvestigationService();
		$investigationService->editVote($params);
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success'];
	}
	
	public function actionEditSurvey()
	{
		$user_id = Yii::$app->user->getId();
		$params=Yii::$app->request->post();
// 		if($params['start_at']){
// 			$params['start_at']=strtotime($params['start_at']);
// 			$params['end_at']=strtotime($params['end_at']);
// 		}

		if($params['start_at']){
			$params['start_at']=strtotime($params['start_at']);
			$end_tmp=strtotime($params['end_at']);
			$params['end_at']=$end_tmp+86399;
		}
	
		$investigationService=new InvestigationService();
		$investigationService->editSurvey($params);
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success'];
	}
	
	
	public function actionSaveSurvey()
	{
		$user_id = Yii::$app->user->getId();
		$params=Yii::$app->request->post();
// 		if($params['start_at']){
// 			$params['start_at']=strtotime($params['start_at']);
// 			$params['end_at']=strtotime($params['end_at']);
// 		}

		if($params['start_at']){
			$params['start_at']=strtotime($params['start_at']);
			$end_tmp=strtotime($params['end_at']);
			$params['end_at']=$end_tmp+86399;
		}
		
	
		$investigationService=new InvestigationService();
		$investigationService->saveSurvey($params);
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success'];
	}
	
	public function actionList()
	{
	
		$pageSize = $this->defaultPageSize;
	
		if (Yii::$app->request->getQueryParam('PageSize') != null) {
			$pageSize = Yii::$app->request->getQueryParam('PageSize');
		}
	
		$service = new InvestigationService();
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
	
	public function actionPublish(){
		
		$id=Yii::$app->request->getQueryParam('id');
		
		$service = new InvestigationService();
		$service->publish($id);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success'];
	}
	
	public function actionDeleteOne(){
		
		$id=Yii::$app->request->getQueryParam('id');
		
		$service = new InvestigationService();
		$service->deleteLnInvestigation($id);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success'];
		
	}
	
	public function actionCopyInvestigation(){
		$id=Yii::$app->request->getQueryParam('id');
		
		$service = new InvestigationService();
		$service->copyInvestigations($id);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success'];
	}
	
	
	
}