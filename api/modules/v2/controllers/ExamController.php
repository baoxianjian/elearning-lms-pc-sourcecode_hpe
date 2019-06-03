<?php
/**
 * Created by PhpStorm.
 * User: chenli
 * Date: 16/1/6
 * Time: 下午4:07
 */

namespace api\modules\v2\controllers;


use api\base\BaseController;
use common\models\learning\LnExamination;
use common\models\learning\LnExaminationPaperUser;
use common\models\learning\LnExaminationResultUser;
use common\models\learning\LnCourse;
use Yii;
use yii\web\Response;
use common\models\learning\LnExaminationQuestion;
use common\models\learning\LnExamPaperQuestion;
use common\services\api\ExamService;
use common\services\learning\ExaminationService;

class ExamController extends BaseController
{
    const LEARNING_DURATION = "30";//太快会影响性能

    public $layout = 'bootstrap';
    public $enableCsrfValidation = false;

    /**
     * 类型 变量
     * @return array
     */
    private function getTypes() {
        return [
            'TYPE_RADIO' => LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO,
            'TYPE_CHECKBOX' => LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX,
            'TYPE_JUDGE' => LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE,
            'DELIMITER_TYPE' => LnExamPaperQuestion::RELATION_TYPE_HR,
            'PAPER_TYPE' => LnExamPaperQuestion::RELATION_TYPE_PAPER
        ];
    }

    /**
     *
     * @param null $question
     * @param null $option
     * @param array $default
     * @return array
     */
    private function getRequireFields($question = null,$option = null,$default = []) {
        $question_fields = [
            'kid','examination_paper_user_id','examination_question_user_id','default_score','sequence_number','status','qu_id'=>'kid','relation_type'
        ];
        $option_fields = [
            'kid','examination_question_user_id','exam_quest_option_copy_id','examination_question_option_id','option_title','option_description','default_score','is_right_option','sequence_number'
        ];

        if($question !== null) return $question_fields;
        if($option !== null) return $option_fields;
        return $default;
    }

    /**
     * 考试详情及历史记录
     * @param $id
     * @return array
     */
    public function actionDetail($id) {
        $userId = $this->user->kid;
        $companyId = $this->user->company_id;
        $examService = new ExamService($this->systemKey,$userId,$companyId);
        $service = new ExaminationService();

        $model = ExamService::findOne($id);
        $resultUserCount = $examService->userCount($id);

        $mod_id = Yii::$app->request->getQueryParam('mod_id',null);
        $mod_res_id = Yii::$app->request->getQueryParam('modResId',null);
        $course_id = Yii::$app->request->getQueryParam('courseId',null);
        $course_reg_id = Yii::$app->request->getQueryParam('courseRegId',null);
        $course_complete_id = null;
        $res_complete_id = null;
        $stand = Yii::$app->request->getQueryParam('stand','true') == 'true';
        if(!$stand) {
            $resModel = \common\models\learning\LnResComplete::findOne([
                'course_id' => $course_id,
                'course_reg_id' => $course_reg_id
            ]);
            $course_complete_id = $resModel->course_complete_id;
            $res_complete_id = $resModel->kid;
        }

        $examinationLast = $examService->getExaminationProcessLast($id,$userId,$companyId,$course_id,$course_reg_id,$mod_id,$mod_res_id);

        /*判断是否生效*/
        $state = 'starting';
        $currentTime = time();
        if (!empty($model->start_at) && $currentTime < $model->start_at){
            $state = 'no_start';
        }
        if (!empty($model->end_at) && $currentTime >= $model->end_at){
            $state = 'end';
        }
        if ($model->limit_attempt_number > 0 && $resultUserCount >= $model->limit_attempt_number){
            $created = false;
        }else{
            $created = true;
        }

        if (empty($examinationLast) && $state == 'starting' && $created ){
            $attempt = $resultUserCount+1;
            $result = "";
            $examinationPaperUserId = "";
            $examinationResultFinalId = $service->getExaminationResultUserFinal($userId, $id);
            $examinationResultProcessId = "";
            $errMessage = "";
            $examService->generateUserPaperByExam(
                $id,
                $companyId,
                $userId,
                $mod_res_id,
                $course_reg_id,
                $course_complete_id,
                $res_complete_id,
                $attempt,
                $result,
                $examinationPaperUserId,
                $examinationResultFinalId,
                $examinationResultProcessId,
                $errMessage
            );
            $examinationLast = LnExaminationResultUser::findOne($examinationResultProcessId);
        }

        
        $history = $examService->GetExaminationByUserResultAll($userId, $id, $companyId,$mod_id,$mod_res_id,$course_id);
        if($history) {
            foreach($history as $k => $v) {
                $history[$k]['human_date'] = date("Y-m-d H:i",$v['created_at']);
            }
        }

        Yii::$app->response->format = 'json';
        $base = $model->toArray();

        function tryLimits($n) {
            if($n == 0) return '不限制';
            return $n . '次';
        }

        function strategy($s) {
            $default = [
                LnExamination::ATTEMPT_STRATEGY_FIRST => '第一次',
                LnExamination::ATTEMPT_STRATEGY_AVG => '平均分',
                LnExamination::ATTEMPT_STRATEGY_LAST => '最后一次',
                LnExamination::ATTEMPT_STRATEGY_TOP => '最高分'
            ];
            return isset($default[$s]) ? $default[$s] : '';
        }

        function state($s,$examinationLast) {
            if($s == 'starting' && !empty($examinationLast)) return '开始';
            if($s == 'starting' && empty($examinationLast)) return '您已经完成此项考试';
            if($s == 'no_start') return '考试未生效';
            if($s == 'end') return '考试已失效';
            return '';
        }

        $base['strategy'] = strategy($base['attempt_strategy']);
        $base['try_limits'] = tryLimits($base['limit_attempt_number']);
        $base['date_range'] = $model->start_at && $model->end_at ? date('y-m-d', $model->start_at) .' - '. date('y-m-d', $model->end_at) : '';
        $base['can_start'] = $state == 'starting' && !empty($examinationLast);
        $base['state'] = state($state,$examinationLast);
        $base['all_number'] = $examinationLast->all_number;
        $base['is_test_mode'] = $model->examination_mode == ExamService::EXAMINATION_MODE_TEST;
        return [
            'base' => $base,
            'history' => $history,
            'examinationLast' => is_callable([$examinationLast,'toArray']) ? $examinationLast->toArray() : []
        ];
    }

