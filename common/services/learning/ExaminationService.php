<?php

namespace common\services\learning;

use common\models\learning\LnExamQuestionCategory;
use common\models\learning\LnRelatedUser;
use common\models\learning\LnResComplete;
use common\models\message\MsTimeline;
use common\services\framework\PointRuleService;
use common\services\framework\UserService;
use common\models\framework\FwOrgnization;
use common\models\framework\FwUser;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseactivity;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnCourseMods;
use common\models\learning\LnCourseReg;
use common\models\learning\LnExaminationCategory;
use common\models\learning\LnExaminationPaper;
use common\models\learning\LnExaminationPaperCopy;
use common\models\learning\LnExamPaperQuestion;
use common\models\learning\LnExamPaperQuestCopy;
use common\models\learning\LnExamPaperQuestUser;
use common\models\learning\LnExaminationPaperUser;
use common\models\learning\LnExaminationQuestion;
use common\models\learning\LnExamQuestionCopy;
use common\models\learning\LnExamQuestionOption;
use common\models\learning\LnExamQuestOptionCopy;
use common\models\learning\LnExamQuestOptionUser;
use common\models\learning\LnExamQuestionUser;
use common\models\learning\LnExaminationResultUser;
use common\models\learning\LnExamResultDetail;
use common\models\learning\LnHomeworkResult;
use common\models\learning\LnModRes;
use common\services\message\TimelineService;
use common\base\BaseActiveRecord;
use common\eLearningLMS;
use common\helpers\TStringHelper;
use components\widgets\TPagination;
use stdClass;
use Yii;
use yii\data\ActiveDataProvider;
use Exception;
use PDO;
use yii\data\Pagination;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\db\Query;
use common\models\learning\LnExamination;
use yii\log\Logger;
use yii\web\YiiAsset;


class ExaminationService extends LnExamination
{

    const IS_RIGHT_YES = 'yes'; //选择正确
    const IS_RIGHT_NO = 'no';   //选择错误

    const LEARNING_DURATION = '30'; /*更新时长*/

    const PLAY_MODE_NORMAL = 'normal';
    const PLAY_MODE_PREVIEW = 'preview';

