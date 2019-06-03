<?php
/**
 * Created by PhpStorm.
 * User: chenli
 * Date: 16/1/15
 * Time: 下午4:15
 */

namespace api\modules\v2\controllers;


use api\base\BaseController;
use common\models\learning\LnCourse;
use common\models\learning\LnExamination;
use common\models\learning\LnExaminationResultUser;
use common\models\learning\LnResComplete;
use common\services\learning\ExaminationService;
use common\services\framework\UserService;
use Yii;
use yii\db\Expression;
use yii\web\Response;
use common\services\framework\PointRuleService;

class ExamManageMainController extends BaseController
{
    const LEARNING_DURATION = "30";//太快会影响性能
    public $layout = 'frame';
    public $enableCsrfValidation = false;
    
    /**
     * ps: 废弃
     * 考试返回结果
     * @param $id
     * @param null $modResId
     * @param string $mode
     * @return string
     */
    public function actionPlayResult($id, $modResId = null, $mode = ''){
        $params = Yii::$app->request->get();
        $userResult = LnExaminationResultUser::findOne($id);
        $uid = $this->user->kid;
        $company_id = $this->user->company_id;
        $service = new ExaminationService();
        $examination = $service->GetExaminationByUserOne($userResult->examination_id);
        $userResultAll = $service->GetExaminationByUserResultAll($uid, $userResult->examination_id, $company_id, $userResult->mod_id, $userResult->mod_res_id, $userResult->course_id);

        /*判断是否可以在考试*/
        $next = LnExaminationResultUser::find(false)->andFilterWhere(['user_id' => $uid, 'examination_id' => $userResult['examination_id'], 'result_type' => LnExaminationResultUser::RESULT_TYPE_PROCESS])->andFilterWhere(['in', 'examination_status', array(LnExaminationResultUser::EXAMINATION_STATUS_NOT,LnExaminationResultUser::EXAMINATION_STATUS_START)])->one();
        if (empty($next)) {
            $created = false;
            if ($examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST) {
                $count = LnExaminationResultUser::find(false)->andFilterWhere(['user_id' => $uid, 'company_id' => $company_id, 'examination_id' => $userResult->examination_id, 'course_id' => $userResult->course_id, 'mod_id'=>$userResult->mod_id, 'result_type' => LnExaminationResultUser::RESULT_TYPE_PROCESS, 'examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_END])->count();
                if ($count < $examination['limit_attempt_number']) {
                    $created = true;
                }
            } else {
                $created = true;
            }
            if ($created) {
                $attempt = $userResult->examination_attempt_number + 1;
                $result = "";
                $examinationPaperUserId = "";
                $examinationResultFinalId = "";
                $examinationResultProcessId = "";
                $errMessage = "";
                if ($mode == 'course'){
                    $resComplete = LnResComplete::find(false)->andFilterWhere(['course_complete_id' => $userResult->course_complete_id, 'course_id'=>$userResult->course_id, 'user_id'=> $uid, 'mod_id'=> $userResult->mod_id, 'mod_res_id' => $modResId, 'courseactivity_id' => $userResult->courseactivity_id, 'complete_status' => '1', 'complete_type' => '1'])->one();
                    $service->generateUserPaperByExam($userResult->examination_id, $company_id, $uid, $userResult->mod_res_id, $userResult->course_reg_id, $userResult->course_complete_id, $resComplete->kid, $attempt, $result, $examinationPaperUserId, $examinationResultFinalId, $examinationResultProcessId, $errMessage);
                }else{
                    $service->generateUserPaperByExam($userResult->examination_id, $company_id, $uid, "", "", "", "", $attempt, $result, $examinationPaperUserId, $examinationResultFinalId, $examinationResultProcessId, $errMessage);
                }

                if ($result == 'OK') {
                    $next = LnExaminationResultUser::findOne($examinationResultProcessId);
                } else {
                    $next = new LnExaminationResultUser();
                }
            } else {
                $next = new LnExaminationResultUser();
            }
        }

        if (!empty($userResult->course_id)){
            $course = LnCourse::findOne($userResult->course_id);
        }else{
            $course = new LnCourse();
        }
        if ($mode == 'course') {
            return $this->renderAjax('play_result', [
                'examination' => $examination,
                'userResult' => $userResult,
                'userResultAll' => $userResultAll,
                //'rightNumber' => $rightNumber,
                'modResId' => $modResId,
                'mode' => $mode,
                'course' => $course,
                'next' => $next,
                'access_token'=> $params['access_token'],
                'system_key' => $this->systemKey
            ]);
        }else {
            return $this->render('play_result', [
                'examination' => $examination,
                'userResult' => $userResult,
                'userResultAll' => $userResultAll,
                //'rightNumber' => $rightNumber,
                'modResId' => $modResId,
                'mode' => $mode,
                'course' => $course,
                'next' => $next,
                'access_token'=> $params['access_token'],
                'system_key' => $this->systemKey
            ]);
        }
    }


    public function actionSubmitResult()
    {
        $params=Yii::$app->request->post();
        $examinationService=new ExaminationService();
        $result = $examinationService->SubmitResult($params,$this->user->kid,$this->user->company_id);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (isset($result['result']) && $result['result'] == 'fail'){
            return $result;
        }else {
            return ['result' => 'success', 'result_id' => $result['result_id'], 'score' => $result['score']];
        }
    }

    public function actionPlayResComplete(){
        $params=Yii::$app->request->get();
        $courseComplete=false;
        $getCetification=false;
        $courseId=null;
        $certificationId=null;
        $investigationService=new ExaminationService();
        $investigationService->addResCompleteDoneInfo($params);
        $investigationService->addResCompleteDoneInfo($params,$courseComplete,$getCetification,$courseId,$certificationId);
        //计算积分
        $pointRuleService=new PointRuleService();
        $pointResult=$pointRuleService->countCourseAndCetificationPoint($courseComplete,$getCetification,$courseId,$certificationId);
        
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['result' => 'success'];
    }
}