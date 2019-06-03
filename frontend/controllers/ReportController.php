<?php


namespace frontend\controllers;


use Yii;
use frontend\base\BaseFrontController;

use yii\data\Pagination;
use common\models\framework\FwUser;

use yii\web\Response;
use components\widgets\TPagination;

use common\helpers\TTimeHelper;

use yii\helpers\ArrayHelper;
use common\services\report\ReportService;
use common\helpers\TCharsetConv;
use common\services\framework\CompanyMenuService;




class ReportController extends BaseFrontController
{
	public $layout = 'frame';
	
	public function actionIndex()
	{
		/*报表*/
		$companyMenuService = new CompanyMenuService();
		$reportMenuModels = $companyMenuService->getCompanyMenuByType(Yii::$app->user->identity->company_id,"report");
		$lnReportCount = count($reportMenuModels);
		$resource['lnReportCount'] = $lnReportCount;
		return $this->render('index',['result'=>$reportMenuModels,'count'=>$lnReportCount]);
	}
	
	public function actionStudyConditionDay()
	{
	
		return $this->renderAjax('study_condition_day');
	}
	
	public function actionUserStudyConditionGather()
	{
	
		return $this->renderAjax('user_study_condition_gather');
	}
	
	public function actionCourseUserScore()
	{
	
		return $this->renderAjax('course_user_score');
	}
	
	public function actionCourseStudyConditionDay()
	{
	
		return $this->renderAjax('course_study_condition_day');
	}
	
	public function actionStatisticalAnalysis()
	{
		
		return $this->renderAjax('statistical_analysis');
	}
	
	public function actionActivityDegree()
	{
	
		return $this->renderAjax('activity_degree');
	}
	
	public function actionCourseReport()
	{
	
		return $this->renderAjax('course_report');
	}
	
	public function actionGetQuery(){
		
		$reportService=new ReportService();
		
		$result=$reportService->getQuery();
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetCourseStudyConditionDayData()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportService();
		$result=$reportService->getCourseStudyConditionDayData($params);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetCourseUserScoreData()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportService();
		$result=$reportService->getCourseUserScoreData($params);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionExportCourseUserScore()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportService();
		$result=$reportService->getCourseUserScoreData($params);
		$content = "No.,".Yii::t('frontend', 'register_name').",".Yii::t('common', 'mobile_no').",".Yii::t('frontend', 'department')
		.",".Yii::t('frontend', 'position').",".Yii::t('frontend', 'signup_time').",".Yii::t('frontend', 'exam_choose_wanchengshijian').",".
		Yii::t('frontend', 'exam_score')."\n";
		$i = 1;
		foreach ($result as $res) {
			$content .= $i . ',' .
					$res['user_id']  . ',' .
					$res['mobile_no']  . ',' .
					$res['orgnization_name']  . ',"' .
					$res['position_name']  . '",' .
					$res['reg_time']  . ',' .
					$res['comp_time']  . ',' .
					$res['score']  . "\n";
			$i++;
		}
	
		$this->exportIO($content, "CourseUserScore-".date('Y-m-d',time()));
	}
	
	public function actionGetStudyConditionDayData()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportService();
		$result=$reportService->getStudyConditionDayData($params);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionExportCourseStudyConditionDay()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportService();
		$result=$reportService->getCourseStudyConditionDayData($params);
		$content = "No.,".Yii::t('common', 'time').",".Yii::t('frontend', 'register_number').",".Yii::t('frontend', 'finish_number')
		.",".Yii::t('frontend', 'course_completion_rate').",".Yii::t('frontend', 'grade_average')."\n";
		$i = 1;
		foreach ($result as $res) {
			$content .= $i . ',' .
					$res['time']  . ',' .
					$res['reg_user_num']  . ',' .
					$res['comp_user_num']  . ',' .
					$res['comp_rate']  . ',' .
					$res['score']  . "\n";
			$i++;
		}
	
