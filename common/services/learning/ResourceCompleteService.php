<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 3/21/2015
 * Time: 10:57 AM
 */
namespace common\services\learning;

use common\helpers\TBaseHelper;
use common\models\framework\FwUser;
use common\models\framework\FwUserDisplayInfo;
use common\models\learning\LnCertification;
use common\models\learning\LnComponent;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseactivity;
use common\models\learning\LnCourseCertification;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnCourseEnroll;
use common\models\learning\LnCourseReg;
use common\models\learning\LnCourseware;
use common\models\learning\LnCoursewareScorm;
use common\models\learning\LnExaminationResultUser;
use common\models\learning\LnModRes;
use common\models\learning\LnResComplete;
use common\models\learning\LnResourceDomain;
use common\services\learning\CourseService;
use common\services\scorm\ScormScoesTrackService;
use common\services\scorm\ScormService;
use components\widgets\TPagination;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class ResourceCompleteService extends LnResComplete{

    /**
     * 判断资源完成情况
     * @param $courseRegId
     * @return bool
     */
    public function isResComplete($courseCompleteId,$modResId)
    {
        $model = new LnResComplete();
        $result = $model->find(false)
            ->andFilterWhere(['=', 'complete_status', LnResComplete::COMPLETE_STATUS_DONE])
            ->andFilterWhere(['=', 'complete_type', LnResComplete::COMPLETE_TYPE_FINAL])
//            ->andFilterWhere(['=','course_reg_id',$courseRegId])
            ->andFilterWhere(['=', 'mod_res_id', $modResId])
            ->andFilterWhere(['=', 'course_complete_id', $courseCompleteId])
            ->count(1);

        if (!empty($result) && intval($result) > 0) {
            return true;
        }

        return false;
    }


    /**
     * 获取资源进度
     * @param $courseCompleteId
     * @param $modResId
     * @param $isDoing
     * @param $isComplete
     * @return array|null|\yii\db\ActiveRecord
     */
    public function checkResourceStatus($courseCompleteId, $modResId, &$isDoing, &$isComplete)
    {
        if (empty($courseCompleteId) || empty($modResId) ){
            $isDoing = false;
            $isComplete = false;
            return null;
        }
        else {
            $model = new LnResComplete();

            $result = $model->find(false)
                ->andFilterWhere(['=', 'mod_res_id', $modResId])
                ->andFilterWhere(['=', 'complete_type', LnResComplete::COMPLETE_TYPE_FINAL])
                ->andFilterWhere(['=', 'course_complete_id', $courseCompleteId])
                ->addOrderBy(['updated_at' => SORT_DESC])
                ->one();

            if ($result->complete_status == LnResComplete::COMPLETE_STATUS_DOING) {
                $isDoing = true;
            } else {
                $isDoing = false;
            }

            if ($result->complete_status == LnResComplete::COMPLETE_STATUS_DONE) {
                $isComplete = true;
            } else {
                $isComplete = false;
            }
        }
        return $result;
    }

    /**
     * 判断资源完成情况（含重学中的）
     * @param $courseRegId
     * @return bool
     */
    public function isResCompleteOrRetake($courseCompleteId,$modResId)
    {
        $model = new LnResComplete();
        $result = $model->find(false)
            ->andFilterWhere(['or',['=', 'complete_type',LnResComplete::COMPLETE_TYPE_BACKUP],[
                'and',['=', 'complete_status', LnCourseComplete::COMPLETE_STATUS_DONE],
                ['=', 'complete_type', LnCourseComplete::COMPLETE_TYPE_FINAL]]])

//            ->andFilterWhere(['=','course_reg_id',$courseRegId])
            ->andFilterWhere(['=', 'mod_res_id', $modResId])
            ->andFilterWhere(['=', 'course_complete_id', $courseCompleteId])
            ->count(1);

        if (!empty($result) && intval($result) > 0) {
            return true;
        }

        return false;
    }

    /**
     * 判断资源是否进行中
     * @param $courseRegId
     * @param $modResId
     * @return bool
     */
    public function isResDoing($courseCompleteId, $modResId)
    {
        $model = new LnResComplete();
        $result = $model->find(false)
            ->andFilterWhere(['=','complete_status',LnResComplete::COMPLETE_STATUS_DOING])
            ->andFilterWhere(['=','complete_type',LnResComplete::COMPLETE_TYPE_FINAL])
//            ->andFilterWhere(['=','course_reg_id',$courseRegId])
            ->andFilterWhere(['=','mod_res_id',$modResId])
            ->andFilterWhere(['=','course_complete_id',$courseCompleteId])
            ->count(1);

        if (!empty($result) && intval($result) > 0) {
            return true;
        }

        return false;
    }


    /**
     * 获取课件完成记录中最后一条未完成的信息记录
     * @param $courseRegId
     * @param $modResId
     * @param $completeType
     * @param $courseCompleteId
     * @return null|LnResComplete
     */
    public function getLastResCompleteNonDoneInfo($courseRegId,$modResId,$completeType,$courseCompleteId)
    {
        $model = new LnResComplete();


        $query = $model->find(false)
                ->andFilterWhere(['=', 'course_reg_id', $courseRegId]);

//        if ($completeType == LnResComplete::COMPLETE_TYPE_PROCESS) {
            $query->andFilterWhere(['<>','complete_status',LnResComplete::COMPLETE_STATUS_DONE]);
//        }

        $query->andFilterWhere(['=','mod_res_id',$modResId])
            ->andFilterWhere(['=','complete_type',$completeType])
            ->andFilterWhere(['=','course_complete_id',$courseCompleteId])
            ->addOrderBy(['updated_at' => SORT_DESC]);

        return $query->one();
    }


    /**
     * 获取最近一次资源完成状态
     * @param $courseRegId
     * @param $modResId
     * @param $completeType
     * @param $courseCompleteId
     * @return array|null|LnResComplete
     */
    public function getLastResCompleteInfo($courseRegId,$modResId,$completeType,$courseCompleteId)
    {
        $model = new LnResComplete();

        $query = $model->find(false)
            ->andFilterWhere(['=', 'course_reg_id', $courseRegId])
            ->andFilterWhere(['=','mod_res_id',$modResId])
            ->andFilterWhere(['=','complete_type',$completeType])
            ->andFilterWhere(['=','course_complete_id',$courseCompleteId])
            ->addOrderBy(['updated_at' => SORT_DESC]);

        return $query->one();
    }

    /**
     * 备份资源完成情况
     * @param $courseCompleteId
     * @param null $modResId
     */
    public function backupResCompleteInfo($courseCompleteId)
    {
        $model = new LnResComplete();

        $params = [
            ':course_complete_id'=>$courseCompleteId,
        ];

        $condition = 'course_complete_id = :course_complete_id';

        $attributes = [
            'complete_type' => LnResComplete::COMPLETE_TYPE_BACKUP,
        ];

        $model->updateAll($attributes,$condition,$params);
    }


    /**
     * 还原资源完成情况
     * @param $courseCompleteId
     */
    public function restoreResCompleteInfo($courseCompleteId)
    {
        $model = new LnResComplete();

        $params = [
            ':course_complete_id'=>$courseCompleteId,
            ':complete_type'=>LnResComplete::COMPLETE_TYPE_BACKUP,
        ];

        $condition = 'complete_type = :complete_type and course_complete_id = :course_complete_id';

        $attributes = [
            'complete_type' => LnResComplete::COMPLETE_TYPE_FINAL,
        ];

        $model->updateAll($attributes,$condition,$params);
    }

    /**
     * 重置资源完成情况
     * @param $courseCompleteId
     */
    public function resetResCompleteInfo($courseCompleteId,$completeType,$modResId = null)
    {
        $model = new LnResComplete();


        if (empty($modResId)) {
            $params = [
                ':course_complete_id' => $courseCompleteId,
                ':complete_type' => $completeType,
            ];

            $condition = 'complete_type = :complete_type and course_complete_id = :course_complete_id';

        }
        else
        {
            $params = [
                ':course_complete_id' => $courseCompleteId,
                ':complete_type' => $completeType,
                ':mod_res_id' => $modResId,
            ];

            $condition = "complete_type = :complete_type and course_complete_id = :course_complete_id and mod_res_id = :mod_res_id";
        }

//        $attributes = [
//            'complete_grade' => null,
//            'complete_score' => null,
//            'complete_status' => LnResComplete::COMPLETE_STATUS_NOTSTART,
//            'is_passed' => LnResComplete::IS_PASSED_NO,
//            'start_at' => null,
//            'end_at' => null,
//            'last_record_at' => null,
//            'learning_duration' => 0,
//        ];

        $model->deleteAll($condition,$params);
    }


    /**
     * 增加资源进行中状态
     * @param $courseRegId
     * @param $modResId
     * @param $completeType
     */
    public function addResCompleteDoingInfo($courseCompleteId,$courseRegId,$modResId,$completeType,$systemKey = null)
    {
        $userId = Yii::$app->user->getId();

        $modResModel = LnModRes::findOne($modResId);
        $courseId = $modResModel->course_id;
        $courseModel = LnCourse::findOne($courseId);
        $courseVersion = $courseModel->course_version;


        $resourceVersion = null;

        if ($modResModel->res_type == LnModRes::RES_TYPE_COURSEWARE)
        {
            $coursewareModel = LnCourseware::findOne($modResModel->courseware_id);
            $resourceVersion = $coursewareModel->resource_version;
        }
        else
        {
            $courseactivityModel = LnCourseactivity::findOne($modResModel->courseactivity_id);
            $resourceVersion = $courseactivityModel->resource_version;
        }

        if ($courseCompleteId != null)
        {
            $currentTime = time();
            $resCompleteModel = new LnResComplete();
            $resCompleteModel->course_complete_id = $courseCompleteId;
            $resCompleteModel->course_id = $courseId;
            $resCompleteModel->course_reg_id = $courseRegId;
            $resCompleteModel->user_id = $userId;
            $resCompleteModel->mod_id = $modResModel->mod_id;
            $resCompleteModel->mod_res_id = $modResId;
            $resCompleteModel->courseware_id = $modResModel->courseware_id;
            $resCompleteModel->courseactivity_id = $modResModel->courseactivity_id;
            $resCompleteModel->component_id =  $modResModel->component_id;
            $resCompleteModel->resource_type =  $modResModel->res_type;
            $resCompleteModel->complete_status = LnResComplete::COMPLETE_STATUS_DOING;
            $resCompleteModel->complete_type = $completeType;
            $resCompleteModel->course_version = $courseVersion;
            $resCompleteModel->resource_version = $resourceVersion;
            $resCompleteModel->start_at = $currentTime;
            $resCompleteModel->last_record_at = $currentTime;

            if ($resCompleteModel->validate())
            {
                $resCompleteModel->systemKey = $systemKey;
                $resCompleteModel->needReturnKey = true;
                $resCompleteModel->save();
                return $resCompleteModel->kid;
            }
        }
    }


    /**
     * 设置上次记录时间
     * @param $resCompleteId
     * @param $time
     */
    public function setLastRecordAt($resCompleteId, $time, $duration,$systemKey = null)
    {
        if ($resCompleteId != null) {
            $model = new LnResComplete();

            $condition = "kid = :kid";

            $param = [
                ':kid' => $resCompleteId,
            ];

            $attributes = [
                'learning_duration' => new Expression('learning_duration + ' . strval($duration)),
                'last_record_at' => $time
            ];

            $model->updateAll($attributes, $condition, $param, true, false, $systemKey, false);
        }
    }

    /**
     * 增加资源完成状态
     * @param $courseRegId
     * @param $modResId
     * @param $completeType
     * @param $scoreBefore //资源完成成绩
     * @param $completeGrade //资源完成学分
     * @param $allScoPassed //所有scorm子单元
     */
    public function addResCompleteDoneInfo($courseCompleteId,$courseRegId,$modResId,$completeType,$scoreBefore = null,$completeGrade = null,$allowRepeat = false,
                                           $systemKey = null,$allScoPassed = true, $isMaster=false,&$courseComplete =false,&$getCetification=false,&$courseId=null,&$certificationId=null)
    {
        /*$stderr = fopen("test.txt",'a');
        fwrite($stderr,"\r\n\r\n".$scoreBefore);
        fclose($stderr);*/
        $courseCompleteService = new CourseCompleteService();
        $currentTime = time();
        $modResModel = LnModRes::findOne($modResId);
        $courseId = $modResModel->course_id;
        $courseModel = LnCourse::findOne($courseId);
        $courseVersion = $courseModel->course_version;
        $componentCode = LnComponent::findOne($modResModel->component_id)->component_code;
        $isHaveScale = LnModRes::IS_HAVE_SOCRE_SCALE_NO;
        $courseService = new CourseService();
        //是否有没设置权重的非直通课件
        $CountModResScale = $courseService->CountModResScaleCourseId($courseId);
        if($CountModResScale > 0){
            $isHaveScale = LnModRes::IS_HAVE_SOCRE_SCALE_YES;
        }
        $resourceVersion = null;

        $isRecordScore = $modResModel->is_record_score; //是否计分
        $passGrade = $modResModel->pass_grade; //合格线
        $scoreScale = $modResModel->score_scale; //权重
        $transferTotalScore = $modResModel->transfer_total_score; //换算分制
        //强制百分制（临时）
        $transferTotalScore = 100.00;
        //单项总分
        $singleTotalScore = null;

        if ($modResModel->res_type == LnModRes::RES_TYPE_COURSEWARE) {
            $coursewareModel = LnCourseware::findOne($modResModel->courseware_id);
            $resourceVersion = $coursewareModel->resource_version;

            $singleTotalScore = $coursewareModel->default_credit;

//            //课件最终成绩
//            if ($componentCode == "scorm" || $componentCode == "aicc") {
//                $scormService = new ScormService();
//                $scormModel = $scormService->getScormByCoursewareId($modResModel->courseware_id);
//                $singleTotalScore = intval($scormModel->total_score);
//            }
//            else {
//                $singleTotalScore = $coursewareModel->default_credit;
//            }

//            if ($coursewareTotalGrade == null)
//                $coursewareTotalGrade = 0;


//            //资源完成学分=资源完成成绩
//            if ($completeGrade == null){
//                $completeGrade = $coursewareTotalGrade;
//            }

//            if ($completeGrade == null && $completeScore != null) {
//                if ($componentCode == "scorm") {
//                    $scormService = new ScormService();
//
//                    $scorm = $scormService->getScormByCoursewareId($modResModel->courseware_id);
//
//                    if (!empty($scorm)) {
//                        //Scorm课件总成绩
//                        $scormScore = $scorm->total_score;
//                    }
//
//                    if (empty($scormScore) || $scormScore == 0) //如果没有设置Scorm课件总成绩，则默认等于课件总学分，以便乘除相抵
//                        $scormScore = $coursewareTotalGrade;
//
//                    //资源完成学分=资源完成成绩/Scorm课件总成绩*课件总学分
//                    $completeGrade = $completeScore / $scormScore * $coursewareTotalGrade;
//                } else {
//                    //资源完成学分=课件总学分
//                    $completeGrade = $coursewareTotalGrade;
//                }
//            }
        } else {
            $courseactivityModel = LnCourseactivity::findOne($modResModel->courseactivity_id);
            $resourceVersion = $courseactivityModel->resource_version;

            $singleTotalScore = $courseactivityModel->default_credit;

//            //活动的计分逻辑，以后可能要改
//            if ($courseactivityTotalGrade == null)
//                $courseactivityTotalGrade = 0;




//            if ($completeGrade == null)
//                $completeGrade = $courseactivityTotalGrade;
        }

        //如果scorm课件已全部完成，并且传入的成绩为空,则取默认值
        if ($componentCode == "scorm" || $componentCode == "aicc") {
            //2016/5/9:唐明强，scorm成绩修改为得几分就记录积分，不会强制设置成100
//            if ($allScoPassed && $scoreBefore == null) {
//                //并且是没有设置过合格线的课件。如果有合格线设置，说明这门课是有一定要求的
//                if (!isset($passGrade) || $passGrade == null || $passGrade == 0) {
//                    $scoreBefore = $singleTotalScore;
//                }
//            }
        }
        else {
            //如果是非scorm课件，并且传入的成绩为空,则取默认值
            if (is_null($scoreBefore)) {
                $scoreBefore = $singleTotalScore;
            }
        }

        if (!empty($transferTotalScore)) {
            if ($scoreBefore != null) {
                //如果要换算 ，加权后成绩就是当前分*换算/总分
                $scoreBeforeTransfer = ($scoreBefore / $singleTotalScore) * $transferTotalScore;
            }
            else {
                $scoreBeforeTransfer = null;
            }
        }
        else {
            $scoreBeforeTransfer = $scoreBefore;
        }


        if ($scoreBeforeTransfer != null) {
            if ($isHaveScale == LnModRes::IS_HAVE_SOCRE_SCALE_YES && $modResModel->direct_complete_course == LnModRes::DIRECT_COMPLETE_COURSE_NO) {

                if (empty($scoreScale)) {
                    $scoreScale = 0;
                }
                $scoreAfter = $scoreBeforeTransfer * $scoreScale / LnModRes::SCORE_PERCENT;

            } elseif ($modResModel->direct_complete_course == LnModRes::DIRECT_COMPLETE_COURSE_NO) {
                //求非直通课件的平均分
                $resourceService = new ResourceService();
                $modResInfoCount = $resourceService->getResourceInfoNoDirectCount($courseId);
                if ($modResInfoCount === 0) {
                    $scoreAfter = null;
                } else {
                    $scoreAfter = $scoreBeforeTransfer / $modResInfoCount;
                }
            } elseif ($modResModel->direct_complete_course == LnModRes::DIRECT_COMPLETE_COURSE_YES) {
                //仅适合单直通，后期多直通的话需要修改

                $scoreAfter = $scoreBeforeTransfer;
            } else {
                $scoreAfter = null;
            }
        }
        else {
            $scoreAfter = null;
        }

        //如果是计分的话
        if ($isRecordScore) {
            $completeScore = $scoreBeforeTransfer;
        } else {
            $completeScore = null;
            $scoreAfter  = null;
        }

        //如果资源合格线为空，则只要资源完成，就算是合格。
        //否则，如果资源完成成绩>=资源合格线则算是完成
        $isPassed = LnResComplete::IS_PASSED_YES;
        if (isset($passGrade) && $passGrade != null && $passGrade != 0 && (empty($scoreBefore) || $scoreBeforeTransfer < $passGrade)) {
            $isPassed = LnResComplete::IS_PASSED_NO;
        }

        if ($allowRepeat) {
            $resCompleteModel = $this->getLastResCompleteInfo($courseRegId,
                $modResId, $completeType, $courseCompleteId);
        } else {
            $resCompleteModel = $this->getLastResCompleteNonDoneInfo($courseRegId,
                $modResId, $completeType, $courseCompleteId);
        }
        

        if ($isPassed && $allScoPassed) {
            $completeStatus = LnResComplete::COMPLETE_STATUS_DONE;
        } else {
            $completeStatus = LnResComplete::COMPLETE_STATUS_DOING;
        }

        if ($resCompleteModel != null) {
            
            if ($isMaster) {
                $resCompleteModel->complete_method = LnResComplete::COMPLETE_METHOD_MASTER;
            }
            else {
                $resCompleteModel->complete_method = LnResComplete::COMPLETE_METHOD_COMPLETE;
            }
            
            $resCompleteModel->complete_grade = $completeGrade;
            $resCompleteModel->score_before = $scoreBefore;
            $resCompleteModel->score_after = $scoreAfter;
            $resCompleteModel->complete_score = $completeScore;
            $resCompleteModel->complete_status = $completeStatus;
            $resCompleteModel->end_at = $currentTime;
            $resCompleteModel->is_passed = $isPassed;
            $resCompleteModel->course_version = $courseVersion;
            $resCompleteModel->resource_version = $resourceVersion;

//            //(当前时间-上次记录时间)+历史持续时间
//            if (!empty($resCompleteModel) && $currentTime > $resCompleteModel->last_record_at) {
//                $resCompleteModel->learning_duration = ($currentTime - $resCompleteModel->last_record_at) + $resCompleteModel->learning_duration;
//                $resCompleteModel->last_record_at = $currentTime;
//            }

            $resCompleteModel->systemKey = $systemKey;
//            $resCompleteModel->needReturnKey = true;
            if ($resCompleteModel->save()) {

                //如果此资源的资源直接完成课程=true，那么这门课完成且成绩合格后，课程直接就完成且合格。
                $directCompleteCourse = $modResModel->direct_complete_course;

                //如果强制完成的课件完成，则整个课程完成，如果存在多个课件,则需要取平均成绩
                $forceComplete = false;
                $forceCompleteScore = null;

                //进入直通模式计分处理
                if ($directCompleteCourse == LnModRes::DIRECT_COMPLETE_COURSE_YES) {
                    if ($completeStatus == LnResComplete::COMPLETE_STATUS_DONE) {

                        $resourceService = new ResourceService();
                        $resourceCompleteService = new ResourceCompleteService();
                        //直接完成课程的总数：
                        //已经完成的总数：

                        //需要判断是否所有的直接完成课程的模块都已经完成
                        $modResInfos = $resourceService->GetDirectCompleteResourceInfo($courseId);
                        $selectedList = ArrayHelper::map($modResInfos, 'kid', 'kid');
                        $modResIdList = array_keys($selectedList);

//                Yii::getLogger()->log("pc modResIdList:" .  implode(",",$modResIdList)  , Logger::LEVEL_ERROR);

                        $modResCompleteInfos = $resourceCompleteService->getDirectCompleteResourceInfo($courseCompleteId, $completeType);
                        $selectedCompleteList = ArrayHelper::map($modResCompleteInfos, 'mod_res_id', 'mod_res_id');
                        $modResCompleteIdList = array_keys($selectedCompleteList);

//                Yii::getLogger()->log("pc modResCompleteIdList:" . implode(",",$modResCompleteIdList), Logger::LEVEL_ERROR);

                        $diffArray = array_diff($modResIdList, $modResCompleteIdList);

                        if (empty($diffArray)) {
                            //全部完成了
                            $forceComplete = true;
                            //已经完成的平均分
                            $tempScore = null;
                            foreach ($modResCompleteInfos as $resCom) {
                                if ($resCom->complete_score != null) {
                                    if ($tempScore == null) {
                                        $tempScore = 0;
                                    }
                                    $tempScore += $resCom->complete_score;
                                }
                            }

                            if ($tempScore != null) {
                                $forceCompleteScore = $tempScore / count($modResCompleteInfos);
                            }
                            else {
                                $forceCompleteScore = null;
                            }

                        } else {
                            //还未全部完成
                        }


                    }
                }

                if ($componentCode == "scorm" || $componentCode == "aicc") {
                    if ($completeStatus == LnResComplete::COMPLETE_STATUS_DONE 
                        && $completeType == LnResComplete::COMPLETE_TYPE_FINAL) {
                        //如果使用MongoDB不需要删除历史数据
                        if (!TBaseHelper::isUseMongoDB()) {
                            $attempt = LnCourseComplete::findOne($resCompleteModel->course_complete_id)->attempt_number;
                            $scormScoesTrackService = new ScormScoesTrackService();
                            $scormScoesTrackService->deleteTrackData($courseRegId, $modResId, $attempt);
                        }
                    }
                }
                //检查课程完成情况(仅针对在线课程)
                if ($courseModel->course_type == LnCourse::COURSE_TYPE_ONLINE) {
                    $courseCompleteService->addCourseCompleteDoneInfoForOnline($courseCompleteId, $forceComplete, $systemKey, $forceCompleteScore, $allowRepeat, $isMaster,$courseComplete,$getCetification,$courseId,$certificationId);
                }
            }
        }
    }


    /**
     * 获取相关课程的完成资源数
     * @param $courseRegId
     * @param $courseCompleteId
     * @return int|string
     */
    public function getCompleteResourceCount($courseCompleteId,$courseRegId,$completeType)
    {
        $model = new LnResComplete();
        $result = $model->find(false)
            ->andFilterWhere(['=','course_complete_id',$courseCompleteId])
            ->andFilterWhere(['=','course_reg_id',$courseRegId])
            ->andFilterWhere(['=','complete_status',LnResComplete::COMPLETE_STATUS_DONE])
            ->andFilterWhere(['=','complete_type',$completeType])
            ->count(1);

        return intval($result);
    }

    /**
     * 获取相关课程的完成资源信息
     * @param $courseRegId
     * @param $courseCompleteId
     * @return int|string
     */
    public function getCompleteResourceInfo($courseCompleteId,$completeType)
    {
        $model = new LnResComplete();
        $result = $model->find(false)
            ->andFilterWhere(['=','course_complete_id',$courseCompleteId])
            ->andFilterWhere(['=','complete_status',LnResComplete::COMPLETE_STATUS_DONE])
            ->andFilterWhere(['=','complete_type',$completeType])
            ->all();

        return $result;
    }

    /**
     * 获取相关课程的完成资源信息
     * @param $courseRegId
     * @param $courseCompleteId
     * @return int|string
     */
    public function getNoneDirectCompleteResourceInfo($courseCompleteId,$completeType)
    {
        $model = new LnResComplete();
        $result = $model->find(false)
            ->innerJoinWith("lnModRes")
            ->andFilterWhere(['=','course_complete_id',$courseCompleteId])
            ->andFilterWhere(['=','complete_status',LnResComplete::COMPLETE_STATUS_DONE])
            ->andFilterWhere(['=','complete_type',$completeType])
            ->andFilterWhere(['=', 'direct_complete_course', LnModRes::DIRECT_COMPLETE_COURSE_NO])
            ->all();

        return $result;
    }

    public function getDirectCompleteResourceInfo($courseCompleteId,$completeType)
    {
        $model = new LnResComplete();
        $result = $model->find(false)
            ->innerJoinWith("lnModRes")
            ->andFilterWhere(['=','course_complete_id',$courseCompleteId])
            ->andFilterWhere(['=','complete_status',LnResComplete::COMPLETE_STATUS_DONE])
            ->andFilterWhere(['=','complete_type',$completeType])
            ->andFilterWhere(['=', 'direct_complete_course', LnModRes::DIRECT_COMPLETE_COURSE_YES])
            ->all();

        return $result;
    }

    /**
     * 获取已完成的资源模块列表
     * @param $courseCompleteId
     * @param $completeType
     * @param $modResIdList
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getCompleteResourceInfoModresIdList($courseCompleteId, $completeType, $modResIdList)
    {
        $model = new LnResComplete();
        $result = $model->find(false)
            ->andFilterWhere(['=','course_complete_id',$courseCompleteId])
            ->andFilterWhere(['=','complete_status',LnResComplete::COMPLETE_STATUS_DONE])
            ->andFilterWhere(['=','complete_type',$completeType])
            ->andFilterWhere(['in','mod_res_id',$modResIdList])
            ->all();

        return $result;
    }

    /**
     * 获取相关课程的完成资源信息
     * @param $courseCompleteId
     * @param $completeType
     * @param $modResId
     * @return array|null|LnResComplete
     */
    public function getCompleteResourceData($courseCompleteId,$completeType,$modResId)
    {
        $model = new LnResComplete();
        $result = $model->find(false)
            ->andFilterWhere(['=','course_complete_id',$courseCompleteId])
            ->andFilterWhere(['=','mod_res_id',$modResId])
            ->andFilterWhere(['=','complete_status',LnResComplete::COMPLETE_STATUS_DONE])
            ->andFilterWhere(['=','complete_type',$completeType])
            ->one();

        return $result;
    }

	/**
     * 获取记分课件数量
     * @param $courseId
     * @return int|string
     */
    public function getCourseIsRecordScoreModResCount($courseId){
        $model = new LnModRes();
        $count = $model->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'is_record_score', LnModRes::IS_HAVE_SOCRE_SCALE_YES])
            ->andFilterWhere(['=', 'publish_status', LnModRes::PUBLIC_STATUS_YES])
            ->count(1);

        return $count;
    }

    /**
     * 判断课件是否配置计分,根据总权重分为100判断
     * @param $courseId
     * @return bool
     */
    public function isCourseModResScale($courseId){
        $model = new LnModRes();
        $scoreScaleTotal = $model->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'is_record_score', LnModRes::IS_HAVE_SOCRE_SCALE_YES])
            ->andFilterWhere(['=', 'publish_status', LnModRes::PUBLIC_STATUS_YES])
            ->sum("score_scale");

        if ($scoreScaleTotal < 100){
            return false;
        }else{
            return true;
        }
    }