    /**
     * 获取试题列表
     * @param $params
     * @param bool|false $split //是否将数据与分页分离
     * @return ActiveDataProvider
     */
    public function search($params, $split = false){
        if (!empty($params['companyId'])) {
            $company_id = $params['companyId'];
        }else{
            $company_id = Yii::$app->user->identity->company_id;
        }
        $query = LnExamination::find(false);
        if (isset($params['TreeNodeKid'])){
            $tree_node_id = explode(',', $params['TreeNodeKid']);
            $categories = $this->getTreeNodeIdToCategoryId($tree_node_id);
        }else{
            $categories = !empty($params['category_id']) ? array($params['category_id']) : "";
        }

        if (!empty($categories)) {
            $query->andFilterWhere(['in', 'category_id', $categories]);
        }


        if (!empty($params['keywords'])) {
            $keywords = TStringHelper::clean_xss($params['keywords']);
            $query->andWhere("`title` like '%{$keywords}%' OR `code` like '%{$keywords}%' OR `description` like '%{$keywords}%'");
        }

        if (isset($params['examination_mode'])) {
            $query->andFilterWhere(['=', 'examination_mode', $params['examination_mode']]);
        }

        if (isset($params['examination_range'])) {
            $query->andFilterWhere(['=', 'examination_range', $params['examination_range']]);
        }

        if (isset($params['release_status'])){
            $query->andFilterWhere(['=', 'release_status', $params['release_status']]);
        }
        $query->andFilterWhere(['=', 'company_id', $company_id]);
        $query->addOrderBy(['created_at' => SORT_DESC]);
//        echo ($query->createCommand()->getRawSql());
        if ($split){
            $count = $query->count();
            $pages = new TPagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
            $data = $query->offset($pages->offset)->limit($pages->limit)->all();
            $dataProvider = array(
                'pages' => $pages,
                'data' => $data,
            );
            return $dataProvider;
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

//        $this->load($params);
//        if (!$this->validate()) {
//            return $dataProvider;
//        }
        $dataProvider->setSort(false);
        return $dataProvider;
    }
    /*获取编号*/
    public function setExamCode($kid=null){
        $start_at = strtotime(date('Y-m-d'));
        $end_at = $start_at+86399;
        $count = $this->find()->where("created_at>".$start_at)->andWhere("created_at<".$end_at)->count();
        $count = $count+1;/*默认成1开始*/
        return date('Ymd').sprintf("%03d", $count);
    }
    /*获取编号*/
    public function setExamVersion($kid=null){
        if (empty($kid)) return date('Ymd') . '001';
        $model = new LnExamination();
        $condition = ['kid' => $kid];
        $result = $model->findOne($condition,false);
        $course_version = $result->examination_version;
        if (substr($course_version, 0, 8) == date('Ymd')) {
            $last_version = substr($course_version, -3);
            return date('Ymd') . sprintf("%03d", intval($last_version) + 1);
        } else {
            return date('Ymd') . '001';
        }
    }

    /*tree_node_id 转 category_id*/
    public function getTreeNodeIdToCategoryId($tree_node_id){
        if (empty($tree_node_id)) return null;
        $categories = LnExaminationCategory::findAll(['tree_node_id'=>$tree_node_id],false);
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

    /*category_id 转 tree_node_id*/
    public function getCategoryIdToTreeNodeId($category_id){
        if (empty($category_id)) return null;
        $find = LnExaminationCategory::findOne($category_id);
        return $find ? $find->tree_node_id : '';
    }

    /*tree_node_id 转 category_id*/
    public function GetQuestionTreeNodeIdToCategoryId($tree_node_id){
        if (empty($tree_node_id)) return null;
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

    /*category_id 转 tree_node_id*/
    public function GetQuestionCategoryIdToTreeNodeId($category_id){
        if (empty($category_id)) return null;
        $find = LnExamQuestionCategory::findOne($category_id);
        return $find ? $find->tree_node_id : '';
    }
    /*获取及格线*/
    public function GetPassGrade($id){
        if (empty($id)) return null;
        $examination = LnExamination::findOne($id);
        //$total_score = LnExaminationPaperCopy::findOne($examination->examination_paper_copy_id)->default_total_score;//及格线现在已改成百分比形式
        $pass_grade = round($examination->pass_grade);
        return $pass_grade ;
    }

    /**
     *获取考试分类
     */
    public function GetExaminationCategory($params = null){
        $query = LnExaminationCategory::find(false);
        $company_id = Yii::$app->user->identity->company_id;
        $query->andFilterWhere(['=', 'company_id', $company_id]);
        $query->andFilterWhere(['=', 'status', LnExaminationCategory::STATUS_FLAG_NORMAL]);
        if (isset($params['category_name'])){
            $query->andWhere("category_name like '%{$params['category_name']}%'");
        }
        if (isset($params['parent_category_id'])){
            $query->andFilterWhere(['=', 'parent_category_id', $params['parent_category_id']]);
        }
        $result = $query->all();
        return $result;
    }
    /**
     * @param null $examination_mode
     * @return array|\yii\db\ActiveRecord[]
     */
    public function searchPaper($examination_mode = null){
        //$list = LnExaminationPaper::findAll([],false);
        $company_id = Yii::$app->user->identity->company_id;
        $model = LnExaminationPaper::find(false);
        if (!is_null($examination_mode)){
            $model->andFilterWhere(['examination_paper_type'=> $examination_mode]);
        }
        $model->andFilterWhere(['=', 'company_id', $company_id]);
        $list = $model->asArray()
            ->select('kid,title,default_total_score')
            ->orderBy('created_at desc')
            ->asArray()
            ->all();

        if (!empty($list)){
            foreach ($list as $key => $val){
                $list[$key]['examination_question_number'] = LnExamPaperQuestion::find(false)->andFilterWhere(['examination_paper_id' => $val['kid'], 'status' => LnExamPaperQuestion::STATUS_FLAG_NORMAL, 'relation_type' => LnExamPaperQuestion::RELATION_TYPE_PAPER])->count();
            }
        }

        return $list;
    }

    /**
     * 添加试卷
     * @param $examination_paper_id
     * @return bool|string
     */
    public function copyExamination(LnExamination $model){
        $examination_paper_id = $model->examination_paper_id;
        $paper = LnExaminationPaper::findOne($examination_paper_id);
        if (empty($paper->kid)){
            return false;
        }
        //$this->deleteExamRelation($model, false);//删除以前保存的数据关联
        $paperCopyModel = new LnExaminationPaperCopy();
        $paperCopyModel->category_id = $paper->category_id;
        $paperCopyModel->examination_paper_id = $examination_paper_id;
        $paperCopyModel->exam_quest_category_id = $paper->category_id;//此字段有异议
        $paperCopyModel->company_id = $paper->company_id;
        $paperCopyModel->question_from = LnExaminationPaperCopy::QUESTION_FROM_PAPER;
        $paperCopyModel->title = $paper->title;
        $paperCopyModel->code = $paper->code;
        $paperCopyModel->description = $paper->description;
        $paperCopyModel->default_total_score = $paper->default_total_score;
        $paperCopyModel->result_output_type = $paper->result_output_type ? $paper->result_output_type : LnExaminationPaper::RESULT_OUTPUT_TYPE_AUTO;
        $paperCopyModel->examination_paper_type = $paper->examination_paper_type;
        $paperCopyModel->examination_paper_level = $paper->examination_paper_level;
        $paperCopyModel->examination_question_number = $paper->examination_question_number;
        $paperCopyModel->paper_version = $paper->paper_version;
        $paperCopyModel->needReturnKey = true;
        if($paperCopyModel->save()){
            $paperQuestionList = LnExamPaperQuestion::findAll(['examination_paper_id'=>$examination_paper_id,'status'=>LnExaminationQuestion::STATUS_FLAG_NORMAL],false);
            if (!empty($paperQuestionList)){
                foreach ($paperQuestionList as $items){
                    $paperQuestionCopyModel = new LnExamPaperQuestCopy();
                    /*判断是否分页符*/
                    $default_score = $items->default_score;
                    //$is_allow_change_score = LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_YES;
                    $is_allow_change_score = '';
                    if ($items->relation_type == LnExaminationPaper::RELATION_TYPE_PAGE){
                        $paperQuestionCopyModel->examination_question_copy_id = null;
                    }else {
                        $questionOne = LnExaminationQuestion::findOne($items->examination_question_id);
                        /*判断试题是否删除*/
                        if (empty($questionOne->kid)){
                            continue ;//跳出单次循环
                        }
                        //判断是否可以修改默认分，可以则同步paparquestion分数
                        if ($questionOne->is_allow_change_score == LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_NO){
                            $is_allow_change_score = LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_NO;
                            $default_score = $questionOne->default_score;
                        }else{
                            $is_allow_change_score = LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_YES;
                        }
                        $newQuestionCopyModel = new LnExamQuestionCopy();
                        $fields = $questionOne->attributes;
                        $newQuestionCopyModel->category_id = $fields['category_id'];
                        $newQuestionCopyModel->examination_question_id = $items->examination_question_id;
                        $newQuestionCopyModel->company_id = $fields['company_id'];
                        $newQuestionCopyModel->title = $fields['title'];
                        $newQuestionCopyModel->code = $fields['code'];
                        $newQuestionCopyModel->examination_question_type = $fields['examination_question_type'];
                        $newQuestionCopyModel->result_output_type = $fields['result_output_type'] ? $fields['result_output_type'] : LnExaminationQuestion::RESULT_OUTPUT_TYPE_AUTO;
                        $newQuestionCopyModel->is_allow_change_score = $fields['is_allow_change_score'];
                        $newQuestionCopyModel->description = $fields['description'];
                        $newQuestionCopyModel->answer = $fields['answer'];
                        $newQuestionCopyModel->examination_question_level = $fields['examination_question_level'];
//                        $newQuestionCopyModel->default_score = $fields['default_score'];
                        $newQuestionCopyModel->default_score = $default_score;//20160217
                        $newQuestionCopyModel->question_version = $fields['question_version'];
                        $newQuestionCopyModel->sequence_number = $fields['sequence_number'];
                        $newQuestionCopyModel->needReturnKey = true;
                        if (!$newQuestionCopyModel->save()) {
                            return array('model' => 'newQuestionCopyModel', 'errmsg' => $newQuestionCopyModel);
                        }
                        $paperQuestionCopyModel->examination_question_copy_id = $newQuestionCopyModel->kid;
                    }
                    $paperQuestionCopyModel->examination_paper_copy_id = $paperCopyModel->kid;
                    $paperQuestionCopyModel->default_score = $items->default_score;
                    $paperQuestionCopyModel->relation_type = $items->relation_type;
                    $paperQuestionCopyModel->sequence_number = $items->sequence_number;
                    $paperQuestionCopyModel->status = $items->status;
                    $paperQuestionCopyModel->start_at = $items->start_at;
                    $paperQuestionCopyModel->end_at = $items->end_at;
                    $paperQuestionCopyModel->needReturnKey = true;
                    if (!$paperQuestionCopyModel->save()){
                        return array('model' => 'paperQuestionCopyModel', 'errmsg' => $paperQuestionCopyModel);
                    }

                    $oldQuestionOption = LnExamQuestionOption::findAll(['examination_question_id'=>$items->examination_question_id],false);
                    foreach ($oldQuestionOption as $sval){
                        $newQuestionOptionCopyModel = new LnExamQuestOptionCopy();
                        $newQuestionOptionCopyModel->examination_question_copy_id = $newQuestionCopyModel->kid;
                        $newQuestionOptionCopyModel->examination_question_option_id = $sval->kid;
                        $newQuestionOptionCopyModel->option_title = $sval->option_title;
                        $newQuestionOptionCopyModel->option_description = $sval->option_description;
                        $newQuestionOptionCopyModel->default_score = ($is_allow_change_score == LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_YES && $sval->default_score > 0) ? $default_score : $sval->default_score;
                        $newQuestionOptionCopyModel->is_right_option = $sval->is_right_option;
                        $newQuestionOptionCopyModel->option_stand_result = $sval->option_stand_result;
                        $newQuestionOptionCopyModel->sequence_number = $sval->sequence_number;
                        $newQuestionOptionCopyModel->option_version = $sval->option_version;
                        if (!$newQuestionOptionCopyModel->save()){
                            return array('model' => 'newQuestionOptionCopyModel', 'errmsg' => $newQuestionOptionCopyModel);
                        }
                    }
                }
                return $paperCopyModel->kid;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 删除试卷各种复制表数据
     * @param $examinationModel
     * @return bool
     */
    public function deleteExamRelation(&$examinationModel, $exam_del = true){
        $paperQuestionCopySql = LnExaminationPaperCopy::find(false)->andFilterWhere(['kid'=>$examinationModel->examination_paper_copy_id])->select('kid')->distinct()->createCommand()->getRawSql();
        $questionCopySql = LnExamPaperQuestCopy::find(false)->andWhere("`examination_paper_copy_id` in ({$paperQuestionCopySql})")->select('examination_question_copy_id')->createCommand()->getRawSql(); //镜像试题ID
        LnExamQuestOptionCopy::deleteAll("`examination_question_copy_id` in ({$questionCopySql})");
        LnExamQuestionCopy::deleteAll("`kid` in ({$questionCopySql})");
        LnExamPaperQuestCopy::deleteAll("`examination_paper_copy_id` in ({$paperQuestionCopySql})");
        LnExaminationPaperCopy::deleteAll("`kid`='{$examinationModel->examination_paper_copy_id}'");//镜像试卷记录
        if ($exam_del) {
            $examinationModel->delete();//考试记录
        }
    }

    /**
     * 查询参与考试的学员
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchExaminationResultUser($params, $query_all = false){
        $resultUserModel = LnExaminationResultUser::find(false);
        if (!empty($params['examination_id'])){
            $resultUserModel->andFilterWhere(['examination_id' => $params['examination_id']]);
        }
        if (isset($params['examination_status']) && $params['examination_status'] != ""){
            $resultUserModel->andFilterWhere(['examination_status' => $params['examination_status']]);
        }
        if (isset($params['examination_score']) && $params['examination_score'] != "" && isset($params['expression']) && $params['expression'] != ""){
            if ($params['expression'] == '0'){
                $params['expression'] = '>=';
            }else if ($params['expression'] == '1'){
                $params['expression'] = '<=';
            }else if ($params['expression'] == '2') {
                $params['expression'] = '=';
            }
            $model = LnExamination::findOne($params['examination_id']);
            if ($model->examination_mode == LnExamination::EXAMINATION_MODE_TEST){
                $field = 'examination_score';
            }else{
                $field = 'correct_rate';
            }
            $resultUserModel->andFilterWhere([$params['expression'], $field, floatval($params['examination_score'])]);
        }
        if (!empty($params['course_id'])){
            $resultUserModel->andFilterWhere(['=','course_id',$params['course_id']]);
        }else{
            $resultUserModel->andWhere("ISNULL(course_id)");
        }
        if (!empty($params['mod_id'])){
            $resultUserModel->andFilterWhere(['=','mod_id',$params['mod_id']]);
        }else{
            $resultUserModel->andWhere("ISNULL(mod_id)");
        }
        if (!empty($params['mod_res_id'])){
            $resultUserModel->andFilterWhere(['=','mod_res_id',$params['mod_res_id']]);
        }else{
            $resultUserModel->andWhere("ISNULL(mod_res_id)");
        }
        if (!empty($params['courseactivity_id'])){
            $resultUserModel->andFilterWhere(['=','courseactivity_id',$params['courseactivity_id']]);
        }else{
            $resultUserModel->andWhere("ISNULL(courseactivity_id)");
        }
        if (isset($params['result_type'])){
            $resultUserModel->andFilterWhere(['=','result_type',$params['result_type']]);
        }
//        var_dump($resultUserModel->createCommand()->getRawSql());exit;
        $resultUserModel->innerJoin(FwUser::tableName() . " as u", LnExaminationResultUser::tableName().'.user_id = u.kid');
        if (isset($params['user_keyword'])){
            $keywords = trim(urldecode($params['user_keyword']));
            $resultUserModel->andWhere("u.real_name like '%{$keywords}%' OR u.email like '%{$keywords}%'");
        }
        $resultUserModel->groupBy('user_id');
        $count = $resultUserModel->count();
        $resultUserModel->select( [
            'kid' => LnExaminationResultUser::tableName().'.kid',
            'user_id' => LnExaminationResultUser::tableName().'.user_id',
            'examination_id' => LnExaminationResultUser::tableName().'.examination_id',
            'company_id' => LnExaminationResultUser::tableName().'.company_id',
            'user_real_name' => 'u.real_name',
            'orgnization_id' => 'u.orgnization_id',
            'user_email' => 'u.email',
            'user_mobile' => 'u.mobile_no',
            'examination_status' => LnExaminationResultUser::tableName().'.examination_status',
            'end_at' => LnExaminationResultUser::tableName().'.end_at',
            'all_number' => LnExaminationResultUser::tableName().'.all_number',
            'correct_number' => LnExaminationResultUser::tableName().'.correct_number',
            'correct_rate' => LnExaminationResultUser::tableName().'.correct_rate',
        ]);
        $pages = new TPagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
        $data = $resultUserModel->offset($pages->offset)->limit($pages->limit)->asArray()->all();
        //echo $resultUserModel->createCommand()->getRawSql();
        if (!empty($data)){
            foreach ($data as $key => $val) {
                $examination_id = $val['examination_id'];
                $user_id = $val['user_id'];
                $score = $this->GetExaminationGrade($user_id, $examination_id, $params['course_id'], $params['mod_res_id'], $params['mod_id']);
                $data[$key]['score'] = $score;
                $data[$key]['last'] = $this->GetExaminationUserResultLast($user_id, $examination_id, $params['course_id'], $params['mod_res_id'], $params['mod_id']);
            }
        }
        //var_dump($resultUserModel->createCommand()->getRawSql());
        return ['data' => $data, 'page' => $pages];
    }

    /**
     * 随机生成个人试卷
     * @param string $examinationId 考试ID
     * @param string $companyId 企业ID
     * @param string $userId 用户ID
     * @param string $modResId 结果，OK 或 ERROR
     * @param string $courseRegId 注册ID，如果非课程考试，直接输入空
     * @param string $courseCompleteId 课程完成ID，如果非课程考试，直接输入空
     * @param string $resCompleteId 资源完成ID，如果非课程考试，直接输入空
     * @param integer $courseAttemptNumber 考试尝试次数
     * @param string $result 结果，OK 或 ERROR
     * @param string $examinationPaperUserId 个人试卷ID
     * @param string $examinationResultFinalId 个人试卷最终结果ID
     * @param string $examinationResultProcessId 个人试卷过程结果ID
     * @param string $errMessage 错误信息
     */
    public function generateUserPaperByExam($examinationId, $companyId, $userId, $modResId = "", $courseRegId = "", $courseCompleteId = "",
                                                    $resCompleteId = "", $courseAttemptNumber = 1,&$result, &$examinationPaperUserId, &$examinationResultFinalId,
                                                    &$examinationResultProcessId, &$errMessage){
        try {
            $sql = "CALL generate_user_paper_by_exam(:ExaminationId, :CompanyId, :UserId,"
                . ":ModResId,:CourseRegId,:CourseCompleteId,:ResCompleteId,:CourseAttemptNumber)";

            $inputParams = array();
            $paramExaminationId = new stdClass();
            $paramExaminationId->name = ":ExaminationId";
            $paramExaminationId->value = $examinationId;
            $paramExaminationId->type = PDO::PARAM_STR;
            $inputParams[] = $paramExaminationId;

            $paramCompanyId = new stdClass();
            $paramCompanyId->name = ":CompanyId";
            $paramCompanyId->value = $companyId;
            $paramCompanyId->type = PDO::PARAM_STR;
            $inputParams[] = $paramCompanyId;

            $paramUserId = new stdClass();
            $paramUserId->name = ":UserId";
            $paramUserId->value = $userId;
            $paramUserId->type = PDO::PARAM_STR;
            $inputParams[] = $paramUserId;

            $paramModResId = new stdClass();
            $paramModResId->name = ":ModResId";
            $paramModResId->value = $modResId;
            $paramModResId->type = PDO::PARAM_STR;
            $inputParams[] = $paramModResId;

            $paramCourseRegId = new stdClass();
            $paramCourseRegId->name = ":CourseRegId";
            $paramCourseRegId->value = $courseRegId;
            $paramCourseRegId->type = PDO::PARAM_STR;
            $inputParams[] = $paramCourseRegId;

            $paramCourseCompleteId = new stdClass();
            $paramCourseCompleteId->name = ":CourseCompleteId";
            $paramCourseCompleteId->value = $courseCompleteId;
            $paramCourseCompleteId->type = PDO::PARAM_STR;
            $inputParams[] = $paramCourseCompleteId;

            $paramResCompleteId = new stdClass();
            $paramResCompleteId->name = ":ResCompleteId";
            $paramResCompleteId->value = $resCompleteId;
            $paramResCompleteId->type = PDO::PARAM_STR;
            $inputParams[] = $paramResCompleteId;

            $paramCourseAttemptNumber = new stdClass();
            $paramCourseAttemptNumber->name = ":CourseAttemptNumber";
            $paramCourseAttemptNumber->value = $courseAttemptNumber;
            $paramCourseAttemptNumber->type = PDO::PARAM_INT;
            $inputParams[] = $paramCourseAttemptNumber;

//        $outParams = array();
//        $paramResult = new stdClass();
//        $paramResult->name = "Result";
//        $outParams[] = $paramResult;
//
//        $paramMessage = new stdClass();
//        $paramMessage->name = "Message";
//        $outParams[] = $paramMessage;

            $procedureResult = eLearningLMS::queryScalar($sql, $inputParams);
//        $result = $paramResult->value;

            if (!empty($procedureResult)) {
                $procedureResultArray = explode("-", $procedureResult);
                $result = $procedureResultArray[0];
                /*处理有问题，更改@author:adophper<hello@adophper.com> @date:20160606 14:41*/
                //$message = $procedureResultArray[1];
                unset($procedureResultArray[0]);
                $message = join('-', $procedureResultArray);
                if ($result == "OK") {
                    $tempMessage = explode("|", $message);//格式是：ExaminationPaperUserId|ExaminationResultFinalId|ExaminationResultProcessId|SuccessCount|SuccessScore
                    $examinationPaperUserId = $tempMessage[0];
                    $examinationResultFinalId = $tempMessage[1];
                    $examinationResultProcessId = $tempMessage[2];
                }
            }
        }catch (Exception $e) {
            $errMessage = 'Message: '.$e->getMessage();
        }
    }

    /**
     * 获取用户最近一次未完成的考试
     * @param $examinationId
     * @param $userId
     * @param $companyId
     * @param string $courseId
     * @param string $courseRegId
     * @param string $modId
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getExaminationProcessLast($examinationId, $userId, $companyId, $courseId = "", $courseRegId = "", $modId = "", $modResId = ""){
        $model = LnExaminationResultUser::find(false);
        $result = $model->andFilterWhere(['examination_id' => $examinationId])
            ->andFilterWhere(['user_id' => $userId,'company_id' => $companyId])
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'course_reg_id', $courseRegId])
            ->andFilterWhere(['=', 'mod_id', $modId])
            ->andFilterWhere(['=', 'mod_res_id', $modResId])
            ->andFilterWhere(['=', 'result_type', LnExaminationResultUser::RESULT_TYPE_PROCESS])
            ->andFilterWhere(['in', 'examination_status', array(LnExaminationResultUser::EXAMINATION_STATUS_NOT,LnExaminationResultUser::EXAMINATION_STATUS_START)])
            ->one();

        return $result;
    }

    /**
     * 获取用户过程考试次数
     * @param $examinationId
     * @param $userId
     * @param $companyId
     * @param string $courseId
     * @param string $courseRegId
     * @param string $modId
     * @return int|string
     */
    public function getExaminationProcessCount($examinationId, $userId, $companyId, $status = "", $courseId = "", $courseRegId = "", $modId = "", $modResId = ""){
        $model = LnExaminationResultUser::find(false);
        $model->andFilterWhere(['examination_id' => $examinationId])
            ->andFilterWhere(['user_id' => $userId,'company_id' => $companyId])
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'course_reg_id', $courseRegId])
            ->andFilterWhere(['=', 'mod_id', $modId])
            ->andFilterWhere(['=', 'mod_res_id', $modResId])
            ->andFilterWhere(['=', 'result_type', LnExaminationResultUser::RESULT_TYPE_PROCESS]);
        if (!empty($status)){
            $model->andFilterWhere(['=', 'examination_status', $status]);
        }
        $count = $model ->count('kid');

        return $count;
    }

    /**
     * 获取考试基本信息
     * @param $examination_id
     * @param null $userId
     * @param null $company_id
     */
    public function GetExaminationByUserOne($examination_id, $userId = null, $company_id = null){
        return LnExamination::find(false)
            ->leftJoin(LnExaminationPaperUser::tableName().' as paper', 'paper.examination_id='.LnExamination::tableName().'.kid and paper.examination_paper_copy_id='.LnExamination::tableName().'.examination_paper_copy_id')
            ->andFilterWhere(['=',  LnExamination::tableName() .'.kid', $examination_id])
            ->select([LnExamination::tableName().'.*', 'paper.examination_question_number'])
            ->asArray()
            ->one();
    }

    /**
     * 获取考试基本信息
     * @param $examination_id
     * @param null $userId
     * @param null $company_id
     */
    public function GetExaminationByCopyOne($examination_id, $userId = null, $company_id = null){
        return LnExamination::find(false)
            ->leftJoin(LnExaminationPaperCopy::tableName().' as paper', 'paper.kid='.LnExamination::tableName().'.examination_paper_copy_id')
            ->andFilterWhere(['=',  LnExamination::tableName() .'.kid', $examination_id])
            ->select([LnExamination::tableName().'.*', 'paper.examination_question_number'])
            ->asArray()
            ->one();
    }

    /**
     * 返回回答正确数
     * @param $examination_paper_user_id
     * @return int
     */
    public function GetQuestionUserIsRightNumber($examination_paper_user_id, $examination_result_process_id){
        $paperQuestionUser = LnExamPaperQuestUser::find(false)
            ->andFilterWhere(['examination_paper_user_id' => $examination_paper_user_id])
            ->asArray()
            ->all();
        $count = 0;
        if (!empty($paperQuestionUser)){
            foreach ($paperQuestionUser as $item){
                $questionUser = LnExamQuestionUser::findOne($item['examination_question_user_id']);
               if ($questionUser->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX){
                   $sql_1 = LnExamQuestOptionUser::find(false)->andFilterWhere(['examination_question_user_id' => $item['examination_question_user_id'], 'is_right_option' => LnExamQuestionOption::IS_RIGHT_OPTION_YES]);//->asArray()->all();
                   $orgi_count = $sql_1->count();
                   $sql = $sql_1->distinct()->select('kid')->createCommand()->getRawSql();
                   $real_count = LnExamResultDetail::find(false)->andWhere("examination_option_user_id in ({$sql})")->andFilterWhere(['option_result' => LnExamResultDetail::OPTION_RESULT_YES])->count();
                }else{
                   $orgi_count = 1;
                   $real_count = LnExamResultDetail::find(false)->andFilterWhere(['examination_question_user_id' => $item['examination_question_user_id'], 'examination_result_process_id' => $examination_result_process_id, 'option_result' => LnExamResultDetail::OPTION_RESULT_YES])->count();
               }
                if ($orgi_count > 0 && $orgi_count == $real_count){
                    $count ++;
                }
            }
        }
        return $count;


    }

    /**
     * 返回学习列表
     * @param $uid
     * @param $examination_id
     * @param $mod_id
     * @param $modResId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetExaminationByUserResultAll($uid, $examination_id, $company_id = null, $mod_id = null, $modResId = null, $courseId = null, $attempt = null){
        $query = LnExaminationResultUser::find(false)
            ->andFilterWhere(['user_id' => $uid, 'examination_id' => $examination_id, 'result_type' => LnExaminationResultUser::RESULT_TYPE_PROCESS]);
        if (!empty($company_id)){
            $query->andFilterWhere(['company_id' => $company_id]);
        }
        if (!empty($mod_id)){
            $query->andFilterWhere(['mod_id' => $mod_id]);
        }else{
            $query->andWhere("ISNULL(mod_id)");
        }
        if (!empty($modResId)){
            $query->andFilterWhere(['mod_res_id' => $modResId]);
        }else{
            $query->andWhere("ISNULL(mod_res_id)");
        }
        if (!empty($courseId)){
            $query->andFilterWhere(['course_id' => $courseId]);
        }else{
            $query->andWhere("ISNULL(course_id)");
        }
        if (!empty($attempt)){
            $query->andFilterWhere(['=', 'course_attempt_number', $attempt]);
        }
//           $result = $query->andFilterWhere(['in','examination_status',array('1','2')])
           $result = $query->andFilterWhere(['=','examination_status',LnExaminationResultUser::EXAMINATION_STATUS_END])
            ->distinct('examination_paper_user_id')
               ->orderBy('created_at')
            ->asArray()
            ->all();

        return $result;
    }

    /**
     * 返回学员的学习记录
     * @param $modResId
     * @param $mode
     * @return array|void
     */
    public function GetExaminationInfoByModResId($modResId, $mode,$userId=null,$companyId=null){
        $modResModel = LnModRes::findOne($modResId);
        if (empty($modResModel->kid)){
            return null;
        }
        $courseactivityModel = LnCourseactivity::findOne($modResModel->courseactivity_id);
        if (empty($courseactivityModel->kid)){
            return null;
        }
        $examinationModel = $this->GetExaminationByUserOne($courseactivityModel->object_id);
        $examinationResultUserModel = null;
        if ($mode == 'normal'){
            $uid = Yii::$app->user->getId();
            $company_id = Yii::$app->user->identity->company_id;

            if(!empty($userId)){
                $uid = $userId;
            }
            if(!empty($userId)){
                $company_id = $companyId;
            }

            $examinationResultUserModel = $this->GetExaminationByUserResultAll($uid, $courseactivityModel->object_id, $company_id, $modResModel->mod_id, $modResId, $modResModel->course_id);
        }

        return [
            'kid' => $examinationModel['kid'],
            'mod_id' => $modResModel->mod_id,
            'courseactivity_id' => $courseactivityModel->kid,
            'component_id' => $courseactivityModel->component_id,
            'mod_res' => $modResModel,
            'courseactivity' => $courseactivityModel,
            'examination' => $examinationModel,
            'examinationResultUser' => $examinationResultUserModel,
        ];
    }

    /**
     * 获取考试试题
     * @param $examination_id
     * @return array|bool
     */
    public function GetExaminationPaperQuestionUser($examination_id, $examination_paper_user_id,array $question_fields = [],array $option_fields = []){
        $model = LnExamination::findOne($examination_id);
        if (empty($model->kid)){
            return false;
        }
        /*个人试卷*/
        $userPaper = LnExaminationPaperUser::findOne($examination_paper_user_id);//->createCommand()->getRawSql();

        $query = LnExamPaperQuestUser::find(false)
            ->andFilterWhere(['examination_paper_user_id' => $userPaper->kid])
            ->andFilterWhere(['status' => '1'])
            ->orderBy('sequence_number');
        if(!empty($question_fields)) {
            $query = $query->select($question_fields);
        }
        $userPaperQuestionModel = $query->asArray()->all();

        if (empty($userPaperQuestionModel)) {
            return false;
        }
        $result = [];
        $page = 0;
        foreach ($userPaperQuestionModel as $key => $item){
            if ($item['relation_type'] == LnExamPaperQuestion::RELATION_TYPE_HR){
                $item['options'] = null;
                $page ++;
            }else{
                $questionCopyInfo = LnExamQuestionUser::find(false)
                    ->andFilterWhere(['=','kid',$item['examination_question_user_id']])
                    ->select([
                        'qu_id' => 'kid',
                        'title',
                        'examination_question_type',
                        'result_output_type',
                        'description',
                        'answer',
                        'examination_question_level',
                    ])
                    ->asArray()
                    ->one();

                if (!empty($questionCopyInfo)){
                    $item = array_merge($item, $questionCopyInfo);
                    $options = $this->GetQuestionOptionsUser($item['examination_question_user_id'],null,$option_fields);
                    $item['options'] = $options;
                }
            }
            $result[] = $item;
        }
        $end = end($result);
        if ($end['relation_type'] == LnExamPaperQuestion::RELATION_TYPE_PAPER){
            $result[] = array(
                'relation_type' => LnExamPaperQuestion::RELATION_TYPE_HR,
                'options' => null,
                'kid' => null,
            );
            $page ++;
        }

        return ['result' => $result, 'page' => $page];

    }

    /**
     * @param $examination_question_user_id
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetQuestionOptionsCopy($examination_question_copy_id){
        return $lnExamQuestionOption = LnExamQuestOptionCopy::find(false)
            ->andFilterWhere(["=","examination_question_copy_id",$examination_question_copy_id])
            ->orderBy('sequence_number')
            ->asArray()
            ->all()
            ;
    }

    /**
     * 获取考试试题
     * @param $examination_id
     * @return array|bool
     */
    public function GetExaminationPaperQuestionCopyPreview($examination_id){
        $model = LnExamination::findOne($examination_id);
        if (empty($model->kid)){
            return false;
        }
        /*个人试卷*/
        $userPaper = LnExaminationPaperCopy::findOne($model->examination_paper_copy_id);//->createCommand()->getRawSql();

        $userPaperQuestionModel = LnExamPaperQuestCopy::find(false)
            ->andFilterWhere(['examination_paper_copy_id' => $userPaper->kid])
            ->andFilterWhere(['status' => '1'])
            ->orderBy('sequence_number')
            ->asArray()
            ->all();

        if (empty($userPaperQuestionModel)) {
            return false;
        }
        $result = [];
        $page = 0;
        foreach ($userPaperQuestionModel as $key => $item){
            if ($item['relation_type'] == LnExamPaperQuestion::RELATION_TYPE_HR){
                $item['options'] = null;
                $page ++;
            }else{
                $questionCopyInfo = LnExamQuestionCopy::find(false)
                    ->andFilterWhere(['=','kid',$item['examination_question_copy_id']])
                    ->select([
                        'qu_id' => 'kid',
                        'title',
                        'examination_question_type',
                        'result_output_type',
                        'description',
                        'answer',
                        'examination_question_level',
                    ])
                    ->asArray()
                    ->one();

                if (!empty($questionCopyInfo)){
                    $item = array_merge($item, $questionCopyInfo);
                    $options = $this->GetQuestionOptionsCopy($item['examination_question_copy_id']);
                    $item['options'] = $options;
                }
            }
            $result[] = $item;
        }
        $end = end($result);
        if ($end['relation_type'] == LnExamPaperQuestion::RELATION_TYPE_PAPER){
            $result[] = array(
                'relation_type' => LnExamPaperQuestion::RELATION_TYPE_HR,
                'options' => null,
                'kid' => null,
            );
            $page ++;
        }

        return ['result' => $result, 'page' => $page];

    }

    /**
     * @param $examination_question_user_id
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetQuestionOptionsUser($examination_question_user_id, $option_disorder = null,array $option_fields = null){
        $query = LnExamQuestOptionUser::find(false)
            ->andFilterWhere(["=","examination_question_user_id",$examination_question_user_id])
            ->orderBy('sequence_number');
        if(!empty($option_fields)) {
            $query = $query->select($option_fields);
        }
        return $query->asArray()->all();
    }

    /**
     * @param $param
     * 注：scoreBefore 考试成绩
     * completeGrade 学分
     */
    public function addResCompleteDoneInfo($param,&$courseComplete=false,&$getCetification=false,&$courseId=null,&$certificationId=null){
        $model = LnExamination::findOne($param['examination_id']);
        if ($param['complete_type'] == LnResComplete::COMPLETE_TYPE_PROCESS) {
            $examinationResultUser = LnExaminationResultUser::findOne($param['examination_process_id']);
        } else {
            $examinationResultUser = LnExaminationResultUser::findOne($param['examination_complete_id']);
        }
        if ($model->examination_mode == LnExamination::EXAMINATION_MODE_TEST) {
            $completeScore = $examinationResultUser->examination_score;
        }else{
            $completeScore = $examinationResultUser->correct_rate;/*考试正确百分比*/
        }
        $resourceCompleteService=new ResourceCompleteService();
        $resourceCompleteService->addResCompleteDoneInfo($param['course_complete_id'], $param['course_reg_id'], $param['mod_res_id'], $param['complete_type'], $completeScore, null, true,$this->systemKey,true,false,$courseComplete,$getCetification,$courseId,$certificationId);
    }

    /**
     * 考试结果处理
     * @param $params
     */
    public function SubmitResult($params,$userId=null,$companyId=null){
        $user_id = Yii::$app->user->getId();
        $company_id = Yii::$app->user->identity->company_id;

        if(empty($user_id) || empty($company_id)){
            $user_id = $userId;
            $company_id = $companyId;
        }
        $options = $params['options'];
        $time = time();
        $score = 0;
        $correct_number = 0;
        $error_number = 0;
        $examinationModel = LnExamination::findOne($params['examination_id']);
        $findResultFinalUser = LnExaminationResultUser::find(false)->andFilterWhere([
            'examination_id' => $params['examination_id'],
            'examination_paper_user_id' => $params['examination_paper_user_id'],/*必须传此参数*/
            'company_id' => $company_id,
            'user_id' => $user_id,
            'result_type' => LnExaminationResultUser::RESULT_TYPE_FINALLY,
        ])->one();
        if (empty($findResultFinalUser)){
            return ['result' => 'fail', 'errmsg' => '数据异常', 'errmodel' => 'final'];
        }
        $findResultProcessUser = LnExaminationResultUser::find(false)->andFilterWhere([
            'examination_id' => $params['examination_id'],
            'examination_paper_user_id' => $params['examination_paper_user_id'],
            'user_id' => $user_id,
            'company_id' => $company_id,
            'course_id' => $params['course_id'],
            'course_reg_id' => $params['course_reg_id'],
            'mod_id' => $params['mod_id'],
            'mod_res_id' => $params['mod_res_id'],
            'courseactivity_id' => $params['courseactivity_id'],
            'course_complete_id' => $params['course_complete_id'],
//            'res_complete_id' => $params['res_complete_id'],
            'result_type' => LnExaminationResultUser::RESULT_TYPE_PROCESS,
            'examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_START
        ])->one();
        if (empty($findResultProcessUser)){
            $findResultProcessUser = LnExaminationResultUser::find(false)->andFilterWhere([
                'examination_id' => $params['examination_id'],
                'examination_paper_user_id' => $params['examination_paper_user_id'],
                'user_id' => $user_id,
                'company_id' => $company_id,
                'course_id' => $params['course_id'],
                'course_reg_id' => $params['course_reg_id'],
                'mod_id' => $params['mod_id'],
                'mod_res_id' => $params['mod_res_id'],
                'courseactivity_id' => $params['courseactivity_id'],
//                'course_complete_id' => $params['course_complete_id'],
//            'res_complete_id' => $params['res_complete_id'],
                'result_type' => LnExaminationResultUser::RESULT_TYPE_PROCESS,
                'examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_START
            ])->one();
            if (empty($findResultProcessUser)) {
                return ['result' => 'fail', 'errmsg' => '数据异常', 'errmodel' => 'process'];
            }
            LnExaminationResultUser::updateAll(['course_complete_id' => $findResultProcessUser->course_complete_id],"`kid`=:kid",[':kid'=>$findResultProcessUser->kid]);
        }

        $examination_version = $examinationModel->examination_version;
        if (!empty($options)){
            /*物理删除暂存数据*/
            LnExamResultDetail::physicalDeleteAll(['user_id' => $user_id, 'examination_id' => $examinationModel->kid, 'examination_paper_user_id' => $params['examination_paper_user_id'], 'examination_result_process_id' => $findResultProcessUser->kid, 'examination_result_final_id' => $findResultFinalUser->kid]);
            foreach ($options as $key => $item){
                $question_score = 0;
                $questionUser = LnExamQuestionUser::findOne($key);
                /*多选题*/
                if ($questionUser->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX && is_array($item)){
                    //20160216
                    $questionOptionUserCount = LnExamQuestOptionUser::find(false)->andFilterWhere(['examination_question_user_id' => $key, 'is_right_option' => LnExamQuestionOption::IS_RIGHT_OPTION_YES])->distinct()->select('kid')->count();
                    $is_right = 1;
                    foreach ($item as $val) {
                        $isRightOptions = LnExamQuestOptionUser::findOne($val);
                        if ($isRightOptions && $isRightOptions->is_right_option == LnExamQuestionOption::IS_RIGHT_OPTION_YES){
                            $option_result = self::IS_RIGHT_YES;
                        }else{
                            $option_result = self::IS_RIGHT_NO;
                            $is_right = 0;
                        }
                        $result = self::saveResultUserDetail($key, $params, $val, $findResultProcessUser, $findResultFinalUser, $company_id, $user_id, $questionUser, $isRightOptions, $examination_version, $option_result);
                        if (isset($result['result']) && $result['result'] == 'fail'){
                            return $result;
                        }
                    }
                    if ($is_right && $questionOptionUserCount == count($item)){
                        $question_score = $questionUser->default_score;
                        $correct_number ++;
                    }else{
                        $question_score = 0;
                        $error_number ++;
                    }
                }else if ($questionUser->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO){
                    $isRightOptions = LnExamQuestOptionUser::findOne($item);
                    if ($isRightOptions->is_right_option == LnExamQuestionOption::IS_RIGHT_OPTION_YES){
                        $question_score = $isRightOptions->default_score;
                        $option_result = self::IS_RIGHT_YES;
                        $correct_number ++;
                    }else{
                        $question_score = 0;
                        $option_result = self::IS_RIGHT_NO;
                        $error_number ++;
                    }
                    $result = self::saveResultUserDetail($key, $params, $item, $findResultProcessUser, $findResultFinalUser, $company_id, $user_id, $questionUser, $isRightOptions, $examination_version, $option_result);
                    if (isset($result['result']) && $result['result'] == 'fail'){
                        return $result;
                    }
                }else if ($questionUser->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE){
                    $questionOptionUserOne = LnExamQuestOptionUser::find(false)->andFilterWhere(['examination_question_user_id'=>$key])->one();
                    if ($questionOptionUserOne->option_stand_result == LnExamQuestionOption::JUDGE_OPTION_RESULT_RIGHT){
                        if ($item == LnExamQuestionOption::IS_RIGHT_OPTION_YES){/*回答正确*/
                            $option_result = self::IS_RIGHT_YES;
                            $question_score = $questionUser->default_score;
                            $correct_number ++;
                        }else{/*回答错误*/
                            $option_result = self::IS_RIGHT_NO;
                            $question_score = 0;
                            $error_number ++;
                        }
                    }else{
                        if ($item == LnExamQuestionOption::IS_RIGHT_OPTION_YES){/*回答错误*/
                            $option_result = self::IS_RIGHT_NO;
                            $question_score = 0;
                            $error_number ++;
                        }else{/*回答正确*/
                            $correct_number ++;
                            $option_result = self::IS_RIGHT_YES;
                            $question_score = $questionUser->default_score;
                        }
                    }
                    if ($option_result == self::IS_RIGHT_YES) {
                        $is_right_option = LnExamQuestionOption::IS_RIGHT_OPTION_YES;
                    }else{
                        $is_right_option = LnExamQuestionOption::IS_RIGHT_OPTION_NO;
                    }
                    $questionOptionUser = LnExamQuestOptionUser::findOne(['examination_question_user_id'=>$key, 'is_right_option' => $is_right_option]);
                    $item = $questionOptionUser->kid;
                    $result = self::saveResultUserDetail($key, $params, $item, $findResultProcessUser, $findResultFinalUser, $company_id, $user_id, $questionUser, $questionOptionUser, $examination_version, $option_result);
                    if (isset($result['result']) && $result['result'] == 'fail'){
                        return $result;
                    }
                }
                LnExamQuestionUser::updateAll(['examination_question_score' => $question_score, 'submit_status' => '1'],'kid=:kid',[':kid'=>$key]);
				LnExamQuestionUser::removeFromCacheByKid($key);/*清除缓存*/
                $score += $question_score;
            }
            /*转换成百分制*/
            if ($examinationModel->examination_mode == LnExamination::EXAMINATION_MODE_TEST) {
                if ($examinationModel->random_mode == LnExamination::RANDOM_MODE_NO){
                    $parperCopyModel = LnExaminationPaperCopy::findOne($examinationModel->examination_paper_copy_id);
                    $examinationScoreAll = $parperCopyModel->default_total_score;
                }else{
                    $examinationScoreAll = LnExamPaperQuestUser::find(false)->andFilterWhere(['=', 'examination_paper_user_id', $findResultFinalUser->examination_paper_user_id])->sum('default_score');
                }
                $score = $examinationScoreAll > 0 ? round(($score / $examinationScoreAll * 100),2) : $score;
            }
            LnExaminationPaperUser::updateAll(['examination_paper_score' => $score, 'submit_status'=>'1'],'kid=:kid',[':kid'=> $params['examination_paper_user_id']]);
			LnExaminationPaperUser::removeFromCacheByKid($params['examination_paper_user_id']);/*清除缓存*/
        }

        $duration = 15;
        $record_at = $findResultProcessUser->examination_duration + $duration;
        if ($examinationModel->limit_time > 0 && $record_at > $examinationModel->limit_time*60){
            $examination_duration = $examinationModel->limit_time*60;
        }else{
            $examination_duration = $record_at;
        }
        $all_number = LnExamPaperQuestUser::find(false)
            ->andFilterWhere(['=', 'examination_paper_user_id', $findResultFinalUser->examination_paper_user_id])
            ->andFilterWhere(['=', 'relation_type', LnExamPaperQuestion::RELATION_TYPE_PAPER])
            ->count('kid');
        $rate = round(($correct_number / $all_number * 100), 2); //正确率

        $attributes = [
            'last_record_at' => $time,
            'examination_score' => $score,
            'correct_number' => $correct_number,
            'error_number' => $error_number,
            'correct_rate' => $rate,
            'examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_END, //完成
            'examination_duration' => $examination_duration,
            'end_at' => $time
        ];
        LnExaminationResultUser::updateAll($attributes, "kid='{$findResultProcessUser->kid}'");
        if ($examinationModel->examination_range == LnExamination::EXAMINATION_RANGE_COURSE) {
            LnExaminationResultUser::updateAll($attributes, "kid='{$findResultFinalUser->kid}'");
        }else{
            if ($examinationModel->examination_mode == LnExamination::EXAMINATION_MODE_TEST){
                /*重考及格*/
                if ($score >= $examinationModel->pass_grade) {
                    $attributes['start_at'] = $findResultProcessUser->start_at;
                    LnExaminationResultUser::updateAll($attributes, "kid='{$findResultFinalUser->kid}'");
                }else{
                    /*课程已经完成重考不及格*/
                    if($findResultFinalUser->examination_status == LnExaminationResultUser::EXAMINATION_STATUS_END){
                        /**/
                    }else{
                        /*考试不及格不更新完成状态*/
                        $attributes['examination_status'] = LnExaminationResultUser::EXAMINATION_STATUS_START;
                        LnExaminationResultUser::updateAll($attributes, "kid='{$findResultFinalUser->kid}'");
                    }
                }
            }else{
                /*练习可以每次更新*/
                LnExaminationResultUser::updateAll($attributes, "kid='{$findResultFinalUser->kid}'");
            }
        }

        /*更新时间轴状态完成: 尝试次数用完,历程每次更新20160513讨论@丽华赵亮*/
        if ($examinationModel->examination_range == LnExamination::EXAMINATION_RANGE_SELF && $examinationModel->examination_mode == LnExamination::EXAMINATION_MODE_TEST) {
            $count = $this->getResultUserCount($examinationModel->kid, LnExaminationResultUser::EXAMINATION_STATUS_END, LnExaminationResultUser::RESULT_TYPE_PROCESS, $user_id);
            $record = new RecordService();
            /*添加更新考试完成历程*/
            if ($count > 1){
                $record->updateByCompletedExam($user_id, $examinationModel->kid, $score);
            }else{
                $record->addByCompletedExam($user_id, $examinationModel->kid, $score);
            }
            if ($count >= $examinationModel->limit_attempt_number){
                $timelineService = new TimelineService();
                $timelineService->setComplete($examinationModel->kid, MsTimeline::OBJECT_TYPE_EXAM, MsTimeline::TIMELINE_TYPE_TODO, $user_id);
            }
        }else{
            $score = 0;
        }

        return ['result' => 'success', 'result_id' => $findResultProcessUser->kid, 'score' => $score];
    }

    /**
     * 保存考试明细结果表
     * @param $examination_question_user_id
     * @param $params
     * @param $examination_option_user_id
     * @param $findResultProcessUser
     * @param $findResultFinalUser
     * @param $company_id
     * @param $user_id
     * @param $questionUser
     * @param $isRightOptions
     * @param $examination_version
     * @param string $option_result
     */
    public function saveResultUserDetail($examination_question_user_id, $params, $examination_option_user_id, $findResultProcessUser, $findResultFinalUser, $company_id, $user_id, $questionUser, $isRightOptions, $examination_version, $option_result = ""){
        $lnExaminationResultUserDetail = new LnExamResultDetail();
        $lnExaminationResultUserDetail->examination_question_user_id = $examination_question_user_id;
        $lnExaminationResultUserDetail->examination_paper_user_id = $params['examination_paper_user_id'];
        $lnExaminationResultUserDetail->examination_option_user_id = $examination_option_user_id;
        $lnExaminationResultUserDetail->examination_result_process_id = $findResultProcessUser->kid;
        $lnExaminationResultUserDetail->examination_result_final_id = $findResultFinalUser->kid;
        $lnExaminationResultUserDetail->company_id = $company_id;
        $lnExaminationResultUserDetail->user_id = $user_id;
        $lnExaminationResultUserDetail->examination_id = $params['examination_id'];
        $lnExaminationResultUserDetail->course_id = $params['course_id'];
        $lnExaminationResultUserDetail->course_reg_id = $params['course_reg_id'];
        $lnExaminationResultUserDetail->mod_id = $params['mod_id'];
        $lnExaminationResultUserDetail->mod_res_id = $params['mod_res_id'];
        $lnExaminationResultUserDetail->courseactivity_id = $params['courseactivity_id'];
        $lnExaminationResultUserDetail->component_id = $params['component_id'];
        $lnExaminationResultUserDetail->course_complete_id = $params['course_complete_id'];
        $lnExaminationResultUserDetail->res_complete_id = $params['res_complete_id'];
        $lnExaminationResultUserDetail->course_attempt_number = $findResultFinalUser->course_attempt_number;
        $lnExaminationResultUserDetail->examination_attempt_number = $findResultFinalUser->examination_attempt_number;
        $lnExaminationResultUserDetail->question_title = $questionUser->title;
        $lnExaminationResultUserDetail->examination_question_type = $questionUser->examination_question_type;
        $lnExaminationResultUserDetail->question_description = $questionUser->description;
        $lnExaminationResultUserDetail->option_title = $isRightOptions->option_title;
        $lnExaminationResultUserDetail->option_description = $isRightOptions->option_description;
        $lnExaminationResultUserDetail->option_stand_result = $isRightOptions->option_stand_result;
        $lnExaminationResultUserDetail->option_result = $option_result;
        $lnExaminationResultUserDetail->examination_version = $examination_version;
        $lnExaminationResultUserDetail->question_version = $questionUser->question_version;
        $lnExaminationResultUserDetail->option_version = $isRightOptions->option_version;
        if (!$lnExaminationResultUserDetail->save()){
            return ['result' => 'fail', 'errmsg' => $lnExaminationResultUserDetail->getErrors()];
        }
    }

    /**
     * 考试查看
     * @param $result_user_id
     * @return array|bool|void
     */
    public function GetExaminationUserPaper($result_user_id,array $question_fields = [],array $option_fields = []){
        if (empty($result_user_id)) return null;
        $model = LnExaminationResultUser::findOne($result_user_id);
        $examination = LnExamination::findOne($model->examination_id);

        $default_fiels = [
            LnExamPaperQuestUser::tableName().'.*',
            'qu_id' => 'qu.kid',
            'title',
            'examination_question_type',
            'result_output_type',
            'description',
            'answer',
            'examination_question_level',
        ];
        if(!empty($question_fields)) {
            $question_fields = array_map(function($val){
                return LnExamPaperQuestUser::tableName().'.'.$val;
            },$question_fields);
            $default_fiels = array_merge($question_fields,[
                'qu_id' => 'qu.kid',
                'title',
                'examination_question_type',
                'result_output_type',
                'description',
                'answer',
                'examination_question_level',
            ]);
        }

        $paperUserQuestion = LnExamPaperQuestUser::find(false)
            ->leftJoin(LnExamQuestionUser::tableName() .' as qu', "qu.kid = ".LnExamPaperQuestUser::tableName().".examination_question_user_id")
            ->andFilterWhere(['examination_paper_user_id' => $model->examination_paper_user_id])
            ->andFilterWhere(['status' => LnExamPaperQuestUser::STATUS_FLAG_NORMAL])
            ->select($default_fiels)
            ->orderBy(LnExamPaperQuestUser::tableName().".sequence_number")
            ->asArray()
            ->all()
            ;

        if (empty($paperUserQuestion)) {
            return false;
        }
        $result = [];
        $page = 0;
        foreach ($paperUserQuestion as $key => $item){
            if ($item['relation_type'] == LnExamPaperQuestion::RELATION_TYPE_HR){
                $item['options'] = null;
                $page ++;
            }else{
                $options = $this->GetResultUserDetail($item['examination_question_user_id'], $result_user_id,$option_fields);
                $item['options'] = $options;
                if ($item['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO){
                    $find = LnExamResultDetail::find(false)->andFilterWhere(['examination_question_user_id' => $item['examination_question_user_id'], 'examination_result_process_id' => $result_user_id])->one();
                    if (!empty($find) && $find->option_result == self::IS_RIGHT_YES){
                        $item['is_yes'] = 1;
                    }else{
                        $item['is_yes'] = 0;
                    }
                }else if ($item['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX){
                    $sql_1 = LnExamQuestOptionUser::find(false)->andFilterWhere(['examination_question_user_id' => $item['examination_question_user_id'], 'is_right_option' => LnExamQuestionOption::IS_RIGHT_OPTION_YES]);

                    $count = $sql_1->count();
                    /*查询数据是否存在option_result=no的数据*/
                    $sql = LnExamQuestOptionUser::find(false)->andFilterWhere(['examination_question_user_id' => $item['examination_question_user_id']])->distinct()->select('kid')->createCommand()->getRawSql();
                    $findCount = LnExamResultDetail::find(false)->andWhere("examination_option_user_id in ({$sql})")->andFilterWhere(['examination_result_process_id' => $result_user_id, 'option_result' => LnExamResultDetail::OPTION_RESULT_YES])->count();
                    $findCount_corrent = LnExamResultDetail::find(false)->andWhere("examination_option_user_id in ({$sql})")->andFilterWhere(['examination_result_process_id' => $result_user_id, 'option_result' => LnExamResultDetail::OPTION_RESULT_NO])->count();
                    if (intval($findCount) == intval($count) && intval($findCount_corrent) == 0){
                        $item['is_yes'] = 1;
                    }else{
                        $item['is_yes'] = 0;
                    }
                }else if ($item['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE){
                    //var_dump($item);
                    $find = LnExamResultDetail::find(false)->andFilterWhere(['examination_question_user_id' => $item['examination_question_user_id'], 'examination_result_process_id' => $result_user_id])->one();//->createCommand()->getRawSql();
                    $findQuestionOptionUser = LnExamQuestOptionUser::find(false)->andFilterWhere(['examination_question_user_id'=>$item['examination_question_user_id']])->one();
                    //var_dump($find);
                    if (!empty($find) && $find->option_result == self::IS_RIGHT_YES){
                        $item['is_yes'] = 1;
                    }else{
                        $item['is_yes'] = 0;
                    }
                    if ($findQuestionOptionUser->option_stand_result == LnExamQuestionOption::IS_RIGHT_OPTION_YES) {
                        if ($item['is_yes']) {
                            $item['values'] = 1;
                        } else {
                            $item['values'] = 0;
                        }
                    } else {
                        if ($item['is_yes']) {
                            $item['values'] = 0;
                        } else {
                            $item['values'] = 1;
                        }
                    }
                    if (!empty($find)){
                        $item['is_checked'] = 1;
                    }
                }
            }
            $result[] = $item;
        }
        $end = end($result);
        if ($end['relation_type'] == LnExamPaperQuestion::RELATION_TYPE_PAPER){
            $result[] = array(
                'relation_type' => LnExamPaperQuestion::RELATION_TYPE_HR,
                'options' => null,
                'kid' => null,
            );
            $page ++;
        }

        return ['result' => $result, 'page' => $page, 'examination' => $examination, 'model' => $model];
    }

    /**
     * @param $examination_question_user_id
     * @param $examination_result_final_id
     * @param $examination_paper_user_id
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetResultUserDetail($examination_question_user_id, $examination_result_process_id,array $option_fields = []){
        $query = LnExamQuestOptionUser::find(false)
            ->andFilterWhere(["=","examination_question_user_id",$examination_question_user_id])
            ->orderBy('sequence_number');
        if(!empty($option_fields)) {
            $query = $query->select($option_fields);
        }
        $lnExamQuestionOption = $query->asArray()->all();

        $result = [];
        if ($lnExamQuestionOption){
            foreach ($lnExamQuestionOption as $val){
                $count = LnExamResultDetail::find(false)->andFilterWhere(['examination_option_user_id' => $val['kid'], 'examination_result_process_id' => $examination_result_process_id])->count();
                if ($count > 0){
                    $val['is_checked'] = 1;
                }
                $result[] = $val;
            }
        }else{
            $result = $lnExamQuestionOption;
        }

        return $result;
    }

    /**
     * 考试暂存专用查询
     * @param $userId
     * @param $companyId
     * @param $examinationId
     * @param $processId
     * @param $field
     * @return array
     */
    public function GetResultUserDetailSelect($userId, $companyId, $examinationId, $processId, $field = 'examination_option_user_id'){
        $result = LnExamResultDetail::find(false)->andFilterWhere(['user_id' => $userId, 'company_id' => $companyId, 'examination_id' => $examinationId, 'examination_result_process_id' => $processId])->select($field)->asArray()->all();
        if ($result){
            $keys = ArrayHelper::map($result, $field, $field);
            $keys = array_keys($keys);
            array_unique($keys);
            return $keys;
        }else{
            return array();
        }
    }

    /**
     * 查询考试结果
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     */
    public function searchResultUser($params){
        $query = LnExaminationResultUser::find(false);

        if (isset($params['examination_id'])){
            $query->andFilterWhere(['=', 'examination_id', $params['examination_id']]);
        }
        if (isset($params['examination_paper_user_id'])){
            $query->andFilterWhere(['=', 'examination_paper_user_id', $params['examination_paper_user_id']]);
        }
        if (isset($params['user_id'])){
            $query->andFilterWhere(['=', 'user_id', $params['user_id']]);
        }
        if (isset($params['course_id'])){
            $query->andFilterWhere(['=', 'course_id', $params['course_id']]);
        }
        if (isset($params['mod_id'])){
            $query->andFilterWhere(['=', 'mod_id', $params['mod_id']]);
        }
        if (isset($params['courseactivity_id'])){
            $query->andFilterWhere(['=', 'courseactivity_id', $params['courseactivity_id']]);
        }
        if (isset($params['result_type'])){
            $query->andFilterWhere(['=', 'result_type', $params['result_type']]);
        }
        if (isset($params['examination_status'])){
            $query->andFilterWhere(['=', 'examination_status', $params['examination_status']]);
        }
        $query->orderBy('created_at');
        $count = $query->count();
        $pages = new TPagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
        $data = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
        if ($data){
            $commonUserSerivce = new UserService();
            foreach ($data as $k => $v){
                $userModel = FwUser::findOne($v['user_id']);
                $data[$k]['user_name'] = $userModel->real_name;
                $data[$k]['user_location'] = $userModel->location;
                $orgnization = $userModel->orgnization_id;
                $data[$k]['user_orgnization'] = FwOrgnization::findOne($orgnization)->orgnization_name;
                $data[$k]['user_position'] = $commonUserSerivce->getPositionListStringByUserId($v['user_id']);
            }
        }

        return ['data' => $data, 'page' => $pages];
    }
 /**
     * 查询考试结果
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     */
    public function searchHomeworkResultUser($params){
        $query = LnHomeworkResult::find(false);
        
        $query->leftJoin(LnResComplete::tableName(), LnHomeworkResult::tableName().'.res_complete_id='.LnResComplete::tableName().'.kid');
        $query->andFilterWhere(['=', LnResComplete::tableName().'.complete_type', LnResComplete::COMPLETE_TYPE_FINAL]);
        
        if (isset($params['user_id'])){
            $query->andFilterWhere(['=', LnHomeworkResult::tableName().'.user_id', $params['user_id']]);
        }
        if (isset($params['course_id'])){
            $query->andFilterWhere(['=', LnHomeworkResult::tableName().'.course_id', $params['course_id']]);
        }
        if (isset($params['mod_res_id'])){
            $query->andFilterWhere(['=', LnHomeworkResult::tableName().'.mod_res_id', $params['mod_res_id']]);
        }
        if (isset($params['homework_id'])){
            $query->andFilterWhere(['=', LnHomeworkResult::tableName().'.homework_id', $params['homework_id']]);
        }                                                   
        if (isset($params['result_type'])){
            $query->andFilterWhere(['=', LnHomeworkResult::tableName().'.result_type', $params['result_type']]);
        }

        $query->select([
            LnHomeworkResult::tableName().'.user_id',
            LnResComplete::tableName().'.complete_status',
            LnResComplete::tableName().'.end_at',
        ]);

        $query->orderBy(LnResComplete::tableName().'.created_at'); 
        $count = $query->count();
        $pages = new TPagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
        $data = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
        if ($data){
            $commonUserSerivce = new UserService();
            foreach ($data as $k => $v){
                $userModel = FwUser::findOne($v['user_id']);
                $data[$k]['user_name'] = $userModel->real_name;
                $data[$k]['user_location'] = $userModel->location;
                $data[$k]['user_email'] = $userModel->email;
                $orgnization = $userModel->orgnization_id;
                $data[$k]['user_orgnization'] = FwOrgnization::findOne($orgnization)->orgnization_name;
                $data[$k]['user_position'] = $commonUserSerivce->getPositionListStringByUserId($v['user_id']);
                $data[$k]['complete_status'] = $v['complete_status'];
                $data[$k]['complete_status'] = $v['end_at'];
            }
        }
        return ['data' => $data, 'page' => $pages];
    }


    public function GetExaminationPaper($paper_id, $examination_mod = '0', $option_disorder = '0'){
        $query = LnExamPaperQuestion::find(false)
            ->andFilterWhere(['examination_paper_id' => $paper_id])
            ->andFilterWhere(['status' => LnExamPaperQuestion::STATUS_FLAG_NORMAL])
            ->leftJoin(LnExaminationQuestion::tableName().' as qu', 'qu.kid='.LnExamPaperQuestion::tableName().'.examination_question_id')
            ->select([
                LnExamPaperQuestion::tableName().'.*',
                'examination_question_type' => 'qu.examination_question_type',
                'title' => 'qu.title',
                'qu_kid' => 'qu.kid',
            ])
            ->orderBy('sequence_number')
            ->asArray()
            ->all();

        if (!empty($query)){
            foreach ($query as $key => $val) {
                if ($val['relation_type'] == LnExamPaperQuestion::RELATION_TYPE_PAPER){
                    $options = LnExamQuestionOption::find(false)->andFilterWhere(['examination_question_id' => $val['examination_question_id']])->orderBy('sequence_number')->asArray()->all();
                    if (!empty($options) && $option_disorder == LnExamination::OPTIOIN_DISORDER_YES){
                        shuffle($options);/*乱序*/
                    }
                    $query[$key]['options'] = $options;
                }else{
                    if ($examination_mod == LnExamination::EXAMINATION_MODE_EXERCISE){
                        unset($query[$key]);
                    }else{
                        $query[$key]['options'] = null;
                    }
                }
            }
            $end = end($query);
            if (empty($end['options'])){
                array_pop($query);
            }
        }
        return $query;
    }

    public function GetExaminationPaperCopy($paper_id, $examination_mod = '0', $option_disorder = '0'){
        $query = LnExamPaperQuestCopy::find(false)
            ->andFilterWhere(['examination_paper_copy_id' => $paper_id])
            ->andFilterWhere(['status' => LnExamPaperQuestion::STATUS_FLAG_NORMAL])
            ->leftJoin(LnExamQuestionCopy::tableName().' as qu', 'qu.kid='.LnExamPaperQuestCopy::tableName().'.examination_question_copy_id')
            ->select([
                LnExamPaperQuestCopy::tableName().'.*',
                'examination_question_type' => 'qu.examination_question_type',
                'title' => 'qu.title',
                'qu_kid' => 'qu.kid',
            ])
            ->orderBy('sequence_number')
            ->asArray()
            ->all();
        if (!empty($query)){
            foreach ($query as $key => $val) {
                if ($val['relation_type'] == LnExamPaperQuestion::RELATION_TYPE_PAPER){
                    $options = LnExamQuestOptionCopy::find(false)->andFilterWhere(['examination_question_copy_id' => $val['examination_question_copy_id']])->orderBy('sequence_number')->asArray()->all();
                    if (!empty($options) && $option_disorder == LnExamination::OPTIOIN_DISORDER_YES){
                        shuffle($options);/*乱序*/
                    }
                    $query[$key]['options'] = $options;
                }else{
//                    if ($examination_mod == LnExamination::EXAMINATION_MODE_EXERCISE){
//                        unset($query[$key]);
//                    }else{
                        $query[$key]['options'] = null;
//                    }
                }
            }
            $end = end($query);
            if (empty($end['options'])){
                array_pop($query);
            }
        }
        return $query;

    }

    /**
     * 获取学员考试成绩
     * @param $uid
     * @param $examination_id
     * @return int|mixed|string
     */
    public function GetExaminationGrade($uid, $examination_id, $course_id = null, $mod_res_id = null, $mod_id = null){
        $model = LnExamination::findOne($examination_id);
        $query = LnExaminationResultUser::find(false)
            ->andFilterWhere(['user_id' => $uid, 'examination_id' => $examination_id, 'result_type' => LnExaminationResultUser::RESULT_TYPE_PROCESS]);
            if (!empty($course_id)){
                $query->andFilterWhere(['=', 'course_id', $course_id]);
                $courseService = new CourseService();
                $courseRegId = $courseService->getUserRegInfo($uid, $course_id)->kid;
                $courseCompleteService = new CourseCompleteService();
                $attempt = $courseCompleteService->getLastAttempt($courseRegId);
                $query->andFilterWhere(['=', 'course_attempt_number', $attempt]);
            }else{
                $query->andWhere("ISNULL(course_id)");
            }
            if (!empty($mod_res_id)){
                $query->andFilterWhere(['=', 'mod_res_id', $mod_res_id]);
            }else{
                $query->andWhere("ISNULL(mod_res_id)");
            }
            if (!empty($mod_id)){
                $query->andFilterWhere(['=', 'mod_id', $mod_id]);
            }else{
                $query->andWhere("ISNULL(mod_id)");
            }
            //$query->andFilterWhere(['in','examination_status',array('1','2')]);
            $query->andFilterWhere(['=','examination_status',LnExaminationResultUser::EXAMINATION_STATUS_END]);

        $score = 0;
        if ($model->examination_mode == LnExamination::EXAMINATION_MODE_TEST){
            /*课程取最高分*/
            /*if ($model->examination_range == LnExamination::EXAMINATION_RANGE_COURSE){
                $score = $query->max('examination_score');
            }else {*/
                if ($model->attempt_strategy == LnExamination::ATTEMPT_STRATEGY_TOP) {
                    $score = $query->max('examination_score');
                } else if ($model->attempt_strategy == LnExamination::ATTEMPT_STRATEGY_LAST) {
                    $last = $query->orderBy("created_at desc")->select("examination_score")->one();
                    $score = $last->examination_score;
                } else if ($model->attempt_strategy == LnExamination::ATTEMPT_STRATEGY_AVG) {
                    $score = $query->average('examination_score');
                } else if ($model->attempt_strategy == LnExamination::ATTEMPT_STRATEGY_FIRST) {
                    $first = $query->orderBy("created_at")->select("examination_score")->one();
                    $score = $first->examination_score;
                }
            /*}*/

            if (empty($score)){
                $score = '--';
            }else {
                $score = sprintf("%.2f", round($score, 2));
            }
        }else{
            $last = $query->orderBy("created_at desc")->select("examination_status,correct_number,all_number,correct_rate")->one();
            if (!empty($last)) {
                $score = sprintf("%.2f", $last->correct_rate) . '%';
            }else{
                $score = '--';
            }
        }

        return $score;

    }

    /**
     * 依次查询最后一次完成，最后一次开始，最后一次未开始
     * @param $user_id
     * @param $examination_id
     * @param null $course_id
     * @param null $mod_id
     * @param null $mod_res_id
     * @return $this|array|null|\yii\db\ActiveRecord
     */
    public function GetExaminationUserResultLast($user_id, $examination_id, $course_id = null, $mod_res_id = null, $mod_id = null){
        $query = LnExaminationResultUser::find(false);
        $query->andFilterWhere(['user_id' => $user_id])
            ->andFilterWhere(['examination_id' => $examination_id])
            ->andFilterWhere(['result_type' => LnExaminationResultUser::RESULT_TYPE_PROCESS]);
        if (!empty($course_id)){
            $query->andFilterWhere(['=', 'course_id', $course_id]);
        }else{
            $query->andWhere("ISNULL(course_id)");
        }
        if (!empty($mod_res_id)){
            $query->andFilterWhere(['=', 'mod_res_id', $mod_res_id]);
        }else{
            $query->andWhere("ISNULL(mod_res_id)");
        }
        if (!empty($mod_id)){
            $query->andFilterWhere(['=', 'mod_id', $mod_id]);
        }else{
            $query->andWhere("ISNULL(mod_id)");
        }
        $query->orderBy('created_at desc');
        $temp2 = clone $query;
        $temp3 = clone $query;
        $end = $query->andFilterWhere(['examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_END])->one();
        if (!empty($end)){
            return $end;
        }else{
            $starting = $temp2->andFilterWhere(['examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_START])->one();
            if (!empty($starting)){
                return $starting;
            }else{
                $not = $temp3->andFilterWhere(['examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_NOT])->one();
                return $not;
            }
        }
    }


    /**
     * 判断独立考试是否完成： 测试根据及格分数，练习根据是否有提交
     * @param $userId
     * @param $company_id
     * @param $examination_id
     * @return bool
     */
    public function CheckIsPassExamination($userId, $company_id, $examination_id){
        $model = LnExamination::findOne($examination_id);
        if ($model->examination_mode == LnExamination::EXAMINATION_MODE_TEST) {
            $max = LnExaminationResultUser::find(false)->andFilterWhere(['user_id' => $userId, 'company_id' => $company_id, 'examination_id' => $examination_id,  'examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_END, 'result_type' => LnExaminationResultUser::RESULT_TYPE_PROCESS])->max('examination_score');
            if ($max >= $model->pass_grade){
                return true;
            }else{
                return false;
            }
        }else{
            $count = LnExaminationResultUser::find(false)->andFilterWhere(['user_id' => $userId, 'company_id' => $company_id, 'examination_id' => $examination_id, 'examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_END, 'result_type' => LnExaminationResultUser::RESULT_TYPE_PROCESS])->count();
            if ($count){
                return true;
            }else{
                return false;
            }
        }

    }

    /**
     * 统计考试推送数量
     * @param $examinationId
     * @return int|string
     */
    public function getRelatedUserCount($examinationId){
        $count = LnRelatedUser::find(false)->andFilterWhere(['learning_object_id' => $examinationId, 'learning_object_type' => LnRelatedUser::OBJECT_TYPE_EXAM, 'status' => LnRelatedUser::STATUS_FLAG_NORMAL])->count();
        return $count;
    }

    /**
     * 统计考试数量
     * @param $examinationId
     * @param $examinationStatus
     * @param $resultType
     * @return int|string
     */
    public function getResultUserCount($examinationId, $examinationStatus, $resultType, $user_id = null){
        $query = LnExaminationResultUser::find(false)
            ->andFilterWhere(['examination_id' => $examinationId, 'examination_status' => $examinationStatus, 'result_type' => $resultType]);

        if ($examinationStatus == LnExaminationResultUser::EXAMINATION_STATUS_START){
            $sql = LnExaminationResultUser::find(false)
                ->andFilterWhere(['=', 'examination_status', LnExaminationResultUser::EXAMINATION_STATUS_END])
                ->andFilterWhere(['=', 'result_type', $resultType])
                ->distinct()
                ->select('user_id')
                ->createCommand()
                ->getRawSql();
            $query->andWhere(" user_id not in ({$sql})");
        }

        if (!empty($user_id)){
            $query->andFilterWhere(['user_id' => $user_id]);
        }else{
            $query->groupBy('user_id');
        }
        $count = $query->count('kid');
        return intval($count);
    }

    /**
     * @param $id
     * @param $params
     * @param string $callback: query查询数据,count统计数据
     * @return mixed
     */
    public function getExaminationUserInfoResult($id, $params, $callback = 'query'){
        $where = "";
        $resultUserTable = LnExaminationResultUser::tableName();
        $relatedUserTable = LnRelatedUser::tableName();
        $fwUserTable = FwUser::tableName();
        if (isset($params['examination_status']) && $params['examination_status'] != ""){
            $examination_status = $params['examination_status'];
            switch ($examination_status){
                case '0':
                    $where = " and (t3.examination_status='".LnExaminationResultUser::EXAMINATION_STATUS_NOT."' OR t3.examination_status is null)";
                    break;

                case '1':
                    $where = " and t3.examination_status='".LnExaminationResultUser::EXAMINATION_STATUS_START."'";
                    break;

                case '2':
                    $where = " and t3.examination_status='".LnExaminationResultUser::EXAMINATION_STATUS_END."'";
                    break;
            }
        }

        if (isset($params['examination_score']) && $params['examination_score'] != "" && isset($params['expression']) && $params['expression'] != ""){
            $score = floatval($params['examination_score']);
            $model = LnExamination::findOne($id);
            if ($model->examination_mode == LnExamination::EXAMINATION_MODE_TEST){
                $field = "t3.examination_score";
                $field_1 = "examination_score";
            }else{
                $field = "t3.correct_rate";
                $field_1 = "correct_rate";
            }
            $expression = intval($params['expression']);
            switch($expression){
                case 0:
                    $where .= " and $field >= $score";
                    break;

                case 1:
                    $where .= " and $field <= $score";
                    break;

                case 2:
                    $where .= " and $field = $score";
                    break;
            }

        }
        $where_1 = "";
        if (!empty($params['user_keyword'])){
            $user_keyword = htmlspecialchars($params['user_keyword']);
            $where_1 = " and (real_name like '%{$user_keyword}%' OR email like '%{$user_keyword}%')";
        }

        $count_sql = "select * from ( select * from
(
select user_id,learning_object_id as examination_id from {$relatedUserTable} where learning_object_id='{$id}' and is_deleted='0' GROUP BY user_id
) t1
LEFT JOIN
(
SELECT kid as result_id,user_id as uid,examination_score,examination_status,correct_rate,result_type from {$resultUserTable} where examination_id='{$id}' and result_type='{$params['result_type']}' and is_deleted='0' GROUP BY user_id
) t2
on t1.user_id=t2.uid
) t3 ". (!empty($where) ? " WHERE 1=1 ". $where : '');

        $db = \Yii::$app->db;

        if ($callback == 'count'){
            $sql_count = "select count(1) as c from ($count_sql) tt";
            $count_= $db->createCommand($sql_count)->queryAll();
            $count = $count_[0]['c'];
            return $count;
        }
        $count_sql .= ' order by t3.examination_status desc';
        if (!empty($where_1)){
            $sql = "select * from
({$count_sql}) t5
LEFT JOIN
(SELECT kid,real_name,email,mobile_no FROM {$fwUserTable} where status='".FwUser::STATUS_FLAG_NORMAL."' and user_type<>'1' and is_deleted='0' {$where_1}) t4
on t5.user_id=t4.kid WHERE t4.kid is not null GROUP BY t4.kid";
        }else{
            $sql = $count_sql;
        }
        $sql_count = "select count(1) as c from ($sql) tt";
        $count_= $db->createCommand($sql_count)->queryAll();
        /*print_r($db->createCommand($sql_count)->getRawSql());*/
        $count = $count_[0]['c'];

        if (empty($params['export'])) {
            $pages = new Pagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
            $result['page'] = $pages;
            $result_sql = $sql . " limit $pages->offset,$pages->limit";
            if (empty($where_1)){
                $result_sql = "select * from ({$result_sql}) as e left join (SELECT kid,real_name,email,mobile_no,company_id FROM {$fwUserTable} where is_deleted='0') t4 on e.user_id=t4.kid WHERE t4.kid is not null GROUP BY t4.kid";
            }
        }else{
            $result_sql = $sql;
            if (empty($where_1)){
                $result_sql = "select * from ({$result_sql}) as e left join (SELECT kid,real_name,email,mobile_no,company_id FROM {$fwUserTable} where is_deleted='0') t4 on e.user_id=t4.kid WHERE t4.kid is not null GROUP BY t4.kid";
            }
        }
        if (!isset($params['examination_status']) || $params['examination_status'] == ""){
            $result_sql = "select * from ({$result_sql}) ss order by examination_status desc";
        }
        $sub_result_arr = $db->createCommand($result_sql) ->queryAll();
        /*print_r($db->createCommand($result_sql)->getRawSql());*/
        $data = array();
        if (!empty($sub_result_arr)){
            foreach ($sub_result_arr as $key => $val){
                $examination_id = $val['examination_id'];
                $user_id = $val['kid'];
                $data[$key] = $val;
                if (!empty($user_id) && !empty($examination_id)) {
                    $score = $this->GetExaminationGrade($user_id, $examination_id);
                    $data[$key]['examination_score'] = $score;
                    $last = $this->GetExaminationUserResultLast($user_id, $examination_id);
                    $data[$key]['correct_rate'] = $last->correct_rate;
                    $data[$key]['examination_status'] = $last->examination_status;
                    $data[$key]['end_at'] = $last->end_at;
                }else{
                    $data[$key]['examination_score'] = $data[$key]['examination_status'] = $data[$key]['correct_rate'] = null;
                }
            }
        }

        $result['data'] = $data;

        return $result;
    }

    /**
     * 更新课程考试尝试次数
     * @param $kid
     * @param $data
     * @return int|void
     */
    public function updateExaminationUser($kid, $data){
        if (empty($data)) return ;
        return LnExaminationResultUser::updateAll($data, "kid=:kid", ['kid' => $kid]);
    }

    /**
     * 删除重学过程中放弃学习的考试记录
     * @param $courseId
     * @param $courseCompleteId
     * @param $courseRegId
     * @param $attempt
     * @param null $userId
     * @param null $companyId
     */
    public function deleteGiveUpCoursesExaminationResult($courseId, $courseCompleteId, $courseRegId, $attempt, $userId = null, $companyId = null){
        if (empty($userId)) {
            $userId = Yii::$app->user->getId();
        }
        if (empty($companyId)){
            $companyId = Yii::$app->user->identity->company_id;
        }
        $findAll = LnExaminationResultUser::findAll([
            'company_id' => $companyId,
            'user_id' => $userId,
            'course_id' => $courseId,
            'course_reg_id' => $courseRegId,
            'course_complete_id' => $courseCompleteId,
            'course_attempt_number' => $attempt,
            'result_type' => LnExaminationResultUser::RESULT_TYPE_PROCESS,
        ]);
        if (!empty($findAll)){
            $kids = ArrayHelper::map($findAll, 'kid', 'kid');
            $kids = array_keys($kids);
            if (is_array($kids)){
                $kids = "'".join("','", $kids)."'";
                LnExaminationResultUser::deleteAllByKid($kids);
                $condition = "examination_result_process_id in (".$kids.")";
                LnExamResultDetail::deleteAll($condition);
            }
        }
    }

    /**
     * 复制课程时复制考试组件
     * @param $exam
     * @param $companyId
     * @return array
     */
    public function copyAllExamination(LnExamination $exam, $companyId){
        $examPaper = LnExaminationPaper::findOne($exam->examination_paper_id);
        if ($examPaper->company_id == $companyId){
            return ['result' => 'fail', 'examination_id' => $exam->kid];
        }
        $transaction = Yii::$app->db->beginTransaction();/*设置事务*/
        /*试卷临时目录*/
        $examPaperService = new ExamPaperManageService();
        $examPaperCategoryId = $examPaperService->getExamPaperTempCategoryId($companyId);
        /*复制试卷基本信息*/
        $newPaper = new LnExaminationPaper();
        $newPaper->category_id = $examPaperCategoryId;
        $newPaper->company_id = $companyId;
        $newPaper->title = $examPaper->title;
        $newPaper->code = $examPaperService->getExamPaperCode();
        $newPaper->default_total_score = $examPaper->default_total_score;
        $newPaper->result_output_type = $examPaper->result_output_type;
        $newPaper->examination_paper_type = $examPaper->examination_paper_type;
        $newPaper->examination_paper_level = $examPaper->examination_paper_level;
        $newPaper->examination_question_number = $examPaper->examination_question_number;
        $newPaper->paper_version = $examPaperService->setExamVersion();;
        $newPaper->needReturnKey = true;
        if ($newPaper->save() === false){
            $transaction->rollback();//回滚事务
            return ['result' => 'fail', 'examination_id' => $exam->kid];
        }

        $examPaperQuestion = LnExamPaperQuestion::findAll(['examination_paper_id' => $exam->examination_paper_id]);
        if (empty($examPaperQuestion)) {
            $transaction->rollback();//回滚事务
            return ['result' => 'fail', 'examination_id' => $exam->kid];
        }else{
            /*试题临时目录*/
            $examQuestionCategoryService = new ExaminationQuestionCategoryService();
            $examQuestionCategoryId = $examQuestionCategoryService->getExaminationQuestionTempCategoryId($companyId);
            $examinationQuestionService = new ExaminationQuestionService();
            foreach ($examPaperQuestion as $val){
                $examQuestion = LnExaminationQuestion::findOne($val->examination_question_id);
                if (!empty($examQuestion)) {
                    /*试题基本信息复制*/
                    $newExaminationQuestion = new LnExaminationQuestion();
                    $newExaminationQuestion->category_id = $examQuestionCategoryId;
                    $newExaminationQuestion->company_id = $companyId;
                    $newExaminationQuestion->title = $examQuestion->title;
                    $newExaminationQuestion->code = $examinationQuestionService->setExamQuestionCode();
                    $newExaminationQuestion->examination_question_type = $examQuestion->examination_question_type;
                    $newExaminationQuestion->result_output_type = $examQuestion->result_output_type;
                    $newExaminationQuestion->is_allow_change_score = $examQuestion->is_allow_change_score;
                    $newExaminationQuestion->description = $examQuestion->description;
                    $newExaminationQuestion->answer = $examQuestion->answer;
                    $newExaminationQuestion->examination_question_level = $examQuestion->examination_question_level;
                    $newExaminationQuestion->default_score = $examQuestion->default_score;
                    $newExaminationQuestion->question_version = $examinationQuestionService->setExamQuestionVersion();
                    $newExaminationQuestion->sequence_number = $examQuestion->sequence_number;
                    $newExaminationQuestion->needReturnKey = true;
                    if ($newExaminationQuestion->save() === false){
                        continue;
                    }
                    /*答案复制*/
                    $examQuestionOptionAll = LnExamQuestionOption::findAll(['examination_question_id' => $val->examination_question_id]);
                    if (empty($examQuestionOptionAll)) {
                        continue;
                    }else{
                        $optionService = new ExaminationQuestionOptionService();
                        foreach ($examQuestionOptionAll as $item){
                            $newExamQuestionOption = new LnExamQuestionOption();
                            $newExamQuestionOption->examination_question_id = $newExaminationQuestion->kid;
                            $newExamQuestionOption->option_title = $item->option_title;
                            $newExamQuestionOption->option_description = $item->option_description;
                            $newExamQuestionOption->default_score = $item->default_score;
                            $newExamQuestionOption->is_right_option = $item->is_right_option;
                            $newExamQuestionOption->option_stand_result = $item->option_stand_result;
                            $newExamQuestionOption->sequence_number = $item->sequence_number;
                            $newExamQuestionOption->option_version = $optionService->getExaminationQuestionOptionVersion();
                            $newExamQuestionOption->save();
                        }
                    }
                    /*复制试卷QUESTION*/
                    $newExamPaperQuestion = new LnExamPaperQuestion();
                    $newExamPaperQuestion->examination_paper_id = $newPaper->kid;
                    $newExamPaperQuestion->examination_question_id = $newExaminationQuestion->kid;
                    $newExamPaperQuestion->default_score = $val->default_score;
                    $newExamPaperQuestion->relation_type = $val->relation_type;
                    $newExamPaperQuestion->sequence_number = $val->sequence_number;
                    $newExamPaperQuestion->status = $val->status;
                    $newExamPaperQuestion->start_at = $val->start_at;
                    $newExamPaperQuestion->end_at = $val->end_at;
                    $newExamPaperQuestion->save();
                }
            }
            /*判断是否存在临时目录*/
            $examCategoryService = new ExaminationCategoryService();
            $examinationCategoryId = $examCategoryService->getExaminationTempCategoryId($companyId);
            $newExam = new LnExamination();
            $newExam->category_id = $examinationCategoryId;
            $newExam->examination_paper_id = $newPaper->kid;
            $newExam->company_id = $companyId;
            $newExam->title = $exam->title;
            $newExam->code = $this->setExamCode();
            $newExam->examination_mode = $exam->examination_mode;
            $newExam->question_from = $exam->question_from;
            $newExam->description = $exam->description;
            $newExam->pre_description = $exam->pre_description;
            $newExam->after_description = $exam->after_description;
            $newExam->start_at = $exam->start_at;
            $newExam->end_at = $exam->end_at;
            $newExam->limit_time = $exam->limit_time;
            $newExam->random_mode = $exam->random_mode;
            $newExam->question_disorder = $exam->question_disorder;
            $newExam->option_disorder = $exam->option_disorder;
            $newExam->result_output_type = $exam->result_output_type;
            $newExam->answer_view = $exam->answer_view;
            $newExam->limit_attempt_number = $exam->limit_attempt_number;
            $newExam->attempt_strategy = $exam->attempt_strategy;
            $newExam->random_number = $exam->random_number;
            $newExam->each_page_number = $exam->each_page_number;
            $newExam->pass_grade = $exam->pass_grade;
            $newExam->examination_range = $exam->examination_range;
            $newExam->release_status = $exam->release_status;
            $newExam->is_email = $exam->is_email;
            $newExam->is_sms = $exam->is_sms;
            $newExam->examination_version = $this->setExamVersion();
            $newExam->needReturnKey = true;
            $result = $this->copyExamination($newExam);
            if (isset($result['errmsg'])){
                $transaction->rollback();//回滚事务
                return ['result' => 'fail', 'examination_id' => $exam->kid];
            }
            $newExam->examination_paper_copy_id = $result;
            $errmsg = $newExam->save();
            if ($errmsg === false){
                $transaction->rollback();//回滚事务
                return ['result' => 'fail', 'examination_id' => $exam->kid];
            }else{
                $transaction->commit();//提交事务
                return ['result' => 'success', 'examination_id' => $newExam->kid];
            }
        }
    }

    /**
     * 返回考试的最终考试ID
     * @param $userId
     * @param $examinationId
     * @return mixed|string
     */
    public function getExaminationResultUserFinal($userId, $examinationId){
        $find = LnExaminationResultUser::find(false)
            ->andFilterWhere(['user_id' => $userId])
            ->andFilterWhere(['examination_id' => $examinationId])
            ->andFilterWhere(['result_type' => LnExaminationResultUser::RESULT_TYPE_FINALLY])
            ->one();

        if (empty($find)){
            return "";
        }else{
            return $find->kid;
        }

    }

    /**
     * 返回考试结果
     * @param $processResultId
     * @param $userId
     * @param $companyId
     * @param null $modResId
     * @param string $mode
     * @return array
     */
    public function getExaminationPlayResult($processResultId, $userId, $companyId, $modResId = null, $mode = ''){
        $userResult = LnExaminationResultUser::findOne($processResultId);
        /*考试基本信息*/
        $examination = $this->GetExaminationByUserOne($userResult->examination_id);
        /*获取当前的结果集*/
        $userResultAll = $this->GetExaminationByUserResultAll($userId, $userResult->examination_id, $companyId, $userResult->mod_id, $userResult->mod_res_id, $userResult->course_id);
        /*判断是否可以在考试*/
        $next = $this->getExaminationProcessLast($userResult['examination_id'], $userId, $companyId, $userResult->course_id, $userResult->course_reg_id, $userResult->mod_id, $userResult->mod_res_id);
        $courseCompleteService = new CourseCompleteService();
        if (empty($next)) {
            $created = false;
            if ($examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST) {
                /*获取考试过程次数*/
                $count = $this->getExaminationProcessCount($userResult->examination_id, $userId, $companyId, LnExaminationResultUser::EXAMINATION_STATUS_END, $userResult->course_id, $userResult->course_reg_id, $userResult->mod_id, $userResult->mod_res_id);
                if ($examination['limit_attempt_number'] == 0 || ($examination['limit_attempt_number'] > 0 && $count < $examination['limit_attempt_number'])) {
                    $created = true;
                }
            } else {
                $created = true;
            }

            if ($created) {
                $result = "";
                $examinationPaperUserId = "";
                $examinationResultFinalId = "";
                $examinationResultProcessId = "";
                $errMessage = "";
                /*课程考试*/
                if ($mode == 'course'){
                    $attempt = $courseCompleteService->getLastAttempt($userResult->course_reg_id);
                    $resComplete = LnResComplete::find(false)->andFilterWhere(['course_complete_id' => $userResult->course_complete_id, 'course_id'=>$userResult->course_id, 'user_id'=> $userId, 'mod_id'=> $userResult->mod_id, 'mod_res_id' => $modResId, 'courseactivity_id' => $userResult->courseactivity_id, 'complete_status' => LnResComplete::COMPLETE_STATUS_DOING, 'complete_type' => LnResComplete::COMPLETE_TYPE_FINAL])->one();
                    $this->generateUserPaperByExam($userResult->examination_id, $companyId, $userId, $userResult->mod_res_id, $userResult->course_reg_id, $userResult->course_complete_id, $resComplete->kid, $attempt, $result, $examinationPaperUserId, $examinationResultFinalId, $examinationResultProcessId, $errMessage);

                    if ($result == 'OK') {
                        $this->updateExaminationUser($examinationResultFinalId, array('course_attempt_number' => $attempt));
                    }
                }else{
                    /*独立考试*/
                    $examinationResultFinalId = $this->getExaminationResultUserFinal($userId, $userResult->examination_id);
                    $this->generateUserPaperByExam($userResult->examination_id, $companyId, $userId, "", "", "", "", 0, $result, $examinationPaperUserId, $examinationResultFinalId, $examinationResultProcessId, $errMessage);
                }

                if ($result != 'OK') {
                    Yii::getLogger()->log("ExamManageMainController->actionPlayResult:".$errMessage, Logger::LEVEL_ERROR);
                }
                /*获取最后一次过程考试*/
                $next = $this->getExaminationProcessLast($userResult['examination_id'], $userId, $companyId, $userResult->course_id, $userResult->course_reg_id, $userResult->mod_id, $userResult->mod_res_id);
            } else {
                $next = new LnExaminationResultUser();
            }
        }else{
            $attempt = $next->course_attempt_number;
        }

        if (!empty($userResult->course_id)){
            $course = LnCourse::findOne($userResult->course_id);
        }else{
            $course = new LnCourse();
        }

        if ($mode == 'course'){
            $modRes = LnModRes::findOne($userResult->mod_res_id);
            $resCompleteProcessModel = LnResComplete::find(false)->andFilterWhere(['course_id'=>$userResult->course_id, 'user_id'=> $uid, 'mod_id'=> $userResult->mod_id, 'mod_res_id' => $modResId, 'courseactivity_id' => $userResult->courseactivity_id, 'complete_type' => LnResComplete::COMPLETE_TYPE_PROCESS])->one();
            $courseCompleteProcessId = $resCompleteProcessModel->course_complete_id;
        }else{
            $modRes = null;
            $courseCompleteProcessId = null;
        }

        $array = [
            'examination' => $examination,
            'userResult' => $userResult,
            'userResultAll' => $userResultAll,
            'modResId' => $modResId,
            'mode' => $mode,
            'course' => $course,
            'next' => $next,
            'modType' => 0,
            'attempt' => $attempt,
            'modRes' => $modRes,
            'scoId' => '',
            'courseCompleteProcessId' => $courseCompleteProcessId,
        ];

        return $array;
    }

    /**
     * 更新考试时长
     * @param $params
     * @param $userId
     * @param $companyId
     */
    public function updateDuration($params, $userId, $companyId){
        /*最终*/
        $findResultFinalUser = LnExaminationResultUser::find(false)->andFilterWhere([
            'examination_id' => $params['examination_id'],
            'examination_paper_user_id' => $params['examination_paper_user_id'],
            'user_id' => $userId,
            'company_id' => $companyId,
            'course_id' => $params['course_id'],
            'course_reg_id' => $params['course_reg_id'],
            'mod_id' => $params['mod_id'],
            'mod_res_id' => $params['mod_res_id'],
            'courseactivity_id' => $params['courseactivity_id'],
            'course_complete_id' => $params['course_complete_id'],
            'result_type' => LnExaminationResultUser::RESULT_TYPE_FINALLY,
            'examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_START
        ])->one();
        /*过程*/
        $findResultProcessUser = LnExaminationResultUser::find(false)->andFilterWhere([
            'examination_id' => $params['examination_id'],
            'examination_paper_user_id' => $params['examination_paper_user_id'],
            'user_id' => $userId,
            'company_id' => $companyId,
            'course_id' => $params['course_id'],
            'course_reg_id' => $params['course_reg_id'],
            'mod_id' => $params['mod_id'],
            'mod_res_id' => $params['mod_res_id'],
            'courseactivity_id' => $params['courseactivity_id'],
            'course_complete_id' => $params['course_complete_id'],
            'result_type' => LnExaminationResultUser::RESULT_TYPE_PROCESS,
            'examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_START
        ])->one();

        $currentTime = time();
        $duration = self::LEARNING_DURATION;
        if ($findResultFinalUser) {
            $attributes = [
                'examination_duration' => new Expression('examination_duration + ' . strval($duration)),
                'last_record_at' => $currentTime
            ];
            LnExaminationResultUser::updateAll($attributes, 'kid=:kid', [':kid' => $findResultFinalUser->kid]);
        }
        if ($findResultProcessUser) {
            $attributes = [
                'examination_duration' => new Expression('examination_duration + ' . strval($duration)),
                'last_record_at' => $currentTime
            ];
            LnExaminationResultUser::updateAll($attributes, 'kid=:kid', [':kid' => $findResultProcessUser->kid]);
        }
    }

    /**
     * 考试查看
     * @param $userId
     * @param $companyId
     * @param $courseId
     * @param $coursewareId
     * @param $modResId
     * @param $courseRegId
     * @param $courseCompleteFinalId
     * @param $attempt
     * @param $mode
     * @return array
     */
    public function player($userId, $companyId, $courseId, $coursewareId, $modResId, $courseRegId, $courseCompleteFinalId, $attempt, $mode = self::PLAY_MODE_NORMAL){
        $courseactivityModel = LnCourseactivity::findOne($coursewareId);
        $examination = $this->GetExaminationInfoByModResId($modResId, $mode);
        $examinationModel = $examination['examination'];
        /*查询是否存在未完成的过程记录*/
        $examinationLast = $this->getExaminationProcessLast($courseactivityModel->object_id, $userId, $companyId, $courseId, $courseRegId, $courseactivityModel->mod_id, $modResId);
        $generate = true;
        $errMessage = "";
        //判断生成学员试卷数据
        if (empty($examinationLast->kid) && $mode == self::PLAY_MODE_NORMAL) {
            $resComplete = LnResComplete::find(false)
                ->andFilterWhere([
                    'course_complete_id' => $courseCompleteFinalId,
                    'course_id' => $courseId,
                    'user_id' => $userId,
                    'mod_id' => $courseactivityModel->mod_id,
                    'mod_res_id' => $modResId,
                    'courseactivity_id' => $coursewareId,
                    'complete_status' => LnResComplete::COMPLETE_STATUS_DOING,
                    'complete_type' => LnResComplete::COMPLETE_TYPE_FINAL
                ])->one();
            $result = "";
            $examinationPaperUserId = "";
            $examinationResultFinalId = "";
            $examinationResultProcessId = "";
            if (empty($attempt)){
                $courseCompleteService = new CourseCompleteService();
                $attempt = $courseCompleteService->getLastAttempt($courseRegId);
            }
            /*生成个人试卷*/
            $this->generateUserPaperByExam($courseactivityModel->object_id, $companyId, $userId, $modResId, $courseRegId, $courseCompleteFinalId, $resComplete->kid, $attempt, $result, $examinationPaperUserId, $examinationResultFinalId, $examinationResultProcessId, $errMessage);
//                    echo $result;
//                    echo $examinationPaperUserId;
//                    echo $examinationResultFinalId;
//                    echo $examinationResultProcessId;
//                    echo $errMessage;
            if ($result != 'OK') {
                $generate = false;
                Yii::getLogger()->log("PlayerController->actionExaminationPlayer:".$errMessage, Logger::LEVEL_ERROR);
            }
            /*获取最后一次生成未开始或未完成的考试*/
            $examinationLast = $this->getExaminationProcessLast($courseactivityModel->object_id, $userId, $companyId, $courseId, $courseRegId, $courseactivityModel->mod_id, $modResId);

            if (!empty($examinationLast)){
                $generate = true;
                $errMessage = "";
            }

            /*更新res_complete数据记录*/
            if ($examinationModel['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST) {
                LnResComplete::updateAll(
                    ['score_before' => $examinationModel['pass_grade']],
                    "course_id=:course_id and course_reg_id=:course_reg_id and user_id=:user_id and mod_id=:mod_id and mod_res_id=:mod_res_id and courseactivity_id=:courseactivity_id and isnull(score_before)",
                    [
                        //':course_complete_id' => $courseCompleteFinalId,
                        ':course_id' => $courseId,
                        ':course_reg_id' => $courseRegId,
                        ':user_id' => $userId,
                        ':mod_id' => $courseactivityModel->mod_id,
                        ':mod_res_id' => $modResId,
                        ':courseactivity_id' => $courseactivityModel->kid,
                        //':resource_type' => LnResComplete::RESOURCE_TYPE_COURSEACTIVITY,
                    ]
                );
            }

        }
        if (empty($attempt)) {
            $courseCompleteService = new CourseCompleteService();
            $attempt = $courseCompleteService->getLastAttempt($courseRegId);
        }
        /*更新课程尝试次数*/
        $this->updateExaminationUser($examinationLast->kid, array('course_attempt_number' => $attempt));

        $paperCopy = LnExaminationPaperCopy::findOne($examinationModel['examination_paper_copy_id']);
        $default_total_score = $paperCopy->default_total_score;

        return [
            "examination_id" => $examination['kid'],
            "mod_id" => $examination['mod_id'],
            'attempt' => $attempt,
            "courseactivity_id" => $examination['courseactivity_id'],
            "component_id" => $examination['component_id'],
            'examination' => $examination['examination'],
            'resultUser' => $examination['examinationResultUser'],
            'examinationLast' => $examinationLast,
            'errMessage' => $errMessage,
            'generate' => $generate,
            'default_total_score' => $default_total_score,
        ];
    }

    /**
     * 开始学习
     * @param $examinationId
     * @param $ProcessId
     * @param $mode
     * @return array
     */
    public function playerStudy($examinationId, $ProcessId, $mode){
        /*设置开始时间及状态*/
        if ($mode == self::PLAY_MODE_NORMAL) {
            $findResultModel = LnExaminationResultUser::findOne($ProcessId);
            /*完成数据*/
            $findFinalResult = LnExaminationResultUser::find(false)
                ->andFilterWhere([
                    'examination_id' => $findResultModel->examination_id,
                    'examination_paper_user_id' => $findResultModel->examination_paper_user_id,
                    'course_id' => $findResultModel->course_id,
                    'user_id' => $findResultModel->user_id,
                    'courseactivity_id' => $findResultModel->courseactivity_id,
                    'mod_id' => $findResultModel->mod_id,
                    'mod_res_id' => $findResultModel->mod_res_id,
                    'company_id' => $findResultModel->company_id,
                    'result_type' => LnExaminationResultUser::RESULT_TYPE_FINALLY
                ])->one();
            if (!empty($findResultModel->kid) && $findResultModel->examination_status == LnExaminationResultUser::EXAMINATION_STATUS_NOT) {
                $time = time();
                /*更新过程数据*/
                LnExaminationResultUser::updateAll(
                    ['examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_START, 'start_at' => $time],
                    "kid=:kid",
                    [':kid' => $findResultModel->kid]
                );
                LnExaminationResultUser::updateAll(
                    ['examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_START, 'start_at' => $time, 'examination_duration' => 0],
                    "kid=:kid",
                    [':kid' => $findFinalResult->kid]
                );
            }
        } else {
            $findResultModel = new LnExaminationResultUser();
            $findFinalResult = clone $findResultModel;
        }
        $examinationModel = LnExamination::findOne($examinationId);
        /*预览*/
        if ($mode == self::PLAY_MODE_PREVIEW) {
            $paperQuestion = $this->GetExaminationPaperQuestionCopyPreview($examinationId);
        } else {
            $paperQuestion = $this->GetExaminationPaperQuestionUser($examinationId, $findResultModel->examination_paper_user_id);
        }
        if (!empty($findResultModel->kid)) {
            /*查询上次未提交已选择选项结果*/
            $selectOptions = $this->GetResultUserDetailSelect($findResultModel->user_id, $findResultModel->company_id, $findResultModel->examination_id, $findResultModel->kid);
            sort($selectOptions);
            $selectQuestion = $this->GetResultUserDetailSelect($findResultModel->user_id, $findResultModel->company_id, $findResultModel->examination_id, $findResultModel->kid, 'examination_question_user_id');
            sort($selectQuestion);
        } else {
            $selectOptions = $selectQuestion = array();
        }

        return [
            'paperQuestion' => $paperQuestion['result'],
            'countPage' => $paperQuestion['page'],
            'examinationModel' => $examinationModel,
            'findResultModel' => $findResultModel,
            'findFinalResult' => $findFinalResult,
            'selectOptions' => $selectOptions,
            'selectQuestion' => $selectQuestion,
        ];
    }

    /**
     * 临时保存记录
     * @param $params
     * @param $company_id
     * @param $user_id
     * @return array
     */
    public function tempSave($params, $company_id, $user_id){
        /*post参数*/
        $checked = $params['checked'];
        $result_id = $params['result_id'];
        $examination_paper_user_id = $params['examination_paper_user_id'];
        $examination_id = $params['examination_id'];
        $examination_question_user_id = $params['question_id'];
        $options_id = $params['options_id'];

        $resultProcessUser = LnExaminationResultUser::findOne($result_id);
        $examinationModel = LnExamination::findOne($examination_id);
        $examQuestionUser = LnExamQuestionUser::findOne($examination_question_user_id);
        if ($examQuestionUser->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE){/*判断题因为传入的options_id为1或0所以根据存储时的定义查询*/
            $examinationQuestionOptionUser = LnExamQuestOptionUser::findOne(['examination_question_user_id' => $examination_question_user_id]);
        }else{
            $examinationQuestionOptionUser = LnExamQuestOptionUser::findOne($options_id);
        }
        /*查询最终数据*/
        $resultFinalUser = LnExaminationResultUser::find(false)->andFilterWhere(['examination_id' => $examination_id, 'examination_paper_user_id' => $examination_paper_user_id, 'user_id' => $user_id, 'course_id' => $resultProcessUser->course_id, 'mod_res_id' => $resultProcessUser->mod_res_id, 'courseactivity_id' => $resultProcessUser->courseactivity_id, 'result_type' => LnExaminationResultUser::RESULT_TYPE_FINALLY])->one();

        if ($examQuestionUser->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO){
            /*判断是否储存过*/
            $findResultUserDetail = LnExamResultDetail::find(false)->andFilterWhere(['examination_paper_user_id'=>$examination_paper_user_id, 'examination_question_user_id'=> $examination_question_user_id, 'examination_option_user_id' => $options_id, 'examination_result_process_id' => $resultProcessUser->kid, 'examination_result_final_id' => $resultFinalUser->kid, 'user_id' => $user_id, 'examination_id' => $examination_id])->one();
            if ($findResultUserDetail){
                return ['result' => 'success', 'errmsg' => 'already'];
            }else{
		        /*删除以前的临时记录*/
                LnExamResultDetail::physicalDeleteAll(['user_id' => $user_id, 'examination_id' => $examination_id, 'examination_question_user_id' => $examination_question_user_id, 'examination_result_process_id' => $resultProcessUser->kid, 'examination_result_final_id' => $resultFinalUser->kid]);
                if ($examinationQuestionOptionUser->is_right_option == LnExamQuestionOption::IS_RIGHT_OPTION_YES){
                    $option_result = self::IS_RIGHT_YES;
                }else{
                    $option_result = self::IS_RIGHT_NO;
                }
		        /*保存临时数据*/
                $this->saveResultUserDetail($examination_question_user_id, $params, $options_id, $resultProcessUser, $resultFinalUser, $company_id, $user_id, $examQuestionUser, $examinationQuestionOptionUser, $examinationModel->examination_version, $option_result);
            }
        }else if ($examQuestionUser->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE){
            if ($examinationQuestionOptionUser->option_stand_result == LnExamQuestionOption::JUDGE_OPTION_RESULT_RIGHT){
                if ($options_id == LnExamQuestionOption::IS_RIGHT_OPTION_YES){/*回答正确*/
                    $option_result = self::IS_RIGHT_YES;
                }else{/*回答错误*/
                    $option_result = self::IS_RIGHT_NO;
                }
            }else{
                if ($options_id == LnExamQuestionOption::IS_RIGHT_OPTION_YES){/*回答错误*/
                    $option_result = self::IS_RIGHT_NO;
                }else{/*回答正确*/
                    $option_result = self::IS_RIGHT_YES;
                }
            }
            if ($option_result == self::IS_RIGHT_YES) {
                $is_right_option = LnExamQuestionOption::IS_RIGHT_OPTION_YES;
            }else{
                $is_right_option = LnExamQuestionOption::IS_RIGHT_OPTION_NO;
            }
            $examinationQuestionOptionUser = LnExamQuestOptionUser::findOne(['examination_question_user_id'=> $examination_question_user_id, 'is_right_option' => $is_right_option]);
            /*判断是否储存过*/
            $findResultUserDetail = LnExamResultDetail::find(false)->andFilterWhere(['examination_paper_user_id'=>$examination_paper_user_id, 'examination_question_user_id'=> $examination_question_user_id, 'examination_option_user_id' => $examinationQuestionOptionUser->kid, 'examination_result_process_id' => $resultProcessUser->kid, 'examination_result_final_id' => $resultFinalUser->kid, 'company_id' => $company_id, 'user_id' => $user_id, 'examination_id' => $examination_id])->one();
            if ($findResultUserDetail){
                return ['result' => 'success', 'errmsg' => 'already'];
            }else{
		        /*删除以前的临时记录*/
                LnExamResultDetail::physicalDeleteAll(['user_id' => $user_id, 'examination_id' => $examination_id, 'examination_question_user_id' => $examination_question_user_id, 'examination_result_process_id' => $resultProcessUser->kid, 'examination_result_final_id' => $resultFinalUser->kid]);
                $this->saveResultUserDetail($examination_question_user_id, $params, $examinationQuestionOptionUser->kid, $resultProcessUser, $resultFinalUser, $company_id, $user_id, $examQuestionUser, $examinationQuestionOptionUser, $examinationModel->examination_version, $option_result);
            }
        }else if ($examQuestionUser->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX){
            $isRightOptions = LnExamQuestOptionUser::findOne($options_id);
            if ($isRightOptions && $isRightOptions->is_right_option == LnExamQuestionOption::IS_RIGHT_OPTION_YES){
                $option_result = self::IS_RIGHT_YES;
            }else{
                $option_result = self::IS_RIGHT_NO;
            }

            $findResultUserDetail = LnExamResultDetail::find(false)->andFilterWhere(['examination_paper_user_id'=>$examination_paper_user_id, 'examination_question_user_id'=> $examination_question_user_id, 'examination_option_user_id' => $options_id, 'examination_result_process_id' => $resultProcessUser->kid, 'examination_result_final_id' => $resultFinalUser->kid, 'company_id' => $company_id, 'user_id' => $user_id, 'examination_id' => $examination_id])->one();
            if ($checked == 'True'){
                if (!empty($findResultUserDetail->kid)) {
                    return ['result' => 'success', 'errmsg' => 'already'];
                }else{
                    /*添加记录*/
                    $this->saveResultUserDetail($examination_question_user_id, $params, $options_id, $resultProcessUser, $resultFinalUser, $company_id, $user_id, $examQuestionUser, $examinationQuestionOptionUser, $examinationModel->examination_version, $option_result);
                }
            }else {
                if ($findResultUserDetail) {
                    /*删除记录*/
                    LnExamResultDetail::physicalDeleteAll(['kid' => $findResultUserDetail->kid]);
                }
            }
        }else{
            return ['result' => 'fail', 'errmsg' => Yii::t('frontend','exam_type_not_have')];
        }
    }

    /**
     * 获取推送用户
     * @param $params
     * @return bool
     */
    public function getRelUser($params){
        $cacheKey = 'get-rel-user-'.$params['learning_object_id']."-".$params['user_id'];
        $result =Yii::$app->cache->get($cacheKey);
        if($result){
            return true;
        }else{
            $result=LnRelatedUser::find(false)
                ->andFilterWhere(["=", "learning_object_id", $params['learning_object_id']])
                ->andFilterWhere(["=", "user_id", $params['user_id']])
                ->andFilterWhere(["=", "learning_object_type", 'exam'])
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

}