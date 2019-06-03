<?php

namespace common\services\learning;


use common\models\treemanager\FwTreeNode;
use common\services\framework\TagService;
use common\services\framework\TreeNodeService;
use common\base\BaseActiveRecord;
use Yii;

use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\db\Query;




use common\models\learning\LnExaminationPaper;
use common\models\learning\LnExamPaperCategory;
use common\models\learning\LnExaminationQuestion;

use common\models\learning\LnExamQuestionOption;
use common\models\learning\LnExamPaperQuestion;
use common\models\learning\LnExaminationCategory;
use common\models\learning\LnExamQuestionCategory;
use common\models\framework\FwTag;
use common\models\learning\LnExamPaperQuestTemp;
use yii\helpers\VarDumper;
use yii\helpers\Html;



class ExamPaperManageService extends LnExaminationPaper
{
	const EXAMINATION_QUESTION_TYPE_ALL = '999';

	/**
	 * 获取试卷目录的子目录
	 * @param $categoryid
	 * @return array
	 */
	private function getSubCategories($categoryid){
		$categories = LnExamPaperCategory::findAll(['parent_category_id'=>$categoryid],false);
		$result = [];
		if (!empty($categories)) {
			foreach ($categories as $val) {
				$result[] = $val->kid;
				$result = array_merge($result, $this->getSubCategories($val->kid));
			}
		}
		return $result;
	}

	public function GetExaminationPaperCategoryCount($treeNodeId){
		$model = LnExamPaperCategory::find(false)
				->andFilterWhere(['=','tree_node_id',$treeNodeId])
				->one();

		$categoryAll = $this->getSubCategories($model->kid);
		if (!empty($categoryAll)){
			$categoryAll = array_merge(array($model->kid), $categoryAll);
		}else{
			$categoryAll = array($model->kid);
		}

		$companyId = $model->company_id;
		$count =  LnExaminationPaper::find(false)
			->andFilterWhere(['in','category_id',$categoryAll])
			->andFilterWhere(['=','company_id',$companyId])
			->count('kid');
		return $count;
	}
	
	public function getTag($tag_value)
	{
		$fwTag = new FwTag();
		$companyId = Yii::$app->user->identity->company_id;

		$tagService = new TagService();
		$tagCategoryId = $tagService->getTagCateIdByCateCode("examination_question-knowledge-point");
	
		$fwTagResult = $fwTag->find(false)
			->andFilterWhere(['like','tag_value',$tag_value])
			->andFilterWhere(['=','tag_category_id',$tagCategoryId])
			->andFilterWhere(['=','company_id',$companyId])
			->asArray()
			->all();
	
		return $fwTagResult;
	}


	public function getExaminationPaperCategoryIdByTreeNodeId($id)
	{
		if ($id != null && $id != "") {
			$examinationPaperCategoryModel = new LnExamPaperCategory();

			$examinationPaperCategoryResult = $examinationPaperCategoryModel->findOne(['tree_node_id' => $id]);

			if ($examinationPaperCategoryResult != null)
			{
				$examinationPaperCategoryId = $examinationPaperCategoryResult->kid;
			}
			else
			{
				$examinationPaperCategoryId = null;
			}
		}
		else
		{
			$examinationPaperCategoryId = null;
		}

		return $examinationPaperCategoryId;
	}

	public function getExaminationQuestionCategory($companyIdList,$category_name)
	{
		$lnExaminationCategory = new LnExamQuestionCategory();

		if( is_array($companyIdList)){
			$query_category= $lnExaminationCategory->find(false)
				->andFilterWhere(['in','company_id',$companyIdList])
			;
		}else{
			$query_category= $lnExaminationCategory->find(false)
				->andFilterWhere(['in','company_id',[$companyIdList]])
			;
		}

		if($category_name){		
			$query_category->andFilterWhere(['like','category_name',$category_name]);
		}
		
		$lnExaminationCategoryResult=$query_category->asArray()
			   ->all();

		//$xxx=$query_category->createCommand()->getRawSql();
	
		return $lnExaminationCategoryResult;
	}
	
