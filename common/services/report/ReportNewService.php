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
use common\models\report\RpStPlatformStudyM;
use common\models\report\RpStActiveDegreeM;
use common\models\report\RpStOnlineCourseComp;
use common\models\report\RpStFaceCourseComp;
use common\models\report\RpStOnlineCourseSeq;
use common\models\report\RpStOnlineCourseSeqM;
use common\models\report\RpStOnlineCourseSeqW;
use common\models\report\RpStStudyScore;
use common\models\framework\FwOrgnization;
use common\models\framework\FwPosition;
use common\models\report\RpStPersonalStudy;
use yii\data\Pagination;
use common\models\learning\LnCourseEnroll;
use common\models\learning\LnCourseComplete;

class ReportNewService
{
	
	const MANAGER_FLAG_YES = "1";

	function getMonth($y, $m)
	{
		if ($m < 10) {
			return $y . '0' . $m;
		} else {
			return $y . $m;
		}
	}

	
	
	/**
	 * 平台学习概况
	 * @param unknown $param
	 * @return multitype:multitype:
	 */
	public function getPlatformStudyData($param){
		$datas = [];
		$chart=null;
		//全部域
		if($param['domain_param']=='all'){
			$select_val="
					month,sum(login_num) as login_num,sum(login_num)/sum(total_user_num) as login_num_rate,
			        sum(reg_num) as reg_num,sum(reg_num)/sum(total_user_num) as reg_num_rate,
			        sum(com_num) as com_num, sum(com_num)/sum(total_user_num) as com_num_rate,
			        sum(duration) as duration,sum(duration)/sum(total_user_num) as duration_rate,
			        sum(certif_num) as certif_num,sum(certif_num)/sum(total_user_num) as certif_num_rate,sum(total_user_num)  
					";
			$domains = $this->getNoShareDomainIds();
			$chart = RpStPlatformStudyM::find(false)
						->andFilterWhere(['=', 'year', $param['time_param']])
						->andFilterWhere(['in', 'domain_id', $domains])
						->groupBy('month')
						->addOrderBy(['month' => SORT_ASC])
						->select($select_val)
						->asArray()
						->all();
		}else{
			$chart = RpStPlatformStudyM::find(false)
				->andFilterWhere(['=', 'year', $param['time_param']])
				->andFilterWhere(['=', 'domain_id', $param['domain_param']])
				->addOrderBy(['month' => SORT_ASC])
				->asArray()
				->all();
		}
	
		
		
		$chart_label = [];
		$chart_login_num = [];
		$chart_reg_num = [];
		$chart_com_num = [];
		$chart_duration = [];
		$chart_certif_num = [];
		
		$chart_tmp = [];
		
		foreach ($chart as $ch) {
			$ch['login_num'] = $ch['login_num'] == null ? 0 : $ch['login_num'];
			$ch['reg_num'] = $ch['reg_num'] == null ? 0 : $ch['reg_num'];
			$ch['com_num'] = $ch['com_num'] == null ? 0 : $ch['com_num'];
			$ch['certif_num'] = $ch['certif_num'] == null ? 0 : $ch['certif_num'];	
			$ch['duration'] = round($ch['duration'] == null ? 0 : $ch['duration'],1);
			//$ch['duration'] = round($ch['duration'] / 60 / 60, 2);
			array_push($chart_label, $this->getMonth($param['time_param'], $ch['month']));
			array_push($chart_login_num, $ch['login_num']);
			array_push($chart_reg_num, $ch['reg_num']);
			array_push($chart_com_num, $ch['com_num']);
			array_push($chart_certif_num, $ch['certif_num']);		
			array_push($chart_duration, $ch['duration']);
			
			$ch['login_num_rate'] =round( $ch['login_num_rate'] == null ? 0 : $ch['login_num_rate'],1);
			$ch['reg_num_rate'] = round($ch['reg_num_rate'] == null ? 0 : $ch['reg_num_rate'],1);
			$ch['com_num_rate'] =round( $ch['com_num_rate'] == null ? 0 : $ch['com_num_rate'],1);
			$ch['certif_num_rate'] = round($ch['certif_num_rate'] == null ? 0 : $ch['certif_num_rate'],1);
			$ch['duration_rate'] = round($ch['duration_rate'] == null ? 0 : $ch['duration_rate'],1);
		
			array_push($chart_tmp, $ch);
		}
		
		
		$datas['label'] = $chart_label;
		$datas['login_num'] = $chart_login_num;
		$datas['reg_num'] = $chart_reg_num;
		$datas['com_num'] = $chart_com_num;
		$datas['certif_num'] = $chart_certif_num;
		$datas['duration'] = $chart_duration;
		
		$datas['platformStudy'] = $chart_tmp;
		
		return $datas;
	}
	