    /**
     * 获取考试问题
     * @param $kid
     * @return array|bool
     */
    public function actionQuestions($kid) {
        $examService = new ExamService($this->systemKey,$this->user->kid,$this->user->company_id);
        $findResultModel = LnExaminationResultUser::findOne($kid);
        if ($findResultModel && $findResultModel->examination_status == LnExaminationResultUser::EXAMINATION_STATUS_NOT) {
            $examService->preExamRecord($kid,$findResultModel);
        }

        $question_fields = $this->getRequireFields(true);
        $option_fields = $this->getRequireFields(null,true);

        $paperQuestion = $examService->GetExaminationPaperQuestionUser($findResultModel->examination_id, $findResultModel->examination_paper_user_id,$question_fields,$option_fields);

        $examinationModel = LnExamination::findOne($findResultModel->examination_id);
        $queryParams = Yii::$app->request->getQueryParams();
        $exam = ExamService::findOne($queryParams['id']);

        Yii::$app->response->format = 'json';
        $paperQuestion['const'] = $this->getTypes();
        $paperQuestion['time_left'] = $examinationModel->limit_time*60 - $findResultModel->examination_duration;
        $paperQuestion['time_limit'] = $examinationModel->limit_time*60;
        $paperQuestion['history'] = is_callable([$findResultModel,'toArray']) ? $findResultModel->toArray() : [];
        $paperQuestion['title'] = $exam->title;
        return $paperQuestion;
    }

