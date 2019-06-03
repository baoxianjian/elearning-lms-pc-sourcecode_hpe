<?php
/**
* @name:课程签到服务
* @author:baoxianjian
* @date:18:00 2016/4/29
*/

namespace common\services\learning;


use Yii;
use yii\db\Query;
use common\models\learning\LnCourseSignIn;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseEnroll;
use common\models\framework\FwUserDisplayInfo;
use components\widgets\TPagination;
use common\helpers\TTimeHelper;


class CourseSignInService extends LnCourseSignIn{




    /**
     * 得到学员签到列表
     * @param $courseId 课程id
     * @param array $sp  查询参数,有 sign_date:日期,ssids:配置id，sign_status:签到状态(1已到，2未到), sign_order:排序,keyword:查询关键字, uid:学员id
     * @param int $outType 1纵向输出 2横向输出
     * @return array
     */
    function getStudentSignInList($courseId,$sp,$outType=1,$pageSize=12)
    {
        if(!$courseId){return false;}
        if($sp['ssids'] && !is_array($sp['ssids']))
        {
            $sp['ssids']=array($sp['ssids']);
        }

        $courseSignInSettingService = new CourseSignInSettingService();
        //print_r($students=$this->getEnrollStudentList($courseId,$sp,0));exit;


        $spu=$sp;
        if($sp['sign_status'])
        {
            if($sp['ssids'])
            {
                //部分未到(上午未到)
                if($sp['sign_status']==2)
                {
                    $sp_sign_status_temp=$sp['sign_status'];
                    //先查询出已到的学员
                    $sp['sign_status']=1;
                    //从数据库中无法直接定位未到的学员
                    unset($spu['ssids']);
                    //先查询出已到的学员
                    $students=$this->getEnrollStudentList($courseId,$sp,0);
                    //再排除已到的学员
                    $spu['not_uid']='';
                    foreach($students['data'] as $v)
                    {
                        $spu['not_uid'][]=$v['user_id'];
                    }
                    $sp['sign_status']=$sp_sign_status_temp;
                }
            }
            else
            {
                //全部已到   (或未到)
                if($sp['sign_status']==1 || $sp['sign_status']==2 ) {
                    if ($sp['sign_date']) {

                        $list = $courseSignInSettingService->getSignTitlesByCourseIdAndSignDate($courseId, $sp['sign_date']);
                        if (count($list) > 0) {
                            $sp['ssids'] = array();
                            foreach ($list as $v) {
                                $sp['ssids'][] = $v['kid'];
                            }
                            $spu['ssids'] = $sp['ssids'];
                        }
                    }
                }
                //全部未到
               if($sp['sign_status']==2)
               {
                   $sp_sign_status_temp=$sp['sign_status'];
                   //从数据库中无法直接定位未到的学员
                   unset($spu['ssids']);

                   //先查询出已到的学员
                   $sp['sign_status']=1;
                   $students=$this->getEnrollStudentList($courseId,$sp,0);
                   //再排除已到的学员
                   $spu['not_uid']=array();
                   foreach($students['data'] as $v)
                   {
                       $spu['not_uid'][]=$v['user_id'];
                   }
                   $sp['sign_status']=$sp_sign_status_temp;
               }
            }

        }
        else
        {
            unset($spu['ssids']);
        }


        //得到签到配置
        $signSettings=$courseSignInSettingService->getListByCourseId($courseId,$sp,true);

        //得到入学人员
        $students=$this->getEnrollStudentList($courseId,$spu,$pageSize);

        //得到签到记录
        $signInRecords=$this->getSignInRecords($courseId,$sp);

        //建立签到纪录索引
        $sir_idx=array();
        foreach($signInRecords as $k=>$v)
        {
            //$signInRecords2[$v['user_id']][]=$v;
            $sir_idx[$v['user_id']][$v['sign_in_setting_id']]=$k;
        }

        $student_sign_list=array();
        $pages=null;

        if($outType==1) //纵向输出
        {
            //多个日期
            foreach ($signSettings as $k => $v)
            {
                $student_temp = $students['data'];
                //日期下多个签到配置
                foreach ($v as $v2)
                {

                    //每一个签到配置都对应学员
                    foreach ($students['data'] as $k3 => $v3)
                    {

                        //当前学员 在 当前签到配置 中吗？
                        $student_temp[$k3]['sign_data'][$v2['kid']] = null;

                        //如果 当前用户和当前签到配置 在签到记录的索引之中
                        if (is_int($sir_idx[$v3['user_id']][$v2['kid']]))
                        {

                            $student_temp[$k3]['sign_data'][$v2['kid']] = $signInRecords[$sir_idx[$v3['user_id']][$v2['kid']]];
                        }
                    }
                    $student_sign_list[$k]['sign_settings'][] = $v2;
                }
                $student_sign_list[$k]['students'] = $student_temp;
                $student_sign_list[$k]['pages'] = $students['pages'];
            }
        }
        else //横向输出
        {
            //多个学员
            foreach($students['data'] as $k=>$v)
            {
                //多个日期
                foreach($signSettings as $k2=>$v2)
                {
                    //日期下多个签到配置
                    foreach($v2 as $v3)
                    {
                        //当前学员 在 当天 当前签到配置 中吗？
                        $students['data'][$k]['sign_data'][$k2][$v3['kid']]=null;

                        //如果 当前用户和当前签到配置 在签到记录的索引之中
                        if(is_int($sir_idx[$v['user_id']][$v3['kid']]))
                        {
                            $students['data'][$k]['sign_data'][$k2][$v3['kid']]=$signInRecords[$sir_idx[$v['user_id']][$v3['kid']]];
                        }
                    }
                }
            }

            $student_sign_list['students']=$students['data'];
            $student_sign_list['sign_settings']=$signSettings;
            $student_sign_list['pages']=$students['pages'];
        }

        return $student_sign_list;
    }