	/**
	 * 活跃度报表
	 * @param unknown $param
	 * @return multitype:multitype:
	 */
	public function getActiveDegreeData($param){
		$datas = [];
		$chart=[];
		$query=RpStActiveDegreeM::find(false)
			->andFilterWhere(['between', 'op_time', $param['begin'], $param['end']])
			->andFilterWhere(['=', 'domain_id', $param['domain_param']])
			//->andFilterWhere(['=', 'type', $param['type_param']])
			->addOrderBy(['op_time' => SORT_ASC]);
		
	    /*$sql=$query->createCommand()->getRawSql();*/
	    $query_tmp = $query->createCommand ()->rawSql;
	    
	    $sql_chart="select 
					        sum(case when type=1 then login_num end) as pc,
					        sum(case when type=2 then login_num end) as weixin,
					        sum(case when type=3 then login_num end) as app,
					        op_time from ( ".$query_tmp." ) t1 group by op_time";
	    $db = \Yii::$app->db;
	    $chart_arr = $db->createCommand ($sql_chart)->queryAll ();
	
		
	
		$chart_tmp = [];
	
		foreach ($chart_arr as $ch) {
			//$ch['all_'] = $ch['all_'] == null ? 0 : $ch['all_'];
			$ch['pc'] = $ch['pc'] == null ? 0 : $ch['pc'];
			$ch['weixin'] = $ch['weixin'] == null ? 0 : $ch['weixin'];
			$ch['app'] = $ch['app'] == null ? 0 : $ch['app'];
			
			array_push($chart, $ch);
		}
		
		
		$tbList=RpStActiveDegreeM::find(false)
			->andFilterWhere(['between', 'op_time', $param['begin'], $param['end']])
			->andFilterWhere(['=', 'domain_id', $param['domain_param']])
			->andFilterWhere(['=', 'type', $param['type_param']])
			->addOrderBy(['op_time' => SORT_ASC])
			->asArray()
			->all();
		
		foreach ($tbList as $tb) {
			
			$tb['login_user_num'] = $tb['login_user_num'] == null ? 0 : $tb['login_user_num'];
			$tb['login_user_num_rate'] = $tb['login_user_num_rate'] == null ? 0 : $tb['login_user_num_rate'];
			$tb['login_user_num_rate'] =$tb['login_user_num_rate']*100;
			$tb['login_num'] = $tb['login_num'] == null ? 0 : $tb['login_num'];
			$opTimeArr=explode('-', $tb['op_time']);
			$tb['op_time']=$opTimeArr[0].Yii::t('common', 'time_year').$opTimeArr[1].Yii::t('frontend', 'month2');
			
			array_push($chart_tmp, $tb);
		}
	
		$datas['chart'] = $chart;
		$datas['activeDegree'] = $chart_tmp;
	
		return $datas;
	}

	/**
	 * 在线课程完成
	 * @param unknown $param
	 * @return multitype:multitype:
	 */
	public function getOnlineCourseCompData($param){
		$opTime=RpStOnlineCourseComp::find(false)
		         ->max('op_time');
		
		$datas = [];
		
		$auth_sql=$this->getAuthByTypes($param['type_param'],RpStOnlineCourseComp::tableName());
		
		$query=RpStOnlineCourseComp::find(false)
			->andFilterWhere(['=', 'op_time',$opTime])
			->andFilterWhere(['=', 'course_id', $param['course_id']])
			->andFilterWhere(['=', 'type', $param['type_param']])
			->andWhere($auth_sql);
			
		
		/*$sql=$query->createCommand()->getRawSql();*/
		$table_arr=$query
		    ->addOrderBy(['total_user_num' => SORT_DESC])
			->asArray()
			->all();
	
		$table_tmp = [];
	
		foreach ($table_arr as $ch) {
			$ch['total_user_num'] = $ch['total_user_num'] == null ? 0 : $ch['total_user_num'];
			$ch['reg_num'] = $ch['reg_num'] == null ? 0 : $ch['reg_num'];
			$ch['com_num'] = $ch['com_num'] == null ? 0 : $ch['com_num'];
			$ch['com_num_rate'] = $ch['com_num_rate'] == null ? 0 : $ch['com_num_rate'];
			$ch['score'] = $ch['score'] == null ? 0 : $ch['score'];
			
			$ch['com_num_rate'] =round($ch['com_num_rate'] *100, 2);
				
			array_push($table_tmp, $ch);
		}
	
	
		$datas['onlineCourseComp'] = $table_tmp;
	
		return $datas;
	}
	
