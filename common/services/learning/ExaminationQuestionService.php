<?php

namespace common\services\learning;

use common\models\learning\LnExamPaperQuestion;
use common\models\learning\LnExamQuestionCategory;
use common\services\framework\TagService;
use common\eLearningLMS;
use stdClass;
use Yii;
use yii\data\ActiveDataProvider;
use Exception;
use yii\helpers\ArrayHelper;
use common\helpers\TStringHelper;
use yii\db\Query;
use PDO;
use common\models\learning\LnExaminationQuestion;
use common\models\learning\LnExamQuestionOption;


class ExaminationQuestionService extends LnExaminationQuestion
{
    /**
     * 获取试题列表
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params){
        $company_id = Yii::$app->user->identity->company_id;
        $query = LnExaminationQuestion::find(false);
        if (isset($params['TreeNodeKid'])){
            $tree_node_id = explode(',', $params['TreeNodeKid']);
            $categories = $this->getTreeNodeIdToCategoryId($tree_node_id);
            if ($categories) {
                $query->andFilterWhere(['in', 'category_id', $categories]);
            }
        }
        if (!empty($params['keywords'])) {
            $keywords = TStringHelper::clean_xss($params['keywords']);
            $query->andWhere("title like '%{$keywords}%' OR description like '%{$keywords}%'");
        }

        if (isset($params['examination_question_type'])) {
            $query->andFilterWhere(['=', 'examination_question_type', $params['examination_question_type']]);
        }
        if (isset($params['examination_question_level'])) {
            $query->andFilterWhere(['=', 'examination_question_level', $params['examination_question_level']]);
        }
        if (!empty($this->title)) {
            $keywords = trim(urldecode($this->title));
            $query->andWhere("title like '%{$keywords}%' OR description like '%{$keywords}%'");
        }
        $query->andFilterWhere(['=', 'company_id', $company_id]);
        $query->addOrderBy(['created_at' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $dataProvider->setSort(false);
        /* echo ($query->createCommand()->getRawSql());*/
        return $dataProvider;
    }

    /**
     * 获取编号
     * @param null $kid
     * @return string
     */
    public function setExamQuestionCode($kid=null){
        $start_at = strtotime(date('Y-m-d'));
        $end_at = $start_at+86399;
        $count = $this->find()->where("created_at>".$start_at)->andWhere("created_at<".$end_at)->count();
        $count = $count+1;/*默认成1开始*/
        return date('Ymd').sprintf("%03d", $count);
    }

    /**
     * 获取版本
     * @param null $kid
     * @return string
     */
    public function setExamQuestionVersion($kid=null){
        if (empty($kid)) return date('Ymd') . '001';
        $model = new LnExaminationQuestion();
        $condition = ['kid' => $kid];
        $result = $model->findOne($condition,false);
        $course_version = $result->question_version;
        if (substr($course_version, 0, 8) == date('Ymd')) {
            $last_version = substr($course_version, -3);
            return date('Ymd') . sprintf("%03d", intval($last_version) + 1);
        } else {
            return date('Ymd') . '001';
        }
    }

    /**
     * @param $companyId
     * @return int|string
     */
    public function setSequenceNumber($companyId){
        $count = LnExaminationQuestion::find()->andFilterWhere(['company_id'=>$companyId])->count();
        return $count+1;
    }

    /**
     * tree_node_id 转 category_id
     * @param $tree_node_id
     * @return array|string|void
     */
    public function getTreeNodeIdToCategoryId($tree_node_id){
        if (empty($tree_node_id)) return ;
        $categories = LnExamQuestionCategory::findAll(['tree_node_id'=>$tree_node_id],false);
        if (is_array($tree_node_id)){
            $result = array();
            foreach ($categories as $value){
                $result[] = $value->kid;
            }
            return $result;
        }else{
            return $categories ? $categories[0]->kid : '';
        }
    }


    /**
     * category_id 转 tree_node_id
     * @param $category_id
     * @return string|void
     */
    public function getCategoryIdToTreeNodeId($category_id){
        if (empty($category_id)) return null;
        $find = LnExamQuestionCategory::findOne($category_id);
        return $find ? $find->tree_node_id : '';
    }

    /**
     * 根据指定的题数生成试卷
     * @param string $examinationPaperBatch 试卷批号
     * @param string $companyId 企业ID
     * @param string $questionCategoryId 试题目录ID
     * @param string $questionType 试题类型，如选择题等
     * @param string $questionLevel 试题难度
     * @param string $keyword 试题标题 或 知识点名字
     * @param string $requestNumber 需要生成的试题数量
     * @param string $userId 用户ID
     * @param string $result 结果，OK 或 ERROR
     * @param string $message 结果信息，如果成功返回成功插入的数据条数，否则返回错误信息
     */
    public function GeneratePaperQuestionByNumber($examinationPaperBatch, $companyId, $questionCategoryId = "", $questionType = "", $questionLevel = "", $keyword = "", $requestNumber, $userId, &$result, &$message){
        $sql = "CALL generate_paper_quest_by_number(:ExaminationPaperBatch, :CompanyId, :QuestionCategoryId,"
            . ":QuestionType,:QuestionLevel,:Keyword,:RequestNumber,"
            . ":UserId)";

        $inputParams = array();
        $paramExaminationPaperBatch = new stdClass();
        $paramExaminationPaperBatch->name = ":ExaminationPaperBatch";
        $paramExaminationPaperBatch->value = $examinationPaperBatch;
        $paramExaminationPaperBatch->type = PDO::PARAM_STR;
        $inputParams[] = $paramExaminationPaperBatch;

        $paramCompanyId = new stdClass();
        $paramCompanyId->name = ":CompanyId";
        $paramCompanyId->value = $companyId;
        $paramCompanyId->type = PDO::PARAM_STR;
        $inputParams[] = $paramCompanyId;

        $paramQuestionCategoryId = new stdClass();
        $paramQuestionCategoryId->name = ":QuestionCategoryId";
        $paramQuestionCategoryId->value = $questionCategoryId;
        $paramQuestionCategoryId->type = PDO::PARAM_STR;
        $inputParams[] = $paramQuestionCategoryId;

        $paramQuestionType = new stdClass();
        $paramQuestionType->name = ":QuestionType";
        $paramQuestionType->value = $questionType;
        $paramQuestionType->type = PDO::PARAM_STR;
        $inputParams[] = $paramQuestionType;

        $paramQuestionLevel = new stdClass();
        $paramQuestionLevel->name = ":QuestionLevel";
        $paramQuestionLevel->value = $questionLevel;
        $paramQuestionLevel->type = PDO::PARAM_STR;
        $inputParams[] = $paramQuestionLevel;

        $paramKeyword = new stdClass();
        $paramKeyword->name = ":Keyword";
        $paramKeyword->value = $keyword;
        $paramKeyword->type = PDO::PARAM_STR;
        $inputParams[] = $paramKeyword;

        $paramRequestNumber = new stdClass();
        $paramRequestNumber->name = ":RequestNumber";
        $paramRequestNumber->value = $requestNumber;
        $paramRequestNumber->type = PDO::PARAM_INT;
        $inputParams[] = $paramRequestNumber;

        $paramUserId = new stdClass();
        $paramUserId->name = ":UserId";
        $paramUserId->value = $userId;
        $paramUserId->type = PDO::PARAM_STR;
        $inputParams[] = $paramUserId;


        $procedureResult = eLearningLMS::queryScalar($sql,$inputParams);
//        $result = $paramResult->value;

        if (!empty($procedureResult)) {
            $procedureResultArray = explode("-", $procedureResult);
            $result = $procedureResultArray[0];
            $message = $procedureResultArray[1];
            if ($result == "OK") {
                $tempMessage = explode("|", $message);//格式是：成功数量|成功分数
                $message = $tempMessage[0];
            }
        }

    }



    /**
     * 根据指定的分数生成试卷
     * @param string $examinationPaperBatch 试卷批号
     * @param string $companyId 企业ID
     * @param string $questionCategoryId 试题目录ID
     * @param string $questionType 试题类型，如选择题等
     * @param string $questionLevel 试题难度
     * @param string $keyword 试题标题 或 知识点名字
     * @param string $score 需要生成的试题总分数
     * @param string $userId 用户ID
     * @param string $result 结果，OK 或 ERROR
     * @param string $message 结果信息，如果成功返回成功插入的数据条数，否则返回错误信息
     */
    public function GeneratePaperQuestionByScore($examinationPaperBatch, $companyId, $questionCategoryId = "", $questionType = "", $questionLevel = "", $keyword = "", $score, $userId, &$result, &$message){
        $sql = "CALL generate_paper_quest_by_score(:ExaminationPaperBatch, :CompanyId, :QuestionCategoryId,"
            . ":QuestionType,:QuestionLevel,:Keyword,:Score,"
            . ":UserId)";

        $inputParams = array();
        $paramExaminationPaperBatch = new stdClass();
        $paramExaminationPaperBatch->name = ":ExaminationPaperBatch";
        $paramExaminationPaperBatch->value = $examinationPaperBatch;
        $paramExaminationPaperBatch->type = PDO::PARAM_STR;
        $inputParams[] = $paramExaminationPaperBatch;

        $paramCompanyId = new stdClass();
        $paramCompanyId->name = ":CompanyId";
        $paramCompanyId->value = $companyId;
        $paramCompanyId->type = PDO::PARAM_STR;
        $inputParams[] = $paramCompanyId;

        $paramQuestionCategoryId = new stdClass();
        $paramQuestionCategoryId->name = ":QuestionCategoryId";
        $paramQuestionCategoryId->value = $questionCategoryId;
        $paramQuestionCategoryId->type = PDO::PARAM_STR;
        $inputParams[] = $paramQuestionCategoryId;

        $paramQuestionType = new stdClass();
        $paramQuestionType->name = ":QuestionType";
        $paramQuestionType->value = $questionType;
        $paramQuestionType->type = PDO::PARAM_STR;
        $inputParams[] = $paramQuestionType;

        $paramQuestionLevel = new stdClass();
        $paramQuestionLevel->name = ":QuestionLevel";
        $paramQuestionLevel->value = $questionLevel;
        $paramQuestionLevel->type = PDO::PARAM_STR;
        $inputParams[] = $paramQuestionLevel;

        $paramKeyword = new stdClass();
        $paramKeyword->name = ":Keyword";
        $paramKeyword->value = $keyword;
        $paramKeyword->type = PDO::PARAM_STR;
        $inputParams[] = $paramKeyword;

        $paramScore = new stdClass();
        $paramScore->name = ":Score";
        $paramScore->value = $score;
        $paramScore->type = PDO::PARAM_STR;
        $inputParams[] = $paramScore;

        $paramUserId = new stdClass();
        $paramUserId->name = ":UserId";
        $paramUserId->value = $userId;
        $paramUserId->type = PDO::PARAM_STR;
        $inputParams[] = $paramUserId;



        $procedureResult = eLearningLMS::queryScalar($sql,$inputParams);
//        $result = $paramResult->value;

        if (!empty($procedureResult)) {
            $procedureResultArray = explode("-", $procedureResult);
            $result = $procedureResultArray[0];
            $message = $procedureResultArray[1];
            if ($result == "OK") {
                $tempMessage = explode("|", $message);//格式是：成功数量|成功分数
                $message = $tempMessage[1];
            }
        }
    }

    /**
     * 判断试题是否被关联
     * @return bool
     */
    public function isPaperRelated($question_id){
        $count = LnExamPaperQuestion::find(false)->andFilterWhere(['examination_question_id'=>$question_id])->count();
        if ($count > 0){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 获取题库分类标题
     * @param string $categoryId
     */
    public function getQuestionCategoryName($categoryId){
    	$find = LnExamQuestionCategory::findOne($categoryId);
    	if ($find){
    		return $find->category_name;
    	}else{
    		return null;
    	}
    }
    
    /**
     * 读取导入的文件，保存到题库中
     * @param string $categoryId
     * @param string $file
     * @param string $fileName
     */
    public function readExaminationQuestionFile($categoryId, $file, $fileName = null){
    	if (empty($categoryId) || empty($file)){
    		return ['result' => 'fail', 'errmsg' => ''];
    	}
    	$file = Yii::$app->basePath .'/../'.$file;
    	//读入上传文件
    	$objPHPExcel = \PHPExcel_IOFactory::load($file);
    	//excel  sheet个数
    	$sheetNumber = $objPHPExcel->getSheetCount();
    	$err = array();
    	$companyId = Yii::$app->user->identity->company_id;
    	$select_right_number = $judge_right_number = 0;
        /*事务*/
        $transaction = Yii::$app->db->beginTransaction();
        /*选择题*/
    	if ($sheetNumber > 0){
	    	//内容转换为数组
	    	$sheet_0 = $objPHPExcel->getSheet(0)->toArray();
	    	if (!empty($sheet_0)){
                /*判断是否系统提供的模板，第一行隐藏掉*/
	    		foreach ($sheet_0 as $i => $item){
	    			if ($i > 1){
                        $title = (string)$item[0];/*标题*/
                        if (empty($title)){
                            break;
                        }
                        $exam_question_type = $item[1];/*类型*/
                        if (empty($exam_question_type)){
                            $err['select']['err'][] = array('row' => $i,'col'=> 2);
                            continue;
                        }
                        $tags = $item[2];/*知识点*/
                        if (empty($tags)){
                            $err['select']['err'][] = array('row' => $i,'col'=> 3);
                            continue;
                        }
                        /*难度*/
                        if (empty($item[3])){
                            $err['select']['err'][] = array('row' => $i,'col'=> 4);
                            continue;
                        }
                        /*默认分*/
                        if (empty($item[4])){
                            $err['select']['err'][] = array('row' => $i,'col'=> 5);
                            continue;
                        }
	    				$model = new LnExaminationQuestion();
	    				$model->company_id = $companyId;
	    				$model->category_id = $categoryId;
	    				$model->title = $title;
	    				$model->code = $this->setExamQuestionCode();
	    				$model->examination_question_level = $model->getExamLevel($item[3]);
	    				$model->answer = $item[5];
	    				$default = explode(',', $item[4]);
	    				$maxKey = null;
	    				if (count($default) > 1){
	    					$maxKey = array_search(max($default), $default);
	    					$default_score = $default[$maxKey];
	    					$model->is_allow_change_score = LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_NO;
	    				}else{
	    					$default_score = (float)$item[4];
	    					$model->is_allow_change_score = LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_YES;
	    				}
	    				$is_right = str_split(strtoupper($item[6]));
                        $is_right = array_filter($is_right);
                        $is_right = array_unique($is_right);
	    				if ($exam_question_type == '单选题'){
                            if (count($is_right) > 1){
                                $err['select']['err'][] = array('row'=>$i,'col'=> 7);
                                continue;
                            }
	    					$model->examination_question_type = LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO;
	    				}else{
                            if (count($is_right) < 2){
                                $err['select']['err'][] = array('row'=>$i,'col'=> 7);
                                continue;
                            }
	    					$model->examination_question_type = LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX;
	    				}
	    				$model->default_score = $default_score;
	    				$model->question_version = $this->setExamQuestionVersion();
	    				$model->sequence_number = $this->setSequenceNumber($companyId);
	    				$model->needReturnKey = true;
                        $option = array();
                        /* 添加选项 */
                        $option[] = (string)$item[7];/* A~H */
                        if (!isset($item[7]) || $item[7] == ""){
                            $err['select']['err'][] = array('row'=>$i,'col'=> 8);
                            continue;
                        }
                        $option[] = (string)$item[8];
                        if (!isset($item[8]) || $item[8] == ""){
                            $err['select']['err'][] = array('row'=>$i,'col'=> 9);
                            continue;
                        }
                        $option[] = (string)$item[9];
                        $option[] = (string)$item[10];
                        $option[] = (string)$item[11];
                        $option[] = (string)$item[12];
                        $option[] = (string)$item[13];
                        $option[] = (string)$item[14];
                        $option[] = (string)$item[15];
                        $option[] = (string)$item[16];
                        $option[] = (string)$item[17];
                        $option[] = (string)$item[18];
                        $option = array_filter($option);
                        $optionCount = count($option);
                        if ($optionCount < 2){/*至少需要两个选项*/
                            $err['select']['err'][] = array('row' => $i);
                            continue;
                        }
                        if (empty($is_right) && $optionCount<count($default)){/*选项与设置的默认分个数不对*/
                            $err['select']['err'][] = array('row' => $i);
                            continue;
                        }

	    				if ($model->save()!==false){
	    					/* 添加知识点 */
	    					if (!empty($tags)){
	    						$tags = explode(',', $tags);
	    						$tagService = new TagService();
	    						$tagService->addTag($tags, $model->kid, $companyId, 'examination_question-knowledge-point');
	    					}
	    					$select_right = $select_err = array();
	    					foreach ($option as $k => $val){
	    						if (empty($val)) continue;
	    						$optionModel = new LnExamQuestionOption();
	    						$optionModel->examination_question_id = $model->kid;
	    						$optionModel->option_title = $val;
	    						$optionModel->sequence_number = $k+1;
	    						$optionService = new ExaminationQuestionOptionService();
	    						$optionModel->option_version = $optionService->getExaminationQuestionOptionVersion();
	    						$char = chr(ord('A')+$k);
                                if ($model->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO) {/*单选*/
                                    if (empty($is_right)) {
                                        $default_option = explode(',', $item[4]);
                                        $optionModel->default_score = (float)(isset($default_option[$k])?$default_option[$k]:0);
                                        $optionModel->is_right_option = LnExamQuestionOption::IS_RIGHT_OPTION_YES;
                                    } else {/*单选*/
                                        if ($char == strtoupper($item[6])) {
                                            $optionModel->default_score = (float)$item[4];
                                            $optionModel->is_right_option = LnExamQuestionOption::IS_RIGHT_OPTION_YES;
                                        } else {
                                            $optionModel->default_score = 0;
                                            $optionModel->is_right_option = LnExamQuestionOption::IS_RIGHT_OPTION_NO;
                                        }
                                    }
                                }else{
                                    $default_option = explode(',', $item[4]);
                                    $optionModel->default_score = (float)$default_option[$k];
                                    if (!empty($is_right) && in_array($char, $is_right)) {
                                        $optionModel->is_right_option = LnExamQuestionOption::IS_RIGHT_OPTION_YES;
                                    }else{
                                        $optionModel->is_right_option = LnExamQuestionOption::IS_RIGHT_OPTION_NO;
                                    }
                                }

	    						if ($optionModel->save()!==false){
	    							$select_right[] = $k;
	    						}else{
	    							LnExaminationQuestion::deleteAllByKid($model->kid);
	    							LnExamQuestionOption::deleteAll("`examination_question_id`='{$model->kid}'");
	    							$select_err[] = $k+7;
	    						}
	    					}
	    					if (!empty($select_err)){
	    						$err['select']['err'][] = array('row'=>$i,'col'=>$select_err);
	    					}else{
	    						$select_right_number ++;
	    						$err['select']['success'][] = $i;
	    					}
	    				}else{
	    					$err['select']['err'][] = array('row' => $i);
	    				}
                        unset($item);
                        unset($is_right);
	    			}else{
                        continue;
                    }
	    		}
	    	}
    	}
        /*判断题*/
        if ($sheetNumber > 1){
            $sheet_1 = $objPHPExcel->getSheet(1)->toArray();
            if (!empty($sheet_1)){
                foreach ($sheet_1 as $i => $item){
                    if ($i > 1){
                        $title =  $item[0];
                        $tags = $item[1];
                        $level = $item[2];
                        $default_score = (float)$item[3];
                        $answer = $item[5];
                        if (empty($title)){
                            break;
                        }
                        if (empty($tags)){
                            $err['judge']['err'][] = array('row' => $i,'col'=> 2);
                            continue;
                        }
                        if (empty($level)){
                            $err['judge']['err'][] = array('row' => $i,'col'=> 3);
                            continue;
                        }
                        if (empty($default_score)){
                            $err['judge']['err'][] = array('row' => $i,'col'=> 4);
                            continue;
                        }
                        if (empty($answer)){
                            $err['judge']['err'][] = array('row' => $i,'col'=> 6);
                            continue;
                        }
                        $model = new LnExaminationQuestion();
                        $model->company_id = $companyId;
                        $model->category_id = $categoryId;
                        $model->title = $item[0];
                        $model->code = $this->setExamQuestionCode();
                        $model->examination_question_level = $model->getExamLevel($item[2]);
                        $model->answer = $item[4];
                        $model->is_allow_change_score = LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_YES;
                        $model->examination_question_type = LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE;
                        $model->default_score = $default_score;
                        $model->question_version = $this->setExamQuestionVersion();
                        $model->sequence_number = $this->setSequenceNumber($companyId);
                        $model->needReturnKey = true;
                        if ($model->save()!==false){
                            /* 添加知识点 */
                            if (!empty($tags)){
                                $tags = explode(',', $tags);
                                $tagService = new TagService();
                                $tagService->addTag($tags, $model->kid, $companyId, 'examination_question-knowledge-point');
                            }
                            /* 添加选项 */
                            if ($answer == '正确'){
                                $is_right_option = 1;
                            }else{
                                $is_right_option = 0;
                            }
                            if ($is_right_option){
                                $option = array(1,0);
                            }else{
                                $option = array(0,1);
                            }
                            $is_import_right = 1;
                            foreach ($option as $key=>$i){
                                $optionModel = new LnExamQuestionOption();
                                $optionModel->examination_question_id = $model->kid;
                                $optionModel->option_title = $model->title;
                                $optionModel->default_score = 0;
                                $optionModel->is_right_option = $is_right_option == $i ? LnExamQuestionOption::IS_RIGHT_OPTION_YES : LnExamQuestionOption::IS_RIGHT_OPTION_NO;
                                $optionModel->option_stand_result = (string)$is_right_option;
                                $optionModel->sequence_number = $key+1;
                                $optionService = new ExaminationQuestionOptionService();
                                $optionModel->option_version = $optionService->getExaminationQuestionOptionVersion();
                                if ($optionModel->save()!==false){

                                }else{
                                    LnExaminationQuestion::deleteAllByKid($model->kid);
                                    LnExamQuestionOption::deleteAll("`examination_question_id`='{$model->kid}'");
                                    $is_import_right = 0;
                                }
                            }
                            if ($is_import_right){
                                $err['judge']['success'][] = $i;
                                $judge_right_number ++;
                            }else{
                                $err['judge']['err'][] = array('row' => $i);
                            }
                        }else{
                            $err['judge']['err'][] = $i;
                        }
                        unset($item);
                    }else{
                        continue;
                    }
                }
            }
        }

        if (empty($err['select']['err']) && empty($err['judge']['err'])){
            $transaction->commit(); //提交事务会真正的执行数据库操作
        }else{
            $transaction->rollback(); //如果操作失败, 数据回滚
        }
    	$err['select_right_number'] = $select_right_number;
    	$err['judge_right_number'] = $judge_right_number;
    	$err['total'] = $select_right_number + $judge_right_number;
    	return ['result' => 'success', 'errmsg' => $err];    	
    }
}