    /**
     * 得到某学员的签到状态
     * @param $courseId
     * @param $studentId
     * @param int $time
     * @return array|bool
     */
    function getStudentSignInStatus($courseId,$studentId,$time=0)
    {
        if(!$courseId){return false;}
        if(!$studentId){return false;}
        if(!$time){$time=time();}

        $courseSignInSettingService = new CourseSignInSettingService();

        $temp=$courseSignInSettingService->getRecentSignInSettingId($courseId,$time);
        if(!$temp) {return ['result' => '2', 'msg' => 'can not find sign_in_setting_id for this time'];}

        $sp['sign_date']=TTimeHelper::getDateInt($time);
        $sp['ssids']=$temp['kid'];
        $sp['uid']=$studentId;


        $list=$this->getStudentSignInList($courseId,$sp,2);


        if(!$list['students'])
        {
            return ['result'=>3,'msg'=>'can not find student'];
        }
        $sign_data=$list['students'][0]['sign_data'][$sp['sign_date']][$sp['ssids']];
        $sign_setting=$list['sign_settings'][$sp['sign_date']][0];

        if($sign_data)
        {
            if($sign_data['sign_flag']==self::SIGN_FLAG_SIGN_IN)
            {
                $sign_status=self::SIGN_STATUS_SIGNED_IN; //已签到
            }
            else if($sign_data['sign_flag']==self::SIGN_FLAG_LEAVE)
            {
                $sign_status=self::SIGN_STATUS_LEFT; //已请假
            }
            else
            {
                return ['result'=>4,'msg'=>'sign_flag_error'];
            }
        }
        else
        {
            $sign_status=self::SIGN_STATUS_NO_SIGN_IN; //未签到
        }

        $sign_status_text=$this->getSignInStatuses($sign_status);

        return ['result'=>1,'msg'=>'success','sign_title'=>$sign_setting['title'],'sign_status'=>$sign_status,'sign_status_text'=>$sign_status_text,'sign_data'=>$sign_data,'sign_setting'=>$sign_setting];
    }