	/**
	 * 面授课程完成
	 * @param unknown $param
	 * @return multitype:multitype:
	 */
	public function getFaceCourseCompData($param){
		$opTime=RpStFaceCourseComp::find(false)
		->max('op_time');
	
		$datas = [];
	
		$auth_sql=$this->getAuthByTypes($param['type_param'],RpStFaceCourseComp::tableName());
	
		$query=RpStFaceCourseComp::find(false)
		->andFilterWhere(['=', 'op_time',$opTime])
		->andFilterWhere(['=', 'course_id', $param['course_id']])
		->andFilterWhere(['=', 'type', $param['type_param']])
		->andWhere($auth_sql);
			
	
		/*$sql=$query->createCommand()->getRawSql();*/
		$table_arr=$query
		->addOrderBy(['total_user_num' => SORT_DESC])
		->asArray()
		->all();
	
		$table_tmp = [];
	
		foreach ($table_arr as $ch) {
			$ch['total_user_num'] = $ch['total_user_num'] == null ? 0 : $ch['total_user_num'];
			$ch['reg_num'] = $ch['reg_num'] == null ? 0 : $ch['reg_num'];
			$ch['com_num'] = $ch['com_num'] == null ? 0 : $ch['com_num'];
			$ch['com_num_rate'] = $ch['com_num_rate'] == null ? 0 : $ch['com_num_rate'];
			$ch['not_qualify'] = $ch['not_qualify'] == null ? 0 : $ch['not_qualify'];
			$ch['not_qualify_rate'] = $ch['not_qualify_rate'] == null ? 0 : $ch['not_qualify_rate'];
			$ch['certification'] = $ch['certification'] == null ? 0 : $ch['certification'];
			$ch['certification_rate'] = $ch['certification_rate'] == null ? 0 : $ch['certification_rate'];
			
			$ch['com_num_rate'] =round($ch['com_num_rate'] *100, 2);
			$ch['not_qualify_rate'] =round($ch['not_qualify_rate'] *100, 2);
			$ch['certification_rate'] =round($ch['certification_rate'] *100, 2);
	
			array_push($table_tmp, $ch);
		}
	
	
		$datas['faceCourseComp'] = $table_tmp;
	
		return $datas;
	}
	
	/**
	 * 在线课程完成时序报表
	 * @param unknown $param
	 * @return multitype:multitype:
	 */
	public function getOnlineCourseSeqData($param){
		$datas = [];
		$chart=null;
		//全部域
		if($param['domain_param']=='all'){
			$domains = $this->getNoShareDomainIds();
			$select_val="
					op_time,sum(total_user_num) as total_user_num,
					sum(reg_num) as reg_num,
			        sum(com_num) as com_num,
					sum(com_num)/sum(reg_num) as com_num_rate,
			        sum(score)/".count($domains) ." as score         
					";
			
			$chart = $this->getAllDomainsData($param, $domains, $select_val);
		}else{		
			$chart = $this->getData($param);
		}
	
		$chart_label = [];	
		$chart_reg_num = [];
		$chart_com_num = [];
		$chart_com_num_rate = [];
		$chart_score = [];
	
		$chart_tmp = [];
	
		foreach ($chart as $ch) {
			$ch['total_user_num'] = $ch['total_user_num'] == null ? 0 : $ch['total_user_num'];
			$ch['reg_num'] = $ch['reg_num'] == null ? 0 : $ch['reg_num'];
			$ch['com_num'] = $ch['com_num'] == null ? 0 : $ch['com_num'];
			$ch['com_num_rate'] = $ch['com_num_rate'] == null ? 0 : $ch['com_num_rate'];
			$ch['score'] = round($ch['score'] == null ? 0 : $ch['score'],1);
			
			if($param['circle']=='week'){
				$ch['op_time'] = date('W',strtotime($ch['op_time']));
			}
			
			$ch['com_num_rate'] =round($ch['com_num_rate'] *100, 2);
			
			array_push($chart_label, $ch['op_time']);
			array_push($chart_reg_num, $ch['reg_num']);
			array_push($chart_com_num, $ch['com_num']);
			array_push($chart_com_num_rate, $ch['com_num_rate']);
			array_push($chart_score, $ch['score']);
			
			
			
			array_push($chart_tmp, $ch);
		}
	
	
		$datas['label'] = $chart_label;
		$datas['reg_num'] = $chart_reg_num;
		$datas['com_num'] = $chart_com_num;
		$datas['com_num_rate'] = $chart_com_num_rate;
		$datas['score'] = $chart_score;
	
		$datas['onlineCourseSeq'] = $chart_tmp;
	
		return $datas;
	}
	
