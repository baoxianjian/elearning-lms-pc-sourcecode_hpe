<?php

namespace common\services\report;


use Yii;

use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\db\Query;
use common\services\framework\UserDomainService;
use common\models\report\RpStAnalysisData;
use common\models\report\RpStActiDegreeData;
use common\models\report\RpStCourseReportData;
use common\models\learning\LnResourceDomain;
use common\models\learning\LnCourse;
use common\models\report\RpStCourseDayData;
use common\models\report\RpStCourseScoreData;
use common\models\report\RpStConDayData;
use common\models\report\RpStUserConData;
use common\models\framework\FwUser;
use common\models\framework\FwUserDisplayInfo;

class ReportService
{

	function getMonth($y, $m)
	{
		if ($m < 10) {
			return $y . '0' . $m;
		} else {
			return $y . $m;
		}
	}

	public function getActivityDegreeData($time_param, $domain_param)
	{
		$datas = [];

		$chart = RpStActiDegreeData::find(false)
			->andFilterWhere(['=', 'year', $time_param])
			->andFilterWhere(['=', 'domain_id', $domain_param])
			->addOrderBy(['month' => SORT_ASC])
			->asArray()
			->all();


		$chart_label = [];
		$chart_login_number = [];
		$chart_login_user_num = [];
		$chart_active_user = [];


		$first_obj = $chart[0];
		$previous = [];
		if ($first_obj['month'] == 1) {
			$previous_year = $first_obj['year'] - 1;
			$previous = RpStActiDegreeData::find(false)
				->andFilterWhere(['=', 'year', $previous_year])
				->andFilterWhere(['=', 'domain_id', $domain_param])
				->andFilterWhere(['=', 'month', 12])
				->addOrderBy(['month' => SORT_ASC])
				->asArray()
				->one();
		}

		$chart_tmp = [];

		if (count($previous) == 0) {
			foreach ($chart as $ch) {

				$ch['login_user_num'] = $ch['login_user_num'] == null ? 0 : $ch['login_user_num'];
				$ch['active_user'] = $ch['active_user'] == null ? 0 : $ch['active_user'];
				$ch['login_number'] = $ch['login_number'] == null ? 0 : $ch['login_number'];

				array_push($chart_label, $this->getMonth($time_param, $ch['month']));
				array_push($chart_login_user_num, $ch['login_user_num']);
				array_push($chart_active_user, $ch['active_user']);
				$login_number_tmp = 0;
				if (!$previous) {
					$login_number_tmp = $ch['login_number'] - 0;
				} else {
					$login_number_tmp = $ch['login_number'] - $previous['login_number'];
				}

				array_push($chart_login_number, $login_number_tmp);
				$previous['login_number'] = $ch['login_number'];

				$ch['login_number'] = $login_number_tmp;
				array_push($chart_tmp, $ch);

			}
		} else {
			foreach ($chart as $ch) {

				$ch['login_user_num'] = $ch['login_user_num'] == null ? 0 : $ch['login_user_num'];
				$ch['active_user'] = $ch['active_user'] == null ? 0 : $ch['active_user'];
				$ch['login_number'] = $ch['login_number'] == null ? 0 : $ch['login_number'];

				array_push($chart_label, $this->getMonth($time_param, $ch['month']));
				array_push($chart_login_user_num, $ch['login_user_num']);
				array_push($chart_active_user, $ch['active_user']);

				$login_number_tmp = $ch['login_number'] - $previous['login_number'];
				array_push($chart_login_number, $login_number_tmp);
				$previous['login_number'] = $ch['login_number'];

				$ch['login_number'] = $login_number_tmp;
				array_push($chart_tmp, $ch);
			}
		}

		$datas['label'] = $chart_label;
		$datas['login_number'] = $chart_login_number;
		$datas['login_user_num'] = $chart_login_user_num;
		$datas['active_user'] = $chart_active_user;
		$datas['statisticalAnalysis'] = $chart_tmp;

		return $datas;

	}
	
	
	public function getCourseUserScoreData($param){
		$datas = [];
		$datas_tmp=[];
		$m_table=RpStCourseScoreData::tableName();
		
		$datas_tmp=RpStCourseScoreData::find(false)
		    ->innerjoin(FwUserDisplayInfo::tableName().' as t1','t1.user_id = '.$m_table.".user_id")
			->andFilterWhere(['=', 'year', $param['year']])
			->andFilterWhere(['=', 'month', $param['month']])
			->andFilterWhere(['=', $m_table.'.domain_id', $param['domain_id']])
			->andFilterWhere(['=', 'course_id', $param['course_id']])
			->orderBy('time')
			->select("t1.real_name as user_id,t1.email,reg_time,comp_time,t1.mobile_no,
					score,t1.orgnization_name,t1.position_name")
			->asArray()
			->all();
		
		foreach ($datas_tmp as $ch) {
		
			$ch['position_name'] = $ch['position_name'] == null ? '无' : $ch['position_name'];
			$ch['orgnization_name'] = $ch['orgnization_name'] == null ? '无' : $ch['orgnization_name'];
			$ch['reg_time'] = $ch['reg_time'] == null ? '未注册' : date("Y-m-d H:i:s",$ch['reg_time']);
			$ch['comp_time'] = $ch['comp_time'] == null ? '未完成' :date("Y-m-d H:i:s",$ch['comp_time']);
			$ch['score'] = $ch['score'] == null ? 0 : $ch['score'];
				
			$ch['score'] = round($ch['score'], 1);
			
			$ch['user_id'] = $ch['user_id']."(".$ch['email'].")";
				
			array_push($datas, $ch);
		}
	
		return $datas;
	}
	
	public function getStudyConditionDayData($param){
		$datas = [];
		
		$datas_tmp=[];
		
		$datas_tmp=RpStConDayData::find(false)
		->andFilterWhere(['=', 'year', $param['year']])
		->andFilterWhere(['=', 'month', $param['month']])
		->andFilterWhere(['=', 'domain_id', $param['domain_id']])
		->orderBy('time')
		->asArray()
		->all();
		
		foreach ($datas_tmp as $ch) {
		
			$ch['log_user_num'] = $ch['log_user_num'] == null ? '当天无用户登陆' : $ch['log_user_num'];
			$ch['log_user_rate'] = $ch['log_user_rate'] == null ? '当天无用户登陆' : round($ch['log_user_rate'] *100, 1).'%';
			$ch['acc_study_time'] = $ch['acc_study_time'] == null ? 0 : $ch['acc_study_time'];
			$ch['max_acc_comment_course'] = $ch['max_acc_comment_course'] == null ? '无' : $ch['max_acc_comment_course'];
			$ch['max_acc_study_course'] = $ch['max_acc_study_course'] == null ? '无' : $ch['max_acc_study_course'];
			
			$ch['acc_study_time'] = round($ch['acc_study_time'] / 60 / 60, 2);
			
			array_push($datas, $ch);
		}
	
		return $datas;
	}
	
	public function getUserStudyConditionGatherData($param){
		$datas = [];
		$datas_tmp=[];
		$m_table=RpStUserConData::tableName();
		
		$query=RpStUserConData::find(false)
			->innerjoin(FwUserDisplayInfo::tableName().' as t1','t1.user_id = '.$m_table.".user_id")
			->innerjoin(FwUser::tableName().' as t2','t2.kid = '.$m_table.".user_id")
			->andFilterWhere(['=', $m_table.'.domain_id', $param['domain_id']])
			;
		if(trim($param['user_id'])!=""){
			$query_user_id=$param['user_id'];
			//$query_user_id=ltrim($param['user_id'],"'");
			//$query_user_id=rtrim($query_user_id,"'");
			$query->andWhere($m_table.'.user_id in (' . $query_user_id . ')');
		}
		
		$datas_tmp=$query
			->select("t1.real_name as user_id,acc_study_time,reg_course_num,comp_course_num,obliga_course_comp_rate,obliga_course_score,
					t1.orgnization_name,t1.position_name,t2.last_login_at,t2.login_number,t1.email")
			->asArray()
			->all();
		
		foreach ($datas_tmp as $ch) {
			
			$ch['position_name'] = $ch['position_name'] == null ? '无' : $ch['position_name'];
			$ch['orgnization_name'] = $ch['orgnization_name'] == null ? '无' : $ch['orgnization_name'];		
			$ch['login_number'] = $ch['login_number'] == null ? 0 : $ch['login_number'];				
			$ch['last_login_at'] = $ch['last_login_at'] == null ? '未登陆' : date("Y-m-d H:i:s",$ch['last_login_at']);
			
			$ch['acc_study_time'] = $ch['acc_study_time'] == null ? 0 : $ch['acc_study_time'];
			$ch['reg_course_num'] = $ch['reg_course_num'] == null ? 0 : $ch['reg_course_num'];
			$ch['comp_course_num'] = $ch['comp_course_num'] == null ? 0 : $ch['comp_course_num'];
			$ch['obliga_course_comp_rate'] = $ch['obliga_course_comp_rate'] == null ? 0 : $ch['obliga_course_comp_rate'];
			$ch['obliga_course_score'] = $ch['obliga_course_score'] == null ? 0 : $ch['obliga_course_score'];
				
			
			$ch['acc_study_time'] = round($ch['acc_study_time'] / 60 / 60,2);
			$ch['obliga_course_comp_rate'] = round($ch['obliga_course_comp_rate'] *100, 1).'%';
			
			$ch['user_id']=$ch['user_id']."(".$ch['email'].")";
				
			array_push($datas, $ch);
		}
		
		return $datas;
	}
	
	
	public function getCourseStudyConditionDayData($param){
		$datas = [];
		
		$datas_tmp=[];
		$datas_tmp=RpStCourseDayData::find(false)
			->andFilterWhere(['=', 'year', $param['year']])
			->andFilterWhere(['=', 'month', $param['month']])
			->andFilterWhere(['=', 'domain_id', $param['domain_id']])
			->andFilterWhere(['=', 'course_id', $param['course_id']])
			->asArray()
			->orderBy('time')
			->all();
		
		
		foreach ($datas_tmp as $ch) {
		
			$ch['reg_user_num'] = $ch['reg_user_num'] == null ? 0 : $ch['reg_user_num'];
			$ch['comp_user_num'] = $ch['comp_user_num'] == null ? 0 : $ch['comp_user_num'];
			$ch['comp_rate'] = $ch['comp_rate'] == null ? 0 : $ch['comp_rate'];
			$ch['score'] = $ch['score'] == null ? 0 : $ch['score'];
				
			$ch['comp_rate'] = round($ch['comp_rate']*100, 1).'%';
			$ch['score'] = round($ch['score'], 1);
				
			array_push($datas, $ch);
		}
		
		return $datas;
	}

	public function getStatisticalAnalysisData($time_param, $domain_param)
	{
		$datas = [];

		$chart = RpStAnalysisData::find(false)
			->andFilterWhere(['=', 'year', $time_param])
			->andFilterWhere(['=', 'domain_id', $domain_param])
			->addOrderBy(['month' => SORT_ASC])
			->asArray()
			->all();


		$chart_label = [];
		$chart_reg_course = [];
		$chart_comp_course = [];
		$chart_learning_time = [];
		$chart_tmp = [];

		foreach ($chart as $ch) {

			$ch['reg_course'] = $ch['reg_course'] == null ? 0 : $ch['reg_course'];
			$ch['comp_course'] = $ch['comp_course'] == null ? 0 : $ch['comp_course'];
			$ch['learning_time'] = $ch['learning_time'] == null ? 0 : $ch['learning_time'];
			$ch['learning_time'] = round($ch['learning_time'] / 60 / 60, 2);
			array_push($chart_label, $this->getMonth($time_param, $ch['month']));
			array_push($chart_reg_course, $ch['reg_course']);
			array_push($chart_comp_course, $ch['comp_course']);
			array_push($chart_learning_time, $ch['learning_time']);

			array_push($chart_tmp, $ch);
		}


		$datas['label'] = $chart_label;
		$datas['reg_course'] = $chart_reg_course;
		$datas['comp_course'] = $chart_comp_course;
		$datas['learning_time'] = $chart_learning_time;
		$datas['statisticalAnalysis'] = $chart_tmp;

		return $datas;

	}

	public function getGetCourseReportData($time_param, $domain_param, $course_param)
	{
		$datas = [];

		$chart = RpStCourseReportData::find(false)
			->andFilterWhere(['=', 'year', $time_param])
			->andFilterWhere(['=', 'domain_id', $domain_param])
			->andFilterWhere(['=', 'course_id', $course_param])
			->addOrderBy(['month' => SORT_ASC])
			->asArray()
			->all();


		$chart_label = [];
		$chart_coverage = [];
		$chart_completion_rate = [];
		$chart_tmp = [];


		foreach ($chart as $ch) {

			$ch['coverage'] = $ch['coverage'] == null ? 0.0 : $ch['coverage'];
			$ch['completion_rate'] = $ch['completion_rate'] == null ? 0.0 : $ch['completion_rate'];

			$coverage_tmp = strval($ch['coverage'] * 100) . '%';
			$completion_rate_tmp = strval($ch['completion_rate'] * 100) . '%';
			array_push($chart_label, $this->getMonth($time_param, $ch['month']));
			array_push($chart_coverage, $ch['coverage'] * 100);
			array_push($chart_completion_rate, $ch['completion_rate'] * 100);

			$ch['coverage'] = $coverage_tmp;
			$ch['completion_rate'] = $completion_rate_tmp;
			array_push($chart_tmp, $ch);

		}

		$datas['label'] = $chart_label;
		$datas['coverage'] = $chart_coverage;
		$datas['completion_rate'] = $chart_completion_rate;

		$datas['statisticalAnalysis'] = $chart_tmp;

		return $datas;

	}

	public function getQuery()
	{
		$datas = [];
		$years = $this->getTimeList();
		if (empty($years)) {
			$now['year'] = date('Y', time());
			array_push($years, $now);
		}

		$domains = $this->getDomains();

		$datas['years'] = $years;

		$datas['domains'] = $domains;

		return $datas;
	}


	public function getDomains()
	{
		$user_id = Yii::$app->user->getId();
		$userDomainService = new UserDomainService();

		$domainIds = $userDomainService->getSearchListByUserId($user_id);

		$domains = [];
		if (isset($domainIds) && $domainIds != null) {
			foreach ($domainIds as $d) {
				$domain = [];
				$domain['kid'] = $d->kid;
				$domain['domain_name'] = $d->domain_name;
				$domain['share_flag'] = $d->share_flag;
				array_push($domains, $domain);
			}
		}

		return $domains;
	}

	public function getTimeList()
	{
		$chart = RpStAnalysisData::find(false)
			->groupBy("year")
			->select("YEAR")
			->asArray()
			->addOrderBy(['year' => SORT_DESC])
			->all();
		return $chart;
	}

	public function searchCourseByKeyword($domain_id, $keyword)
	{
		$result = array();

		$currentTime = time();

		$domainQuery = LnResourceDomain::find(false);
		$domainQuery->select('resource_id')
			->andFilterWhere(['=', 'domain_id', $domain_id])
			->andFilterWhere(['=', 'status', LnResourceDomain::STATUS_FLAG_NORMAL])
			->andFilterWhere(['=', 'resource_type', LnResourceDomain::RESOURCE_TYPE_COURSE])
			->distinct();

		$domainQuerySql = $domainQuery->createCommand()->rawSql;

		$courseQuery = LnCourse::find(false);
		$courseQuery
			->andWhere('kid in (' . $domainQuerySql . ')')
			->andFilterWhere(['=', 'status', LnCourse::STATUS_FLAG_NORMAL])
			->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES])
			//->andFilterWhere(['=', 'course_type', LnCourse::COURSE_TYPE_ONLINE])
			//->andFilterWhere(['or', ['>=', 'end_time', $currentTime], 'end_time is null'])
			->andFilterWhere(['or', ['<=', 'start_time', $currentTime], 'start_time is null']);

		//关键字搜索
		if (trim($keyword)) {
			$courseQuery->andFilterWhere(['or', ['like', 'course_name', $keyword], ['like', 'course_desc_nohtml', $keyword]]);
		}


		$courses = $courseQuery->orderBy('updated_at desc')
			->asArray()
			->all();

		$arr_title = [];
		foreach ($courses as $r) {
			$t['title'] = $r['course_name'];
			$t['uid'] = $r['kid'];
			Array_push($arr_title, $t);
		}
		$result['results'] = $arr_title;
		$result['success'] = true;

		return $result;
	}
	
