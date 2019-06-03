<?php

namespace common\services\learning;


use common\base\BaseActiveRecord;
use Yii;

use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\db\Query;



use common\models\learning\LnTeacher;
use common\models\framework\FwUser;
use common\services\framework\UserDomainService;
use common\models\framework\FwDomain;
use common\models\framework\FwUserPosition;
use common\models\learning\LnCourseTeacher;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseMarkSummary;
use common\models\framework\FwRole;
use common\models\framework\FwUserRole;
use common\models\learning\LnCourseOwner;
use yii\helpers\Html;
use common\helpers\TStringHelper;
use common\models\framework\FwPosition;


class TeacherManageService extends LnTeacher
{
	const STATUS_NORMAL = '1';
	const STATUS_DISABLE = '2';
	const ROLE_TEACHER_CODE = "Teacher";

	/**
	 * 是否是老师
	 * @return boolean
	 */
	public function isTeacher()
	{

		$user_id = Yii::$app->user->getId();

		$lnTeacher = LnTeacher::find(false)
			->andWhere(LnTeacher::tableName() . ".user_id ='" . $user_id . "'")
			->asArray()
			->all();

		if (count($lnTeacher) > 0) {
			return "1";
		} else {
			return "0";
		}

	}


	public function findUserInfo($id)
	{

		$userDomainService = new UserDomainService();

		$query = FwUser::find(false)
			->andWhere(FwUser::tableName() . ".kid ='" . $id . "'")
			->leftjoin('{{%fw_orgnization}} as t1', 't1.kid = ' . FwUser::tableName() . ".orgnization_id and t1.is_deleted='0'")
			->leftjoin('{{%fw_company}} as t2', 't2.kid = ' . FwUser::tableName() . ".company_id and t2.is_deleted='0'")
			->leftjoin('{{%fw_user}} as t3', 't3.kid = ' . FwUser::tableName() . ".reporting_manager_id and t3.is_deleted='0'")
			->select(FwUser::tableName() . ".*,t1.orgnization_name,t2.company_name,t3.real_name as real_name1")
			->asArray()
			->one();


		$user_id = $id;
		$domainIds = $userDomainService->getSearchListByUserId($user_id);

		$sub_sql = "";
		if (isset($domainIds) && $domainIds != null) {
			$domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');

			$domainIds = array_keys($domainIds);
		}
		$user_domain_ids = "";
		foreach ($domainIds as $domain_id) {
			$sub_sql .= "'" . $domain_id . "'" . ",";
			$user_domain_ids .= $domain_id . ",";
		};
		$sub_sql = trim($sub_sql, ',');
		$user_domain_ids = trim($user_domain_ids, ',');

		$domains = FwDomain::find(false)
			->andWhere(FwDomain::tableName() . ".kid in ('" . $query['domain_id'] . "')")
			->asArray()
			->all();

		$positions = FwUserPosition::find(false)
			->andWhere(FwUserPosition::tableName() . ".user_id ='" . $user_id . "'")
			->leftjoin('{{%fw_position}} as t1', 't1.kid = ' . FwUserPosition::tableName() . ".position_id and t1.is_deleted='0'")
			->andWhere(FwUserPosition::tableName().".status ='". self::STATUS_FLAG_NORMAL."' ")
			->select("t1.position_name")
			->asArray()
			->all();
		$user = [];

		if ($query) {
			$user['user_id'] = $query['kid'];
			$user['company_name'] = $query['company_name'];
			$user['user_name'] = $query['user_name'];
			$user['company_id'] = $query['company_id'];
			$user['orgnization_name'] = $query['orgnization_name'];
			$user['orgnization_id'] = $query['orgnization_id'];
			$user['domain_id'] = $user_domain_ids;

			$domain_name_tmp = "";
			foreach ($domains as $d_name) {
				$domain_name_tmp .= $d_name['domain_name'] . ",";
			}
			$domain_name_tmp = trim($domain_name_tmp, ',');
			$user['domain_name'] = $domain_name_tmp;
			$user['reporting_manager'] = $query['real_name1'];

			$position_name_tmp = "";
			foreach ($positions as $posi) {
				$position_name_tmp .= $posi['position_name'] . ",";
			}
			$position_name_tmp = trim($position_name_tmp, ',');
			$user['position_name'] = $position_name_tmp;


		}
		return $user;

	}