	/**
	 * 切换日，周，月数据
	 * @param unknown $param
	 * @return NULL
	 */
	public function getData($param){
		$chart=null;
		if($param['circle']=='day'){
			$chart= RpStOnlineCourseSeq::find(false)
				->andFilterWhere(['between', 'op_time', $param['begin'], $param['end']])
				->andFilterWhere(['=', 'domain_id', $param['domain_param']])
				->andFilterWhere(['=', 'course_id', $param['course_id']])
				->addOrderBy(['op_time' => SORT_ASC])
				->asArray()
				->all();
		}elseif ($param['circle']=='month'){
			$chart= RpStOnlineCourseSeqM::find(false)
				->andFilterWhere(['between', 'op_time', $param['begin'], $param['end']])
				->andFilterWhere(['=', 'domain_id', $param['domain_param']])
				->andFilterWhere(['=', 'course_id', $param['course_id']])
				->addOrderBy(['op_time' => SORT_ASC])
				->asArray()
				->all();
		}elseif ($param['circle']=='week'){
			$chart= RpStOnlineCourseSeqW::find(false)
				->andFilterWhere(['between', 'op_time', $param['begin'], $param['end']])
				->andFilterWhere(['=', 'domain_id', $param['domain_param']])
				->andFilterWhere(['=', 'course_id', $param['course_id']])
				->addOrderBy(['op_time' => SORT_ASC])
				->asArray()
				->all();
		}
		return $chart;		
	}
	
	/**
	 * 取全域数据
	 * @param unknown $param
	 * @param unknown $domains
	 * @param unknown $select_val
	 * @return NULL
	 */
	public function getAllDomainsData($param,$domains,$select_val){
		$chart=null;
		if($param['circle']=='day'){
			$chart= RpStOnlineCourseSeq::find(false)
			->andFilterWhere(['between', 'op_time', $param['begin'], $param['end']])
			->andFilterWhere(['in', 'domain_id', $domains])
			->andFilterWhere(['=', 'course_id', $param['course_id']])
			->groupBy('op_time')
			->addOrderBy(['op_time' => SORT_ASC])
			->select($select_val)
			->asArray()
			->all();
		}elseif ($param['circle']=='month'){
			$chart= RpStOnlineCourseSeqM::find(false)
			->andFilterWhere(['between', 'op_time', $param['begin'], $param['end']])
			->andFilterWhere(['in', 'domain_id', $domains])
			->andFilterWhere(['=', 'course_id', $param['course_id']])
			->groupBy('op_time')
			->addOrderBy(['op_time' => SORT_ASC])
			->select($select_val)
			->asArray()
			->all();
		}elseif ($param['circle']=='week'){
			$chart= RpStOnlineCourseSeqW::find(false)
			->andFilterWhere(['between', 'op_time', $param['begin'], $param['end']])
			->andFilterWhere(['in', 'domain_id', $domains])
			->andFilterWhere(['=', 'course_id', $param['course_id']])
			->groupBy('op_time')
			->addOrderBy(['op_time' => SORT_ASC])
			->select($select_val)
			->asArray()
			->all();
		}
		return $chart;
	}
	
