<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/10/2015
 * Time: 7:58 PM
 */

namespace frontend\controllers\resource;


use common\helpers\TFileModelHelper;
use common\helpers\TStringHelper;
use common\models\framework\FwDictionary;
use common\models\framework\FwUser;
use common\models\learning\LnComponent;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseactivity;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnCourseware;
use common\models\learning\LnCoursewareBook;
use common\models\learning\LnExaminationPaper;
use common\models\learning\LnExaminationPaperCopy;
use common\models\learning\LnExaminationPaperUser;
use common\models\learning\LnExaminationResultUser;
use common\models\learning\LnHomework;
use common\models\learning\LnHomeworkFile;
use common\models\learning\LnCoursewareScorm;
use common\models\learning\LnResDanmu;
use common\models\learning\LnScormScoes;
use common\models\learning\LnExamination;
use common\models\learning\LnFiles;
use common\models\learning\LnHomeworkResult;
use common\models\learning\LnModRes;
use common\services\framework\DictionaryService;
use common\services\learning\PlayerService;
use common\services\learning\ExaminationService;
use common\services\learning\FileService;
use common\services\learning\HomeworkService;
use common\services\learning\ResourceService;
use common\services\scorm\ScormScoesTrackService;
use frontend\controllers\ResourceController;
use common\models\learning\LnResComplete;
use frontend\base\BaseFrontController;
use common\services\learning\CourseCompleteService;
use common\services\learning\CourseService;
use common\services\learning\ResourceCompleteService;
use common\services\scorm\ScormScoesDataService;
use common\services\scorm\ScormScoesService;
use common\services\scorm\ScormService;
use stdClass;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use common\services\learning\InvestigationService;
use common\services\learning\TeacherManageService;
use yii\log\Logger;
use yii\web\Response;

class PlayerController extends BaseFrontController
{

    public $layout = 'frame';

    const PLAY_MODE_NORMAL = 'normal';
    const PLAY_MODE_PREVIEW = 'preview';


    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['except'] = ['scorm-player', 'aicc-player', 'pdf-player', 'audio-player', 'video-player', 'office-player', 'other-player', 'investigation-player', 'investigation-preview-player',
            'rtmp-player', 'html-courseware-player', 'html-player', 'book-player', 'examination-player', 'homework-player', 'homework-preview-player', 'content', 'flash-player', 'examination-study-player'];