    /**
     * 考试时间记录
     * @return array
     */
    public function actionUpdateDuration(){
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $params = Yii::$app->request->post();
            $user_id = Yii::$app->user->getId();
            $company_id = Yii::$app->user->identity->company_id;
            /*保持会话*/
            $commonUserService = new \common\services\framework\UserService();
            $commonUserService->keepOnline($user_id);
            /*更新时长*/
            $service = new \common\services\learning\ExaminationService();
            $service->updateDuration($params, $user_id, $company_id);
            return ['result' => 'success'];
        }
    }
    
    /**
     * 独立考试
     * @param $id
     * @return string
     */
    public function actionView(){
        $stand = Yii::$app->request->getQueryParam('stand','true') == 'true';
        $this->layout = 'amazeui';
        $queryParams = Yii::$app->request->getQueryParams();
        $id = $queryParams['id'];

        $default = [
            'courseId' => null,
            'mod_id' => null,
            'courseactivity_id' => null
        ];
        if(!$stand) {
            $modResModel = \common\models\learning\LnModRes::findOne($queryParams['modResId']);
            $courseactivityModel = \common\models\learning\LnCourseactivity::findOne($modResModel->courseactivity_id);
            $id = $courseactivityModel->object_id;
            $default['courseId'] = $modResModel->course_id;
            $default['mod_id'] = $modResModel->mod_id;
            $default['courseactivity_id'] = $modResModel->courseactivity_id;
        }

        return $this->render('view', [
            'system_key' => $queryParams['system_key'],
            'access_token' => $queryParams['access_token'],
            'id' => $id,
            'stand' => $stand,
            'extra' => $this->getExtraParam($queryParams,$default)
        ]);
    }

    /**
     * 针对课程内考试获取额外参数
     * @param $queryParams
     * @return array
     */
    private function getExtraParam($queryParams,$default = []) {
        $fields = ['modResId', 'courseId', 'courseRegId', 'mod_id', 'coursewareId', 'courseCompleteFinalId', 'courseactivity_id', 'courseCompleteProcessId'];
        $extra = [];
        foreach ($fields as $field) {
            $extra[$field] = isset($queryParams[$field]) ? $queryParams[$field] : (isset($default[$field]) ? $default[$field] : null);
        }
        if ($extra['courseRegId'] === null) {
            return $extra;
        }
        $courseCompleteService = new \common\services\learning\CourseCompleteService();
        $courseCompleteProcessModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($extra['courseRegId'], \common\models\learning\LnCourseComplete::COMPLETE_TYPE_PROCESS);
        $courseCompleteFinalModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($extra['courseRegId'], \common\models\learning\LnCourseComplete::COMPLETE_TYPE_FINAL);

        $extra['courseCompleteProcessId'] = $courseCompleteProcessModel->kid;
        $extra['courseCompleteFinalId'] = $courseCompleteFinalModel->kid;

        $resourceCompleteService = new \common\services\learning\ResourceCompleteService();

        $finalModel = $resourceCompleteService->checkResourceStatus( $extra['courseCompleteFinalId'] , $extra['modResId'], $isDoing, $isComplete);
        if(!$isComplete) {
            $processModel = $resourceCompleteService->getLastResCompleteNonDoneInfo($extra['courseRegId'],
                $extra['modResId'], \common\models\learning\LnResComplete::COMPLETE_TYPE_PROCESS, $extra['courseCompleteProcessId']);
            if(empty($processModel)) {
                $resourceCompleteService->addResCompleteDoingInfo(
                    $courseCompleteProcessModel->kid,
                    $extra['courseRegId'],
                    $extra['modResId'],
                    \common\models\learning\LnResComplete::COMPLETE_TYPE_PROCESS,$this->systemKey);
            }
            if(empty($finalModel)) {
                $resourceCompleteService->addResCompleteDoingInfo(
                    $courseCompleteFinalModel->kid,
                    $extra['courseRegId'],
                    $extra['modResId'],
                    \common\models\learning\LnResComplete::COMPLETE_TYPE_FINAL,$this->systemKey);
            }
        }

        return $extra;
    }

    /**
     * 独立考试学习
     * @param $result_id
     * @param null $examination_id
     * @return string
     */
    public function actionPlayer($kid, $attempt = 1){
        $stand = Yii::$app->request->getQueryParam('stand','true') == 'true';
        $this->layout = 'amazeui';

        $queryParams = Yii::$app->request->getQueryParams();
        return $this->render('player', [
            "kid" => $kid,
            'id' => Yii::$app->request->getQueryParam('id'),
            'system_key' => $queryParams['system_key'],
            'access_token' => $queryParams['access_token'],
            'stand' => $stand,
            'extra' => $this->getExtraParam($queryParams)
        ]);
    }

    /**
     * 查看考试记录
     * @param $kid result_id
     * @return array
     */
    public function actionHistory($kid) {
        $service = new ExaminationService();
        $data = $service->GetExaminationUserPaper($kid,$this->getRequireFields(true),$this->getRequireFields(null,true));
        Yii::$app->response->format = 'json';
        return [
            'result' => $data['result'],
            'const' => $this->getTypes()
        ];
    }


    /**
     * 考试结果查看
     * @param $result_id
     * @param null $modResId
     * @param string $mode
     * @return string
     */
    public function actionResult($result_id, $modResId = null, $mode = ''){
        $this->layout = 'amazeui';
        $userResult = LnExaminationResultUser::findOne($result_id);
        $uid = $this->user->kid;
        $company_id = $this->user->company_id;
        $service = new ExamService($this->systemKey);
        $examination = $service->GetExaminationByUserOne($userResult->examination_id);
        $userResultAll = $service->GetExaminationByUserResultAll(
            $uid,
            $userResult->examination_id,
            $company_id,
            $userResult->mod_id,
            $userResult->mod_res_id,
            $userResult->course_id
        );

        if (!empty($userResult->course_id)){
            $course = LnCourse::findOne($userResult->course_id);
        }else{
            $course = new LnCourse();
        }

        return $this->render('result', [
            'examination' => $examination,
            'userResult' => $userResult,
            'userResultAll' => $userResultAll,
            'modResId' => $modResId,
            'mode' => $mode,
            'course' => $course,
            'access_token' => Yii::$app->request->getQueryParam('access_token'),
            'system_key' => Yii::$app->request->getQueryParam('system_key'),
            'result_id' => $result_id
        ]);
    }



    //i do not know
    public function actionTempSave(){
        $checked = Yii::$app->request->post('checked');
        $result_id = Yii::$app->request->post('result_id');
        $examination_paper_user_id = Yii::$app->request->post('examination_paper_user_id');
        $examination_id = Yii::$app->request->post('examination_id');
        $examination_question_user_id = Yii::$app->request->post('question_id');
        $options_id = Yii::$app->request->post('options_id');
        $params = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (empty($result_id) || empty($examination_paper_user_id) || empty($examination_id) || !isset($options_id)){
            return ['result' => 'fail', 'errmsg' => '参数传递错误'];
        }
        $user_id = $this->user->kid;
        $company_id = $this->user->company_id;

        $examService = new ExamService($this->systemKey,$user_id,$company_id);
        return $examService->saveAnswer(
            $result_id,
            $examination_id,
            $examination_question_user_id,
            $examination_paper_user_id,
            $options_id,
            $params,
            $checked
        );
    }
}