<?php
/**
* @name:服务器
* @author:baoxianjian
* @date:15:09 2016/1/26
*/

namespace common\services\learning;


use Yii;
use common\models\learning\LnCourseSignInSetting;
use common\models\learning\LnCourseSignIn;
use common\helpers\TTimeHelper;
use yii\db\Query;


class CourseSignInSettingService extends LnCourseSignInSetting{

    /**
     * 得到签到配置列表，根据课程id
     * @param $courseId 课程id
     * @param $sp 搜索字段数组
     * @param bool $mergeByDay 当天的进行合并
     * @return array
     */
    function getListByCourseId($courseId,$sp=null,$mergeByDay=true,$fields=null,$useCount=false)
    {
        if (!$courseId) {return false;}

        $query = new Query();
        $query->select("ss.kid,ss.course_id,ss.sign_date,ss.title,ss.start_at_str,ss.start_at,ss.end_at_str,ss.end_at,ss.is_all_day,ss.qr_code")
            ->from(LnCourseSignInSetting::tableName().' ss')
            ->andWhere(['=', 'ss.course_id', $courseId])
            ->andWhere(['=', 'ss.is_deleted', LnCourseSignInSetting::DELETE_FLAG_NO])
            ->orderBy('ss.sign_date ASC,ss.start_at ASC');

        if($sp['sign_date'])
        {
            $query->andWhere(['=', 'ss.sign_date', $sp['sign_date']]);
        }
        if($sp['ssids'])
        {
            $query->andWhere(['IN', 'ss.kid', $sp['ssids']]);
        }
        if($sp['start'])
        {
            $query->andWhere(['>=', 'ss.sign_date', $sp['start']]);
        }
        if($sp['end'])
        {
            $query->andWhere(['<=', 'ss.sign_date', $sp['end']]);
        }
        if($fields)
        {
            $query->select($fields);
        }
        

        $list = $query->all();

        if($useCount)
        {
            $sp['course_id']=$courseId;
            $useList=$this->getSignInSettingsUseCount($sp);

            foreach($list as $k=>$v)
            {
                $list[$k]['use_count']=intval($useList[$v['kid']]);
            }
        }

        if ($mergeByDay)
        {
            $signSettingList2 = array();
            foreach ($list as $v) {
                $signSettingList2[$v['sign_date']][] = $v;
            }
            return $signSettingList2;
        }
        return $list;
    }
    
    
    
    /**
     * 删除行记录，根据课程id和日期
     * @param $courseId
     * @param $signDate
     * @return bool
     */
    function deleteRowsByCourseIdAndSignDate($courseId,$signDate)
    {
        if (!$courseId) {return false;}
        if (!$signDate) {return false;}

        $sp['sign_date']=$signDate;
        $list=$this->getListByCourseId($courseId,$sp,false,'kid');

        $sp['course_id']=$courseId;
        $useList=$this->getSignInSettingsUseCount($sp);

        //print_r($list);exit;
        //print_r($useList);exit;

        $count=count($list);
        $successCount=0;
        foreach($list as $v)
        {
            if($useList[$v['kid']]){continue;}
            $result=LnCourseSignInSetting::deleteAll("kid=:kid", [':kid'=>$v['kid']]);
            if($result)
            {
                $successCount++;
            }
        }
        //var_dump($successCount);
        return array('result'=>$successCount==$count,'use_list'=>$useList,'successCount'=>$successCount);
        //return LnCourseSignInSetting:: deleteAll("course_id=:course_id AND sign_date=:sign_date", [':course_id'=>$courseId,':sign_date'=>$signDate]);
        //return LnCourseSignInSetting:: physicalDeleteAll("course_id=:course_id AND sign_date=:sign_date", [':course_id'=>$courseId,':sign_date'=>$signDate]);
    }

    /**
     * 删除行记录，根据课程id
     * @param $courseId
     * @return array
     */
    function deleteRowsByCourseId($courseId)
    {
        if (!$courseId) {return false;}
        //$courseId,$sp=null,$mergeByDay=true,$fields=null)

        $list=$this->getListByCourseId($courseId,null,false,'kid');
        $useList=$this->getSignInSettingsUseCountByCourseId($courseId);
        
        $count=count($list);
        $successCount=0;
        foreach($list as $v)
        {
            if($useList[$v['kid']]){continue;}
            $result=LnCourseSignInSetting::deleteAll("kid=:kid", [':kid'=>$v['kid']]);
            if($result)
            {
                $successCount++;
            }
        }
        return array('result'=>$successCount==$count,'use_list'=>$useList);
        //return LnCourseSignInSetting:: deleteAll("course_id=:course_id", [':course_id'=>$courseId]);
        //return LnCourseSignInSetting:: physicalDeleteAll("course_id=:course_id", [':course_id'=>$courseId]);
    }

