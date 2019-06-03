<?php

namespace frontend\controllers;

use common\models\framework\FwUser;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnCoursewareScorm;
use common\models\learning\LnScormScoes;
use common\services\framework\WechatService;
use frontend\base\BaseFrontController;
use common\services\learning\CourseCompleteService;
use common\services\learning\CourseService;
use common\services\scorm\ScormScoesDataService;
use common\services\scorm\ScormScoesTrackService;
use common\services\scorm\ScormService;
use stdClass;
use Yii;
use yii\log\Logger;
use yii\filters\AccessControl;
use yii\db;

/**
 * Site controller
 */
class ServiceController extends BaseFrontController
{
    public $layout = 'none';
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $baseBehaviors = parent::behaviors();
        $newBehaviors = [
            'access' => [
                'class' => AccessControl::className(),
                'except' => ['aicc-service','wechat-service'],
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

    public function actionAiccService(){
        $command = Yii::$app->request->getBodyParam("command");
        $sessionId = Yii::$app->request->getBodyParam("session_id");
        $aiccData = Yii::$app->request->getBodyParam("aicc_data");

        $allowAiccHacp = true;

        if (!$allowAiccHacp) {
            echo "";
            Yii::getLogger()->log("not allow AiccHacp", Logger::LEVEL_INFO);
        } else {

            $scormService = new ScormService();
            $scormScoesTrackService = new ScormScoesTrackService();
            $scormScoesDataService = new ScormScoesDataService();
            $scormSession = $scormService->scorm_aicc_confirm_hacp_session($sessionId);
            if (empty($scormSession)) {
                echo "error=3\r\nerror_text=Invalid Session ID\r\n";
                Yii::getLogger()->log("invalid hacp session", Logger::LEVEL_INFO);
            }
            else {
                header("content-type:text/html;charset=UTF-8");
                $userId = $scormSession->user_id;
                $aiccUser = FwUser::findOne($userId);
                if (empty($aiccUser)) {
                    echo "error=3\r\nerror_text=Invalid User ID\r\n";
                    Yii::getLogger()->log("invalid aicc user", Logger::LEVEL_INFO);
                } else {

                    if (!empty($command)) {
                        $command = strtolower($command);
                        $scoId = $scormSession->scorm_sco_id;
                        $scormId = $scormSession->scorm_id;
                        $courseId = $scormSession->course_id;
                        $modResId = $scormSession->mod_res_id;
                        $courseRegId = $scormSession->course_reg_id;

                        $sco = LnScormScoes::findOne($scoId);
                        $scorm = LnCoursewareScorm::findOne($scormId);


                        $mode = 'normal';
                        if (!empty($scormSession->scorm_mode)) {
                            $mode = $scormSession->scorm_mode;
                        }
                        $status = 'Not Initialized';
                        if (!empty($scormSession->scorm_status)) {
                            $status = $scormSession->scorm_status;
                        }
                        $attempt = 1;
                        if (!empty($scormSession->attempt)) {
                            $attempt = $scormSession->attempt;
                        }


                        $aiccRequest = "HACP Request:"
                            . "\r\nMOODLE scoid: " . $scoId
                            . "\r\nMOODLE mode: " . $mode
                            . "\r\nMOODLE status: " . $status
                            . "\r\nMOODLE attempt: " . $attempt
                            . "\r\nAICC sessionid: " . $sessionId
                            . "\r\nAICC command: " . $command
                            . "\r\nAICC aiccdata: "
                            . "\r\n" . $aiccData;
                        Yii::getLogger()->log($aiccRequest, Logger::LEVEL_WARNING);
                        ob_start();

                        if (!empty($scorm)) {
                            $courseService = new CourseService();
                            $courseCompleteService = new CourseCompleteService();
//                            $courseRegId = $courseService->getUserRegInfo($userId, $courseId)->kid;

                            if (!empty($courseRegId)) {
                                $courseCompleteFinalModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);

                                if (!empty($courseCompleteFinalModel)) {
                                    $courseCompleteFinalId = $courseCompleteFinalModel->kid;
                                } else {
                                    $courseCompleteFinalId = null;
                                }

                                $courseCompleteProcessModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_PROCESS);

                                if (!empty($courseCompleteProcessModel)) {
                                    $courseCompleteProcessId = $courseCompleteProcessModel->kid;
                                } else {
                                    $courseCompleteProcessId = null;
                                }
                            }
                            else {
                                $courseCompleteFinalId = null;
                                $courseCompleteProcessId = null;
                            }

                            switch ($command) {
                                case 'getparam':
                                    if ($status == 'Not Initialized') {
                                        $scormSession->scorm_status = 'Running';
                                        $status = 'Running';
                                    }
                                    if ($status != 'Running') {
                                        echo "error=101\r\nerror_text=Terminated\r\n";
                                    } else {
                                        $userdata = $scormService->scorm_get_tracks($courseRegId, $modResId, $scoId, $userId, $attempt);
                                        if (empty($userdata) || $userdata == false) {
                                            $userdata = new stdClass();
                                            $userdata->status = '';
                                            $userdata->score_raw = '';
                                        }
                                        $userdata->student_id = $userId;
                                        $realName = $aiccUser->real_name;
                                        $nickName = $aiccUser->nick_name;
                                        if (!empty($nickName)) {
                                            $studentName = $nickName;
                                        }
                                        else {
                                            $studentName = $realName;
                                        }
//                                        $a = iconv("UTF-8", "GB2312", $realName);
//                                        $b = utf8_decode($realName);
//                                        $c = mb_convert_encoding($realName, "UTF-8", "GB2312");

                                        $userdata->student_name = $studentName;
                                        $userdata->mode = $mode;
                                        if ($userdata->mode == 'normal') {
                                            $userdata->credit = 'credit';
                                        } else {
                                            $userdata->credit = 'no-credit';
                                        }

                                        if (!empty($sco)) {
                                            $userdata->course_id = $sco->identifier;
                                            $scoDatafromlmsModel = $scormScoesDataService->getScormScoesDataByName($scoId, "datafromlms");
                                            if (!empty($scoDatafromlmsModel) && (!empty($scoDatafromlmsModel->value))) {
                                                $userdata->datafromlms = $scoDatafromlmsModel->value;
                                            } else {
                                                $userdata->datafromlms = '';
                                            }

                                            $scoMasteryScoreModel = $scormScoesDataService->getScormScoesDataByName($scoId, "mastery_score");
                                            if (!empty($scoMasteryScoreModel) && (!empty($scoMasteryScoreModel->value))) {
                                                $userdata->mastery_score = is_numeric($scoMasteryScoreModel->value) ?
                                                    trim($scoMasteryScoreModel->value) : '';
                                            } else {
                                                $userdata->mastery_score = '';
                                            }

                                            $scoMaxTimeAllowedModel = $scormScoesDataService->getScormScoesDataByName($scoId, "max_time_allowed");
                                            if (!empty($scoMaxTimeAllowedModel) && (!empty($scoMaxTimeAllowedModel->value))) {
                                                $userdata->max_time_allowed = $scoMaxTimeAllowedModel->value;
                                            } else {
                                                $userdata->max_time_allowed = '';
                                            }

                                            $scoTimeLimitActionModel = $scormScoesDataService->getScormScoesDataByName($scoId, "time_limit_action");
                                            if (!empty($scoTimeLimitActionModel) && (!empty($scoTimeLimitActionModel->value))) {
                                                $userdata->time_limit_action = $scoTimeLimitActionModel->value;
                                            } else {
                                                $userdata->time_limit_action = '';
                                            }

                                            echo "error=0\r\nerror_text=Successful\r\naicc_data=";
                                            echo "[Core]\r\n";
                                            echo 'Student_ID=' . $userdata->student_id . "\r\n";
                                            echo 'Student_Name=' . $userdata->student_name . "\r\n";
                                            if (isset($userdata->{'cmi.core.lesson_location'})) {
                                                echo 'Lesson_Location=' . $userdata->{'cmi.core.lesson_location'} . "\r\n";
                                            } else {
                                                echo 'Lesson_Location=' . "\r\n";
                                            }
                                            echo 'Credit=' . $userdata->credit . "\r\n";
                                            if (isset($userdata->status)) {
                                                if ($userdata->status == '') {
                                                    $userdata->entry = ', ab-initio';
                                                } else {
                                                    if (isset($userdata->{'cmi.core.exit'}) && ($userdata->{'cmi.core.exit'} == 'suspend')) {
                                                        $userdata->entry = ', resume';
                                                    } else {
                                                        $userdata->entry = '';
                                                    }
                                                }
                                            }
                                            if (isset($userdata->{'cmi.core.lesson_status'})) {
                                                echo 'Lesson_Status=' . $userdata->{'cmi.core.lesson_status'} . $userdata->entry . "\r\n";
                                                $scormSession->lesson_status = $userdata->{'cmi.core.lesson_status'};
                                            } else {
                                                echo 'Lesson_Status=not attempted' . $userdata->entry . "\r\n";
                                                $scormSession->lesson_status = 'not attempted';
                                            }
                                            if (isset($userdata->{'cmi.core.score.raw'})) {
                                                $max = '';
                                                $min = '';
                                                if (isset($userdata->{'cmi.core.score.max'}) && !empty($userdata->{'cmi.core.score.max'})) {
                                                    $max = ', ' . $userdata->{'cmi.core.score.max'};
                                                    if (isset($userdata->{'cmi.core.score.min'}) && !empty($userdata->{'cmi.core.score.min'})) {
                                                        $min = ', ' . $userdata->{'cmi.core.score.min'};
                                                    }
                                                }
                                                echo 'Score=' . $userdata->{'cmi.core.score.raw'} . $max . $min . "\r\n";
                                            } else {
                                                echo 'Score=' . "\r\n";
                                            }
                                            if (isset($userdata->{'cmi.core.total_time'})) {
                                                echo 'Time=' . $userdata->{'cmi.core.total_time'} . "\r\n";
                                            } else {
                                                echo 'Time=' . '00:00:00' . "\r\n";
                                            }
                                            echo 'Lesson_Mode=' . $userdata->mode . "\r\n";
                                            if (isset($userdata->{'cmi.suspend_data'})) {
                                                echo "[Core_Lesson]\r\n" . rawurldecode($userdata->{'cmi.suspend_data'}) . "\r\n";
                                            } else {
                                                echo "[Core_Lesson]\r\n";
                                            }
                                            echo "[Core_Vendor]\r\n" . $userdata->datafromlms . "\r\n";
                                            echo "[Evaluation]\r\nCourse_ID = {" . $userdata->course_id . "}\r\n";
                                            echo "[Student_Data]\r\n";
                                            echo 'Mastery_Score=' . $userdata->mastery_score . "\r\n";
                                            echo 'Max_Time_Allowed=' . $userdata->max_time_allowed . "\r\n";
                                            echo 'Time_Limit_Action=' . $userdata->time_limit_action . "\r\n";
                                        } else {
                                            Yii::getLogger()->log("can not find sco", Logger::LEVEL_INFO);
                                        }
                                    }
                                    break;
                                case 'putparam':
                                    if ($status == 'Running') {
                                        if (!empty($aiccData)) {
                                            $initlessonstatus = 'not attempted';
                                            $lessonstatus = 'not attempted';
                                            if (!empty($scormSession->lesson_status)) {
                                                $initlessonstatus = $scormSession->lesson_status;
                                            }
                                            $score = '';
                                            $datamodel['lesson_location'] = 'cmi.core.lesson_location';
                                            $datamodel['lesson_status'] = 'cmi.core.lesson_status';
                                            $datamodel['score'] = 'cmi.core.score.raw';
                                            $datamodel['time'] = 'cmi.core.session_time';
                                            $datamodel['[core_lesson]'] = 'cmi.suspend_data';
                                            $datamodel['[comments]'] = 'cmi.comments';
                                            $datarows = explode("\r\n", $aiccData);
                                            reset($datarows);
                                            while ((list(, $datarow) = each($datarows)) !== false) {
                                                if (($equal = strpos($datarow, '=')) !== false) {
                                                    $element = strtolower(trim(substr($datarow, 0, $equal)));
                                                    $value = trim(substr($datarow, $equal + 1));
                                                    if (isset($datamodel[$element])) {
                                                        $element = $datamodel[$element];
                                                        switch ($element) {
                                                            case 'cmi.core.lesson_location':
                                                                $trackId = $scormScoesTrackService->insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scorm, $scoId, $userId, $element, $attempt, $value);
                                                                break;
                                                            case 'cmi.core.lesson_status':
                                                                $statuses = array(
                                                                    'passed' => 'passed',
                                                                    'completed' => 'completed',
                                                                    'failed' => 'failed',
                                                                    'incomplete' => 'incomplete',
                                                                    'browsed' => 'browsed',
                                                                    'not attempted' => 'not attempted',
                                                                    'p' => 'passed',
                                                                    'c' => 'completed',
                                                                    'f' => 'failed',
                                                                    'i' => 'incomplete',
                                                                    'b' => 'browsed',
                                                                    'n' => 'not attempted'
                                                                );
                                                                $exites = array(
                                                                    'logout' => 'logout',
                                                                    'time-out' => 'time-out',
                                                                    'suspend' => 'suspend',
                                                                    'l' => 'logout',
                                                                    't' => 'time-out',
                                                                    's' => 'suspend',
                                                                );
                                                                $values = explode(',', $value);
                                                                $value = '';
                                                                if (count($values) > 1) {
                                                                    $value = trim(strtolower($values[1]));
                                                                    $value = $value[0];
                                                                    if (isset($exites[$value])) {
                                                                        $value = $exites[$value];
                                                                    }
                                                                }
                                                                if (empty($value) || isset($exites[$value])) {
                                                                    $subelement = 'cmi.core.exit';
                                                                    $trackId = $scormScoesTrackService->insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scorm, $scoId, $userId, $subelement, $attempt, $value);
                                                                }
                                                                $value = trim(strtolower($values[0]));
                                                                $value = $value[0];
                                                                if (isset($statuses[$value]) && ($mode == 'normal')) {
                                                                    $value = $statuses[$value];
                                                                    $trackId = $scormScoesTrackService->insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scorm, $scoId, $userId, $element, $attempt, $value);
                                                                }
                                                                $lessonstatus = $value;
                                                                break;
                                                            case 'cmi.core.score.raw':
                                                                $values = explode(',', $value);
                                                                if ((count($values) > 1) && ($values[1] >= $values[0]) && is_numeric($values[1])) {
                                                                    $subelement = 'cmi.core.score.max';
                                                                    $value = trim($values[1]);
                                                                    $trackId = $scormScoesTrackService->insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scorm, $scoId, $userId, $subelement, $attempt, $value);
                                                                    if ((count($values) == 3) && ($values[2] <= $values[0]) && is_numeric($values[2])) {
                                                                        $subelement = 'cmi.core.score.min';
                                                                        $value = trim($values[2]);
                                                                        $trackId = $scormScoesTrackService->insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scorm, $scoId, $userId, $subelement, $attempt, $value);
                                                                    }
                                                                }

                                                                $value = '';
                                                                if (is_numeric($values[0])) {
                                                                    $value = trim($values[0]);
                                                                    $trackId = $scormScoesTrackService->insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scorm, $scoId, $userId, $element, $attempt, $value);
                                                                }
                                                                $score = $value;
                                                                break;
                                                            case 'cmi.core.session_time':
                                                                $scormSession->session_time = $value;
                                                                break;
                                                        }
                                                    }
                                                } else {
                                                    if (isset($datamodel[strtolower(trim($datarow))])) {
                                                        $element = $datamodel[strtolower(trim($datarow))];
                                                        $value = '';
                                                        while ((($datarow = current($datarows)) !== false) && (substr($datarow, 0, 1) != '[')) {
                                                            $value .= $datarow . "\r\n";
                                                            next($datarows);
                                                        }
                                                        $value = rawurlencode($value);
                                                        $trackId = $scormScoesTrackService->insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scorm, $scoId, $userId, $element, $attempt, $value);
                                                    }
                                                }
                                            }
                                            if (($mode == 'browse') && ($initlessonstatus == 'not attempted')) {
                                                $lessonstatus = 'browsed';
                                                $trackId = $scormScoesTrackService->insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scorm, $scoId, $userId, 'cmi.core.lesson_status', $attempt, 'browsed');
                                            }
                                            if ($mode == 'normal') {
                                                if (!empty($sco)) {

                                                    $scoMasteryScoreModel = $scormScoesDataService->getScormScoesDataByName($scoId, "mastery_score");
                                                    if (!empty($scoMasteryScoreModel) && (!empty($scoMasteryScoreModel->value))) {

                                                        if (is_numeric($scoMasteryScoreModel->value)) {
                                                            if ($score != '') { // Score is correctly initialized w/ an empty string, see above.
                                                                if ($score >= trim($scoMasteryScoreModel->value)) {
                                                                    $lessonstatus = 'passed';
                                                                } else {
                                                                    $lessonstatus = 'failed';
                                                                }
                                                            }
                                                        }

                                                    }

                                                    if (!empty($lessonstatus)) {
                                                        $trackId = $scormScoesTrackService->insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scorm, $scoId, $userId, 'cmi.core.lesson_status', $attempt, $lessonstatus);
                                                    }
                                                }
                                            }
                                        }
                                        echo "error=0\r\nerror_text=Successful\r\n";
                                    } else if ($status == 'Terminated') {
                                        echo "error=1\r\nerror_text=Terminated\r\n";
                                    } else {
                                        echo "error=1\r\nerror_text=Not Initialized\r\n";
                                    }
                                    break;
                                case 'putcomments':
                                    if ($status == 'Running') {
                                        echo "error=0\r\nerror_text=Successful\r\n";
                                    } else if ($status == 'Terminated') {
                                        echo "error=1\r\nerror_text=Terminated\r\n";
                                    } else {
                                        echo "error=1\r\nerror_text=Not Initialized\r\n";
                                    }
                                    break;
                                case 'putinteractions':
                                    if ($status == 'Running') {
                                        echo "error=0\r\nerror_text=Successful\r\n";
                                    } else if ($status == 'Terminated') {
                                        echo "error=1\r\nerror_text=Terminated\r\n";
                                    } else {
                                        echo "error=1\r\nerror_text=Not Initialized\r\n";
                                    }
                                    break;
                                case 'putobjectives':
                                    if ($status == 'Running') {
                                        echo "error=0\r\nerror_text=Successful\r\n";
                                    } else if ($status == 'Terminated') {
                                        echo "error=1\r\nerror_text=Terminated\r\n";
                                    } else {
                                        echo "error=1\r\nerror_text=Not Initialized\r\n";
                                    }
                                    break;
                                case 'putpath':
                                    if ($status == 'Running') {
                                        echo "error=0\r\nerror_text=Successful\r\n";
                                    } else if ($status == 'Terminated') {
                                        echo "error=1\r\nerror_text=Terminated\r\n";
                                    } else {
                                        echo "error=1\r\nerror_text=Not Initialized\r\n";
                                    }
                                    break;
                                case 'putperformance':
                                    if ($status == 'Running') {
                                        echo "error=0\r\nerror_text=Successful\r\n";
                                    } else if ($status == 'Terminated') {
                                        echo "error=1\r\nerror_text=Terminated\r\n";
                                    } else {
                                        echo "error=1\r\nerror_text=Not Initialized\r\n";
                                    }
                                    break;
                                case 'exitau':
                                    if ($status == 'Running') {
                                        if (!empty($scormSession->session_time)) {
                                            $scormScoesTrackService = new ScormScoesTrackService();
                                            $track = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId, $modResId, $scoId,'cmi.core.total_time', $attempt);
                                            if (!empty($track)) {
                                                // Add session_time to total_time.
                                                $value = $scormService->scorm_add_time($track->value, $scormSession->session_time);
                                                $track->value = $value;
                                                $track->save();
                                            } else {
                                                $value = $scormSession->session_time;
                                                $trackId = $scormScoesTrackService->insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scorm, $scoId, $userId, 'cmi.core.total_time', $attempt, $value);
                                            }

