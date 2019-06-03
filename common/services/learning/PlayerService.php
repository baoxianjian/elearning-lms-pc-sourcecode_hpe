<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 5/16/16
 * Time: 9:22 PM
 */

namespace common\services\learning;


use common\base\BaseService;
use common\helpers\TFileModelHelper;
use common\models\framework\FwDictionary;
use common\models\framework\FwUser;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnCourseware;
use common\models\learning\LnFiles;
use common\models\learning\LnModRes;
use common\models\learning\LnScormScoes;
use common\services\framework\DictionaryService;
use common\services\learning\CourseCompleteService;
use common\services\learning\CourseService;
use common\services\learning\FileService;
use common\services\learning\ResourceCompleteService;
use common\services\scorm\ScormScoesService;
use common\services\scorm\ScormScoesTrackService;
use common\services\scorm\ScormService;
use stdClass;
use Yii;

class PlayerService extends BaseService
{

    const PLAY_MODE_NORMAL = 'normal';
    const PLAY_MODE_PREVIEW = 'preview';

    /**
     * 获取Scorm播放器参数
     * @param string $courseId 课程ID
     * @param string $modResId 课程资源ID
     * @param string $scoId Scorm单元ID
     * @param string $coursewareId 课件ID
     * @param string $courseRegId 注册ID
     * @param string $courseCompleteFinalId 最终完成记录ID
     * @param string $courseCompleteProcessId 过程完成记录ID
     * @param string $userId 用户ID
     * @param string $attempt 尝试次数
     * @param string $componentCode 组件代码
     * @param string $mode 播放模式
     * @param bool $isMobile 是否移动端
     * @param bool $withSession 是否使用Session
     * @return array
     */
    public function getScormPlayer($courseId, $modResId, $scoId, $coursewareId,
                                   $courseRegId, $courseCompleteFinalId, $courseCompleteProcessId, $userId,
                                   $attempt = "1", $componentCode, $mode = self::PLAY_MODE_PREVIEW, $isMobile = false,
                                   $withSession = true)
    {
        $result = [];
        $result['modResId'] = $modResId;
        $result['courseId'] = $courseId;
        $result['attempt'] = $attempt;
        $result['courseId'] = $courseId;

        $result['mode'] = $mode;
        $result['courseCompleteProcessId'] = $courseCompleteProcessId;
        $result['courseCompleteFinalId'] = $courseCompleteFinalId;
        $result['courseRegId'] = $courseRegId;
        $result['scoId'] = $scoId;

        $result['isMobile'] = $isMobile;
        
        $result['isExceedLimit'] = false;
        $result['isNoDisplay'] = false;

        $scormDebugging = $this->isScormLog();

        if (!empty($modResId) || $mode == self::PLAY_MODE_PREVIEW) {
            //课程模块及组件
            $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();
            $coursewareId = !empty($modResId) ? $modResModel->courseware_id : $coursewareId;


            $maxAttempt = $modResModel->max_attempt;
            $result['maxAttempt'] = $maxAttempt;
            $result['coursewareId'] = $coursewareId;

            if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                //超过尝试次数
                $result['isExceedLimit'] = true;
                return $result;
            }

            $coursewareModel = LnCourseware::findOne($coursewareId);
            $isAllowDownload = false;


            if (!$isMobile && $coursewareModel->is_display_pc == LnCourseware::DISPLAY_PC_NO) {
                //超过尝试次数
                $result['isNoDisplay'] = true;
                $result['errorClient'] = 'pc';

                return $result;
            } else if ($isMobile && $coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
                $result['isNoDisplay'] = true;
                $result['errorClient'] = 'mobile';

                return $result;
            } else {
                $scormService = new ScormService();
                $scormModel = $scormService->getScormByCoursewareId($coursewareId);

                if ($scoId == null || $scoId == "") {
                    $scoId = $scormModel->launch_scorm_sco_id;
                }

                if (!empty($scormModel)) {

                    $scoModel = LnScormScoes::findOne($scoId);

                    $scormVersion = strtolower($scormModel->scorm_version);
                    $currentorg = $scoModel->organization;
                    $scoName = $scoModel->title;

                   


                    if ($mode == self::PLAY_MODE_NORMAL) {
//                        $courseService = new CourseService();
                        $courseCompleteService = new CourseCompleteService();

//                        $isReg = $courseService->isUserRegCourse($userId, $courseId, $courseRegId);

                        if (empty($courseCompleteFinalId) || empty($courseCompleteProcessId)) {
                            $courseCompleteProcessModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_PROCESS);
                            $courseCompleteFinalModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);

                            $courseCompleteProcessId = $courseCompleteProcessModel->kid;
                            $courseCompleteFinalId = $courseCompleteFinalModel->kid;
                        }
//                        $attempt = $scormService->scorm_get_last_attempt($courseRegId, $modResId, $userId);

                        $value = strval(time());
                        $scormScoesTrackService = new ScormScoesTrackService();
                        $scormScoesTrackService->removeTrackSessionData($courseRegId, $modResId, $scoId, $attempt);

                        $scormScoesTrackService->insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scormModel, $scoId, $userId, 'x.start.time', $attempt, $value, null, $withSession, false, null, $courseComplete, $getCertification, false);

                        $element = "cmi.core.lesson_status";
                        if ($scormVersion == ScormScoesTrackService::SCORM_13) {
                            $element = "cmi.completion_status";
                        }

                        $scormScoesTrackService->insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scormModel, $scoId, $userId, $element, $attempt, "incomplete", null, $withSession, false, null, $courseComplete, $getCertification, false);


                    } else {
                        $companyId = null;
                        $courseRegId = null;

                        $courseCompleteProcessId = "";
                        $courseCompleteFinalId = "";
                    }


