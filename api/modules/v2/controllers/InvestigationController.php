<?php
/**
 * Created by PhpStorm.
 * User: chenli
 * Date: 16/1/6
 * Time: 下午5:42
 */

namespace api\modules\v2\controllers;


use api\base\BaseController;
use common\models\message\MsTimeline;
use common\services\learning\InvestigationService;
use common\services\learning\RecordService;
use common\services\message\TimelineService;
use Yii;
use yii\web\Response;
use common\services\framework\PointRuleService;


class InvestigationController extends BaseController
{

    public $layout = 'frame';

    const LEARNING_DURATION = "30";//太快会影响性能


    public function actionPlay()
    {
        $access_token = Yii::$app->request->getQueryParam('access_token');
        $id = Yii::$app->request->getQueryParam('id');
        $investigationService=new InvestigationService();

        $model=$investigationService->getInvest($id);

        return $this->render('play', [
            'name'=>$model['title'],
            'investigation_type'=>$model['investigation_type'],
            'id'=>$id,
            'componentCode'=>'investigation',
            'duration' => self::LEARNING_DURATION,
            'system_key' => $this->systemKey,
            'access_token' =>$access_token
        ]);
    }


    public function actionGetPlayInvestigationSubmitResult()
    {
        $params=Yii::$app->request->get();
        $investigationService=new InvestigationService();

        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = $this->user->kid;
        if($investigationService->getPlaySurveySubmitResult($params,$userId)){
            return ['result' => 'yes'];
        }else{
            return ['result' => 'no'];
        }

    }

    public function actionPlayInvestigationResComplete()
    {
        $params=Yii::$app->request->get();
        
        if($params['complete_type'] == 0) {
            $params['course_complete_id'] = $params['course_complete_process_id'];
        }
        
        $courseComplete=false;
        $getCetification=false;
        $courseId=null;
        $certificationId=null;
        $investigationService=new InvestigationService();
        $investigationService->addResCompleteDoneInfo($params,$courseComplete,$getCetification,$courseId,$certificationId);
        
        //计算积分
        $pointRuleService=new PointRuleService();
        $pointResult=$pointRuleService->countCourseAndCetificationPoint($courseComplete,$getCetification,$courseId,$certificationId);
        
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ['result' => 'success'];
    }

    public function actionGetSubVoteResult()
    {
        $id = Yii::$app->request->getQueryParam('id');
        $params=Yii::$app->request->get();
        $investigationService=new InvestigationService();
        $v_result=$investigationService->getVoteSubResult($id,$params);


        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['result' => $v_result];
    }

    public function actionCoursePlayVoteResult()
    {
        $id = Yii::$app->request->getQueryParam('id');
        $params=Yii::$app->request->get();

        $investigationService=new InvestigationService();
        $v_result=$investigationService->getVoteStResult($id,$params);

        if(isset($params['dataType']) && $params['dataType'] == 'json') {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $v_result;
        }

        return $this->renderAjax('course_play_vote_result',['results'=>$v_result]);
    }



    public function actionInvestigationSubmitResult()
    {
        $params=Yii::$app->request->post();

        $user_id = $this->user->kid;
        $investigationService=new InvestigationService();
        $investigationService->surveySubmitResult($params,$user_id);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['result' => 'success'];
    }


    public function actionGetVote()
    {
        $id = Yii::$app->request->getQueryParam('id');

        $investigationService=new InvestigationService();
        $v_result=$investigationService->getVote($id);


        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['result' => $v_result];
    }


    /**
     * 获取调查数据
     * @return array
     */
    public function actionGetSurvey()
    {
        $id = Yii::$app->request->getQueryParam('id');

        $investigationService=new InvestigationService();
        $s_result=$investigationService->getSurvey($id);


        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['result' => $s_result];
    }


    public function actionCoursePlaySurveyResult()
    {
        $id = Yii::$app->request->getQueryParam('id');
        $params=Yii::$app->request->get();

        $investigationService=new InvestigationService();
        $v_result=$investigationService->getSurveyStResult($id,$params);

        if(isset($params['dataType']) && $params['dataType'] == 'json') {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $v_result;
        }

        return $this->renderAjax('course_play_survey_result',['results'=>$v_result]);
    }


    public function actionGetSubSurveyResult()
    {
        $id = Yii::$app->request->getQueryParam('id');
        $params=Yii::$app->request->get();
        $investigationService=new InvestigationService();
        $userId = $this->user->kid;
        $v_result=$investigationService->getSurveySubResult($id,$params,$userId);


        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['result' => $v_result];
    }