        return $behaviors;
    }

    public function actionPostDanmu($courseId,$modResId)
    {
        if (!empty($modResId) && !empty($courseId)) {
            $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();

            $userId = Yii::$app->user->getId();

            $model = new LnResDanmu();
            $model->course_id = $courseId;
            $model->user_id = $userId;
            $model->mod_res_id = $modResId;
            $model->courseware_id = $modResModel->courseware_id;;
            $model->courseactivity_id = $modResModel->courseactivity_id;
            $model->resource_type = $modResModel->res_type;

            $danmuString = Yii::$app->request->post("danmu");

            $array = json_decode($danmuString);
            $model->danmu_text = $array->text;
            $model->danmu_color = $array->color;
            $model->danmu_size = $array->size;
            $model->danmu_position = $array->position;
            $model->danmu_time = $array->time;
            $model->danmu_string = $danmuString;

            $model->save();
        }
    }

    public function actionGetDanmu($courseId,$modResId)
    {
//        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = "";
        if (!empty($modResId) && !empty($courseId)) {
            $resourceService = new ResourceService();
            $danmuInfos = $resourceService->getDanmuInfo($modResId);

            $totalCount = count($danmuInfos);
            if ($totalCount > 0) {
                $result = "[";

                $currentCount = 1;
                foreach ($danmuInfos as $danmu) {
                    $result .= "'" . $danmu->danmu_string . "'";

                    if ($currentCount < $totalCount) {
                        $result .= ",";
                    }
                }

                $result .= "]";

            }
        }

//        $array = ["text"=>"test","color"=>"#ffffff","size"=>"1","position"=>"0","time"=>3];
//        $b = json_encode($array);
//        $temp = "[" . "'" . $b . "'". "]";
//        $temp = "[" . "'" . $b . "'" . ",".  "'" . $b . "'" .",".  "'" . $b . "'". "]";
        return $result;
    }
    
    public function actionContent($componentCode, $fileId, $scoId, $userId, $aiccSid = null)
    {
//        Yii::getLogger()->log("componentCode:". $componentCode, Logger::LEVEL_ERROR);
//        Yii::getLogger()->log("fileId:". $fileId, Logger::LEVEL_ERROR);
//        Yii::getLogger()->log("scoId:". $scoId, Logger::LEVEL_ERROR);
//        Yii::getLogger()->log("aiccSid:". $aiccSid, Logger::LEVEL_ERROR);

        $fileModel = LnFiles::findOne($fileId);
        $scoModel = LnScormScoes::findOne($scoId);

        $launchUrl = $scoModel->launch;

        if ($componentCode == "aicc") {
            if (!empty($launchUrl)) {
                //修正课件生成工具造成的url问题
                $launchUrl = str_replace("http://www.servername.com/presentation/", "", $launchUrl);
                $launchUrl = str_replace("http://www.servername.com/story/", "", $launchUrl);
            }
        }

        if (strpos($launchUrl, 'http://') === false && strpos($launchUrl, 'https://') === false) {
            $companyId = FwUser::findOne($userId)->company_id;
            $fileService = new FileService();
            $resoureUrl = $fileService->getResourceUrl($companyId, $componentCode);
//            if (empty($resoureUrl)) {
//                $resoureUrl = Yii::$app->request->getHostInfo() . "/";
//            }
            $iframeUrl = $resoureUrl . $fileModel->file_dir. $launchUrl;
        } else {
            $iframeUrl = $launchUrl;
        }

        if (stripos($iframeUrl, '?') !== false) {
            $connector = '&';
        } else {
            $connector = '?';
        }

        $scormScoesDataService = new ScormScoesDataService();
        $scoParameterModel = $scormScoesDataService->getScormScoesDataByName($scoModel->kid, "parameters");

        if ($componentCode == "aicc") {
            $hostUrl = Yii::$app->request->getHostInfo();
            $aiccUrl = $hostUrl . Yii::$app->urlManager->createUrl(['service/aicc-service']);
            if (!empty($scoParameterModel) && (!empty($scoParameterModel->value))) {
                $iframeUrl .= $connector . $scoParameterModel->value . '&aicc_sid=' . $aiccSid . '&aicc_url=' . $aiccUrl;
            } else {
                $iframeUrl .= $connector . 'aicc_sid=' . $aiccSid . '&aicc_url=' . $aiccUrl;
            }
        } else {
            if (!empty($scoParameterModel) && (!empty($scoParameterModel->value))) {
                $iframeUrl .= $connector . $scoParameterModel->value;
            }
        }
//        $iframeUrl = "http://demo.hpe-online.com/upload/filedir/d4/d4bb99a2307073c83d3599f16c21c2a0/res/index.html";
        return $this->render('content', [
            'iframeUrl' => str_replace("api/","",$iframeUrl)
        ]);
    }

    public function actionScormPlayer($courseId = null, $modResId = null, $scoId = null, $coursewareId = null,
                                      $courseRegId = null, $courseCompleteFinalId = null, $courseCompleteProcessId = null,
                                      $attempt = "1", $mode = PlayerService::PLAY_MODE_PREVIEW, $isMobile = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            $withSession = true;

            $componentCode = "scorm";

            $userId = Yii::$app->user->getId();
            if (empty($userId)) {
                $userId = Yii::$app->request->getQueryParam('user_id');
            }

            if ($isMobile === null) {
                if (Yii::$app->session->has("isMobile")) {
                    $isMobile = Yii::$app->session->get("isMobile");
                } else {
                    $isMobile = false;
                }
            } else {
                $isMobile == 1 ? true : false;
            }

            $withSessionStr = "False";
            if ($withSession) {
                $withSessionStr = "True";
            }

            $playerService = new PlayerService();
            $result = $playerService->getScormPlayer($courseId, $modResId, $scoId, $coursewareId,
                                   $courseRegId, $courseCompleteFinalId, $courseCompleteProcessId, $userId,
                                   $attempt, $componentCode, $mode, $isMobile, $withSession);


            if ($result['isExceedLimit']) {
                //超过尝试次数
                return $this->renderAjax('exceed-limit-player', [
                    'modResId' => $modResId,
                    'courseId' => $courseId,
                    'coursewareId' => $result['coursewareId'],
                    'attempt' => $attempt,
                    'componentCode' => $componentCode,
                    'maxAttempt' => $result['maxAttempt'],
                ]);
            }
            if ($result['isNoDisplay']){
                return $this->renderAjax('none-player', [
                    'modResId' => $modResId,
                    'mode' => $mode,
                    'courseId' => $courseId,
                    'coursewareId' => $result['coursewareId'],
                    'attempt' => $attempt,
                    'courseCompleteProcessId' => $courseCompleteProcessId,
                    'courseCompleteFinalId' => $courseCompleteFinalId,
                    'componentCode' => $componentCode,
                    'errorClient' => $result['errorClient'],
                ]);
            }

            return $this->renderAjax($componentCode . '-player', [
                'courseId' => $courseId,
                'modResId' => $modResId,
                'currentScoId' => $scoId,
                'iframeUrl' => $result['iframeUrl'],
                'mode' => $mode,
//                        'adlnav' => $adlnav,
                'scormVersion' => $result['scormVersion'],
                'coursewareId' => $result['coursewareId'],
                'def' => $result['def'],
                'cmiobj' => $result['cmiobj'],
                'cmiint' => $result['cmiint'],
                'cmicommentsuser' => $result['cmicommentsuser'],
                'cmicommentslms' => $result['cmicommentslms'],
                'cmistring256' => $result['cmistring256'],
                'cmistring4096' => $result['cmistring4096'],
                'scorm_debugging' => $result['scorm_debugging'],
                'scormAuto' => $result['scormAuto'],
                'scormAutoCommit' => $result['scormAutoCommit'],
                'scormId' => $result['scormId'],
                'attempt' => $attempt,
                'currentorg' => $result['currentorg'],
                'courseRegId' => $courseRegId,
                'scoName' => $result['scoName'],
                'courseCompleteProcessId' => $courseCompleteProcessId,
                'courseCompleteFinalId' => $courseCompleteFinalId,
                'componentCode' => $componentCode,
                'isMobile' => $isMobile,
                'userId' => $userId,
                'withSessionStr' => $withSessionStr
            ]);
        }
    }


    public function actionAiccPlayer($courseId = null, $modResId = null, $scoId = null, $coursewareId = null,
                                     $courseRegId = null, $courseCompleteFinalId = null,
                                     $courseCompleteProcessId = null, $attempt = "1",
                                     $mode = self::PLAY_MODE_PREVIEW, $isMobile = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            $withSession = true;

            $componentCode = "aicc";

            $userId = Yii::$app->user->getId();
            if (empty($userId)) {
                $userId = Yii::$app->request->getQueryParam('user_id');
            }

            if ($isMobile === null) {
                if (Yii::$app->session->has("isMobile")) {
                    $isMobile = Yii::$app->session->get("isMobile");
                } else {
                    $isMobile = false;
                }
            } else {
                $isMobile == 1 ? true : false;
            }

            $withSessionStr = "False";
            if ($withSession) {
                $withSessionStr = "True";
            }

            $playerService = new PlayerService();
            $result = $playerService->getAiccPlayer($courseId, $modResId, $scoId, $coursewareId,
                $courseRegId, $courseCompleteFinalId, $courseCompleteProcessId, $userId,
                $attempt, $componentCode, $mode, $isMobile, $withSession);


            if ($result['isExceedLimit']) {
                //超过尝试次数
                return $this->renderAjax('exceed-limit-player', [
                    'modResId' => $modResId,
                    'courseId' => $courseId,
                    'coursewareId' => $result['coursewareId'],
                    'attempt' => $attempt,
                    'componentCode' => $componentCode,
                    'maxAttempt' => $result['maxAttempt'],
                ]);
            }
            
            if ($result['isNoDisplay']){
                return $this->renderAjax('none-player', [
                    'modResId' => $modResId,
                    'mode' => $mode,
                    'courseId' => $courseId,
                    'coursewareId' => $result['coursewareId'],
                    'attempt' => $attempt,
                    'courseCompleteProcessId' => $courseCompleteProcessId,
                    'courseCompleteFinalId' => $courseCompleteFinalId,
                    'componentCode' => $componentCode,
                    'errorClient' => $result['errorClient'],
                ]);
            }

            return $this->renderAjax($componentCode . '-player', [
                'courseId' => $courseId,
                'modResId' => $modResId,
                'currentScoId' => $scoId,
                'iframeUrl' => $result['iframeUrl'],
                'mode' => $mode,
//                        'adlnav' => $adlnav,
                'scormVersion' => $result['scormVersion'],
                'coursewareId' => $result['coursewareId'],
                'def' => $result['def'],
                'cmiobj' => $result['cmiobj'],
                'cmiint' => $result['cmiint'],
                'cmicommentsuser' => $result['cmicommentsuser'],
                'cmicommentslms' => $result['cmicommentslms'],
                'cmistring256' => $result['cmistring256'],
                'cmistring4096' => $result['cmistring4096'],
                'scorm_debugging' => $result['scorm_debugging'],
                'scormAuto' => $result['scormAuto'],
                'scormAutoCommit' => $result['scormAutoCommit'],
                'scormId' => $result['scormId'],
                'attempt' => $attempt,
                'currentorg' => $result['currentorg'],
                'courseRegId' => $courseRegId,
                'scoName' => $result['scoName'],
                'courseCompleteProcessId' => $courseCompleteProcessId,
                'courseCompleteFinalId' => $courseCompleteFinalId,
                'componentCode' => $componentCode,
                'isMobile' => $isMobile,
                'currentStatus' => $result['currentStatus'],
                'currentIsResCompleteStr' => $result['currentIsResCompleteStr'],
                'userId' => $userId,
                'withSessionStr' => $withSessionStr
            ]);

        }
    }

    public function actionNonePlayer($courseId = null, $modResId = null, $coursewareId = null,
                                     $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {

            //if (!empty($modResId) || $mode == self::PLAY_MODE_PREVIEW)
            $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();
            $coursewareId = !empty($modResId) ? $modResModel->courseware_id : $coursewareId;
//        }
            $componentId = $modResModel->component_id;

            $componentCode = LnComponent::findOne($componentId)->component_code;

            return $this->renderAjax('none-player', [
                'modResId' => $modResId,
                'courseId' => $courseId,
                'mode' => $mode,
                'coursewareId' => $coursewareId,
                'attempt' => $attempt,
                'courseCompleteProcessId' => $courseCompleteProcessId,
                'courseCompleteFinalId' => $courseCompleteFinalId,
                'componentCode' => $componentCode,
                'errorClient' => 'mobile'
            ]);
        }
    }


    public function actionExceedLimitPlayer($courseId = null, $modResId = null, $coursewareId = null,
                                            $attempt = "1", $maxAttempt, $mode = self::PLAY_MODE_PREVIEW)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {

            if (!empty($modResId) || $mode == self::PLAY_MODE_PREVIEW) {
                $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();
                $coursewareId = !empty($modResId) ? $modResModel->courseware_id : $coursewareId;


                $componentId = $modResModel->component_id;

                $componentCode = LnComponent::findOne($componentId)->component_code;

                return $this->renderAjax('none-player', [
                    'modResId' => $modResId,
                    'courseId' => $courseId,
                    'coursewareId' => $coursewareId,
                    'attempt' => $attempt,
                    'componentCode' => $componentCode,
                    'maxAttempt' => $maxAttempt,
                ]);
            }
        }
    }

    public function actionPdfPlayer($courseId = null, $modResId = null, $coursewareId = null,
                                    $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW, $isMobile = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            if (!empty($modResId) || $mode == self::PLAY_MODE_PREVIEW) {
                $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();
                $coursewareId = !empty($modResId) ? $modResModel->courseware_id : $coursewareId;

                $componentCode = "pdf";

                $maxAttempt = $modResModel->max_attempt;
                if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                    //超过尝试次数
                    return $this->renderAjax('exceed-limit-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode,
                        'maxAttempt' => $maxAttempt,
                    ]);
                }

                $coursewareModel = LnCourseware::findOne($coursewareId);
                $isAllowDownload = $coursewareModel->is_allow_download == LnCourseware::ALLOW_DOWNLOAD_YES ? true : false;

                if ($isMobile === null) {
                    if (Yii::$app->session->has("isMobile")) {
                        $isMobile = Yii::$app->session->get("isMobile");
                        $browserName = Yii::$app->session->get("browserName");
                    } else {
                        $isMobile = false;
                    }
                } else {
                    $isMobile == 1 ? true : false;
                }

                if (!$isMobile && $coursewareModel->is_display_pc == LnCourseware::DISPLAY_PC_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'pc'
                    ]);
                } else if ($isMobile && $coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'mobile'
                    ]);
                } else {

                    $fileModel = LnFiles::findOne($coursewareModel->file_id);

                    $userId = Yii::$app->user->getId();
                    $companyId = FwUser::findOne($userId)->company_id;
                    $fileService = new FileService();
                    $resoureUrl = $fileService->getResourceUrl($companyId, $componentCode);
                    $tFileModel = new TFileModelHelper();
                    //$iframeUrl = $resoureUrl . $fileModel->file_path;
                    $iframeUrl = $resoureUrl . $tFileModel->secureLink($fileModel->file_path);/*防盗链*/

                    $coursewareName = $coursewareModel->courseware_name;

                    $downloadFileName = "";

                    if (!empty($coursewareName)) {
                        if (!empty($fileModel->file_extension)) {
                            $downloadFileName = $coursewareName . '.' . $fileModel->file_extension;
                        } else {
                            $downloadFileName = $coursewareName;
                        }
                    }

                    //$downloadUrl = Url::toRoute(['/resource/down', 'id' => $coursewareModel->file_id, 'file_name' => urlencode($coursewareName)]);
                    $downloadUrl = TFileModelHelper::getFileSecureLink($coursewareModel->file_id);/*防盗链*/

                    return $this->renderAjax($componentCode . '-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'iframeUrl' => $iframeUrl,
                        'downloadUrl' => $downloadUrl,
                        'downloadFileName' => $downloadFileName,
                        'mode' => $mode,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'isAllowDownload' => $isAllowDownload,
                        'componentCode' => $componentCode,
                        'file_id' => $coursewareModel->file_id,
                    ]);
                }
            }
        }
    }

    public function actionAudioPlayer($courseId = null, $modResId = null, $coursewareId = null,
                                      $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW, $isMobile = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            if (!empty($modResId) || $mode == self::PLAY_MODE_PREVIEW) {
                $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();
                $coursewareId = !empty($modResId) ? $modResModel->courseware_id : $coursewareId;

                $componentCode = "audio";

                $maxAttempt = $modResModel->max_attempt;
                if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                    //超过尝试次数
                    return $this->renderAjax('exceed-limit-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode,
                        'maxAttempt' => $maxAttempt,
                    ]);
                }

                $coursewareModel = LnCourseware::findOne($coursewareId);
                $isAllowDownload = $coursewareModel->is_allow_download == LnCourseware::ALLOW_DOWNLOAD_YES ? true : false;

                if ($isMobile === null) {
                    if (Yii::$app->session->has("isMobile")) {
                        $isMobile = Yii::$app->session->get("isMobile");
                        $browserName = Yii::$app->session->get("browserName");
                    } else {
                        $isMobile = false;
                    }
                } else {
                    $isMobile == 1 ? true : false;
                }

                if (!$isMobile && $coursewareModel->is_display_pc == LnCourseware::DISPLAY_PC_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'pc'
                    ]);
                } else if ($isMobile && $coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'mobile'
                    ]);
                } else {

                    $fileModel = LnFiles::findOne($coursewareModel->file_id);
                    $userId = Yii::$app->user->getId();
                    $companyId = FwUser::findOne($userId)->company_id;
                    $fileService = new FileService();
                    $resoureUrl = $fileService->getResourceUrl($companyId, $componentCode);

                    //$iframeUrl = $resoureUrl . $fileModel->file_path;
                    $tFileModel = new TFileModelHelper();
                    $iframeUrl = $resoureUrl . $tFileModel->secureLink($fileModel->file_path);/*防盗链*/

                    $coursewareName = $coursewareModel->courseware_name;

                    $downloadFileName = "";

                    if (!empty($coursewareName)) {
                        if (!empty($fileModel->file_extension)) {
                            $downloadFileName = $coursewareName . '.' . $fileModel->file_extension;
                        } else {
                            $downloadFileName = $coursewareName;
                        }
                    }

                    //$downloadUrl = Url::toRoute(['/resource/down', 'id' => $coursewareModel->file_id, 'file_name' => urlencode($coursewareName)]);
                    $downloadUrl = TFileModelHelper::getFileSecureLink($coursewareModel->file_id);/*防盗链*/

                    return $this->renderAjax($componentCode . '-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
//                    'fileId' => $coursewareModel->file_id,
                        'mode' => $mode,
                        'coursewareId' => $coursewareId,
                        'iframeUrl' => $iframeUrl,
                        'downloadUrl' => $downloadUrl,
                        'downloadFileName' => $downloadFileName,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'isAllowDownload' => $isAllowDownload,
                        'componentCode' => $componentCode
                    ]);
                }
            }
        }
    }



    public function actionVideoPlayer($courseId = null, $modResId = null, $coursewareId = null,
                                      $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW,$isMobile = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            if (!empty($modResId) || $mode == self::PLAY_MODE_PREVIEW) {
                $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();
                $coursewareId = !empty($modResId) ? $modResModel->courseware_id : $coursewareId;


                $componentCode = "video";

                $maxAttempt = $modResModel->max_attempt;
                if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                    //超过尝试次数
                    return $this->renderAjax('exceed-limit-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode,
                        'maxAttempt' => $maxAttempt,
                    ]);
                }

                $coursewareModel = LnCourseware::findOne($coursewareId);
                $isAllowDownload = $coursewareModel->is_allow_download == LnCourseware::ALLOW_DOWNLOAD_YES ? true : false;

                if ($isMobile === null) {
                    if (Yii::$app->session->has("isMobile")) {
                        $isMobile = Yii::$app->session->get("isMobile");
                        $browserName = Yii::$app->session->get("browserName");
                    } else {
                        $isMobile = false;
                    }
                } else {
                    $isMobile == 1 ? true : false;
                }

                if (!$isMobile && $coursewareModel->is_display_pc == LnCourseware::DISPLAY_PC_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'pc'
                    ]);
                } else if ($isMobile && $coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'mobile'
                    ]);
                } else {

                    $fileModel = LnFiles::findOne($coursewareModel->file_id);
                    $userId = Yii::$app->user->getId();
                    $companyId = FwUser::findOne($userId)->company_id;
                    $fileService = new FileService();
                    $resoureUrl = $fileService->getResourceUrl($companyId, $componentCode);
                    //$iframeUrl = $resoureUrl . $fileModel->file_path;
                    $tFileModel = new TFileModelHelper();
                    $iframeUrl = $resoureUrl . $tFileModel->secureLink($fileModel->file_path);/*防盗链*/

                    $coursewareName = $coursewareModel->courseware_name;

                    $downloadFileName = "";

                    if (!empty($coursewareName)) {
                        if (!empty($fileModel->file_extension)) {
                            $downloadFileName = $coursewareName . '.' . $fileModel->file_extension;
                        } else {
                            $downloadFileName = $coursewareName;
                        }
                    }

                    //$downloadUrl = Url::toRoute(['/resource/down', 'id' => $coursewareModel->file_id, 'file_name' => urlencode($coursewareName)]);
                    $downloadUrl = TFileModelHelper::getFileSecureLink($coursewareModel->file_id);/*防盗链*/

                    if ($fileModel->mime_type == "video/x-ms-wmv") {
                        $player = "wmv";
                    } else {
                        $player = "html5";
                    }
                    $dictionaryService = new DictionaryService();
                    $isAllowDanmu = $dictionaryService->getDictionaryValueByCode("system","is_allow_danmu");
                    if ($isAllowDanmu == null) {
                        $isAllowDanmu = FwDictionary::NO;
                    }

                    return $this->renderAjax($componentCode . '-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'iframeUrl' => $iframeUrl,
                        'downloadUrl' => $downloadUrl,
                        'downloadFileName' => $downloadFileName,
                        'player' => $player,
                        'mode' => $mode,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'isAllowDownload' => $isAllowDownload,
                        'isAllowDanmu' => $isAllowDanmu,
                        'componentCode' => $componentCode
                    ]);
                }
            }
        }
    }

    public function actionOfficePlayer($courseId = null, $modResId = null, $coursewareId = null,
                                       $courseRegId = null, $courseCompleteFinalId = null, $courseCompleteProcessId = null, 
                                       $attempt = "1", $mode = self::PLAY_MODE_PREVIEW, $isMobile = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            $withSession = true;

            $componentCode = "office";

            $userId = Yii::$app->user->getId();
            if (empty($userId)) {
                $userId = Yii::$app->request->getQueryParam('user_id');
            }

            if ($isMobile === null) {
                if (Yii::$app->session->has("isMobile")) {
                    $isMobile = Yii::$app->session->get("isMobile");
                } else {
                    $isMobile = false;
                }
            } else {
                $isMobile == 1 ? true : false;
            }

            $playerService = new PlayerService();
            $result = $playerService->getOfficePlayer($courseId, $modResId, $coursewareId,
                $courseRegId, $courseCompleteFinalId, $courseCompleteProcessId, $userId,
                $attempt, $componentCode, $mode, $isMobile, $withSession);


            if ($result['isExceedLimit']) {
                //超过尝试次数
                return $this->renderAjax('exceed-limit-player', [
                    'modResId' => $modResId,
                    'courseId' => $courseId,
                    'coursewareId' => $result['coursewareId'],
                    'attempt' => $attempt,
                    'componentCode' => $componentCode,
                    'maxAttempt' => $result['maxAttempt'],
                ]);
            }

            if ($result['isNoDisplay']){
                return $this->renderAjax('none-player', [
                    'modResId' => $modResId,
                    'mode' => $mode,
                    'courseId' => $courseId,
                    'coursewareId' => $result['coursewareId'],
                    'attempt' => $attempt,
                    'courseCompleteProcessId' => $courseCompleteProcessId,
                    'courseCompleteFinalId' => $courseCompleteFinalId,
                    'componentCode' => $componentCode,
                    'errorClient' => $result['errorClient'],
                ]);
            }

            return $this->renderAjax($componentCode . '-player', [
                'modResId' => $modResId,
                'courseId' => $courseId,
                'iframeUrl' => $result['iframeUrl'],
                'downloadFileName' => $result['downloadFileName'],
                'fileType' => $result['fileType'],
                'mode' => $mode,
                'coursewareId' => $result['coursewareId'],
                'attempt' => $attempt,
                'courseCompleteProcessId' => $courseCompleteProcessId,
                'courseCompleteFinalId' => $courseCompleteFinalId,
                'isAllowDownload' => $result['isAllowDownload'],
                'isConvertOffice' => $result['isConvertOffice'],
                'isConverted' => $result['isConverted'],
                'isConvertFailed' => $result['isConvertFailed'],
                'componentCode' => $componentCode,
                'fileId' => $result['fileId'],
            ]);
        }
    }

    public function actionFlashPlayer($courseId = null, $modResId = null, $coursewareId = null,
                                      $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW,$isMobile = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            if (!empty($modResId) || $mode == self::PLAY_MODE_PREVIEW) {
                $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();
                $coursewareId = !empty($modResId) ? $modResModel->courseware_id : $coursewareId;

                $componentCode = "flash";

                $maxAttempt = $modResModel->max_attempt;
                if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                    //超过尝试次数
                    return $this->renderAjax('exceed-limit-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode,
                        'maxAttempt' => $maxAttempt,
                    ]);
                }

                $coursewareModel = LnCourseware::findOne($coursewareId);
                $isAllowDownload = $coursewareModel->is_allow_download == LnCourseware::ALLOW_DOWNLOAD_YES ? true : false;

                if ($isMobile === null) {
                    if (Yii::$app->session->has("isMobile")) {
                        $isMobile = Yii::$app->session->get("isMobile");
                        $browserName = Yii::$app->session->get("browserName");
                    } else {
                        $isMobile = false;
                    }
                } else {
                    $isMobile == 1 ? true : false;
                }

                if (!$isMobile && $coursewareModel->is_display_pc == LnCourseware::DISPLAY_PC_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'pc'
                    ]);
                } else if ($isMobile && $coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'mobile'
                    ]);
                } else {

                    $fileModel = LnFiles::findOne($coursewareModel->file_id);

                    $userId = Yii::$app->user->getId();
                    $companyId = FwUser::findOne($userId)->company_id;
                    $fileService = new FileService();
                    $resoureUrl = $fileService->getResourceUrl($companyId, $componentCode);

                    //$iframeUrl = $resoureUrl . $fileModel->file_path;
                    $tFileModel = new TFileModelHelper();
                    $iframeUrl = $resoureUrl . $tFileModel->secureLink($fileModel->file_path);/*防盗链*/

                    $coursewareName = $coursewareModel->courseware_name;

                    //$downloadUrl = Url::toRoute(['/resource/down', 'id' => $coursewareModel->file_id, 'file_name' => urlencode($coursewareName)]);
                    $downloadUrl = TFileModelHelper::getFileSecureLink($coursewareModel->file_id);/*防盗链*/

                    if ($fileModel->file_extension == "flv") {
                        $player = "flv";
                    } else {
                        $player = "swf";
                    }


                    $downloadFileName = "";

                    if (!empty($coursewareName)) {
                        if (!empty($fileModel->file_extension)) {
                            $downloadFileName = $coursewareName . '.' . $fileModel->file_extension;
                        } else {
                            $downloadFileName = $coursewareName;
                        }
                    }

                    return $this->renderAjax($componentCode . '-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'iframeUrl' => $iframeUrl,
                        'downloadUrl' => $downloadUrl,
                        'downloadFileName' => $downloadFileName,
                        'mode' => $mode,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'player' => $player,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'isAllowDownload' => $isAllowDownload,
                        'componentCode' => $componentCode
                    ]);
                }
            }
        }
    }

    public function actionOtherPlayer($courseId = null, $modResId = null, $coursewareId = null,
                                      $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW, $isMobile = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            if (!empty($modResId) || $mode == self::PLAY_MODE_PREVIEW) {
                $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();
                $coursewareId = !empty($modResId) ? $modResModel->courseware_id : $coursewareId;


                $componentCode = "other";

                $maxAttempt = $modResModel->max_attempt;
                if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                    //超过尝试次数
                    return $this->renderAjax('exceed-limit-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode,
                        'maxAttempt' => $maxAttempt,
                    ]);
                }

                $coursewareModel = LnCourseware::findOne($coursewareId);
                $isAllowDownload = $coursewareModel->is_allow_download == LnCourseware::ALLOW_DOWNLOAD_YES ? true : false;

                if ($isMobile === null) {
                    if (Yii::$app->session->has("isMobile")) {
                        $isMobile = Yii::$app->session->get("isMobile");
                        $browserName = Yii::$app->session->get("browserName");
                    } else {
                        $isMobile = false;
                    }
                } else {
                    $isMobile == 1 ? true : false;
                }

                if (!$isMobile && $coursewareModel->is_display_pc == LnCourseware::DISPLAY_PC_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'pc'
                    ]);
                } else if ($isMobile && $coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'mobile'
                    ]);
                } else {

                    $fileModel = LnFiles::findOne($coursewareModel->file_id);

                    $coursewareName = $coursewareModel->courseware_name;

                    $downloadFileName = "";

                    if (!empty($coursewareName)) {
                        if (!empty($fileModel->file_extension)) {
                            $downloadFileName = $coursewareName . '.' . $fileModel->file_extension;
                        } else {
                            $downloadFileName = $coursewareName;
                        }
                    }

                    //$iframeUrl = Url::toRoute(['/resource/down', 'id' => $coursewareModel->file_id, 'file_name' => urlencode($coursewareName)]);
                    $iframeUrl = TFileModelHelper::getFileSecureLink($coursewareModel->file_id);/*防盗链*/

                    return $this->renderAjax($componentCode . '-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'iframeUrl' => $iframeUrl,
                        'downloadFileName' => $downloadFileName,
                        'mode' => $mode,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'isAllowDownload' => $isAllowDownload,
                        'componentCode' => $componentCode
                    ]);
                }
            }
        }
    }

    public function actionInvestigationPlayer($courseId = null, $courseRegId = null, $modResId = null, $coursewareId = null, $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW,$isMobile = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            if (!empty($modResId)) {
                $modResModel = LnModRes::findOne($modResId);
                $coursewareId = $modResModel->courseactivity_id;


                $componentCode = "investigation";

                $maxAttempt = $modResModel->max_attempt;
                if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                    //超过尝试次数
                    return $this->renderAjax('exceed-limit-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode,
                        'maxAttempt' => $maxAttempt,
                    ]);
                }

                $attempt = Yii::$app->request->getQueryParam('attempt');

                $courseactivityModel = LnCourseactivity::findOne($coursewareId);

                if ($isMobile === null) {
                    if (Yii::$app->session->has("isMobile")) {
                        $isMobile = Yii::$app->session->get("isMobile");
                        $browserName = Yii::$app->session->get("browserName");
                    } else {
                        $isMobile = false;
                    }
                } else {
                    $isMobile == 1 ? true : false;
                }

                if (!$isMobile && $courseactivityModel->is_display_pc == LnCourseactivity::DISPLAY_PC_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'pc'
                    ]);
                } else if ($isMobile && $courseactivityModel->is_display_mobile == LnCourseactivity::DISPLAY_MOBILE_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'mobile'
                    ]);
                } else {

                    $investigationService = new InvestigationService();
                    $investigation = $investigationService->getInvestigationInfoByModResId($modResId);

                    if ($investigation['investigation_type'] == InvestigationService::INVESTIGATION_TYPE_SURVEY) {
                        return $this->renderAjax('/investigation/course_play_survey', [
                            "id" => $investigation['kid'],
                            "modResId" => $modResId,
                            "courseId" => $courseId,
                            "courseCompleteFinalId" => $courseCompleteFinalId,
                            "courseCompleteProcessId" => $courseCompleteProcessId,
                            "mod_id" => $investigation['mod_id'],
                            "course_reg_id" => $courseRegId,
                            "courseactivity_id" => $investigation['courseactivity_id'],
                            "component_id" => $investigation['component_id'],
                            'componentCode' => $componentCode,
                            'attempt' => $attempt,
                            //'isTeacher'=>$isTeacher1,
                            "mode" => $mode,
                            "isMobile" => $isMobile
                        ]);
                    } else {
                        return $this->renderAjax('/investigation/course_play_vote', [
                            "id" => $investigation['kid'],
                            "modResId" => $modResId,
                            "courseId" => $courseId,
                            "courseCompleteFinalId" => $courseCompleteFinalId,
                            "courseCompleteProcessId" => $courseCompleteProcessId,
                            "mod_id" => $investigation['mod_id'],
                            "course_reg_id" => $courseRegId,
                            "courseactivity_id" => $investigation['courseactivity_id'],
                            "component_id" => $investigation['component_id'],
                            'componentCode' => $componentCode,
                            'attempt' => $attempt,
                            //'isTeacher'=>$isTeacher1,
                            "mode" => $mode,
                            "isMobile" => $isMobile
                        ]);
                    }
                }
            }
        }
    }

    public function actionInvestigationPreviewPlayer($courseId = null,$courseRegId = null,$modResId = null,$coursewareId = null,$courseCompleteFinalId=null,$courseCompleteProcessId = null,$attempt="1",$mode = self::PLAY_MODE_PREVIEW, $isMobile = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId)) ) {


            $attempt = Yii::$app->request->getQueryParam('attempt');

            if (!empty($modResId)) {
                $modResModel = LnModRes::findOne($modResId);
                $coursewareId = $modResModel->courseactivity_id;


                $componentCode = "investigation";

                $maxAttempt = $modResModel->max_attempt;
                if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                    //超过尝试次数
                    return $this->renderAjax('exceed-limit-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode,
                        'maxAttempt' => $maxAttempt,
                    ]);
                }

                $courseactivityModel = LnCourseactivity::findOne($coursewareId);

                if ($isMobile === null) {
                    if (Yii::$app->session->has("isMobile")) {
                        $isMobile = Yii::$app->session->get("isMobile");
                        $browserName = Yii::$app->session->get("browserName");
                    } else {
                        $isMobile = false;
                    }
                } else {
                    $isMobile == 1 ? true : false;
                }

                if (!$isMobile && $courseactivityModel->is_display_pc == LnCourseactivity::DISPLAY_PC_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'pc'
                    ]);
                } else if ($isMobile && $courseactivityModel->is_display_mobile == LnCourseactivity::DISPLAY_MOBILE_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'mobile'
                    ]);
                } else {

                    $investigationService = new InvestigationService();
                    $investigation = $investigationService->getInvestigationInfoByModResId($modResId);
                    if ($investigation['investigation_type'] == InvestigationService::INVESTIGATION_TYPE_SURVEY) {
                        return $this->renderAjax('/resource/player/play_survey_preview', [
                            "id" => $investigation['kid'],
                            "modResId" => $modResId,
                            "courseId" => $courseId,
                            "courseCompleteFinalId" => $courseCompleteFinalId,
                            "courseCompleteProcessId" => $courseCompleteProcessId,
                            "mod_id" => $investigation['mod_id'],
                            "course_reg_id" => $courseRegId,
                            'attempt' => $attempt,
                            "courseactivity_id" => $investigation['courseactivity_id'],
                            "component_id" => $investigation['component_id'],
                            'componentCode' => $componentCode,
                            "mode" => $mode,
                        ]);
                    } else {
                        return $this->renderAjax('/resource/player/play_vote_preview', [
                            "id" => $investigation['kid'],
                            "modResId" => $modResId,
                            "courseId" => $courseId,
                            "courseCompleteFinalId" => $courseCompleteFinalId,
                            "courseCompleteProcessId" => $courseCompleteProcessId,
                            "mod_id" => $investigation['mod_id'],
                            "course_reg_id" => $courseRegId,
                            'attempt' => $attempt,
                            "courseactivity_id" => $investigation['courseactivity_id'],
                            "component_id" => $investigation['component_id'],
                            'componentCode' => $componentCode,
                            "mode" => $mode,
                        ]);
                    }
                }
            }
        }
    }


    public function actionRtmpPlayer($courseId = null, $modResId = null, $coursewareId = null,
                                     $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW, $isMobile = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            if (!empty($modResId) || $mode == self::PLAY_MODE_PREVIEW) {
                $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();
                $coursewareId = !empty($modResId) ? $modResModel->courseware_id : $coursewareId;

                $componentCode = "rtmp";

                $maxAttempt = $modResModel->max_attempt;
                if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                    //超过尝试次数
                    return $this->renderAjax('exceed-limit-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode,
                        'maxAttempt' => $maxAttempt,
                    ]);
                }

                $coursewareModel = LnCourseware::findOne($coursewareId);
                $isAllowDownload = $coursewareModel->is_allow_download == LnCourseware::ALLOW_DOWNLOAD_YES ? true : false;

                if ($isMobile === null) {
                    if (Yii::$app->session->has("isMobile")) {
                        $isMobile = Yii::$app->session->get("isMobile");
                        $browserName = Yii::$app->session->get("browserName");
                    } else {
                        $isMobile = false;
                    }
                } else {
                    $isMobile == 1 ? true : false;
                }

                if (!$isMobile && $coursewareModel->is_display_pc == LnCourseware::DISPLAY_PC_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'pc'
                    ]);
                } else if ($isMobile && $coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'mobile'
                    ]);
                } else {


                    if (isset(Yii::$app->params['rtmp_url'])) {
                        $rtmpUrl = Yii::$app->params['rtmp_url'];
                    } else {
                        $rtmpUrl = null;
                    }

                    $fileType = $coursewareModel->courseware_type;
                    $fileUrl = $coursewareModel->embed_url;
                    $fileEmbedCode = $coursewareModel->embed_code;

                    $formatTransferStatus = LnFiles::FORMAT_TRANSFER_STATUS_COMPLETED;
                    $thumbnailUrl = null;

                    if ($fileType == LnCourseware::COURSEWARE_TYPE_LOCAL) {
                        $fileModel = LnFiles::findOne($coursewareModel->file_id);
                        if (!empty($fileModel)) {
                            $formatTransferStatus = $fileModel->format_transfer_status;
                            $thumbnailUrl = $fileModel->thumbnail_url;
                        }
                    }


                    return $this->renderAjax($componentCode . '-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'mode' => $mode,
                        'coursewareId' => $coursewareId,
                        'fileType' => $fileType,
                        'fileUrl' => $fileUrl,
                        'rtmpUrl' => $rtmpUrl,
                        'formatTransferStatus' => $formatTransferStatus,
                        'thumbnailUrl' => $thumbnailUrl,
                        'fileEmbedCode' => $fileEmbedCode,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'isAllowDownload' => $isAllowDownload,
                        'componentCode' => $componentCode
                    ]);
                }
            }
        }
    }


    public function actionHtmlCoursewarePlayer($courseId = null, $modResId = null, $scoId = null, $coursewareId = null,
                                               $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW, $isMobile = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {

            if (!empty($modResId) || $mode == self::PLAY_MODE_PREVIEW) {
                //课程模块及组件
                $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();
                $coursewareId = !empty($modResId) ? $modResModel->courseware_id : $coursewareId;

                $componentCode = "html-courseware";

                $maxAttempt = $modResModel->max_attempt;
                if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                    //超过尝试次数
                    return $this->renderAjax('exceed-limit-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode,
                        'maxAttempt' => $maxAttempt,
                    ]);
                }

                $coursewareModel = LnCourseware::findOne($coursewareId);
                $isAllowDownload = false;

                if ($isMobile === null) {
                    if (Yii::$app->session->has("isMobile")) {
                        $isMobile = Yii::$app->session->get("isMobile");
                        $browserName = Yii::$app->session->get("browserName");
                    } else {
                        $isMobile = false;
                    }
                } else {
                    $isMobile == 1 ? true : false;
                }

                if (!$isMobile && $coursewareModel->is_display_pc == LnCourseware::DISPLAY_PC_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'pc'
                    ]);
                } else if ($isMobile && $coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'mobile'
                    ]);
                } else {

                    $iframeUrl = null;
                    if (!empty($coursewareModel->file_id)) {
                        $fileModel = LnFiles::findOne($coursewareModel->file_id);

                        $userId = Yii::$app->user->getId();

                        $companyId = FwUser::findOne($userId)->company_id;
                        $fileService = new FileService();
                        $resoureUrl = $fileService->getResourceUrl($companyId, $componentCode);

                        if (!empty($coursewareModel->entrance_address)) {
                            $iframeUrl = $resoureUrl . $fileModel->file_dir . $coursewareModel->entrance_address;
                        } else {
                            $iframeUrl = $resoureUrl . $fileModel->file_dir;
                        }
                    }

                    return $this->renderAjax($componentCode . '-player', [
                        'courseId' => $courseId,
                        'modResId' => $modResId,
                        'currentScoId' => $scoId,
                        'iframeUrl' => $iframeUrl,
                        'mode' => $mode,
//                        'adlnav' => $adlnav,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode
                    ]);
                }
            }
        }
    }

    public function actionHtmlPlayer($courseId = null, $modResId = null, $scoId = null, $coursewareId = null,
                                     $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW, $isMobile = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {

            if (!empty($modResId) || $mode == self::PLAY_MODE_PREVIEW) {
                //课程模块及组件
                $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();
                $coursewareId = !empty($modResId) ? $modResModel->courseware_id : $coursewareId;


                $componentCode = "html";

                $maxAttempt = $modResModel->max_attempt;
                if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                    //超过尝试次数
                    return $this->renderAjax('exceed-limit-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode,
                        'maxAttempt' => $maxAttempt,
                    ]);
                }

                $coursewareModel = LnCourseware::findOne($coursewareId);
                $isAllowDownload = false;

                if ($isMobile === null) {
                    if (Yii::$app->session->has("isMobile")) {
                        $isMobile = Yii::$app->session->get("isMobile");
                        $browserName = Yii::$app->session->get("browserName");
                    } else {
                        $isMobile = false;
                    }
                } else {
                    $isMobile == 1 ? true : false;
                }

                if (!$isMobile && $coursewareModel->is_display_pc == LnCourseware::DISPLAY_PC_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'pc'
                    ]);
                } else if ($isMobile && $coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'mobile'
                    ]);
                } else {

                    if (!empty($coursewareModel->embed_url) && $coursewareModel->courseware_type == 1 && $coursewareModel->entry_mode == 1) {
//                    $fileModel = LnFiles::findOne($coursewareModel->file_id);
                        $iframeUrl = $coursewareModel->embed_url;
                        $type = 'url';
                    } elseif (!empty($coursewareModel->embed_code) && $coursewareModel->courseware_type == 2 && $coursewareModel->entry_mode == 1) {
                        $iframeCode = $coursewareModel->embed_code;
                        $type = 'code';
                    } elseif (!empty($coursewareModel->file_id) && $coursewareModel->courseware_type == 0 && $coursewareModel->entry_mode == 0) {
                        $fileModel = LnFiles::findOne($coursewareModel->file_id);
                        $userId = Yii::$app->user->getId();

                        $companyId = FwUser::findOne($userId)->company_id;

                        $fileService = new FileService();
                        $resoureUrl = $fileService->getResourceUrl($companyId, $componentCode);

                        $iframeUrl = $resoureUrl . $fileModel->file_path;
                        $type = 'file';
                    }
                    $display = $coursewareModel->display_position;

                    return $this->renderAjax($componentCode . '-player', [
                        'courseId' => $courseId,
                        'modResId' => $modResId,
                        'currentScoId' => $scoId,
                        'iframeUrl' => $iframeUrl,
                        'iframeCode' => $iframeCode,
                        'mode' => $mode,
                        'type' => $type,
//                        'adlnav' => $adlnav,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'display' => $display,
                        'componentCode' => $componentCode
                    ]);
                }
            }
        }
    }

    public function actionBookPlayer($courseId = null, $modResId = null, $scoId = null, $coursewareId = null,
                                          $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW, $isMobile = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {

            if (!empty($modResId) || $mode == self::PLAY_MODE_PREVIEW) {
                //课程模块及组件
                $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();
                $coursewareId = !empty($modResId) ? $modResModel->courseware_id : $coursewareId;


                $componentCode = "book";

                $maxAttempt = $modResModel->max_attempt;
                if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                    //超过尝试次数
                    return $this->renderAjax('exceed-limit-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode,
                        'maxAttempt' => $maxAttempt,
                    ]);
                }

                $coursewareModel = LnCourseware::findOne($coursewareId);
                $isAllowDownload = false;

                if ($isMobile === null) {
                    if (Yii::$app->session->has("isMobile")) {
                        $isMobile = Yii::$app->session->get("isMobile");
                        $browserName = Yii::$app->session->get("browserName");
                    } else {
                        $isMobile = false;
                    }
                } else {
                    $isMobile == 1 ? true : false;
                }

                if (!$isMobile && $coursewareModel->is_display_pc == LnCourseware::DISPLAY_PC_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'pc'
                    ]);
                } else if ($isMobile && $coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'mobile'
                    ]);
                } else {
                    $fileModel = LnCoursewareBook::findOne(['courware_id' => $coursewareModel->kid]);
                    $result = $fileModel->attributes;
                    $courseService = new CourseService();
                    $bookdata = $courseService->Getbook('isbn', $fileModel->isbn_no, null);
                    $bookresult = json_decode($bookdata, true);
                    $result['image'] = isset($bookresult['images']['large']) ? $bookresult['images']['large'] : '';
                    $result['bookurl'] = isset($bookresult['alt']) ? $bookresult['alt'] : '';

                    return $this->renderAjax($componentCode . '-player', [
                        'courseId' => $courseId,
                        'modResId' => $modResId,
                        'currentScoId' => $scoId,
                        'result' => $result,

                        'mode' => $mode,
//                        'adlnav' => $adlnav,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode
                    ]);
                }
            }
        }
    }

    /**
     * 考试学习
     * @param null $courseId
     * @param null $courseRegId
     * @param null $modResId
     * @param null $coursewareId
     * @param null $courseCompleteFinalId
     * @param null $courseCompleteProcessId
     * @param string $attempt
     * @param string $mode
     * @param null $isMobile
     * @return string
     */
    public function actionExaminationPlayer($courseId = null, $courseRegId = null, $modResId = null,$coursewareId = null,$courseCompleteFinalId=null,$courseCompleteProcessId = null,$attempt="1", $mode = self::PLAY_MODE_PREVIEW, $isMobile = null,$user_id=null,$company_id=null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId)) ) {
            $attempt = Yii::$app->request->getQueryParam('attempt');
            if (!empty($modResId)) {
                $modResModel = LnModRes::findOne($modResId);
                $coursewareId = $modResModel->courseactivity_id;

                $componentCode = "examination";

                $maxAttempt = $modResModel->max_attempt;
                if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                    //超过尝试次数
                    return $this->renderAjax('exceed-limit-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode,
                        'maxAttempt' => $maxAttempt,
                    ]);
                }

                $courseactivityModel = LnCourseactivity::findOne($coursewareId);

                if ($isMobile === null) {
                    if (Yii::$app->session->has("isMobile")) {
                        $isMobile = Yii::$app->session->get("isMobile");
                        $browserName = Yii::$app->session->get("browserName");
                    } else {
                        $isMobile = false;
                    }
                } else {
                    $isMobile == 1 ? true : false;
                }

                if (!$isMobile && $courseactivityModel->is_display_pc == LnCourseactivity::DISPLAY_PC_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'pc'
                    ]);
                } else if ($isMobile && $courseactivityModel->is_display_mobile == LnCourseactivity::DISPLAY_MOBILE_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'mobile'
                    ]);
                } else {
                    $userId = Yii::$app->user->getId();
                    $companyId = Yii::$app->user->identity->company_id;

                    if (empty($userId)) {
                        $userId = $user_id;
                    }
                    if (empty($companyId)) {
                        $companyId = $company_id;
                    }

                    $examinationService = new ExaminationService();
                    $res = $examinationService->player($userId, $companyId, $courseId, $coursewareId, $modResId, $courseRegId, $courseCompleteFinalId, $attempt, $mode);
                    if (is_array($res)) {
                        $res['modResId'] = $modResId;
                        $res['courseId'] = $courseId;
                        $res['courseCompleteFinalId'] = $courseCompleteFinalId;
                        $res['courseCompleteProcessId'] = $courseCompleteProcessId;
                        $res['course_reg_id'] = $courseRegId;
                        $res['componentCode'] = $componentCode;
                        $res['mode'] = $mode;
                    }
                    return $this->renderAjax('play_examination',$res);
                }
            }
        }
    }

    public function actionExaminationStudyPlayer($result_id = null, $examination_id = null,$modResId = null,$courseId = null,$courseRegId=null, $mod_id = null, $attempt="1", $coursewareId = null, $courseCompleteFinalId=null,$courseCompleteProcessId=null,$mode=self::PLAY_MODE_PREVIEW, $isMobile = null,$userId=null,$companyId=null){
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($examination_id)) ) {
            $attempt = Yii::$app->request->getQueryParam('attempt');
            if (!empty($modResId)) {
                $modResModel = LnModRes::findOne($modResId);
                $coursewareId = $modResModel->courseactivity_id;

                $componentCode = "examination";

                $maxAttempt = $modResModel->max_attempt;
                if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                    //超过尝试次数
                    return $this->renderAjax('exceed-limit-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode,
                        'maxAttempt' => $maxAttempt,
                    ]);
                }

                $courseactivityModel = LnCourseactivity::findOne($coursewareId);

                if ($isMobile === null) {
                    if (Yii::$app->session->has("isMobile")) {
                        $isMobile = Yii::$app->session->get("isMobile");
                        $browserName = Yii::$app->session->get("browserName");
                    } else {
                        $isMobile = false;
                    }
                } else {
                    $isMobile == 1 ? true : false;
                }

                if (!$isMobile && $courseactivityModel->is_display_pc == LnCourseactivity::DISPLAY_PC_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'pc'
                    ]);
                } else if ($isMobile && $courseactivityModel->is_display_mobile == LnCourseactivity::DISPLAY_MOBILE_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'mobile'
                    ]);
                } else {
                    /*开始学习*/
                    $examinationService = new ExaminationService();
                    $res = $examinationService->playerStudy($examination_id, $result_id, $mode);

                    return $this->renderAjax('play_examination_study', [
                        "examination_id" => $examination_id,
                        "modResId" => $modResId,
                        "courseId" => $courseId,
                        "courseCompleteFinalId" => $courseCompleteFinalId,
                        "courseCompleteProcessId" => $courseCompleteProcessId,
                        "mod_id" => $mod_id,
                        "course_reg_id" => $courseRegId,
                        'attempt' => $attempt,
                        "courseactivity_id" => $courseactivityModel->kid,
                        "component_id" => $courseactivityModel->component_id,
                        'componentCode' => $componentCode,
                        "mode" => $mode,
                        'paperQuestion' => $res['paperQuestion'],
                        'countPage' => $res['countPage'],
                        'examinationModel' => $res['examinationModel'],
                        'findResultModel' => $res['findResultModel'],
                        'findFinalResult' => $res['findFinalResult'],
                        'selectOptions' => $res['selectOptions'],
                        'selectQuestion' => $res['selectQuestion'],
                        'user_id' => $userId,
                        'company_id' => $companyId
                    ]);
                }
            }
        }
    }

    public function actionHomeworkPlayer($courseId = null, $courseRegId = null, $modResId = null, $coursewareId = null, $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW,$isMobile = null)
    {
        if(isset($_REQUEST['user_id'])){
            $user_id =$_REQUEST['user_id'];
        }else{
            $user_id = Yii::$app->user->getId();
        }

        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            if (!empty($modResId) || $mode == self::PLAY_MODE_PREVIEW) {
                $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();
                $coursewareId = !empty($modResId) ? $modResModel->courseware_id : $coursewareId;


                $componentCode = "homework";

                $maxAttempt = $modResModel->max_attempt;
                if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                    //超过尝试次数
                    return $this->renderAjax('exceed-limit-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode,
                        'maxAttempt' => $maxAttempt,
                    ]);
                }

                $attempt = Yii::$app->request->getQueryParam('attempt');

                $courseactivityModel = LnCourseactivity::findOne($coursewareId);

                if ($isMobile === null) {
                    if (Yii::$app->session->has("isMobile")) {
                        $isMobile = Yii::$app->session->get("isMobile");
                        $browserName = Yii::$app->session->get("browserName");
                    } else {
                        $isMobile = false;
                    }
                } else {
                    $isMobile == 1 ? true : false;
                }

                if (!$isMobile && $courseactivityModel->is_display_pc == LnCourseactivity::DISPLAY_PC_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'pc'
                    ]);
                } else if ($isMobile && $courseactivityModel->is_display_mobile == LnCourseactivity::DISPLAY_MOBILE_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'mobile'
                    ]);
                } else {
                    $investigationService = new InvestigationService();
                    $homework = $investigationService->getHomeworkInfoByModResId($modResId);
                    $uploadBatch = date("YmdHis");
                    $teacherfiles = array();
                    if (!empty($homework['kid'])) {
                        $teacherfiles = LnHomeworkFile::findAll(['homework_id' => $homework['kid'], 'homework_file_type' => LnHomeworkFile::FILE_TYPE_TEACHER]);
                    }
                    $resComplete = LnResComplete::find(false)->andFilterWhere(['course_complete_id' => $courseCompleteFinalId, 'course_id' => $courseId, 'user_id' => $user_id, 'mod_id' => $homework['mod_id'], 'mod_res_id' => $modResId, 'courseactivity_id' => $coursewareId, 'complete_type' => LnResComplete::COMPLETE_TYPE_FINAL])->one();
                    $resCompleteId = $resComplete->kid;
                    $view = intval($resComplete->complete_status);
                    $homeworkresult = LnHomeworkResult::find(false)->andFilterWhere(['user_id' => $user_id, 'homework_id' => $homework['kid'], 'mod_id' => $homework['mod_id'], 'mod_res_id' => $modResId])->orderBy('course_attempt_number desc,version desc')->one();

                    /*$view = $completetype;
                    if (empty($courseCompleteFinalId)) {
                        $view = 2;
                    }*/
                    //作业过期判断
                    $isOverdue=false;
                    if($homework['finish_before_at'] && time() > $homework['finish_before_at'])
                    {
                        $isOverdue=true;
                    }
                    /*计算作业上传次数*/
                    $studentfiles = array();
                    $homeworkService = new HomeworkService();
                    $courseModel = LnCourse::findOne($courseId);
                    $disabled = false;
                    $uploadBtn = true;
                    if ($courseModel->course_type == LnCourse::COURSE_TYPE_FACETOFACE){
                        $maxAttempt = !empty($homeworkresult) ? $homeworkresult->course_attempt_number : 1;
                        if (!empty($homework['kid'])) {
                            $studentfiles = $homeworkService->getHomeworkUserFile($homework['kid'], $user_id, LnHomeworkFile::FILE_TYPE_STUDENT, $courseCompleteProcessId, $maxAttempt);
                        }
                        if (!empty($homeworkresult) && !empty($courseModel)) {
                            $maxAttempt = $maxAttempt + 1;
                        }
                        if ($maxAttempt > 3) {
                            $disabled = true;
                            $uploadBtn = false;
                        }
                    }else{
                        $studentfiles = $homeworkService->getHomeworkUserFile($homework['kid'], $user_id, LnHomeworkFile::FILE_TYPE_STUDENT, $courseCompleteProcessId, false);

                        if ($view == 2){
                            $disabled = true;
                            $uploadBtn = false;
                        }

                    }

                    return $this->renderAjax('homework-player-study', [
                        'uploadBatch' => $uploadBatch,
                        "id" => $homework['kid'],
                        "modResId" => $modResId,
                        "courseId" => $courseId,
                        "courseactivityId" => $coursewareId,
                        "courseCompleteFinalId" => $courseCompleteFinalId,
                        "resCompleteId" => $resCompleteId,
                        "courseCompleteProcessId" => $courseCompleteProcessId,
                        "mod_id" => $homework['mod_id'],
                        "course_reg_id" => $courseRegId,
                        'attempt' => $attempt,
                        "courseactivity_id" => $homework['courseactivity_id'],
                        "component_id" => $homework['component_id'],
                        'result' => $homework,
                        'teacherfiles' => $teacherfiles,
                        'studentfiles' => $studentfiles,
                        'componentCode' => $componentCode,
                        "mode" => $mode,
                        'homeworkresult' => $homeworkresult,
                        "view" => $view,
                        'isOverdue'=>$isOverdue,
                        'maxAttempt' => $maxAttempt,
                        'courseModel' => $courseModel,
                        'disabled' => $disabled,
                        'uploadBtn' => $uploadBtn,
                    ]);
                }
            }
        }
    }

    public function actionHomeworkPreviewPlayer($courseId = null,$courseRegId = null,$modResId = null,$coursewareId = null,$courseCompleteFinalId=null,$courseCompleteProcessId = null,$attempt="1",$mode = self::PLAY_MODE_PREVIEW, $isMobile = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId)) ) {


            $attempt = Yii::$app->request->getQueryParam('attempt');

            if (!empty($modResId) || $mode == self::PLAY_MODE_PREVIEW) {
                $modResModel = !empty($modResId) ? LnModRes::findOne($modResId) : new LnModRes();
                $coursewareId = !empty($modResId) ? $modResModel->courseware_id : $coursewareId;


                $componentCode = "homework";

                $maxAttempt = $modResModel->max_attempt;
                if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                    //超过尝试次数
                    return $this->renderAjax('exceed-limit-player', [
                        'modResId' => $modResId,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'componentCode' => $componentCode,
                        'maxAttempt' => $maxAttempt,
                    ]);
                }

                $courseactivityModel = LnCourseactivity::findOne($coursewareId);

                if ($isMobile === null) {
                    if (Yii::$app->session->has("isMobile")) {
                        $isMobile = Yii::$app->session->get("isMobile");
                        $browserName = Yii::$app->session->get("browserName");
                    } else {
                        $isMobile = false;
                    }
                } else {
                    $isMobile == 1 ? true : false;
                }

                if (!$isMobile && $courseactivityModel->is_display_pc == LnCourseactivity::DISPLAY_PC_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'pc'
                    ]);
                } else if ($isMobile && $courseactivityModel->is_display_mobile == LnCourseactivity::DISPLAY_MOBILE_NO) {
                    return $this->renderAjax('none-player', [
                        'modResId' => $modResId,
                        'mode' => $mode,
                        'courseId' => $courseId,
                        'coursewareId' => $coursewareId,
                        'attempt' => $attempt,
                        'courseCompleteProcessId' => $courseCompleteProcessId,
                        'courseCompleteFinalId' => $courseCompleteFinalId,
                        'componentCode' => $componentCode,
                        'errorClient' => 'mobile'
                    ]);
                } else {

                    $investigationService = new InvestigationService();
                    $homework = $investigationService->getHomeworkInfoByModResId($modResId);
                    $uploadBatch = date("YmdHis");
                    $model = !empty($id) ? LnHomework::findOne($id) : new LnHomework();
                    $teacherfiles = array();
                    if (!empty($homework['kid'])) {
                        $teacherfiles = LnHomeworkFile::findAll(['homework_id' => $homework['kid'], 'homework_file_type' => '0'], false);
                    }
                    $studentfiles = array();

                    return $this->renderAjax('/resource/player/homework-player', [
                        'uploadBatch' => $uploadBatch,
                        "id" => $homework['kid'],
                        "modResId" => $modResId,
                        "courseId" => $courseId,
                        "courseCompleteFinalId" => $courseCompleteFinalId,
                        "courseCompleteProcessId" => $courseCompleteProcessId,
                        "mod_id" => $homework['mod_id'],
                        "course_reg_id" => $courseRegId,
                        'attempt' => $attempt,
                        "courseactivity_id" => $homework['courseactivity_id'],
                        "component_id" => $homework['component_id'],
                        'result' => $homework,
                        'teacherfiles' => $teacherfiles,
                        'studentfiles' => $studentfiles,
                        'componentCode' => $componentCode,
                        "mode" => $mode,
                        "view" => 0,
                    ]);
                }
            }
        }
    }

    public function actionHomeworkResultView($homeworkId, $courseId, $userId = null, $modResId = null, $companyId = null, $modId = null)
    {
        $userId = $userId ? $userId : Yii::$app->user->getId();

        $service = new HomeworkService();

        $result = $service->getUserHomeworkResult($userId, $courseId, $modId, $modResId, $companyId, $homeworkId);

        return $this->renderAjax('homework-result-view', $result);
    }
}