	/**
	 * 根据企业ID列表获取试卷分类
	 * @param $companyIdList
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public function getExaminationPaperCategoryByCompanyIdList($companyIdList)
	{
		$lnExamPaperCategory = new LnExamPaperCategory();
	
		$lnExamPaperCategoryResult = $lnExamPaperCategory->find(false)
			->andFilterWhere(['in','company_id',$companyIdList])
			->all();
	
		return $lnExamPaperCategoryResult;
	}
	
	/**
	 * 根据树节点ID获取目录ID
	 * @param $id
	 * @return null|string
	 */
	public function getCategoryIdByTreeNodeId($id)
	{
		if ($id != null && $id != "") {
			$lnExamPaperCategory = new LnExamPaperCategory();
	
			$lnExamPaperCategoryResult = $lnExamPaperCategory->findOne(['tree_node_id' => $id]);
	
			if ($lnExamPaperCategoryResult != null)
			{
				$categoryId = $lnExamPaperCategoryResult->kid;
			}
			else
			{
				$categoryId = null;
			}
		}
		else
		{
			$categoryId = null;
		}
	
		return $categoryId;
	}

	/**
	 * 根据树节点ID，删除相关课程目录ID
	 * @param $treeNodeId
	 */
	public function deleteRelateData($treeNodeId)
	{
		if (empty($treeNodeId)) return false;

		$kids = "";
		if (is_array($treeNodeId)) {
			foreach ($treeNodeId as $key) {
				$kids = $kids . "'" . $key . "',";

				$examPaperCategoryKey = $this->getExaminationPaperCategoryIdByTreeNodeId($key);
				LnExamPaperCategory::removeFromCacheByKid($examPaperCategoryKey);
			}

			$kids = rtrim($kids, ",");
		}else{
			$kids = "'".$treeNodeId."'";

			$examPaperCategoryKey = $this->getExaminationPaperCategoryIdByTreeNodeId($treeNodeId);
			LnExamPaperCategory::removeFromCacheByKid($examPaperCategoryKey);
		}

		LnExamPaperCategory::deleteAll(BaseActiveRecord::getQuoteColumnName("tree_node_id") ." in (".$kids.")");
		FwTreeNode::deleteAll(BaseActiveRecord::getQuoteColumnName("kid") . " in (".$kids.")");

		return true;
	}


	public function getQuestions($params)
	{
		$limit=$params['limit'];
		$offset=$params['offset'];
		$current_time=$params['current_time'];
		$uuid=$params['mission_id'];
		$companyId = Yii::$app->user->identity->company_id;

		$tagService=new TagService();
		$tagCategoryId = $tagService->getTagCateIdByCateCode("examination_question-knowledge-point");
		
		$tag_sql=" (
				select tag_ref.subject_id, GROUP_CONCAT(ifnull(tag_ref.tag_id,'')) as tag_id, GROUP_CONCAT(ifnull(tt.tag_value,'')) as tag_value from {{%fw_tag_reference}} tag_ref inner join 
(select * from  {{%fw_tag}} where tag_category_id='".$tagCategoryId."'
  and company_id='".$companyId."') tt on tag_ref.tag_id=tt.kid  where tag_ref.status='1'   group by tag_ref.subject_id)
				";
		
	
		$query=LnExaminationQuestion::find(false)
		    ->leftjoin($tag_sql.' as t1','t1.subject_id = '.LnExaminationQuestion::tableName().".kid")
			->andFilterWhere(["=",LnExaminationQuestion::tableName().".company_id",$companyId])
			->andFilterWhere(['<', LnExaminationQuestion::tableName() . '.created_at', $current_time])
			->andWhere(" not exists  (select * from {{%ln_exam_paper_quest_temp}} p where ".LnExaminationQuestion::tableName().".kid=p.examination_question_id and p.is_read='".LnExamPaperQuestTemp::IS_READ_YES."' and p.examination_paper_batch= '".$uuid."')")
			->orderBy(LnExaminationQuestion::tableName().".created_at desc")
			
		;
		