//    /**
//     * 获取平均分
//     * @param $courseCompleteId
//     * @param $completeType
//     * @return mixed
//     */
//    public function getAvgResourceScoreAfter($courseCompleteId, $completeType){
//        $model = new LnResComplete();
//        $result = $model->find(false)
//            ->innerJoinWith("lnModRes")
//            ->andFilterWhere(['=','course_complete_id',$courseCompleteId])
////            ->andFilterWhere(['=','complete_status',LnResComplete::COMPLETE_STATUS_DONE])
//            ->andFilterWhere(['=','complete_type',$completeType])
//            ->andFilterWhere(['=', 'is_record_score', LnModRes::IS_RECORD_SCORE_YES])
//            ->average("score_after");
//
//        return $result;
//    }


    /**
     * 求合加权后成绩
     * @param $courseCompleteId
     * @param $completeType
     * @return mixed
     */
    public function getSumResourceScoreAfter($courseCompleteId, $completeType)
    {
        $model = new LnResComplete();
        $count = $model->find(false)
            ->innerJoinWith("lnModRes")
            ->andFilterWhere(['=','course_complete_id',$courseCompleteId])
//            ->andFilterWhere(['=','complete_status',LnResComplete::COMPLETE_STATUS_DONE])
            ->andFilterWhere(['=','complete_type',$completeType])
            ->andFilterWhere(['=', 'is_record_score', LnModRes::IS_RECORD_SCORE_YES])
            ->andWhere('score_after is not null')
            ->count(1);

        $result = null;
        if ($count > 0) {
            $result = $model->find(false)
                ->innerJoinWith("lnModRes")
                ->andFilterWhere(['=', 'course_complete_id', $courseCompleteId])
//            ->andFilterWhere(['=','complete_status',LnResComplete::COMPLETE_STATUS_DONE])
                ->andFilterWhere(['=', 'complete_type', $completeType])
                ->andFilterWhere(['=', 'is_record_score', LnModRes::IS_RECORD_SCORE_YES])
                ->sum("score_after");
        }
        return $result;
    }


    /**
     * 求合加权后成绩
     * @param $courseCompleteId
     * @param $completeType
     * @param $modResId
     * @return
     */
    public function getNoneDirectSumResourceScoreAfter($courseCompleteId, $completeType)
    {
        $model = new LnResComplete();
        $count = $model->find(false)
            ->innerJoinWith("lnModRes")
            ->andFilterWhere(['=','course_complete_id',$courseCompleteId])
//            ->andFilterWhere(['=','complete_status',LnResComplete::COMPLETE_STATUS_DONE])
            ->andFilterWhere(['=','complete_type',$completeType])
            ->andFilterWhere(['=', 'direct_complete_course', LnModRes::DIRECT_COMPLETE_COURSE_NO])
            ->andWhere('score_after is not null')
            ->count(1);

        $result = null;
        if ($count > 0) {
            $model = new LnResComplete();
            $result = $model->find(false)
                ->innerJoinWith("lnModRes")
                ->andFilterWhere(['=', 'course_complete_id', $courseCompleteId])
//            ->andFilterWhere(['=','complete_status',LnResComplete::COMPLETE_STATUS_DONE])
                ->andFilterWhere(['=', 'complete_type', $completeType])
                ->andFilterWhere(['=', 'direct_complete_course', LnModRes::DIRECT_COMPLETE_COURSE_NO])
                ->sum("score_after");
        }
        return $result;
    }

    /**
     * 查询组件学习数据
     * @param $modResId
     * @param null $key
     * @param null $score_type
     * @param null $score
     * @param null $score_status
     * @return int|string
     */
    public function getResCompleteCount($modResId, $key = null, $score_type = null, $score = null, $score_status = null)
    {
        $query = LnResComplete::find(false);
        $query->innerJoin(FwUser::tableName() . ' u', LnResComplete::tableName() . '.user_id=u.kid')
            ->andFilterWhere(['=', 'mod_res_id', $modResId])
            ->andFilterWhere(['=', 'complete_type', LnResComplete::COMPLETE_TYPE_FINAL]);

        if ($key) {
            $query->andFilterWhere(['or', ['like', 'real_name', $key], ['like', 'email', $key]]);
        }
        if ($score_type && $score) {
            $query->andFilterWhere([$score_type, 'complete_score', $score]);
        }
        $result = $query->count(1);

        return $result;
    }

    /**
     * 课程成绩查看
     * @param $modResId
     * @param $params
     * @return array
     */
    public function getResCompleteData($modResId, $params)
    {
        $modRes = LnModRes::findOne($modResId);
        $course = LnCourse::findOne($modRes->course_id);
        if ($course->course_type == LnCourse::COURSE_TYPE_FACETOFACE){
            $query = LnCourseEnroll::find(false)
                ->andFilterWhere(['=', LnCourseEnroll::tableName().'.course_id', $modRes->course_id])
                ->andFilterWhere(['=', LnCourseEnroll::tableName().'.enroll_type', LnCourseEnroll::ENROLL_TYPE_ALLOW])
                ->leftJoin(LnResComplete::tableName(), LnResComplete::tableName().".course_id=".LnCourseEnroll::tableName().".course_id and ".LnResComplete::tableName().".user_id=".LnCourseEnroll::tableName().".user_id and ".LnResComplete::tableName().".mod_res_id='{$modResId}' and ".LnResComplete::tableName().".complete_type='".LnResComplete::COMPLETE_TYPE_FINAL."' and ".LnResComplete::tableName().".is_deleted='0'");
            if (!empty($params['userId'])){
                $query->andFilterWhere(['=', LnCourseEnroll::tableName().'.user_id', $params['userId']]);
            }
            $query->leftJoin(FwUserDisplayInfo::tableName(), FwUserDisplayInfo::tableName().".user_id=".LnCourseEnroll::tableName().".user_id");
        }else{
            $query = LnCourseReg::find(false)
                ->andFilterWhere(['=', LnCourseReg::tableName().'.course_id', $modRes->course_id])
                ->andFilterWhere(['=', LnCourseReg::tableName().'.reg_state', LnCourseReg::REG_STATE_APPROVED])
                ->leftJoin(LnResComplete::tableName(), LnResComplete::tableName().".course_reg_id=".LnCourseReg::tableName().".kid and ".LnResComplete::tableName().".mod_res_id='{$modResId}' and ".LnResComplete::tableName().".complete_type='".LnResComplete::COMPLETE_TYPE_FINAL."' and ".LnResComplete::tableName().".is_deleted='0'");
            if (!empty($params['userId'])){
                $query->andFilterWhere(['=', LnCourseReg::tableName().'.user_id', $params['userId']]);
            }
            $query->leftJoin(FwUserDisplayInfo::tableName(), FwUserDisplayInfo::tableName().".user_id=".LnCourseReg::tableName().".user_id");
        }

        if (isset($params['score_status'])){
            if ($params['score_status'] == '0'){
                $query->andFilterWhere(['or', ['=', LnResComplete::tableName().".complete_status", LnResComplete::COMPLETE_STATUS_NOTSTART], LnResComplete::tableName().".complete_status is null"]);
            }else{
                $query->andFilterWhere(['=', LnResComplete::tableName().'.complete_status', $params['score_status']]);
            }
        }

        if (!empty($params['keyword'])) {
            $keyword = htmlspecialchars($params['keyword']);
            $query->andFilterWhere(['or', ['like', 'real_name', $keyword], ['like', 'email', $keyword]]);
        }
        if (isset($params['componentCode']) &&  $params['componentCode'] == 'examination'){
            if (!empty($params['score_type']) && empty($params['userId'])) {
                $score = floatval($params['score']);
                $examinationResult = LnExaminationResultUser::find(false)
                    ->andFilterWhere(['=', 'course_id', $params['courseId']])
                    ->andFilterWhere(['=', 'mod_res_id', $modResId])
                    ->andFilterWhere(['=', 'result_type', LnExaminationResultUser::RESULT_TYPE_PROCESS])
                    ->andFilterWhere(['in', 'examination_status', [LnExaminationResultUser::EXAMINATION_STATUS_START, LnExaminationResultUser::EXAMINATION_STATUS_END]])
                    ->orderBy("created_at DESC")
                    ->select("user_id, examination_score")
                    ->asArray()
                    ->all();
                $res = array();
                if (!empty($examinationResult)) {
                    $compare = array();
                    foreach ($examinationResult as $key => $val){
                        if (!in_array($val['user_id'], $compare)){
                            $replace = false;
                            if ($params['score_type'] == '>='){
                                if ($val['examination_score'] >= $score){
                                    $replace = true;
                                }
                            } else if ($params['score_type'] == '<='){
                                if ($val['examination_score'] <= $score){
                                    $replace = true;
                                }
                            } else if ($params['score_type'] == '='){
                                if ($val['examination_score'] == $score) {
                                    $replace = true;
                                }
                            }
                            if ($replace) {
                                $res[$val['user_id']] = $val;
                            }
                            $compare[] = $val['user_id'];
                        }
                    }
                }
                if (empty($res)){
                    $query->andWhere(FwUserDisplayInfo::tableName().'.user_id is null');
                }else {
                    $res = ArrayHelper::map($res, 'user_id', 'user_id');
                    $res = array_keys($res);
                    $query->andFilterWhere(['in', LnResComplete::tableName().'.user_id', $res]);
                }
            }
        } else {
            if (!empty($params['score_type'])) {
                $score = floatval($params['score']);
                $query->andFilterWhere([$params['score_type'], LnResComplete::tableName().'.complete_score', $score]);
            }
            $query->andWhere(FwUserDisplayInfo::tableName().".user_id is not null");
        }
        $count = $query->count();
        if ($count) {
            $pages = new TPagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
            $data = $query->offset($pages->offset)
                ->limit($pages->limit)
                ->select([
                    'kid' => FwUserDisplayInfo::tableName().'.user_id',
                    FwUserDisplayInfo::tableName().'.email',
                    FwUserDisplayInfo::tableName().'.real_name',
                    FwUserDisplayInfo::tableName().'.orgnization_name',
                    FwUserDisplayInfo::tableName().'.position_name',
                    FwUserDisplayInfo::tableName().'.company_id',
                    FwUserDisplayInfo::tableName().'.mobile_no',
                    LnResComplete::tableName().'.mod_res_id',
                    LnResComplete::tableName().'.mod_id',
                    LnResComplete::tableName().'.complete_score',
                    LnResComplete::tableName().'.score_before',
                    LnResComplete::tableName().'.score_after',
                    LnResComplete::tableName().'.complete_status',
                    LnResComplete::tableName().'.end_at',
                ])
                ->asArray()
                ->all();

            $result = array(
                'pages' => $pages,
                'data' => $data,
            );
        }else{
            $result = array(
                'pages' => '',
                'data' => '',
            );
        }
        return $result;
    }

    public function getResCompleteAllData($modResId)
    {
        $query = LnResComplete::find(false);
        $result = $query->joinWith('fwUser')
            ->andFilterWhere(['=', 'mod_res_id', $modResId])
            ->andFilterWhere(['=', 'complete_type', LnResComplete::COMPLETE_TYPE_FINAL])
            ->all();

        return $result;
    }

    /**
     * 返回课程已发布课件总数
     * @param $courseId
     * @return int|string
     */
    public function getCoursePublicModResCount($courseId){
        $count = LnModRes::find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'publish_status', LnModRes::PUBLIC_STATUS_YES])
            ->count(1);

        return $count;
    }

    /**
     * @param $userId
     * @param $courseId
     */
    public function getUserModResPassedCount($userId, $courseId){
        $count = LnResComplete::find(false)
            ->andFilterWhere(['=','user_id',$userId])
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'is_passed', LnResComplete::IS_PASSED_YES])
            ->andFilterWhere(['=', 'complete_type', LnResComplete::COMPLETE_TYPE_FINAL])
            ->count(1);

        return $count;
    }
}