//                                            $scormService->scorm_update_grades($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId, $modResId, $scorm, $userId, $attempt);
                                        }
                                        $scormSession->scorm_status = 'Terminated';
                                        $scormSession->session_time = '';
                                        echo "error=0\r\nerror_text=Successful\r\n";
                                    } else if ($status == 'Terminated') {
                                        echo "error=1\r\nerror_text=Terminated\r\n";
                                    } else {
                                        echo "error=1\r\nerror_text=Not Initialized\r\n";
                                    }
                                    break;
                                default:
                                    echo "error=1\r\nerror_text=Invalid Command\r\n";
                                    break;
                            }
                        }
                    } else {
                        if (empty($command)) {
                            echo "error=1\r\nerror_text=Invalid Command\r\n";
                        } else {
                            echo "error=3\r\nerror_text=Invalid Session ID\r\n";
                        }
                    }

                    $scormSession->save();

                    $cacheResponse = ob_get_contents();

                    $aiccResponse = "HACP Response:"
                        . "\r\n" . $cacheResponse;
                    Yii::getLogger()->log($aiccResponse, Logger::LEVEL_WARNING);

                    ob_end_flush();
                }
            }
        }


    }

    public function actionWechatService($companyId){
        $wechatService = new WechatService();
        $echoStr = Yii::$app->request->getQueryParam('echostr');
        if(isset($echoStr)){
            //验证公众号令牌
            if (!empty($echoStr) && $wechatService->checkSignature($companyId)) {
                echo $echoStr;
                exit;
            }
            else {
                echo "";
                exit;
            }
        }else{
            $wechatService->responseMsg($companyId);
        }

    }
}