	/**
	 * 取用户数据权限
	 * @param unknown $type
	 * @param unknown $tableName
	 * @return string
	 */
	public function getAuthByTypes($type,$tableName){
		$where_sql="";
		if($type==RpStOnlineCourseComp::TYPE_COMPANY){
			$where_sql="display_id='".Yii::$app->user->identity->company_id."'";
		}else if($type==RpStOnlineCourseComp::TYPE_DOMAIN){
			$domainIdsTmp=$this->getNoShareDomains();
			
			$where_sql="display_id in (";
			foreach($domainIdsTmp as $is){
				$where_sql.="'".$is['kid']."',";
			}
			
			$where_sql=trim($where_sql,',');
			$where_sql.=")";
		
		}else if($type==RpStOnlineCourseComp::TYPE_REPORTING_MANAGER){
			$domainIdsTmp=$this->getNoShareDomains();
			$domainIds=[];
			$domain_ids_sql="";
			foreach($domainIdsTmp as $is){
				$domain_ids_sql.="'".$is['kid']."',";
			}
			$domain_ids_sql=trim($domain_ids_sql,',');
			
			$where_sql=" EXISTS (
				select 1 from  eln_fw_user t2 where  t2.is_deleted = '0' and t2.status='1'
						and t2.domain_id in (".$domain_ids_sql.")  and manager_flag='".ReportNewService::MANAGER_FLAG_YES."'
								and t2.kid=".$tableName.".display_id
				)";
			
		}
		return $where_sql;
	}
	
	/**
	 * 取课程信息
	 * @param unknown $kid
	 * @return Ambigous <\common\base\mixed, NULL, \common\base\static>
	 */
	public function getCourseInfo($kid){

	  return LnCourse::findOne($kid);
	}
	
	/**
	 * 学习成绩
	 * @param unknown $param
	 * @return multitype:multitype:
	 */
	public function getStudyScoreData($param){
		$opTime=RpStStudyScore::find(false)
		->max('op_time');
		
		$datas = [];
		
		$db = \Yii::$app->db;
		$user_sql_="";
		if($param['user_id']){
			$user_sql_=" and user_id='".$param['user_id']."'";
		}
		$ext_sql_="";
		if($param['ext_id']){
			$ext_sql_=" and (orgnization_id='".$param['ext_id']."' or position_id like '%".$param['ext_id']."%' or reporting_manager_id='".$param['ext_id']."')";
		}
		$sql_=" select t1.*,t2.* from (
				   select score,reg_time,comp_time,user_id from eln_rp_st_study_score   where op_time='".$opTime."' and domain_id='".$param['domain_param']."' and course_id='".$param['course_id']."'  ".$user_sql_."
				      ) t1 inner join (
				   select real_name,mobile_no,user_id,email,orgnization_name,position_name,reporting_manager_name from eln_fw_user_display_info  where 1=1  ".$ext_sql_." 		
				     ) t2 on (t1.user_id=t2.user_id)
				";
		$data_ = $db->createCommand ($sql_)->queryAll ();
		
		$table_tmp = [];
		
		foreach ($data_ as $ch) {
			$ch['score'] = $ch['score'] == null ? 0 : $ch['score'];
			$ch['reporting_manager_name'] = $ch['reporting_manager_name'] == null ? '/' : $ch['reporting_manager_name'];
			$ch['email'] = $ch['email'] == null ? '/' : $ch['email'];
			array_push($table_tmp, $ch);
		}
		
		
		$datas['studyScore'] = $table_tmp;
		
		return $datas;
	}
	
	/**
	 * 个人学习情况
	 * @param unknown $param
	 * @return multitype:multitype:
	 */
	public function getPersonalStudyData($param){
		$maxTime=RpStPersonalStudy::find(false)
			->max('op_time');
		
		$minTime=RpStPersonalStudy::find(false)
			->min('op_time');
		
		//时间比较大小
		$intBegin=strtotime($param['begin']);
		$intEnd=strtotime($param['end']);
		$intMaxTime=strtotime($maxTime);
		$intMinTime=strtotime($minTime);
		if($intBegin>=$intMinTime){
			$minTime=$param['begin'];
		}
		
		if($intEnd<$intMaxTime){
			$maxTime=$param['end'];
		}
		//
	
		$datas = [];
	
		$db = \Yii::$app->db;
		$user_sql_="";
		if($param['user_id']){
			$user_sql_=" and user_id='".$param['user_id']."'";
		}
		$ext_sql_="";
		if($param['ext_id']){
			$ext_sql_=" and (orgnization_id='".$param['ext_id']."' or position_id like '%".$param['ext_id']."%' or reporting_manager_id='".$param['ext_id']."')";
		}
		
		$sql_="
		 select (duration-duration2) as duration,(reg_num-reg_num2) as reg_num,(com_num-com_num2)  as com_num,(certification-certification2) as certification,u.* from (
				select ifnull( t1.duration,0) as duration, ifnull(  t1.reg_num,0) as reg_num,ifnull(t1.com_num,0) as com_num, ifnull( t1.certification,0) as certification,t1.domain_id,t1.user_id
				   ,ifnull( t2.duration,0) as duration2, ifnull(  t2.reg_num,0) as reg_num2,ifnull(t2.com_num,0) as com_num2, ifnull( t2.certification,0) as certification2  
				         from (
				select duration,reg_num,com_num,certification,domain_id,user_id   from eln_rp_st_personal_study 
				   where domain_id='".$param['domain_param']."'
				  and op_time ='".$maxTime."' ".$user_sql_."  ) t1 left join 
				  (
				 select   duration,reg_num,com_num,certification,domain_id,user_id
				  from eln_rp_st_personal_study 
				   where domain_id='".$param['domain_param']."'
				  and op_time ='".$minTime."' ".$user_sql_."  ) t2 on ( t1.domain_id=t2.domain_id  and t1.user_id=t2.user_id ) ) t   join (
				   select real_name,mobile_no,user_id,email,orgnization_name,position_name,reporting_manager_name from eln_fw_user_display_info  where 1=1  ".$ext_sql_."
				     ) u on (t.user_id=u.user_id)
				";
		
		$data_ = $db->createCommand ($sql_)->queryAll ();
	
		$table_tmp = [];
	
		foreach ($data_ as $ch) {
			$ch['duration'] = $ch['duration'] == null ? 0 : $ch['duration'];
			$ch['email'] = $ch['email'] == null ? '/' : $ch['email'];
			$ch['reporting_manager_name'] = $ch['reporting_manager_name'] == null ? '/' : $ch['reporting_manager_name'];
			
			$ch['duration'] =round($ch['duration'], 2);
			array_push($table_tmp, $ch);
		}
	
	
		$datas['personalStudy'] = $table_tmp;
	
		return $datas;
	}
	

	/**
	 * 查询初始化数据（包含共享域）
	 * @return multitype:multitype: unknown
	 */
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
	
	/**
	 * 查询初始化数据（不包含共享域）
	 * @return multitype:multitype: unknown
	 */
	public function getQueryNoShare()
	{
		$datas = [];
		$years = $this->getTimeList();
		if (empty($years)) {
			$now['year'] = date('Y', time());
			array_push($years, $now);
		}
	
		$domains = $this->getNoShareDomains();
	
		$datas['years'] = $years;
	
		$datas['domains'] = $domains;
	
		return $datas;
	}


	/**
	 * 当前用户的域信息（包含共享域）
	 * @return multitype:
	 */
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
	
	/**
	 * 当前用户的域信息（不包含共享域）
	 * @return multitype:
	 */
	public function getNoShareDomains()
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
				if($d->share_flag=='0'){
					array_push($domains, $domain);
				}
				
			}
		}
	
		return $domains;
	}
	
	/**
	 * 当前用户的域id（不包含共享域）
	 * @return multitype:
	 */
	public function getNoShareDomainIds()
	{
		$user_id = Yii::$app->user->getId();
		$userDomainService = new UserDomainService();
	
		$domainIds = $userDomainService->getSearchListByUserId($user_id);
	
		$domains = [];
		if (isset($domainIds) && $domainIds != null) {
			foreach ($domainIds as $d) {
				if($d->share_flag=='0'){
					array_push($domains,  $d->kid);
				}
			}
		}
	
		return $domains;
	}

	/**
	 * 获取时间信息
	 * @return unknown
	 */
	public function getTimeList()
	{
		$chart = RpStPlatformStudyM::find(false)
			->groupBy("year")
			->select("YEAR")
			->asArray()
			->addOrderBy(['year' => SORT_DESC])
			->all();
		return $chart;
	}

	/**
	 * 课程搜索插件
	 * @param unknown $params
	 * @return multitype:boolean multitype:
	 */
	public function searchCourseByKeyword($params)
	{
		$domain_id=$params['domain_id'];
		$keyword=$params['q'];
		$result = array();

		$currentTime = time();
		$domain_id_arr=[];
		if(!$domain_id){
			$domainIdsTmp=null;
			if(isset($params['noshare'])){
				$domainIdsTmp=$this->getNoShareDomains();
			}else{
				$domainIdsTmp=$this->getDomains();
			}
			
			foreach ($domainIdsTmp as $dom){
				array_push($domain_id_arr,  $dom['kid']);
			}
		}else{
			array_push($domain_id_arr,  $domain_id);
		}
		

		$domainQuery = LnResourceDomain::find(false);
		$domainQuery->select('resource_id')
			->andFilterWhere(['in', 'domain_id', $domain_id_arr])
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
		
		if(null!=$params['course_type']){
			$courseQuery->andFilterWhere(['=','course_type',$params['course_type']]);				
		}
		

		//关键字搜索
		if (trim($keyword)) {
			$courseQuery->andFilterWhere(['or', ['like', 'course_code', $keyword],['like', 'course_name', $keyword], ['like', 'course_desc_nohtml', $keyword]]);
		}


		//$iiiii=$courseQuery->createCommand()->getRawSql();
		
		$courses = $courseQuery->orderBy('updated_at desc')
			->asArray()
			->all();

		$arr_title = [];
		foreach ($courses as $r) {
			$t['title'] = $r['course_name']."【".$r['course_code']."】";
			$t['uid'] = $r['kid'];
			Array_push($arr_title, $t);
		}
		$result['results'] = $arr_title;
		$result['success'] = true;

		return $result;
	}
	
	
	
	
	/**
	 * 查询用户id
	 * @param unknown $domain_id
	 * @param unknown $keyword
	 * @return multitype:boolean multitype:
	 */
	public function searchUserByKeyword($domain_id, $keyword)
	{
		$result = array();
		$companyId = Yii::$app->user->identity->company_id;
	
		$courseQuery=FwUser::find(false)
		->andFilterWhere(['=', 'status', FwUser::STATUS_FLAG_NORMAL])
		->andFilterWhere(['=', 'company_id', $companyId]);
		//->andFilterWhere(['=', 'domain_id', $domain_id]);
	
		//关键字搜索
// 		if (trim($keyword)) {
// 			$courseQuery->andFilterWhere( ['like', 'real_name', $keyword]);
// 		}
		
		if (trim($keyword)) {
			$courseQuery->andFilterWhere(['or', ['like', 'real_name', $keyword],['like', 'email', $keyword], ['like', 'user_no', $keyword]]);
		}
	
		$users = $courseQuery
		->asArray()
		->all();

		//$iiiii=$courseQuery->createCommand()->getRawSql();
	
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
	
	/**
	 * 
	 *  查询部门，岗位，汇报经理信息
	 * @param unknown $keyword
	 * @return multitype:boolean multitype:
	 */
	public function searchExtInfosByKeyword( $keyword)
	{
		$result = array();
		$companyId = Yii::$app->user->identity->company_id;
	
		//直线经理
		$query=FwUser::find(false)
					->andFilterWhere(['=', 'status', FwUser::STATUS_FLAG_NORMAL])
					->andFilterWhere(['=', 'company_id', $companyId])
					->andFilterWhere(['=', 'manager_flag', ReportNewService::MANAGER_FLAG_YES]);
		if (trim($keyword)) {
			$query->andFilterWhere(['or', ['like', 'real_name', $keyword],['like', 'email', $keyword], ['like', 'user_no', $keyword]]);
		}	
		$users = $query
			->asArray()
			->all();
		
		//部门
		$query_org=FwOrgnization::find(false)
				->andFilterWhere(['=', 'status', FwUser::STATUS_FLAG_NORMAL])
				->andFilterWhere(['=', 'company_id', $companyId]);		
		if (trim($keyword)) {
			$query_org->andFilterWhere(['or', ['like', 'orgnization_code', $keyword], ['like', 'orgnization_name', $keyword]]);
		}	
		$orgs = $query_org
					->asArray()
					->all();
		
		//岗位
		$query_pos=FwPosition::find(false)
				->andFilterWhere(['=', 'status', FwUser::STATUS_FLAG_NORMAL])
				->andFilterWhere(['=', 'company_id', $companyId]);
		if (trim($keyword)) {
			$query_pos->andFilterWhere(['or', ['like', 'position_code', $keyword], ['like', 'position_name', $keyword]]);
		}
		$poses = $query_pos
			->asArray()
			->all();
		
		$userArr = [];	
		$orgArr = [];
		$posArr = [];
		
		foreach ($users as $r) {
			$user['id'] = $r['kid'];
			$user['name'] = $r['real_name'];
			$user['email'] = $r['email'];
			
			Array_push($userArr, $user);		
		}
		
		foreach ($orgs as $o){
			$org['id'] = $o['kid'];
			$org['name'] = $o['orgnization_name'];
			
			Array_push($orgArr, $org);
		}
		
		foreach ($poses as $p){
			$pos['id'] = $p['kid'];
			$pos['name'] = $p['position_name'];
				
			Array_push($posArr, $pos);
		}
		
		$result['user'] = $userArr;
		$result['pos'] = $posArr;
		$result['org'] = $orgArr;
		$tabTitleArr=[];
		
		if($userArr){
			$tabTitleArr['user'] =Yii::t('common', 'reporting_model_line_manager') ;
		}
		
		if($orgArr){
			$tabTitleArr['org'] = Yii::t('common', 'department');
		}
		
		if($posArr){
			$tabTitleArr['pos'] = Yii::t('common', 'position');
		}
		
		$result['tabTitle'] = $tabTitleArr;
		
	
		return $result;
	}
	
	/**
	 * 查询用户注册和完成课程列表
	 * @param unknown $params
	 * @return \common\services\report\Pagination
	 */
	public function search_pub($params){		
		$maxTime=RpStPersonalStudy::find(false)
			->max('op_time');
		
		$minTime=RpStPersonalStudy::find(false)
			->min('op_time');
		
		//时间比较大小
		$intBegin=strtotime($params['begin']);
		$intEnd=strtotime($params['end']);
		$intMaxTime=strtotime($maxTime);
		$intMinTime=strtotime($minTime);
		if($intBegin>=$intMinTime){
			$minTime=$params['begin'];
		}
		
		if($intEnd<$intMaxTime){
			$maxTime=$params['end'];
		}
		
		$params['size']=10000000;
		$user_id=$params['user_id'];	
		$domain_id=$params['domain_id'];	
		$db = \Yii::$app->db;
		$result_sql ="";
		$is_delete_flag=RpStPersonalStudy::DELETE_FLAG_NO;
		
		
		if("reg"==$params['type']){
			$result_sql = "select t.* from (
				select t2.course_name,t1.created_at,t1.course_id
				  from (select course_id,created_at
				           from eln_ln_course_reg
				          where user_id = '".$user_id."'  and created_at between ".strtotime($minTime)."  and ".strtotime($maxTime)."
				            and is_deleted = '".$is_delete_flag."') t1  join
				        (select kid, course_name
				           from eln_ln_course
				          where is_deleted = '".$is_delete_flag."'
				            and course_type = '".LnCourse::COURSE_TYPE_ONLINE."') t2 on(t1.course_id = t2.kid)
				            union all
				            select course_name, el.created_at,course_id
				          from eln_ln_course_enroll el left join (
				          select kid, course_name
				           from eln_ln_course
				          where is_deleted = '".$is_delete_flag."'
				            and course_type = '".LnCourse::COURSE_TYPE_FACETOFACE."') t1  on(el.course_id = t1.kid)
				         where el.enroll_type = '".LnCourseEnroll::ENROLL_TYPE_ALLOW."'
				           and el.approved_state = '".LnCourseEnroll::APPROVED_STATE_APPROVED."'
				           and el.cancel_state <> '".LnCourseEnroll::CANCEL_STATE_APPROVED."'  and  el.created_at between ".strtotime($minTime)."  and ".strtotime($maxTime)."
				           and el.is_deleted = '".$is_delete_flag."'  and el.user_id='".$user_id."'
				           ) t 
             left join eln_ln_resource_domain rd
    on (t.course_id = rd.resource_id and rd.resource_type = '".LnResourceDomain::RESOURCE_TYPE_COURSE."' 
       and rd.is_deleted = '".$is_delete_flag."')   where rd.status = '".LnResourceDomain::STATUS_FLAG_NORMAL."' and rd.domain_id='".$domain_id."' 
       order by 2 desc
				";
		}
		
		if("com"==$params['type']){
			$result_sql ="
					select cr.created_at,t1.course_name from 
						eln_ln_course_complete cr 
						  left join (
						  select kid, course_name
						           from eln_ln_course
						          where is_deleted = '".$is_delete_flag."'        
						  )  t1 on cr.course_id=t1.kid
								left join eln_ln_resource_domain rd
			            on (cr.course_id = rd.resource_id and rd.resource_type = '".LnResourceDomain::RESOURCE_TYPE_COURSE."' and
			               cr.is_deleted = '".$is_delete_flag."' and rd.is_deleted = '".$is_delete_flag."')  
						   where cr.complete_type = '".LnCourseComplete::COMPLETE_TYPE_FINAL."'
						           and (cr.complete_status = '".LnCourseComplete::COMPLETE_STATUS_DONE."' or cr.is_retake = '".LnCourseComplete::IS_RETAKE_YES."')   and user_id='".$user_id."' and 
						           		cr.created_at between ".strtotime($minTime)."  and ".strtotime($maxTime)."
						           	and  rd.status = '".LnResourceDomain::STATUS_FLAG_NORMAL."' and rd.domain_id='".$domain_id."' 			
						           order by cr.created_at desc
					";
		}
		
		
		$sub_result_arr = $db->createCommand ( $result_sql )->queryAll ();
		$pages = new Pagination ( [
				'defaultPageSize' => $params ['size'],
				'totalCount' => count($sub_result_arr)
		] );
		
		$result ['pages'] = $pages;
		$datas = [ ];
		foreach ( $sub_result_arr as $ch ) {
			if ($ch ['created_at'] != null) {
				$ch ['created_at'] = date ( "Y年m月d日 H:m:s", $ch ['created_at'] );
			}			
			array_push ( $datas, $ch );
		}
		$result ['data'] = $datas;
	
		return $result;
	}
	
}