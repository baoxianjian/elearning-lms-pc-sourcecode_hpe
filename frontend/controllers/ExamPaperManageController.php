<?php


namespace frontend\controllers;


use common\services\framework\TreeNodeService;
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
use common\services\learning\CoursewareCategoryService;
use common\services\learning\ExamPaperManageService;
use common\models\learning\LnExamPaperCategory;
use common\models\treemanager\FwTreeNode;
use common\services\message\MessageService;
use common\models\framework\FwPrimaryKey;
use common\models\learning\LnExaminationQuestion;
use common\services\framework\DictionaryService;
use common\services\learning\ExaminationQuestionService;





class ExamPaperManageController extends BaseFrontController
{
	//const SLEEP_TIME =8;
	//const SLEEP_MUTI_TIME =4;
	
	public $layout = 'frame';
	
	public function actionIndex()
	{
		return $this->render('index');
	}
	
	/*添加分类*/
	public function actionAddCategory($tree_node_id = null){
		$tree_type_code = Yii::$app->request->get('tree_type_code');
		$catlog = array();
	
		$examPaperManageService = new ExamPaperManageService();
		$companyId = Yii::$app->user->identity->company_id;
		$category = $examPaperManageService->getExaminationPaperCategoryByCompanyIdList(array($companyId));
		if (!empty($category)) {
			foreach ($category as $val) {
				if (empty($val->parent_category_id)) {
					$catlog['parent'][] = $val->attributes;
				} else {
					$catlog['sub'][$val->parent_category_id][] = $val->attributes;
				}
			}
		}
		if ($tree_node_id){
			$category_id = $examPaperManageService->getCategoryIdByTreeNodeId($tree_node_id);
			$model = LnExamPaperCategory::findOne($category_id);
		}else{
			$model = new LnExamPaperCategory();
		}
		
		return $this->renderAjax('add-category',[
			'tree_type_code' => $tree_type_code,
			'catlog' => $catlog,
			'model' => $model,
		]);
	}
	
	
	/*添加试卷分类*/
	public function actionAddExaminationPaperCategory(){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$category_name = Yii::$app->request->post('category_name');
		$parent_category_id = Yii::$app->request->post('parent_category_id');
		$id = Yii::$app->request->post('id');
		if (empty($category_name)){
			return ['result' => 'fail'];
		}
		$companyId = Yii::$app->user->identity->company_id;
		$query = LnExamPaperCategory::find(false)
			->andFilterWhere(['category_name'=>$category_name, 'company_id'=>$companyId, 'status'=>LnExamPaperCategory::STATUS_FLAG_NORMAL]);
		if (!empty($id)){
			$query->andFilterWhere(['<>', 'kid', $id]);
		}
		$count = $query->count();
		if ($count){
			return ['result' => 'reply', 'errmsg' => Yii::t('frontend', 'exist_identical_catalog')];
		}
		$tree_parent_id = "";
		if (!empty($parent_category_id)){
			$parentCategory = LnExamPaperCategory::findOne($parent_category_id);
			$tree_parent_id = $parentCategory->tree_node_id;
		}
		$treeNodeService = new TreeNodeService();
		$parperCategory = empty($id) ? new LnExamPaperCategory() : LnExamPaperCategory::findOne($id);
		if ($id){
			$treeNodeService->updateTreeNode($parperCategory->tree_node_id, $category_name, $tree_parent_id);
			$tree_node_id = $parperCategory->tree_node_id;
		}else{
			$tree_node_id = $treeNodeService->addTreeNode('examination-paper-category', $category_name, $tree_parent_id);
		}

		if ($tree_node_id) {
			$treeNode = FwTreeNode::findOne($tree_node_id);
			$parperCategory->tree_node_id = $tree_node_id;
			$parperCategory->parent_category_id = empty($parent_category_id) ? null : $parent_category_id;;
			$parperCategory->company_id = $companyId;
			$parperCategory->category_code = $treeNode->tree_node_code;
			$parperCategory->category_name = $category_name;
			$parperCategory->status = LnExamPaperCategory::STATUS_FLAG_NORMAL;
			if ($id){
				$parperCategory->update();
			}else {
				$parperCategory->save();
			}
			return ['result' => 'success'];
		}else{
			return ['result' => 'fail'];
		}
	}

