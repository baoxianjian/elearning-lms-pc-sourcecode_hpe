<?php
/**
 * 签到控制器
 * author: baoxianjian
 * date: 2016/5/10
 * time: 14:00 
 */

namespace frontend\controllers;

use frontend\base\BaseFrontController;
use yii;
use yii\db;
use yii\web\Response;
use common\models\learning\LnCourseSignIn;
use common\models\learning\LnCourseSignInSetting;
use common\services\learning\CourseSignInService;
use common\services\learning\CourseSignInSettingService;
use common\services\interfaces\service\ToolInterface;
use common\services\learning\CourseService;
use components\widgets\TPagination;
use common\models\learning\LnCourse;
use common\helpers\TTimeHelper;
use common\services\learning\TeacherManageService;

define('HP_QR_SIGN_IN_KEY','hp_qr_sign_in_key_2597825');

class SignInController extends BaseFrontController
{
    public $layout = 'frame';


    //edit by baoxianjian 10:08 2016/4/26
    public function actionDetailSign($id){
        $courseModel = LnCourse::findOne($id);
        if(!$courseModel){
            exit();
        }

        $courseSignInSettingService = new CourseSignInSettingService();
        $signDates=$courseSignInSettingService->getSignDatesByCourseId($id);

        
        $courseService = new CourseService ();
        $studentCount= $courseService->searchCourseEnroll($id,null,true);

        //今天选中
        if($signDates)
        {
            $today = strtotime(date('Y-m-d'));
            foreach ($signDates as $v) {
                if ($v['sign_date'] == $today) {
                    $sp['sign_date'] = $today;
                    $signDateIsToday[$today]='('.Yii::t('common', 'today').')';
                    break;
                }
            }
            if (!$sp['sign_date']) {
                $sp['sign_date'] = $signDates[0]['sign_date'];
            }
            $signTitles=$courseSignInSettingService->getListByCourseId($id, $sp,false);
        }
        $signDateSelected[$sp['sign_date']]=' selected="selected"';

        $curMonth=date('m');
        $curDay=date('d');

        return $this->renderAjax('detail-sign',[
            'id' => $id,
            'courseModel'=>$courseModel,
            'signDates'=>$signDates,
            'signTitles'=>$signTitles,
            'signDateSelected'=>$signDateSelected,
            'signDateIsToday'=>$signDateIsToday,
            'curMonth'=>$curMonth,
            'curDay'=>$curDay,
            'studentCount'=>$studentCount
        ]);
    }

    /*
    //教师签到
    public function actionTeacherSign($id)
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $uid = Yii::$app->user->getId();
            $courseModel = LnCourse::findOne($id);
            if (!$courseModel) {
                return ['result' => 'fail', 'msg' => '课程不存在'];
            }
            $nowTime = time();

            $commonDictionaryService = new DictionaryService();
            $isDemo = $commonDictionaryService->getDictionaryValueByCode("system","is_demo");
            if ($isDemo === "0") {
                if ($courseModel->open_status != LnCourse::COURSE_START || $nowTime < $courseModel->open_start_time || $nowTime > $courseModel->open_end_time) {
                    return ['result' => 'fail', 'msg' => '课程未开始或不在开课时间内'];
                }
            }

            $sid = Yii::$app->request->post('studentid');
            
            //add by baoxianjian 16:52 2016/1/13s
            if(!$sid)
            {
                 return ['result' => 'fail', 'msg' => '学员id非法!'];
            }
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
            }

            return ['result' => 'success', 'msg' => '签到成功', 'time' => date('H:i')];

        }
    }
    */


    /*
    //详情 获取全部签到记录
    public function actionDetailSignRecord($id){
        $courseModel = LnCourse::findOne($id);
        if(!$courseModel){
            exit();
        }
        $param['open_start_time'] =strtotime(TTimeHelper::getCurrentDayStart($courseModel->open_start_time));
        $param['open_end_time'] = $courseModel->open_end_time ;
        $param['time'] = Yii::$app->request->getQueryParam('signtime');
        $param['signstatus'] = Yii::$app->request->getQueryParam('signstatus');

        $param['time'] = $param['time']? $param['time']: strtotime(TTimeHelper::getCurrentDayStart()) ;

        $courseService = new CourseService ();
        $result = $courseService->getSignRecordTeacher($id,$param);

        return $this->renderAjax('detailSignRecord',[
            'students'=>$result['data'],
            'pages' => $result['pages'] ,
            'id' => $id ,
            'param' => $param ,
        ]);
    }
    */