    /**
     *
     * @param $kids
     * @return bool|int
     */
    function deleteRowsByKids($kids)
    {
        if (!$kids) {return false;}
        $sp['kids']=$kids;
        $useList=$this->getSignInSettingsUseCount($sp);
        
        $count=count($kids);
        $successCount=0;
        foreach($kids as $kid)
        {
            if($useList[$kid]){continue;}
            $result=LnCourseSignInSetting::deleteAll("kid=:kid", [':kid'=>$kid]);
            if($result)
            {
                $successCount++;
            }
        }

        return array('result'=>$successCount==$count,'use_list'=>$useList);

        //return LnCourseSignInSetting:: deleteAll("kid IN(:course_id)", [':sign_date'=>$kids]);
        //return return LnCourseSignInSetting:: physicalDeleteAll("course_id=:course_id", [':course_id'=>$courseId]);
    }


    function getSignInSettingsUseCount($sp)
    {
        if(!$sp || !is_array($sp)){return false;}
        $query=new Query();
        $query ->select("COUNT(*) as count,s.sign_in_setting_id")
            ->from(LnCourseSignInSetting::tableName()." ss")
            ->leftjoin(LnCourseSignIn::tableName() . ' as s', 'ss.kid=s.sign_in_setting_id')
       //     ->andWhere(['=','ss.course_id',$cid])
            ->andWhere(['=','ss.is_deleted',LnCourseSignInSetting::DELETE_FLAG_NO])
            ->andWhere(['=','s.is_deleted',LnCourseSignIn::DELETE_FLAG_NO])
            ->groupBy("s.sign_in_setting_id");

        if($sp['course_id'])
        {
            $query->andWhere(['=','ss.course_id',$sp['course_id']]);
        }
        if($sp['sign_date'])
        {
            $query->andWhere(['=','ss.sign_date',$sp['sign_date']]);
        }
        if($sp['ssids'])
        {
            $query->andWhere(['IN', 'ss.kid', $sp['ssids']]);
        }
        if($sp['start'])
        {
            $query->andWhere(['>=', 'ss.sign_date', $sp['start']]);
        }
        if($sp['end'])
        {
            $query->andWhere(['<=', 'ss.sign_date', $sp['end']]);
        }

        if($sp['kids'])
        {
            if(!is_array($sp['kids']))
            {
                $sp['kids'][]=$sp['kids'];
            }
            $query->andWhere(['IN','ss.kid',$sp['kids']]);
        }
        
        $list=$query->all();
        foreach($list as $v)
        {
            $list_temp[$v['sign_in_setting_id']]=$v['count'];
        }
        return $list_temp;
    }

    function getSignInSettingsUseCountByCourseId($cid)
    {
        if(!$cid){return false;}
        $sp=array('course_id'=>$cid);
        return $this->getSignInSettingsUseCount($sp);
    }

    /**
     * 得到签到日期列表，根据课程id
     * @param $courseId
     * @return array
     */
    function getSignDatesByCourseId($courseId, $field = 'sign_date')
    {
        if (!$courseId) {return false;}
        $query = LnCourseSignInSetting::find(false)
            ->select($field)
            ->distinct(true)
        //    ->from(LnCourseSignInSetting::tableName())
            ->andWhere(['=', 'course_id', $courseId])
            ->orderBy('sign_date ASC');
        return $query->all();
    }

    /**
     * 得到签到标题，根据课程id和签到日期
     * @param $courseId
     * @param $signDate
     * @return array
     */
    function getSignTitlesByCourseIdAndSignDate($courseId,$signDate)
    {
        if (!$courseId) {return false;}
        if (!$signDate=intval($signDate)) {return false;}
        $query = LnCourseSignInSetting::find(false)
            ->select('kid,title')
            //    ->from(LnCourseSignInSetting::tableName())
            ->andWhere(['=', 'course_id', $courseId])
            ->andWhere(['=', 'sign_date', $signDate])
            ->orderBy('start_at ASC');
        return $query->all();
    }


    function getRecentSignInSettingId($courseId,$sign_time)
    {
        if(!$courseId){return false;}
        //$date=TTimeHelper::getDateInt($sign_time);

        $query=LnCourseSignInSetting::find(false);
        $query->select('kid')
            ->andWhere(['=','course_id',$courseId])
         //   ->andWhere(['=','sign_date',$date])
            ->andWhere(['<=','start_at',$sign_time])
            ->andWhere(['>=','end_at',$sign_time]);
            //->orderBy('start_at DESC ')
            //->offset(0)->limit(1);

       //ECHO $courseId,'<br/>',$sign_time,'<br/>',$sign_time,'<br/>';
       //var_dump($query->createCommand()->getSql());exit;

        return $query->one();
    }

    /**
     * 获取课程应签到次数
     * @author adophper<hello@adophper.com>
     * @date 2016-06-06 10:00
     * @param $courseId
     * @return bool|int|string
     */
    public function getCourseSignInSettingTimes($courseId){
        if (!$courseId) {return false;}
        $query = LnCourseSignInSetting::find(false)
            ->andWhere(['=', 'course_id', $courseId]);
        return $query->count('kid');
    }


}