	public function searchUserByKeyword($domain_id, $keyword)
	{
		$result = array();
		$companyId = Yii::$app->user->identity->company_id;
		
		$courseQuery=FwUser::find(false)
		  ->andFilterWhere(['=', 'status', FwUser::STATUS_FLAG_NORMAL])
		  ->andFilterWhere(['=', 'company_id', $companyId])
		  ->andFilterWhere(['=', 'domain_id', $domain_id]);
	
		//关键字搜索
		if (trim($keyword)) {
			$courseQuery->andFilterWhere( ['like', 'real_name', $keyword]);
		}
	
		$users = $courseQuery
		->asArray()
		->all();
	
		$arr_title = [];
		foreach ($users as $r) {
			$t['title'] = $r['real_name']."(".$r['email'].")";
			$t['uid'] = $r['kid'];
			Array_push($arr_title, $t);
		}
		$result['results'] = $arr_title;
		$result['success'] = true;
	
		return $result;
	}
	
	public function getCourseStudyConditionDayDataTimeList()
	{
		$chart = RpStCourseDayData::find(false)
		->groupBy("year")
		->select("YEAR")
		->asArray()
		->addOrderBy(['year' => SORT_DESC])
		->all();
		return $chart;
	}
	
	public function getCourseStudyConditionDayDataMonthList($params)
	{
		$chart = RpStCourseDayData::find(false)
		->andFilterWhere(['=', 'year', $params['YEAR']])
		->groupBy("month")
		->select("MONTH")
		->asArray()
		->addOrderBy(['month' => SORT_ASC])
		->all();
		return $chart;
	}
	
	public function getCourseUserScoreDataTimeList()
	{
		$chart = RpStCourseScoreData::find(false)
		->groupBy("year")
		->select("YEAR")
		->asArray()
		->addOrderBy(['year' => SORT_DESC])
		->all();
		return $chart;
	}
	
	public function getCourseUserScoreDataMonthList($params)
	{
		$chart = RpStCourseScoreData::find(false)
		->andFilterWhere(['=', 'year', $params['YEAR']])
		->groupBy("month")
		->select("MONTH")
		->asArray()
		->addOrderBy(['month' => SORT_ASC])
		->all();
		return $chart;
	}
	
	public function getStudyConditionDayDataTimeList()
	{
		$chart = RpStConDayData::find(false)
		->groupBy("year")
		->select("YEAR")
		->asArray()
		->addOrderBy(['year' => SORT_DESC])
		->all();
		return $chart;
	}
	
	public function getStudyConditionDayDataMonthList($params)
	{
		$chart = RpStConDayData::find(false)
		->andFilterWhere(['=', 'year', $params['YEAR']])
		->groupBy("month")
		->select("MONTH")
		->asArray()
		->addOrderBy(['month' => SORT_ASC])
		->all();
		return $chart;
	}
	
}