	public function getUser($user_name)
	{
		$c_user_id = Yii::$app->user->getId();
		$userDomainService = new UserDomainService();
		$c_domainIds = $userDomainService->getSearchListByUserId($c_user_id);

		$c_sub_sql = "";
		if (isset($c_domainIds) && $c_domainIds != null) {
			$c_domainIds = ArrayHelper::map($c_domainIds, 'kid', 'kid');

			$c_domainIds = array_keys($c_domainIds);
		}

		foreach ($c_domainIds as $c_domain_id) {
			$c_sub_sql .= "'" . $c_domain_id . "'" . ",";

		};
		$c_sub_sql = trim($c_sub_sql, ',');

		$query = FwUser::find(false)
			->andWhere(FwUser::tableName() . ".user_name ='" . $user_name . "'")
			->andWhere(FwUser::tableName() . ".status ='1'")
			->andWhere(FwUser::tableName() . ".domain_id in (" . $c_sub_sql . ")")
			->leftjoin('{{%fw_orgnization}} as t1', 't1.kid = ' . FwUser::tableName() . ".orgnization_id and t1.is_deleted='0'")
			->leftjoin('{{%fw_company}} as t2', 't2.kid = ' . FwUser::tableName() . ".company_id and t2.is_deleted='0'")
			->leftjoin('{{%fw_user}} as t3', 't3.kid = ' . FwUser::tableName() . ".reporting_manager_id and t3.is_deleted='0'")
			->select(FwUser::tableName() . ".*,t1.orgnization_name,t2.company_name,t3.real_name as real_name1")
			->asArray()
			->one();


		$user_id = $query['kid'];
// 		$domainIds = $userDomainService->getSearchListByUserId($user_id);


 		$sub_sql = "'".$query['domain_id']."'";
// 		if (isset($domainIds) && $domainIds != null) {
// 			$domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');

// 			$domainIds = array_keys($domainIds);
// 		}
		$user_domain_ids = $query['domain_id'];
// 		foreach ($domainIds as $domain_id) {
// 			$sub_sql .= "'" . $domain_id . "'" . ",";
// 			$user_domain_ids .= $domain_id . ",";
// 		};
// 		$sub_sql = trim($sub_sql, ',');
// 		$user_domain_ids = trim($user_domain_ids, ',');


		//add by baoxianjian 防止用户名不存在时会报错
		if(!$sub_sql)
		{
			return false;
		}


		$domains = FwDomain::find(false)
			->andWhere(FwDomain::tableName() . ".kid in (" . $sub_sql . ")")
			->asArray()
			->all();

		$positions = FwUserPosition::find(false)
			->andWhere(FwUserPosition::tableName() . ".user_id ='" . $user_id . "'")
			->leftjoin('{{%fw_position}} as t1', 't1.kid = ' . FwUserPosition::tableName() . ".position_id and t1.is_deleted='0'")
			->andWhere("t1.status ='". self::STATUS_FLAG_NORMAL."' ")
			->andWhere(FwUserPosition::tableName().".status ='". self::STATUS_FLAG_NORMAL."' ")
			->select("t1.position_name")
			->asArray()
			->all();
		
		
		
		$user = [];

		if ($query) {
			$user['user_id'] = $query['kid'];
			$user['company_name'] = $query['company_name'];
			$user['company_id'] = $query['company_id'];
			$user['orgnization_name'] = $query['orgnization_name'];
			$user['orgnization_id'] = $query['orgnization_id'];
			$user['domain_id'] = $user_domain_ids;

			$domain_name_tmp = "";
			foreach ($domains as $d_name) {
				$domain_name_tmp .= $d_name['domain_name'] . ",";
			}
			$domain_name_tmp = trim($domain_name_tmp, ',');
			$user['domain_name'] = $domain_name_tmp;
			$user['reporting_manager'] = $query['real_name1'];

			$position_name_tmp = "";
			foreach ($positions as $posi) {
				$position_name_tmp .= $posi['position_name'] . ",";
			}
			$position_name_tmp = trim($position_name_tmp, ',');
			$user['position_name'] = $position_name_tmp;


		}
		return $user;
	}

	public function getTeacherRole()
	{

		return FwRole::find(false)
			->andWhere(FwRole::tableName() . ".role_code = '" . TeacherManageService::ROLE_TEACHER_CODE . "'")
			->asArray()
			->one();
	}


