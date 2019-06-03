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

use common\helpers\TCharsetConv;
use common\services\framework\CompanyMenuService;
use common\services\report\ReportNewService;
use common\models\report\RpStOnlineCourseComp;




class ReportNewController extends BaseFrontController
{
	public $layout = 'frame';
	
	public function actionIndex()
	{
		/*报表*/
		$companyMenuService = new CompanyMenuService();
		$reportMenuModels = $companyMenuService->getCompanyMenuByType(Yii::$app->user->identity->company_id,"report_new");
		$lnReportCount = count($reportMenuModels);
		$resource['lnReportCount'] = $lnReportCount;
		return $this->render('index',['result'=>$reportMenuModels,'count'=>$lnReportCount]);
	}
	
	public function actionPlatformStudy()
	{
		return $this->renderAjax('platform_study');
	}
	
	public function actionHistogram()
	{
		return $this->renderAjax('histogram');
	}
	
	
	public function actionActiveDegree()
	{
		return $this->renderAjax('active_degree');
	}
	
	public function actionOnlineCourseComp()
	{
		return $this->renderAjax('online_course_comp');
	}
	
	public function actionFaceCourseComp()
	{
		return $this->renderAjax('face_course_comp');
	}
	
	public function actionOnlineCourseSeq()
	{
		return $this->renderAjax('online_course_seq');
	}
	
	public function actionStudyScore()
	{
		return $this->renderAjax('study_score');
	}
	
	public function actionPersonalStudy()
	{
		return $this->renderAjax('personal_study');
	}
	
	
	public function actionGetQueryNoShare(){
		
		$reportService=new ReportNewService();
		
		$result=$reportService->getQueryNoShare();
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetQuery(){
	
		$reportService=new ReportNewService();
	
		$result=$reportService->getQuery();
	
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetPlatformStudyData()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportNewService();
		$result=$reportService->getPlatformStudyData($params);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetActiveDegreeData()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportNewService();
		$result=$reportService->getActiveDegreeData($params);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetOnlineCourseCompData()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportNewService();
		$result=$reportService->getOnlineCourseCompData($params);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetFaceCourseCompData()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportNewService();
		$result=$reportService->getFaceCourseCompData($params);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetOnlineCourseSeqData()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportNewService();
		$result=$reportService->getOnlineCourseSeqData($params);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetStudyScoreData()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportNewService();
		$result=$reportService->getStudyScoreData($params);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetPersonalStudyData()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportNewService();
		$result=$reportService->getPersonalStudyData($params);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionExportPlatformStudy()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportNewService();
		$result=$reportService->getPlatformStudyData($params);
		$content = "No.,"
				.Yii::t('frontend', 'month').","
				.Yii::t('frontend', 'rep_login_num').","
				.Yii::t('frontend', 'per_capita').","		
				.Yii::t('frontend', 'rep_reg_num').","
				.Yii::t('frontend', 'per_capita').","			
				.Yii::t('frontend', 'rep_com_num').","
				.Yii::t('frontend', 'per_capita').","			
			    .Yii::t('frontend', 'rep_duration').","
			    .Yii::t('frontend', 'per_capita').","	
			    .Yii::t('frontend', 'rep_certif_num').","					
			    .Yii::t('frontend', 'per_capita')."\n";
		$i = 1;
		foreach ($result['platformStudy'] as $res) {
			$content .= $i . ',' .
					$res['month'] .Yii::t('frontend', 'month2')  . ',' .
					$res['login_num']  . ',' .
					$res['login_num_rate']  . ',' .
					$res['reg_num']  . ',' .
					$res['reg_num_rate']  . ',' .
					$res['com_num']  . ',' .
					$res['com_num_rate']  . ',' .
					$res['duration']  . ',' .
					$res['duration_rate']  . ',' .
					$res['certif_num']  . ',' .
					$res['certif_num_rate']  . "\n";
			$i++;
		}
	
		$this->exportIO($content, "PlatformStudy-".date('Y-m-d',time()));
	}
	
	public function actionExportActiveDegree()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportNewService();
		//导出全部类型
		$params['type_param']=0;
		$result=$reportService->getActiveDegreeData($params);
		$content = "No.,"
				.Yii::t('frontend', 'month').","
				.Yii::t('frontend', 'login_users').","
				.Yii::t('frontend', 'login_users_rate')."%".","
				.Yii::t('frontend', 'rep_login_num').","
				.Yii::t('frontend', 'rep_pc_login_num').","
				.Yii::t('frontend', 'rep_weixin_login_num').","
				.Yii::t('frontend', 'rep_app_login_num')."\n";
		$i = 1;
// 		foreach ($result['activeDegree'] as $res) {
// 			$content .= $i . ',' .
// 					$res['op_time'] . ',' .
// 					$res['login_user_num']  . ',' .
// 					$res['login_user_num_rate'] ."%" . ',' .
// 					$res['login_num']  . "\n";
// 			$i++;
// 		}

		for ($x=0; $x<count($result['activeDegree']); $x++) {
			
			$activeDegreeArr=$result['activeDegree'];		
			$activeChartArr=$result['chart'];
			
			$activeDegree=$activeDegreeArr[$x];
			$activeChart=$activeChartArr[$x];
			$content .= $i . ',' .
					$activeDegree['op_time'] . ',' .
					$activeDegree['login_user_num']  . ',' .
					$activeDegree['login_user_num_rate'] ."%" . ',' .
					$activeDegree['login_num']  . ',' .
					$activeChart['pc']  . ',' .
					$activeChart['weixin']  . ',' .
					$activeChart['app']  . "\n";
			$i++;
		}
	
		$this->exportIO($content, "ActiveDegree-".date('Y-m-d',time()));
	}
	
	
	public function actionExportOnlineCourseComp()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportNewService();
		$typeVal="";
		if($params['type_param']==RpStOnlineCourseComp::TYPE_COMPANY){
			$typeVal=Yii::t('common', 'company');
		}else if($params['type_param']==RpStOnlineCourseComp::TYPE_DOMAIN){
			$typeVal= Yii::t('common', 'domain') ;
		}else if($params['type_param']==RpStOnlineCourseComp::TYPE_REPORTING_MANAGER){
			$typeVal=Yii::t('common', 'reporting_manager');
		}
	
