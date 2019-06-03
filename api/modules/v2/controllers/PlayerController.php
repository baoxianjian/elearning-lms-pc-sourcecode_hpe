<?php
/**
 * Created by PhpStorm.
 * User: chenli
 * Date: 16/1/19
 * Time: 上午10:07
 */

namespace api\modules\v2\controllers;



use api\base\BaseController;
use common\models\framework\FwDictionary;
use common\models\learning\LnCourseactivity;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnCourseware;
use common\models\learning\LnCoursewareBook;
use common\models\learning\LnScormScoes;
use common\models\learning\LnExamination;
use common\models\learning\LnExaminationResultUser;
use common\models\learning\LnFiles;
use common\models\learning\LnHomework;
use common\models\learning\LnHomeworkFile;
use common\models\learning\LnHomeworkResult;
use common\models\learning\LnModRes;
use common\models\learning\LnResComplete;
use common\services\learning\CourseCompleteService;
use common\services\learning\CourseService;
use common\services\framework\DictionaryService;
use common\services\learning\ExaminationService;
use common\services\learning\InvestigationService;
use common\services\learning\ResourceCompleteService;
use common\services\scorm\ScormScoesDataService;
use common\services\scorm\ScormScoesService;
use common\services\scorm\ScormScoesTrackService;
use common\services\scorm\ScormService;
use common\crpty\AES;
use common\crpty\CryptErrorCode;
use stdClass;
use Yii;
use yii\helpers\Url;
use common\services\learning\PlayerService;

class PlayerController extends  BaseController
{

    public $layout = 'frame';
    const PLAY_MODE_NORMAL = 'normal';
    const PLAY_MODE_PREVIEW = 'preview';


    public function actionContent($componentCode,$fileId,$scoId,$aiccSid = null){

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

        if (strpos($launchUrl, 'http://') === false && strpos($launchUrl, 'https://') === false)
        {
            $iframeUrl = $fileModel->file_dir . $launchUrl;
        }
        else {
            $iframeUrl = $launchUrl;
        }

        if (stripos($iframeUrl, '?') !== false) {
            $connector = '&';
        } else {
            $connector = '?';
        }

        $scormScoesDataService = new ScormScoesDataService();
        $scoParameterModel = $scormScoesDataService->getScormScoesDataByName($scoModel->kid,"parameters");

        if ($componentCode == "aicc") {
            $hostUrl = Yii::$app->request->getHostInfo();
            $aiccUrl = $hostUrl . Yii::$app->urlManager->createUrl(['service/aicc-service']);
            if (!empty($scoParameterModel) && (!empty($scoParameterModel->value))) {
                $iframeUrl .= $connector . $scoParameterModel->value . '&aicc_sid=' . $aiccSid . '&aicc_url=' . $aiccUrl;
            } else {
                $iframeUrl .= $connector . 'aicc_sid=' . $aiccSid . '&aicc_url=' . $aiccUrl;
            }
        }
        else {
            if (!empty($scoParameterModel) && (!empty($scoParameterModel->value))) {
                $iframeUrl .= $connector . $scoParameterModel->value;
            }
        }

        return $this->render('content', [
            'iframeUrl' => $iframeUrl
        ]);
    }

