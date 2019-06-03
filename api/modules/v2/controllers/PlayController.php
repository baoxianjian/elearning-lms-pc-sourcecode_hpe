<?php
/**
 * Created by PhpStorm.
 * User: chenli
 * Date: 10/10/15
 * Time: 2:11 PM
 */

namespace api\modules\v2\controllers;


use api\base\BaseController;
use api\base\BaseOpenApiController;
use common\models\learning\LnCertification;
use common\models\learning\LnComponent;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseactivity;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnCourseware;
use common\models\learning\LnCoursewareScorm;
use common\models\learning\LnExamination;
use common\models\learning\LnHomework;
use common\models\learning\LnInvestigation;
use common\models\learning\LnModRes;
use common\models\learning\LnResComplete;
use common\models\learning\LnUserCertification;
use common\models\message\MsTimeline;
use common\services\learning\CertificationService;
use common\services\learning\RecordService;
use common\services\learning\ResourceCompleteService;
use common\services\scorm\ScormScoesService;
use common\services\scorm\ScormScoesTrackService;
use common\services\learning\CourseCompleteService;
use common\services\learning\CourseService;
use common\services\scorm\ScormService;
use common\services\message\TimelineService;
use common\services\framework\UserService;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Response;
use common\services\framework\PointRuleService;

class PlayController extends BaseController
{
    const PLAY_MODE_NORMAL = 'normal';
    const PLAY_MODE_PREVIEW = 'preview';
    const LEARNING_DURATION = "30";//太快会影响性能
    public $layout = 'frame';

    public $enableCsrfValidation = false;
    /*课件播放*/

    public function actionPlay($modResId, $scoId = null, $courseCompleteFinalId = null, $courseCompleteProcessId = null,$access_token) {
        $modResModel = LnModRes::findOne($modResId);
        $courseId = $modResModel->course_id;

        $userId = Yii::$app->user->getId();

        $courseService = new CourseService();
        $courseRegId = null;

        $isReg = $courseService->isUserRegCourse($userId, $courseId, $courseRegId);

        $courseCompleteService = new CourseCompleteService();
        if ($isReg) {
            if (!empty($courseCompleteFinalId)) {
                $courseCompleteFinalModel = LnCourseComplete::findOne($courseCompleteFinalId);
            } else {
                $courseCompleteProcessModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_PROCESS);
                $courseCompleteFinalModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);

                $courseCompleteProcessId = $courseCompleteProcessModel->kid;
                $courseCompleteFinalId = $courseCompleteFinalModel->kid;
            }
            $attempt = strval($courseCompleteFinalModel->attempt_number);