		$result=$reportService->getOnlineCourseCompData($params);
		$content = "No.,"
				.$typeVal.","
				.Yii::t('frontend', 'rep_total_user').","
				.Yii::t('frontend', 'register_number').","
				.Yii::t('frontend', 'finish_number').","
				.Yii::t('frontend', 'rep_comp_rate').'(%)'.","
				.Yii::t('frontend', 'rep_avg_score')."\n";
		$i = 1;
		foreach ($result['onlineCourseComp'] as $res) {
			$content .= $i . ',' .
					$res['display_val'] . ',' .
					$res['total_user_num']  . ',' .
					$res['reg_num']  . ',' .
					$res['com_num']  . ',' .
					$res['com_num_rate']  . ',' .
					$res['score']  . "\n";
			$i++;
		}
	
		$this->exportIO($content, "OnlineCourseComp-".date('Y-m-d',time()));
	}
	
	public function actionExportFaceCourseComp()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportNewService();
		
		$typeVal="";
		if($params['type_param']==RpStOnlineCourseComp::TYPE_COMPANY){
			$typeVal=Yii::t('common', 'company');
		}else if($params['type_param']==RpStOnlineCourseComp::TYPE_DOMAIN){
			$typeVal= Yii::t('common', 'domain') ;
		}else if($params['type_param']==RpStOnlineCourseComp::TYPE_REPORTING_MANAGER){
			$typeVal=Yii::t('common', 'reporting_manager');
		}
		
		$result=$reportService->getFaceCourseCompData($params);
		$content = "No.,"
				.$typeVal.","
				.Yii::t('frontend', 'rep_total_user').","
				.Yii::t('frontend', 'rep_enroll_num').","
				.Yii::t('frontend', 'finish_number').","
				.Yii::t('frontend', 'rep_rate').","
				.Yii::t('frontend', 'rep_not_qualify').","
				.Yii::t('frontend', 'rep_rate').","
				.Yii::t('frontend', 'rep_certification').","
				.Yii::t('frontend', 'rep_rate')."\n";
		$i = 1;
		foreach ($result['faceCourseComp'] as $res) {
			$content .= $i . ',' .
					$res['display_val'] .',' .
					$res['total_user_num']  . ',' .
					$res['reg_num']  . ',' .
					$res['com_num']  . ',' .
					$res['com_num_rate']  . ',' .
					$res['not_qualify']  . ',' .
					$res['not_qualify_rate']  . ',' .
					$res['certification']  . ',' .
					$res['certification_rate']  . "\n";
			$i++;
		}
	
		$this->exportIO($content, "FaceCourseComp-".date('Y-m-d',time()));
	}
	
	public function actionExportOnlineCourseSeq()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportNewService();
	
		
		$result=$reportService->getOnlineCourseSeqData($params);
		$content = "No.,"
				.Yii::t('frontend', 'date_text').","
				.Yii::t('frontend', 'rep_total_user').","
				.Yii::t('frontend', 'register_number').","
				.Yii::t('frontend', 'finish_number').","
				.Yii::t('frontend', 'course_completion_rate').","
				.Yii::t('frontend', 'grade_average')."\n";
		$i = 1;
		foreach ($result['onlineCourseSeq'] as $res) {
			$content .= $i . ',' .
					$res['op_time'] .',' .
					$res['total_user_num']  . ',' .
					$res['reg_num']  . ',' .
					$res['com_num']  . ',' .
					$res['com_num_rate']  . ',' .
					$res['score']  . "\n";
			$i++;
		}
	
		$this->exportIO($content, "OnlineCourseSeq-".date('Y-m-d',time()));
	}
	
	public function actionExportStudyScore()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportNewService();
		$cour=$reportService->getCourseInfo($params['course_id']);

		$courseName=$cour->course_name;
		$result=$reportService->getStudyScoreData($params);
		$content = "No.,"
				.Yii::t('common', 'course_name').","
				.Yii::t('common', 'real_name').","
				.Yii::t('frontend', 'top_mail_text').","
				.Yii::t('common', 'mobile_no').","						
				.Yii::t('common', 'department').","
				.Yii::t('common', 'position').","
				.Yii::t('common', 'reporting_manager').","	
				.Yii::t('frontend', 'signup_time').","
				.Yii::t('frontend', 'exam_choose_wanchengshijian').","				
				.Yii::t('common', 'examination_score')."\n";
		$i = 1;
		foreach ($result['studyScore'] as $res) {
			$content .= $i . ',' .
					$courseName .',' .
					$res['real_name'] .',' .
					$res['email']  . ',' .
					$res['mobile_no']  . ',' .
					$res['orgnization_name']  . ',"' .
					$res['position_name']  . '",' .
					$res['reporting_manager_name']  . ',' .
					date("Y年m月d日 H:i:s",$res['reg_time'])  . ',' .
					date("Y年m月d日 H:i:s",$res['comp_time'])  . ',' .
					$res['score']  . "\n";
			$i++;
		}
	
		$this->exportIO($content, "StudyScore-".date('Y-m-d',time()));
	}
	
	public function actionExportPersonalStudy()
	{
		$params=Yii::$app->request->get();
		$reportService=new ReportNewService();
		
		$result=$reportService->getPersonalStudyData($params);
		$content = "No.,"
				.Yii::t('common', 'real_name').","
				.Yii::t('common', 'department').","
				.Yii::t('common', 'position').","
				.Yii::t('common', 'reporting_manager').","
				.Yii::t('frontend', 'learning_time_total').","
				.Yii::t('frontend', 'register_course_number').","		
				.Yii::t('frontend', 'register_finish_number').","
				.Yii::t('frontend', 'rep_certification_num')."\n";
		$i = 1;
		foreach ($result['personalStudy'] as $res) {
			$content .= $i . ',' .
					$res['real_name'] .',' .
					$res['orgnization_name']  . ',"' .
					$res['position_name']  . '",' .
					$res['reporting_manager_name']  . ',' .
					$res['duration']  . ',' .
					$res['reg_num']  . ',' .
					$res['com_num']  . ',' .
					$res['certification']  . "\n";
			$i++;
		}
	
		$this->exportIO($content, "PersonalStudy-".date('Y-m-d',time()));
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
	
	public function actionGetCourses(){
	
		$params=Yii::$app->request->get();
	
		$reportService=new ReportNewService();
	
		$result=$reportService->searchCourseByKeyword($params);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	/**
	 * 非共享域
	 * @return Ambigous <multitype:boolean , multitype:boolean multitype: >
	 */
	public function actionGetNoCourses(){
	
		$params=Yii::$app->request->get();
	
		$reportService=new ReportNewService();
		
		$params['noshare']='1';
	
		$result=$reportService->searchCourseByKeyword($params);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetUsers(){
	
		$params=Yii::$app->request->get();
		$domain_id=$params['domain_id'];
		$keyword=$params['q'];
		$reportService=new ReportNewService();
	
		$result=$reportService->searchUserByKeyword($domain_id, $keyword);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionGetExtInfos(){
	
		$params=Yii::$app->request->get();
		
		$keyword=$params['q'];
		$reportService=new ReportNewService();
	
		$result=$reportService->searchExtInfosByKeyword($keyword);
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $result;
	}
	
	public function actionCourseInfos(){
		$params=Yii::$app->request->get();
		$reportService=new ReportNewService();
		$pinfolist = $reportService->search_pub($params);
		$pinfolist['type']=$params['type'];
		return $this->renderAjax('view_info',$pinfolist);
	}
	
	
	
}