//                        //老的模式
//                        $fileModel = LnFiles::findOne($coursewareModel->file_id);
//
//                        $launchUrl = $scoModel->launch;
//                        $iframeUrl = $fileModel->file_dir. $launchUrl;
//
//                        if (stripos($iframeUrl, '?') !== false) {
//                            $connector = '&';
//                        } else {
//                            $connector = '?';
//                        }
//
//                        $scormScoesDataService = new ScormScoesDataService();
//                        $scoParameterModel = $scormScoesDataService->getScormScoesDataByName($scoModel->kid, "parameters");
//
//                        if (!empty($scoParameterModel) && (!empty($scoParameterModel->value))) {
//                            $iframeUrl .= $connector . $scoParameterModel->value;
//                        }


//                        新的模式
                    $iframeUrl = Yii::$app->urlManager->createUrl(["/resource/player/content",
                        'componentCode' => $componentCode,
                        "fileId" => $coursewareModel->file_id,
                        "scoId" => $scoId,
                        "userId" => $userId]);

//                    $scoes = $scormService->scorm_get_toc_object($courseRegId, $modResId, $userId, $scormModel, $currentorg, $scoId, $mode, $attempt);
//                    $adlnav = $scormService->scorm_get_adlnav_json($scoes['scoes']);

                    $cmistring256 = '^[\\u0000-\\uFFFF]{0,255}$';
                    $cmistring4096 = '^[\\u0000-\\uFFFF]{0,4096}$';

                    $userdata = new stdClass();
                    $def = new stdClass();
                    $cmiobj = new stdClass();
                    $cmiint = new stdClass();
                    $cmicommentsuser = new stdClass();
                    $cmicommentslms = new stdClass();

                    $scormScoesService = new ScormScoesService();

                    $scoes = $scormScoesService->getScormScoesByScormId($scormModel->kid, null, "sco");

                    if (!empty($scoes)) {
                        // Drop keys so that it is a simple array.
                        //$scoes = array_values($scoes);
                        foreach ($scoes as $sco) {
                            $def->{($sco->kid)} = new stdClass();
                            $userdata->{($sco->kid)} = new stdClass();
                            $def->{($sco->kid)} = $scormService->get_scorm_default($userdata->{($sco->kid)}, $courseRegId, $modResId, $sco->kid, $attempt, $mode, $withSession);

                            // Reconstitute objectives.
                            $cmiobj->{($sco->kid)} = $scormService->scorm_reconstitute_array_element($scormVersion, $userdata->{($sco->kid)},
                                'cmi.objectives', array('score'));
                            $cmiint->{($sco->kid)} = $scormService->scorm_reconstitute_array_element($scormVersion, $userdata->{($sco->kid)},
                                'cmi.interactions', array('objectives', 'correct_responses'));

                            $cmicommentsuser->{($sco->kid)} = $scormService->scorm_reconstitute_array_element($scormVersion, $userdata->{($sco->kid)},
                                'cmi.comments_from_learner', array());
                            $cmicommentslms->{($sco->kid)} = $scormService->scorm_reconstitute_array_element($scormVersion, $userdata->{($sco->kid)},
                                'cmi.comments_from_lms', array());
                        }
                    }


                    $result['iframeUrl'] = $iframeUrl;
                    $result['scormVersion'] = $scormVersion;
                    $result['def'] = json_encode($def);
                    $result['cmiobj'] = json_encode($cmiobj);
                    $result['cmiint'] = json_encode($cmiint);
                    $result['cmicommentsuser'] = json_encode($cmicommentsuser);
                    $result['cmicommentslms'] = json_encode($cmicommentslms);
                    $result['cmistring256'] = $cmistring256;
                    $result['cmistring4096'] = $cmistring4096;
                    $result['scorm_debugging'] = $scormDebugging;

                    $result['scormAuto'] = "0";
                    $result['scormAutoCommit'] = "0";
                    $result['scormId'] = $scormModel->kid;
                    $result['currentorg'] = $currentorg;
                    $result['scoName'] = $scoName;

                    return $result;
                }
            }
        }
    }



    /**
     * 获取Aicc播放器参数
     * @param string $courseId 课程ID
     * @param string $modResId 课程资源ID
     * @param string $scoId Scorm单元ID
     * @param string $coursewareId 课件ID
     * @param string $courseRegId 注册ID
     * @param string $courseCompleteFinalId 最终完成记录ID
     * @param string $courseCompleteProcessId 过程完成记录ID
     * @param string $userId 用户ID
     * @param string $attempt 尝试次数
     * @param string $componentCode 组件代码
     * @param string $mode 播放模式
     * @param bool $isMobile 是否移动端
     * @param bool $withSession 是否使用Session
     * @return array
     */
    public function getAiccPlayer($courseId, $modResId, $scoId, $coursewareId,
                                   $courseRegId, $courseCompleteFinalId, $courseCompleteProcessId, $userId,
                                   $attempt = "1", $componentCode, $mode = self::PLAY_MODE_PREVIEW, $isMobile = false,
                                   $withSession = true)
    {
        $result = [];
        $result['modResId'] = $modResId;
        $result['courseId'] = $courseId;
        $result['attempt'] = $attempt;
        $result['courseId'] = $courseId;

        $result['mode'] = $mode;
        $result['courseCompleteProcessId'] = $courseCompleteProcessId;
        $result['courseCompleteFinalId'] = $courseCompleteFinalId;
        $result['courseRegId'] = $courseRegId;
        $result['scoId'] = $scoId;

        $result['isMobile'] = $isMobile;

        $result['isExceedLimit'] = false;
        $result['isNoDisplay'] = false;

        $scormDebugging = $this->isScormLog();

        if (!empty($modResId) || $mode == self::PLAY_MODE_PREVIEW) {
            //课程模块及组件
            $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();
            $coursewareId = !empty($modResId) ? $modResModel->courseware_id : $coursewareId;


            $maxAttempt = $modResModel->max_attempt;
            $modId = $modResModel->mod_id;
            $result['maxAttempt'] = $maxAttempt;
            $result['coursewareId'] = $coursewareId;

            if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                //超过尝试次数
                $result['isExceedLimit'] = true;
                return $result;
            }

            $coursewareModel = LnCourseware::findOne($coursewareId);
            $isAllowDownload = false;


            if (!$isMobile && $coursewareModel->is_display_pc == LnCourseware::DISPLAY_PC_NO) {
                //超过尝试次数
                $result['isNoDisplay'] = true;
                $result['errorClient'] = 'pc';

                return $result;
            } else if ($isMobile && $coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
                $result['isNoDisplay'] = true;
                $result['errorClient'] = 'mobile';

                return $result;
            } else {
                $scormService = new ScormService();
                $scormModel = $scormService->getScormByCoursewareId($coursewareId);

                if ($scoId == null || $scoId == "") {
                    $scoId = $scormModel->launch_scorm_sco_id;
                }

                if (!empty($scormModel)) {

                    $scoModel = LnScormScoes::findOne($scoId);

                    $scormVersion = strtolower($scormModel->scorm_version);
                    $currentorg = $scoModel->organization;
                    $scoName = $scoModel->title;

                    if ($mode == self::PLAY_MODE_NORMAL) {
                        $courseService = new CourseService();
                        $courseCompleteService = new CourseCompleteService();

//                        $isReg = $courseService->isUserRegCourse($userId, $courseId, $courseRegId);

                        if (empty($courseCompleteFinalId) || empty($courseCompleteProcessId)) {
                            $courseCompleteProcessModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_PROCESS);
                            $courseCompleteFinalModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);

                            $courseCompleteProcessId = $courseCompleteProcessModel->kid;
                            $courseCompleteFinalId = $courseCompleteFinalModel->kid;
                        }
//                        $attempt = $scormService->scorm_get_last_attempt($courseRegId, $modResId, $userId);

                        $value = strval(time());

                        $scormScoesTrackService = new ScormScoesTrackService();
                        $scormScoesTrackService->removeTrackSessionData($courseRegId, $modResId, $scoId, $attempt);


                        $scormScoesTrackService->insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scormModel, $scoId, $userId, 'x.start.time', $attempt, $value, null, $withSession, false, null, $courseComplete, $getCertification, false);

                        $scormScoesTrackService->insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scormModel, $scoId, $userId, 'cmi.core.lesson_status', $attempt, "incomplete", null, $withSession, false, null, $courseComplete, $getCertification, false);

                        $aiccSid = $scormService->scorm_aicc_get_hacp_session($courseRegId, $courseId, $coursewareId, $modResId, $scormModel->kid, $scoId, $modId, $userId, $attempt);