            $courseCompleteService->checkCourseStatus($courseCompleteFinalModel, $isCourseDoing, $isCourseComplete, $isCourseRetake);
        } else {
            $isCourseComplete = false;
            $isCourseDoing = false;
            $isCourseRetake = false;
            $attempt = "1";
            $courseCompleteProcessId = "";
            $courseCompleteFinalId = "";
        }

        if ((!empty($courseCompleteFinalId) && !empty($courseCompleteProcessId))
            || ($isReg && !$isCourseComplete)
        ) {
            if (!$isCourseDoing) {
                /*增加学习量*/
                LnCourse::addFieldNumber($courseId, 'learned_number');

                //添加学习记录（学习课程）
                $recordService = new RecordService();
                $recordService->addByLearningCourse($userId, $courseId);

                /*更新时间轴状态*/
                $timelineService = new TimelineService();
                $timelineService->updateButtonType($userId, $courseId, MsTimeline::OBJECT_TYPE_COURSE, MsTimeline::BUTTON_TYPE_CONTINUE);
            }

            $courseModel = LnCourse::findOne($courseId);
            $maxAttempt = $courseModel->max_attempt;
            if (($maxAttempt != 0 && intval($attempt) > $maxAttempt) || $isCourseComplete) {
                //超过课程最大尝试次数
                $this->redirect(Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $courseId]));
            }
            $courseName = $courseModel->course_name;
            $modType = $courseModel->mod_type;
            $courseType = $courseModel->course_type;
            $componentId = $modResModel->component_id;
            $compoentModel = LnComponent::findOne($componentId);
            $componentCode = $compoentModel->component_code;

            if ($modResModel->res_type == LnModRes::RES_TYPE_COURSEWARE) {
                $courseWareId = $modResModel->courseware_id;
                $model = LnCourseware::findOne($courseWareId);
                $itemName = $model->courseware_name;

                $scormService = new ScormService();
                if ($scormService->isScormComponent($componentCode) && empty($scoId)) {

                    $scorm = $scormService->getScormByCoursewareId($courseWareId);
                    $scormScoesService = new ScormScoesService();
                    $scormScoesTrackService = new ScormScoesTrackService();
                    $scoes = $scormScoesService->getScormScoesByScormId($scorm->kid, null, "sco");

                    $scoId = "";
                    
                    foreach ($scoes as $sco) {
                        $scormScoId = $sco->kid;
                        if (empty($scoId)) {
                            $result = $scormScoesTrackService->checkIsScormScoesCompletedByAttempt($courseRegId, $modResId, $scormScoId, $attempt, $scorm, true);
                            if (!$result) {
                                //没通过的SCO才需要再学习
                                $scoId = $scormScoId;
                            }
                        }
                    }

                    if (empty($scoId)) {
                        $scoId = $scorm->launch_scorm_sco_id;
                    }

                }
            } else {
                $courseActivityId = $modResModel->courseactivity_id;
                $activityModel = LnCourseactivity::findOne($courseActivityId);
                if ($componentCode == "investigation") {
                    $model = LnInvestigation::findOne($activityModel->object_id);
                    $itemName = $model->title;
                } else if ($componentCode == "homework") {
                    $model = LnHomework::findOne($activityModel->object_id);
                    $itemName = $model->title;
                } else if ($componentCode == "examination") {
                    $model = LnExamination::findOne($activityModel->object_id);
                    $itemName = $model->title;
                } else {
                    $model = null;
                    $itemName = null;
                }
            }

            $iframe = Yii::$app->request->get('iframe');
            $resultUserId = Yii::$app->request->get('resultUserId');

            $resourceCompleteService = new ResourceCompleteService();
            $resourceCompleteService->checkResourceStatus($courseCompleteFinalId, $modResId, $learning, $learned);

            return $this->render('play', [
                'modResId' => $modResId,
                'courseName' => $courseName,
                'itemName' => $itemName,
                'courseId' => $courseId,
                'componentCode' => $componentCode,
                'model' => $model,
                'courseRegId' => $courseRegId,
                'scoId' => $scoId,
                'userId' => $userId,
                'attempt' => $attempt,
                'courseCompleteFinalId' => $courseCompleteFinalId,
                'courseCompleteProcessId' => $courseCompleteProcessId,
                'modType' => $modType,
                'courseType' => $courseType,
                'duration' => self::LEARNING_DURATION,
                'iframe' => $iframe,
                'resultUserId' => $resultUserId,
                'currentCoursewareStatus' => $learned
            ]);
        } else {
            return $this->redirect(['view', 'id' => $courseId]);
        }
    }

    public function actionPlayPreview($modResId,$courseRegId=null,$scoId=null,$access_token=null,$supportEncryptPdfVer=null/* 用于android手机低版本不兼容pdf.js流方式打开文件 */)
    {

        $modResModel = LnModRes::findOne($modResId);
        $courseId = $modResModel->course_id; //课程ID
        $coursewareId = $modResModel->courseware_id;
        $userId = $this->user->kid;
        $currentTime = time();
        $courseService = new CourseService();
        $courseCompleteService = new CourseCompleteService();
        //判断用户是否已经注册课程
        $isReg = $courseService->isUserRegCourse($userId, $courseId, $courseRegId,false);

        if($isReg){
//            var_dump("课程已经注册");
            if (!empty($courseCompleteFinalId)) {
                $courseCompleteFinalModel = LnCourseComplete::findOne($courseCompleteFinalId);
            }
            else {
//                var_dump("没有获取到courseCompleteFinalId");
                $courseCompleteProcessModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_PROCESS);
                $courseCompleteFinalModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);

                $courseCompleteProcessId = $courseCompleteProcessModel->kid;
                $courseCompleteFinalId = $courseCompleteFinalModel->kid;

//                var_dump("获取到courseCompleteProcessId:",$courseCompleteProcessId);
//                var_dump("获取到courseCompleteFinalId:",$courseCompleteFinalId);
            }

            $attempt = strval($courseCompleteFinalModel->attempt_number);
            $courseCompleteService->checkCourseStatus($courseCompleteFinalModel, $isCourseDoing, $isCourseComplete, $isCourseRetake);

        }else{
//            var_dump("课程没有注册");
            $isCourseComplete = false;
            $isCourseDoing = false;
            $attempt = "1";
            $courseCompleteProcessId = "";
            $courseCompleteFinalId = "";
        }



        if ((!empty($courseCompleteFinalId) && !empty($courseCompleteProcessId))
            || ($isReg && !$isCourseComplete)) {
            if (!$isCourseDoing) {
                /*增加学习量*/
                LnCourse::addFieldNumber($courseId, 'learned_number');

                //添加学习记录（学习课程）
                $recordService = new RecordService();
                $recordService->addByLearningCourse($userId, $courseId);

                /*更新时间轴状态*/
                $timelineService = new TimelineService();
                $timelineService->updateButtonType($userId,$courseId,MsTimeline::OBJECT_TYPE_COURSE,MsTimeline::BUTTON_TYPE_CONTINUE);
            }

            $courseModel = LnCourse::findOne($courseId);
            $maxAttempt = $courseModel->max_attempt;
            if (($maxAttempt != 0 && intval($attempt) > $maxAttempt) || $isCourseComplete) {
                return;
                //超过课程最大尝试次数
//                $this->redirect(Yii::$app->urlManager->createUrl(['resource/course/view','id'=>$courseId]));
            }

            $courseName = $courseModel->course_name;
            $modType = $courseModel->mod_type;
            $courseType = $courseModel->course_type;
            $componentId = $modResModel->component_id;
            $compoentModel = LnComponent::findOne($componentId);
            $componentCode = $compoentModel->component_code;

            if ($modResModel->res_type == LnModRes::RES_TYPE_COURSEWARE) {
                $courseWareId = $modResModel->courseware_id;
                $model = LnCourseware::findOne($courseWareId);
                $itemName = $model->courseware_name;

                $scormService = new ScormService();
                if ($scormService->isScormComponent($componentCode) && empty($scoId)) {

                    $scorm = $scormService->getScormByCoursewareId($courseWareId);
                    $scormScoesService = new ScormScoesService();
                    $scormScoesTrackService = new ScormScoesTrackService();
                    $scoes = $scormScoesService->getScormScoesByScormId($scorm->kid, null, "sco");

                    $scoId = "";


                    foreach ($scoes as $sco) {
                        $scormScoId = $sco->kid;

                        if (empty($scoId)) {
                            $result = $scormScoesTrackService->checkIsScormScoesCompletedByAttempt($courseRegId, $modResId, $scormScoId, $attempt, $scorm);
                            if (!$result) {
                                //没通过的SCO才需要再学习
                                $scoId = $scormScoId;
                            }
                        }
                    }

                    if (empty($scoId)) {
                        $scoId = $scorm->launch_scorm_sco_id;
                    }

                }
            }
            else {
                $courseActivityId = $modResModel->courseactivity_id;
                $activityModel = LnCourseactivity::findOne($courseActivityId);
                if ($componentCode == "investigation") {
                    $model = LnInvestigation::findOne($activityModel->object_id);
                    $itemName = $model->title;
                }
                else if ($componentCode == "homework") {
                    $model = LnHomework::findOne($activityModel->object_id);
                    $itemName = $model->title;
                }
                else if ($componentCode == "examination") {
                    $model = LnExamination::findOne($activityModel->object_id);
                    $itemName = $model->title;
                }
                else {
                    $model = null;
                    $itemName = null;
                }
            }



            if ((!empty($courseCompleteFinalId) && !empty($courseCompleteProcessId)) || !$courseCompleteService->isCourseComplete($courseRegId))
            {
                //初次打开页面，课程进入学习中状态
                $courseCompleteService->changeCourseCompleteStatusToDoing($courseCompleteProcessModel, $currentTime);
                $courseCompleteService->changeCourseCompleteStatusToDoing($courseCompleteFinalModel, $currentTime);

                $resourceCompleteService = new ResourceCompleteService();

                $finalModel = $resourceCompleteService->checkResourceStatus($courseCompleteFinalId, $modResId, $isDoing, $isComplete);

                if (!$isComplete) {
                    $processModel = $resourceCompleteService->getLastResCompleteNonDoneInfo($courseRegId,
                        $modResId, LnResComplete::COMPLETE_TYPE_PROCESS, $courseCompleteProcessId);


                    if (empty($processModel)) {
                        $resCompleteProcessId = $resourceCompleteService->addResCompleteDoingInfo($courseCompleteProcessId, $courseRegId, $modResId, LnResComplete::COMPLETE_TYPE_PROCESS,$this->systemKey);//创建过程记录（进行中）
                    } else {
                        $resCompleteProcessId = $processModel->kid;
                    }

//                    $finalModel = $resourceCompleteService->getLastResCompleteNonDoneInfo($courseRegId,
//                        $modResId, LnResComplete::COMPLETE_TYPE_FINAL, $courseCompleteFinalId);
                    if (empty($finalModel)) {
                        $resCompleteFinalId = $resourceCompleteService->addResCompleteDoingInfo($courseCompleteFinalId, $courseRegId, $modResId, LnResComplete::COMPLETE_TYPE_FINAL,$this->systemKey);//创建最终记录（进行中）
                    } else {
                        $resCompleteFinalId = $finalModel->kid;
//                        $resourceCompleteService->setLastRecordAt($resCompleteFinalId, $currentTime, $duration);
                    }
                    //对于完成规则为浏览即完成，打开页面自动完成
                    if ($modResModel->complete_rule == LnModRes::COMPLETE_RULE_BROWSE) {

                        if (empty($coursewareId)) {
                            $coursewareId = $modResModel->courseware_id;
                        }
                        $coursewareModel = LnCourseware::findOne($coursewareId);
                        if ($coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_YES)
                         {
                            $courseComplete=false;
                            $getCetification=false;
                            $courseId=null;
                            $certificationId=null;
                            //PC端可见，就必须要是PC端访问才行；移动端可见，就必须要是移动端访问才行
                            $resourceCompleteService->addResCompleteDoneInfo($courseCompleteProcessId, $courseRegId, $modResId, LnResComplete::COMPLETE_TYPE_PROCESS,null,null,false,$this->systemKey);//创建过程记录（完成）
                            $resourceCompleteService->addResCompleteDoneInfo($courseCompleteFinalId, $courseRegId, $modResId, LnResComplete::COMPLETE_TYPE_FINAL,null,null,false,$this->systemKey,true,false,$courseComplete,$getCetification,$courseId,$certificationId);//创建最终记录（完成）
                            
                            //课程完成增加积分
                            $pointRuleService=new PointRuleService();
                            $pointResult=$pointRuleService->countCourseAndCetificationPoint($courseComplete,$getCetification,$courseId,$certificationId);
                        }
                    }
                }
                else {
                    //这里提供CompleteId主要是为了能继续记录学习时长
                    $processModel = $resourceCompleteService->getLastResCompleteInfo($courseRegId,
                        $modResId, LnResComplete::COMPLETE_TYPE_PROCESS, $courseCompleteProcessId);
                    if (!empty($processModel)) {
                        $resCompleteProcessId = $processModel->kid;
                    }
                    else {
                        $resCompleteProcessId = "";
                    }

                    $finalModel = $resourceCompleteService->getLastResCompleteInfo($courseRegId,
                        $modResId, LnResComplete::COMPLETE_TYPE_FINAL, $courseCompleteFinalId);

                    if (!empty($finalModel)) {
                        $resCompleteFinalId = $finalModel->kid;
                    }
                    else {
                        $resCompleteFinalId = "";
                    }
                }
            }

            $iframe = Yii::$app->request->get('iframe');
            $resultUserId = Yii::$app->request->get('resultUserId');

            return $this->render('play-preview', [
                'modResId' => $modResId,
                'courseName' => $courseName,
                'itemName' => $itemName,
                'courseId' => $courseId,
                'componentCode' => $componentCode,
                'model' => $model,
                'courseRegId'=>$courseRegId,
                'scoId' => $scoId,
                'attempt' => $attempt,
                'courseCompleteFinalId' => $courseCompleteFinalId,
                'courseCompleteProcessId' => $courseCompleteProcessId,
                'modType'=>$modType,
                'courseType' => $courseType,
                'isMobile' => 1,
                'system_key'=>$this->systemKey,
                'access_token'=>$access_token,
                'iframe' => $iframe,
                'resultUserId' => $resultUserId,
                'supportEncryptPdfVer' => $supportEncryptPdfVer,
                'coursewareId' => $coursewareId
            ]);

        } else {
            return $this->redirect(['view', 'id' => $courseId]);
        }

    }


    public function actionCertificationPreview($id)
    {
        $this->layout = 'none';
        $service = new CertificationService();
        $model = LnUserCertification::findOne($id);

        $template = LnCertification::findOne($model->certification_id);
        if (!empty($template)) {
            $printOrientation = $template->print_orientation;
        }
        if ($model->status == LnUserCertification::STATUS_FLAG_STOP) {
            $message = "您的这张证书已经被取消";
            $html = null;
        } else {

            $html = $service->GetUserCertificationContent($model);
            if (empty($html)) {
                $message = "证书模板已经被删除，无法生成证书";
            } else {
                $message = null;
            }
        }
        return $this->render('certification-preview', [
            'message' => $message,
            'html' => $html,
            'printOrientation' => $printOrientation,
        ]);
    }


    public function actionRecordData($mode = self::PLAY_MODE_PREVIEW, $courseCompleteProcessId, $courseCompleteFinalId, $resCompleteProcessId, $resCompleteFinalId,$access_token){

        $this->enableCsrfValidation = false;
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $currentTime = time();
            $duration = $this::LEARNING_DURATION;

            $commonUserService = new UserService();
            $commonUserService->keepOnline(Yii::$app->user->getId());

            if ($mode == self::PLAY_MODE_NORMAL) {
                if (!empty($courseCompleteProcessId) && !empty($courseCompleteFinalId)) {
                    $courseCompleteService = new CourseCompleteService();

                    $courseCompleteService->setLastRecordAt($courseCompleteProcessId, $currentTime, $duration);
                    $courseCompleteService->setLastRecordAt($courseCompleteFinalId, $currentTime, $duration);
                }

                if (!empty($resCompleteProcessId) && !empty($resCompleteFinalId)) {
                    $resourceCompleteService = new ResourceCompleteService();
                    $resourceCompleteService->setLastRecordAt($resCompleteProcessId, $currentTime, $duration);
                    $resourceCompleteService->setLastRecordAt($resCompleteFinalId, $currentTime, $duration);
                }

                return ['result' => 'success'];
            } else {
                return ['result' => 'success'];
            }
        } else {
            return ['result' => 'success'];
        }
    }

    public function actionRecordScormData($courseRegId, $modResId, $scoId, $coursewareId, $attempt, $courseCompleteProcessId, $courseCompleteFinalId, $userId, $withSessionStr,$access_token)
    {
        if (Yii::$app->request->isPost && !empty($courseRegId) && !empty($modResId)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $withSession = $withSessionStr == "True" ? true : false;
            $currentSessionKey = Yii::$app->session->getId();
            $sessionKey = $currentSessionKey; //sessionKey是用来判断发送成绩信息的人和当前用户是不是同一个人，避免作弊。（为了测试方便，暂时屏蔽此功能）
            if ($currentSessionKey == $sessionKey) {

                $scormScoesTrackService = new ScormScoesTrackService();
                $scormService = new ScormService();
                $scorm = $scormService->getScormByCoursewareId($coursewareId);
                $trackdata = $scormScoesTrackService->getScoesTrackResultByAttempt($courseRegId, $modResId, $scoId, $attempt, $withSession);
                $postData = Yii::$app->request->getBodyParams();

                $request = "";
                $courseComplete = false;
                $getCetification = false;
                $courseId = null;
                $certificationId = null;
                if (!empty($postData) && !empty($postData['datalist']) && count($postData['datalist']) > 0) {
                    $scormScoesTrackService->batchInsertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scorm, $scoId, $userId, $postData['datalist'], $attempt, $trackdata, $withSession, null, $courseComplete, $getCetification, $courseId, $certificationId);
                }

                $pointRuleService = new PointRuleService();
                $pointResult = $pointRuleService->countCourseAndCetificationPoint($courseComplete, $getCetification, $courseId, $certificationId);

                return ['result' => 'true', 'message' => "", 'request' => $request, 'pointResult' => $pointResult];
            } else {
                return ['result' => 'false', 'message' => "0", 'request' => "", 'show_point' => 0];
            }
        }
    }

    public function actionGetScormStatus($courseCompleteFinalId, $courseRegId, $modResId, $userId, $scoId, $attempt,$access_token)
    {
        $this->enableCsrfValidation = false;
        if (Yii::$app->request->isPost && !empty($courseRegId) && !empty($modResId)) {
            $withSession = true;
            Yii::$app->response->format = Response::FORMAT_JSON;
            $scormId = \common\models\learning\LnScormScoes::findOne($scoId)->scorm_id;
            $scorm = LnCoursewareScorm::findOne($scormId);
            $scormScoesTrackService = new ScormScoesTrackService();

            $element = "cmi.core.lesson_status";
            if ($scorm->scorm_version == ScormScoesTrackService::SCORM_13) {
                $element = "cmi.completion_status";
            }
            $track = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId, $modResId, $scoId, $element, $attempt, $withSession);

            if ($track == null)
                $status = "";
            else {
                $status = $track->value;
            }

            $resourceCompleteService = new ResourceCompleteService();
            $isResComplete = $resourceCompleteService->isResComplete($courseCompleteFinalId, $modResId);
            $isResCompleteStr = $isResComplete ? "1" : "0";

            return ['status' => $status,
                'isResCompleteStr' => $isResCompleteStr];
        }
    }
}