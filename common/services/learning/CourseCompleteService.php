<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 3/21/2015
 * Time: 10:57 AM
 */
namespace common\services\learning;

use common\models\framework\FwUser;
use common\models\learning\LnCertification;
use common\models\learning\LnComponent;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseactivity;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnCourseware;
use common\models\learning\LnModRes;
use common\models\learning\LnResComplete;
use common\models\learning\LnUserCertification;
use common\models\learning\LnHomeworkResult;
use common\models\message\MsTimeline;
use common\services\framework\PointRuleService;
use common\services\message\TimelineService;
use common\services\scorm\ScormScoesTrackService;
use common\base\BaseActiveRecord;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use vakata\database\Result;
use yii\log\Logger;

class CourseCompleteService extends LnCourseComplete{

    /**
     * 判断课程完成情况
     * @param $courseRegId
     * @return bool
     */
    public function isCourseComplete($courseRegId)
    {
        $model = new LnCourseComplete();
        $result = $model->find(false)
            ->andFilterWhere(['=','complete_status',LnCourseComplete::COMPLETE_STATUS_DONE])
            ->andFilterWhere(['=','complete_type',LnCourseComplete::COMPLETE_TYPE_FINAL])
            ->andFilterWhere(['=','course_reg_id',$courseRegId])
            ->count(1);

        if (!empty($result) && intval($result) > 0) {
           return true;
        }

        return false;
    }

    /**
     * 当课程已完成或正在重学状态时，都可以是已算是已完成
     * @param $courseRegId
     * @return bool
     */
    public function isCourseCompleteOrRetake($courseRegId)
    {
        $model = new LnCourseComplete();
        $result = $model->find(false)
            ->andFilterWhere(['or',['=', 'complete_status', LnCourseComplete::COMPLETE_STATUS_DONE],
                ['=', 'is_retake', LnCourseComplete::IS_RETAKE_YES]])
            ->andFilterWhere(['=','complete_type',LnCourseComplete::COMPLETE_TYPE_FINAL])
            ->andFilterWhere(['=','course_reg_id',$courseRegId])
            ->count(1);

        if (!empty($result) && intval($result) > 0) {
            return true;
        }

        return false;
    }

    /**
     * 判断课程是否正在重修
     * @param $courseRegId
     * @return bool
     */
    public function isCourseRetake($courseRegId)
    {
        $model = new LnCourseComplete();
        $result = $model->find(false)
            ->andFilterWhere(['<>','complete_status',LnCourseComplete::COMPLETE_STATUS_DONE])
            ->andFilterWhere(['=','complete_type',LnCourseComplete::COMPLETE_TYPE_FINAL])
            ->andFilterWhere(['=','is_retake',LnCourseComplete::IS_RETAKE_YES])
            ->andFilterWhere(['=','course_reg_id',$courseRegId])
            ->count(1);

        if (!empty($result) && intval($result) > 0) {
            return true;
        }

        return false;
    }

    /**
     * 判断课程是否进行中
     * @param $courseRegId
     * @param $courseId
     * @return bool
     */
    public function isCourseDoing($courseRegId)
    {
        $model = new LnCourseComplete();
        $result = $model->find(false)
            ->andFilterWhere(['=','complete_status',LnCourseComplete::COMPLETE_STATUS_DOING])
            ->andFilterWhere(['=','complete_type',LnCourseComplete::COMPLETE_TYPE_FINAL])
            ->andFilterWhere(['=','course_reg_id',$courseRegId])
            ->count(1);

        if (!empty($result) && intval($result) > 0) {
            return true;
        }

        return false;
    }


    /**
     * 检查课程状态
     * @param $courseCompleteFinalModel LnCourseComplete
     * @param $isDoing
     * @param $isCourseComplete
     * @param $isRetake
     * @return mixed|LnCourseComplete|static
     */
    public function checkCourseStatus($courseCompleteFinalModel, &$isDoing, &$isCourseComplete, &$isRetake)
    {
        if ($courseCompleteFinalModel) {
            if ($courseCompleteFinalModel->complete_status == LnCourseComplete::COMPLETE_STATUS_DOING) {
                $isDoing = true;
            } else {
                $isDoing = false;
            }

            if ($courseCompleteFinalModel->complete_status == LnCourseComplete::COMPLETE_STATUS_DONE) {
                $isCourseComplete = true;
            } else {
                $isCourseComplete = false;
            }

            if ($courseCompleteFinalModel->is_retake == LnCourseComplete::COMPLETE_TYPE_FINAL) {
                $isRetake = true;
            } else {
                $isRetake = false;
            }
        }
    }

    /**
     * 改变课程完成情况为进行中
     * @param LnCourseComplete $courseCompleteModel
     */
    public function changeCourseCompleteStatusToDoing($courseCompleteModel,$currentTime)
    {
        if ($courseCompleteModel->complete_status == LnCourseComplete::COMPLETE_STATUS_NOTSTART) {
            $courseCompleteModel->complete_status = LnCourseComplete::COMPLETE_STATUS_DOING;
            $courseCompleteModel->start_at = $currentTime;
            $courseCompleteModel->last_record_at = $currentTime;
            $courseCompleteModel->save();
        }
    }