		if(isset($params['examination_question_type'])){
			if($params['examination_question_type']!=ExamPaperManageService::EXAMINATION_QUESTION_TYPE_ALL){
				$query->andFilterWhere(["=",LnExaminationQuestion::tableName().".examination_question_type",$params['examination_question_type']]);
			}		
		}
		
		
		if(isset($params['tag_select_id'])){
			$params['tag_select_id']=trim($params['tag_select_id']);
			$query->andWhere("t1.tag_id like '%".$params['tag_select_id']."%' or ".LnExaminationQuestion::tableName().".title like '%{$params['tag_select_id']}%'");
		}
		
		
		if(isset($params['category_select_id'])){
			$query->andFilterWhere(["=",LnExaminationQuestion::tableName().".category_id",$params['category_select_id']]);
		}
		
		$lnExaminationQuestionArrTmp=$query->select(LnExaminationQuestion::tableName().".kid,".LnExaminationQuestion::tableName()
					.".examination_question_type ,".LnExaminationQuestion::tableName().".title,is_allow_change_score,
				default_score,category_id,t1.tag_id,t1.tag_value")
			->asArray()
			->limit($limit)
			->offset($offset)
			->all();
         
		//echo ($lnExaminationQuestionArrTmp->createCommand()->getRawSql());
		
		
		$lnExaminationQuestionArr=[];
		foreach ($lnExaminationQuestionArrTmp as $lnExaminationQuestion){
			if($lnExaminationQuestion['examination_question_type']==LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO){
				$lnExaminationQuestion['examination_question_type']="单选";
			}else if($lnExaminationQuestion['examination_question_type']==LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX){
				$lnExaminationQuestion['examination_question_type']="多选";
			}else if($lnExaminationQuestion['examination_question_type']==LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE){
				$lnExaminationQuestion['examination_question_type']="判断";
			}
			
			if($lnExaminationQuestion['is_allow_change_score']==LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_NO){
				$lnExaminationQuestion['is_allow_change_score']='readonly=readonly';
				
			}else if($lnExaminationQuestion['is_allow_change_score']==LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_YES){
				$lnExaminationQuestion['is_allow_change_score']=" ";
			}
			
			array_push($lnExaminationQuestionArr, $lnExaminationQuestion);
		}
		
		return $lnExaminationQuestionArr;
		
	}
	
	
	public function getPaper($id){
		$paper_m=LnExaminationPaper::find(false)
		     ->andFilterWhere(['=','kid',$id])
		     ->asArray()
		     ->one();
		       
		return $paper_m;
	}
	
	public function getExaminationPaperQuestion($id)
	{
		
		
		$result=LnExamPaperQuestion::find(false)
			->andFilterWhere(['=','examination_paper_id',$id])
			->asArray()
			->all();
		
		$question_in_ids=[];
		foreach ($result as $r){
			array_push($question_in_ids,$r['examination_question_id']);
		}
		
		$result_quest= LnExaminationQuestion::find(true)
			->andFilterWhere(['in','kid',$question_in_ids])
			->asArray()
			->all()
		   ;
		$question_delete_ids=[];
		
		foreach ($result_quest as $qr){
			if($qr['is_deleted']==LnExamPaperQuestion::DELETE_FLAG_YES){
				array_push($question_delete_ids,$qr['kid']);
			}	
		}
		
		if(count($question_delete_ids)>0){
			$this->deleteExaminationPaperQuestionBy($question_delete_ids);
		}
		
		$question_show_ids=[];
		$question_show_ids=LnExamPaperQuestion::find(false)
		    ->leftJoin('{{%ln_examination_question}} as t1','t1.kid = '.LnExamPaperQuestion::tableName().".examination_question_id")
			->andFilterWhere(['=','examination_paper_id',$id])
			->select("t1.title,t1.examination_question_type,t1.kid,t1.description,".LnExamPaperQuestion::tableName().".relation_type,".LnExamPaperQuestion::tableName().".default_score,"
					.LnExamPaperQuestion::tableName().".sequence_number")
			->asArray()
			->all();
		
		$total_num=0;
		$total_score=0;
		
		foreach ($question_show_ids as $qsr){
			if($qsr['relation_type']=="0"){

				$total_num=$total_num+1;
				$total_score=$total_score+$qsr['default_score'];
			}

		}
		
		$lnExaminationPaper=new LnExaminationPaper();
	
		$attributes_1=[
				'paper_version'=> $this->setExamVersion($lnExaminationPaper->kid),
				'default_total_score'=>$total_score,
				'examination_question_number'=>$total_num
		];
	
		$lnExaminationPaper->updateAll($attributes_1,"kid = '".$id."'");
		LnExaminationPaper::removeFromCacheByKid($id);
		
		return $question_show_ids;
	}
	
