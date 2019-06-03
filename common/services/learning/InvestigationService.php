<?php


namespace common\services\learning;

use common\models\framework\FwUserDisplayInfo;
use common\models\learning\LnCourseReg;
use components\widgets\TPagination;
use Yii;
use common\models\learning\LnInvestigation;
use common\models\learning\LnInvestigationQuestion;
use common\models\learning\LnInvestigationOption;
use common\models\learning\LnHomework;
use common\models\learning\LnHomeworkFile;
use common\models\learning\LnHomeworkResult;

use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\db\Query;
use common\models\learning\LnModRes;
use common\models\learning\LnCourseactivity;
use common\models\learning\LnInvestigationResult;
use common\models\learning\LnResComplete;
use common\models\framework\FwUser;
use common\models\framework\FwOrgnization;
use common\models\framework\FwPosition;
use common\models\framework\FwUserPosition;
use common\base\BaseActiveRecord;
use common\models\learning\LnCourse;
use yii\helpers\Html;
use common\models\learning\LnRelatedUser;



class InvestigationService extends LnInvestigation
{
//	const INVESTIGATION_TYPE_VOTE = '1';
//	const INVESTIGATION_TYPE_SURVEY = '0';
	const STATUS_FORMAL = '1';
	const STATUS_TEMP = '0';

	const QUESTION_TYPE_WENDA = "2";
	
	
	

	public function getInvest($id)
	{
		$lnInvestigation = LnInvestigation::find(false)
			->andFilterWhere(["=", "kid", $id])
			->asArray()
			->one();
		return $lnInvestigation;
	}

	public function getCourseType($course_id)
	{
		$result = LnCourse::find(false)
			->andFilterWhere(['=', 'kid', $course_id])
			->asArray()
			->all();

		return $result[0]['course_type'];
	}


//	public function getCourseRegId($course_id,$course_complete_id,$mod_res_id){
//		$results = LnResComplete::find(false)
//			->andFilterWhere(['=','course_id',$course_id])
//			->andFilterWhere(['=','course_complete_id',$course_complete_id])
//			->andFilterWhere(['=','mod_res_id',$mod_res_id])
//			->asArray()
//			->all();
//
//		$result=$results[0];
//
//		return $result['course_reg_id'];
//	}


	public function addResCompleteDoneInfo($param,&$courseComplete=false,&$getCetification=false,&$courseId=null,&$certificationId=null){
		$resourceCompleteService = new ResourceCompleteService();
		$resourceCompleteService->addResCompleteDoneInfo($param['course_complete_id'], $param['course_reg_id'], $param['mod_res_id'], $param['complete_type'], null, null, false, $this->systemKey,true,false,$courseComplete,$getCetification,$courseId,$certificationId);
	}


	public function getInvestigationInfoByModResId($modResId)
	{

		$lnModRes = LnModRes::findOne($modResId);
		$lnCourseactivity = LnCourseactivity::findOne($lnModRes->courseactivity_id);
		$investigation_id = $lnCourseactivity->object_id;

		$lnInvestigation = LnInvestigation::findOne($investigation_id);
		$investigationInfo = [];
		$investigationInfo['kid'] = $lnInvestigation->kid;
		$investigationInfo['mod_id'] = $lnModRes->mod_id;
		$investigationInfo['courseactivity_id'] = $lnModRes->courseactivity_id;
		$investigationInfo['component_id'] = $lnModRes->component_id;
		$investigationInfo['investigation_type'] = $lnInvestigation->investigation_type;

		return $investigationInfo;

	}

	public function getHomeworkInfoByModResId($modResId)
	{

		$lnModRes = LnModRes::findOne($modResId);
		$lnCourseactivity = LnCourseactivity::findOne($lnModRes->courseactivity_id);
		$homework_id = $lnCourseactivity->object_id;

		$lnHomework = LnHomework::findOne($homework_id);
		$homeworkInfo = $lnHomework->attributes;
		$homeworkInfo['kid'] = $lnHomework->kid;
		$homeworkInfo['mod_id'] = $lnModRes->mod_id;
		$homeworkInfo['courseactivity_id'] = $lnModRes->courseactivity_id;
		$homeworkInfo['component_id'] = $lnModRes->component_id;

		return $homeworkInfo;

	}

