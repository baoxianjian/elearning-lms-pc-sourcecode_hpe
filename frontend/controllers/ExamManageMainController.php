<?php
namespace frontend\controllers;

use common\models\learning\LnCourse;
use common\models\learning\LnExamination;
use common\models\learning\LnExaminationPaper;
use common\models\learning\LnExaminationPaperCopy;
use common\models\learning\LnExamPaperQuestion;
use common\models\learning\LnExaminationPaperUser;
use common\models\learning\LnExamQuestionCategory;
use common\models\learning\LnExaminationResultUser;
use common\models\learning\LnModRes;
use common\models\learning\LnRelatedUser;
use common\models\learning\LnResComplete;
use common\models\treemanager\FwTreeNode;
use common\services\framework\DictionaryService;
use common\models\learning\LnExaminationQuestion;
use common\models\learning\LnExamQuestionOption;
use common\services\framework\PointRuleService;
use common\services\learning\CourseCompleteService;
use common\services\learning\CourseService;
use common\services\learning\ExaminationCategoryService;
use common\services\learning\ExaminationQuestionCategoryService;
use common\services\framework\TagService;
use common\services\learning\ExaminationQuestionOptionService;
use common\services\learning\ExaminationService;
use common\services\learning\ExamPaperManageService;
use common\services\framework\TreeNodeService;
use common\services\framework\UserService;
use common\base\BaseActiveRecord;
use common\helpers\TExportHelper;
use common\helpers\TStringHelper;
use Yii;
use frontend\base\BaseFrontController;
use yii\data\Pagination;
use common\models\framework\FwUser;
use yii\db\Expression;
use yii\helpers\Html;
use yii\log\Logger;
use yii\web\Response;
use components\widgets\TPagination;
use common\helpers\TTimeHelper;
use yii\helpers\ArrayHelper;
use common\services\learning\ExaminationQuestionService;
use SAML2\XML\mdrpi\PublicationInfo;
use common\helpers\TFileModelHelper;

class ExamManageMainController extends BaseFrontController
{

	const LEARNING_DURATION = "30";//太快会影响性能

	public $layout = 'frame';

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['access']['except'] = ['update-duration','submit-result','play-reuslt'];