	/**
	 * 删除试卷目录
	 * @param $tree_node_id
	 * @return array
	 */
	public function actionDeletePaperCategory($tree_node_id){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$service = new ExamPaperManageService();
		$service->deleteRelateData($tree_node_id);
		return ['result' => 'success'];
	}
	
	
	public function actionNewExamPaper($id)
	{
		$examPaperManageService = new ExamPaperManageService();
		
		$cid=$examPaperManageService->getCategoryIdByTreeNodeId($id);
		$dictionaryService = new DictionaryService();
		$dictionary_list = $dictionaryService->getDictionariesByCategory('examination_question_level');
		return $this->renderAjax('new_exam_paper',['cid'=>$cid,'dictionary_list' => $dictionary_list]);
	}
	
	public function actionAddExamPaperTmp()
	{
		$params=Yii::$app->request->post();
	
		return $this->render('new_exam_paper_question',['examPaper'=>$params,'current_time'=>time()]);
	}
	
	public function actionGetAllQuestionCategorys()
	{
		$companyId = Yii::$app->user->identity->company_id;
		$examPaperManageService = new ExamPaperManageService();
		$tmp=$examPaperManageService->getExaminationQuestionCategory($companyId,"");
		Yii::$app->response->format = Response::FORMAT_JSON;
	
		$arr_title=[];
		foreach ($tmp as $r){
			$t['title']=$r['category_name'];
			$t['uid']=$r['kid'];
			Array_push($arr_title,$t);
		}
		$result['results']=$arr_title;
		$result['success']=true;
		return $result;
	
	}
	
	public function actionGetQuestionCategory()
	{
		$companyId = Yii::$app->user->identity->company_id;
		$examPaperManageService = new ExamPaperManageService();
		$params=Yii::$app->request->get();
		$tmp=$examPaperManageService->getExaminationQuestionCategory($companyId,$params['q']);
		Yii::$app->response->format = Response::FORMAT_JSON;
		
		$arr_title=[];
		foreach ($tmp as $r){
			$t['title']=$r['category_name'];
			$t['uid']=$r['kid'];
			Array_push($arr_title,$t);
		}
		$result['results']=$arr_title;
		$result['success']=true;
		return $result;
		
	}
	
	public function actionGetTags()
	{
		
		$examPaperManageService = new ExamPaperManageService();
		$params=Yii::$app->request->get();
		$tmp=$examPaperManageService->getTag($params['q']);
		Yii::$app->response->format = Response::FORMAT_JSON;
	
		$arr_title=[];
		foreach ($tmp as $r){
			$t['title']=$r['tag_value'];
			$t['uid']=$r['kid'];
			Array_push($arr_title,$t);
		}
		$result['results']=$arr_title;
		$result['success']=true;
		return $result;
	
	}
	