    public function actionInvestigationPlayer($investigation_id , $investigation_type, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW)
    {

        $access_token = Yii::$app->request->getQueryParam('access_token');
        $componentCode = "investigation";
        $attempt=1;
        if ($investigation_type == InvestigationService::INVESTIGATION_TYPE_SURVEY) {
            return $this->renderAjax('/investigation/single_course_play_survey', [
                "id" =>$investigation_id,
                'componentCode' => $componentCode,
                'attempt' => $attempt,
                "mode" => $mode,
                'system_key' => $this->systemKey,
                'access_token' => $access_token,
                'stand' => true
            ]);
        } else {
            return $this->renderAjax('/investigation/single_course_play_vote', [
                "id" => $investigation_id,
                'componentCode' => $componentCode,
                'attempt' => $attempt,
                "mode" => $mode,
                'system_key' => $this->systemKey,
                'access_token' => $access_token,
                'stand' => true
            ]);
        }


    }


    /**
     * 获取调查历史记录
     * @return array
     */
    public function actionGetSinglePlayInvestigationSubmitResult()
    {
        $params=Yii::$app->request->get();
        $investigationService=new InvestigationService();

        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = $this->user->kid;
        if($investigationService->getSinglePlaySurveySubmitResult($params,$userId)){
            return ['result' => 'yes'];
        }else{
            return ['result' => 'no'];
        }

    }


    public function actionGetSingleSubVoteResult()
    {
        $id = Yii::$app->request->getQueryParam('id');
        $params=Yii::$app->request->get();
        $userId = $this->user->kid;
        $investigationService=new InvestigationService();
        $v_result=$investigationService->getSingleVoteSubResult($id,$params,$userId);


        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['result' => $v_result];
    }

    public function actionGetSingleSubSurveyResult()
    {
        $id = Yii::$app->request->getQueryParam('id');
        $params=Yii::$app->request->get();
        $investigationService=new InvestigationService();
        $user_id = $this->user->kid;
        $v_result=$investigationService->getSingleSurveySubResult($id,$params,$user_id);


        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['result' => $v_result];
    }




    public function actionSingleCoursePlayVoteResult()
    {
        $id = Yii::$app->request->getQueryParam('id');
        $params=Yii::$app->request->get();

        $investigationService=new InvestigationService();
        $v_result=$investigationService->getSingleVoteStResult($id,$params);

        if(isset($params['dataType']) && $params['dataType'] == 'json') {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $v_result;
        }

        return $this->renderAjax('course_play_vote_result',['results'=>$v_result]);
    }

    /**
     * 查看调查结果
     * @return string
     */
    public function actionSingleCoursePlaySurveyResult()
    {
        $id = Yii::$app->request->getQueryParam('id');
        $params=Yii::$app->request->get();

        $investigationService=new InvestigationService();
        $v_result=$investigationService->getSingleSurveyStResult($id,$params);

        if(isset($params['dataType']) && $params['dataType'] == 'json') {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $v_result;
        }

        return $this->renderAjax('course_play_survey_result',['results'=>$v_result]);
    }

    public function actionSingleInvestigationSubmitResult()
    {
        $user_id = $this->user->kid;
        $params=Yii::$app->request->post();

        $investigationService=new InvestigationService();
        $investigationService->singleSurveySubmitResult($params,$user_id);

        $param=$params['param'][0];
        
//        $pointRuleService=new PointRuleService();
        $result_score = null;
        if($params['investigation_type']=='survey'){
            $result_score = $this->curUserCheckActionForPoint('Complete-Questionare',$this->systemKey,$param['investigation_id']);
        }else if($params['investigation_type']=='vote'){
            $result_score = $this->curUserCheckActionForPoint('Complete-Investigation',$this->systemKey,$param['investigation_id']);
        }

//        var_dump('result_score',$result_score);
        
        $timelineService=new TimelineService();
        $timelineService->setComplete($param['investigation_id'],MsTimeline::OBJECT_TYPE_SURVEY,MsTimeline::TIMELINE_TYPE_TODO,$user_id);

        $recordService=new RecordService();
        $recordService->addByCompletedSurvey($user_id,$param['investigation_id']);

        if ($result_score['result'] == 1){
            $score = $result_score['trans_op'].$result_score['trans_point'];
        }else{
            $score = 0;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['result' => 'success','score' => $score];
    }

}