	public function findExamPaper($id){
		$this->getExaminationPaperQuestion($id);
		return LnExaminationPaper::findOne($id,false);
	}
	
	public function  deleteExaminationPaperQuestionBy($ids){
	
		//删除eln_ln_exam_paper_question关系
	
		$lnExamPaperQuestion=new LnExamPaperQuestion();
		$ids_sql="";
		foreach($ids as $id){
			$ids_sql=$ids_sql."'".$id."',";
		}
		$ids_sql=rtrim($ids_sql,',');
	
		$params = [

				':status' => self::STATUS_FLAG_NORMAL,
		];
	
		$condition = 'examination_question_id in ('.$ids_sql.') and status = :status ';
	
		$lnExamPaperQuestion->physicalDeleteAll($condition,$params);
	
	}
	
	public function getExamPaperQuestions($params)
	{
	
		$examination_paper_id=$params['examination_paper_id'];
		$companyId = Yii::$app->user->identity->company_id;
		
		$tagService=new TagService();
		$tagCategoryId = $tagService->getTagCateIdByCateCode("examination_question-knowledge-point");
		
	
		$tag_sql=" (
				select tag_ref.subject_id, GROUP_CONCAT(tag_ref.tag_id) as tag_id,GROUP_CONCAT(tt.tag_value) as tag_value from {{%fw_tag_reference}} tag_ref inner join
(select * from  {{%fw_tag}} where tag_category_id='".$tagCategoryId."'
  and company_id='".$companyId."') tt on tag_ref.tag_id=tt.kid   where tag_ref.status='1'  group by tag_ref.subject_id)
				";
	
		$paper_question_sql="
				(select * from {{%ln_exam_paper_question}} where examination_paper_id='".$examination_paper_id."')
				";
	
		$query=LnExaminationQuestion::find(false)
		->leftjoin($tag_sql.' as t1','t1.subject_id = '.LnExaminationQuestion::tableName().".kid")
		->innerJoin($paper_question_sql.' as ttt','ttt.examination_question_id = '.LnExaminationQuestion::tableName().".kid")
		->andFilterWhere(["=",LnExaminationQuestion::tableName().".company_id",$companyId])
		->orderBy(LnExaminationQuestion::tableName().".created_at desc")
			
		;
	
	
	
