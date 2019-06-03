<?php

namespace backend\services;

use common\models\framework\FwService;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use common\services\framework\ExternalSystemService;


class ReportAfreshService extends FwService{
	
	/**
	 * 搜索
	 * @param unknown $params
	 * @return \backend\services\ActiveDataProvider
	 */
	public function search($params)
	{
		$query = FwService::find(false);
	
		$dataProvider = new ActiveDataProvider([
				'query' => $query,
		]);
		
		$query->andFilterWhere(['=', 'service_type',FwService::SERVICE_TYPE_REPORT]);
	
		$dataProvider->setSort(false);
		$query->addOrderBy(['created_at' => SORT_DESC]);
		return $dataProvider;
	}
	
	public function runRestart($param){
		$externalSystemService = new ExternalSystemService();
		$url = $externalSystemService->getExternalSystemInfoByExternalSystemCode("elearning-report-service")->api_address;
		$url=$this->initRemoteUrl($url, $param);
		$ch = curl_init($url) ;
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
		$output = curl_exec($ch) ;
		return $output;
	}
	
	public function initRemoteUrl($url,$param){
	 
	  $url_params="?sid=".$param['id']."&begin_time=".$param['begin'];	
	  if($param['end']){
	  	$url_params=$url_params."&end_time=".$param['end'];	
	  }
	  switch ($param['code'])
	  {
			case "platform_study" :
				$url = $url . "runPlatformStudy" . $url_params;
				break;
			case "platform_study_mon" :
				$url = $url . "runPlatformStudyMon" . $url_params;
				break;
			case "active_degree" :
				$url = $url . "runActiveDegree" . $url_params;
				break;
			case "active_degree_mon" :
				$url = $url . "runActiveDegreeMon" . $url_params;
				break;
			case "online_course_comp" :
				$url = $url . "runOnlineCourseComplete" . $url_params;
				break;
			case "face_course_comp" :
				$url = $url . "runFaceCourseComplete" . $url_params;
				break;
			case "online_course_seq" :
				$url = $url . "runOnlineCourseSeq" . $url_params;
				break;
			case "online_course_seq_mon" :
				$url = $url . "runOnlineCourseSeqMon" . $url_params;
				break;
			case "online_course_seq_week" :
				$url = $url . "runOnlineCourseSeqWeek" . $url_params;
				break;
			case "study_score" :
				$url = $url . "runStudyScore" . $url_params;
				break;
			case "personal_study" :
				$url = $url . "runPersonalStudy" . $url_params;
				break;
		}
	  return $url;
    }
	
	
}