	public function saveTeacher($params)
	{
		$lnTeacher = new LnTeacher();

		foreach ($params as $k => $v) {
			if ($k == "teacher_name") {
				$lnTeacher->$k = Html::encode($v);
			} else if ($k == "teacher_nick") {
				$lnTeacher->$k = Html::encode($v);
			} else if ($k == "graduate_school") {
				$lnTeacher->$k = Html::encode($v);
			} else if ($k == "teach_domain") {
				$lnTeacher->$k = Html::encode($v);
			} else if ($k == "description") {
				$lnTeacher->$k = Html::encode($v);
			} else if ($k == "teacher_title") {
				$lnTeacher->$k = Html::encode($v);
			} else {
				$lnTeacher->$k = $v;
			};

		}

		$lnTeacher->birthday = date("Y-m-d", strtotime($params['birthday']));
		$lnTeacher->data_from = LnTeacher::DATA_FROM_USER_MANAGEMENT;
		$lnTeacher->language = "chinese_simplified";
		$lnTeacher->timezone = "Asia/Beijing";

		$lnTeacher->save();

		$this->roleEmpowerment($params);


	}

	public function roleEmpowerment($params)
	{
		$role = $this->getTeacherRole();

		$userRole = FwUserRole::find(false)
			->andWhere("role_id = '" . $role['kid'] . "' and user_id='" . $params['user_id'] . "'  and status='1'")
			->asArray()
			->one();
		if (count($userRole) == 0) {
			$fwUserRole = new FwUserRole();
			$fwUserRole->user_id = $params['user_id'];
			$fwUserRole->role_id = $role['kid'];
			$fwUserRole->status = FwUserRole::STATUS_FLAG_NORMAL;
			$fwUserRole->start_at = time();
			$fwUserRole->save();
		}
	}

	public function updateTeacher($params)
	{
		$lnTeacher = new LnTeacher();

		$attributes = [];

		foreach ($params as $k => $v) {
			if ($k == "teacher_name") {
				$attributes[$k] = Html::encode($v);
			} else if ($k == "teacher_nick") {
				$attributes[$k] = Html::encode($v);
			} else if ($k == "graduate_school") {
				$attributes[$k] = Html::encode($v);
			} else if ($k == "teach_domain") {
				$attributes[$k] = Html::encode($v);
			} else if ($k == "description") {
				$attributes[$k] = Html::encode($v);
			} else if ($k == "teacher_title") {
				$attributes[$k] = Html::encode($v);
			} else {
				$attributes[$k] = $v;
			};

		}


		$id = $params['kid'];

		$model = LnTeacher::findOne($id);
		$user_id = $model->user_id;
		$lnTeacher->updateAll($attributes, "kid = '" . $id . "'");
		LnTeacher::removeFromCacheByKid($model->kid);

		if ($params['user_id'] != $user_id) {
			$ts = LnTeacher::find(false)
				->andWhere("user_id='" . $user_id . "' ")
				->asArray()
				->all();

			if (count($ts) > 0) {
				$this->roleEmpowerment($params);
			} else {

				$role = $this->getTeacherRole();


				$query = new Query();
				$query->createCommand()
					->delete('{{%fw_user_role}}',
						[
							'user_id' => $user_id,
							'role_id' => $role['kid'],
							'status' => FwUserRole::STATUS_FLAG_NORMAL
						])
					->execute();

				$this->roleEmpowerment($params);
			}
		}


	}

	public function findTeacher($id)
	{
		return LnTeacher::findOne($id);
	}

	/**
	 * add by baoxianjian 17:28 2016/4/26
	 *根据用户id得到讲师
	 * @param $uid
	 * @return LnTeacher
	 */
	public function findInnerTeacherByUserId($uid)
	{
		if (!$uid) {return false;}
		$lnTeacher = LnTeacher::find(false)
			->andWhere(['=', 'user_id', $uid])
			->andWhere(['=', 'teacher_type', LnTeacher::TEACHER_TYPE_INTERNAL])
			->one();
		return $lnTeacher;
	}

	/**
	 * add by baoxianjian 17:28 2016/4/26
	 * 得到讲师绑定的用户账号个数
	 * @param $userName
	 * @return int
	 */
	public function getInnerTeacherCanBindFromUser($userId,$teacherKid)
	{
		if(!$userId){return false;}
		//if (!$userName=trim($userName)) {return false;}

		//$userModel=FwUser::findByUsername($userName);

		$query = LnTeacher::find(false);
		$query=$query ->select("kid")
			->andWhere(['=', 'user_id', $userId]);
		$query=$query->andWhere(['=', 'teacher_type', LnTeacher::TEACHER_TYPE_INTERNAL]);

		$bindList=$query->all();

		if(is_array($bindList))
		{
			$bindCount=count($bindList);
		}
		else
		{
			$bindCount=0;
		}

		if($bindCount==0)
		{
			$canBind=true;
		}
		//else if($bindCount==1 && $teacherKid)
		else if($teacherKid)
		{
			$canBind=false;
			//如果绑定的是当前讲师，则可以进行绑定
			foreach ($bindList as $item)
			{
				if ($item['kid'] == $teacherKid) {
					$canBind = true;
				}
			}
		}
		else
		{
			$canBind=false;
		}
		return $canBind;
}