		$lnExaminationQuestionArrTmp=$query->select(LnExaminationQuestion::tableName().".kid,".LnExaminationQuestion::tableName()
				.".examination_question_type ,".LnExaminationQuestion::tableName().".title,is_allow_change_score,
				ttt.default_score,category_id,t1.tag_id,t1.tag_value,ttt.sequence_number,ttt.relation_type")
					->asArray()
					->all();
	
	
		$lnExaminationQuestionArr=[];
		foreach ($lnExaminationQuestionArrTmp as $lnExaminationQuestion){
			if($lnExaminationQuestion['examination_question_type']==LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO){
				$lnExaminationQuestion['examination_question_type']="单选";
			}else if($lnExaminationQuestion['examination_question_type']==LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX){
				$lnExaminationQuestion['examination_question_type']="多选";
			}else if($lnExaminationQuestion['examination_question_type']==LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE){
				$lnExaminationQuestion['examination_question_type']="判断";
			}
	
			if($lnExaminationQuestion['is_allow_change_score']==LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_NO){
				$lnExaminationQuestion['is_allow_change_score']='readonly=readonly';
	
			}else if($lnExaminationQuestion['is_allow_change_score']==LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_YES){
				$lnExaminationQuestion['is_allow_change_score']=" ";
			}
	
			array_push($lnExaminationQuestionArr, $lnExaminationQuestion);
		}
	
		$fenyefu=LnExamPaperQuestion::find(false)
		->andFilterWhere(["=","examination_paper_id",$examination_paper_id])
		->andFilterWhere(["=","relation_type",LnExamPaperQuestion::RELATION_TYPE_HR])
		->select("relation_type,sequence_number")
		->asArray()
		->all();
		$lnExaminationQuestionArr=array_merge($lnExaminationQuestionArr,$fenyefu);
		return $lnExaminationQuestionArr;
	
	}
	
	
	public function updateExaminationPaperQuestionTemp($params){
		
		$lnExamPaperQuestTemp=new LnExamPaperQuestTemp();
		
		$user_id = Yii::$app->user->getId();
		
		$attributes = [	
				'is_read' => LnExamPaperQuestTemp::IS_READ_YES,
			
		];
		
		$params1 = [
				':examination_paper_batch'=>$params['mission_id'],
				':created_by'=>$user_id,
		];
		
		$condition = 'examination_paper_batch = :examination_paper_batch and created_by=:created_by ';
		
		$lnExamPaperQuestTemp->updateAll($attributes,$condition,$params1);
		//print_r($lnExamPaperQuestTemp) ;
		
	}
	
	public function getQuestionsSimple($params)
	{
	
		$uuid=$params['mission_id'];
		$companyId = Yii::$app->user->identity->company_id;
		$user_id = Yii::$app->user->getId();
		$tagService=new TagService();
		$tagCategoryId = $tagService->getTagCateIdByCateCode("examination_question-knowledge-point");
		$tag_sql=" (
				select tag_ref.subject_id,GROUP_CONCAT(tag_ref.tag_id) as tag_id,GROUP_CONCAT(tt.tag_value) as tag_value from {{%fw_tag_reference}} tag_ref inner join
(select * from  {{%fw_tag}} where tag_category_id='".$tagCategoryId."'
  and company_id='".$companyId."') tt on tag_ref.tag_id=tt.kid  where tag_ref.status='1'  group by tag_ref.subject_id)
				";
	