	public function nameValidate($params, $companyId)
	{
		$investigationCount = LnInvestigation::find(false)
			->andFilterWhere(["=", "title", $params['name']])
			->andFilterWhere(["=", "investigation_type", $params['investigation_type']])
			->andFilterWhere(["=", "company_id", $companyId])
			->count();
		if ($investigationCount > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getRelUser($params){
		$cacheKey = 'get-rel-user-'.$params['learning_object_id']."-".$params['user_id'];	
		$result =Yii::$app->cache->get($cacheKey);
		if($result){	
			return true;	
		}else{
			$result=LnRelatedUser::find(false)
				->andFilterWhere(["=", "learning_object_id", $params['learning_object_id']])
				->andFilterWhere(["=", "user_id", $params['user_id']])
				->andFilterWhere(["=", "learning_object_type", 'investigation'])
				->asArray()
				->all();
			
			Yii::$app->cache->set($cacheKey, $result);
			if($result){			
				return true;
			}else{
				return false;
			}
		}
		
	
		
	}

	public function getSinglePlaySurveySubmitResult($params, $userId = null)
	{
		$user_id = Yii::$app->user->getId();
		if (empty($user_id)) {
			$user_id = $userId;
		}
		
		
		$cacheKey = 'get-single-play-survey-submit-result-'.$params['investigation_id']."-".$user_id;
		
		$investigationResultCount =Yii::$app->cache->get($cacheKey);
		
		if($investigationResultCount){
			return true;
		}else{
			$investigationResultCount = LnInvestigationResult::find(false)
				->andFilterWhere(["=", "investigation_id", $params['investigation_id']])
				->andFilterWhere(["=", "user_id", $user_id])
				->asArray()
				->all();
			
			Yii::$app->cache->set($cacheKey, $investigationResultCount);
			if($investigationResultCount){
				return true;
			}else{
				return false;
			}
		}
		
	}

	public function getPlaySurveySubmitResult($params, $userId = null)
	{
		$user_id = Yii::$app->user->getId();
		if (empty($user_id)) {
			$user_id = $userId;
		}

		$md= LnInvestigationResult::find(false);
		$investigationResultCount =$md
			->andFilterWhere(["=", "investigation_id", $params['investigation_id']])
			->andFilterWhere(["=", "course_id", $params['course_id']])
			->andFilterWhere(["=", "mod_id", $params['mod_id']])
			->andFilterWhere(["=", "user_id", $user_id])
			->andFilterWhere(["=", "course_reg_id", $params['course_reg_id']])
			->andFilterWhere(["=", "course_complete_id", $params['course_complete_id']])
			->andFilterWhere(["=", "attempt", $params['attempt']])
			->count();


		if ($investigationResultCount > 0) {
			return true;
		} else {
			return false;
		}
	}


	public function getSurveySubResult($id, $params, $userId = null)
	{
		$user_id = Yii::$app->user->getId();
		if (empty($user_id)) {
			$user_id = $userId;
		}
		
		$cacheInvestigationKey = 'get-survey-sub-result-investigation'.$id;
		$lnInvestigation =[];
		$lnInvestigation = Yii::$app->cache->get($cacheInvestigationKey);
		if(!$lnInvestigation){
			$lnInvestigation =LnInvestigation::find(false)
				->andFilterWhere(["=", "kid", $id])
				->asArray()
				->one();
			Yii::$app->cache->set($cacheInvestigationKey, $lnInvestigation);
		}
		

		$investigationQuestionArr =[];
		$cacheQuestionKey = 'get-survey-sub-result-question'.$id;
		$investigationQuestionArr = Yii::$app->cache->get($cacheQuestionKey);
		if(!$investigationQuestionArr){
			$investigationQuestionArr=LnInvestigationQuestion::find(false)
					->andFilterWhere(["=", "investigation_id", $id])
					->orderBy("sequence_number asc")
					->asArray()
					->all();
			Yii::$app->cache->set($cacheQuestionKey, $investigationQuestionArr);
		}
		

		$survey_result = [];
		if ($lnInvestigation['start_at']) {
			$survey_result['start_at'] = date("Y-m-d", $lnInvestigation['start_at']);
			$survey_result['end_at'] = date("Y-m-d", $lnInvestigation['end_at']);
		} else {
			$survey_result['start_at'] = $lnInvestigation['start_at'];
			$survey_result['end_at'] = $lnInvestigation['end_at'];
		}

		$survey_result['answer_type'] = $lnInvestigation['answer_type'];
		$survey_result['investigation_range'] = $lnInvestigation['investigation_range'];
		$survey_result['id'] = $lnInvestigation['kid'];

		$survey_result['description'] = $lnInvestigation['description'];
		$survey_result['title'] = $lnInvestigation['title'];
		$survey_result['question'] = [];

		foreach ($investigationQuestionArr as $investigationQuestion) {
			$question_type = $investigationQuestion['question_type'];
			if ($question_type == LnInvestigationQuestion::QUESTION_TYPE_QA) {

				$sub_result_arr = LnInvestigationResult::find(false)
					->andFilterWhere(["=", "investigation_id", $params['investigation_id']])
					->andFilterWhere(["=", "course_id", $params['course_id']])
					->andFilterWhere(["=", "mod_id", $params['mod_id']])
					->andFilterWhere(["=", "user_id", $user_id])
					->andFilterWhere(["=", "course_reg_id", $params['course_reg_id']])
					->andFilterWhere(["=", "investigation_question_id", $investigationQuestion['kid']])
					->asArray()
					->all();

				$s_question_obj = [];
				$s_question_obj['question_title'] = $investigationQuestion['question_title'];
				$s_question_obj['question_description'] = $investigationQuestion['question_description'];
				$s_question_obj['question_type'] = $investigationQuestion['question_type'];
				$s_question_obj['id'] = $investigationQuestion['kid'];
				$s_question_obj['option_result'] = $sub_result_arr[0]['option_result'];
				array_push($survey_result['question'], $s_question_obj);

			} elseif ($question_type == LnInvestigationQuestion::QUESTION_TYPE_PAGE_SPLIT) {

				$s_pagination_obj = [];
				$s_pagination_obj['question_title'] = $investigationQuestion['question_title'];
				$s_pagination_obj['question_type'] = $investigationQuestion['question_type'];
				$s_pagination_obj['id'] = $investigationQuestion['kid'];

				array_push($survey_result['question'], $s_pagination_obj);
			} else {
				$sub_result_arr = LnInvestigationResult::find(false)
					->andFilterWhere(["=", "investigation_id", $params['investigation_id']])
					->andFilterWhere(["=", "course_id", $params['course_id']])
					->andFilterWhere(["=", "mod_id", $params['mod_id']])
					->andFilterWhere(["=", "user_id", $user_id])
					->andFilterWhere(["=", "course_reg_id", $params['course_reg_id']])
					->andFilterWhere(["=", "investigation_question_id", $investigationQuestion['kid']])
					->asArray()
					->all();


				$s_choice_obj = [];
				$s_choice_obj['question_title'] = $investigationQuestion['question_title'];
				$s_choice_obj['question_type'] = $investigationQuestion['question_type'];
				$s_choice_obj['id'] = $investigationQuestion['kid'];
				$s_choice_obj['options'] = [];

				
				$cacheInvestigationOptionKey = 'get-survey-sub-result-investigation-option'.$id."-".$investigationQuestion['kid'];
				$lnInvestigationOptionArr=null;
				$lnInvestigationOptionArr = Yii::$app->cache->get($cacheInvestigationOptionKey);
				if(!$lnInvestigationOptionArr){
					$lnInvestigationOptionArr = LnInvestigationOption::find(false)
							->andFilterWhere(["=", "investigation_id", $id])
							->andFilterWhere(["=", "investigation_question_id", $investigationQuestion['kid']])
							->orderBy("sequence_number asc")
							->all();
					Yii::$app->cache->set($cacheInvestigationOptionKey, $lnInvestigationOptionArr);
				}
				

				foreach ($lnInvestigationOptionArr as $investigationOption) {

					$choice_option_obj = [];
					$choice_option_obj['option_title'] = $investigationOption['option_title'];
					$choice_option_obj['sequence_number'] = $investigationOption['sequence_number'];
					$choice_option_obj['kid'] = $investigationOption['kid'];

					$lnInvestigationOption_id = $investigationOption['kid'];
					for ($i = 0; $i < count($sub_result_arr); $i++) {
						$sub_result = $sub_result_arr[$i];
						if ($lnInvestigationOption_id == $sub_result['investigation_option_id']) {
							$choice_option_obj['isCheck'] = 'checked="checked"';
						}
					}

					array_push($s_choice_obj['options'], $choice_option_obj);
				}

				array_push($survey_result['question'], $s_choice_obj);
			}
		}

		//$survey_result['investigation_question_id']=$investigationQuestionTmp->kid;

		return $survey_result;
	}

	public function getSingleSurveySubResult($id, $params, $user_id)
	{

		//问卷主体
		$lnInvestigation = LnInvestigation::find(false)
			->andFilterWhere(["=", "kid", $id])
			->asArray()
			->one();

		//题目
		$investigationQuestionArr = LnInvestigationQuestion::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->orderBy("sequence_number asc")
			->asArray()
			->all();

		$survey_result = [];
		if ($lnInvestigation['start_at']) {
			$survey_result['start_at'] = date("Y-m-d", $lnInvestigation['start_at']);
			$survey_result['end_at'] = date("Y-m-d", $lnInvestigation['end_at']);
		} else {
			$survey_result['start_at'] = $lnInvestigation['start_at'];
			$survey_result['end_at'] = $lnInvestigation['end_at'];
		}

		$survey_result['answer_type'] = $lnInvestigation['answer_type'];
		$survey_result['investigation_range'] = $lnInvestigation['investigation_range'];
		$survey_result['id'] = $lnInvestigation['kid'];

		$survey_result['description'] = $lnInvestigation['description'];
		$survey_result['title'] = $lnInvestigation['title'];
		$survey_result['question'] = [];

		foreach ($investigationQuestionArr as $investigationQuestion) {
			$question_type = $investigationQuestion['question_type'];
			if ($question_type == LnInvestigationQuestion::QUESTION_TYPE_QA) {

				$sub_result_arr = LnInvestigationResult::find(false)
					->andFilterWhere(["=", "investigation_id", $params['investigation_id']])
					->andFilterWhere(["=", "user_id", $user_id])
					->andFilterWhere(["=", "investigation_question_id", $investigationQuestion['kid']])
					->asArray()
					->all();

				$s_question_obj = [];
				$s_question_obj['question_title'] = $investigationQuestion['question_title'];
				$s_question_obj['question_description'] = $investigationQuestion['question_description'];
				$s_question_obj['question_type'] = $investigationQuestion['question_type'];
				$s_question_obj['id'] = $investigationQuestion['kid'];
				$s_question_obj['option_result'] = $sub_result_arr[0]['option_result'];
				array_push($survey_result['question'], $s_question_obj);

			} elseif ($question_type == LnInvestigationQuestion::QUESTION_TYPE_PAGE_SPLIT) {

				$s_pagination_obj = [];
				$s_pagination_obj['question_title'] = $investigationQuestion['question_title'];
				$s_pagination_obj['question_type'] = $investigationQuestion['question_type'];
				$s_pagination_obj['id'] = $investigationQuestion['kid'];

				array_push($survey_result['question'], $s_pagination_obj);
			} else {
				$sub_result_arr = LnInvestigationResult::find(false)
					->andFilterWhere(["=", "investigation_id", $params['investigation_id']])
					->andFilterWhere(["=", "user_id", $user_id])
					->andFilterWhere(["=", "investigation_question_id", $investigationQuestion['kid']])
					->asArray()
					->all();


				$s_choice_obj = [];
				$s_choice_obj['question_title'] = $investigationQuestion['question_title'];
				$s_choice_obj['question_type'] = $investigationQuestion['question_type'];
				$s_choice_obj['id'] = $investigationQuestion['kid'];
				$s_choice_obj['options'] = [];

				$lnInvestigationOptionArr = LnInvestigationOption::find(false)
					->andFilterWhere(["=", "investigation_id", $id])
					->andFilterWhere(["=", "investigation_question_id", $investigationQuestion['kid']])
					->orderBy("sequence_number asc")
					->all();

				foreach ($lnInvestigationOptionArr as $investigationOption) {

					$choice_option_obj = [];
					$choice_option_obj['option_title'] = $investigationOption['option_title'];
					$choice_option_obj['sequence_number'] = $investigationOption['sequence_number'];
					$choice_option_obj['kid'] = $investigationOption['kid'];

					$lnInvestigationOption_id = $investigationOption['kid'];
					for ($i = 0; $i < count($sub_result_arr); $i++) {
						$sub_result = $sub_result_arr[$i];
						if ($lnInvestigationOption_id == $sub_result['investigation_option_id']) {
							$choice_option_obj['isCheck'] = 'checked="checked"';
						}
					}

					array_push($s_choice_obj['options'], $choice_option_obj);
				}

				array_push($survey_result['question'], $s_choice_obj);
			}
		}

		//$survey_result['investigation_question_id']=$investigationQuestionTmp->kid;

		return $survey_result;
	}


	public function getVoteSubResult($id, $params)
	{
		$lnInvestigation = LnInvestigation::findOne($id);
		
		$cacheQuestionKey = 'get-vote-sub-result-question'.$id;
		$investigationQuestionArr=null;
		$investigationQuestionArr = Yii::$app->cache->get($cacheQuestionKey);
		if(!$investigationQuestionArr){
			$investigationQuestionArr = LnInvestigationQuestion::find(false)
				->andFilterWhere(["=", "investigation_id", $id])
				->all();
			Yii::$app->cache->set($cacheQuestionKey, $investigationQuestionArr);
		}
		
		$user_id = Yii::$app->user->getId();
		//
		$sub_result_arr = LnInvestigationResult::find(false)
			->andFilterWhere(["=", "investigation_id", $params['investigation_id']])
			->andFilterWhere(["=", "course_id", $params['course_id']])
			->andFilterWhere(["=", "mod_id", $params['mod_id']])
			->andFilterWhere(["=", "user_id", $user_id])
			->andFilterWhere(["=", "course_reg_id", $params['course_reg_id']])
			->asArray()
			->all();

		foreach ($investigationQuestionArr as $investigationQuestion) {

//			$question_type=$investigationQuestion->question_type;

			$cacheInvestigationOptionKey = 'get-vote-sub-result-investigation-option-'.$id."-".$investigationQuestion->kid;
			$lnInvestigationOptionArr=[];
			$lnInvestigationOptionArr = Yii::$app->cache->get($cacheInvestigationOptionKey);
			if(!$lnInvestigationOptionArr){
				$lnInvestigationOptionArr = LnInvestigationOption::find(false)
					->andFilterWhere(["=", "investigation_id", $id])
					->andFilterWhere(["=", "investigation_question_id", $investigationQuestion->kid])
					->orderBy("sequence_number asc")
					->asArray()
					->all();
				Yii::$app->cache->set($cacheInvestigationOptionKey, $lnInvestigationOptionArr);
			}
			

			$lnInvestigationOptionArrTmp = [];

			foreach ($lnInvestigationOptionArr as $lnInvestigationOption) {
				$lnInvestigationOption_id = $lnInvestigationOption['kid'];
				for ($i = 0; $i < count($sub_result_arr); $i++) {
					$sub_result = $sub_result_arr[$i];
					if ($lnInvestigationOption_id == $sub_result['investigation_option_id']) {
						$lnInvestigationOption['isCheck'] = 'checked="checked"';
					}


				}
				array_push($lnInvestigationOptionArrTmp, $lnInvestigationOption);
			};
		}

		$investigationQuestionTmp = $investigationQuestionArr[0];
		$vote_result = [];
		if ($lnInvestigation->start_at) {
			$vote_result['start_at'] = date("Y-m-d", $lnInvestigation->start_at);
			$vote_result['end_at'] = date("Y-m-d", $lnInvestigation->end_at);
		} else {
			$vote_result['start_at'] = $lnInvestigation->start_at;
			$vote_result['end_at'] = $lnInvestigation->end_at;
		}

		$vote_result['answer_type'] = $lnInvestigation->answer_type;
		$vote_result['question_title'] = $investigationQuestionTmp->question_title;
		$vote_result['question_type'] = $investigationQuestionTmp->question_type;
		$vote_result['id'] = $id;

		$vote_result['investigation_range'] = $lnInvestigation->investigation_range;

		$vote_result['investigation_question_id'] = $investigationQuestionTmp->kid;
		$vote_result['options'] = $lnInvestigationOptionArrTmp;
		return $vote_result;

	}

	public function getSingleVoteSubResult($id, $params, $userId = null)
	{
		$lnInvestigation = LnInvestigation::findOne($id);
		$investigationQuestionArr = LnInvestigationQuestion::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->all();
		$user_id = Yii::$app->user->getId();
		if (empty($user_id)) {
			$user_id = $userId;
		}
		//
		$sub_result_arr = LnInvestigationResult::find(false)
			->andFilterWhere(["=", "investigation_id", $params['investigation_id']])
			->andFilterWhere(["=", "user_id", $user_id])
			->asArray()
			->all();

		foreach ($investigationQuestionArr as $investigationQuestion) {

			//			$question_type=$investigationQuestion->question_type;
			$lnInvestigationOptionArr = LnInvestigationOption::find(false)
				->andFilterWhere(["=", "investigation_id", $id])
				->andFilterWhere(["=", "investigation_question_id", $investigationQuestion->kid])
				->orderBy("sequence_number asc")
				->asArray()
				->all();

			$lnInvestigationOptionArrTmp = [];

			foreach ($lnInvestigationOptionArr as $lnInvestigationOption) {
				$lnInvestigationOption_id = $lnInvestigationOption['kid'];
				for ($i = 0; $i < count($sub_result_arr); $i++) {
					$sub_result = $sub_result_arr[$i];
					if ($lnInvestigationOption_id == $sub_result['investigation_option_id']) {
						$lnInvestigationOption['isCheck'] = 'checked="checked"';
					}


				}
				array_push($lnInvestigationOptionArrTmp, $lnInvestigationOption);
			};
		}

		$investigationQuestionTmp = $investigationQuestionArr[0];
		$vote_result = [];
		if ($lnInvestigation->start_at) {
			$vote_result['start_at'] = date("Y-m-d", $lnInvestigation->start_at);
			$vote_result['end_at'] = date("Y-m-d", $lnInvestigation->end_at);
		} else {
			$vote_result['start_at'] = $lnInvestigation->start_at;
			$vote_result['end_at'] = $lnInvestigation->end_at;
		}

		$vote_result['answer_type'] = $lnInvestigation->answer_type;
		$vote_result['question_title'] = $investigationQuestionTmp->question_title;
		$vote_result['question_type'] = $investigationQuestionTmp->question_type;
		$vote_result['id'] = $id;

		$vote_result['investigation_range'] = $lnInvestigation->investigation_range;

		$vote_result['investigation_question_id'] = $investigationQuestionTmp->kid;
		$vote_result['options'] = $lnInvestigationOptionArrTmp;
		return $vote_result;

	}
	
	public function getSingleSurveyStResult($id, $params){
		$cacheKey = 'get-single-survey-st-result-'.$id;
		//测试用
		//return $this->getSingleSurveyStResultByDb($id, $params);
		//学习管理员查询统计结果，直接从数据库中去
		if (isset($params['is_content_flag'])){
			return $this->getSingleSurveyStResultByDb($id, $params);
		}else{
			//学员查询统计结果，从缓存中取,因为java存的json串，
			//$survey_result =Yii::$app->redis->get($cacheKey);
			$survey_result =Yii::$app->cache->getCache($cacheKey);
			
			//return [];
			$survey_result=json_decode($survey_result,true);
		
			if($survey_result){
				return $survey_result;
			}else {
				return $this->getSingleSurveyStResultByDb($id, $params);
			}
		}
	}
	

	/**
	 * 从数据库中取问卷统计结果
	 * @param unknown $id
	 * @param unknown $params
	 * @return multitype:multitype: string unknown NULL
	 */
	public function getSingleSurveyStResultByDb($id, $params)
	{
		$lnInvestigation = LnInvestigation::find(false)
			->andFilterWhere(["=", "kid", $id])
			->asArray()
			->one();

		$investigationQuestionArr = LnInvestigationQuestion::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->orderBy("sequence_number asc")
			->asArray()
			->all();

		$survey_result = [];

		if ($lnInvestigation['answer_type'] == LnInvestigation::ANSWER_TYPE_REALNAME) {
			$survey_result['answer_type'] = '实名问卷';
		} else {
			$survey_result['answer_type'] = '匿名问卷';
		}

		$survey_result['investigation_range'] = $lnInvestigation['investigation_range'];
		$survey_result['id'] = $lnInvestigation['kid'];

		$survey_result['description'] = $lnInvestigation['description'];
		$survey_result['title'] = $lnInvestigation['title'];
		$survey_result['question'] = [];

		//算总人数
		$sub_result_arr = LnInvestigationResult::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->groupBy("user_id")
			->select("user_id")
			->asArray()
			->count();
			//->all();

		//$sub_result_arr_num = count($sub_result_arr);
		$sub_result_arr_num =$sub_result_arr;

		//算每个选项多少人
		$user_arr = LnInvestigationResult::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->groupBy("investigation_option_id")
			->select("count(user_id) as num,investigation_option_id")
			->asArray()
			->all();
		$user_map = [];
		foreach ($user_arr as $us) {
			$user_map[$us['investigation_option_id']] = $us['num'];
		}

		foreach ($investigationQuestionArr as $investigationQuestion) {
			$question_type = $investigationQuestion['question_type'];
			if ($question_type == LnInvestigationQuestion::QUESTION_TYPE_SINGLE || $question_type == LnInvestigationQuestion::QUESTION_TYPE_MULTIPLE) {


				$s_choice_obj = [];
				$s_choice_obj['question_title'] = $investigationQuestion['question_title'];
				$s_choice_obj['question_type'] = $investigationQuestion['question_type'];
				$s_choice_obj['id'] = $investigationQuestion['kid'];
				$s_choice_obj['options'] = [];

				$lnInvestigationOptionArrTmp = [];

				$lnInvestigationOptionArr = LnInvestigationOption::find(false)
					->andFilterWhere(["=", "investigation_id", $id])
					->andFilterWhere(["=", "investigation_question_id", $investigationQuestion['kid']])
					->orderBy("sequence_number asc")
					->all();
				foreach ($lnInvestigationOptionArr as $investigationOption) {

					$choice_option_obj = [];
					$choice_option_obj['option_title'] = $investigationOption['option_title'];
					$choice_option_obj['sequence_number'] = $investigationOption['sequence_number'];
					$choice_option_obj['kid'] = $investigationOption['kid'];

					$lnInvestigationOption_id = $investigationOption['kid'];
					$choice_option_obj['submit_num'] = $user_map[$lnInvestigationOption_id];

					array_push($lnInvestigationOptionArrTmp, $choice_option_obj);
				}

				foreach ($lnInvestigationOptionArrTmp as $optionTmp) {

					$optionTmp['submit_num_rate'] = round($optionTmp['submit_num'] / $sub_result_arr_num * 100, 2);
					array_push($s_choice_obj['options'], $optionTmp);
				};

				array_push($survey_result['question'], $s_choice_obj);


			} else {

				if (isset($params['is_content_flag'])) {
					$m_table = LnInvestigationResult::tableName();
					$user_pos_org_sql = "(  select fu.kid, fu.real_name from {{%fw_user}} fu where fu.is_deleted = '0' ) ";
					$s_choice_obj = [];
					$s_choice_obj['question_title'] = $investigationQuestion['question_title'];
					$s_choice_obj['question_description'] = $investigationQuestion['question_description'];
					$s_choice_obj['question_type'] = $investigationQuestion['question_type'];
					$s_choice_obj['id'] = $investigationQuestion['kid'];
					$s_choice_obj['options'] = [];
					$s_choice_obj['options2'] = [];
					$page_flag_num = 0;
					$content_result_arr = LnInvestigationResult::find(false)
						->innerjoin($user_pos_org_sql . ' as t1', 't1.kid = ' . $m_table . ".user_id")
						->andFilterWhere(["=", "investigation_id", $id])
						->andFilterWhere(["=", "investigation_question_id", $investigationQuestion['kid']])
						->andFilterWhere(["=", "question_type", InvestigationService::QUESTION_TYPE_WENDA])
						->orderBy($m_table . ".created_at asc")
						->select("t1.real_name as user_id1," . $m_table . ".*")
						->asArray()
						->all();
					foreach ($content_result_arr as $result_arr1) {

						$result_arr1['created_at'] = date("Y-m-d H:i:s", $result_arr1['created_at']);
						$page_flag_num = $page_flag_num + 1;
						if ($lnInvestigation['answer_type'] == LnInvestigation::ANSWER_TYPE_ANONYMOUS) {
							$result_arr1['user_id1'] = "参与者" . $page_flag_num;
						}

						array_push($s_choice_obj['options'], $result_arr1);

						if ($page_flag_num == 3) {
							break;
						}
					}

					for ($i = $page_flag_num; $i < sizeof($content_result_arr); $i++) {
						$content_result_arr[$i]['created_at'] = date("Y-m-d H:i:s", $content_result_arr[$i]['created_at']);

						if ($lnInvestigation['answer_type'] == LnInvestigation::ANSWER_TYPE_ANONYMOUS) {
							$content_result_arr[$i]['user_id1'] = "参与者" . ($i + 1);
						}

						array_push($s_choice_obj['options2'], $content_result_arr[$i]);
					}

					array_push($survey_result['question'], $s_choice_obj);
				}

			}
		}

		$survey_result['sub_result_arr_num'] = $sub_result_arr_num;
		$survey_result['title'] = $lnInvestigation['title'];
		$survey_result['start_at'] = date("Y年m月d日", $lnInvestigation['start_at']);
		$survey_result['end_at'] = date("Y年m月d日", $lnInvestigation['end_at']);

		return $survey_result;

	}


	public function getSurveyStResult($id, $params)
	{
		$lnInvestigation = LnInvestigation::find(false)
			->andFilterWhere(["=", "kid", $id])
			->asArray()
			->one();

		$investigationQuestionArr = LnInvestigationQuestion::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->orderBy("sequence_number asc")
			->asArray()
			->all();

		$survey_result = [];

		if ($lnInvestigation['answer_type'] == LnInvestigation::ANSWER_TYPE_REALNAME) {
			$survey_result['answer_type'] = '实名问卷';
		} else {
			$survey_result['answer_type'] = '匿名问卷';
		}

		$survey_result['investigation_range'] = $lnInvestigation['investigation_range'];
		$survey_result['id'] = $lnInvestigation['kid'];

		$survey_result['description'] = $lnInvestigation['description'];
		$survey_result['title'] = $lnInvestigation['title'];
		$survey_result['question'] = [];

		//算总人数
		$sub_result_arr = LnInvestigationResult::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->andFilterWhere(["=", "course_id", $params['course_id']])
			->andFilterWhere(["=", "mod_id", $params['mod_id']])
			->andFilterWhere(["=", "mod_res_id", $params['mod_res_id']])
			->groupBy("user_id")
			->select("user_id")
			->asArray()
			->all();

		$sub_result_arr_num = count($sub_result_arr);

		//算每个选项多少人
		$user_arr = LnInvestigationResult::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->andFilterWhere(["=", "course_id", $params['course_id']])
			->andFilterWhere(["=", "mod_id", $params['mod_id']])
			->andFilterWhere(["=", "mod_res_id", $params['mod_res_id']])
			->groupBy("investigation_option_id")
			->select("count(user_id) as num,investigation_option_id")
			->asArray()
			->all();
		$user_map = [];
		foreach ($user_arr as $us) {
			$user_map[$us['investigation_option_id']] = $us['num'];
		}

		foreach ($investigationQuestionArr as $investigationQuestion) {
			$question_type = $investigationQuestion['question_type'];
			if ($question_type == LnInvestigationQuestion::QUESTION_TYPE_SINGLE || $question_type == LnInvestigationQuestion::QUESTION_TYPE_MULTIPLE) {


				$s_choice_obj = [];
				$s_choice_obj['question_title'] = $investigationQuestion['question_title'];
				$s_choice_obj['question_type'] = $investigationQuestion['question_type'];
				$s_choice_obj['id'] = $investigationQuestion['kid'];
				$s_choice_obj['options'] = [];

				$lnInvestigationOptionArrTmp = [];

				$lnInvestigationOptionArr = LnInvestigationOption::find(false)
					->andFilterWhere(["=", "investigation_id", $id])
					->andFilterWhere(["=", "investigation_question_id", $investigationQuestion['kid']])
					->orderBy("sequence_number asc")
					->all();
				foreach ($lnInvestigationOptionArr as $investigationOption) {

					$choice_option_obj = [];
					$choice_option_obj['option_title'] = $investigationOption['option_title'];
					$choice_option_obj['sequence_number'] = $investigationOption['sequence_number'];
					$choice_option_obj['kid'] = $investigationOption['kid'];

					$lnInvestigationOption_id = $investigationOption['kid'];
					$choice_option_obj['submit_num'] = $user_map[$lnInvestigationOption_id];

					array_push($lnInvestigationOptionArrTmp, $choice_option_obj);
				}

				foreach ($lnInvestigationOptionArrTmp as $optionTmp) {
					if ($sub_result_arr_num > 0){
						$optionTmp['submit_num_rate'] = round($optionTmp['submit_num'] / $sub_result_arr_num * 100, 2);
					}else{
						$optionTmp['submit_num_rate'] = 0;
					}

					array_push($s_choice_obj['options'], $optionTmp);
				};

				array_push($survey_result['question'], $s_choice_obj);


			} else {

				if (isset($params['is_content_flag'])) {
					$m_table = LnInvestigationResult::tableName();
					$user_pos_org_sql = "(  select fu.kid, fu.real_name from {{%fw_user}} fu where fu.is_deleted = '0' ) ";
					$s_choice_obj = [];
					$s_choice_obj['question_title'] = $investigationQuestion['question_title'];
					$s_choice_obj['question_description'] = $investigationQuestion['question_description'];
						
					$s_choice_obj['question_type'] = $investigationQuestion['question_type'];
					$s_choice_obj['id'] = $investigationQuestion['kid'];
					$s_choice_obj['options'] = [];
					$s_choice_obj['options2'] = [];
					$page_flag_num = 0;
					$content_result_arr = LnInvestigationResult::find(false)
						->innerjoin($user_pos_org_sql . ' as t1', 't1.kid = ' . $m_table . ".user_id")
						->andFilterWhere(["=", "investigation_id", $id])
						->andFilterWhere(["=", "course_id", $params['course_id']])
						->andFilterWhere(["=", "investigation_question_id", $investigationQuestion['kid']])
						->andFilterWhere(["=", "question_type", InvestigationService::QUESTION_TYPE_WENDA])
						->orderBy($m_table . ".created_at asc")
						->select("t1.real_name as user_id1," . $m_table . ".*")
						->asArray()
						->all();
					foreach ($content_result_arr as $result_arr1) {
						$page_flag_num = $page_flag_num + 1;
						$result_arr1['created_at'] = date("Y-m-d H:i:s", $result_arr1['created_at']);

						if ($lnInvestigation['answer_type'] == LnInvestigation::ANSWER_TYPE_ANONYMOUS) {
							$result_arr1['user_id1'] = "参与者" . $page_flag_num;
						}

						array_push($s_choice_obj['options'], $result_arr1);
						if ($page_flag_num == 3) {
							break;
						}
					}

					for ($i = $page_flag_num; $i < sizeof($content_result_arr); $i++) {
						$content_result_arr[$i]['created_at'] = date("Y-m-d H:i:s", $content_result_arr[$i]['created_at']);

						if ($lnInvestigation['answer_type'] == LnInvestigation::ANSWER_TYPE_ANONYMOUS) {
							$content_result_arr[$i]['user_id1'] = "参与者" . ($i + 1);
						}

						array_push($s_choice_obj['options2'], $content_result_arr[$i]);
					}

					array_push($survey_result['question'], $s_choice_obj);
				}
			}
		}

		$survey_result['sub_result_arr_num'] = $sub_result_arr_num;
		$survey_result['title'] = $lnInvestigation['title'];

		return $survey_result;

	}

	public function getVoteStResult($id, $params)
	{
		$lnInvestigation = LnInvestigation::findOne($id);
		$investigationQuestionArr = LnInvestigationQuestion::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->all();
		$user_id = Yii::$app->user->getId();
		//
		$sub_result_arr = LnInvestigationResult::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->andFilterWhere(["=", "course_id", $params['course_id']])
			->andFilterWhere(["=", "mod_id", $params['mod_id']])
			->groupBy("user_id")
			->select("user_id")
			->asArray()
			->all();

		$sub_result_arr_num = count($sub_result_arr);

		$user_arr = LnInvestigationResult::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->andFilterWhere(["=", "course_id", $params['course_id']])
			->andFilterWhere(["=", "mod_id", $params['mod_id']])
			->groupBy("investigation_option_id")
			->select("count(user_id) as num,investigation_option_id")
			->asArray()
			->all();
		$user_map = [];
		foreach ($user_arr as $us) {
			$user_map[$us['investigation_option_id']] = $us['num'];
		}

		foreach ($investigationQuestionArr as $investigationQuestion) {

//			$question_type=$investigationQuestion->question_type;
			$lnInvestigationOptionArr = LnInvestigationOption::find(false)
				->andFilterWhere(["=", "investigation_id", $id])
				->andFilterWhere(["=", "investigation_question_id", $investigationQuestion->kid])
				->orderBy("sequence_number asc")
				->asArray()
				->all();

			$lnInvestigationOptionArrTmp = [];
			$lnInvestigationOptionArrTmp2 = [];

			foreach ($lnInvestigationOptionArr as $lnInvestigationOption) {
				$lnInvestigationOption_id = $lnInvestigationOption['kid'];
				$lnInvestigationOption['submit_num'] = 0;
				for ($i = 0; $i < count($sub_result_arr); $i++) {
					$lnInvestigationOption['submit_num'] = $user_map[$lnInvestigationOption_id];
				}
				array_push($lnInvestigationOptionArrTmp, $lnInvestigationOption);
			};

			foreach ($lnInvestigationOptionArrTmp as $optionTmp) {

				$optionTmp['submit_num_rate'] = round($optionTmp['submit_num'] / $sub_result_arr_num * 100, 2);
				array_push($lnInvestigationOptionArrTmp2, $optionTmp);
			};

		}

		$investigationQuestionTmp = $investigationQuestionArr[0];
		$vote_result = [];


		if ($lnInvestigation->answer_type == self::ANSWER_TYPE_REALNAME) {
			$vote_result['answer_type'] = '实名投票';
		} else {
			$vote_result['answer_type'] = '匿名投票';
		}

		$vote_result['question_title'] = $investigationQuestionTmp->question_title;
		$vote_result['question_type'] = $investigationQuestionTmp->question_type;
		$vote_result['id'] = $id;

		$vote_result['investigation_range'] = $lnInvestigation->investigation_range;

		$vote_result['investigation_question_id'] = $investigationQuestionTmp->kid;
		$vote_result['options'] = $lnInvestigationOptionArrTmp2;

		$vote_result['sub_result_arr_num'] = $sub_result_arr_num;
		$vote_result['title'] = $lnInvestigation->title;

		return $vote_result;

	}
	
	public function getSingleVoteStResult($id, $params){
		$cacheKey = 'get-single-vote-st-result-'.$id;
		//测试用
		//return $this->getSingleVoteStResultByDb($id, $params);
		//学习管理员查询统计结果，直接从数据库中去
		if (isset($params['is_manag'])){
			return $this->getSingleVoteStResultByDb($id, $params);
		}else{
			//学员查询统计结果，从缓存中取		
			//$vote_result =Yii::$app->redis->get($cacheKey);	
			$vote_result =Yii::$app->cache->getCache($cacheKey);
			$vote_result=json_decode($vote_result,true);
			
			if($vote_result){
				return $vote_result;
			}else {
				return $this->getSingleVoteStResultByDb($id, $params);
			}
		}
	}

	public function getSingleVoteStResultByDb($id, $params)
	{
		$lnInvestigation = LnInvestigation::findOne($id);
		$investigationQuestionArr = LnInvestigationQuestion::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->all();
		$user_id = Yii::$app->user->getId();
		//
		$sub_result_arr = LnInvestigationResult::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->groupBy("user_id")
			->select("user_id")
			->asArray()
			->all();

		//$sub_result_arr_num = count($sub_result_arr);
		
		$sub_result_arr_num= LnInvestigationResult::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->groupBy("user_id")
			->select("user_id")
			->asArray()
			->count();

		$user_arr = LnInvestigationResult::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->groupBy("investigation_option_id")
			->select("count(user_id) as num,investigation_option_id")
			->asArray()
			->all();
		$user_map = [];
		foreach ($user_arr as $us) {
			$user_map[$us['investigation_option_id']] = $us['num'];
		}

		foreach ($investigationQuestionArr as $investigationQuestion) {

			//			$question_type=$investigationQuestion->question_type;
			$lnInvestigationOptionArr = LnInvestigationOption::find(false)
				->andFilterWhere(["=", "investigation_id", $id])
				->andFilterWhere(["=", "investigation_question_id", $investigationQuestion->kid])
				->orderBy("sequence_number asc")
				->asArray()
				->all();

			$lnInvestigationOptionArrTmp = [];
			$lnInvestigationOptionArrTmp2 = [];

			foreach ($lnInvestigationOptionArr as $lnInvestigationOption) {
				$lnInvestigationOption_id = $lnInvestigationOption['kid'];
				$lnInvestigationOption['submit_num'] = 0;
				for ($i = 0; $i < count($sub_result_arr); $i++) {
					$lnInvestigationOption['submit_num'] = $user_map[$lnInvestigationOption_id];
				}
				array_push($lnInvestigationOptionArrTmp, $lnInvestigationOption);
			};

			foreach ($lnInvestigationOptionArrTmp as $optionTmp) {

				$optionTmp['submit_num_rate'] = round($optionTmp['submit_num'] / $sub_result_arr_num * 100, 2);
				array_push($lnInvestigationOptionArrTmp2, $optionTmp);
			};

		}

		$investigationQuestionTmp = $investigationQuestionArr[0];
		$vote_result = [];


		if ($lnInvestigation->answer_type == self::ANSWER_TYPE_REALNAME) {
			$vote_result['answer_type'] = '实名投票';
		} else {
			$vote_result['answer_type'] = '匿名投票';
		}

		$vote_result['question_title'] = $investigationQuestionTmp->question_title;
		$vote_result['question_type'] = $investigationQuestionTmp->question_type;
		$vote_result['id'] = $id;

		$vote_result['investigation_range'] = $lnInvestigation->investigation_range;

		$vote_result['investigation_question_id'] = $investigationQuestionTmp->kid;
		$vote_result['options'] = $lnInvestigationOptionArrTmp2;
		$vote_result['sub_result_arr_num'] = $sub_result_arr_num;
		$vote_result['title'] = $lnInvestigation->title;

		$vote_result['start_at'] = date("Y年m月d日", $lnInvestigation->start_at);
		$vote_result['end_at'] = date("Y年m月d日", $lnInvestigation->end_at);


		return $vote_result;

	}

	public function saveSurvey($params)
	{
		$title = Html::encode($params['title']);
		$invest = $this->saveInvestigation($params, self::INVESTIGATION_TYPE_SURVEY, $title);
		$questionArr = $params['question'];
		$investigation_id = $invest->kid;
		$num = 0;
		foreach ($questionArr as $question) {
			$num++;
			if ($question['question_type'] == LnInvestigationQuestion::QUESTION_TYPE_QA) {
				$this->saveLnInvestigationQuestionForQuest($question, $investigation_id, $num);
			} elseif ($question['question_type'] == LnInvestigationQuestion::QUESTION_TYPE_PAGE_SPLIT) {
				$this->saveLnInvestigationQuestion($question, $investigation_id, $num);
			} else {
				$invest_quest = $this->saveLnInvestigationQuestion($question, $investigation_id, $num);
				$this->saveLnInvestigationOption($question, $investigation_id, $invest_quest->kid);
			}

		}

	}

	public function saveVote($params)
	{
		$title = Html::encode($params['question_title']);
		$invest = $this->saveInvestigation($params, self::INVESTIGATION_TYPE_VOTE, $title);
		$invest_quest = $this->saveLnInvestigationQuestion($params, $invest->kid, 1);
		$this->saveLnInvestigationOption($params, $invest->kid, $invest_quest->kid);
	}


	public function surveySubmitResult($params, $userId = null)
	{
		$arrs = $params['param'];
		$user_id = Yii::$app->user->getId();

		if (empty($user_id)) {
			$user_id = $userId;
		}

		//提交前先删除之前的数据
		$delete_investigation_result = new LnInvestigationResult();
		$delete_params = $arrs[0];

		$params = [
			'investigation_id' => $delete_params['investigation_id'],
			'course_id' => $delete_params['course_id'],
			'course_reg_id' => $delete_params['course_reg_id'],
			'mod_id' => $delete_params['mod_id'],
			'mod_res_id' => $delete_params['mod_res_id'],
			'courseactivity_id' => $delete_params['courseactivity_id'],
			'component_id' => $delete_params['component_id'],
		];
		
		$courseCompleteService = new CourseCompleteService();
		$attempt = $courseCompleteService->getLastAttempt($delete_params['course_reg_id']);
		

		$condition = "investigation_id = :investigation_id and course_id = :course_id "
			. "and course_reg_id = :course_reg_id and mod_id = :mod_id and mod_res_id = :mod_res_id "
			. "and courseactivity_id = :courseactivity_id and component_id = :component_id";

		$delete_investigation_result->deleteAll($condition, $params);


		foreach ($arrs as $arr) {

			$LnInvestigationResult = new LnInvestigationResult();
			foreach ($arr as $k => $v) {

				if ($k == "option_title") {
					$LnInvestigationResult->$k = Html::encode($v);
				} else if ($k == "option_result") {
					$LnInvestigationResult->$k = Html::encode($v);
				} else {
					$LnInvestigationResult->$k = $v;
				};

			}
			
			$LnInvestigationResult->attempt=$attempt;
			$LnInvestigationResult->course_attempt_number=$attempt;
			$LnInvestigationResult->user_id = $user_id;
			$LnInvestigationResult->save();
			//var_dump($LnInvestigationResult->getErrors());
		}
	}

	public function singleSurveySubmitResult($params, $userId = null)
	{
		$arrs = $params['param'];
		$user_id = Yii::$app->user->getId();
		if (empty($user_id)) {
			$user_id = $userId;
		}
		$models1 = [];
		foreach ($arrs as $arr) {

			$LnInvestigationResult = new LnInvestigationResult();
			foreach ($arr as $k => $v) {
				if ($k == "option_title") {
					$LnInvestigationResult->$k = Html::encode($v);
				} else if ($k == "option_result") {
					$LnInvestigationResult->$k = Html::encode($v);
				} else {
					$LnInvestigationResult->$k = $v;
				};

			}
			$LnInvestigationResult->user_id = $user_id;
			//$LnInvestigationResult->save();
			array_push($models1, $LnInvestigationResult);

		}
		$errMsg = "";
		BaseActiveRecord::batchInsertSqlArray($models1, $errMsg);

	}

	/**
	 * 还原资源完成情况
	 * @param $courseCompleteId
	 * @param $attempt
	 */
	public function restoreInvestigationResult($courseCompleteId, $attempt)
	{
		//恢复被删除的数据
		$model = new LnInvestigationResult();

		$params = [
			':course_complete_id' => $courseCompleteId,
			':attempt' => $attempt,
			':is_deleted' => LnInvestigationResult::DELETE_FLAG_YES,
		];

		$condition = 'attempt = :attempt and course_complete_id = :course_complete_id and is_deleted = :is_deleted';

		$attributes = [
			'is_deleted' => LnInvestigationResult::DELETE_FLAG_NO,
		];

		$model->updateAll($attributes, $condition, $params, true);


		//删除无用的尝试数据（为避免今后产生同一重试次数的垃圾数据，所以直接物理删除）
		$model = new LnInvestigationResult();

		$params = [
			':course_complete_id' => $courseCompleteId,
			':attempt' => $attempt,
		];

		$condition = 'attempt > :attempt and course_complete_id = :course_complete_id';

		$model->physicalDeleteAll($condition, $params);
	}

	public function saveInvestigation($params, $investigation_type, $title)
	{

		$answer_type = $params['answer_type'];
		$is_estimate = $params['is_estimate'];
		$start_at = $params['start_at'];
		$end_at = $params['end_at'];
		$status = $params['status'];
		$description = null;
		if ($investigation_type == self::INVESTIGATION_TYPE_SURVEY) {
			$description = Html::encode($params['description']);
		}
		$investigation_range = $params['investigation_range'];

		return $this->saveInvest($answer_type, $title, $investigation_type,
			$start_at, $end_at, $description, $status, $investigation_range,$is_estimate);
	}
	
	public function saveInvestigationByCopy($params, $investigation_type, $title)
	{
	
		$answer_type = $params['answer_type'];
		$is_estimate = $params['is_estimate'];
		$start_at = $params['start_at'];
		$end_at = $params['end_at'];
		$status = $params['status'];
		$description = null;
		if ($investigation_type == self::INVESTIGATION_TYPE_SURVEY) {
			$description = $params['description'];
		}
		$investigation_range = $params['investigation_range'];
	
		return $this->saveInvest($answer_type, $title, $investigation_type,
				$start_at, $end_at, $description, $status, $investigation_range,$is_estimate);
	}


	function saveInvest($answer_type, $title, $investigation_type, $start_at, $end_at, $description, $status, $investigation_range,$is_estimate)
	{
		$lnInvestigation = new LnInvestigation();
		$lnInvestigation->answer_type = $answer_type;	
		$lnInvestigation->is_estimate=$is_estimate;
		$lnInvestigation->title = $title;
		$lnInvestigation->investigation_type = $investigation_type;
		$lnInvestigation->start_at = $start_at;
		$lnInvestigation->end_at = $end_at;
		$lnInvestigation->investigation_range = $investigation_range;

		$lnInvestigation->status = $status;
		if ($description) {
			$lnInvestigation->description = $description;
		}

		$lnInvestigation->company_id = Yii::$app->user->identity->company_id;
		$lnInvestigation->needReturnKey = true;
		$lnInvestigation->saveEncode=true;
		$lnInvestigation->save();

		return $lnInvestigation;
	}

	public function editVote($params)
	{
		$title = Html::encode($params['question_title']);
		$id = $params['id'];
		$invest_quest_id = $params['invest_quest_id'];
		$invest = $this->editInvestigation($params, self::INVESTIGATION_TYPE_VOTE, $title, $id);

		$deleteOptionModel = new LnInvestigationOption();
		$params1 = [
			'investigation_id' => $id,
		];
		$condition = "investigation_id = :investigation_id";
		$deleteOptionModel->deleteAll($condition, $params1);

		$deleteQuestionModel = new LnInvestigationQuestion();
		$deleteQuestionModel->deleteAll($condition, $params1);

		$invest_quest = $this->saveLnInvestigationQuestion($params, $id, 1);
		$this->editLnInvestigationOption($params, $id, $invest_quest->kid);
	}

	public function editInvestigation($params, $investigation_type, $title, $id)
	{

		$answer_type = $params['answer_type'];
		$is_estimate = $params['is_estimate'];

		$investigation_range = $params['investigation_range'];

		$start_at = $params['start_at'];
		$end_at = $params['end_at'];

		$description = null;
		if ($investigation_type == self::INVESTIGATION_TYPE_SURVEY) {
			$description = Html::encode($params['description']);
		}


		return $this->editInvest($answer_type, $title, $investigation_type,
			$start_at, $end_at, $description, $id, $investigation_range,$is_estimate);
	}


	function editInvest($answer_type, $title, $investigation_type, $start_at, $end_at, $description, $id, $investigation_range,$is_estimate)
	{
		$lnInvestigation = new LnInvestigation();
		$lnInvestigation->answer_type = $answer_type;
		$lnInvestigation->title = $title;

		$lnInvestigation->start_at = $start_at;
		$lnInvestigation->end_at = $end_at;


		if ($description) {
			$lnInvestigation->description = $description;
		}

		$attributes = [
			'answer_type' => $answer_type,
			'title' => $title,
			'start_at' => $start_at,
			'end_at' => $end_at,
			'description' => $description,
			'investigation_range' => $investigation_range,
			'is_estimate'=>$is_estimate,
		];

		$lnInvestigation->updateAll($attributes, "kid = '" . $id . "'");
		return LnInvestigation::removeFromCacheByKid($id);


	}

	public function editLnInvestigationQuestion($params, $investigation_id, $sequence_number)
	{
		$question_title = $params['question_title'];
		$question_type = $params['question_type'];
		$lnInvestigationQuestion = new LnInvestigationQuestion();


		$attributes = [

			'question_title' => $question_title,
			'question_type' => LnInvestigationQuestion::QUESTION_TYPE_QA,
			'sequence_number' => $sequence_number,

		];
		$params1 = [
			':kid' => $investigation_id,
		];

		$condition = 'kid = :kid ';

		$lnInvestigationQuestion->updateAll($attributes, $condition, $params1);
	}


	public function editLnInvestigationOption($params, $investigation_id, $investigation_question_id)
	{
		$options = $params['options'];
		$num = 0;
		foreach ($options as $option) {
			$num++;
			$lnInvestigationOption = new LnInvestigationOption();
			$lnInvestigationOption->investigation_id = $investigation_id;
			$lnInvestigationOption->investigation_question_id = $investigation_question_id;
			$lnInvestigationOption->option_title = Html::encode($option['option_title']);
			$lnInvestigationOption->sequence_number = $num;
			$lnInvestigationOption->save();
		}
	}


	public function saveLnInvestigationQuestionForQuest($params, $investigation_id, $sequence_number)
	{
		$question_title = Html::encode($params['question_title']);
		$question_description = Html::encode($params['question_description']);
		$question_type = $params['question_type'];
		$lnInvestigationQuestion = new LnInvestigationQuestion();
		$lnInvestigationQuestion->investigation_id = $investigation_id;
		$lnInvestigationQuestion->question_title = $question_title;
		$lnInvestigationQuestion->question_description = $question_description;
		$lnInvestigationQuestion->question_type = $question_type;
		$lnInvestigationQuestion->sequence_number = $sequence_number;
		$lnInvestigationQuestion->needReturnKey = true;
		$lnInvestigationQuestion->saveEncode=true;
		$lnInvestigationQuestion->save();
		return $lnInvestigationQuestion;
	}
	
	public function saveLnInvestigationQuestionForQuestByCopy($params, $investigation_id, $sequence_number)
	{
		$question_title = $params['question_title'];
		$question_description = $params['question_description'];
		$question_type = $params['question_type'];
		$lnInvestigationQuestion = new LnInvestigationQuestion();
		$lnInvestigationQuestion->investigation_id = $investigation_id;
		$lnInvestigationQuestion->question_title = $question_title;
		$lnInvestigationQuestion->question_description = $question_description;
		$lnInvestigationQuestion->question_type = $question_type;
		$lnInvestigationQuestion->sequence_number = $sequence_number;
		$lnInvestigationQuestion->needReturnKey = true;
		$lnInvestigationQuestion->saveEncode=true;
		$lnInvestigationQuestion->save();
		return $lnInvestigationQuestion;
	}

	public function saveLnInvestigationQuestionVote($params, $investigation_id, $sequence_number)
	{
		$question_title = $params['question_title'] . '[副本]';
		$question_type = $params['question_type'];
		$lnInvestigationQuestion = new LnInvestigationQuestion();
		$lnInvestigationQuestion->investigation_id = $investigation_id;
		$lnInvestigationQuestion->question_title = $question_title;
		$lnInvestigationQuestion->question_type = $question_type;
		$lnInvestigationQuestion->sequence_number = $sequence_number;
		$lnInvestigationQuestion->needReturnKey = true;
		$lnInvestigationQuestion->save();
		return $lnInvestigationQuestion;
	}

	public function saveLnInvestigationQuestion($params, $investigation_id, $sequence_number)
	{
		$question_title = Html::encode($params['question_title']);
		$question_type = $params['question_type'];
		$lnInvestigationQuestion = new LnInvestigationQuestion();
		$lnInvestigationQuestion->investigation_id = $investigation_id;
		$lnInvestigationQuestion->question_title = $question_title;
		$lnInvestigationQuestion->question_type = $question_type;
		$lnInvestigationQuestion->sequence_number = $sequence_number;
		$lnInvestigationQuestion->needReturnKey = true;
		$lnInvestigationQuestion->saveEncode=true;
		$lnInvestigationQuestion->save();
		return $lnInvestigationQuestion;
	}
	
	public function saveLnInvestigationQuestionByCopy($params, $investigation_id, $sequence_number)
	{
		$question_title = $params['question_title'];
		$question_type = $params['question_type'];
		$lnInvestigationQuestion = new LnInvestigationQuestion();
		$lnInvestigationQuestion->investigation_id = $investigation_id;
		$lnInvestigationQuestion->question_title = $question_title;
		$lnInvestigationQuestion->question_type = $question_type;
		$lnInvestigationQuestion->sequence_number = $sequence_number;
		$lnInvestigationQuestion->needReturnKey = true;
		$lnInvestigationQuestion->saveEncode=true;
		$lnInvestigationQuestion->save();
		return $lnInvestigationQuestion;
	}

	public function saveLnInvestigationOption($params, $investigation_id, $investigation_question_id)
	{
		$options = $params['options'];

		$num = 0;
		foreach ($options as $option) {
			$num++;
			$lnInvestigationOption = new LnInvestigationOption();
			$lnInvestigationOption->investigation_id = $investigation_id;
			$lnInvestigationOption->investigation_question_id = $investigation_question_id;
			$lnInvestigationOption->option_title = Html::encode($option['option_title']);
			$lnInvestigationOption->sequence_number = $num;
			$lnInvestigationOption->saveEncode=true;
			$lnInvestigationOption->save();
		}
	}


	public function search($params, $component = false)
	{
		$query = LnInvestigation::find(false);
		if (isset($params['keyword'])) {
			$keyword = $params['keyword'];
			$keyword = trim($keyword);
		} else {
			$keyword = "";
		}
		if ($keyword) {
			$query->andWhere("title like '%{$keyword}%'");
		}
		if (isset($params['investigation_range'])) {
			$query->andFilterWhere(['=', 'investigation_range', $params['investigation_range']]);
		}
		if (isset($params['status'])) {
			$query->andFilterWhere(["=", "status", $params['status']]);
		}
		if (isset($params['investigation_type']) && $params['investigation_type'] != "") {
			$query->andFilterWhere(["=", "investigation_type", $params['investigation_type']]);
		}
		if (!empty($params['companyId'])){
			$companyId = $params['companyId'];
		}else{
			$companyId = Yii::$app->user->identity->company_id;
		}
		$query->andFilterWhere(["=", "company_id", $companyId]);
		/*课程组件列表调用*/
		if ($component) {
			$count = $query->count();
			$pages = new TPagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
			$data = $query->offset($pages->offset)->limit($pages->limit)->addOrderBy(['created_at' => SORT_DESC])->all();
			$dataProvider = array(
				'pages' => $pages,
				'data' => $data,
			);
		} else {
			$dataProvider = new ActiveDataProvider([
				'query' => $query,
			]);

			$this->load($params);
			$dataProvider->setSort(false);
			$query->addOrderBy(['created_at' => SORT_DESC]);
		}
		/*echo ($query->createCommand()->getRawSql());*/
		return $dataProvider;
	}


	public function publish($id)
	{
		$model = LnInvestigation::findOne($id);
		if (!empty($model)) {
			$model->status = InvestigationService::STATUS_FORMAL;
			$model->save();
		}
	}

	public function getVote($id)
	{
		$cacheKey = 'single-investigation-vote-query-'.$id;
		$vote_result = [];
		$vote_result = Yii::$app->cache->get($cacheKey);
		if($vote_result){
			return $vote_result;
		}else{
			
		
		
		$lnInvestigation = LnInvestigation::findOne($id);
		$investigationQuestionArr = LnInvestigationQuestion::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->all();

		foreach ($investigationQuestionArr as $investigationQuestion) {
			$lnInvestigationOptionArr = LnInvestigationOption::find(false)
				->andFilterWhere(["=", "investigation_id", $id])
				->andFilterWhere(["=", "investigation_question_id", $investigationQuestion->kid])
				->orderBy("sequence_number asc")
				->asArray()
				->all();
		}

		$investigationQuestionTmp = $investigationQuestionArr[0];
		
		if ($lnInvestigation->start_at) {
			$vote_result['start_at'] = date("Y-m-d", $lnInvestigation->start_at);
			$vote_result['end_at'] = date("Y-m-d", $lnInvestigation->end_at);
		} else {
			$vote_result['start_at'] = $lnInvestigation->start_at;
			$vote_result['end_at'] = $lnInvestigation->end_at;
		}

		$vote_result['is_estimate'] = $lnInvestigation->is_estimate;
		$vote_result['answer_type'] = $lnInvestigation->answer_type;
		$vote_result['question_title'] = $investigationQuestionTmp->question_title;
		$vote_result['question_type'] = $investigationQuestionTmp->question_type;
		$vote_result['id'] = $id;

		$vote_result['investigation_range'] = $lnInvestigation->investigation_range;

		$vote_result['investigation_question_id'] = $investigationQuestionTmp->kid;
		$vote_result['options'] = $lnInvestigationOptionArr;

			Yii::$app->cache->set($cacheKey, $vote_result);
		return $vote_result;
		
		}	

	}

	public function getSurvey($id)
	{
		
		$cacheKey = 'single-investigation-survey-query-'.$id;
		
		$survey_result = [];
		
		$survey_result = Yii::$app->cache->get($cacheKey);
		if($survey_result){
			return $survey_result;
		}else{
			
		
		$lnInvestigation = LnInvestigation::find(false)
			->andFilterWhere(["=", "kid", $id])
			->asArray()
			->one();

		$investigationQuestionArr = LnInvestigationQuestion::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->orderBy("sequence_number asc")
			->asArray()
			->all();

		
		if ($lnInvestigation['start_at']) {
			$survey_result['start_at'] = date("Y-m-d", $lnInvestigation['start_at']);
			$survey_result['end_at'] = date("Y-m-d", $lnInvestigation['end_at']);
		} else {
			$survey_result['start_at'] = $lnInvestigation['start_at'];
			$survey_result['end_at'] = $lnInvestigation['end_at'];
		}

		$survey_result['is_estimate'] = $lnInvestigation['is_estimate'];
		$survey_result['answer_type'] = $lnInvestigation['answer_type'];
		$survey_result['investigation_range'] = $lnInvestigation['investigation_range'];
		$survey_result['id'] = $lnInvestigation['kid'];

		$survey_result['description'] = $lnInvestigation['description'];
		$survey_result['title'] = $lnInvestigation['title'];
		$survey_result['question'] = [];

		foreach ($investigationQuestionArr as $investigationQuestion) {
			$question_type = $investigationQuestion['question_type'];
			if ($question_type == LnInvestigationQuestion::QUESTION_TYPE_QA) {
				$s_question_obj = [];
				$s_question_obj['question_title'] =Html::decode($investigationQuestion['question_title']);
				$s_question_obj['question_description'] =Html::decode( $investigationQuestion['question_description']);
				$s_question_obj['question_type'] = $investigationQuestion['question_type'];
				$s_question_obj['id'] = $investigationQuestion['kid'];

				array_push($survey_result['question'], $s_question_obj);

			} elseif ($question_type == LnInvestigationQuestion::QUESTION_TYPE_PAGE_SPLIT) {
				$s_pagination_obj = [];
				$s_pagination_obj['question_title'] = $investigationQuestion['question_title'];
				$s_pagination_obj['question_type'] = $investigationQuestion['question_type'];
				$s_pagination_obj['id'] = $investigationQuestion['kid'];

				array_push($survey_result['question'], $s_pagination_obj);
			} else {


				$s_choice_obj = [];
				$s_choice_obj['question_title'] = Html::decode($investigationQuestion['question_title']);
				$s_choice_obj['question_type'] = $investigationQuestion['question_type'];
				$s_choice_obj['id'] = $investigationQuestion['kid'];
				$s_choice_obj['options'] = [];

				$lnInvestigationOptionArr = LnInvestigationOption::find(false)
					->andFilterWhere(["=", "investigation_id", $id])
					->andFilterWhere(["=", "investigation_question_id", $investigationQuestion['kid']])
					->orderBy("sequence_number asc")
					->all();
				foreach ($lnInvestigationOptionArr as $investigationOption) {

					$choice_option_obj = [];
					$choice_option_obj['option_title'] = $investigationOption['option_title'];
					$choice_option_obj['sequence_number'] = $investigationOption['sequence_number'];
					$choice_option_obj['kid'] = $investigationOption['kid'];
					array_push($s_choice_obj['options'], $choice_option_obj);
				}

				array_push($survey_result['question'], $s_choice_obj);


			}
		}


		//$survey_result['investigation_question_id']=$investigationQuestionTmp->kid;

		Yii::$app->cache->set($cacheKey, $survey_result);
		return $survey_result;
		}
		

	}


	public function deleteLnInvestigation($id)
	{
		/*
		$lnInvestigation =LnInvestigation::findOne($id);
		$investigationQuestionArr=LnInvestigationQuestion::find(false)
				->andFilterWhere(["=","investigation_id",$id])				
				->all();
		
		foreach ($investigationQuestionArr as $investigationQuestion) {
			$question_type=$investigationQuestion->question_type;
			if($question_type=='2'){
				$investigationQuestion->delete();
			}elseif ($question_type=='3'){
				$investigationQuestion->delete();
			}else{
				$lnInvestigationOptionArr=LnInvestigationOption::find(false)
					->andFilterWhere(["=","investigation_id",$id])
					->andFilterWhere(["=","investigation_question_id",$investigationQuestion->kid])					
					->all();
				foreach ($lnInvestigationOptionArr as $investigationOption) {
					$investigationOption->delete();
				}
				$investigationQuestion->delete();
			}
		}
		$lnInvestigation->delete();
		*/
		$deleteModel = new LnInvestigation();
		if ($deleteModel->findOne($id)->delete()) {

			$this->removeInvestigationQuestion($id);

			$this->removeInvestigationOption($id);
		}

	}

	public function removeInvestigationQuestion($investigation_id)
	{
		$deleteQuestionModel = new LnInvestigationQuestion();
		$params = [
			'investigation_id' => $investigation_id,
		];
		$condition = "investigation_id = :investigation_id";
		$deleteQuestionModel->deleteAll($condition, $params);
	}

	public function removeInvestigationOption($investigation_id)
	{
		$deleteOptionModel = new LnInvestigationOption();
		$params = [
			'investigation_id' => $investigation_id,
		];
		$condition = "investigation_id = :investigation_id";
		$deleteOptionModel->deleteAll($condition, $params);
	}


	public function editSurvey($params)
	{
		$title = Html::encode($params['title']);
		$id = $params['id'];
		$this->editInvestigation($params, self::INVESTIGATION_TYPE_SURVEY, $title, $id);
		$questionArr = $params['question'];
		$investigation_id = $id;

		/*
		$investigationQuestionArr=LnInvestigationQuestion::find(false)
			->andFilterWhere(["=","investigation_id",$id])
			->all();
		
		foreach ($investigationQuestionArr as $investigationQuestion) {
			$question_type=$investigationQuestion->question_type;
			if($question_type=='2'){
				
			}elseif ($question_type=='3'){
			
			}else{				
				$this->removeInvestigationOption($investigation_id,$investigationQuestion->kid);				
			}
		}
		
		$this->removeInvestigationQuestion($investigation_id);
	*/
		$this->removeInvestigationOption($investigation_id);
		$this->removeInvestigationQuestion($investigation_id);

		$num = 0;
		foreach ($questionArr as $question) {
			$num++;
			if ($question['question_type'] == LnInvestigationQuestion::QUESTION_TYPE_QA) {
				$this->saveLnInvestigationQuestionForQuest($question, $investigation_id, $num);
			} elseif ($question['question_type'] == LnInvestigationQuestion::QUESTION_TYPE_PAGE_SPLIT) {
				$this->saveLnInvestigationQuestion($question, $investigation_id, $num);
			} else {
				$invest_quest = $this->saveLnInvestigationQuestion($question, $investigation_id, $num);
				$this->saveLnInvestigationOption($question, $investigation_id, $invest_quest->kid);
			}
		}
	}


	public function copyInvestigations($id)
	{
		$lnInvestigation = LnInvestigation::find(false)
			->andFilterWhere(["=", "kid", $id])
			->asArray()
			->one();
		$lnInvestigation['status'] = InvestigationService::STATUS_TEMP;
		$investigation_type = $lnInvestigation['investigation_type'];

		$lnInvestigation_new = $this->saveInvestigationByCopy($lnInvestigation, $investigation_type, $lnInvestigation['title'] . "[副本]");


		$investigationQuestionArr = LnInvestigationQuestion::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->asArray()
			->all();
		if ($investigation_type == '1') {
			foreach ($investigationQuestionArr as $investigationQuestion) {
				$question_type = $investigationQuestion['question_type'];
				$invest_quest = $this->saveLnInvestigationQuestionVote($investigationQuestion, $lnInvestigation_new->kid, $investigationQuestion['sequence_number']);

				$lnInvestigationOptionArr = LnInvestigationOption::find(false)
					->andFilterWhere(["=", "investigation_id", $id])
					->andFilterWhere(["=", "investigation_question_id", $investigationQuestion['kid']])
					->all();
				foreach ($lnInvestigationOptionArr as $investigationOption) {
					$this->copyLnInvestigationOption($investigationQuestion, $lnInvestigation_new->kid, $invest_quest->kid,
						$investigationOption['option_title'], $investigationOption['sequence_number']);
				}
			}
		} else {
			foreach ($investigationQuestionArr as $investigationQuestion) {
				$question_type = $investigationQuestion['question_type'];
				if ($question_type == LnInvestigationQuestion::QUESTION_TYPE_QA) {
					$this->saveLnInvestigationQuestionForQuestByCopy($investigationQuestion, $lnInvestigation_new->kid, $investigationQuestion['sequence_number']);
				} elseif ($question_type == LnInvestigationQuestion::QUESTION_TYPE_PAGE_SPLIT) {
					$this->saveLnInvestigationQuestion($investigationQuestion, $lnInvestigation_new->kid, $investigationQuestion['sequence_number']);
				} else {
					$invest_quest = $this->saveLnInvestigationQuestionByCopy($investigationQuestion, $lnInvestigation_new->kid, $investigationQuestion['sequence_number']);

					$lnInvestigationOptionArr = LnInvestigationOption::find(false)
						->andFilterWhere(["=", "investigation_id", $id])
						->andFilterWhere(["=", "investigation_question_id", $investigationQuestion['kid']])
						->all();
					foreach ($lnInvestigationOptionArr as $investigationOption) {
						$this->copyLnInvestigationOption($investigationQuestion, $lnInvestigation_new->kid, $invest_quest->kid,
							$investigationOption['option_title'], $investigationOption['sequence_number']);
					}
				}
			}
		}
	}

	public function copyLnInvestigationOption($params, $investigation_id, $investigation_question_id, $option_title, $num)
	{
		$lnInvestigationOption = new LnInvestigationOption();
		$lnInvestigationOption->investigation_id = $investigation_id;
		$lnInvestigationOption->investigation_question_id = $investigation_question_id;
		$lnInvestigationOption->option_title = $option_title;
		$lnInvestigationOption->sequence_number = $num;
		$lnInvestigationOption->save();
	}


	/**
	 * 获取问卷结果
	 * @param $id
	 * @return array
	 */
	public function getInvestigation($id)
	{
		//调查信息
		$lnInvestigation = LnInvestigation::findOne($id);

		//调查问题
		$investigationQuestionArr = LnInvestigationQuestion::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->orderBy("sequence_number asc")
			->all();
		foreach ($investigationQuestionArr as $investigationQuestion) {
			$qusetionarr[] = $investigationQuestion->attributes;

			if ($investigationQuestion->question_type == LnInvestigationQuestion::QUESTION_TYPE_SINGLE || $investigationQuestion->question_type == LnInvestigationQuestion::QUESTION_TYPE_MULTIPLE) {
				//调查选项
				$lnInvestigationOptionArr["$investigationQuestion->kid"] = LnInvestigationOption::find(false)
					->andFilterWhere(["=", "investigation_id", $id])
					->andFilterWhere(["=", "investigation_question_id", $investigationQuestion->kid])
					->orderBy("sequence_number asc")
					->asArray()
					->all();
			}

		}
		$result = $lnInvestigation->attributes;
		foreach ($qusetionarr as $k => $v) {
			if ($v['question_type'] == LnInvestigationQuestion::QUESTION_TYPE_SINGLE || $v['question_type'] == LnInvestigationQuestion::QUESTION_TYPE_MULTIPLE) {
				$qusetionarr[$k]['option'] = $lnInvestigationOptionArr["$v[kid]"];
			}
		}
		$result['question'] = $qusetionarr;

		return $result;
	}

	/**
	 * 获取投票结果
	 * @param $id
	 * @param $courseid
	 * @param $modresid
	 * @param $type
	 * @return mixed
	 */
	public function getVoteresult($id, $courseid, $modresid, $type, $defaultPageSize = 10)
	{
		$model =  LnInvestigationResult::find(false);
		$model->andFilterWhere(["=", "investigation_id", $id])
			->andFilterWhere(["=", "mod_res_id", $modresid])
			->andFilterWhere(["=", "course_id", $courseid])
			->groupBy('user_id');
		$count = $model->count(1);
		if ($count > 0){
			$pages = new TPagination(['defaultPageSize' => $defaultPageSize, 'totalCount' => $count]);
			$voteobject = $model->offset($pages->offset)->limit($pages->limit)->orderBy("created_at desc")->all();
			$vote = array();
			foreach ($voteobject as $k => $v) {
				$vote[$k] = $v->attributes;
				$votetemp = LnInvestigationOption::findOne($v['investigation_option_id']);
				$vote[$k]['option'] = $votetemp['sequence_number'];
				if ($type == LnInvestigation::ANSWER_TYPE_REALNAME) {
					$userModel = FwUserDisplayInfo::findOne(['user_id' => $v->user_id]);
					$vote[$k]['name'] = $userModel->real_name;
					$vote[$k]['orgnization_name_path'] = $userModel->orgnization_name_path;
					$vote[$k]['orgnization_name'] = $userModel->orgnization_name;
					$vote[$k]['position_name'] = $userModel->position_name;
				}
			}
		}else{
			$vote = null;
			$pages = null;
		}

		return ['data' => $vote, 'pages' => $pages, 'count' => $count];
	}

    /**
     * 新版课程内调查结果查询
     * @param $id
     * @param $courseId
     * @param $modResId
     * @param $type
     * @param int $defaultPageSize
     * @param null $status
     * @param null $keywords
     * @return array
     */
	public function getVoteResultComplete($id, $courseId, $modResId, $type, $defaultPageSize = 10, $status = null, $keywords = null){
	    $regTable = LnCourseReg::tableName();
	    $resCompleteTableName = LnResComplete::tableName();
        $userTableName = FwUserDisplayInfo::tableName();

	    /*$resComplete = LnResComplete::find(false)
            ->andFilterWhere(['=', 'course_id' => $courseId])
            ->andFilterWhere(['=', 'mod_res_id', $modResId])
            ->andFilterWhere(['=', 'complete_type', LnResComplete::COMPLETE_TYPE_FINAL]);*/
	    $model = LnCourseReg::find(false);
        $model->andFilterWhere(['=', $regTable.'.course_id', $courseId])
            ->andFilterWhere(['=', $regTable.'.reg_state', LnCourseReg::REG_STATE_APPROVED]);
        /*关联课件完成表*/
        $model->leftJoin($resCompleteTableName, $resCompleteTableName.'.user_id='.$regTable.".user_id and ".$resCompleteTableName.".complete_type=".LnResComplete::COMPLETE_TYPE_FINAL." and ".$resCompleteTableName.".mod_res_id='".$modResId."' and ".$resCompleteTableName.".course_id=".$regTable.".course_id");

        /*状态查询*/
        if (!is_null($status)){
            if ($status == LnResComplete::COMPLETE_STATUS_NOTSTART){
                $model->andWhere(" isnull(".$resCompleteTableName.".complete_status) OR ".$resCompleteTableName.".complete_status=".$status);
                //$model->andFilterWhere('or', ['isnull', $resCompleteTableName.".complete_status"], ['=', $resCompleteTableName.".complete_status", $status]);
            }else{
                $model->andFilterWhere(['=', $resCompleteTableName.".complete_status", $status]);
            }
        }
        $model->leftJoin($userTableName, $userTableName.".user_id=".$regTable.".user_id");
        /*用户名邮箱工号查询*/
        if (!empty($keywords)){
            $model->andFilterWhere(['or', ['like', 'real_name', $keywords], ['like', 'email', $keywords], ['like', 'user_no', $keywords]]);
        }

        $count = $model->count(1);

        if ($count > 0){
            $pages = new TPagination(['defaultPageSize' => $defaultPageSize, 'totalCount' => $count]);
            $model->offset($pages->offset)->limit($pages->limit)->orderBy($regTable.".created_at");
            $result = $model->select([
                $userTableName.'.real_name as name',
                $userTableName.'.orgnization_name_path',
                $userTableName.'.orgnization_name',
                $userTableName.'.position_name',
                $userTableName.'.user_id',
                $resCompleteTableName.'.complete_status'
            ])->asArray()->all();
            $vote = array();
            foreach ( $result as $k => $item){
                $vote[$k] = $item;
                $investigationData = LnInvestigationResult::find(false)
                    ->andFilterWhere(["=", "investigation_id", $id])
                    ->andFilterWhere(["=", "mod_res_id", $modResId])
                    ->andFilterWhere(["=", "course_id", $courseId])
                    ->andFilterWhere(['=', 'user_id', $item['user_id']])
                    ->select('created_at,investigation_option_id')
                    ->one();
                if (!empty($investigationData)) {
                    $votetemp = LnInvestigationOption::findOne($investigationData->investigation_option_id);
                    $vote[$k]['option'] = $votetemp['sequence_number'];
                }else{
                    $vote[$k]['option'] = "";
                }
            }
        }else{
            $vote = null;
            $pages = null;
        }
        return ['data' => $vote, 'pages' => $pages, 'count' => $count];
    }

	/**
	 * 获取投票统计
	 * @param $id
	 * @param $courseid
	 * @param $modresid
	 * @return int|string
	 */
	public function getVotecount($id, $courseid, $modresid)
	{
		$countvote = LnInvestigationResult::find(false)
			->andFilterWhere(["=", "investigation_option_id", $id])
			->andFilterWhere(["=", "mod_res_id", $modresid])
			->andFilterWhere(["=", "course_id", $courseid])
			->count('kid');
		return $countvote;
	}

	public function getVoteuser($courseid, $modresid)
	{
		$countvote = LnInvestigationResult::find(false)
			->andFilterWhere(["=", "mod_res_id", $modresid])
			->andFilterWhere(["=", "course_id", $courseid])
			->count('created_by');
		return $countvote;
	}

	/**
	 * 获取调查结果
	 * @param $id
	 * @param $courseid
	 * @param $modresid
	 * @param $type
	 * @param $size
	 * @param $page
	 * @return mixed
	 */
	public function getQuestionaireresult($id, $courseid, $modresid, $type, $defaultPageSize = 10)
	{
		$model = LnInvestigationResult::find(false);
		$model->andFilterWhere(["=", "investigation_id", $id])
			->andFilterWhere(["=", "mod_res_id", $modresid])
			->andFilterWhere(["=", "course_id", $courseid])
			->orderBy('created_at')
			->select('user_id,created_at')
			->groupBy('user_id');
		$count = $model->count(1);
		if ($count > 0){
			$pages = new TPagination(['defaultPageSize' => $defaultPageSize, 'totalCount' => $count]);
			$questionaireobject = $model->offset($pages->offset)->limit($pages->limit)->all();
			foreach ($questionaireobject as $k => $v) {
				$questionaire[$k] = $v->attributes;
				if ($type == LnInvestigation::ANSWER_TYPE_REALNAME) {
					$userModel = FwUserDisplayInfo::findOne(['user_id' => $v->user_id]);
					$questionaire[$k]['name'] = $userModel->real_name;
					$questionaire[$k]['orgnization_name_path'] = $userModel->orgnization_name_path;
					$questionaire[$k]['orgnization_name'] = $userModel->orgnization_name;
					$questionaire[$k]['position_name'] = $userModel->position_name;
				}
			}
		}else{
			$questionaire = null;
			$pages = null;
		}

		return ['data' => $questionaire, 'pages' => $pages, 'count' => $count];
	}

	/**
	 * 获取调查详情
	 * @param $id
	 * @param $user_id
	 * @param $courseid
	 * @param $modresid
	 * @return array
	 */
	public function getQuestionairedetail($id, $user_id, $courseid, $modresid)
	{
		$questionaireobject = LnInvestigationResult::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->andFilterWhere(["=", "user_id", $user_id])
			->andFilterWhere(["=", "mod_res_id", $modresid])
			->andFilterWhere(["=", "course_id", $courseid])
			->orderBy("created_at desc")
			->all();

		foreach ($questionaireobject as $k => $v) {
			$questionaire[] = $v->attributes;
			if ($questionaire['question_type'] == LnInvestigationQuestion::QUESTION_TYPE_SINGLE || $questionaire['question_type'] == LnInvestigationQuestion::QUESTION_TYPE_MULTIPLE) {
				$questionaire[$k]['answer_category'] = 0;
				//$questionaire[$k]['option_title'] = LnInvestigationOption::findOne($v['investigation_option_id'])->option_title;
				//$questionaire[$k]['sequence_number'][] = LnInvestigationOption::findOne($v['investigation_option_id'])->sequence_number;
			} elseif ($questionaire['question_type'] == LnInvestigationQuestion::QUESTION_TYPE_PAGE_SPLIT) {
				$questionaire[$k]['answer_category'] = 1;
			} else {
				$questionaire[$k]['answer_category'] = 2;
			}

		}
		return $questionaire;
	}

	//获取调查详情
	public function countQuestionaireresult($id, $courseid, $modresid)
	{
		$count = LnInvestigationResult::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->andFilterWhere(["=", "mod_res_id", $modresid])
			->andFilterWhere(["=", "course_id", $courseid])
			->count('distinct(user_id)');
		return $count;
	}

}