	public function findTeacherCourse($id)
	{
		$course = LnCourseTeacher::find(false)
			->andWhere(LnCourseTeacher::tableName() . ".teacher_id ='" . $id . "'")
			->leftjoin('{{%ln_course}} as t1', 't1.kid = ' . LnCourseTeacher::tableName() . ".course_id and t1.is_deleted='0'")
			->andWhere("t1.course_type in('" . LnCourse::COURSE_TYPE_ONLINE . "','" . LnCourse::COURSE_TYPE_FACETOFACE . "')")
			->andWhere("t1.status ='" . LnCourse::STATUS_FLAG_NORMAL . "'")
			->select("t1.course_type,t1.kid,t1.start_time,t1.end_time,t1.open_start_time,t1.open_end_time,t1.course_name,t1.open_status")
			->asArray()
			->addOrderBy(['open_start_time' => SORT_DESC])
			->all();

		$courseTmp = [];

		foreach ($course as $c) {

			$return_val = TStringHelper::getCourseStatus($c['kid']);

			$c['return_val'] = $return_val;
			if ($c['course_type'] == '1') {
				$c['show_start_time'] = date("Y年m月d日", $c['open_start_time']);
				if ($c['open_end_time']) {
					$c['show_end_time'] = date("Y年m月d日", $c['open_end_time']);
				} else {
					$c['show_end_time'] = "永久";
				}

			} else {
				$c['show_start_time'] = date("Y年m月d日", $c['start_time']);
				if ($c['end_time']) {
					$c['show_end_time'] = date("Y年m月d日", $c['end_time']);
				} else {
					$c['show_end_time'] = "永久";
				}

			}
			array_push($courseTmp, $c);
		}


		return $courseTmp;
	}

	public function getTimeList()
	{
		$chart = LnCourseMarkSummary::find(false)
			->groupBy("YEAR")
			->select("YEAR")
			->asArray()
			->addOrderBy(['YEAR' => SORT_DESC])
			->all();
		return $chart;
	}

	public function getCourseRate($id, $time_param)
	{

		$course_tmp = $this->findTeacherCourse($id);

		$course = [];

		foreach ($course_tmp as $c) {

			if ($c['course_type'] == '1') {
				$c['open_start_time'] = date("Y年m月d日", $c['open_start_time']);
				if ($c['open_end_time']) {
					$c['open_end_time'] = date("Y年m月d日", $c['open_end_time']);
				} else {
					$c['open_end_time'] = "永久";
				}

			} else {
				$c['open_start_time'] = date("Y年m月d日", $c['start_time']);
				if ($c['end_time']) {
					$c['open_end_time'] = date("Y年m月d日", $c['end_time']);
				} else {
					$c['open_end_time'] = "永久";
				}

			}


			$chart = LnCourseMarkSummary::find(false)
				->andWhere(LnCourseMarkSummary::tableName() . ".course_id ='" . $c['kid'] . "'")
				->andWhere(LnCourseMarkSummary::tableName() . ".YEAR =" . $time_param . "")
				->select("MONTH,course_mark,kid")
				->asArray()
				->addOrderBy(['MONTH' => SORT_ASC])
				->all();

			$chart_label = [];
			$chart_data = [];

			$total_marks = 0;
			foreach ($chart as $ch) {
				array_push($chart_label, $this->getMonth($time_param, $ch['MONTH']));
				array_push($chart_data, $ch['course_mark']);
				$total_marks += $ch['course_mark'];
			}

			$per_marks = round($total_marks / count($chart), 2);
			$c['chart_label'] = $chart_label;
			$c['chart_data'] = $chart_data;
			$c['per_marks'] = $per_marks;
			array_push($course, $c);
		}

		return $course;
	}

	function getMonth($y, $m)
	{

		if ($m < 10) {
			return $y . '0' . $m;
		} else {
			return $y . $m;
		}

	}

// 	public function findTeacherCourse($id){
// 		$course= LnCourseTeacher::find(false)
// 		->andWhere(LnCourseTeacher::tableName().".teacher_id ='".$id."'")
// 		->leftjoin('{{%ln_course}} as t1','t1.kid = '.LnCourseTeacher::tableName().".course_id and t1.is_deleted='0'")
// 		->andWhere("t1.course_type='".LnCourse::COURSE_TYPE_FACETOFACE."'")
// 		->select("t1.kid,t1.open_start_time,t1.open_end_time,t1.course_name,t1.open_status")
// 		->asArray()
// 		->all();
// 		return $course;
// 	}