		$question_tmp_sql="(
				select * from {{%ln_exam_paper_quest_temp}} where is_read='0' and  examination_paper_batch= '".$uuid."'  and created_by ='".$user_id."'
				)
				";
		
		$query=LnExaminationQuestion::find(false)
			->leftjoin($tag_sql.' as t1','t1.subject_id = '.LnExaminationQuestion::tableName().".kid")
			->innerJoin($question_tmp_sql.'  as t2',"t2.is_read='0' and t2.examination_question_id = ".LnExaminationQuestion::tableName().".kid ")			
			->andFilterWhere(["=",LnExaminationQuestion::tableName().".company_id",$companyId])
			->orderBy("t2.sequence_number asc")				
		;
		
		if (isset($params['examination_question_type'])) {
			$query->andFilterWhere(["=",LnExaminationQuestion::tableName().".examination_question_type",$params['examination_question_type']]);
		}
		
		if (isset($params['examination_question_level'])) {
			$query->andFilterWhere(["=",LnExaminationQuestion::tableName().".examination_question_level",$params['examination_question_level']]);
		}
		
		
		$lnExaminationQuestionArrTmp=$query->select(LnExaminationQuestion::tableName().".kid,".LnExaminationQuestion::tableName()
				.".examination_question_type ,".LnExaminationQuestion::tableName().".title,is_allow_change_score,
			t2.default_score,category_id,t1.tag_id,t1.tag_value")
						->asArray()
					
						->all();
		
		//echo ($lnExaminationQuestionArrTmp->createCommand()->getRawSql());
		
		
		$lnExaminationQuestionArr=[];
		foreach ($lnExaminationQuestionArrTmp as $lnExaminationQuestion){
			if($lnExaminationQuestion['examination_question_type']==LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO){
				$lnExaminationQuestion['examination_question_type']="单选";
			}else if($lnExaminationQuestion['examination_question_type']==LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX){
				$lnExaminationQuestion['examination_question_type']="多选";
			}else if($lnExaminationQuestion['examination_question_type']==LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE){
				$lnExaminationQuestion['examination_question_type']="判断";
			}
		
			if($lnExaminationQuestion['is_allow_change_score']==LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_NO){
				$lnExaminationQuestion['is_allow_change_score']='readonly=readonly';
		
			}else if($lnExaminationQuestion['is_allow_change_score']==LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_YES){
				$lnExaminationQuestion['is_allow_change_score']=" ";
			}
		
			array_push($lnExaminationQuestionArr, $lnExaminationQuestion);
		}
		
		return $lnExaminationQuestionArr;
		
	}
	
	public function getQuestionOptions($kid)
	{
		return $lnExamQuestionOption=LnExamQuestionOption::find(false)
		   ->andFilterWhere(["=","examination_question_id",$kid])
		   ->orderBy("sequence_number asc")
		   ->asArray()
		   ->all()
		   ;
	}
	
	
	public function saveExamPaper($params)
	{
		$exam=$params['exam'];
		$result=$params['result'];
		$companyId = Yii::$app->user->identity->company_id;		
		$default_total_score=0;
		$examination_question_number=0;
		
		foreach ($result as $r){
			if($r['relation_type']==LnExamPaperQuestion::RELATION_TYPE_PAPER){
				$default_total_score=$default_total_score+$r['default_score'];
			    $examination_question_number=$examination_question_number+1;
			}			
		}
		
		$lnExaminationPaper=new LnExaminationPaper();
		
		foreach ($exam as $k=>$v){
			
			if($k=="title"){
				$lnExaminationPaper->$k=Html::encode($v);
			}else if($k=="description"){
				$lnExaminationPaper->$k=Html::encode($v);
			}else{
				$lnExaminationPaper->$k=$v;
			};
		}
		
		$lnExaminationPaper->paper_version = $this->setExamVersion($lnExaminationPaper->kid);
		
		$lnExaminationPaper->company_id=$companyId;
		$lnExaminationPaper->code=$this->getExamPaperCode();
		$lnExaminationPaper->default_total_score=$default_total_score;
		$lnExaminationPaper->examination_question_number=$examination_question_number;
		$lnExaminationPaper->needReturnKey = true;
		$lnExaminationPaper->save();
		
		$pid=$lnExaminationPaper->kid;
		
		$num=0;
		foreach ($result as $r){
			$num++;
			$lnExamPaperQuestion=new LnExamPaperQuestion();
			
			$lnExamPaperQuestion->examination_paper_id=$pid;
			if($r['relation_type']==LnExamPaperQuestion::RELATION_TYPE_PAPER){
				$lnExamPaperQuestion->examination_question_id=$r['kid'];
				$lnExamPaperQuestion->default_score=$r['default_score'];
			}
			
			
			$lnExamPaperQuestion->relation_type=$r['relation_type'];
			$lnExamPaperQuestion->sequence_number=$num;
			$lnExamPaperQuestion->status=LnExamPaperQuestion::STATUS_FLAG_NORMAL;
			$lnExamPaperQuestion->start_at= time();

			$lnExamPaperQuestion->save();
		}
	}
	
	public function editExamPaper($params)
	{
		$exam=$params['exam'];
		$result=$params['result'];
		$companyId = Yii::$app->user->identity->company_id;
		$id=$exam['kid'];
		$default_total_score=0;
		$examination_question_number=0;
		$attributes = [];
	
		foreach ($result as $r){
			if($r['relation_type']==LnExamPaperQuestion::RELATION_TYPE_PAPER){
				$default_total_score=$default_total_score+$r['default_score'];
				$examination_question_number=$examination_question_number+1;
			}			
		}
	
		$lnExaminationPaper=new LnExaminationPaper();
		foreach ($exam as $k=>$v){
			if($k=="title"){
				$attributes[$k]=Html::encode($v);
			}else if($k=="description"){
				$attributes[$k]=Html::encode($v);
			}else{
				$attributes[$k]=$v;
			};
			
		}
	
		$attributes_1=[				
		        'paper_version'=> $this->setExamVersion($lnExaminationPaper->kid),
				'default_total_score'=>$default_total_score,
				'examination_question_number'=>$examination_question_number
		];
		
		$attributes=array_merge($attributes,$attributes_1);
		
		$lnExaminationPaper->updateAll($attributes,"kid = '".$id."'");
		LnExaminationPaper::removeFromCacheByKid($id);
		
		$pid=$id;
		$this->deleteExaminationPaperQuestion($pid);
	
		$num=0;
		foreach ($result as $r){
			$num++;
			$lnExamPaperQuestion=new LnExamPaperQuestion();
				
			$lnExamPaperQuestion->examination_paper_id=$pid;
			if($r['relation_type']==LnExamPaperQuestion::RELATION_TYPE_PAPER){
				$lnExamPaperQuestion->examination_question_id=$r['kid'];
				$lnExamPaperQuestion->default_score=$r['default_score'];
			}
				
				
			$lnExamPaperQuestion->relation_type=$r['relation_type'];
			$lnExamPaperQuestion->sequence_number=$num;
			$lnExamPaperQuestion->status=LnExamPaperQuestion::STATUS_FLAG_NORMAL;
			$lnExamPaperQuestion->start_at= time();
				
			$lnExamPaperQuestion->save();
		}
	}
	
	public function getExamPaperStats($params)
	{
		$result=$params['result'];
		$mission_id=$params['mission_id'];
		
		$default_total_score=0;
		$examination_question_number=0;
		
		foreach ($result as $r){
			if($r['relation_type']==LnExamPaperQuestion::RELATION_TYPE_PAPER){
				$default_total_score=$default_total_score+$r['default_score'];
				$examination_question_number=$examination_question_number+1;
			}
				
		}
		
		$paper=[];
				
		$sql_category="				
				select qu.category_id,count(qu.category_id) as num,ca.category_name as name from eln_ln_examination_question qu
				   left join eln_ln_exam_question_category ca  on qu.category_id =ca.kid
				    where exists ( select * from {{%ln_exam_paper_quest_temp}} p where qu.kid=p.examination_question_id and is_read='1' and p.examination_paper_batch='".$mission_id."')
				  group by qu.category_id				
				";
		
		
		$db = \Yii::$app->db;
		$category_res= $db->createCommand($sql_category)
		             ->queryAll();
		
		$sql_tag="
				  select t1.tag_id,count(t1.tag_id) as num,tag_value as name from (
					  select  tag_r.tag_id from eln_ln_examination_question qu left join eln_fw_tag_reference tag_r
					    on qu.kid =tag_r.subject_id  where exists ( select * from {{%ln_exam_paper_quest_temp}} p where qu.kid=p.examination_question_id and is_read='1' and p.examination_paper_batch='".$mission_id."')
					) t1 left join eln_fw_tag tag on (t1.tag_id=tag.kid) group by t1.tag_id									
				";
		
		$tag_res= $db->createCommand($sql_tag)
				->queryAll();
		
		
	    $sql_type=" select case examination_question_type  when '0' then '单选'
                                           when  '1'then '多选'
                                           when '3' then '判断' end as name,count(examination_question_type)  as num  
			   from eln_ln_examination_question t where exists ( select * from {{%ln_exam_paper_quest_temp}} p where  t.kid=p.examination_question_id and is_read='1' and p.examination_paper_batch='".$mission_id."')
			    group by examination_question_type ";
	    
	    $type_res= $db->createCommand($sql_type)
	         ->queryAll();
	    
	    
	    $paper['category_res']=$category_res;
	    $paper['tag_res']=$tag_res;
	    $paper['type_res']=$type_res;
	    $paper['default_total_score']=$default_total_score;
	    $paper['examination_question_number']=$examination_question_number;
	   
	    
	    return $paper;
	
	}
	
	/*获取编号*/
	public function getExamPaperCode(){
		
		$start_at = strtotime(date('Y-m-d'));
		$end_at = $start_at+86399;
		$count = $this->find()->where("created_at>".$start_at)->andWhere("created_at<".$end_at)->count();
		$count = $count+1;/*默认成1开始*/
		return date('Ymd').sprintf("%03d", $count);
	}
	
	public function search($params)
	{
	
		$query = LnExaminationPaper::find(false);
		//$keyword=$params['keyword'];
		$keyword="";
		if (isset($params['TreeNodeKid'])){
			$tree_node_id = explode(',', $params['TreeNodeKid']);
			$categories = $this->getCategoryIdByTreeNodeId($tree_node_id);
			if ($categories) {
				$query->andFilterWhere(['in', 'category_id',[$categories] ]);
			}
		}
		
	
		if (isset($params['keyword'])) {
			$keyword = $params['keyword'];
			$keyword = trim($keyword);
		}
		else {
			$keyword = "";
		}
	
		if($keyword){
			$query->andWhere("title like '%{$keyword}%' or description like '%{$keyword}%'");
		}
		
		if (isset($params['examination_paper_type'])) {
			$examination_paper_type = $params['examination_paper_type'];
			$query->andFilterWhere(['=', 'examination_paper_type', $examination_paper_type]);
		}
		
		
	
		$query
		->andFilterWhere(["=","company_id",Yii::$app->user->identity->company_id]);
	
		$dataProvider = new ActiveDataProvider([
				'query' => $query,
		]);
	
		$this->load($params);
	
	
	
		$dataProvider->setSort(false);
		$query->addOrderBy(['created_at' => SORT_DESC]);
		/*echo ($query->createCommand()->getRawSql());*/
		return $dataProvider;
	}
	
	public function deleteExamPaper($id){
	
		$lnExaminationPaper=new LnExaminationPaper();
	
		$delModel=$lnExaminationPaper->findOne($id);
	
		$kid=$delModel->kid;
		$delModel->delete();
	
		$this->deleteExaminationPaperQuestion($kid);
		
	
	}
	
	public function  deleteExaminationPaperQuestion($kid){
		
		//删除eln_ln_exam_paper_question关系
		
		$lnExamPaperQuestion=new LnExamPaperQuestion();
		
		$params = [
				':examination_paper_id' => $kid,
				':status' => self::STATUS_FLAG_NORMAL,
		];
		
		$condition = 'examination_paper_id = :examination_paper_id and status = :status ';
		
		$lnExamPaperQuestion->physicalDeleteAll($condition,$params);
		
	}
	
	/*获取编号*/
	public function setExamVersion($kid=null){
		if (empty($kid)) return date('Ymd') . '001';
		$model = new LnExaminationPaper();
		$condition = ['kid' => $kid];
		$result = $model->findOne($condition,false);
		$course_version = $result->paper_version;
		if (substr($course_version, 0, 8) == date('Ymd')) {
			$last_version = substr($course_version, -3);
			return date('Ymd') . sprintf("%03d", intval($last_version) + 1);
		} else {
			return date('Ymd') . '001';
		}
	}

	/**
	 * 创建目录企业考试临时目录
	 * @param $companyId
	 * @return mixed|string
	 */
	public function getExamPaperTempCategoryId($companyId){
		$categoryTemp = LnExamPaperCategory::find(false)->andFilterWhere(['company_id' => $companyId,'category_name' => Yii::t('common', 'temp_category')])->one();
		if (empty($categoryTemp)){
			$treeNodeService = new TreeNodeService();
			$tree_node_id = $treeNodeService->addTreeNode('examination-paper-category', Yii::t('common', 'temp_category'), "");
			$treeNode = FwTreeNode::findOne($tree_node_id);
			$category = new LnExamPaperCategory();
			$category->tree_node_id = $tree_node_id;
			$category->parent_category_id = null;
			$category->company_id = $companyId;
			$category->category_code = $treeNode->tree_node_code;
			$category->category_name = Yii::t('common', 'temp_category');
			$category->status = LnExamPaperCategory::STATUS_FLAG_NORMAL;
			$category->needReturnKey = true;
			$category->save();
			$categoryId = $category->kid;
		}else{
			$categoryId = $categoryTemp->kid;
		}
		return $categoryId;
	}
	
	
	
}