    //详情 获取全部签到记录
    /*
    public function actionDetailSignDown($id){
        $this->layout = 'none';
        $uid = Yii::$app->user->getId();
        $courseService = new CourseService ();
        $courseModel = $courseService->teacherGetOneCourse($uid, $id) ;
        if(!$courseModel){
            exit();
        }

        $courseService = new CourseService ();

        $resultPHPExcel = new \PHPExcel();
        $resultPHPExcel->getActiveSheet()->setCellValue('A1', '姓名');
        $resultPHPExcel->getActiveSheet()->setCellValue('B1', '部门');
        $resultPHPExcel->getActiveSheet()->setCellValue('C1', '时间');
        $resultPHPExcel->getActiveSheet()->setCellValue('D1', '状态');

        $i = 2;
        $start_time =strtotime(TTimeHelper::getCurrentDayStart($courseModel->open_start_time));
        $end_time = $courseModel->open_end_time ;
        $dictionaryService = new \common\services\framework\DictionaryService();

        while ($start_time<$end_time){
            $param['time'] = $start_time ;
            $result = $courseService->getSignRecordTeacher($id,$param, true);
            if($result){
                foreach($result as $item){
                    $resultPHPExcel->getActiveSheet()->setCellValue('A' . $i, $item['real_name']);
                    if($item['location']){
                        $item['location'] = $dictionaryService->getDictionaryNameByCode("location",$item['location']);
                        $resultPHPExcel->getActiveSheet()->setCellValue('B' . $i, $item['location']."/".$item['orgnization_name']);
                    }else {
                        $resultPHPExcel->getActiveSheet()->setCellValue('B' . $i, $item['orgnization_name']);
                    }

                    if($item['sign_time']){
                        $resultPHPExcel->getActiveSheet()->setCellValue('C' . $i, date('m月d日',$start_time));
                        $resultPHPExcel->getActiveSheet()->setCellValue('D' . $i, '已签到');
                    }else {
                        $resultPHPExcel->getActiveSheet()->setCellValue('C' . $i, date('m月d日',$start_time));
                        $resultPHPExcel->getActiveSheet()->setCellValue('D' . $i, '未签到');
                    }
                    $i ++;
                }
            }

            $start_time+= 24*60*60 ;
        }

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
    */


    //详情 获取全部签到记录
    //add by baoxianjian 18:17 2016/5/9
    public function actionDetailSignDown($id){
      //  $this->layout = 'none';
        $sp['sign_order']=1;
        $courseSignInService=new CourseSignInService();    
        $courseSignInService->signInOutputAndDownload($id,$sp);
    }


