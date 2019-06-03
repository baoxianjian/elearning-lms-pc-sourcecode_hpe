<?php
/**
 * Created by PhpStorm.
 * User: benjin
 * Date: 2015/5/20
 * Time: 21:14
 */

namespace frontend\controllers;

use common\services\framework\ExternalSystemService;
use frontend\viewmodels\TaskPushForm;
use Yii;
use frontend\base\BaseFrontController;
use common\services\message\MessageService;
use yii\data\Pagination;
use common\models\framework\FwUser;
use common\models\learning\LnCourse;
use yii\web\Response;
use components\widgets\TPagination;
use common\models\message\MsSelectTemp;
use common\helpers\TTimeHelper;
use common\models\learning\LnResourceDomain;
use common\services\framework\UserDomainService;
use yii\helpers\ArrayHelper;

/**
 * 消息及推送相关
 * Class MessageController
 * @package frontend\controllers
 */
class MessageController extends BaseFrontController
{
	public $layout = 'frame';

    /**
     * 直线经理推送
     */
    public function actionTaskPush()
    {
        $user_id = Yii::$app->user->getId();
        //获取用户信息
        $userList = FwUser::find(false);
        
        $userList->joinWith('fwUserPositions.fwPosition');
        
        $userList->andFilterWhere(['=', 'reporting_manager_id', $user_id]);
        
        $userList->select(FwUser::tableName().'.kid, real_name,position_name');

        $users = $userList->orderBy('user_name')
            ->asArray()
            ->all();

        $pushForm = new TaskPushForm();

        return $this->render('task-push', [
            'users' => $users,
            
            'taskPush' => $pushForm,
        ]);

    }
    
    
    public function actionGetCourse(){
    	$this->layout = 'list';
    	$user_id = Yii::$app->user->getId();
    	$keyword= Yii::$app->request->getQueryParam('keyword');
    	$uuid= Yii::$app->request->getQueryParam('uuid');
    	
    	$pageNo= Yii::$app->request->getQueryParam('page');
    	$currentTime = time();
    	//域搜索
    	$userDomainService = new UserDomainService();
    	$domainIds = $userDomainService->getSearchListByUserId($user_id);
    	
    	
    	if (isset($domainIds) && $domainIds != null) {
    		$domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');
    	
    		$domainIds = array_keys($domainIds);
    		
    	}
    	
    	$domainQuery = LnResourceDomain::find(false);
    	$domainQuery->select('resource_id')
    	->andFilterWhere(['in', 'domain_id', $domainIds])
    	->andFilterWhere(['=', 'status', LnResourceDomain::STATUS_FLAG_NORMAL])
    	->andFilterWhere(['=', 'resource_type', LnResourceDomain::RESOURCE_TYPE_COURSE])
    	->distinct();
    	
    	$domainQuerySql = $domainQuery->createCommand()->rawSql;
    	
    	$lnCourseList = LnCourse::find(false);
    	
    	$lnCourseList
    	->andWhere('kid in (' . $domainQuerySql . ')')
    	->andFilterWhere(['=', 'status', LnCourse::STATUS_FLAG_NORMAL])
    	->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES])
    	->andFilterWhere(['or', ['>=', 'end_time', $currentTime], 'end_time is null'])
    	->andFilterWhere(['or', ['<=', 'start_time', $currentTime], 'start_time is null']);
    	
    	//关键字搜索
    	if($keyword){
    		$lnCourseList ->andFilterWhere(['like', 'course_name',  $keyword])
    		             ->orWhere(['like', 'course_desc_nohtml',  $keyword])
    		              ->andFilterWhere(['=', 'is_deleted',  '0']);	               
    	}
    	
    	
    	//去重
    	$lnCourseList ->andWhere(" not exists (select * from eln_ms_select_temp p where ".LnCourse::tableName().".kid=p.select_id and p.mission_id= '".$uuid."')");
    	
    	
    	
    	$count=$lnCourseList->count();
    	$pages = new TPagination(['defaultPageSize' => '5', 'totalCount' => $count]);
    	
    	
    	
    	$courses = $lnCourseList->orderBy('course_name')
    	           ->offset($pages->offset)
    	            ->limit($pages->limit)
    	              ->all();
    	