	public function search($params)
	{

		$query = LnTeacher::find(false);
		$keyword = $params['keyword'];

		if (isset($params['keyword'])) {
			$keyword = $params['keyword'];
			$keyword = trim($keyword);
		} else {
			$keyword = "";
		}
		if(isset($params['tg']) && $params['tg']!='-1')
		{
			$teacher_level=trim($params['tg']);
			$query->andFilterWhere(["=", "teacher_level",$teacher_level]);
		}

		if ($keyword) {
			$query->andWhere("teacher_name like '%{$keyword}%'  or teacher_nick like '%{$keyword}%'");
		}

		$query
			->andFilterWhere(["=", "company_id", Yii::$app->user->identity->company_id]);

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		$this->load($params);


		$dataProvider->setSort(false);
		$query->addOrderBy(['created_at' => SORT_DESC]);
		/*echo ($query->createCommand()->getRawSql());*/
		return $dataProvider;
	}


	public function deleteTeacher($id)
	{

		$lnTeacher = new LnTeacher();

		$delModel = $lnTeacher->findOne($id);

		$user_id = $delModel->user_id;
		$delModel->delete();

		//删除ln_course_owner关系

		$lnCourseOwner = new LnCourseOwner();

		$attributes = [

			'is_deleted' => LnCourseOwner::DELETE_FLAG_YES,

		];

		$lnCourseOwner->updateAll($attributes, "user_id = '" . $id . "'");


		$ts = LnTeacher::find(false)
			->andWhere("user_id='" . $user_id . "' ")
			->asArray()
			->all();

		if (count($ts) == 0) {

			$role = $this->getTeacherRole();

			$query = new Query();
			$query->createCommand()
				->delete('{{%fw_user_role}}',
					[
						'user_id' => $user_id,
						'role_id' => $role['kid'],
						'status' => FwUserRole::STATUS_FLAG_NORMAL
					])
				->execute();
		}


	}


	public function clearPic($id)
	{
		//更新头像
		$teacher = LnTeacher::findOne($id);
		$teacher->teacher_thumb_url = null;
		$teacher->save();
	}

	/*课程查询讲师*/
	public function courseSearchTeacher($keyword = null, $kids = null, $input = true, $companyId = null)
	{
		if ($input && empty($keyword)) return false;
		$query = LnTeacher::find(false);
		if (!empty($kids) && is_array($kids)) {
			$query->andFilterWhere(['in', $this->tableName() . '.kid', $kids]);
		} else if (!empty($kids) && !is_array($kids)) {
			$query->andFilterWhere(['=', $this->tableName() . '.kid', $kids]);
		}
		if (empty($companyId)){
			$companyId = Yii::$app->user->identity->company_id;
		}
		$query->innerJoin(FwUser::tableName(), FwUser::tableName() . '.kid=' . $this->tableName() . '.user_id')
			->andFilterWhere(["=", $this->tableName() . ".company_id", $companyId]);
		if (!empty($keyword)) {
			$query->andWhere($this->tableName() . ".teacher_name like '%{$keyword}%' or " . $this->tableName() . ".teacher_nick like '%{$keyword}%' or ".FwUser::tableName().".user_no like '%{$keyword}%' or ".FwUser::tableName().".email like '%{$keyword}%'");
		}
		$result = $query->select([$this->tableName() . ".kid", $this->tableName() . ".company_id", $this->tableName() . ".teacher_name", $this->tableName() . ".teacher_type", $this->tableName() . ".user_id", FwUser::tableName() . ".email"])
			->asArray()
			->all();
		return $result;
	}

	function getTeacherLevels($id=null,$type=null,$company_id=null,$withCache=true)
	{
		if(!$company_id){$company_id= Yii::$app->user->identity->company_id;}
		return parent::getTeacherLevels($id,$type,$company_id,false);
	}

	function getTeacherNamesByCourseId($course_id)
	{
		$teacherModel = new LnCourseTeacher();
		$temp = $teacherModel->getTeacherAll($course_id);
		$teacherStr = '';
		foreach ($temp as $t) {
			$teacherStr .= $t['teacher_name'] . ',';
		}
		$teacherStr = rtrim($teacherStr, ',');
		return $teacherStr;
	}




}