    /**
     * 设置上次记录时间
     * @param $resCompleteId
     * @param $time
     */
    public function setLastRecordAt($courseCompleteId, $time, $duration, $systemKey = null)
    {
        if ($courseCompleteId != null) {
            $model = new LnCourseComplete();

            $condition = "kid = :kid";

            $param = [
                ':kid' => $courseCompleteId,
            ];

            $attributes = [
                'learning_duration' => new Expression('learning_duration + ' . strval($duration)),
                'last_record_at' => $time
            ];

            $model->updateAll($attributes, $condition, $param, true, false, $systemKey, false);
        }
    }

    /**
     * 返回最后尝试次数，如果没有访问过，则返回1
     * @param $courseRegId
     * @return int
     */
    public function getLastAttempt($courseRegId) {

        $courseComplete = new CourseCompleteService();
        $courseCompleteModel = $courseComplete->getLastCourseCompleteInfo($courseRegId,LnCourseComplete::COMPLETE_TYPE_FINAL);
        if (!empty($courseCompleteModel))
        {
            return strval($courseCompleteModel->attempt_number);
        }
        else
        {
            return "1";
        }
    }

    /**
     * 获取课程完成记录中最后一条未完成信息记录
     * @param $courseRegId
     * @param $completeType
     * @return array|null|LnCourseComplete
     */
    public function getLastCourseCompleteNonDoneInfo($courseRegId,$completeType)
    {
        $model = new LnCourseComplete();
        $query = $model->find(false)
            ->andFilterWhere(['=', 'course_reg_id', $courseRegId]);

//        if ($completeType == LnCourseComplete::COMPLETE_TYPE_PROCESS) {
            $query->andFilterWhere(['<>','complete_status',LnCourseComplete::COMPLETE_STATUS_DONE]);
//        }

        $query->andFilterWhere(['=','complete_type',$completeType])
            ->addOrderBy(['updated_at' => SORT_DESC]);

        $result = $query->one();
        return $result;
    }

    /**
     * 获取课程完成记录中最后一条信息记录
     * @param $courseRegId
     * @param $courseId
     * @param $completeType
     * @return array|null|LnCourseComplete
     */
    public function getLastCourseCompleteInfo($courseRegId,$completeType)
    {
        $model = new LnCourseComplete();
        $query = $model->find(false)
            ->andFilterWhere(['=', 'course_reg_id', $courseRegId])
            ->andFilterWhere(['=','complete_type',$completeType])
            ->addOrderBy(['updated_at' => SORT_DESC]);

        $result = $query->one();
        return $result;
    }