		return $behaviors;
	}

	public function actionIndex()
	{
		return $this->render('index');
	}

	/**
	 * 考试列表
	 * @return string
	 */
	public function actionList(){
		$examQuestionService = new ExaminationService();
		$params = Yii::$app->request->getQueryParams();
		$pageSize = $this->defaultPageSize;
		if (Yii::$app->request->getQueryParam('PageSize') != null) {
			$pageSize = Yii::$app->request->getQueryParam('PageSize');
		}
		$dataProvider = $examQuestionService->search($params);
		$count = $dataProvider->totalCount;
		$page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
		$dataProvider->setPagination($page);
		return $this->renderAjax('list',[
			'page' => $page,
			'dataProvider' => $dataProvider,
			'pageSize' => $pageSize,
			'params' => $params,
		]);
	}

	/**
	 * 添加考试
	 * @return string
	 */
	public function actionNewExam(){

		$model = new LnExamination();
		$examService = new ExaminationService();
		//$paperList = $examService->searchPaper();
		$tree_node_id = Yii::$app->request->get('tree_node_id');
		$category_id = $examService->getTreeNodeIdToCategoryId($tree_node_id);

		return $this->renderAjax('new_exam',[
			'model' => $model,
			//'paper_list' => $paperList,
			'category_id' => $category_id,
		]);
	}

	/**
	 * 查询试卷
	 * @return array
	 */
	public function actionGetPaper(){
		Yii::$app->response->format = Response::FORMAT_JSON;
//		$examination_mode = Yii::$app->request->get('examination_mode');
		$examination_mode = null;
		$examService = new ExaminationService();
		$paperList = $examService->searchPaper($examination_mode);
		return ['result' => 'success', 'data' => $paperList];
	}

	/**
	 * 考试编辑
	 * @param $id
	 * @return string
	 */
	public function actionExamEdit($id){
		$model = LnExamination::findOne($id);
		$examService = new ExaminationService();
		$paperList = $examService->searchPaper();
		return $this->renderAjax('new_exam',[
			'model' => $model,
			'paper_list' => $paperList,
			'category_id' => $model->category_id,
		]);
	}

	/**
	 * 试卷详情
	 * @param $id
	 * @return string
	 */
	public function actionDetail($id){
		$model = LnExamination::findOne($id);
		$paper = LnExaminationPaperCopy::findOne($model->examination_paper_copy_id);
		return $this->renderAjax('detail',[
			'model' => $model,
			'paper' => $paper,
		]);
	}

	/**
	 * 发布考试
	 * @param $id
	 * @return array
	 */
	public function actionExamPublish($id){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$model = LnExamination::findOne($id);
		if (empty($model->kid)){
			return ['result' => 'fail', 'errmsg' => Yii::t('frontend','temp_no_data')];
		}

		LnExamination::updateAll(['release_status'=>self::STATUS_FLAG_NORMAL],'kid = :kid', [':kid'=>$model->kid]);
		LnExamination::removeFromCacheByKid($model->kid);/*清除缓存*/
		return ['result' => 'success', 'errmsg' => ''];
	}

	/**
	 * 删除考试
	 * @param $id
	 * @return array
	 */
	public function actionExamDelete($id){
		Yii::$app->response->format = Response::FORMAT_JSON;
		if (empty($id)){
			return ['result' => 'fail', 'errmsg' => Yii::t('frontend','exam_network_err')];
		}
		$model = LnExamination::findOne($id);
		if ($model){
			$service = new ExaminationService();
			$service->deleteExamRelation($model);
			/*停用标签关系*/
			$tagService = new TagService();
			$tagService->stopCourseRelationShip($model->kid);

			return ['result' => 'success', 'errmsg' => ''];
		}else{
			return ['result' => 'fail', 'errmsg' => Yii::t('frontend','data_is_delete')];
		}
	}

	/**
	 * 添加考试
	 * @param null $id
	 * @return array
	 */
	public function actionExamAdd($id = null){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$examService = new ExaminationService();
		if (!empty($id)){
			$model = LnExamination::findOne($id);

			$examService->deleteExamRelation($model,false);
		}else{
			$model = new LnExamination();
			$model->company_id = Yii::$app->user->identity->company_id;
			$model->code = $examService->setExamCode();
		}
		$post = Yii::$app->request->post();
		if (Yii::$app->request->isPost && $model->load($post)){
			/*xss*/
			$temp_title = TStringHelper::clean_xss($model->title);
			if (empty($temp_title) && !empty($model->title)){
				return ['result' => 'fail', 'errmsg' => Yii::t('frontend','{value}_lillegal_char',['value'=> Yii::t('frontend','exam_title')])];
			}
			$model->title = TStringHelper::clean_xss($model->title);
			$model->description = TStringHelper::clean_xss($model->description);
			$model->pre_description = TStringHelper::clean_xss($model->pre_description);
			$model->after_description = TStringHelper::clean_xss($model->after_description);
			if ($model->examination_mode == LnExamination::EXAMINATION_MODE_TEST){
				$model->random_number = $post['random_number_0'];
				$model->each_page_number = $post['each_page_number_0'];
				if ($model->examination_range == LnExamination::EXAMINATION_RANGE_SELF) {
					$model->start_at = !empty($model->start_at) ? strtotime($model->start_at) : null;
					$model->end_at = !empty($model->end_at) ? strtotime($model->end_at) : null;
				}else{
					$model->start_at = $model->end_at = null;
				}
				if ($model->random_mode == LnExamination::RANDOM_MODE_NO && isset($post['LnExamination']['question_disorder'])){
					$model->question_disorder = LnExamination::QUESTION_DISORDER_YES;
				}else{
					$model->question_disorder = LnExamination::QUESTION_DISORDER_NO;
				}
				if ($model->random_mode == LnExamination::RANDOM_MODE_NO && isset($post['LnExamination']['option_disorder'])){
					$model->option_disorder = LnExamination::OPTIOIN_DISORDER_YES;
				}else{
					$model->option_disorder = LnExamination::OPTIOIN_DISORDER_NO;
				}
			}else{
				$model->start_at = $model->end_at = $model->limit_time = $model->limit_attempt_number = $model->pass_grade = null;
				$model->question_disorder = LnExamination::QUESTION_DISORDER_NO  ;
				$model->option_disorder = LnExamination::OPTIOIN_DISORDER_NO;
				$model->answer_view = LnExamination::ANSWER_VIEW_NO;
				$model->random_mode = LnExamination::RANDOM_MODE_YES;
				$model->random_number = $post['random_number_1'];
				$model->each_page_number = $post['each_page_number_1'];
				$model->attempt_strategy = LnExamination::ATTEMPT_STRATEGY_LAST;
			}
			$model->result_output_type = LnExamination::RESULT_OUTPUT_TYPE_NO;
			$model->examination_version = $examService->setExamVersion($model->kid);
			//开始各种copy
			$service = new ExaminationService();
			$result = $service->copyExamination($model);
			if (isset($result['errmsg'])){
				return ['result' => 'fail', 'errmsg' => Yii::t('frontend','examination_copy_failed')];
			}
			$model->examination_paper_copy_id = $result;
			if ($model->save()!==false){
				return ['result' => 'success', 'errmsg' => ''];
			}else{
				return ['result' => 'fail', 'errmsg' => Yii::t('frontend','edit_failed')];
			}
		}else{
			return ['result' => 'fail', 'errmsg' => Yii::t('frontend','exam_network_err')];//非法操作
		}
	}

	/**
	 * 考试预览
	 * @param string $id
	 * @return string
	 */
	public function actionPreviewExam($id = "", $preview = "add"){
		$data = Yii::$app->request->post();
		$model = $id ? LnExamination::findOne($id) : new LnExamination();
		if (Yii::$app->request->isPost){
			$model->load($data);
		}

		if ($preview == 'add' && $model->examination_mode == LnExamination::EXAMINATION_MODE_EXERCISE){
			$model->random_number = $data['random_number_1'];
			$model->each_page_number = $data['each_page_number_1'];
		}

		if ($preview == 'add'){
			$examination_paper_id = $model->examination_paper_id;
		}else{
			$examination_paper_id = $model->examination_paper_copy_id;
		}

		return $this->renderAjax('preview_exam',[
			'model' => $model,
			'examination_paper_id' => $examination_paper_id,
			'preview' => $preview,
			'examination_mode' => $model->examination_mode,
			'random_mode' => $model->random_mode,
		]);
	}

	/**
	 * 试卷预览
	 * @return string
	 */
	public function actionGetPaperQuestion($preview = 'add'){
		$examination_paper_id = Yii::$app->request->get('examination_paper_id');
		$examination_mode = Yii::$app->request->get('examination_mode');
		$random_mode = Yii::$app->request->get('random_mode');
		$random_number = Yii::$app->request->get('random_number');
		$each_page_number = Yii::$app->request->get('each_page_number');
		$question_disorder = Yii::$app->request->get('question_disorder');
		$option_disorder = Yii::$app->request->get('option_disorder');
		$preview = Yii::$app->request->get('preview');
		$examService = new ExaminationService();
		if ($preview == 'add'){
			$questionData = $examService->GetExaminationPaper($examination_paper_id, $examination_mode, $option_disorder);
		}else{
			$questionData = $examService->GetExaminationPaperCopy($examination_paper_id, $examination_mode, $option_disorder);
		}

		if (!empty($questionData) && $question_disorder == LnExamination::QUESTION_DISORDER_YES){
			$questionData = TStringHelper::disorder($questionData, 'options', null);
		}

		if ($random_mode == LnExamination::RANDOM_MODE_YES || $examination_mode == LnExamination::EXAMINATION_MODE_EXERCISE){
			/*2016-01-14修改*/
			foreach ($questionData as $ii => $sval){
				if (empty($sval['options'])){
					unset($questionData[$ii]);
				}
			}
			$count = count($questionData);
			if ($count) {
				if ($random_number < $count) {
					$randData = array_rand($questionData, $random_number);/*随机取数*/
					$resultTemp = [];
					foreach ($randData as $i){
						$resultTemp[] = $questionData[$i];
					}
					$questionData = $resultTemp;
				}
				$each_page_number = $each_page_number > 0 ? $each_page_number : 10;
				$result = [];
				if ($each_page_number < $count) {
					$i = 1;
					foreach ($questionData as $key => $val) {
						$result[] = $val;
						if ($i % $each_page_number == 0) {
							$result[] = array(
								'relation_type' => LnExamPaperQuestion::RELATION_TYPE_HR,
								'options' => null,
							);
						}
						$i ++;
					}
					$end = end($result);
					if (empty($end['options'])){
						array_pop($result);
					}
				}else{
					$result = $questionData;
				}
			}
		}else{
			$result = $questionData;
		}

		return $this->renderAjax('get_paper_question',[
			'examination_mod' => $examination_mode,
			'data' => $result,
			'preview' => $preview,
		]);
	}

	/**
	 * 考试用户信息
	 * @param $id
	 * @return string|void
	 */
	public function actionViewScore($id){
		$find = LnExamination::findOne($id);
		if (empty($find->kid)){
			return ;
		}
		$params = Yii::$app->request->getQueryParams();
		$params['examination_id'] = $find->kid;
		$params['result_type'] = LnExaminationResultUser::RESULT_TYPE_PROCESS;
		$params['attempt_strategy'] = $find->attempt_strategy;
		$params['defaultPageSize'] = $this->defaultPageSize;
		$examService = new ExaminationService();
		/*统计推送*/
		$relatedCount = $examService->getRelatedUserCount($find->kid);
		/*完成*/
		$completeCount = $examService->getResultUserCount($find->kid,LnExaminationResultUser::EXAMINATION_STATUS_END,LnExaminationResultUser::RESULT_TYPE_PROCESS);
		/*进行中*/
		$processCount = $examService->getResultUserCount($find->kid, LnExaminationResultUser::EXAMINATION_STATUS_START, LnExaminationResultUser::RESULT_TYPE_PROCESS);
		/*未开始*/
		$notCount = $examService->getExaminationUserInfoResult($find->kid, array('result_type' => LnExaminationResultUser::RESULT_TYPE_PROCESS, 'examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_NOT), 'count');
		$result = $examService->getExaminationUserInfoResult($find->kid, $params);

		return $this->renderAjax('view_score', [
			'model' => $find,
			'data' => $result['data'],
			'page' => $result['page'],
			'params' => $params,
			'nowPage' => $params[$result['page']->pageParam],
			'relatedCount' => $relatedCount,
			'completeCount' => $completeCount,
			'processCount' => $processCount,
			'notCount' => $notCount,
		]);
	}

	/**
	 * 查询用户的考试记录
	 * @param $id
	 * @param $userId
	 * @param $companyId
	 * @return string
	 */
	public function actionViewLog($id, $userId, $companyId, $courseId = null, $modId = null, $modResId = null, $courseactivityId = null){
		$model = LnExamination::findOne($id);
		$service = new ExaminationService();
		/*if (!empty($courseId)){
			$courseService = new CourseService();
			$courseRegId = $courseService->getUserRegInfo($userId, $courseId)->kid;
			$courseCompleteService = new CourseCompleteService();
			$attempt = $courseCompleteService->getLastAttempt($courseRegId);
		}else{*/
			$attempt = null;
		/*}*/

		$userResultAll = $service->GetExaminationByUserResultAll($userId, $id, $companyId, $modId, $modResId, $courseId, $attempt);
		$user = FwUser::findOne($userId);
		return $this->renderAjax('view_log', [
			'user' => $user,
			'userResultAll' => $userResultAll,
			'examination' => $model,
		]);
	}

	/**
	 * 导出考试用户信息
	 * @param $id
	 */
	public function actionExportExamUser($id){
		$find = LnExamination::findOne($id);
		if (empty($find->kid)){
			return ;
		}
		$params = Yii::$app->request->getQueryParams();
		$params['examination_id'] = $find->kid;
		$params['result_type'] = LnExaminationResultUser::RESULT_TYPE_PROCESS;
		$params['attempt_strategy'] = $find->attempt_strategy;
		$params['defaultPageSize'] = $this->defaultPageSize;
		$params['export'] = true;
		$examService = new ExaminationService();
		$result = $examService->getExaminationUserInfoResult($find->kid, $params);
		$header = Yii::t('common','real_name').",".Yii::t('common','user_email').",".Yii::t('common','mobile').",".Yii::t('common','status').",".Yii::t('common','complete_end_at').",".($find->examination_mode == LnExamination::EXAMINATION_MODE_EXERCISE ? Yii::t('common','complete_correct_rate') : Yii::t('common','complete_grade'));
		$data = array();
		if (!empty($result['data'])){
			$i = 0;
			foreach ($result['data'] as $item){
				$data[$i][0] = $item['real_name'];
				$data[$i][1] = $item['email'];
				$data[$i][2] = $item['mobile_no'];
				if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_NOT || is_null($item['examination_status'])){
					$data[$i][3] = Yii::t('frontend','complete_status_nostart');
					$data[$i][4] = '--';
				}else if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_START){
					$data[$i][3] = Yii::t('frontend','complete_status_doing');
					$data[$i][4] = '--';
				}else if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_END){
					$data[$i][3] = Yii::t('common','status_2');
					$data[$i][4] = date('Y年m月d日', $item['end_at']);
				}
				if ($find->examination_mode == LnExamination::EXAMINATION_MODE_TEST){
					if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_END ){
						$data[$i][5] = $item['examination_score'];
					}else{
						$data[$i][5] = '--';
					}
				}else{
					if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_END ){
						$data[$i][5] = $item['correct_rate'];
					}else{
						$data[$i][5] = '--';
					}
				}
				$i++;
			}
		}

		TExportHelper::exportCsv($header, $data, $find->title);

	}

	/**
	 * 题库
	 * @return string
	 */
	public function actionQuestion()
	{
		return $this->render('question');
	}

	/**
	 * 弹出页面
	 * @return string
	 */
	public function actionNewExamQuestion(){
		$tree_node_id = Yii::$app->request->get('tree_node_id');
		$examQuestionService = new ExaminationQuestionService();
		$category_id = $examQuestionService->getTreeNodeIdToCategoryId($tree_node_id);
		$new_exam_question = Yii::$app->request->get('new_exam_question');
		$model = new LnExaminationQuestion();
		$dictionaryService = new DictionaryService();
		$dictionary_list = $dictionaryService->getDictionariesByCategory('examination_question_level');

		return $this->renderAjax('new_exam_question',[
			'new_exam_question' => $new_exam_question,
			'category_id' => $category_id,
			'model' => $model,
			'dictionary_list' => $dictionary_list,
		]);
	}

	/**
	 * 编辑弹窗
	 * @param null $id
	 * @return string|void
	 */
	public function actionExamQuestionEdit($id=null){
		if (empty($id)){
			return ;
		}
		$model = LnExaminationQuestion::findOne($id);
		if (!$model){
			return ;//数据不存在
		}
		if ($model->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO){
			$new_exam_question = 'select';
		}else if ($model->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX){
			$new_exam_question = 'select';
		}else if ($model->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE){
			$new_exam_question = 'judge';
		}
		$option_list = LnExamQuestionOption::find(false)->andFilterWhere(['examination_question_id'=>$id])->orderBy('sequence_number')->all();

		$tagService = new TagService();
		$tags_list = $tagService->getTagValue($model->kid);
		$tags = array();
		if ($tags_list){
			foreach ($tags_list as $items){
				$tags[] = array('kid' => $items->kid, 'title' => urlencode($items->tag_value));
			}
			$tags = array('results' => $tags);
		}
		$tags = urldecode(json_encode($tags));

		$dictionaryService = new DictionaryService();
		$dictionary_list = $dictionaryService->getDictionariesByCategory('examination_question_level');

		return $this->renderAjax('new_exam_question',[
			'new_exam_question' => $new_exam_question,
			'category_id' => $model->category_id,
			'model' => $model,
			'option_list' => $option_list,
			'tags' => $tags,
			'dictionary_list' => $dictionary_list,
		]);
	}

	/**
	 * 删除试题操作
	 * @param null $id
	 * @return array
	 */
	public function actionExamQuestionDelete($id=null){
		Yii::$app->response->format = Response::FORMAT_JSON;
		if (empty($id)){
			return ['result' => 'fail', 'errmsg' => Yii::t('frontend','exam_network_err')];
		}
		$model = LnExaminationQuestion::findOne($id);
		if ($model){
			$model->delete();
			LnExamQuestionOption::deleteAll("`examination_question_id`='{$id}'");
			/*停用标签关系*/
			$tagService = new TagService();
			$tagService->stopCourseRelationShip($model->kid);
			return ['result' => 'success', 'errmsg' => ''];
		}else{
			return ['result' => 'fail', 'errmsg' => Yii::t('frontend','data_is_delete')];
		}
	}

	/**
	 * 试题列表
	 * @return string
	 */
	public function actionQuestionList(){
		$examQuestionService = new ExaminationQuestionService();
		$params = Yii::$app->request->get();
		$pageSize = $this->defaultPageSize;
		if (Yii::$app->request->getQueryParam('PageSize') != null) {
			$pageSize = Yii::$app->request->getQueryParam('PageSize');
		}
		$dataProvider = $examQuestionService->search($params);
		$count = $dataProvider->totalCount;
		$page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
		$dataProvider->setPagination($page);
		return $this->renderAjax('question_list',[
			'page' => $page,
			'dataProvider' => $dataProvider,
			'pageSize' => $pageSize,
			'keywords' => isset($params['keywords']) ? $params['keywords'] : "",
			'params' => $params,
		]);
	}

	/**
	 * 添加试题
	 * 注：单选只要得分大于0就是正确答案
	 * @return array
	 */
	public function actionQuestionAdd(){
		Yii::$app->response->format = Response::FORMAT_JSON;
		if (!Yii::$app->request->isPost){
			return ['result' => 'fail', 'errmsg' => Yii::t('frontend','data_submit_illegal')];
		}
		$kid = Yii::$app->request->post('kid');
		$examination_question_type = Yii::$app->request->post('examination_question_type');
		$category_id = Yii::$app->request->post('category_id');
		$title = TStringHelper::clean_xss(Yii::$app->request->post('title'));
		if (empty($title)){
			return ['result' => 'fail', 'errmsg' => Yii::t('frontend','{value}_lillegal_char',['value'=> Yii::t('frontend','examination_title')])];
		}
		$options = Yii::$app->request->post('options');
		$answer = Yii::$app->request->post('answer');
		$question_answer = TStringHelper::clean_xss(Yii::$app->request->post('question_answer'));
		$question_option = array();
		if ($options && $answer){
			$is_allow_change_score = 1;
			$more = 0;
			foreach ($options as $key=>$items){
				$option_title = TStringHelper::clean_xss($items);
				if (empty($option_title)){
					return ['result' => 'fail', 'errmsg' => Yii::t('frontend','{value}_not_null',['value'=>Yii::t('common','option_title')])];
				}
				$question_option[$key]['option_title'] = $option_title;
				$question_option[$key]['default_score'] = $answer[$key];
				$question_option[$key]['sequence_number'] = $key+1;
				if ($examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX && $answer[$key] == LnExamQuestionOption::IS_RIGHT_OPTION_YES){
					$question_option[$key]['is_right_option'] = LnExamQuestionOption::IS_RIGHT_OPTION_YES;
				}else if ($examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO && intval($answer[$key]) > 0 ){
					$question_option[$key]['is_right_option'] = LnExamQuestionOption::IS_RIGHT_OPTION_YES;
				}else{
					$question_option[$key]['is_right_option'] = LnExamQuestionOption::IS_RIGHT_OPTION_NO;
				}
				if ($answer[$key] > 0){
					$more ++;
				}
			}
			if ($examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO && $more > 1){
				$is_allow_change_score = 0;
			}
		}else{
			return ['result' => 'fail', 'errmsg' => Yii::t('frontend','exam_network_err')];
		}
		$tags = Yii::$app->request->post('tags');
		$examination_question_level = Yii::$app->request->post('examination_question_level');
		$default_score = Yii::$app->request->post('default_score');

		$companyId = Yii::$app->user->identity->company_id;
		$examinationQuestionService = new ExaminationQuestionService();
		if ($kid){
			$model = LnExaminationQuestion::findOne($kid);
		}else{
			$model = new LnExaminationQuestion();
			$model->company_id = $companyId;
			$model->code = $examinationQuestionService->setExamQuestionCode();
			$model->category_id = $category_id;
		}
		$model->examination_question_type = $examination_question_type;
		$model->title = $title;
		$model->is_allow_change_score = $examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO ? (string)$is_allow_change_score : LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_YES;/*单选多个得分时不可修改默认分*/
		$model->question_version = $examinationQuestionService->setExamQuestionVersion($kid);
		$model->examination_question_level = $examination_question_level;
		$model->default_score = $default_score;
		$model->answer = $question_answer;
		$model->sequence_number = $examinationQuestionService->setSequenceNumber($companyId);
		$model->needReturnKey = true;
		$result = $model->save();
		if ($result!==false){
			/*更新默认分数*/
			LnExamPaperQuestion::updateAll(['default_score'=>$default_score],'examination_question_id=:examination_question_id',[':examination_question_id'=>$model->kid]);
			LnExamPaperQuestion::removeFromCacheByKid($model->kid);/*清除缓存*/
			/*更新标签库*/
			$tagService = new TagService();
			$tagService->stopCourseRelationShip($model->kid);
			if (!empty($tags)) {
				$tagService->addTag($tags, $model->kid, $companyId, 'examination_question-knowledge-point');
			}
			LnExamQuestionOption::deleteAll("`examination_question_id`='{$model->kid}'");
			foreach ($question_option as $i=>$v){
				$optionModel = new LnExamQuestionOption();
				$optionModel->examination_question_id = $model->kid;
				$optionModel->option_title = $v['option_title'];
				$optionModel->default_score = $model->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO ? $v['default_score'] : 0;
				$optionModel->sequence_number = $v['sequence_number'];
				$optionModel->is_right_option = $v['is_right_option'];
//				$optionModel->option_result = null;
				$optionService = new ExaminationQuestionOptionService();
				$optionModel->option_version = $optionService->getExaminationQuestionOptionVersion();
				$optionModel->save();
			}
			return ['result' => 'success', 'errmsg' => ''];
		}else{
			return ['result' => 'fail', 'errmsg' => Yii::t('frontend','exam_network_err')];//数据存储失败
		}
	}

	/**
	 * 判断题添加
	 * 正确答案值为option_result
	 * is_right_option为正确项
	 */
	public function actionQuestionJudgeAdd(){
		Yii::$app->response->format = Response::FORMAT_JSON;
		if (!Yii::$app->request->isPost){
			return ['result' => 'fail', 'errmsg' => Yii::t('frontend','data_submit_illegal')];
		}
		$kid = Yii::$app->request->post('kid');
		$category_id = Yii::$app->request->post('category_id');
		$title = htmlspecialchars(Yii::$app->request->post('title'));
		$description = Yii::$app->request->post('description');
		$is_right_option = Yii::$app->request->post('is_right_option');
		$tags = Yii::$app->request->post('tags');
		$examination_question_level = Yii::$app->request->post('examination_question_level');
		$default_score = Yii::$app->request->post('default_score');
		$question_answer = Yii::$app->request->post('question_answer');

		$companyId = Yii::$app->user->identity->company_id;
		$examinationQuestionService = new ExaminationQuestionService();
		if ($kid){
			$model = LnExaminationQuestion::findOne($kid);
		}else{
			$model = new LnExaminationQuestion();
			$model->company_id = $companyId;
			$model->code = $examinationQuestionService->setExamQuestionCode();
			$model->category_id = $category_id;
		}
		$model->examination_question_type = LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE;
		$model->title = $title;
		$model->is_allow_change_score = LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_YES;
		$model->question_version = $examinationQuestionService->setExamQuestionVersion($kid);
		$model->examination_question_level = $examination_question_level;
		$model->default_score = $default_score;
		$model->description = $description;
		$model->answer = $question_answer;
		$model->sequence_number = $examinationQuestionService->setSequenceNumber($companyId);
		$model->needReturnKey = true;
		if ($model->save()!==false){
			/*更新默认分数*/
			LnExamPaperQuestion::updateAll(['default_score'=>$default_score],'examination_question_id=:examination_question_id',[':examination_question_id'=>$model->kid]);
			LnExamPaperQuestion::removeFromCacheByKid($model->kid);/*清除缓存*/
			/*更新标签库*/
			$tagService = new TagService();
			$tagService->stopCourseRelationShip($model->kid);
			if (!empty($tags)) {
				$tagService->addTag($tags, $model->kid, $companyId, 'examination_question-knowledge-point');
			}
			if ($is_right_option){
				$option = array(1,0);
			}else{
				$option = array(0,1);
			}
			if ($kid){
				LnExamQuestionOption::deleteAll("`examination_question_id`='{$kid}'");
			}
			foreach ($option as $key=>$i){
				$optionModel = new LnExamQuestionOption();
				$optionModel->examination_question_id = $model->kid;
				$optionModel->option_title = $is_right_option == $i ? Yii::t('frontend', 'exam_right') : Yii::t('frontend', 'exam_wrong');
				$optionModel->option_description = $description;
				$optionModel->default_score = 0;
				$optionModel->is_right_option = $is_right_option == $i ? LnExamQuestionOption::IS_RIGHT_OPTION_YES : LnExamQuestionOption::IS_RIGHT_OPTION_NO;
				$optionModel->option_stand_result = $is_right_option;
				$optionModel->sequence_number = $key+1;
				$optionService = new ExaminationQuestionOptionService();
				$optionModel->option_version = $optionService->getExaminationQuestionOptionVersion();
				$optionModel->save();
			}
			return ['result' => 'success', 'errmsg' => ''];
		}else{
			return ['result' => 'fail', 'errmsg' => Yii::t('frontend','exam_network_err')];//数据存储失败
		}
	}

	/**
	 * 试题预览
	 * @param $id
	 * @return string
	 */
	public function actionQuestionView($id){
		$model = LnExaminationQuestion::findOne($id);
		if ($model->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO){
			$new_exam_question = 'select';
		}else if ($model->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX){
			$new_exam_question = 'select';
		}else if ($model->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE){
			$new_exam_question = 'judge';
		}
		$option_list = LnExamQuestionOption::find(false)->andFilterWhere(['examination_question_id'=>$id])->orderBy('sequence_number')->all();

		$tagService = new TagService();
		$tags_list = $tagService->getTagValue($model->kid);
		$tags = array();
		if ($tags_list){
			foreach ($tags_list as $items){
				$tags[] = $items->tag_value;
			}
		}

		$dictionaryService = new DictionaryService();
		$dictionary_list = $dictionaryService->getDictionariesByCategory('examination_question_level');

		return $this->renderAjax('question_view',[
			'new_exam_question' => $new_exam_question,
			'category_id' => $model->category_id,
			'model' => $model,
			'option_list' => $option_list,
			'tags' => $tags,
			'dictionary_list' => $dictionary_list,
		]);
	}

	/**
	 * 添加题库分类
	 * @param null $id
	 * @return string
	 */
	public function actionAddExaminationQuestionCategory($id=null, $tree_node_id = null, $edit = 'False'){
		if ($tree_node_id) {
			$service = new ExaminationService();
			$id = $service->GetQuestionTreeNodeIdToCategoryId($tree_node_id);
		}
		$model = !empty($id) ? LnExamQuestionCategory::findOne($id) : new LnExamQuestionCategory();
		$edit = Yii::$app->request->get('edit');
		$edit = isset($edit) ? 'True' : 'False';

		return $this->renderAjax('add-category-question',[
			'model' => $model,
			'edit' => $edit,
		]);
	}

	/**
	 * 保存试题目录
	 * @return array
	 */
	public function actionSaveExaminationQuestionCategory(){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$category_id = Yii::$app->request->post('category_id');
		$category_name = Yii::$app->request->post('category_name');
		$description = Yii::$app->request->post('description');
		$edit = Yii::$app->request->post('edit');
		if (empty($category_name)){
			return ['result' => 'fail'];
		}
		$companyId = Yii::$app->user->identity->company_id;
		$count = LnExamQuestionCategory::find(false)->andFilterWhere(['<>', 'kid', $category_id])->andFilterWhere([ 'category_name'=>$category_name, 'company_id'=>$companyId, 'status'=>LnExamQuestionCategory::STATUS_FLAG_NORMAL])->count();
		if ($count){
			return ['result' => 'repeat', 'errmsg' => Yii::t('frontend','question_bank_name_isset')];
		}
		if ($edit == 'True'){
			$attribute['category_name'] = $category_name;
			$attribute['description'] = $description;
			LnExamQuestionCategory::updateAll($attribute,'kid=:kid',[':kid'=>$category_id]);
			LnExamQuestionCategory::removeFromCacheByKid($category_id);/*清除缓存*/
			$model = LnExamQuestionCategory::findOne($category_id);
			FwTreeNode::updateAll(['tree_node_name' => $category_name],'kid=:kid', [':kid' => $model->tree_node_id]);
			return ['result' => 'success'];
		}else {
			$tree_parent_id = "";
			if (!empty($parent_category_id)) {
				$parentCategory = LnExamQuestionCategory::findOne($parent_category_id);
				$tree_parent_id = $parentCategory->tree_node_id;
			}
			$treeNodeService = new TreeNodeService();
			$tree_node_id = $treeNodeService->addTreeNode('examination-question-category', $category_name, $tree_parent_id);
			if ($tree_node_id) {
				$treeNode = FwTreeNode::findOne($tree_node_id);
				$examinationQuestionCategory = new LnExamQuestionCategory();
				$examinationQuestionCategory->tree_node_id = $tree_node_id;
				$examinationQuestionCategory->parent_category_id = empty($parent_category_id) ? null : $parent_category_id;
				$examinationQuestionCategory->company_id = $companyId;
				$examinationQuestionCategory->category_code = $treeNode->tree_node_code;
				$examinationQuestionCategory->category_name = $category_name;
				$examinationQuestionCategory->description = $description;
				$examinationQuestionCategory->status = LnExamQuestionCategory::STATUS_FLAG_NORMAL;
				$examinationQuestionCategory->save();
				return ['result' => 'success'];
			} else {
				return ['result' => 'fail'];
			}
		}
	}

	/**
	 * 删除题库分类
	 * @param $tree_node_id
	 * @return array
	 */
	public function actionDeleteQuestionCategory($tree_node_id){
		Yii::$app->response->format = Response::FORMAT_JSON;
		if (empty($tree_node_id)) return ['result'=>'fail','errmsg'=>Yii::t('frontend','audience_params_error')];
		$categoryService = new ExaminationQuestionCategoryService();
		$categoryService->deleteRelateData($tree_node_id);
		return ['result' => 'success', 'errmsg' => ''];
	}

	/**
	 * 计算课程
	 * @return array
	 */
	public function actionPlayResComplete(){
		$params=Yii::$app->request->get();
        $courseComplete=false;
        $getCetification=false;
		$courseId=null;
		$certificationId=null;
		$examinationService=new ExaminationService();
		$examinationService->addResCompleteDoneInfo($params,$courseComplete,$getCetification,$courseId,$certificationId);
		
        //edit by baoxianjian 11:27 2016/3/31
        $pointRuleService=new PointRuleService();
        $pointResult=$pointRuleService->countCourseAndCetificationPoint($courseComplete,$getCetification,$courseId,$certificationId);
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success','pointResult'=>$pointResult];
	}

	/**
	 * 提交考试
	 * @return array
	 */
	public function actionSubmitResult()
	{
		$params=Yii::$app->request->post();
		$examinationService=new ExaminationService();
		$result = $examinationService->SubmitResult($params);
		Yii::$app->response->format = Response::FORMAT_JSON;
		if (isset($result['result']) && $result['result'] == 'fail'){
			return $result;
		}else {
			/*达到合格线后添加积分*/
			$examinationModel = LnExamination::findOne($params['examination_id']);
			if ($examinationModel->examination_range == LnExamination::EXAMINATION_RANGE_SELF && $examinationModel->examination_mode == LnExamination::EXAMINATION_MODE_TEST && $result['score'] >= $examinationModel->pass_grade){
				$pointRuleService = new PointRuleService();
				$pointResult = $pointRuleService->curUserCheckActionForPoint('Pass-Exam', 'Exam', $examinationModel->kid);
			}
			return ['result' => 'success', 'result_id' => $result['result_id'], 'score' => $result['score'], 'pointResult' => $pointResult];
		}
	}

	/**
	 * 记录时间
	 * @return array
	 */
	public function actionUpdateDuration(){
		if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
			Yii::$app->response->format = Response::FORMAT_JSON;
			$params = Yii::$app->request->post();
			$user_id = Yii::$app->user->getId();
			$company_id = Yii::$app->user->identity->company_id;
			/*保持会话*/
			$commonUserService = new UserService();
			$commonUserService->keepOnline($user_id);
			/*更新时长*/
			$service = new ExaminationService();
			$service->updateDuration($params, $user_id, $company_id);
			return ['result' => 'success'];
		}
	}

	/**
	 * 查看考试
	 * @param null $id
	 * @param null $modResId
	 * @return string
	 */
	public function actionPlayView($id = null, $modResId = null){
		$this->layout = 'frame-bigScreenPage';
		$service = new ExaminationService();
		$data = $service->GetExaminationUserPaper($id);
//		var_dump($data['result']);
		$course = LnCourse::findOne($data['model']->course_id);
		$dialog = isset($_GET['dialog']) ? true : false;

		if (empty($modResId)) $modResId = $data['model']->mod_res_id;

		if ($dialog){/*讲师调用页面*/
			return $this->renderAjax('play_view', [
				'model' => $data['model'],
				'examination' => $data['examination'],
				'paperQuestion' => $data['result'],
				'countPage' => $data['page'],
				'modResId' => $modResId,
				'course' => $course,
				'dialog' => true,
			]);
		}else {
			return $this->render('play_view', [
				'model' => $data['model'],
				'examination' => $data['examination'],
				'paperQuestion' => $data['result'],
				'countPage' => $data['page'],
				'modResId' => $modResId,
				'course' => $course,
				'dialog' => false,
			]);
		}
	}

	/**
	 * 考试返回结果
	 * @param $id
	 * @param null $modResId
	 * @param string $mode
	 * @return string
	 */
	public function actionPlayResult($id, $modResId = null, $mode = ''){
		$uid = Yii::$app->user->getId();
		$company_id = Yii::$app->user->identity->company_id;
		$service = new ExaminationService();
		$res = $service->getExaminationPlayResult($id, $uid, $company_id, $modResId, $mode);
		if ($mode == 'course') {
			return $this->renderAjax('play_result', $res);
		}else{
			return $this->render('play_result', $res);
		}
	}

	/**
	 * 删除考试目录
	 * @param $tree_node_id
	 * @return array
	 */
	public function actionDeleteExaminationCategory($tree_node_id){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$service = new ExaminationCategoryService();
		$service->deleteRelateData($tree_node_id);
		return ['result' => 'success'];
	}
	
	/**
	 * 试题导入
	 */
	public function actionQuestionImport(){
		$treeNodeId = Yii::$app->request->get('treeNodeId');
		$examQuestionService = new ExaminationQuestionService();
		$categoryId = $examQuestionService->getTreeNodeIdToCategoryId($treeNodeId);	
		$categoryName = $examQuestionService->getQuestionCategoryName($categoryId);
		if (empty($categoryName)){
			exit();/* 参数错误或分类不存在出现此情况 */
		}
		return $this->renderAjax('question_import', [
				'categoryId' => $categoryId,
				'categoryName' => $categoryName,
		]);
	}
	
	/**
	 * 文件上传
	 */
	public function actionImportFile(){
		//Yii::$app->response->format = Response::FORMAT_JSON;
		$TFileModelHelper = new TFileModelHelper();
		$extension = ['xls'];
		$result = $TFileModelHelper->importExaminationQuestionFile($_FILES['myfile'], $extension);
		if ($result['result'] == 'success') {
			//读入上传文件
			$objPHPExcel = \PHPExcel_IOFactory::load(Yii::$app->basePath .'/../'.$result['errmsg']);
			//excel  sheet个数
			$sheetNumber = $objPHPExcel->getSheetCount();
			if ($sheetNumber != 2){
				$result = ['result' => 'fail', 'errmsg' => Yii::t('frontend','mod_type_iswrong')];
			}else{
				$sheet_0 = $objPHPExcel->getSheet(0)->toArray();
				$keySelect = $sheet_0[0][0];/*选择题*/
				$sheet_1 = $objPHPExcel->getSheet(1)->toArray();
				$keyJudge = $sheet_1[0][0];/*判断题*/
				if ($keySelect == 'EXAM_QUESTION_SELECT' && $keyJudge == 'EXAM_QUESTION_JUDGE'){
					/*特殊标识，判断是否系统提供模板*/
				}else{
					$result = ['result' => 'fail', 'errmsg' => Yii::t('frontend','mod_type_iswrong')];
				}
			}
		}
		return json_encode($result);
		Yii::$app->end();
	}
	
	/**
	 * 确认提交
	 */
	public function actionImportSubmit(){
		Yii::$app->response->format = Response::FORMAT_JSON;
		if (Yii::$app->request->isAjax && Yii::$app->request->isPost){
			$categoryId = Yii::$app->request->post('categoryId');
			$file = Yii::$app->request->post('file');
			$fileName = Yii::$app->request->post('fileName');
			if (!file_exists(Yii::$app->basePath.'/../'.$file)){
				return ['result' => 'fail', 'errmsg' => Yii::$app->basePath.'/../'.$file];
			}else{
				$TFileModelHelper = new TFileModelHelper();
				$TFileModelHelper->copyExaminationQuestionFile($file, $fileName);
				$service = new ExaminationQuestionService();
				$result = $service->readExaminationQuestionFile($categoryId, $file, $fileName);
				return $result;
			}
		}else{
			return ['result' => 'fail', 'errmsg' => ''];
		}
	}
	
}