    /**
     * 得到签到记录
     * @param $courseId
     * @param $sp
     * @return array
     */
    function getSignInRecords($courseId,$sp)
    {
        if(!$courseId){return false;}

        $query=LnCourseSignIn::find(false);
        $query->select('kid,course_id, sign_in_setting_id, user_id, sign_user_id, sign_time, sign_system, sign_type, sign_flag')
        ->andWhere(['=','course_id',$courseId])
        ->andWhere(['=','is_deleted', LnCourseSignIn::DELETE_FLAG_NO]);
        if($sp['ssids']) //需要参数检查
        {
            $query->andWhere(['IN','sign_in_setting_id',$sp['ssids']]);
        }
        if($start=intval($sp['start']))
        {
            $query->andWhere(['>=','sign_time',$start]);
        }
        if($end=intval($sp['end']))
        {
            $query->andWhere(['<=','sign_time',$end]);
        }

        return $query->all();
    }


    /**
     * 得到入学学员
     * @param $courseId 课程id
     * @param $sp 查询参数
     * @return array|bool
     */
    public function getEnrollStudentList($courseId,$sp,$pageSize=12)
    {

        if (!$courseId) {
            return false;
        }

        $query = new Query();
        $query->select('t1.real_name,t1.orgnization_name,t1.location,t1.position_name,t1.email,len.kid,len.user_id,len.enroll_time,len.enroll_type,len.enroll_method,len.approved_state')
            ->from(LnCourseEnroll::tableName() . ' as len')
            ->leftjoin(FwUserDisplayInfo::tableName() . ' as t1', 'len.user_id = t1.user_id')
            ->andWhere(['=', 'len.course_id', $courseId])
            ->andWhere(['=', 'len.enroll_type', LnCourseEnroll::ENROLL_TYPE_ALLOW])
            ->andWhere(['=', 'len.approved_state', LnCourseEnroll::APPROVED_STATE_APPROVED])
            ->andWhere(['=', 'len.is_deleted', LnCourseEnroll::DELETE_FLAG_NO])
            ->andWhere(['=', 't1.is_deleted', FwUserDisplayInfo::DELETE_FLAG_NO])
            ->andWhere(['=', 't1.status', FwUserDisplayInfo::STATUS_FLAG_NORMAL]);

        if ($sp['ssids']) {
            // var_dump($sp['ssids']);exit;
            //$query->select("s.sign_in_setting_id");
            $query->leftjoin(LnCourseSignIn::tableName() . ' as s', 'len.user_id = s.user_id');
            $query->andWhere(['IN', 's.sign_in_setting_id', $sp['ssids']])
                ->andWhere(['=', 's.is_deleted', LnCourseSignIn::DELETE_FLAG_NO]);
            $query->groupBy('len.user_id')
                ->having('COUNT(len.user_id)=' . count($sp['ssids']));

            if ($sp['sign_status'] == 1)
            {
                $query->andWhere(['=', 's.sign_flag',LnCourseSignIn::SIGN_FLAG_SIGN_IN]);
            }
        }

        if($sp['keyword']=trim($sp['keyword']))
        {
            $query->andWhere("t1.real_name like '%{$sp['keyword']}%' or t1.orgnization_name like '%{$sp['keyword']}%' or t1.position_name like '%{$sp['keyword']}%'");
        }
        if($sp['not_uid'])
        {
            $query->andWhere(['NOT IN', 'len.user_id', $sp['not_uid']]);
        }
        if($sp['uid'])
        {
            $query->andWhere(['=', 'len.user_id', $sp['uid']]);
        }
        if($sp['sign_order']==1)
        {
            $query->orderBy('position_name');
        }
        if($sp['sign_order']==2)
        {
            $query->orderBy('orgnization_name');
        }

//echo $query->createCommand()->getSql();exit;

        $pages=null;
        if($pageSize) {
            $count = $query->count();
            $pages = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
            $data = $query->offset($pages->offset)->limit($pages->limit)->all();
        }
        else
        {
            $data = $query->all();
        }
        $result = array(
            'pages' => $pages,
            'data' => $data,
        );

        return $result;
    }

