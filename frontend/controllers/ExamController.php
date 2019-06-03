<?php
namespace frontend\controllers;

use common\models\learning\LnExamination;
use common\models\learning\LnExaminationPaper;
use common\models\learning\LnExaminationPaperCopy;
use common\models\learning\LnExamPaperQuestion;
use common\models\learning\LnExamPaperQuestUser;
use common\models\learning\LnExaminationPaperUser;
use common\models\learning\LnExamQuestionCategory;
use common\models\learning\LnExamQuestOptionUser;
use common\models\learning\LnExamQuestionUser;
use common\models\learning\LnExaminationResultUser;
use common\models\learning\LnExamResultDetail;
use common\models\learning\LnExaminationQuestion;
use common\models\learning\LnExamQuestionOption;
use common\services\learning\ExaminationService;
use Yii;
use frontend\base\BaseFrontController;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\log\Logger;
use yii\web\Response;


class ExamController extends BaseFrontController
{

	const LEARNING_DURATION = "30";//太快会影响性能

	public $layout = 'frame';

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['access']['except'] = ['temp-save'];

		return $behaviors;
	}

	public function actionIndex(){

	}

	/**
	 * 独立考试
	 * @param $id
	 * @return string
	 */
	public function actionView($id){
		$userId = Yii::$app->user->getId();
		$companyId = Yii::$app->user->identity->company_id;

        $service = new ExaminationService();
        //是否推送过
        $flag=$service->getRelUser(array('user_id' => $userId, 'learning_object_id' => $id));
        if(!$flag){
            return $this->render("/student/index");
        }

		$model = LnExamination::findOne($id);

		if ($model->release_status == LnExamination::STATUS_FLAG_TEMP){
			header('Location: /');
			exit;
		}
		$resultUserCount = $service->getExaminationProcessCount($id, $userId, $companyId);
		$examinationLast = $service->getExaminationProcessLast($id, $userId, $companyId);/*未开始或者进行中的考试*/
		/*判断是否生效*/
		$validity = 'starting';
		$currentTime = time();
		if (!empty($model->start_at) && $currentTime < $model->start_at){
			$validity = 'no_start';
		}
		if (!empty($model->end_at) && $currentTime >= $model->end_at){
			$validity = 'end';
		}
		if ($model->limit_attempt_number > 0 && $resultUserCount >= $model->limit_attempt_number){
			$created = false;
		}else{
			$created = true;
		}

		$errMessage = "";
		$generate = true;
		if (empty($examinationLast) && $validity == 'starting' && $created ){
			$attempt = 0;
			$result = "";
			$examinationPaperUserId = "";
			$examinationResultProcessId = "";
			$examinationResultFinalId = $service->getExaminationResultUserFinal($userId, $id);
			$service->generateUserPaperByExam($id, $companyId, $userId, "", "", "", "", $attempt, $result, $examinationPaperUserId, $examinationResultFinalId, $examinationResultProcessId, $errMessage);
//		echo $result;
//		echo $examinationPaperUserId;
//		echo $examinationResultFinalId;
//		echo $examinationResultProcessId;
//		echo $errMessage;
			if ($result != 'OK'){
				$generate = false;
				Yii::getLogger()->log("ExamController->actionView:".$errMessage, Logger::LEVEL_ERROR);
				/**/
			}
			$examinationLast = $service->getExaminationProcessLast($id, $userId, $companyId);
		}

		if (!empty($examinationLast)){
			$generate = true;
			$errMessage = "";
			$paperUser = LnExaminationPaperUser::findOne($examinationLast->examination_paper_user_id);
		}else{
			$paperUser = new LnExaminationPaperUser();
		}

		$paperCopy = LnExaminationPaperCopy::findOne($model->examination_paper_copy_id);
		$default_total_score = $paperCopy->default_total_score;

		$resultUser = $service->GetExaminationByUserResultAll($userId, $id, $companyId);

		return $this->render('view', [
			'model' => $model,
			'examinationLast' => $examinationLast,
			'resultUser' => $resultUser,
			'paperUser' => $paperUser,
			'validity' => $validity,
			'errMessage' => $errMessage,
			'generate' => $generate,
			'default_total_score' => $default_total_score,
		]);
	}

	/**
	 * 独立考试学习
	 * @param $result_id
	 * @param null $examination_id
	 * @return string
	 */
	public function actionPlayer($id, $attempt = 1){
		$findResultModel = LnExaminationResultUser::findOne($id);

        if (empty($findResultModel)) {
            return $this->render('view');
        }

		$findFinalResult = LnExaminationResultUser::find(false)->andFilterWhere([
			'examination_id' => $findResultModel->examination_id,
			'user_id' => $findResultModel->user_id,
			'company_id' => $findResultModel->company_id,
			'result_type' => LnExaminationResultUser::RESULT_TYPE_FINALLY
		])->one();

		if ($findResultModel && $findResultModel->examination_status == LnExaminationResultUser::EXAMINATION_STATUS_NOT) {
			$time = time();
			LnExaminationResultUser::updateAll(['examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_START, 'start_at' => $time], "kid=:kid", [':kid' => $id]);
			/*判断成绩是否完成过，完成了则不更新数据*/
			if (!empty($findFinalResult) && $findFinalResult->examination_status == LnExaminationResultUser::EXAMINATION_STATUS_NOT) {
				LnExaminationResultUser::updateAll(['examination_status' => LnExaminationResultUser::EXAMINATION_STATUS_START, 'start_at' => $time, 'examination_duration' => 0], "kid=:kid", [':kid' => $findFinalResult->kid]);
			}
		}
		$service = new ExaminationService();
		$paperQuestion = $service->GetExaminationPaperQuestionUser($findResultModel->examination_id, $findResultModel->examination_paper_user_id);

		$examinationModel = LnExamination::findOne($findResultModel->examination_id);

		$selectOptions = $service->GetResultUserDetailSelect($findResultModel->user_id, $findResultModel->company_id, $findResultModel->examination_id, $findResultModel->kid);

		sort($selectOptions);

		$selectQuestion = $service->GetResultUserDetailSelect($findResultModel->user_id, $findResultModel->company_id, $findResultModel->examination_id, $findResultModel->kid, 'examination_question_user_id');

		sort($selectQuestion);

		return $this->render('player', [
			"examination_id" => $findResultModel->examination_id,
			'attempt' => $attempt,
			'paperQuestion' => $paperQuestion['result'],
			'countPage' => $paperQuestion['page'],
			'examinationModel' => $examinationModel,
			'findResultModel' => $findResultModel,
			'selectOptions' => $selectOptions,
			'selectQuestion' => $selectQuestion,
		]);
	}

	/**
	 * 暂存
	 * @return array
	 */
	public function actionTempSave(){
		$result_id = Yii::$app->request->post('result_id');
		$examination_paper_user_id = Yii::$app->request->post('examination_paper_user_id');
		$examination_id = Yii::$app->request->post('examination_id');
		$options_id = Yii::$app->request->post('options_id');
		$params = Yii::$app->request->post();
		Yii::$app->response->format = Response::FORMAT_JSON;
		if (empty($result_id) || empty($examination_paper_user_id) || empty($examination_id) || !isset($options_id)){
			return ['result' => 'fail', 'errmsg' => Yii::t('frontend','audience_params_error')];
		}
		$user_id = Yii::$app->user->getId();
		$company_id = Yii::$app->user->identity->company_id;
		$service = new ExaminationService();
		$res = $service->tempSave($params, $company_id, $user_id);
		return $res;
	}

	/**
	 * 考试查看
	 * @param $id
	 * @return string
	 */
	public function actionPlayView($id){
		$service = new ExaminationService();
		$data = $service->GetExaminationUserPaper($id);
		$dialog = Yii::$app->request->get('dialog');
		$backBtn = Yii::$app->request->get('backBtn');
		$examination_range = isset($_GET['examination_range']) ? 'True' : 'False';

		return $this->render('play_view', [
			'model' => $data['model'],
			'examination' => $data['examination'],
			'paperQuestion' => $data['result'],
			'countPage' => $data['page'],
			'dialog' => $dialog,
			'backBtn' => $backBtn,
			'examination_range' => $examination_range,
		]);
	}

}