    public function actionAudioPlayer($courseId = null, $modResId = null, $coursewareId = null,
                                      $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            if (!empty($modResId)) {
                $modResModel = LnModRes::findOne($modResId);
                $coursewareId = $modResModel->courseware_id;
            }

            $componentCode = "audio";

            $coursewareModel = LnCourseware::findOne($coursewareId);
            $isAllowDownload = $coursewareModel->is_allow_download == LnCourseware::ALLOW_DOWNLOAD_YES ? true : false;


            if ($coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
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
            }
            else {

                $fileModel = LnFiles::findOne($coursewareModel->file_id);
                $iframeUrl = $fileModel->file_path;

                $coursewareName = $coursewareModel->courseware_name;

                $downloadFileName = "";

                if (!empty($coursewareName)) {
                    if (!empty($fileModel->file_extension)) {
                        $downloadFileName = $coursewareName . '.' . $fileModel->file_extension;
                    } else {
                        $downloadFileName = $coursewareName;
                    }
                }

                $downloadUrl = Url::toRoute(['/resource/down', 'id' => $coursewareModel->file_id, 'file_name' => urlencode($coursewareName)]);

                return $this->renderAjax($componentCode . '-player', [
                    'modResId' => $modResId,
                    'courseId' => $courseId,
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


    public function actionPdfPlayer($courseId = null, $modResId = null, $coursewareId = null,
                                    $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW,$supportEncryptPdfVer=null/* 用于android手机低版本不兼容pdf.js流方式打开文件 */)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            if (!empty($modResId)) {
                $modResModel = LnModRes::findOne($modResId);
                $coursewareId = $modResModel->courseware_id;
            }

            $componentCode = "pdf";

            $coursewareModel = LnCourseware::findOne($coursewareId);
            $isAllowDownload = $coursewareModel->is_allow_download == LnCourseware::ALLOW_DOWNLOAD_YES ? true : false;

            if ($coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
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
            }
            else {

                $fileModel = LnFiles::findOne($coursewareModel->file_id);
                $iframeUrl = $fileModel->file_path;

                $coursewareName = $coursewareModel->courseware_name;

                $downloadFileName = "";

                if (!empty($coursewareName)) {
                    if (!empty($fileModel->file_extension)) {
                        $downloadFileName = $coursewareName . '.' . $fileModel->file_extension;
                    } else {
                        $downloadFileName = $coursewareName;
                    }
                }

                $downloadUrl = Url::toRoute(['/resource/down', 'id' => $coursewareModel->file_id, 'file_name' => urlencode($coursewareName)]);

                $parmas = Yii::$app->request->get();
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
                    'system_key'=>$this->systemKey,
                    'supportEncryptPdfVer'=>$supportEncryptPdfVer,
                    'access_token'=>$parmas['access_token']
                ]);
            }
        }
    }


