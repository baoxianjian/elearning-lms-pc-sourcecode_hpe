<?php

namespace common\services\learning;

use components\widgets\TPagination;
use Yii;
use common\models\learning\LnInvestigation;
use common\models\learning\LnInvestigationQuestion;
use common\models\learning\LnInvestigationOption;
use common\models\learning\LnHomework;
use common\models\learning\LnHomeworkFile;
use common\models\learning\LnHomeworkResult;

use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\db\Query;
use common\models\learning\LnModRes;
use common\models\learning\LnCourseactivity;
use common\models\learning\LnInvestigationResult;
use common\models\learning\LnResComplete;
use common\models\framework\FwUser;
use common\models\framework\FwOrgnization;
use common\models\framework\FwPosition;
use common\models\framework\FwUserPosition;
use common\base\BaseActiveRecord;
use yii\data\Pagination;
use common\models\learning\LnRelatedUser;
use common\models\framework\FwUserDisplayInfo;


class InvestigationResultService extends LnInvestigation
{
	
	public function getSingleVoteStUserInfoResult($id, $params) {
		$m_table = LnInvestigationResult::tableName ();
		$sql_ = "";
		// 推送的用户
		$sql_push_users = "select user_id from eln_ln_related_user  where learning_object_id =
               '$id'  and is_deleted = '0' ";
		// 提交的用户
		$query_tmp = LnInvestigationResult::find ( false )
			->andFilterWhere ( [ "=","investigation_id",$id ] )
			->orderBy ( $m_table . ".created_at asc" )
			->groupBy ( $m_table . ".user_id" )
			->select ( "user_id,GROUP_CONCAT(option_title) as option_title,
					.$m_table.created_at " );
		$query_submit_user = $query_tmp->createCommand ()->rawSql;
		
		//未提交的用户
		$query_no_submit_user = "
				select t.user_id from (
						select user_id from eln_ln_related_user  where learning_object_id =
               '$id'  and is_deleted = '0'
               ) t where not exists 
               (
           		    SELECT user_id, GROUP_CONCAT(option_title) as option_title, created_at FROM eln_ln_investigation_result  r
                WHERE is_deleted='0' AND investigation_id = '$id' and t.user_id=r.user_id
                 GROUP BY user_id           
               )
				";
		
		$rel_users_type = $params ['rel_users_type'];
		
		$sql_user_info=FwUserDisplayInfo::tableName();
		
		if ($rel_users_type == "all") {
			$sql_ = $sql_push_users;
			//加上查询条件，关联eln_fw_user_display_info
			if(trim($params['keyword'])!=""){
				$keyword=trim($params['keyword']);
				$sql_user_info="(select user_id,real_name,orgnization_name,position_name,user_name from  ".FwUserDisplayInfo::tableName()." where  real_name like '%$keyword%' or email like '%$keyword%' )";
				$sql_="select  rel_user.user_id from eln_ln_related_user rel_user join ".$sql_user_info." u_info on 
				  rel_user.user_id=u_info.user_id  where learning_object_id =
             		  '$id'  and is_deleted = '0'  ";
			}			
		}
		
		if ($rel_users_type == "yes") {
			$sql_ = $query_submit_user;
			
			//加上查询条件，关联eln_fw_user_display_info
			if(trim($params['keyword'])!=""){
				$keyword=trim($params['keyword']);
				$sql_user_info="(select user_id,real_name,orgnization_name,position_name,user_name from  ".FwUserDisplayInfo::tableName()." where  real_name like '%$keyword%' or email like '%$keyword%' )";
				$sql_=" SELECT r.user_id, GROUP_CONCAT(option_title) as option_title, r.created_at FROM eln_ln_investigation_result  r
				        join ".$sql_user_info." u_info on  r.user_id=u_info.user_id 
	                WHERE is_deleted='0' AND investigation_id = '$id' 
	                 GROUP BY r.user_id   order by r.created_at asc ";
			}
		}
		
		if ($rel_users_type == "no") {
			$sql_ = $query_no_submit_user;
			
			//加上查询条件，关联eln_fw_user_display_info
			if(trim($params['keyword'])!=""){
				
				$keyword=trim($params['keyword']);
				$sql_user_info="(select user_id,real_name,orgnization_name,position_name,user_name from  ".FwUserDisplayInfo::tableName()." where  real_name like '%$keyword%' or email like '%$keyword%' )";
				$sql_="select t1.* from (" .$sql_.") t1  join ".$sql_user_info." u_info on  t1.user_id=u_info.user_id ";
			}
		}
		
		$sql_count = "select count(1) as c from ($sql_) tt ";
		$db = \Yii::$app->db;
		$count_ = $db->createCommand ( $sql_count )->queryAll ();
		$count = $count_ [0] ['c'];
		$pages = new Pagination ( [ 
				'defaultPageSize' => $params ['size'],
				'totalCount' => $count 
		] );
		$result ['pages'] = $pages;
		$result_sql_tmp = $sql_ ;
		if ($rel_users_type == "all") {
			$result_sql_tmp = "select rel.user_id,res.option_title,res.created_at from (" . $result_sql_tmp . ") rel 
               left join (
      			   SELECT user_id, GROUP_CONCAT(option_title) as option_title, created_at FROM eln_ln_investigation_result  r
                WHERE is_deleted='0' AND investigation_id = '$id'
                 GROUP BY user_id   )  res
                 on res.user_id=rel.user_id  order by  ifnull(res.created_at,9999999999) asc ". " limit $pages->offset,$pages->limit";
		}else{
			$result_sql_tmp=$sql_. " limit $pages->offset,$pages->limit";
		}
		
		$result_sql = "select t1.*,user.real_name,user.user_name,user.orgnization_name,user.position_name from ( " . $result_sql_tmp . ") t1 left join " . $sql_user_info. "  user
  				 on t1.user_id=user.user_id ";
		
		if($rel_users_type != "no"){
			$result_sql=$result_sql. " order by  ifnull(t1.created_at, 9999999999) asc";
		}
		
		$sub_result_arr = $db->createCommand ( $result_sql )->queryAll ();
		$datas = [ ];
		
		$num = 1;
		foreach ( $sub_result_arr as $ch ) {
			if ($ch ['created_at'] != null) {
				$ch ['created_at'] = date ( "Y年m月d日 H:i:s", $ch ['created_at'] );
			} else {
				$ch ['created_at'] = "";
			}
			$ch ['user_info'] = "参与者" . $num;
			$ch ['kid'] =$ch ['user_id'];
			$num = $num + 1;
			array_push ( $datas, $ch );
		}
		
		$result ['data'] = $datas;
		
		return $result;
	}
	
	
	
	public function getCourseVoteStUserInfoResult($id,$params)
	{
		$m_table = LnInvestigationResult::tableName();
		$join_str = " left join ";
		if ($params['all_user_count_is_0'] == 'true') {
			$join_str = " right join ";
		}

		$course_id = $params["course_id"];
		$user_pos_org_sql = " (
			select user_.* from (select * from eln_ln_related_user   where learning_object_id =
               '$course_id'
           and is_deleted = '0' ) rel $join_str (
	        	 select fu.kid, fu.real_name,fu.email, org.orgnization_name,GROUP_CONCAT(position_name) as position_name
	            	                       from eln_fw_user fu
	                	                      left join eln_fw_orgnization org
	                                        on (fu.orgnization_id = org.kid)
	                                       join (select up.user_id, p.position_name
	                                                   from eln_fw_user_position up
	                                                   left join eln_fw_position p
	                                                     on up.position_id = p.kid
	                                                  where up.is_deleted = '0' and p.status='1' and up.status='1'  
	                                                    and p.is_deleted = '0'
	                                                    up.user_id in (select user_id from eln_ln_related_user   where learning_object_id =
               '$course_id')
	                                                    
	                                                    ) t
	                                        ON (fu.kid = t.user_id)
	                                     where fu.is_deleted = '0'
	                                       and org.is_deleted = '0'  group by kid ) user_
	                                       on rel.user_id= user_.kid
	                                    
					)";

		$rel_users_type = $params['rel_users_type'];
		$query_tmp = LnInvestigationResult::find(false)
			->andFilterWhere(["=", "investigation_id", $id])
			->andFilterWhere(["=", "course_id", $params['course_id']])
			->orderBy($m_table . ".created_at asc")
			->groupBy($m_table . ".user_id")
			->select("user_id,GROUP_CONCAT(option_title) as option_title,
							.$m_table.created_at ");
		$query_sql = $query_tmp->createCommand()->rawSql;
		$sql_ = "select * from  $user_pos_org_sql t1 left join ($query_sql) t2  on (t1.kid=t2.user_id)  where 1=1";

		if ($rel_users_type == "yes") {
			$sql_ = $sql_ . "  and user_id is not null";
		}
		if ($rel_users_type == "no") {
			$sql_ = $sql_ . "  and user_id is  null";
		}
		if (trim($params['keyword']) != "") {
			$keyword = trim($params['keyword']);
			$sql_ = $sql_ . " and (real_name like '%$keyword%' or email like '%$keyword%')";
		}

		$sql_count = "select count(1) as c from ($sql_) tt";
		$db = \Yii::$app->db;
		$count_ = $db->createCommand($sql_count)->queryAll();
		$count = $count_[0]['c'];
		$pages = new Pagination(['defaultPageSize' => $params['size'], 'totalCount' => $count]);
		$result['pages'] = $pages;
		$result_sql = $sql_ . " limit $pages->offset,$pages->limit";
		$sub_result_arr = $db->createCommand($result_sql)->queryAll();

		$datas = [];

		$num = 1;
		foreach ($sub_result_arr as $ch) {
			if ($ch['created_at'] != null) {
				$ch['created_at'] = date("Y年m月d日 H:i:s", $ch['created_at']);
			} else {
				$ch['created_at'] = "";
			}
			$ch['user_info'] = "参与者" . $num;
			$ch ['kid'] =$ch ['user_id'];
			$num = $num + 1;
			array_push($datas, $ch);
		}

		$result['data'] = $datas;

		return $result;
	}
	
	public function getOnLineInvestigation($mod_id)
	{
// 		$in_sql="(select * from {{%ln_investigation}} where is_deleted='0')";
// 		$m_table=LnCourseactivity::tableName();
// 		$arr=[];
// 		$datas=[];
// 		$arr=LnCourseactivity::find(false)
// 			->innerjoin($in_sql.' as t1','t1.kid = '.$m_table.".object_id")
// 			->andFilterWhere(["=","object_type","investigation"])
// 			->andFilterWhere(["=","course_id",$course_id])
// 			->select("t1.kid,t1.investigation_type,t1.title,".$m_table.".created_at")
// 			->asArray()
// 			->all();
// 		foreach ($arr as $ch) {
// 			$ch['created_at'] = date("Y年m月d日 H:i:s",$ch['created_at']);
		
// 			$num=$num+1;
// 			array_push($datas, $ch);
// 		}
// 		return $datas;

		$modres=LnModRes::find(false)
			->andFilterWhere(["=","kid",$mod_id])
			->asArray()
			->all();
		
		$course_id=$modres[0]['course_id'];
		$courseactivity_id=$modres[0]['courseactivity_id'];
		
		$courseactivity=LnCourseactivity::find(false)
			->andFilterWhere(["=","kid",$courseactivity_id])
			->asArray()
			->all();
		
		$investigation_id=$courseactivity[0]['object_id'];
		
		
		$investigation=LnInvestigation::find(false)
				->andFilterWhere(["=","kid",$investigation_id])
				->asArray()
				->all();

		$result=[];
		$result['id']=$investigation_id;
		$result['course_id']=$course_id;
		$result['type']=$investigation[0]['investigation_type'];
		
		return $result;
		 
	}
	
	public function getAllUsersCount($company_id,$learning_object_type,$learning_object_id){
		$result=LnRelatedUser::find(false)
			->andFilterWhere(["=","company_id",$company_id])
			->andFilterWhere(["=","learning_object_type",$learning_object_type])
			->andFilterWhere(["=","learning_object_id",$learning_object_id])
			->count('kid');

		return $result;
	}

    public function getSingleVoteStUserInfoResultOne($id, $user_id, $params)
    {
        $m_table = LnInvestigationResult::tableName();

        $join_str = " left join ";
        if ($params['all_user_count_is_0'] == 'true') {
            $join_str = " right join ";
        }

        $user_pos_org_sql = " (
			select user_.* from (select * from eln_ln_related_user   where learning_object_id =
               '$id' and user_id='$user_id'
           and is_deleted = '0' ) rel $join_str (
	        	 select fu.kid, fu.real_name, fu.email,org.orgnization_name,GROUP_CONCAT(position_name) as position_name
	            	                       from eln_fw_user fu
	                	                      left join eln_fw_orgnization org
	                                        on (fu.orgnization_id = org.kid)
	                                       join (select up.user_id, p.position_name
	                                                   from eln_fw_user_position up
	                                                   left join eln_fw_position p
	                                                     on up.position_id = p.kid
	                                                  where up.is_deleted = '0' and p.status='1' and up.status='1' 
	                                                    and p.is_deleted = '0'
	                                              and       up.user_id in (select user_id from eln_ln_related_user   where learning_object_id =
               '$id')
	                                                    ) t
	                                        ON (fu.kid = t.user_id)
	                                     where fu.is_deleted = '0'
	                                       and org.is_deleted = '0'  group by kid ) user_
	                                       on rel.user_id= user_.kid

					)";

        $rel_users_type = $params['rel_users_type'];
        $query_tmp = LnInvestigationResult::find(false)
            ->andFilterWhere(["=", "user_id", $user_id])
            ->andFilterWhere(["=", "investigation_id", $id])
            ->orderBy($m_table . ".created_at asc")
            ->groupBy($m_table . ".user_id")
            ->select("user_id,GROUP_CONCAT(option_title) as option_title,
						.$m_table.created_at ");
        $query_sql = $query_tmp->createCommand()->rawSql;
        $sql_ = "select * from  $user_pos_org_sql t1 left join ($query_sql) t2   on (t1.kid=t2.user_id)  where 1=1";


        if ($rel_users_type == "yes") {
            $sql_ = $sql_ . "  and user_id is not null";
        }
        if ($rel_users_type == "no") {
            $sql_ = $sql_ . "  and user_id is  null";
        }
        if ($params['keyword'] != "") {
            $keyword = $params['keyword'];
            $sql_ = $sql_ . " and (real_name like '%$keyword%' or email like '%$keyword%')";
        }

        $sql_count = "select count(1) as c from ($sql_) tt";
        $db = \Yii::$app->db;
        $count_ = $db->createCommand($sql_count)->queryAll();
        $count = $count_[0]['c'];
        $pages = new Pagination(['defaultPageSize' => $params['size'], 'totalCount' => $count]);
        $result['pages'] = $pages;
        $result_sql = $sql_ . " limit $pages->offset,$pages->limit";
        $sub_result_arr = $db->createCommand($result_sql)->queryAll();
        $datas = [];

        $num = 1;
        foreach ($sub_result_arr as $ch) {
            if ($ch['created_at'] != null) {
                $ch['created_at'] = date("Y年m月d日 H:i:s", $ch['created_at']);
            } else {
                $ch['created_at'] = "";
            }
            $ch['user_info'] = "参与者" . $num;
            $num = $num + 1;
            array_push($datas, $ch);
        }


        $result['data'] = $datas;

        return $result;
    }
}