//     	foreach ($courses as $row){		
//     		$row->course_desc= str_replace(array("\r\n", "\r", "\n"), "",trim(strip_tags($row->course_desc)));
//     	}

    	return $this->render('tabCourse', [
    			'data' => $courses,
    			'page_id' => 'page1',
    			'pages' => $pages,
    			'pageNo'=>$pageNo,
    	]);
    }
    
    /**
     * 学习管理员推送
     */
    public function actionAdminPush()
    {
    	//        $models    = new MessageService();
    	//        $userList = $models->getUserInfoByManager();
    	$pushForm = new TaskPushForm();
    	$user_id = Yii::$app->user->getId();
    	//获取用户信息
    	$userList = FwUser::findOne($user_id);
    	$company_id=$userList->company_id;
    	
    	return $this->render('admin-push', [
    			'company_id'=>$company_id,
    			'taskPush' => $pushForm,
    	]);
    
    }
    
    public function actionGetUsers(){
    	$user_id = Yii::$app->user->getId();
    	
    	$userDomainService = new UserDomainService();
    	$domainIds = $userDomainService->getSearchListByUserId($user_id);
    	
    	$searchDbInforItem= Yii::$app->request->getQueryParam('searchDbInforItem');
    	$uuid= Yii::$app->request->getQueryParam('user_uuid');
    	$company_id= Yii::$app->request->getQueryParam('company_id');



    	$sub_sql="";


		if (isset($domainIds) && $domainIds != null) {
			$domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');

			$domainIds = array_keys($domainIds);
		}
        foreach ($domainIds as $domain_id){

        	$sub_sql.="'".$domain_id."'".",";
        };
		$sub_sql=trim($sub_sql,',');
    	
    	$sql="select * from eln_ms_upod_view t where not exists "." (select * from eln_ms_select_temp p where t.object_id=p.select_id and p.mission_id= '".$uuid."')"
    					." and t.company_id='".$company_id."' "." and t.domain_id in (".$sub_sql.")";
    	if(trim($searchDbInforItem)==''){
    		//显示默认的选项
    		$default_option="('".Yii::t('frontend','domain_all')."','".Yii::t('frontend','general_manager')."','".Yii::t('frontend','mid_manager')."','".Yii::t('frontend','marketing')."','".Yii::t('frontend','purchasing')."','".Yii::t('frontend','channel')."')";
    		$sql=$sql." and object_name in ".$default_option;
    	}else{
    		$sql=$sql." and object_name like '%".$searchDbInforItem."%'";
    	}
    	$query=MsSelectTemp::findBySql($sql);
    	$data= $query->asArray()
    			->all();
    	
    	if(trim($searchDbInforItem)==''){}else{
    		//取岗位信息
    		$sql_pos="select * from eln_ms_upod_view t where not exists "." (select * from eln_ms_select_temp p where t.object_id=p.select_id and p.mission_id= '".$uuid."')"
    				." and   object_type='Position' and t.company_id='".$company_id."' "." and object_name like '%".$searchDbInforItem."%'"
    						." UNION ALL  select * from eln_ms_upod_view t where object_type='Position' and company_id is null"
    								." and object_name like '%".$searchDbInforItem."%'"		;
    		
    		$query_pos=MsSelectTemp::findBySql($sql_pos);
    		$data_pos= $query_pos->asArray()
    		->all();
    		$data=ArrayHelper::merge($data,$data_pos);
    		
    	}
    	
    	Yii::$app->response->format = Response::FORMAT_JSON;
    	return $data;
    }
    
    public function actionSelected($mission_id,$select_id){
    	$service = new MessageService();
    	$service->saveSelected($select_id, $mission_id);
    	Yii::$app->response->format = Response::FORMAT_JSON;
    	return ['result' => 'success'];
    }
    
    public function actionDeleteSelected($mission_id,$select_id){
    	$service = new MessageService();
    	$service->deleteSelected($select_id, $mission_id);
    	Yii::$app->response->format = Response::FORMAT_JSON;
    	return ['result' => 'success'];
    }

    public function actionMsTaskPushSave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $pushForm = new TaskPushForm();
        $pushForm->load(Yii::$app->request->post());

        $service = new MessageService();

        $service->pushMessageByCourse($pushForm->courses, $pushForm->users);
        return ['result' => 'success'];
    }
    
    public function actionMsAdminPushSave()
    {
    	Yii::$app->response->format = Response::FORMAT_JSON;
    	$sponsor_id = Yii::$app->user->getId();
    	$pushForm = new TaskPushForm();
    	$pushForm->load(Yii::$app->request->post());
    
    	$courses=$pushForm->courses;
    	$user=$pushForm->users;
    	
    	//域id
    	$userDomainService = new UserDomainService();
    	$domainIds = $userDomainService->getSearchListByUserId($sponsor_id);
    	if (isset($domainIds) && $domainIds != null) {
    		$domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');
    	
    		$domainIds = array_keys($domainIds);
    	}
    	
    	
		$externalSystemService = new ExternalSystemService();
		$admin_push_url = $externalSystemService->getExternalSystemInfoByExternalSystemCode("elearning-task-service")->api_address;
		$admin_push_url .= "text";
    	$data_string= json_encode(array('courses'=>$courses, 'user'=>$user,'sponsor_id'=>$sponsor_id,'domain_ids'=>$domainIds));
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_URL, $admin_push_url);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    	'Content-Type: text/plain; charset=utf-8',
    	'Content-Length: ' . strlen($data_string))
    	);
    	ob_start();
    	curl_exec($ch);
    	$return_content = ob_get_contents();
    	ob_end_clean();
    	
    	$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    	if($return_code!=200){
    		throw new Exception(" 1111");
    	}
    	return ['result' => 'success'];
    }
    
    
}