    /**
     * 删除签到记录
     * @param $courseId 课程id
     * @param $studentId 学员id
     * @param $signInSettingId 签到配置id
     * @return bool|int
     */
    function deleteSignInRecords($courseId,$studentId,$signInSettingId)
    {
        if(!$courseId){return false;}
        if(!$studentId){return false;}
        if(!$signInSettingId){return false;}

       $result=LnCourseSignIn::deleteAll("course_id=:course_id AND user_id=:user_id AND sign_in_setting_id=:ssid",
       [':course_id'=>$courseId,':user_id'=>$studentId,':ssid'=>$signInSettingId]);

        return $result;
    }


    /**
     * 得到签到次数
     * @param $courseId 课程id
     * @param $studentId 学员id
     * @param $signInSettingId 签到配置id
     * @return bool|int|string
     */
    function getStudentSignInCount($courseId,$studentId,$signInSettingId)
    {
        if(!$courseId){return false;}
        if(!$studentId){return false;}
        if(!$signInSettingId){return false;}

        $query=LnCourseSignIn::find(false);
        $query->select('COUNT(*) AS count')
            ->andWhere(['=','course_id',$courseId])
            ->andWhere(['=','user_id',$studentId])   
            ->andWhere(['=','sign_in_setting_id',$signInSettingId])
            ->andWhere(['=','is_deleted',LnCourseSignIn::DELETE_FLAG_NO]);

        return $query->count();
    }

    /**
     * 得到一条学员签到记录
     * @param $courseId 课程id
     * @param $studentId 学员id
     * @param $signInSettingId 签到配置id
     * @return bool|int|string
     */
    function getStudentSignInRecordOne($courseId,$studentId,$signInSettingId)
    {
        if(!$courseId){return false;}
        if(!$studentId){return false;}
        if(!$signInSettingId){return false;}

        $query=LnCourseSignIn::find(false);
        $query->select('sign_flag')
            ->andWhere(['=','course_id',$courseId])
            ->andWhere(['=','user_id',$studentId])
            ->andWhere(['=','sign_in_setting_id',$signInSettingId])
            ->andWhere(['=','is_deleted',LnCourseSignIn::DELETE_FLAG_NO]);
        /*
        var_dump($courseId);
        var_dump($studentId);
        var_dump($signInSettingId);
echo $query->createCommand()->getSql();
        */
        return $query->one();
    }