    //add by baoxianjin 16:12 2016/4/28
    public function actionGenerateSignSettings($cid)
    {
        if(!$cid){exit;}

        $data=Yii::$app->request->post("ssd");
        $data=json_decode($data,1);

        $courseSignInSettingService = new CourseSignInSettingService();
        $delResult=$courseSignInSettingService->deleteRowsByCourseId($cid);

        $sp['ssids']=array_keys($delResult['use_list']);
        $useSettings=$courseSignInSettingService->getListByCourseId($cid,$sp);
        
        $signSettingModel=new LnCourseSignInSetting();
        
        $allCount=count($data);
        $successCount=0;
        foreach($data as $v)
        {
            $signSettingModel->isNewRecord = true;

            $v['course_id']=$cid;
            $this->_copySignSettingValues($signSettingModel, $v);
            
            if(!array_key_exists($signSettingModel->sign_date,$useSettings))
            {
                $result=$signSettingModel->save();
                if($result)
                {
                    $successCount++;
                }
            }

            $signSettingModel->kid=null;
        }
        

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'result' => $successCount==$allCount? 'success':'failed',
            'allCount'=>$allCount,
            'successCount'=>$successCount
        ];
    }

    //add by baoxianjian 16:05 2016/5/4
    public function actionSaveSignSettings($cid)
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost)
        {
            if (!$cid) {exit;}

            $data = Yii::$app->request->post("ssd");
            $data = json_decode($data, 1);

            $delKids = Yii::$app->request->post("dids");
            $delKids = json_decode($delKids, 1);

            $successCount=0;
            foreach ($data as $v)
            {
                if ($v['kid']) {
                    $signSettingModel = LnCourseSignInSetting::findOne($v['kid']);

                    //  var_dump($signSettingMode);exit;
                }
                if (!$signSettingModel || !$v['kid']) {
                    $signSettingModel = new LnCourseSignInSetting();
                }

                $v['course_id'] = $cid;
                $this->_copySignSettingValues($signSettingModel, $v);

                $result=$signSettingModel->save();

                if($result)
                {
                    $successCount++;
                }
            }

            $courseSignInSettingService = new CourseSignInSettingService();
            //$courseSignInSettingService->deleteAllByKid($delKids);
            $courseSignInSettingService->deleteRowsByKids($delKids);

            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['result' => 'success','saveSuccessCount'=>$successCount,'saveAllCount'=>count($data),];
        }
    }
    
    
    
    public function actionDeleteSignSettings($cid)
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost)
        {
            $sign_date = Yii::$app->request->post('d');
            
            $courseSignInSettingService = new CourseSignInSettingService();
            $result=$courseSignInSettingService->deleteRowsByCourseIdAndSignDate($cid,$sign_date);

            

            Yii::$app->response->format = Response::FORMAT_JSON;


            return ['result' => $result['result']?'success':'failed','successCount'=>$result['successCount']];
        }
    }
    

    //add by baoxianjian 14:24 2016/5/3
    public function actionGetSignSettings($cid)
    {
        $courseModel = LnCourse::findOne($cid);
        if(!$courseModel){
            exit();
        }

        $courseSignInSettingService = new CourseSignInSettingService();

        //  $courseSignInSettingService->deleteRowsByCourseId($cid);

        $signSettingList = $courseSignInSettingService->getListByCourseId($cid,null,true,null,true);

        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->renderAjax('get-sign-settings', [
            'id' => $cid,
            'signSettingList' => $signSettingList,
            'courseModel'=>$courseModel
        ]);
    }

    //add by baoxianjian 14:34 2016/5/9
    public function actionGetStudentSigns($cid)
    {
        $courseModel = LnCourse::findOne($cid);
        
        if(!$courseModel){
            exit();
        }

        $courseSignInService=new CourseSignInService();

        $req=Yii::$app->request;
        $sp['sign_date']=$req->getQueryParam('sd');
        $sp['ssids']=$req->getQueryParam('ssids');
        $sp['sign_status']=$req->getQueryParam('ss');
        $sp['sign_order']=$req->getQueryParam('so');
        $sp['keyword']=$req->getQueryParam('kw');
        $show_all=$req->getQueryParam('sa');
        
        $pageSize=12;
        if($show_all)
        {
            $pageSize=0;
        }

        if(!$sp['sign_date'])
        {
            $sp['sign_date']=strtotime(date('Y-m-d'));
        }

        // $sp['start']=1;
        // $sp['end']=1851664000;
        //$sp['sign_date']=1452009600;
        // $sp['']='22C1E36E-77A5-709D-FC0D-20DDAF7F0E3E,3903B564-4671-3ECE-EA2F-CEF27E744648';
        //$sp['ssids']=array('22C1E36E-77A5-709D-FC0D-20DDAF7F0E3E','3903B564-4671-3ECE-EA2F-CEF27E744648');
        $student_sign_list=$courseSignInService->getStudentSignInList($cid,$sp,1,$pageSize);
        
        
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->renderAjax('get-student-signs', [
        'id' => $cid,
        'courseModel'=>$courseModel,
        'student_sign_list'=>$student_sign_list,
        'show_all'=>$show_all
    ]);

    }

    public function actionGetSignTitles($cid,$d)
    {
        if (!$cid) {exit;}
        if (!$cid) {exit;}
        if (!$d = intval($d)) {exit;}

        $courseSignInSettingService = new CourseSignInSettingService();
        $signTitles = $courseSignInSettingService->getSignTitlesByCourseIdAndSignDate($cid, $d);

        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['result' => 'success', 'signTitles' => $signTitles];

        /*
        return $this->renderAjax('getSignSettings',[
            'id' => $cid ,
            'signSettingList'=>$signSettingList,
        ]);
        */
    }

    public function actionStudentSignIn()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost)
        {
            $courseId = Yii::$app->request->post('cid');
            $signInSettingId = Yii::$app->request->post('ssid');
            $userId = Yii::$app->request->post('uid');

            $courseSignInService = new CourseSignInService();
            $signInModel = new LnCourseSignIn();
            $signInModel->course_id = $courseId;
            $signInModel->sign_in_setting_id = $signInSettingId;
            $signInModel->user_id = $userId;
            $signInModel->sign_system = LnCourseSignIn::SIGN_SYSTEM_PC;
            $signInModel->sign_time = time();
            $signInModel->sign_type = LnCourseSignIn::SIGN_TYPE_TEACHER;
            $signInModel->sign_user_id = Yii::$app->user->identity->getId();

            $result = $courseSignInService->studentSignIn($signInModel);

            Yii::$app->response->format = Response::FORMAT_JSON;
            
            if($result['result']!=1)
            {
                return ['result' => 'failed','msg'=>$result['msg']];
            }

            return ['result' => 'success','kid'=>$result['kid'],'msg'=>$result['msg']];
        }
    }


    public function actionStudentSignInRevoke()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $kid = Yii::$app->request->post('id');

            $signInModel = LnCourseSignIn::findOne($kid);
            if($signInModel) {
                if($signInModel->delete()){
                    return ['result' => 'success'];        
                }
            }
            return ['result' => 'failed'];

        }
    }
    
    public function actionStudentLeave()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost)
        {
            $courseId = Yii::$app->request->post('cid');
            $signInSettingId = Yii::$app->request->post('ssid');
            $userId = Yii::$app->request->post('uid');

            $courseSignInService = new CourseSignInService();
            $signInModel = new LnCourseSignIn();
            $signInModel->course_id = $courseId;
            $signInModel->sign_in_setting_id = $signInSettingId;
            $signInModel->user_id = $userId;
            $signInModel->sign_system = LnCourseSignIn::SIGN_SYSTEM_PC;
            $signInModel->sign_time = time();
            $signInModel->sign_type = LnCourseSignIn::SIGN_TYPE_TEACHER;
            $signInModel->sign_flag = LnCourseSignIn::SIGN_FLAG_LEAVE;
            $signInModel->sign_user_id = Yii::$app->user->identity->getId();
            

            $result = $courseSignInService->studentSignIn($signInModel);

            Yii::$app->response->format = Response::FORMAT_JSON;
            
            
            if($result['result']!=1)
            {
                return ['result' => 'failed','msg'=>$result['msg']];
            }

            return ['result' => 'success','kid'=>$result['kid'],'msg'=>$result['msg']];
        }
    }

    //add by baoxianjian 11:17 2016/5/10
    public function actionQrScanCode($date,$cid,$down=0)
    {
        if(!$date=intval($date)){return false;}
        $code=md5($date.$cid.HP_QR_SIGN_IN_KEY);

        $url = Yii::$app->urlManager->getHostInfo();
        $url = $url . Yii::$app->urlManager->createUrl(['/sign-in/qr-sign-in','date'=>$date,'cid'=>$cid, 'code'=>$code]);

 


        $fileName='qr_code_'.TTimeHelper::FormatTime($date);

        if($down)
        {
            //header("Content-Type:text/html;charset=utf-8");
            header("Content-type: image/jpeg");
            //header('Content-type:image/png');
            header("Content-Disposition: attachment;filename={$fileName}.jpg ");
        }
        ToolInterface::genQRCode($url);
    }

    public function actionQrSignIn($date,$cid,$code)
    {
        if(!$date=intval($date)){return false;}
        $code2=md5($date.$cid.HP_QR_SIGN_IN_KEY);

        if($code != $code2)
        {
            exit('sign in failed');
        }

        $courseId = $cid;
        //$signInSettingId = Yii::$app->request->post('ssid');
        $userId = Yii::$app->user->identity->getId();

        $courseSignInService = new CourseSignInService();
        $signInModel = new LnCourseSignIn();
        $signInModel->course_id = $courseId;
        //$signInModel->sign_in_setting_id = $signInSettingId;
        $signInModel->user_id = $userId;
        $signInModel->sign_system = LnCourseSignIn::SIGN_SYSTEM_PC;
        $signInModel->sign_time = time();
        $signInModel->sign_type = LnCourseSignIn::SIGN_TYPE_SELF;
        $signInModel->sign_user_id = Yii::$app->user->identity->getId();

        $result = $courseSignInService->studentSignIn($signInModel);

       // Yii::$app->response->format = Response::FORMAT_JSON;


        return $this->render('qr-sign-in',['signInResult'=>$result]);
       /*
        if($result['result']!=1)
        {           
            return ['result' => 'failed','code'=>$result['result'],'msg'=>$result['msg']];
        }

        return ['result' => 'success','kid'=>$result['kid']];
       */
    }
    
    
    
    //add by baoxianjian 14:55 2016/5/4
    private function _copySignSettingValues($model,$v)
    {
        $model->course_id=$v['course_id'];
        $model->sign_date=strtotime($v['sign_date']);
        $model->title=$v['title'];
        $model->start_at_str=$v['start_at_str'];
        $model->start_at=strtotime($v['sign_date'].' '.$v['start_at_str']);
        $model->end_at_str=$v['end_at_str'];
        $model->end_at=strtotime($v['sign_date'].' '.$v['end_at_str']);
        $model->qr_code='NULL';//$v['ddd'];
        return $model;
    }

}