    public function actionVideoPlayer($courseId = null, $modResId = null, $coursewareId = null,
                                      $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW,$access_token=null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            if (!empty($modResId)) {
                $modResModel = LnModRes::findOne($modResId);
                $coursewareId = $modResModel->courseware_id;
            }

            $componentCode = "video";

            $coursewareModel = LnCourseware::findOne($coursewareId);
            $isAllowDownload = $coursewareModel->is_allow_download == LnCourseware::ALLOW_DOWNLOAD_YES ? true : false;

            if ($coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
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
            }
            else {

                $fileModel = LnFiles::findOne($coursewareModel->file_id);
                $iframeUrl = $fileModel->file_path;

                $coursewareName = $coursewareModel->courseware_name;

                $downloadFileName = "";

                if (!empty($coursewareName)) {
                    if (!empty($fileModel->file_extension)) {
                        $downloadFileName = $coursewareName . '.' . $fileModel->file_extension;
                    } else {
                        $downloadFileName = $coursewareName;
                    }
                }

                $downloadUrl = Url::toRoute(['/resource/down', 'id' => $coursewareModel->file_id, 'file_name' => urlencode($coursewareName)]);

                if ($fileModel->mime_type == "video/x-ms-wmv") {
                    $player = "wmv";
                } else {
                    $player = "html5";
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
                    'componentCode' => $componentCode,
                    'system_key' => $this->systemKey,
                    'access_token' => $access_token
                ]);
            }
        }
    }

    public function actionOfficePlayer($courseId = null, $modResId = null, $coursewareId = null,
                                       $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW, $access_token = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            if (!empty($modResId)) {
                $modResModel = LnModRes::findOne($modResId);
                $coursewareId = $modResModel->courseware_id;
            }

            $componentCode = "office";

            $coursewareModel = LnCourseware::findOne($coursewareId);
            $isAllowDownload = $coursewareModel->is_allow_download == LnCourseware::ALLOW_DOWNLOAD_YES ? true : false;

            if ($coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
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
            }
            else {

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
                $isConvertOffice = $dictionaryService->getDictionaryValueByCode("system","is_convert_office") == FwDictionary::YES ? true : false;
                $isConverted = $fileModel->format_transfer_status == LnFiles::FORMAT_TRANSFER_STATUS_COMPLETED ? true : false;
                if ($isConvertOffice) {
                    if (!$isConverted) {
                        $iframeUrl = "";
                    }
                    else {
                        $iframeUrl = $fileModel->file_path;
                    }
                }
                else {
                    $iframeUrl = Url::toRoute(['/v2/resource/down', 'id' => $coursewareModel->file_id, 'file_name' => urlencode($coursewareName),'access_token'=>$access_token,'system_key'=>$this->systemKey]);
                }

                return $this->renderAjax($componentCode . '-player', [
                    'modResId' => $modResId,
                    'courseId' => $courseId,
                    'iframeUrl' => $iframeUrl,
                    'downloadFileName' => $downloadFileName,
                    'fileType' => $fileType,
                    'mode' => $mode,
                    'coursewareId' => $coursewareId,
                    'attempt' => $attempt,
                    'courseCompleteProcessId' => $courseCompleteProcessId,
                    'courseCompleteFinalId' => $courseCompleteFinalId,
                    'isAllowDownload' => $isAllowDownload,
                    'isConvertOffice' => $isConvertOffice,
                    'isConverted' => $isConverted,
                    'componentCode' => $componentCode,
                    'file_id' => $coursewareModel->file_id,
                ]);
            }
        }
    }


    public function actionInvestigationPlayer($courseId = null, $courseRegId = null, $modResId = null, $coursewareId = null, $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW,$access_token = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            if (!empty($modResId)) {
                $modResModel = LnModRes::findOne($modResId);
                $coursewareId = $modResModel->courseactivity_id;
            }

            $componentCode = "investigation";

            $attempt=Yii::$app->request->getQueryParam('attempt');

            $courseactivityModel = LnCourseactivity::findOne($coursewareId);

            if ($courseactivityModel->is_display_mobile == LnCourseactivity::DISPLAY_MOBILE_NO) {
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
            }
            else {

                $investigationService = new InvestigationService();
                $investigation = $investigationService->getInvestigationInfoByModResId($modResId);

                if ($investigation['investigation_type'] == InvestigationService::INVESTIGATION_TYPE_SURVEY) {
                    return $this->renderAjax('/investigation/single_course_play_survey', [  //调查
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
                        'system_key' => $this->systemKey,
                        'access_token'=> $access_token,
                        'stand' => false
                    ]);
                } else {
                    return $this->renderAjax('/investigation/single_course_play_vote', [ //投票
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
                        'system_key' => $this->systemKey,
                        'access_token'=> $access_token,
                        'stand' => false
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
    public function actionExaminationPlayer($courseId = null, $courseRegId = null, $modResId = null,$coursewareId = null,$courseCompleteFinalId=null,$courseCompleteProcessId = null,$attempt="1", $mode = self::PLAY_MODE_PREVIEW,$access_token=null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId)) ) {
            $attempt=Yii::$app->request->getQueryParam('attempt');
            if (!empty($modResId)) {
                $modResModel = LnModRes::findOne($modResId);
                $coursewareId = $modResModel->courseactivity_id;
            }
            $componentCode = "examination";

            $courseactivityModel = LnCourseactivity::findOne($coursewareId);

            if ($courseactivityModel->is_display_mobile == LnCourseactivity::DISPLAY_MOBILE_NO) {
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
            }
            else {
                $userId = $this->user->kid;
                $companyId = $this->user->company_id;


                $examinationService = new ExaminationService();
                $examination = $examinationService->GetExaminationInfoByModResId($modResId, $mode,$userId,$companyId);
                $examinationModel = $examination['examination'];

                $examinationLast = LnExaminationResultUser::find(false)
                    ->andFilterWhere(['examination_id' => $courseactivityModel->object_id])
                    ->andFilterWhere(['user_id' => $userId,'company_id' => $companyId])
                    ->andFilterWhere(['=', 'course_id', $courseId])
                    ->andFilterWhere(['=', 'course_reg_id', $courseRegId])
                    ->andFilterWhere(['=', 'mod_id', $courseactivityModel->mod_id])
                    ->andFilterWhere(['=', 'mod_res_id', $modResId])
                    ->andFilterWhere(['=', 'result_type', LnExaminationResultUser::RESULT_TYPE_PROCESS])
                    ->andFilterWhere(['in', 'examination_status', array(LnExaminationResultUser::EXAMINATION_STATUS_NOT,LnExaminationResultUser::EXAMINATION_STATUS_START)])
                    ->one();
                //判断生成学员试卷数据
                if (empty($examinationLast->kid) && $mode == self::PLAY_MODE_NORMAL) {
                    $resComplete = LnResComplete::find(false)->andFilterWhere(['course_complete_id' => $courseCompleteFinalId, 'course_id'=>$courseId, 'user_id'=> $userId, 'mod_id'=> $courseactivityModel->mod_id, 'mod_res_id' => $modResId, 'courseactivity_id' => $coursewareId, 'complete_status' => '1', 'complete_type' => '1'])->one();
                    $result = "";
                    $examinationPaperUserId = "";
                    $examinationResultFinalId = "";
                    $examinationResultProcessId = "";
                    $errMessage = "";
                    $examinationService->generateUserPaperByExam($courseactivityModel->object_id, $companyId, $userId, $modResId, $courseRegId, $courseCompleteFinalId, $resComplete->kid, $attempt, $result, $examinationPaperUserId, $examinationResultFinalId, $examinationResultProcessId, $errMessage);
//                    echo $result;
//                    echo $examinationPaperUserId;
//                    echo $examinationResultFinalId;
//                    echo $examinationResultProcessId;
//                    echo $errMessage;
                    if ($result != 'OK'){
                        /**/
                    }
                    /*获取最后一次生成未开始或未完成的考试*/
                    $examinationLast = LnExaminationResultUser::findOne($examinationResultProcessId);

                    /*更新res_complete数据记录*/
                    if ($examinationModel['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST){
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



                return $this->renderAjax('play_examination', [
                    "examination_id" => $examination['kid'],
                    "modResId" => $modResId,
                    "courseId" => $courseId,
                    "courseCompleteFinalId" => $courseCompleteFinalId,
                    "courseCompleteProcessId" => $courseCompleteProcessId,
                    "mod_id" => $examination['mod_id'],
                    "course_reg_id" => $courseRegId,
                    'attempt' => $attempt,
                    "courseactivity_id" => $examination['courseactivity_id'],
                    "component_id" => $examination['component_id'],
                    'componentCode' => $componentCode,
                    "mode" => $mode,
                    'examination' => $examination['examination'],
                    'resultUser' => $examination['examinationResultUser'],
                    'examinationLast' => $examinationLast,
                    'system_key' => $this->systemKey,
                    'access_token' => $access_token,
                    'title' => $examinationModel["title"]
                ]);
            }
        }
    }



    public function actionExaminationStudyPlayer($result_id = null, $examination_id = null,$modResId = null,$courseId = null,$courseRegId=null, $mod_id = null, $attempt="1", $coursewareId = null, $courseCompleteFinalId=null,$courseCompleteProcessId=null,$mode=self::PLAY_MODE_PREVIEW, $access_token=null){
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($examination_id)) ) {
            $attempt=Yii::$app->request->getQueryParam('attempt');
            if (!empty($modResId)) {
                $modResModel = LnModRes::findOne($modResId);
                $coursewareId = $modResModel->courseactivity_id;
            }
            $componentCode = "examination";

            $courseactivityModel = LnCourseactivity::findOne($coursewareId);

            if ($courseactivityModel->is_display_mobile == LnCourseactivity::DISPLAY_MOBILE_NO) {
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
            }
            else {
                /*设置开始时间及状态*/
                if ($mode == self::PLAY_MODE_NORMAL){
                    $findResultModel = LnExaminationResultUser::findOne($result_id);
                    if (!empty($findResultModel->kid) && $findResultModel->examination_status == LnExaminationResultUser::EXAMINATION_STATUS_NOT) {
                        $time = time();
                        LnExaminationResultUser::updateAll(['examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_START, 'start_at' => $time], "kid=:kid", [':kid' => $findResultModel->kid]);
                        $findFinalResult = LnExaminationResultUser::find(false)->andFilterWhere(['examination_id' => $findResultModel->examination_id, 'examination_paper_user_id' => $findResultModel->examination_paper_user_id, 'course_id' => $findResultModel->course_id, 'user_id' => $findResultModel->user_id, 'courseactivity_id' => $findResultModel->courseactivity_id, 'mod_id' => $findResultModel->mod_id, 'mod_res_id' => $findResultModel->mod_res_id, 'company_id' => $findResultModel->company_id, 'result_type' => LnExaminationResultUser::RESULT_TYPE_FINALLY])->one();
                        LnExaminationResultUser::updateAll(['examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_START, 'start_at' => $time, 'examination_duration' => 0], "kid=:kid", [':kid' => $findFinalResult->kid]);
                    }
                }else{
                    $findResultModel = new LnExaminationResultUser();
                    $findFinalResult = clone $findResultModel;
                }
                $examinationModel = LnExamination::findOne($examination_id);
                $examinationService = new ExaminationService();
                if ($mode == self::PLAY_MODE_PREVIEW){
                    $paperQuestion = $examinationService->GetExaminationPaperQuestionCopyPreview($examination_id);
                }else{
                    $paperQuestion = $examinationService->GetExaminationPaperQuestionUser($examination_id, $findResultModel->examination_paper_user_id);
                }
                if (!empty($findResultModel->kid)) {
                    $selectOptions = $examinationService->GetResultUserDetailSelect($findResultModel->user_id, $findResultModel->company_id, $findResultModel->examination_id, $findResultModel->kid);
                    sort($selectOptions);
                    $selectQuestion = $examinationService->GetResultUserDetailSelect($findResultModel->user_id, $findResultModel->company_id, $findResultModel->examination_id, $findResultModel->kid, 'examination_question_user_id');
                    sort($selectQuestion);
                }else{
                    $selectOptions = $selectQuestion = array();
                }

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
                    'paperQuestion' => $paperQuestion['result'],
                    'countPage' => $paperQuestion['page'],
                    'examinationModel' => $examinationModel,
                    'findResultModel' => $findResultModel,
                    'findFinalResult' => $findFinalResult,
                    'selectOptions' => $selectOptions,
                    'selectQuestion' => $selectQuestion,
                    'system_key' =>$this->systemKey,
                    'access_token'=>$access_token
                ]);
            }
        }
    }


    public function actionHtmlPlayer($courseId = null, $modResId = null, $scoId = null, $coursewareId = null,
                                     $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW, $access_token = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {

            if (!empty($modResId)) {
                //课程模块及组件
                $modResModel = LnModRes::findOne($modResId);
                $coursewareId = $modResModel->courseware_id;
            }

            $componentCode = "html";

            $coursewareModel = LnCourseware::findOne($coursewareId);
            $isAllowDownload = false;

            if ($coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
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
            }
            else {

                if (!empty($coursewareModel->embed_url)&&$coursewareModel->courseware_type==1&&$coursewareModel->entry_mode==1) {
//                    $fileModel = LnFiles::findOne($coursewareModel->file_id);
                    $iframeUrl = $coursewareModel->embed_url;
                    $type = 'url';
                }elseif(!empty($coursewareModel->embed_code)&&$coursewareModel->courseware_type==2&&$coursewareModel->entry_mode==1){
                    $iframeCode = $coursewareModel->embed_code;
                    $type = 'code';
                }elseif(!empty($coursewareModel->file_id)&&$coursewareModel->courseware_type==0&&$coursewareModel->entry_mode==0){
                    $fileModel = LnFiles::findOne($coursewareModel->file_id);
                    $iframeUrl = $fileModel->file_path;
                    $type = 'file';
                }
                $display = $coursewareModel->display_position;

                return $this->renderAjax($componentCode .'-player', [
                    'courseId' => $courseId,
                    'modResId' => $modResId,
                    'currentScoId' => $scoId,
                    'iframeUrl' => $iframeUrl,
                    'iframeCode' => $iframeCode,
                    'mode' => $mode,
                    'type'=>$type,
                    'coursewareId' => $coursewareId,
                    'attempt' => $attempt,
                    'display' => $display,
                    'componentCode' => $componentCode
                ]);
            }
        }
    }

    public function actionHtmlCoursewarePlayer($courseId = null, $modResId = null, $scoId = null, $coursewareId = null,
                                               $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW, $access_token = null)
    {
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {

            if (!empty($modResId)) {
                //课程模块及组件
                $modResModel = LnModRes::findOne($modResId);
                $coursewareId = $modResModel->courseware_id;
            }

            $componentCode = "html-courseware";

            $coursewareModel = LnCourseware::findOne($coursewareId);
            $isAllowDownload = false;

            if ($coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
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
            }
            else {

                if (!empty($coursewareModel->file_id)) {
                    $fileModel = LnFiles::findOne($coursewareModel->file_id);
                    if (!empty($coursewareModel->entrance_address)) {
                        $iframeUrl = $fileModel->file_dir. $coursewareModel->entrance_address;
                    }
                    else {
                        $iframeUrl = $fileModel->file_dir;
                    }
                }

                return $this->renderAjax($componentCode .'-player', [
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


    public function actionPdfView($id, $hash)
    {
        $current_time = time();
        $referer = $_SERVER["HTTP_REFERER"];

        if (($referer !== null && !strpos($referer, 'pdf.worker.js')) || $hash === null || $hash === '') {
            exit;
        }

        $aes = new AES();
        $de_hash = $aes->decrypt($hash);
        if ($de_hash[0] !== CryptErrorCode::OK) {
            exit;
        }
        $data = base64_decode($de_hash[1]);
        $data_id = substr($data, 0, 36);
        $time = substr($data, 39);
        $diff = $current_time - intval($time);

        if ($id !== $data_id || $diff > 60) {
            exit;
        }

        $file = LnFiles::findOne($id);
        var_dump($file);
        if ($file === null || substr($file->file_path, -3) !== 'pdf') {
            exit;
        }
        $file_name = $file->file_name;
        $file_path = $_SERVER['DOCUMENT_ROOT'] . $file->file_path;

        if (!file_exists($file_path)) { //检查文件是否存在
            echo "error:file not found!";
            exit;
        } else {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . urlencode($file_name));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            ob_clean();
            flush();
            readfile($file_path);
            exit;
        }
    }


    public function actionScormPlayer($courseId = null, 
                                      $modResId = null, 
                                      $scoId = null, 
                                      $coursewareId = null,
                                      $courseRegId = null,
                                      $courseCompleteFinalId = null, 
                                      $courseCompleteProcessId = null, 
                                      $attempt = "1", 
                                      $mode = self::PLAY_MODE_PREVIEW,
                                      $access_token=null,
                                      $system_key=null,
                                      $isMobile = null)
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
                'withSessionStr' => $withSessionStr,
                'access_token' => $access_token,
                'system_key' => $system_key
            ]);
        }
    }


    public function actionAiccPlayer($courseId = null, $modResId = null, $scoId = null, $coursewareId = null,
                                     $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW, $access_token=null,$system_key=null)
    {
        echo "移动端暂不支持此类型课件!";exit;
        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            $withSession = true;
            if (!empty($modResId)) {
                //课程模块及组件
                $modResModel = LnModRes::findOne($modResId);
                $coursewareId = $modResModel->courseware_id;
                $modId = $modResModel->mod_id;


                $componentCode = "aicc";

                $coursewareModel = LnCourseware::findOne($coursewareId);
                $isAllowDownload = false;

                if ($coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
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
                }
                else {

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

                        $userId = $this->user->kid;
                        $withSessionStr = "True";


                        if ($mode == self::PLAY_MODE_NORMAL) {
                            $courseService = new CourseService();
                            $courseCompleteService = new CourseCompleteService();


                            $isReg = $courseService->isUserRegCourse($userId, $courseId, $courseRegId);

                            if (empty($courseCompleteFinalId) || empty($courseCompleteProcessId)) {
                                $courseCompleteProcessModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_PROCESS);
                                $courseCompleteFinalModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);

                                $courseCompleteProcessId = $courseCompleteProcessModel->kid;
                                $courseCompleteFinalId = $courseCompleteFinalModel->kid;
                            }
//                        $attempt = $scormService->scorm_get_last_attempt($courseRegId, $modResId, $userId);

                            if ($withSession) {
                                $listSessionKey = "ScoesTrackResultListAttempt_CourseRegId_" . $courseRegId . "_ModResId_" . $modResId . "_ScoId_" . $scoId . "_Attempt_" . strval($attempt);
                                if (Yii::$app->session->has($listSessionKey)) {
                                    Yii::$app->session->remove($listSessionKey);
                                }

                                $alreadySessionedListKey = "AlreadySessionedElementList_CourseRegId_" . $courseRegId . "_ModResId_"  . $modResId . "_ScoId_" . $scoId . "_Attempt_" . strval($attempt);
                                if (Yii::$app->session->has($alreadySessionedListKey))
                                {
                                    $alreadySessionedList = Yii::$app->session->get($alreadySessionedListKey);

                                    if (count($alreadySessionedList) > 0) {
                                        foreach ($alreadySessionedList as $elementKey) {
                                            Yii::$app->session->remove($elementKey);
                                        }
                                    }
                                }
                            }

                            $value = strval(time());

                            $scormScoesTrackService = new ScormScoesTrackService();

                            $scormScoesTrackService->insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scormModel, $scoId, $userId, 'x.start.time', $attempt, $value,$withSession);
                            $scormScoesTrackService->insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scormModel, $scoId, $userId, 'cmi.core.lesson_status', $attempt, "incomplete", null, $withSession, false, $system_key, $courseComplete, $getCertification, false);
                            
                            $aiccSid = $scormService->scorm_aicc_get_hacp_session($courseRegId, $courseId, $coursewareId, $modResId, $scormModel->kid, $scoId, $modId, $userId, $attempt);

                            
                            $element = "cmi.core.lesson_status";
                            if ($scormModel->scorm_version == ScormScoesTrackService::SCORM_13) {
                                $element = "cmi.completion_status";
                            }


                            $track = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId, $modResId, $scoId, $element, $attempt, $withSession);

                            if ($track == null)
                                $currentStatus = "";
                            else {
                                $currentStatus = $track->value;
                            }

                            $resourceCompleteService = new ResourceCompleteService();
                            $isResComplete = $resourceCompleteService->isResComplete($courseCompleteFinalId, $modResId);
                            $currentIsResCompleteStr = $isResComplete ? "1" : "0";
                        } else {
                            $courseRegId = null;
                            $aiccSid = $scormService->scorm_aicc_get_hacp_session($courseRegId, $courseId, $coursewareId, $modResId, $scormModel->kid, $scoId, $modId, $userId, 0);
//                        $attempt = "1";

                            $courseCompleteProcessId = "";
                            $courseCompleteFinalId = "";
                            $currentStatus = "";
                            $currentIsResCompleteStr = "0";
                        }

                        if (empty($aiccSid)) {
                            $aiccSid = $scormService->random_string(20);
                        }

                        $iframeUrl = Yii::$app->urlManager->createUrl(["/resource/player/content",
                            'componentCode' => $componentCode,
                            "fileId" => $coursewareModel->file_id,
                            "scoId" => $scoId,
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

                        $scoes = $scormScoesService->getScormScoesByScormId($scormModel->kid);

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


                        return $this->renderAjax($componentCode . '-player', [
                            'courseId' => $courseId,
                            'modResId' => $modResId,
                            'currentScoId' => $scoId,
                            'iframeUrl' => $iframeUrl,
                            'mode' => $mode,
                            'scormVersion' => $scormVersion,
                            'coursewareId' => $coursewareId,
                            'def' => json_encode($def),
                            'cmiobj' => json_encode($cmiobj),
                            'cmiint' => json_encode($cmiint),
                            'cmicommentsuser' => json_encode($cmicommentsuser),
                            'cmicommentslms' => json_encode($cmicommentslms),
                            'cmistring256' => $cmistring256,
                            'cmistring4096' => $cmistring4096,
                            'scorm_debugging' => false,
                            'scormAuto' => "0",
                            'scormAutoCommit' => "0",
                            'scormId' => $scormModel->kid,
                            'attempt' => $attempt,
                            'currentorg' => $currentorg,
                            'courseRegId' => $courseRegId,
                            'scoName' => $scoName,
                            'courseCompleteProcessId' => $courseCompleteProcessId,
                            'courseCompleteFinalId' => $courseCompleteFinalId,
                            'componentCode' => $componentCode,
                            'currentStatus' => $currentStatus,
                            'currentIsResCompleteStr' => $currentIsResCompleteStr,
                            'withSessionStr' => $withSessionStr,
                            'access_token' => $access_token,
                            'system_key' => $this->systemKey
                        ]);
                    }
                }
            }
        }
    }


    public function actionBookPlayer($courseId = null, $modResId = null, $scoId = null, $coursewareId = null,
                                     $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW, $access_token = null)
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

                if ($coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_NO) {
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


    public function actionHomeworkPlayer($courseId = null, $courseRegId = null, $modResId = null, $coursewareId = null, $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW,$access_token = null)
    {
        $this->layout = 'bootstrap';
        $user_id = $this->user->kid;
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

                if ($courseactivityModel->is_display_mobile == LnCourseactivity::DISPLAY_MOBILE_NO) {
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
                }

                 else {
                    $investigationService = new InvestigationService();
                    $homework = $investigationService->getHomeworkInfoByModResId($modResId);
                    $uploadBatch = date("YmdHis");
                    $model = !empty($id) ? LnHomework::findOne($id) : new LnHomework();
                    $teacherfiles = array();
                    if (!empty($homework['kid'])) {
                        $teacherfiles = LnHomeworkFile::findAll(['homework_id' => $homework['kid'], 'homework_file_type' => '0'], false);
                    }
                    $studentfiles = array();
                    if (!empty($homework['kid'])) {
                        $studentfiles = LnHomeworkFile::findAll(['homework_id' => $homework['kid'], 'homework_file_type' => '1', 'user_id' => $user_id, 'course_complete_id' => $courseCompleteProcessId], false);
                    }
                    $resCompleteId = LnResComplete::find(false)->andFilterWhere(['course_complete_id' => $courseCompleteFinalId, 'course_id' => $courseId, 'user_id' => $user_id, 'mod_id' => $homework['mod_id'], 'mod_res_id' => $modResId, 'courseactivity_id' => $coursewareId, 'complete_type' => '1'])->one()->kid;//->createCommand()->getRawSql();
                    $completetype = LnResComplete::find(false)->andFilterWhere(['course_complete_id' => $courseCompleteFinalId, 'course_id' => $courseId, 'user_id' => $user_id, 'mod_id' => $homework['mod_id'], 'mod_res_id' => $modResId, 'courseactivity_id' => $coursewareId, 'complete_type' => '1'])->one()->complete_status;//->createCommand()->getRawSql();
                    $homeworkresult = LnHomeworkResult::findOne(['user_id' => $user_id, 'homework_id' => $homework['kid'], 'mod_res_id' => $modResId, 'course_complete_id' => $courseCompleteProcessId,], false);

                    $view = $completetype;
                    if (empty($courseCompleteFinalId)) {
                        $view = 2;
                    }
                    //作业过期判断
                    $isOverdue=false;
                    if($homework['finish_before_at'] && time() > $homework['finish_before_at'])
                    {
                        $isOverdue=true;
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
                        'system_key' => $this->systemKey,
                        'access_token' => $access_token,
                        'user_id' => $user_id,
                        'company_id' => $this->user->company_id
                    ]);
                }
            }
        }
    }

    public function actionHomeworkPreviewPlayer($courseId = null,$courseRegId = null,$modResId = null,$coursewareId = null,$courseCompleteFinalId=null,$courseCompleteProcessId = null,$attempt="1",$mode = self::PLAY_MODE_PREVIEW, $access_token = null)
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

                if ($courseactivityModel->is_display_mobile == LnCourseactivity::DISPLAY_MOBILE_NO) {
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

                    return $this->renderAjax('homework-player', [
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
                        'user_id' => $this->user->kid,
                        'access_token' => $access_token
                    ]);
                }
            }
        }
    }

    public function actionFlashPlayer($courseId = null, $modResId = null, $coursewareId = null,
                                      $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW,$access_token=null)
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

                if ($this->systemKey == 'lms-ios' || $this->systemKey == 'lms-android') {
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
                }
                else {

                    $fileModel = LnFiles::findOne($coursewareModel->file_id);
                    $iframeUrl = $fileModel->file_path;

                    $coursewareName = $coursewareModel->courseware_name;

                    $downloadUrl = Url::toRoute(['/resource/down', 'id' => $coursewareModel->file_id, 'file_name' => urlencode($coursewareName)]);

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
                        'componentCode' => $componentCode,
                        'access_token' =>$access_token,
                        'system_key' => $this->systemKey
                    ]);
                }
            }
        }
    }


    public function actionOtherPlayer($courseId = null, $modResId = null, $coursewareId = null,
                                      $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW, $isMobile = null)
    {
        echo "移动端暂不支持此类型课件!";exit;

    }

}