		$this->exportIO($content, "CourseStudyCondition-".date('Y-m-d',time()));
	}
	
	
	
	public function actionExportStudyConditionDay()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportService();
		$result=$reportService->getStudyConditionDayData($params);
		
		$content = "No.,".Yii::t('common', 'time').",".Yii::t('frontend', 'login_times2_today').",".Yii::t('frontend', 'login_times2_today_rate')
		.",".Yii::t('frontend', 'learning_time_total_today').",".Yii::t('frontend', 'course_rate_higest')
		.",".Yii::t('frontend', 'course_learn_most')."\n";
		$i = 1;
		foreach ($result as $res) {
			$content .= $i . ',' .
					$res['time']  . ',' .
					$res['log_user_num']  . ',' .
					$res['log_user_rate']  . ',' .
					$res['acc_study_time']  . ',' .
					$res['max_acc_comment_course']  . ',' .
					$res['max_acc_study_course']  . "\n";
			$i++;
		}
		
		$this->exportIO($content, "StudyConditionDay-".date('Y-m-d',time()));
	}
	
	public function actionGetUserStudyConditionGatherData()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportService();
		$result=$reportService->getUserStudyConditionGatherData($params);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	
	public function actionExportUserStudyConditionGather()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportService();
		$result=$reportService->getUserStudyConditionGatherData($params);
		
		
		$content = "No.,".Yii::t('common', 'real_name').",".Yii::t('frontend', 'department').",".Yii::t('frontend', 'position')
		.",".Yii::t('frontend', 'login_times_total').",".Yii::t('frontend', 'login_last_time')
		.",".Yii::t('frontend', 'learning_time_total').",".Yii::t('frontend', 'register_course_number')
		.",".Yii::t('frontend', 'register_finish_number').",".Yii::t('frontend', 'course_must_rate')
		.",".Yii::t('frontend', 'course_must_average')."\n";
		$i = 1;
		foreach ($result as $res) {
			$content .= $i . ',' .
					$res['user_id']  . ',' .
					$res['orgnization_name']  . ',' .
					$res['position_name']  . ',' .
					$res['login_number']  . ',' .
					$res['last_login_at']  . ',' .
					$res['acc_study_time']  . ',' .
					$res['reg_course_num']  . ',' .
					$res['comp_course_num']  . ',' .
					$res['obliga_course_comp_rate']  . ',' .
					$res['obliga_course_score']  . "\n";
			$i++;
		}
		
		$this->exportIO($content, "UserStudyConditionGather-".date('Y-m-d',time()));
	
	}
	
	public function exportIO($content,$name){
		
		$conv=new TCharsetConv('utf-8','utf-8bom');
		$content = $conv->convert($content);
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream; charset=gb2312');
		header('Content-Disposition: attachment; filename=' . urlencode(  $name.'.csv'));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . strlen($content));
		ob_clean();
		flush();
		echo $content;
	}
	
	
	public function actionGetStatisticalAnalysisData(){
		
		$time_param=Yii::$app->request->getQueryParam('time_param');
		$domain_param=Yii::$app->request->getQueryParam('domain_param');
		
		$reportService=new ReportService();
		$result=$reportService->getStatisticalAnalysisData($time_param,$domain_param);
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
		
	}
	
	public function actionGetActivityDegreeData(){
	
		$time_param=Yii::$app->request->getQueryParam('time_param');
		$domain_param=Yii::$app->request->getQueryParam('domain_param');
	
		$reportService=new ReportService();
		$result=$reportService->getActivityDegreeData($time_param,$domain_param);
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	
	}
	
	public function actionGetCourseReportData(){
	
		$time_param=Yii::$app->request->getQueryParam('time_param');
		$domain_param=Yii::$app->request->getQueryParam('domain_param');
		$course_param=Yii::$app->request->getQueryParam('course_param');
	
		$reportService=new ReportService();
		$result=$reportService->getGetCourseReportData($time_param,$domain_param,$course_param);
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	
	}
	
	public function actionGetCourses(){
		
		$params=Yii::$app->request->get();
		$domain_id=$params['domain_id'];
		$keyword=$params['q'];
		$reportService=new ReportService();
		
		$result=$reportService->searchCourseByKeyword($domain_id, $keyword);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetUsers(){
	
		$params=Yii::$app->request->get();
		$domain_id=$params['domain_id'];
		$keyword=$params['q'];
		$reportService=new ReportService();
	
		$result=$reportService->searchUserByKeyword($domain_id, $keyword);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	
	public function actionGetCourseStudyConditionDayYear(){
	
		$reportService=new ReportService();
	
		$result=$reportService->getCourseStudyConditionDayDataTimeList();
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetCourseStudyConditionDayMonth(){
	
		$reportService=new ReportService();
		$params=Yii::$app->request->get();
		$result=$reportService->getCourseStudyConditionDayDataMonthList($params);
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetCourseUserScoreYear(){
	
		$reportService=new ReportService();
	
		$result=$reportService->getCourseUserScoreDataTimeList();
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetCourseUserScoreMonth(){
	
		$reportService=new ReportService();
		$params=Yii::$app->request->get();
		$result=$reportService->getCourseUserScoreDataMonthList($params);
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetStudyConditionDayYear(){
	
		$reportService=new ReportService();
	
		$result=$reportService->getStudyConditionDayDataTimeList();
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetStudyConditionDayMonth(){
	
		$reportService=new ReportService();
		$params=Yii::$app->request->get();
		$result=$reportService->getStudyConditionDayDataMonthList($params);
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	
	
	
}