//                        Yii::getLogger()->log("aiccSid:". $aiccSid, Logger::LEVEL_ERROR);

                        
                        $element = "cmi.core.lesson_status";

                        $statusTrackInfo = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId, $modResId, $scoId, $element, $attempt, $withSession);

                        if ($statusTrackInfo == null)
                            $currentStatus = "";
                        else {
                            $currentStatus = $statusTrackInfo->value;
                        }

                        $resourceCompleteService = new ResourceCompleteService();
                        $isResComplete = $resourceCompleteService->isResComplete($courseCompleteFinalId, $modResId);
                        $currentIsResCompleteStr = $isResComplete ? "1" : "0";
                    } else {
                        $courseRegId = null;
                        $aiccSid = $scormService->scorm_aicc_get_hacp_session($courseRegId, $courseId, $coursewareId, $modResId, $scormModel->kid, $scoId, $modId, $userId, "0");
//                        $attempt = "1";

                        $courseCompleteProcessId = "";
                        $courseCompleteFinalId = "";
                        $currentStatus = "";
                        $currentIsResCompleteStr = "0";
                    }

                    if (empty($aiccSid)) {
                        $aiccSid = $scormService->random_string(20);
                    }

//                        //老的模式
//                        $fileModel = LnFiles::findOne($coursewareModel->file_id);
//
//                        $launchUrl = $scoModel->launch;
//                        $iframeUrl = $fileModel->file_dir. $launchUrl;
//
//                        if (stripos($iframeUrl, '?') !== false) {
//                            $connector = '&';
//                        } else {
//                            $connector = '?';
//                        }
//
//                        $scormScoesDataService = new ScormScoesDataService();
//                        $scoParameterModel = $scormScoesDataService->getScormScoesDataByName($scoModel->kid, "parameters");
//
//                        if (!empty($scoParameterModel) && (!empty($scoParameterModel->value))) {
//                            $iframeUrl .= $connector . $scoParameterModel->value;
//                        }