    /**
     * 学员签到
     * @param LnCourseSignIn $signInModel
     * @return array
     */
    function studentSignIn($signInModel)
    {
        //if(!$signInModel->sign_in_setting_id) {return ['result' => '2', 'msg' => 'sign_in_setting_id is null'];}
        if(!$signInModel->course_id) {return ['result' => '3', 'msg' => Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common','course_id')])];}
        if(!$signInModel->user_id) {return ['result' => '4', 'msg' =>Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common','user_id')])];}

        $courseModel = LnCourse::findOne($signInModel->course_id);
        if (!$courseModel) { return ['result' => '5', 'msg' => Yii::t('frontend', '{value}_not_exists',['value'=>Yii::t('common','course')])];}

        /*
        $commonDictionaryService = new DictionaryService();
        $isDemo = $commonDictionaryService->getDictionaryValueByCode("system","is_demo");
        if ($isDemo === "0") {
            if ($courseModel->open_status != LnCourse::COURSE_START || $nowTime < $courseModel->open_start_time || $nowTime > $courseModel->open_end_time) {
                return ['result' => 'fail', 'msg' => '课程未开始或不在开课时间内'];
            }
        }
        */

        // $sid = Yii::$app->request->post('studentid');

        /*
        //add by baoxianjian 16:52 2016/1/13s
        if(!$sid)
        {
            return ['result' => 'fail', 'msg' => '学员id非法!'];
        }
        */

        $nowTime = time();
        $courseSignInSettingService = new CourseSignInSettingService();

        if(!$signInModel->sign_in_setting_id)
        {
            $temp=$courseSignInSettingService->getRecentSignInSettingId($signInModel->course_id,$nowTime);
            if(!$temp) {return ['result' => '2', 'msg' =>Yii::t('frontend', 'sign_in_time_not_in_range_please_wait')];}

            $signInModel->sign_in_setting_id=$temp['kid'];
        }


        //设置默认值
        if(!$signInModel->sign_flag) {$signInModel->sign_flag=LnCourseSignIn::SIGN_FLAG_SIGN_IN;} //默认签到
        if(!$signInModel->sign_time) {$signInModel->sign_time=$nowTime;} //默认当前时间
        if(!$signInModel->sign_type) {$signInModel->sign_type=LnCourseSignIn::SIGN_TYPE_SELF;} //默认自己
        if(!$signInModel->sign_user_id) {$signInModel->sign_user_id=Yii::$app->user->identity->getId();} //默认自己的id
        if(!$signInModel->sign_system) {$signInModel->sign_system= LnCourseSignIn::SIGN_SYSTEM_PC;} //默认PC端


        if($signInModel->sign_flag==$signInModel::SIGN_FLAG_SIGN_IN)
        {     
            //签到需做开课状态限制
            if ($courseModel->open_status != LnCourse::COURSE_START) {
                return ['result' => '9', 'msg' => Yii::t('frontend', 'course_is_not_in_open_status')];
            }
            //防止重复签到
            /*
            $signCount = $this->getStudentSignInCount($signInModel->course_id, $signInModel->user_id, $signInModel->sign_in_setting_id);
            if ($signCount > 0) {
                return ['result' => '6', 'msg' =>Yii::t('frontend', 'signin_is_not_repeat')];
            }
            */

            $signRec=$this->getStudentSignInRecordOne($signInModel->course_id, $signInModel->user_id, $signInModel->sign_in_setting_id);

            //var_dump($signRec);exit;

            if($signRec)
            {
                if($signRec['sign_flag']==LnCourseSignIn::SIGN_FLAG_SIGN_IN)
                {
                    return ['result' => '6', 'msg' =>Yii::t('frontend', 'signin_is_not_repeat')];
                }
                else if($signRec['sign_flag']==LnCourseSignIn::SIGN_FLAG_LEAVE)
                {
                    return ['result' => '10', 'msg' =>Yii::t('frontend', 'sign_in_no_need_because_has_left')];
                }
                else
                {
                   // return ['result' => '8', 'msg' => Yii::t('frontend', 'sign_flag_error')];
                }
            }
            $msg_name=Yii::t('frontend','sign_in');
        }
        else if($signInModel->sign_flag==$signInModel::SIGN_FLAG_LEAVE)
        {
            //请假优先级较大
            $this->deleteSignInRecords($signInModel->course_id, $signInModel->user_id, $signInModel->sign_in_setting_id);
            $msg_name=Yii::t('frontend','ask_for_leave');
        }
        else
        {
            return ['result' => '8', 'msg' => Yii::t('frontend', 'sign_flag_error')];
        }

       // $signInModel->needReturnKey=true;
        $result=$signInModel->save();

        if(!$result)
        {
            return ['result' => '7', 'msg' =>Yii::t('common', '{value}_failed',['value'=>$msg_name])];
        }

        return  ['result' => '1', 'msg' => Yii::t('common', '{value}_success',['value'=>$msg_name]),'kid'=>$signInModel->kid];

/*
        $startTime = strtotime(TTimeHelper::getCurrentDayStart());
        $endTime = strtotime(TTimeHelper::getCurrentDayEnd());

        $findSign = LnCourseSignIn::find(false)
            ->andWhere(['=', 'course_id', $id])
            ->andWhere(['=', 'user_id', $sid])
            ->andWhere(['>=', 'sign_time', $startTime])
            ->andWhere(['<=', 'sign_time', $endTime])
            ->all();


        if (!$findSign) {
            $signinModel = new LnCourseSignIn();
            $signinModel->course_id = $id;
            $signinModel->user_id = $sid;
            $signinModel->sign_time = time();
            $signinModel->sign_type = LnCourseSignIn::SIGN_TYPE_TEACHER;
            $signinModel->sign_system = LnCourseSignIn::SIGN_SYSTEM_PC;
            $signinModel->sign_user_id = $uid;

            //amend by baoxian 16:52 2016/1/13
            if(!$signinModel->save())
            {
                return ['result' => 'fail', 'msg' => '签到失败'];
            }
*/
        /*
        $courseId= Yii::$app->request->getQueryParam('cid');
        $signInSettingId= Yii::$app->request->getQueryParam('sid');
        $userId= Yii::$app->request->getQueryParam('uid');
        */

    }

    /*
    function deleteRowsByCourseId($courseId)
    {
        if (!$courseId) {return false;}
        //LnCourseSignInSetting:: deleteAll("course_id=:course_id", [':course_id'=>$courseId]);
        return LnCourseSignInSetting:: physicalDeleteAll("course_id=:course_id", [':course_id'=>$courseId]);
    }
    */

    //将十进制转换为26进制
    private function _number2ExcelColumnName($n)
    {
        $s = '';
        while ($n > 0)
        {
            $m = $n % 26;
            if ($m == 0) $m = 26;
            $s = chr($m + 64) . $s;
            $n = ($n - $m) / 26;
        }
        return strtoupper($s);
    }

    //签到输出到excel
    function signInOutput($course_id,$sp)
    {
        if(!$course_id){return false;}

        $courseService = new CourseService ();
        $courseModel = $courseService->findOne( $course_id) ;
        if(!$courseModel){exit();}

        $teacherManageService = new TeacherManageService();
        $teacherStr=$teacherManageService->getTeacherNamesByCourseId($course_id);

        $resultPHPExcel = new \PHPExcel();
        $resultPHPExcel->getActiveSheet()->mergeCells('A1:D1');
        $resultPHPExcel->getActiveSheet()->mergeCells('A2:D2');
        $resultPHPExcel->getActiveSheet()->mergeCells('A3:D3');
        $resultPHPExcel->getActiveSheet()->setCellValue('A1',Yii::t('common', 'course').'：'.$courseModel->course_name);
        $resultPHPExcel->getActiveSheet()->setCellValue('A2',Yii::t('common', 'time').'：'.TTimeHelper::FormatTime($courseModel->open_start_time).'~'.TTimeHelper::FormatTime($courseModel->open_end_time));
        $resultPHPExcel->getActiveSheet()->setCellValue('A3',Yii::t('common', 'lecturer').'：'.$teacherStr);

        $i=4;//从第4行开始
        $resultPHPExcel->getActiveSheet()->setCellValue('A'.$i, Yii::t('common', 'real_name'));
        $resultPHPExcel->getActiveSheet()->setCellValue('B'.$i, Yii::t('common', 'department'));
        $resultPHPExcel->getActiveSheet()->setCellValue('C'.$i, Yii::t('frontend', 'position'));
        $resultPHPExcel->getActiveSheet()->setCellValue('D'.$i, Yii::t('common', 'email'));

        $resultPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
        $resultPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(16);
        $resultPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
        $resultPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);

        $resultPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFont()->setBold(true);
        $resultPHPExcel->getActiveSheet()->getStyle('B'.$i)->getFont()->setBold(true);
        $resultPHPExcel->getActiveSheet()->getStyle('C'.$i)->getFont()->setBold(true);
        $resultPHPExcel->getActiveSheet()->getStyle('D'.$i)->getFont()->setBold(true);

        $resultPHPExcel->getActiveSheet()->getStyle('A'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $resultPHPExcel->getActiveSheet()->getStyle('B'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $resultPHPExcel->getActiveSheet()->getStyle('C'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $resultPHPExcel->getActiveSheet()->getStyle('D'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $courseSignInService=new CourseSignInService();
        $student_sign_list=$courseSignInService->getStudentSignInList($course_id,$sp,2,0);


        $i=4;$j=5;//从第4行5列开始
        foreach($student_sign_list['sign_settings'] as $k=>$v)
        {
            $sign_date=TTimeHelper::FormatTime($k);
            foreach($v as $v2)
            {
                $columnName=$this->_number2ExcelColumnName($j);
                $j++;

                $resultPHPExcel->getActiveSheet()->getColumnDimension($columnName)->setWidth(16);
                $resultPHPExcel->getActiveSheet()->setCellValue($columnName.$i,$sign_date.' '.$v2['title']);
                $resultPHPExcel->getActiveSheet()->getStyle($columnName.$i)->getFont()->setBold(true);
                $resultPHPExcel->getActiveSheet()->getStyle($columnName.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
        }

        $i=5;//从第5行开始
        foreach($student_sign_list['students'] as $v)
        {
            $resultPHPExcel->getActiveSheet()->setCellValue('A' . $i, $v['real_name']);
            $resultPHPExcel->getActiveSheet()->setCellValue('B' . $i, $v['orgnization_name']);
            $resultPHPExcel->getActiveSheet()->setCellValue('C' . $i, $v['position_name']);
            $resultPHPExcel->getActiveSheet()->setCellValue('D' . $i, $v['email']);

            $resultPHPExcel->getActiveSheet()->getStyle('A'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $resultPHPExcel->getActiveSheet()->getStyle('B'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $resultPHPExcel->getActiveSheet()->getStyle('C'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $resultPHPExcel->getActiveSheet()->getStyle('D'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $j=5;//从第5列开始
            foreach($v['sign_data'] as $k2=>$v2)
            {
                foreach($v2 as $k3=>$v3)
                {
                    $columnName=$this->_number2ExcelColumnName($j);
                    $j++;

                    if($v3['sign_flag']==LnCourseSignIn::SIGN_FLAG_SIGN_IN)
                    {
                        $sign_str=Yii::t('frontend', 'arrived');
                        $color=\PHPExcel_Style_Color::COLOR_BLACK;
                    }
                    else if($v3['sign_flag']==LnCourseSignIn::SIGN_FLAG_LEAVE)
                    {
                        $sign_str=Yii::t('frontend', 'sign_in_left');
                        $color=\PHPExcel_Style_Color::COLOR_RED;
                    }
                    else
                    {
                        $sign_str=Yii::t('frontend', 'absent');
                        $color=\PHPExcel_Style_Color::COLOR_RED;
                    }

                    $resultPHPExcel->getActiveSheet()->setCellValue($columnName . $i,$sign_str);
                    $resultPHPExcel->getActiveSheet()->getStyle($columnName . $i)->getFont()->getColor()->setARGB($color);
                    $resultPHPExcel->getActiveSheet()->getStyle($columnName.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                }
            }
            $i++;

        }
        return $resultPHPExcel;
    }

    //签到输出到excel并且下载
    function signInOutputAndDownload($course_id,$sp)
    {
        if(!$course_id){return false;}

        $resultPHPExcel=$this->signInOutput($course_id,$sp);

        $outputFileName = 'signrecord.xls';
        $xlsWriter = new \PHPExcel_Writer_Excel5($resultPHPExcel);

        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outputFileName.'"');
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $xlsWriter->save( "php://output" );
        exit();
    }
}