    public function checkCourseCompleteInfoExist($courseRegId,$completeType, $withSession = true)
    {
        $sessionKey = "CourseReg_" . $courseRegId . "_CompleteType_" . $completeType;

        if ($withSession && Yii::$app->session->has($sessionKey)) {
            return Yii::$app->session->get($sessionKey);
        }
        else {
            $model = new LnCourseComplete();
            $result = $model->find(false)
                ->andFilterWhere(['=', 'course_reg_id', $courseRegId])
                ->andFilterWhere(['=', 'complete_type', $completeType])
                ->count('kid');

            if (isset($result) && intval($result) > 0) {
                if ($withSession) {
                    Yii::$app->session->set($sessionKey, true);
                }
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 初始化课程完成记录
     * @param $courseRegId
     * @param $courseId
     * @param $user_id
     */
    public function initCourseCompleteInfo($courseRegId, $courseId, $user_id = null)
    {
        $result = $this->checkCourseCompleteInfoExist($courseRegId,LnCourseComplete::COMPLETE_TYPE_FINAL);

        if (!$result)
        {
            $userId = $user_id ? $user_id : Yii::$app->user->getId();

            $courseModel = LnCourse::findOne($courseId);
            $courseVersion = $courseModel->course_version;

            $processModel = new LnCourseComplete();
            $processModel->course_id = $courseId;
            $processModel->course_reg_id = $courseRegId;
            $processModel->complete_type = LnCourseComplete::COMPLETE_TYPE_PROCESS;
            $processModel->complete_status = LnCourseComplete::COMPLETE_STATUS_NOTSTART;
            $processModel->user_id = $userId;
            $processModel->is_passed = $courseModel->course_type == LnCourse::COURSE_TYPE_ONLINE ? LnCourseComplete::IS_PASSED_NO : LnCourseComplete::IS_PASSED_YES;
            $processModel->is_direct_completed = LnCourseComplete::IS_DIRECT_COMPLETED_NO;
            $processModel->course_version = $courseVersion;
            $processModel->attempt_number = 1;

            $processModel->save();

            $finalModel = new LnCourseComplete();
            $finalModel->course_id = $courseId;
            $finalModel->course_reg_id = $courseRegId;
            $finalModel->complete_type = LnCourseComplete::COMPLETE_TYPE_FINAL;
            $finalModel->complete_status = LnCourseComplete::COMPLETE_STATUS_NOTSTART;
            $finalModel->user_id = $userId;
            $finalModel->is_passed =  $courseModel->course_type == LnCourse::COURSE_TYPE_ONLINE ? LnCourseComplete::IS_PASSED_NO : LnCourseComplete::IS_PASSED_YES;
            $finalModel->is_direct_completed = LnCourseComplete::IS_DIRECT_COMPLETED_NO;
            $finalModel->course_version = $courseVersion;
            $finalModel->attempt_number = 1;

            $finalModel->save();
        }
    }


    /**
     * 重置课程完成记录
     * @param $courseRegId
     * @param $courseId
     */
    public function resetCourseCompleteInfo($courseRegId,$courseId)
    {
        $finalResult = $this->getLastCourseCompleteDoneInfo($courseRegId,LnCourseComplete::COMPLETE_TYPE_FINAL);
//        $processResult = $this->GetLastCourseCompleteDoneInfo($courseRegId,LnCourseComplete::COMPLETE_TYPE_PROCESS);

        if (!empty($finalResult))
        {
            $currentAttemptNumber =  $finalResult->attempt_number + 1;
            $finalResult->is_direct_completed_last = $finalResult->is_direct_completed;
            $finalResult->is_passed_last = $finalResult->is_passed;
            $finalResult->course_version_last = $finalResult->course_version;
            $finalResult->start_at_last = $finalResult->start_at;
            $finalResult->end_at_last = $finalResult->end_at;
            $finalResult->last_record_at_last = $finalResult->last_record_at;
            $finalResult->learning_duration_last = $finalResult->learning_duration;
            $finalResult->attempt_number_last = $finalResult->attempt_number;
            $finalResult->complete_score_last = $finalResult->complete_score;
            $finalResult->complete_grade_last = $finalResult->complete_grade;
            $finalResult->real_score_last = $finalResult->real_score;
            $finalResult->complete_method_last = $finalResult->complete_method;

            $userId = Yii::$app->user->getId();
            $courseModel = LnCourse::findOne($courseId);
            $courseVersion = $courseModel->course_version;

            //重置一条最终主数据为未开始状态，保留当前成绩
            $finalResult->course_version = $courseVersion;

            //20160121：以后有可能点重学后，需要先把历史分清了，否则如果某个scorm是先拿到完成状态，然后到课程里一判断已经有分了，并且是合格的，就会认为整个课已经完成
            //如果is_retake=1，并且是还未完成状态，成绩要取complete_score_last,real_score_last,complete_grade_last
//            $finalResult->complete_grade = null;
//            $finalResult->complete_score = null;
//            $finalResult->real_score = null;

            $finalResult->complete_status = LnCourseComplete::COMPLETE_STATUS_NOTSTART;
            $finalResult->is_passed =  $courseModel->course_type == LnCourse::COURSE_TYPE_ONLINE ? LnCourseComplete::IS_PASSED_NO : LnCourseComplete::IS_PASSED_YES;
            $finalResult->is_direct_completed = LnCourseComplete::IS_DIRECT_COMPLETED_NO;
//            $result->start_at = null;
//            $result->end_at = null;
//            $result->last_record_at = null;
//            $result->learning_duration = 0;
            $finalResult->is_retake = LnCourseComplete::IS_RETAKE_YES;
            $finalResult->complete_method = LnCourseComplete::COMPLETE_METHOD_COMPLETE;
            $finalResult->attempt_number = $currentAttemptNumber;
            $finalResult->needReturnKey = true;
            $finalResult->save();


            //生成一条新的过程主数据
            $processModel = new LnCourseComplete();
            $processModel->course_id = $courseId;
            $processModel->course_reg_id = $courseRegId;
            $processModel->complete_type = LnCourseComplete::COMPLETE_TYPE_PROCESS;
            $processModel->complete_status = LnCourseComplete::COMPLETE_STATUS_NOTSTART;
            $processModel->user_id = $userId;
            $processModel->is_passed =  $courseModel->course_type == LnCourse::COURSE_TYPE_ONLINE ? LnCourseComplete::IS_PASSED_NO : LnCourseComplete::IS_PASSED_YES;
            $processModel->is_direct_completed = LnCourseComplete::IS_DIRECT_COMPLETED_NO;
            $processModel->start_at = null;
            $processModel->end_at = null;
            $processModel->last_record_at = null;
            $processModel->learning_duration = 0;
            $processModel->attempt_number = $currentAttemptNumber;
            $processModel->course_version = $courseVersion;
            $processModel->save();

            //备份最终明细数据，以便放弃时还原
            $resourceCompleteService = new ResourceCompleteService();
            $resourceCompleteService->backupResCompleteInfo($finalResult->kid);


//            //备份过程明细数据，以便放弃时还原
//            $resourceCompleteService = new ResourceCompleteService();
//            $resourceCompleteService->BackupResCompleteInfo($processResult->kid);
        }
    }


    /**
     * 放弃重学课程
     * @param $courseRegId
     * @param $courseId
     */
    public function giveupResetCourseCompleteInfo($courseRegId)
    {
        $result = $this->getLastCourseCompleteNonDoneInfo($courseRegId,LnCourseComplete::COMPLETE_TYPE_FINAL);

        if (!empty($result))
        {
            $tempAttempt = $result->attempt_number;
            $result->is_direct_completed = $result->is_direct_completed_last;
            $result->is_passed = $result->is_passed_last;
            $result->course_version = $result->course_version_last;
            $result->complete_grade = $result->complete_grade_last;
            $result->complete_score = $result->complete_score_last;
            $result->real_score = $result->real_score_last;
            $result->complete_method = $result->complete_method_last;
//            $result->start_at = $result->start_at_last;//学习时间需要持续计算
//            $result->end_at = $result->end_at_last;//学习时间需要持续计算
//            $result->last_record_at = $result->last_record_at_last;//学习时间需要持续计算
//            $result->learning_duration = $result->learning_duration_last;//学习时间需要持续计算
            $result->attempt_number = $result->attempt_number_last;
            $result->is_retake = LnCourseComplete::IS_RETAKE_NO;
            $result->complete_status = LnCourseComplete::COMPLETE_STATUS_DONE;

            $result->is_direct_completed_last = LnCourseComplete::IS_DIRECT_COMPLETED_NO;
            $result->is_passed_last = LnCourseComplete::IS_PASSED_NO;
            $result->course_version_last = null;
            $result->start_at_last = null;
            $result->end_at_last = null;
            $result->last_record_at_last = null;
            $result->learning_duration_last = null;
            $result->attempt_number_last = null;
            $result->complete_grade_last = null;
            $result->complete_score_last = null;
            $result->real_score_last = null;
            $result->complete_method_last = null;
            $result->needReturnKey = true;
            $result->save();

            //删除新的最终明细数据
            $resourceCompleteService = new ResourceCompleteService();
            $resourceCompleteService->resetResCompleteInfo($result->kid,LnResComplete::COMPLETE_TYPE_FINAL);

            //删除新的过程主数据
            $processResult = $this->getLastCourseCompleteNonDoneInfo($courseRegId,LnCourseComplete::COMPLETE_TYPE_PROCESS);
            if (!empty($processResult))
            {
                //删除新的过程明细数据
                $resourceCompleteService->resetResCompleteInfo($processResult->kid,LnResComplete::COMPLETE_TYPE_PROCESS);
                $processResult->delete();
            }

            $scormScoesTrackService = new ScormScoesTrackService();
            $scormScoesTrackService->resetScoesTrackInfo($courseRegId,null,$tempAttempt);

            //还原最终明细数据
            $resourceCompleteService = new ResourceCompleteService();
            $resourceCompleteService->restoreResCompleteInfo($result->kid);

            //调查结果不还原，始终以最后一次为准
//            //还原调查结果明细数据
           $investigationSerivce = new InvestigationService();
           $investigationSerivce->restoreInvestigationResult($result->kid,$result->attempt_number);

            /*删除本次重学的考试成绩*/
            $examinationService = new ExaminationService();
            $examinationService->deleteGiveUpCoursesExaminationResult($result->course_id, $result->kid, $courseRegId, $tempAttempt);

            /*删除本次重学的作业成绩*/
            $homeworkResultService = new HomeworkService();
            $homeworkResultService->deleteGiveUpCoursesHomeworkResult($result->course_id, $tempAttempt);
//            $stderr = fopen("temp.txt",'a');
//            fwrite($stderr,"\r\n\r\n temp:".$temp);
//
//            fclose($stderr);
        }
    }



    /**
     * 获取课程完成记录中最后一条信息记录
     * @param $courseRegId
     * @param $courseId
     * @param $completeType
     * @return LnCourseComplete
     */
    public function getLastCourseCompleteDoneInfo($courseRegId,$completeType)
    {
        $model = new LnCourseComplete();
        $query = $model->find(false)
            ->andFilterWhere(['=', 'course_reg_id', $courseRegId])
            ->andFilterWhere(['=','complete_status',LnCourseComplete::COMPLETE_STATUS_DONE])
            ->andFilterWhere(['=','complete_type',$completeType])
            ->addOrderBy(['updated_at' => SORT_DESC]);

        $result = $query->one();
        return $result;
    }

    /**
     * 增加课程完成状态（适用于面授课程）
     * @param $courseRegId
     * @param $courseId
     * @param $completeType
     * @param $forceComplete
     */
    public function addCourseCompleteDoneInfoForFaceToFace($courseCompleteId)
    {
        $courseCompleteModel = LnCourseComplete::findOne($courseCompleteId);
        $userId = $courseCompleteModel->user_id;
        $courseId = $courseCompleteModel->course_id;
        $completeType = $courseCompleteModel->complete_type;
//        $userId = Yii::$app->user->getId();
        $courseModel = LnCourse::findOne($courseId);
        $courseVersion = $courseModel->course_version;
        $currentTime = time();

        $timelineService = new TimelineService();
        $resourceCompleteService = new ResourceCompleteService();

        //课程合格线
        $coursePassGrade = $courseModel->pass_grade;
        $courseTotalGrade = $courseModel->default_credit;//课程总学分
        $isRecordScore = $courseModel->is_record_score; //是否计分

//            $courseCompleteModel->complete_type = $completeType;
        $courseCompleteModel->course_version = $courseVersion;

        if (!$courseCompleteModel->is_noshow) {

            if ($courseModel->is_recalculate_score == LnCourse::YES) {
                //重新计分
                $this->reCalculateScore($courseId, $courseCompleteId, $completeType);
            }

            //面授课程强制完成
            $courseCompleteModel->complete_status = LnCourseComplete::COMPLETE_STATUS_DONE;

            //需要计算总成绩（根据权重）
            $totalScore = $resourceCompleteService->getSumResourceScoreAfter($courseCompleteId, $completeType);

            if ($totalScore != null)
            {
                $totalScore = round($totalScore, 2); //加权平均后的分数,需要做四舍五入保留2位处理
            }

            $isPassed = LnCourseComplete::IS_PASSED_YES;
            /*通过课件通过数据与已经发布的课件数据对比判断是否完成*/
            $courseModResCount = $resourceCompleteService->getCoursePublicModResCount($courseId);
            $userModResPassedCount = $resourceCompleteService->getUserModResPassedCount($userId, $courseId);
            if ($userModResPassedCount < $courseModResCount){
                $isPassed = LnResComplete::IS_PASSED_NO;
            }
            //如果课程合格线为空，则只要资源完成，就算是合格。
            //否则，如果资源完成学分>=资源合格线则算是完成
            if (isset($coursePassGrade) && $coursePassGrade != null && $totalScore < $coursePassGrade) {
                $isPassed = LnCourseComplete::IS_PASSED_NO;
            }
            /*20160615修改讲师成绩管理时调整参与讨论人：明强、赵亮、丽华、adophper*/
            //$courseCompleteModel->is_passed = $isPassed;
            $courseCompleteModel->is_passed = LnCourseComplete::IS_PASSED_YES;;

            $courseCompleteModel->real_score = $totalScore;
            if ($isRecordScore) {
                $courseCompleteModel->complete_score = $totalScore;
            } else {
                $courseCompleteModel->complete_score = null;
            }

            //只要课程是否合格为合格，就拿到课程总学分
            if ($isPassed == LnCourseComplete::IS_PASSED_YES) {
                $totalGrade = $courseTotalGrade;
            } else {
                $totalGrade = null;
            }


            $courseCompleteModel->complete_grade = $totalGrade;
            $courseCompleteModel->is_direct_completed = LnCourseComplete::IS_DIRECT_COMPLETED_YES;
            $courseCompleteModel->end_at = $currentTime;

            /*历程树在设置完成前判断本身学习是否完成*/
            /*if ($courseCompleteModel->complete_type == LnCourseComplete::COMPLETE_TYPE_FINAL) {
                //添加学习记录（完成课程）20160120修改
                if ($this->IsCourseComplete($courseCompleteModel->course_reg_id)) {
                    $recordService = new RecordService();
                    $recordService->addByCompletedCourse($userId, $courseId, strval($totalGrade));
                }
                $timelineService->setComplete($courseId, MsTimeline::OBJECT_TYPE_COURSE, MsTimeline::TIMELINE_TYPE_TODO, $userId);
            }*/

            if ($courseCompleteModel->save()) {

                if ($courseCompleteModel->complete_type == LnCourseComplete::COMPLETE_TYPE_FINAL) {
                    //添加学习记录（完成课程）
                    $recordService = new RecordService();
                    $recordService->addByCompletedCourse($userId, $courseId, strval($totalGrade));
                    $timelineService->setComplete($courseId, MsTimeline::OBJECT_TYPE_COURSE, MsTimeline::TIMELINE_TYPE_TODO, $userId);
                    /*添加积分*/
                    if ($courseCompleteModel->is_passed == LnCourseComplete::IS_PASSED_YES){
                        $pointRuleService = new PointRuleService();
                        $user = FwUser::find(false)->andFilterWhere(['kid'=>$userId])->select('company_id')->one();
                        $companyId = $user->company_id;
                        $result = $pointRuleService->checkActionForPoint($companyId, $userId, 'Complete-F2F-Course', 'Learning-Portal', $courseId);
                    }
                }
            }
        }
    }

	/**
     * 讲师编辑课程判断重新算分
     * @param $courseId
     */
    public function resetCourseResComplete($courseId){
        $result = LnCourseComplete::find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->select('kid,complete_type')
            ->all();

        if (!empty($result)){
            foreach ($result as $item){
                $this->reCalculateScore($courseId, $item->kid, $item->complete_type);
            }
        }
    }

    /**
     * 重新算分
     * @param $modResInfos
     * @param $courseCompleteId
     * @param $completeType
     */
    private function reCalculateScore($courseId, $courseCompleteId, $completeType)
    {
        $resourceService = new ResourceService();
        $modResInfos = $resourceService->GetResourceInfo($courseId);
        if (!empty($modResInfos) && count($modResInfos) > 0) {
            $completeModels = [];
            $resourceCompleteService = new ResourceCompleteService();
//            $currentTime = time();
            foreach ($modResInfos as $modRes) {
                $modResId = $modRes->kid;
//                $courseId = $modRes->course_id;
                $ishaveScale = LnModRes::IS_HAVE_SOCRE_SCALE_NO;
                $courseService = new CourseService();
                //是否有直通
                $CountModResScale = $courseService->CountModResScaleCourseId($courseId);
                if ($CountModResScale > 0) {
                    $ishaveScale = LnModRes::IS_HAVE_SOCRE_SCALE_YES;
                }
                $isRecordScore = $modRes->is_record_score; //是否计分
                $passGrade = $modRes->pass_grade; //合格线
                $scoreScale = $modRes->score_scale; //权重
                $transferTotalScore = $modRes->transfer_total_score; //换算分制
                //强制百分制（临时）
                $transferTotalScore = 100.00;

//                $componentCode = LnComponent::findOne($modRes->component_id)->component_code;
                $completeModel = $resourceCompleteService->getCompleteResourceData($courseCompleteId, $completeType, $modResId);


                if ($modRes->res_type == LnModRes::RES_TYPE_COURSEWARE) {
                    $coursewareModel = LnCourseware::findOne($modRes->courseware_id);

                    $singleTotalScore = $coursewareModel->default_credit;
//                    //课件最终成绩
//                    if ($componentCode == "scorm" || $componentCode == "aicc") {
//                        $scormService = new ScormService();
//                        $scormModel = $scormService->getScormByCoursewareId($modRes->courseware_id);
//                        $singleTotalScore = intval($scormModel->total_score);
//                    }
//                    else {
//                        $singleTotalScore = $coursewareModel->default_credit;
//                    }

                } else {
                    $courseactivityModel = LnCourseactivity::findOne($modRes->courseactivity_id);

                    $singleTotalScore = $courseactivityModel->default_credit;
                }

                if (!empty($completeModel)) {
                    $scoreBefore = $completeModel->score_before;
//                    Yii::getLogger()->log("pc cousrsecomplete scoreBefore:" .  implode(",",$scoreBefore)  , Logger::LEVEL_ERROR);


                    if (!empty($transferTotalScore)) {
                        //如果要换算 ，加权后成绩就是当前分*换算/总分
                        $scoreBeforeTransfer = $scoreBefore * $transferTotalScore / $singleTotalScore;
                    } else {
                        $scoreBeforeTransfer = $scoreBefore;
                    }

                    if ($ishaveScale == LnModRes::IS_HAVE_SOCRE_SCALE_YES) {
                        $scoreAfter = $scoreBeforeTransfer * $scoreScale / LnModRes::SCORE_PERCENT;
                    } else {
                        //需要判断是否所有的子模块都已经完成,除去直通
                        $resourceService = new ResourceService();
                        $modResInfoCount = $resourceService->getResourceInfoNoDirectCount($courseId);
                        $scoreAfter = $scoreBeforeTransfer / $modResInfoCount;
                    }

                    if ($isRecordScore) {
                        $completeScore = null;
                    } else {
                        $completeScore = $scoreBefore;
                    }

                    //如果资源合格线为空，则只要资源完成，就算是合格。
                    //否则，如果资源完成成绩>=资源合格线则算是完成
                    $isPassed = LnResComplete::IS_PASSED_YES;
                    if (isset($passGrade) && $passGrade != null && $scoreBeforeTransfer < $passGrade) {
                        $isPassed = LnResComplete::IS_PASSED_NO;
                    }

                    if ($isPassed) {
                        $completeStatus = LnResComplete::COMPLETE_STATUS_DONE;
                    } else {
                        $completeStatus = LnResComplete::COMPLETE_STATUS_DOING;
                    }

                    $completeModel->score_after = $scoreAfter;
                    $completeModel->complete_score = $completeScore;
                    $completeModel->is_passed = $isPassed;
                    $completeModel->complete_status = $completeStatus;
//                    $completeModel->end_at = $currentTime;

                    array_push($completeModels, $completeModel);
                }
            }

            BaseActiveRecord::batchUpdateNormalMode($completeModels);
        }
    }


    /**
     * 增加课程完成状态（适用于在线课程）
     * @param $courseRegId
     * @param $courseId
     * @param $completeType
     * @param $forceComplete
     * @param $forceCompleteScore //是否直通课成绩
     */
    public function addCourseCompleteDoneInfoForOnline($courseCompleteId,  $forceComplete = false, $systemKey = null,$forceCompleteScore = null, $allowRepeat = false,$isMaster= false,&$courseComplete =false,&$getCetification=false,&$courseId=null,&$certificationId=null)
    {
        $courseCompleteModel = LnCourseComplete::findOne($courseCompleteId);

        if (!empty($courseCompleteModel)) {
            $oldCompleteStatus = $courseCompleteModel->complete_status;

            $userId = $courseCompleteModel->user_id;
            $courseId = $courseCompleteModel->course_id;
            $completeType = $courseCompleteModel->complete_type;

            $courseModel = LnCourse::findOne($courseId);
            $certificationFrom = $courseModel->course_name;
            $courseVersion = $courseModel->course_version;
            $currentTime = time();
            $courseComplete = false;

            $isRecordScore = $courseModel->is_record_score; //是否计分

            //课程合格线
            $coursePassGrade = $courseModel->pass_grade;

            $courseTotalGrade = $courseModel->default_credit;//课程总学分

            $timelineService = new TimelineService();

            if ($forceComplete == true) {
//            $courseCompleteModel->course_id = $courseId;
//            $courseCompleteModel->user_id = $userId;
//            $courseCompleteModel->course_reg_id = $courseRegId;
                $completeStatus = LnCourseComplete::COMPLETE_STATUS_DONE;
                $courseCompleteModel->complete_status = $completeStatus;
//            $courseCompleteModel->complete_type = $completeType;
                $courseCompleteModel->course_version = $courseVersion;

                //如果课程合格线为空，则只要资源完成，就算是合格。
                //否则，如果资源完成学分>=资源合格线则算是完成
                $isPassed = LnCourseComplete::IS_PASSED_YES;
                if (isset($coursePassGrade) && $coursePassGrade != null && $coursePassGrade != 0 && ($forceCompleteScore == null || $forceCompleteScore < $coursePassGrade)) {
                    $isPassed = LnCourseComplete::IS_PASSED_NO;
                }

                $courseCompleteModel->is_passed = $isPassed;


//            $score = $totalGrade;


                $courseCompleteModel->real_score = $forceCompleteScore;

                //如果计分则显示分数,否则不显示
                if ($isRecordScore) {
                    $courseCompleteModel->complete_score = $forceCompleteScore;
                } else {
                    $courseCompleteModel->complete_score = null;
                }

                if ($isPassed == LnCourseComplete::IS_PASSED_YES) {
                    //课程完成学分只要课程是否合格为合格，就拿到课程总学分
                    $courseCompleteModel->complete_grade = $courseTotalGrade;//课程总学分
                } else {
                    $courseCompleteModel->complete_grade = null;
                }

                $courseCompleteModel->is_direct_completed = LnCourseComplete::IS_DIRECT_COMPLETED_YES;
                $courseCompleteModel->end_at = $currentTime;

//            //(当前时间-上次记录时间)+历史持续时间
//            if (!empty($courseCompleteModel) && $currentTime > $courseCompleteModel->last_record_at) {
//                $courseCompleteModel->learning_duration = ($currentTime - $courseCompleteModel->last_record_at) + $courseCompleteModel->learning_duration;
//                $courseCompleteModel->last_record_at = $currentTime;
//            }
                $courseCompleteModel->systemKey = $systemKey;
                if ($isMaster) {
                    $courseCompleteModel->complete_method = LnResComplete::COMPLETE_METHOD_MASTER;
                }
                else {
                    $courseCompleteModel->complete_method = LnResComplete::COMPLETE_METHOD_COMPLETE;
                }

                if ($courseCompleteModel->save()) {

                    if ($completeStatus == LnCourseComplete::COMPLETE_STATUS_DONE) {
                        $courseComplete = true;
                    }
                }
            } else {
                $resourceService = new ResourceService();
                $resourceCompleteService = new ResourceCompleteService();

                //需要判断是否所有的子模块都已经完成
                $modResInfos = $resourceService->getNoneDirectCompleteResourceInfo($courseId);//只取非直通的
                $selectedList = ArrayHelper::map($modResInfos, 'kid', 'kid');
                $modResIdList = array_keys($selectedList);

//                Yii::getLogger()->log("pc modResIdList:" .  implode(",",$modResIdList)  , Logger::LEVEL_ERROR);

                $modResCompleteInfos = $resourceCompleteService->getNoneDirectCompleteResourceInfo($courseCompleteId, $completeType);//只取非直通的
                $selectedCompleteList = ArrayHelper::map($modResCompleteInfos, 'mod_res_id', 'mod_res_id');
                $modResCompleteIdList = array_keys($selectedCompleteList);

//                Yii::getLogger()->log("pc modResCompleteIdList:" . implode(",",$modResCompleteIdList), Logger::LEVEL_ERROR);

                $diffArray = array_diff($modResIdList, $modResCompleteIdList);

//                Yii::getLogger()->log("pc diffArray:" . implode(",",$diffArray), Logger::LEVEL_ERROR);

                //需要计算总成绩（根据权重）
                $totalScore = $resourceCompleteService->getNoneDirectSumResourceScoreAfter($courseCompleteId, $completeType);//只取非直通的

                if ($totalScore != null) {
                    $totalScore = round($totalScore, 2); //加权平均后的分数,需要做四舍五入保留2位处理
                }

                //如果课程合格线为空，则只要资源完成，就算是合格。
                //否则，如果资源完成学分>=资源合格线则算是完成
                $isPassed = LnCourseComplete::IS_PASSED_YES;
                if (isset($coursePassGrade) && $coursePassGrade != null && ($totalScore == null || $totalScore < $coursePassGrade)) {
                    $isPassed = LnCourseComplete::IS_PASSED_NO;
                }

                if (empty($diffArray)) {
                    //全部完成了

                    //只要课程是否合格为合格，就拿到课程总学分
                    if ($isPassed == LnCourseComplete::IS_PASSED_YES) {
                        $totalGrade = $courseTotalGrade;
                        $completeStatus = LnCourseComplete::COMPLETE_STATUS_DONE;
                    } else {
                        $totalGrade = null;
                        $completeStatus = LnCourseComplete::COMPLETE_STATUS_DOING;
                    }

                    $courseCompleteModel->end_at = $currentTime;
                } else {
                    //还未全部完成
                    $totalGrade = null;
                    $completeStatus = LnCourseComplete::COMPLETE_STATUS_DOING;
                }

                //                //课程完成学分=课程完成成绩/课程总成绩 *课程总学分
//                $totalGrade = $totalScore/$courseTotalScore*$courseTotalGrade;

//                $courseCompleteModel->course_id = $courseId;
//                $courseCompleteModel->user_id = $userId;
//                $courseCompleteModel->course_reg_id = $courseRegId;
                $courseCompleteModel->complete_status = $completeStatus;
//                $courseCompleteModel->complete_type = $completeType;
                $courseCompleteModel->course_version = $courseVersion;
                $courseCompleteModel->is_passed = $isPassed;
                $courseCompleteModel->real_score = $totalScore;

                if ($isRecordScore) {
                    $courseCompleteModel->complete_score = $totalScore;
                } else {
                    $courseCompleteModel->complete_score = null;
                }
                $courseCompleteModel->complete_grade = $totalGrade;
                $courseCompleteModel->is_direct_completed = LnCourseComplete::IS_DIRECT_COMPLETED_NO;


//                //(当前时间-上次记录时间)+历史持续时间
//                if (!empty($courseCompleteModel) && $currentTime > $courseCompleteModel->last_record_at) {
//                    $courseCompleteModel->learning_duration = ($currentTime - $courseCompleteModel->last_record_at) + $courseCompleteModel->learning_duration;
//                    $courseCompleteModel->last_record_at = $currentTime;
//                }

                $courseCompleteModel->systemKey = $systemKey;
                if ($isMaster) {
                    $courseCompleteModel->complete_method = LnResComplete::COMPLETE_METHOD_MASTER;
                }
                else {
                    $courseCompleteModel->complete_method = LnResComplete::COMPLETE_METHOD_COMPLETE;
                }
                if ($courseCompleteModel->save()) {
                    if ($completeStatus == LnCourseComplete::COMPLETE_STATUS_DONE) {
//                        if($courseCompleteModel->complete_type == LnCourseComplete :: COMPLETE_TYPE_FINAL){
//                            $pointRuleService=new PointRuleService();
//                            $pointRuleService->curUserCheckActionForPoint('Complete-Online-Course','Learning-Portal',$courseCompleteModel->kid);
//                        }
                        $courseComplete = true;
                    }
                }
            }


            if ($courseComplete) {
                //删除备份的最终明细数据
                $resourceCompleteService = new ResourceCompleteService();
                $resourceCompleteService->resetResCompleteInfo($courseCompleteId, LnResComplete::COMPLETE_TYPE_BACKUP);


                if ($courseCompleteModel->complete_type == LnCourseComplete::COMPLETE_TYPE_FINAL) {
                    //20160117：对于只是掌握的情况，说明有可能一直在重复提交掌握进度，所以只发一次证、只记录一次学习完成记录（首次）
                    if (!$isMaster || ($isMaster && $oldCompleteStatus != LnCourseComplete::COMPLETE_STATUS_DONE)) {
                        //课件完成后，如果证书是自动颁证的，需要颁证
                        $certificationService = new CertificationService();
                        $certificationList = $certificationService->getCertificationListByCourseId($courseId);
                        if (!empty($certificationList) && count($certificationList) > 0) {
                            foreach ($certificationList as $certification) {
                                if ($certification->is_auto_certify == LnCertification::IS_AUTO_CERTIFY_YES) {
                                    $certificationId = $certification->kid;
                                    $issuedBy = $courseModel->created_by;//自动发证的颁发人员为课程创建者
                                    $userCertificationId = $certificationService->createUserCertification($certificationId, $userId, $issuedBy,
                                        $courseCompleteModel->complete_score, $courseCompleteModel->complete_grade, $courseId,$certificationFrom, $systemKey);

                                    //首次完成时，才执行如下操作
                                    if (!empty($userCertificationId) && !empty($courseId)) {
                                        $getCetification = true;
//                                        //add by baoxianjian 14:35 2016/3/17调用积分接口
//                                        $pointRuleService=new PointRuleService();
//                                        $pointRuleService->curUserCheckActionForPoint('Get-Certification','Certification',$certificationId);
                                        $certificationModel = LnCertification::findOne($certificationId);
                                        //邮件通知讲师
                                        if ($certificationModel->is_email_teacher == LnCertification::IS_EMAIL_TEACHER_YES) {
                                            $t = new LnCourse();
                                            if ($teacher = $t->getLnCourseTeacher($courseId)) {
                                                $teacherId = $teacher['kid'];
                                                $userList = [];
                                                array_push($userList, $userId);
                                                $certificationService->sendEmailToTeacher($courseId, $userList, $teacherId, $certificationModel);
                                            }
                                        }
                                    }

                                }
                            }
                        }



                        //添加学习记录（完成课程）
                        $recordService = new RecordService();
                        $recordService->addByCompletedCourse($userId, $courseId, strval($courseCompleteModel->complete_grade));
                        $timelineService->setComplete($courseId, MsTimeline::OBJECT_TYPE_COURSE, MsTimeline::TIMELINE_TYPE_TODO, $userId);
                    }
                }
            }
        }
    }

    /**
     * 获取用户在指定时间内完成的课程数
     * @param $userId
     * @param $beginTime
     * @param $endTime
     * @return int|string
     */
    public function getUserCompleteCourseCount($userId, $beginTime, $endTime)
    {
        $model = LnCourseComplete::find(false)
            ->andWhere(['user_id' => $userId])
            ->andWhere(['complete_type' => LnCourseComplete::COMPLETE_TYPE_FINAL])
            ->andFilterWhere(['or', ['=', 'complete_status', LnCourseComplete::COMPLETE_STATUS_DONE],
                ['=', 'is_retake', LnCourseComplete::IS_RETAKE_YES]]);

        if ($beginTime) {
            $model->andWhere(['>=', 'updated_at', $beginTime]);

        }
        if ($endTime) {
            $model->andWhere(['<=', 'updated_at', $endTime]);
        }
        return $model->count(1);
    }

    /**
     * 判断用户学习是否及格
     * @param $courseId
     * @param $userId
     * @return bool
     */
    public function isUserPass($courseId, $userId){
        $count = LnCourseComplete::find(false)
            ->andFilterWhere(['=', 'user_id', $userId])
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'complete_status', LnCourseComplete::COMPLETE_STATUS_DONE])
            ->andFilterWhere(['=', 'complete_type', LnCourseComplete::COMPLETE_TYPE_FINAL])
            ->andFilterWhere(['=', 'is_passed', LnCourseComplete::IS_PASSED_YES])
            ->count(1);

        if ($count > 0){
            return true;
        }else{
            return false;
        }
    }

    public function setCourseCompletePassStatus($courseId, $pass, $user){
        if (empty($user)){
            return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'please_select_handle_object')];
        }
        if (is_array($user)){
            $userArray = "'".join("','", $user)."'";
        }else{
            $userArray = "'{$user}'";
        }
        $this->updateAll(
            ['is_passed' => $pass],
            "course_id=:course_id and complete_type=:complete_type and user_id in ({$userArray})",
            [
                ':course_id' => $courseId,
                ':complete_type' => LnCourseComplete::COMPLETE_TYPE_FINAL,
//                ':user' => $user,
            ]
        );

        return ['result' => 'success', 'errmsg' => 'OK'];
    }
}