//                        新的模式
                    $iframeUrl = Yii::$app->urlManager->createUrl(["/resource/player/content",
                        'componentCode' => $componentCode,
                        "fileId" => $coursewareModel->file_id,
                        "scoId" => $scoId,
                        "userId" => $userId,
                        "aiccSid" => $aiccSid]);

//                    $scoes = $scormService->scorm_get_toc_object($courseRegId, $modResId, $userId, $scormModel, $currentorg, $scoId, $mode, $attempt);
//                    $adlnav = $scormService->scorm_get_adlnav_json($scoes['scoes']);

                    $cmistring256 = '^[\\u0000-\\uFFFF]{0,255}$';
                    $cmistring4096 = '^[\\u0000-\\uFFFF]{0,4096}$';

                    $userdata = new stdClass();
                    $def = new stdClass();
                    $cmiobj = new stdClass();
                    $cmiint = new stdClass();
                    $cmicommentsuser = new stdClass();
                    $cmicommentslms = new stdClass();

                    $scormScoesService = new ScormScoesService();

                    $scoes = $scormScoesService->getScormScoesByScormId($scormModel->kid, null, "sco");

                    if (!empty($scoes)) {
                        // Drop keys so that it is a simple array.
                        //$scoes = array_values($scoes);
                        foreach ($scoes as $sco) {
                            $def->{($sco->kid)} = new stdClass();
                            $userdata->{($sco->kid)} = new stdClass();
                            $def->{($sco->kid)} = $scormService->get_scorm_default($userdata->{($sco->kid)}, $courseRegId, $modResId, $sco->kid, $attempt, $mode, $withSession);

                            // Reconstitute objectives.
                            $cmiobj->{($sco->kid)} = $scormService->scorm_reconstitute_array_element($scormVersion, $userdata->{($sco->kid)},
                                'cmi.objectives', array('score'));
                            $cmiint->{($sco->kid)} = $scormService->scorm_reconstitute_array_element($scormVersion, $userdata->{($sco->kid)},
                                'cmi.interactions', array('objectives', 'correct_responses'));

                            $cmicommentsuser->{($sco->kid)} = $scormService->scorm_reconstitute_array_element($scormVersion, $userdata->{($sco->kid)},
                                'cmi.comments_from_learner', array());
                            $cmicommentslms->{($sco->kid)} = $scormService->scorm_reconstitute_array_element($scormVersion, $userdata->{($sco->kid)},
                                'cmi.comments_from_lms', array());
                        }
                    }


                    $result['iframeUrl'] = $iframeUrl;
                    $result['scormVersion'] = $scormVersion;
                    $result['def'] = json_encode($def);
                    $result['cmiobj'] = json_encode($cmiobj);
                    $result['cmiint'] = json_encode($cmiint);
                    $result['cmicommentsuser'] = json_encode($cmicommentsuser);
                    $result['cmicommentslms'] = json_encode($cmicommentslms);
                    $result['cmistring256'] = $cmistring256;
                    $result['cmistring4096'] = $cmistring4096;
                    $result['scorm_debugging'] = $scormDebugging;

                    $result['scormAuto'] = "0";
                    $result['scormAutoCommit'] = "0";
                    $result['scormId'] = $scormModel->kid;
                    $result['currentorg'] = $currentorg;
                    $result['scoName'] = $scoName;

                    $result['currentStatus'] = $currentStatus;
                    $result['currentIsResCompleteStr'] = $currentIsResCompleteStr;

                    return $result;
                }
            }
        }
    }


    /**
     * 获取Office播放器参数
     * @param string $courseId 课程ID
     * @param string $modResId 课程资源ID
     * @param string $coursewareId 课件ID
     * @param string $courseRegId 注册ID
     * @param string $courseCompleteFinalId 最终完成记录ID
     * @param string $courseCompleteProcessId 过程完成记录ID
     * @param string $userId 用户ID
     * @param string $attempt 尝试次数
     * @param string $componentCode 组件代码
     * @param string $mode 播放模式
     * @param bool $isMobile 是否移动端
     * @param bool $withSession 是否使用Session
     * @return array
     */
    public function getOfficePlayer($courseId, $modResId, $coursewareId,
                                    $courseRegId, $courseCompleteFinalId, $courseCompleteProcessId, $userId,
                                    $attempt = "1", $componentCode, $mode = self::PLAY_MODE_PREVIEW, $isMobile = false,
                                    $withSession = true)
    {
        $result = [];
        $result['modResId'] = $modResId;
        $result['courseId'] = $courseId;
        $result['attempt'] = $attempt;
        $result['courseId'] = $courseId;

        $result['mode'] = $mode;
        $result['courseCompleteProcessId'] = $courseCompleteProcessId;
        $result['courseCompleteFinalId'] = $courseCompleteFinalId;
        $result['courseRegId'] = $courseRegId;

        $result['isMobile'] = $isMobile;

        $result['isExceedLimit'] = false;
        $result['isNoDisplay'] = false;

        if (!empty($modResId) || $mode == self::PLAY_MODE_PREVIEW) {
            //课程模块及组件
            $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();
            $coursewareId = !empty($modResId) ? $modResModel->courseware_id : $coursewareId;


            $maxAttempt = $modResModel->max_attempt;
            $result['maxAttempt'] = $maxAttempt;
            $result['coursewareId'] = $coursewareId;

            if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                //超过尝试次数
                $result['isExceedLimit'] = true;
                return $result;
            }

            $coursewareModel = LnCourseware::findOne($coursewareId);
            $isAllowDownload = $coursewareModel->is_allow_download == LnCourseware::ALLOW_DOWNLOAD_YES ? true : false;

            $result['isAllowDownload'] = $isAllowDownload;

            if (!$isMobile && $coursewareModel->is_display_pc == LnCourseware::DISPLAY_PC_NO) {
                //超过尝试次数
                $result['isNoDisplay'] = true;
                $result['errorClient'] = 'pc';

                return $result;
            } else if ($isMobile && $coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
                $result['isNoDisplay'] = true;
                $result['errorClient'] = 'mobile';

                return $result;
            } else {

                $fileModel = LnFiles::findOne($coursewareModel->file_id);
                //$iframeUrl = $fileModel->file_path;
                $coursewareName = $coursewareModel->courseware_name;

                $downloadFileName = "";

                if (!empty($coursewareName)) {
                    if (!empty($fileModel->file_extension)) {
                        $downloadFileName = $coursewareName . '.' . $fileModel->file_extension;
                    } else {
                        $downloadFileName = $coursewareName;
                    }
                }

                $fileType = "";
                if ($fileModel->file_extension == "doc" || $fileModel->file_extension == "docx") {
                    $fileType = "word";
                } else if ($fileModel->file_extension == "xls" || $fileModel->file_extension == "xlsx") {
                    $fileType = "excel";
                } else {
                    $fileType = "ppt";
                }
                //如果是新建的Office文件，则根据系统参数判断是否要进行格式转换
                $dictionaryService = new DictionaryService();
                $isConvertOffice = $dictionaryService->getDictionaryValueByCode("system", "is_convert_office") == FwDictionary::YES ? true : false;
                $isConverted = $fileModel->format_transfer_status == LnFiles::FORMAT_TRANSFER_STATUS_COMPLETED ? true : false;
                $isConvertFailed = $fileModel->format_transfer_status == LnFiles::FORMAT_TRANSFER_STATUS_FAILED ? true : false;
                if ($isConvertOffice) {
                    if (!$isConverted) {
                        $iframeUrl = "";
                    } else {
                        $userId = Yii::$app->user->getId();
                        $companyId = FwUser::findOne($userId)->company_id;
                        $fileService = new FileService();
                        $resoureUrl = $fileService->getResourceUrl($companyId, $componentCode);

                        //$iframeUrl = $resoureUrl . $fileModel->file_path;
                        $tFileModel = new TFileModelHelper();
                        $iframeUrl = $resoureUrl . $tFileModel->secureLink($fileModel->file_path);/*防盗链*/
                    }
                } else {
                    //$iframeUrl = Yii::$app->urlManager->createUrl(['/resource/down', 'id' => $coursewareModel->file_id, 'file_name' => urlencode($coursewareName)]);
                    $iframeUrl = TFileModelHelper::getFileSecureLink($coursewareModel->file_id);/*防盗链*/
                }


                $result['iframeUrl'] = $iframeUrl;

                $result['downloadFileName'] = $downloadFileName;
                $result['fileType'] = $fileType;

                $result['isConvertOffice'] = $isConvertOffice;
                $result['isConverted'] = $isConverted;
                $result['isConvertFailed'] = $isConvertFailed;
                $result['fileId'] = $coursewareModel->file_id;

                return $result;

            }
        }

    }


    /**
     * 是否启用Scorm日志
     * @return bool
     */
    public static function isScormLog(){
        $dictionaryService = new DictionaryService();
        $isScormLog = $dictionaryService->getDictionaryValueByCode("system","is_scorm_log");
        if ($isScormLog == null) {
            return false;
        }
        else {
            if ($isScormLog == FwDictionary::YES) {
                return true;
            }
            else {
                return false;
            }
        }
    }
}