	/**
	 * 编辑试卷中：获取左边列表
	 * @return multitype:multitype:
	 */
	public function actionGetLeftQuestions()
	{
		
		$examPaperManageService = new ExamPaperManageService();
		$get=Yii::$app->request->get();
		$page=$get['page'];
		$current_time=$get['current_time'];
		$params=Yii::$app->request->post();
	
		
		$size = 15;
		$offset=$size < 1 ? 0 : ((int)$page - 1) * $size;
		$params['limit']=$size;
		$params['offset']=$offset;
		$params['current_time']=$current_time;
	
		$result=$examPaperManageService->getQuestions($params);
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $result];
	}
	
	public function actionAddLeftQuestionsTmp()
	{
	
	
		$params=Yii::$app->request->post();
	
		$messageService=new MessageService();
		$mission_id=$params['mission_id'];
		$results=$params['result'];
		foreach($results as $re){
			$messageService->saveLnExamPaperQuestTemp($re['kid'], $mission_id);
		}
		//
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => "sucess"];
	}
	
	
	
	public function actionRemoveRightQuestionsTmp()
	{
		$params=Yii::$app->request->post();
		
		$messageService=new MessageService();
		$mission_id=$params['mission_id'];
		$results=$params['result'];
		
		foreach($results as $re){
			$messageService->deleteExaminationPaperQuestionTemp($re['kid'], $mission_id);
		}
		//
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => "sucess"];
		
		
	}
	
	/**
	 * 试卷编辑中预览试卷
	 */
	public function actionExamPaperQuestionPreview()
	{
		
		return $this->renderAjax('exam_paper_ques_preview');
	}
	
	/**
	 * 首页中预览试卷
	 */
	public function actionViewExamPaper($id)
	{	
		$examPaperManageService=new ExamPaperManageService();
		$paper=$examPaperManageService->getPaper($id);
		return $this->renderAjax('view_exam_paper',['id'=>$id,'paper'=>$paper]);
	}
	
	/**
	 * 首页中预览试卷的数据
	 */
	public function actionViewExamPaperData()
	{
		$params=Yii::$app->request->post();
		$questionArr=[];
		$examPaperManageService=new ExamPaperManageService();
		
		
		$result=$examPaperManageService->getExaminationPaperQuestion($params['id']);
		
		foreach($result as $question){
	
			if($question['relation_type']=='0'){
				$kid=$question['kid'];
				$options=$examPaperManageService->getQuestionOptions($kid);
				$question['options']=$options;
				$question['id']=$kid;
			}else{
				$question['id']= FwPrimaryKey::guid();
				$question['examination_question_type']= Yii::t('frontend','exam_pq_fenye');
				
			}
	
			array_push($questionArr, $question);
		}
	
		$result=[];
		$result['question']=$questionArr;
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $result];
	
	
	}
	
	
	public function actionPreviewPaper()
	{
		$params=Yii::$app->request->post();
        $questionArr=[];
		$examPaperManageService=new ExamPaperManageService();
		foreach($params['result'] as $question){

			if($question['relation_type']=='0'){
				$kid=$question['kid'];
				$options=$examPaperManageService->getQuestionOptions($kid);
				$question['options']=$options;
				$question['id']=$kid;
			}else{
				$question['id']= FwPrimaryKey::guid();
			}

			array_push($questionArr, $question);
		}

		$result=[];
		$result['question']=$questionArr;
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $result];
	

	}
	
	public function actionNewExamPaperSubmit()
	{
		$params=Yii::$app->request->post();
		$examPaperManageService=new ExamPaperManageService();

		$result=$examPaperManageService->getExamPaperStats($params);
		return $this->renderAjax('new_exam_paper_submit',['result'=>$result]);
	}
	
	
	public function actionEditExamPaperSubmit()
	{
		$params=Yii::$app->request->post();
		$examPaperManageService=new ExamPaperManageService();
	
		$result=$examPaperManageService->getExamPaperStats($params);
		return $this->renderAjax('edit_exam_paper_submit',['result'=>$result]);
	}
	
	public function actionEditExamPaperFinalSubmit()
	{
		$params=Yii::$app->request->post();
		$examPaperManageService=new ExamPaperManageService();
		$examPaperManageService->editExamPaper($params);
	
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success'];
	}
	
	public function actionNewExamPaperFinalSubmit()
	{
		$params=Yii::$app->request->post();
		$examPaperManageService=new ExamPaperManageService();
		$examPaperManageService->saveExamPaper($params);
		
		
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
		$service = new ExamPaperManageService();
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
	
	
	public function actionGetExamQuestionExecData(){
		$examPaperManageService = new ExamPaperManageService();
		$params=Yii::$app->request->get();
		$result_array=[];
		
		$examinationPaperBatch = $params['mission_id'];
		$companyId = Yii::$app->user->identity->company_id;
		$questionCategoryId = $params['categlist_val'];
		$questionType = "";
		$keyword = "";
		$questionLevel = "";
		$examinationQuestionService = new ExaminationQuestionService();
		
		$requestNumber = $params['question_num'];
		$userId = Yii::$app->user->getId();
		$result = "";
		$message = "";
		
		$examinationQuestionService->GeneratePaperQuestionByNumber($examinationPaperBatch,$companyId,$questionCategoryId,$questionType,$questionLevel,$keyword,$requestNumber,$userId, $result, $message);
		//sleep(ExamPaperManageController::SLEEP_TIME);
		if($message!='0'){
			$result_array['list']=$examPaperManageService->getQuestionsSimple($params);
			$examPaperManageService->updateExaminationPaperQuestionTemp($params);
			$result_array['message']=$message;
		}else{
			$result_array="false";
		}
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $result_array];
	
	}
	
	public function actionGetExamScoreExecData(){
		$examPaperManageService = new ExamPaperManageService();
		$params=Yii::$app->request->get();
		$result_array=[];
	
		$examinationPaperBatch = $params['mission_id'];
		$companyId = Yii::$app->user->identity->company_id;
		$questionCategoryId = $params['categlist_val'];
		$questionType = "";
		$keyword = "";
		$questionLevel = "";
		$score = $params['question_score'];
		$examinationQuestionService = new ExaminationQuestionService();
	
		
		$userId = Yii::$app->user->getId();
		$result = "";
		$message = "";
	
		$examinationQuestionService->GeneratePaperQuestionByScore($examinationPaperBatch,$companyId,$questionCategoryId,$questionType,$questionLevel,$keyword,$score,$userId, $result, $message);
 
		//sleep(ExamPaperManageController::SLEEP_TIME);
		if($message!='0'){
			$result_array['list']=$examPaperManageService->getQuestionsSimple($params);
			$examPaperManageService->updateExaminationPaperQuestionTemp($params);
			$result_array['message']=$message;
		}else{
			$result_array="false";
		}
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $result_array];
	
	}
	
	public function actionDeleteOne(){
		
		$examPaperManageService = new ExamPaperManageService();
		$params=Yii::$app->request->get();
		$examPaperManageService->deleteExamPaper($params['id']);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => 'success'];
	}
	
	
	
	public function actionGetExamQuestionLevelData(){
		$examPaperManageService = new ExamPaperManageService();
		$params=Yii::$app->request->get();
		
		$result_array=[];
		$fault=[];
	
		$examinationPaperBatch = $params['mission_id'];
		$companyId = Yii::$app->user->identity->company_id;
		$questionCategoryId = $params['categlist_val'];
		$questionType = "";
		$keyword = "";
		$examinationQuestionService = new ExaminationQuestionService();
		//简单
		if(isset($params['easy_input_val'])){
			$params['examination_question_level']=	'easy';	
		
			$questionLevel = "easy";
			$requestNumber = $params['easy_input_val'];
			$userId = Yii::$app->user->getId();
			$result = "";
			$message = "";
				
			$examinationQuestionService->GeneratePaperQuestionByNumber($examinationPaperBatch,$companyId,$questionCategoryId,$questionType,$questionLevel,$keyword,$requestNumber,$userId, $result, $message);
			//sleep(ExamPaperManageController::SLEEP_MUTI_TIME);
			if($message!='0'){
				$result_easy=$examPaperManageService->getQuestionsSimple($params);
				$result_array=array_merge($result_array,$result_easy);
				$examPaperManageService->updateExaminationPaperQuestionTemp($params);
				$fault['easy_input']=$message;
			}else{
				$fault['easy_input']="false";
			}
		}
		
		//中等
		if(isset($params['intermediate_input_val'])){
			$params['examination_question_level']=	'intermediate';
			
			$questionLevel = "intermediate";
			$requestNumber = $params['intermediate_input_val'];
			$userId = Yii::$app->user->getId();
			$result = "";
			$message = "";
			
			$examinationQuestionService->GeneratePaperQuestionByNumber($examinationPaperBatch,$companyId,$questionCategoryId,$questionType,$questionLevel,$keyword,$requestNumber,$userId, $result, $message);
			//sleep(ExamPaperManageController::SLEEP_MUTI_TIME);
			if($message!='0'){
				$result_intermediate=$examPaperManageService->getQuestionsSimple($params);
				$result_array=array_merge($result_array,$result_intermediate);
				$examPaperManageService->updateExaminationPaperQuestionTemp($params);
				$fault['intermediate_input']=$message;
			}else{
				$fault['intermediate_input']="false";
			}
		}
		
		//困难
		if(isset($params['hard_input_val'])){
			$params['examination_question_level']=	'hard';

			$questionLevel = "hard";
			$requestNumber = $params['hard_input_val'];
			$userId = Yii::$app->user->getId();
			$result = "";
			$message = "";

			$examinationQuestionService->GeneratePaperQuestionByNumber($examinationPaperBatch,$companyId,$questionCategoryId,$questionType,$questionLevel,$keyword,$requestNumber,$userId, $result, $message);
			//sleep(ExamPaperManageController::SLEEP_MUTI_TIME);
			if($message!='0'){
				$result_hard=$examPaperManageService->getQuestionsSimple($params);
				$result_array=array_merge($result_array,$result_hard);
				$examPaperManageService->updateExaminationPaperQuestionTemp($params);
				$fault['hard_input']=$message;
			}else{
				$fault['hard_input']="false";
			}
		}
		
		$r=[];
		$r['result']=$result_array;
		$r['fault']=$fault;
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $r];
	
	}
	
	
	
	public function actionGetExamQuestionTypeData(){
		
		$examPaperManageService = new ExamPaperManageService();
		$params=Yii::$app->request->get();
		
		$result_array=[];
		$fault=[];
		
		$examinationPaperBatch = $params['mission_id'];
		$companyId = Yii::$app->user->identity->company_id;
		$questionCategoryId = $params['categlist_val'];
		$questionLevel = "";
		$keyword = "";
		$examinationQuestionService = new ExaminationQuestionService();
		//单选
		if(isset($params['exam_question_type_danxuan_input_val'])){
			$params['examination_question_type']=	LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO;
			$params['num']=$params['exam_question_type_danxuan_input_val'];
			$questionType = "0";
			
			$requestNumber = $params['exam_question_type_danxuan_input_val'];
			$userId = Yii::$app->user->getId();
			$result = "";
			$message = "";
			
			$examinationQuestionService->GeneratePaperQuestionByNumber($examinationPaperBatch,$companyId,$questionCategoryId,$questionType,$questionLevel,$keyword,$requestNumber,$userId, $result, $message);
			//sleep(ExamPaperManageController::SLEEP_MUTI_TIME);
			if($message!='0'){
				$result_easy=$examPaperManageService->getQuestionsSimple($params);
				$result_array=array_merge($result_array,$result_easy);
				$examPaperManageService->updateExaminationPaperQuestionTemp($params);
				$fault['danxuan_input']=$message;
			}else{
				$fault['danxuan_input']="false";
			}
		}
		
		//多选
		if(isset($params['exam_question_type_duoxuan_input_val'])){
			$params['examination_question_type']=	LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX;
			$params['num']=$params['exam_question_type_duoxuan_input_val'];

			$questionType = "1";
			$requestNumber = $params['exam_question_type_duoxuan_input_val'];
			$userId = Yii::$app->user->getId();
			$result = "";
			$message = "";
				
			$examinationQuestionService->GeneratePaperQuestionByNumber($examinationPaperBatch,$companyId,$questionCategoryId,$questionType,$questionLevel,$keyword,$requestNumber,$userId, $result, $message);
			//sleep(ExamPaperManageController::SLEEP_MUTI_TIME);
			if($message!='0'){
				$result_intermediate=$examPaperManageService->getQuestionsSimple($params);
				$result_array=array_merge($result_array,$result_intermediate);
				$examPaperManageService->updateExaminationPaperQuestionTemp($params);
				$fault['duoxuan_input']=$message;
			}else{
				$fault['duoxuan_input']="false";
			}
		}
		
		//判断
		if(isset($params['exam_question_type_panduan_input_val'])){
			$params['examination_question_type']=	LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE;
			$params['num']=$params['exam_question_type_panduan_input_val'];

			$questionType = "3";
			$requestNumber = $params['exam_question_type_panduan_input_val'];
			$userId = Yii::$app->user->getId();
			$result = "";
			$message = "";
			
			$examinationQuestionService->GeneratePaperQuestionByNumber($examinationPaperBatch,$companyId,$questionCategoryId,$questionType,$questionLevel,$keyword,$requestNumber,$userId, $result, $message);
			//sleep(ExamPaperManageController::SLEEP_MUTI_TIME);
			if($message!='0'){
				$result_hard=$examPaperManageService->getQuestionsSimple($params);
				$result_array=array_merge($result_array,$result_hard);
				$examPaperManageService->updateExaminationPaperQuestionTemp($params);
				$fault['panduan_input']=$message;
			}else{
				$fault['panduan_input']="false";
			}
		}
		
		$r=[];
		$r['result']=$result_array;
		$r['fault']=$fault;
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return ['result' => $r];
		
	}
	
	
	
	public function actionEditExamPaperUi($id)
	{
		$examPaperManageService = new ExamPaperManageService();
		$examPaper=$examPaperManageService->findExamPaper($id);
	
	
		$dictionaryService = new DictionaryService();
		$dictionary_list = $dictionaryService->getDictionariesByCategory('examination_question_level');
		return $this->renderAjax('edit_exam_paper',['model'=>$examPaper,'dictionary_list' => $dictionary_list]);
	}
	
	public function actionEditExamPaperTmp()
	{
		$params=Yii::$app->request->post();
	
		return $this->render('edit_exam_paper_question',['examPaper'=>$params,'current_time'=>time()]);
	}
	
	public function actionGetEditExamPaperQuestions()
	{
		$params=Yii::$app->request->get();
		$examPaperManageService = new ExamPaperManageService();
		$result=$examPaperManageService->getExamPaperQuestions($params);
		Yii::$app->response->format = Response::FORMAT_JSON;
	
		return ['result' => $result];
	
	}




	
}
