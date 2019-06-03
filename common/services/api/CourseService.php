<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/4/26
 * Time: 10:40
 */

namespace common\services\api;

use common\traits\ResponseTrait;
use common\models\learning\LnCoursewareScorm;
use common\models\learning\LnScormScoes;
use common\helpers\TMessageHelper;
use common\models\learning\LnComponent;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseCertification;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnCourseEnroll;
use common\models\learning\LnCourseReg;
use common\models\learning\LnCourseTeacher;
use common\models\learning\LnModRes;
use common\models\learning\LnResComplete;
use common\models\learning\LnResourceDomain;
use common\models\social\SoCollect;
use common\services\learning\CourseCategoryService;
use common\services\learning\RecordService;
use common\services\scorm\ScormScoesTrackService;
use common\services\framework\UserDomainService;
use common\services\learning\CourseCompleteService;
use common\services\learning\CourseService as LearningCourseService;
use common\services\social\QuestionService;
use common\services\learning\ResourceCompleteService;
use common\services\learning\ResourceService;
use common\services\scorm\ScormService;
use common\services\social\ShareService;
use common\services\message\TimelineService;
use common\services\framework\UserService;
use common\services\framework\DictionaryService;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\helpers\Url;
use common\models\framework\FwUser;
use common\services\framework\PointRuleService;
use common\services\learning\ResourceDomainService;
use common\traits\HelperTrait;

class CourseService extends FwUser{
    use ResponseTrait,HelperTrait;

    const PLAY_MODE_NORMAL = 'normal';
    const PLAY_MODE_PREVIEW = 'preview';
    const LEARNING_DURATION = "30";

    public $systemKey;
    public function __construct($system_key,array $config = [])
    {
        $this->systemKey = $system_key;
        parent::__construct($config);
    }

    public function decryptBodyParam() {
        $errorCode = $errorMessage = null;
        $rawBody = Yii::$app->request->getRawBody();
        $rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);
        if(!empty($errorCode)) {
            return false;
        }
        $bodyParams = json_decode($rawDecryptBody, true);
        return $bodyParams;
    }
    /**
     * 重学课程
     * @param $user_id
     * @return array
     */
    public function setRetakeCourse($user_id) {
        $bodyParams = $this->decryptBodyParam();
        if($bodyParams === false) {
            return $this->exception(['number' => '004','param' => 'post','name' => 'RestartCourse']);
        }
        if(!isset($bodyParams['course_id'])) {
            return $this->exception(['number' => '001','param' => 'course_id','name' => 'RestartCourse']);
        }
        $courseId = $bodyParams['course_id'];
        $courseService = new LearningCourseService();
        $courseCompleteService = new CourseCompleteService();
        $courseRegId = $courseService->getUserRegInfo($user_id, $courseId)->kid;
        $courseCompleteService->resetCourseCompleteInfo($courseRegId, $courseId);

        // 清空session中scorm缓存
        $scormService = new ScormScoesTrackService();
        $scormService->cleanSessionList();

        return TMessageHelper::resultBuild($this->systemKey,"OK", "RestartCourse", "", ["result"=>"success"]);
    }

    /**
     * 放弃重学课程
     * @param $user_id
     * @return array
     */
    public function quitRetakeCourse($user_id) {
        $bodyParams = $this->decryptBodyParam();
        if($bodyParams === false) {
            return $this->exception(['number' => '004','param' => 'post','name' => 'GiveupRestartCourse']);
        }
        if(!isset($bodyParams['course_id'])) {
            return $this->exception(['number' => '001','param' => 'course_id','name' => 'GiveupRestartCourse']);
        }
        $courseId = intval($bodyParams['course_id']);
        $courseService = new LearningCourseService();
        $courseCompleteService = new CourseCompleteService();
        $courseRegId = $courseService->getUserRegInfo($user_id, $courseId)->kid;
        $courseCompleteService->giveupResetCourseCompleteInfo($courseRegId);

        return TMessageHelper::resultBuild($this->systemKey,"OK", "GiveupRestartCourse", "", ["result"=>"success"]);
    }


    /**
     * 对于非scorm/aicc课件的完成记录
     * @return array
     */
    public function markComplete($courseRegId,$modResId) {
        $resourceCompleteService = new ResourceCompleteService();
        $courseCompleteService = new CourseCompleteService();

        $courseCompleteProcessModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_PROCESS);
        $courseCompleteFinalModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);
        $courseCompleteProcessId = $courseCompleteProcessModel->kid;
        $courseCompleteFinalId = $courseCompleteFinalModel->kid;

        $modResModel = LnModRes::findOne($modResId);
        $componentId = $modResModel->component_id;

        $componentCode = LnComponent::findOne($componentId)->component_code;
        if($resourceCompleteService->isResComplete($courseCompleteFinalId, $modResId)) {
            return $this->response(['code' => 'YES','name' => 'ResCompleteDone','message' => '已记录','data' => ["result"=>"success"] ]);
        }

        $processModel = $resourceCompleteService->getLastResCompleteNonDoneInfo(
            $courseRegId,
            $modResId,
            LnResComplete::COMPLETE_TYPE_PROCESS,
            $courseCompleteProcessId
        );

        if (empty($processModel)) {
            //创建新纪录
            $resCompleteProcessId = $resourceCompleteService->addResCompleteDoingInfo(
                $courseCompleteProcessId,
                $courseRegId,
                $modResId,
                LnResComplete::COMPLETE_TYPE_PROCESS,
                $this->systemKey
            );
        } else {
            $resCompleteProcessId = $processModel->kid;
        }

        $finalModel = $resourceCompleteService->getLastResCompleteNonDoneInfo(
            $courseRegId,
            $modResId,
            LnResComplete::COMPLETE_TYPE_FINAL,
            $courseCompleteFinalId
        );
        if (empty($finalModel)) {
            $resCompleteFinalId = $resourceCompleteService->addResCompleteDoingInfo(
                $courseCompleteFinalId,
                $courseRegId,
                $modResId,
                LnResComplete::COMPLETE_TYPE_FINAL,
                $this->systemKey
            );//创建最终记录（进行中）
        } else {
            $resCompleteFinalId = $processModel->kid;
        }

        $scormService = new ScormService();
        //对于非scorm/aicc的资源课件，打开页面自动完成
        if (!$scormService->isScormComponent($componentCode) && $modResModel->res_type == LnModRes::RES_TYPE_COURSEWARE) {
            $resourceCompleteService->addResCompleteDoneInfo(
                $courseCompleteProcessId,
                $courseRegId,
                $modResId,
                LnResComplete::COMPLETE_TYPE_PROCESS,
                null,
                null,
                false,
                $this->systemKey
            );//创建过程记录（完成）
            $resourceCompleteService->addResCompleteDoneInfo(
                $courseCompleteFinalId,
                $courseRegId,
                $modResId,
                LnResComplete::COMPLETE_TYPE_FINAL,
                null,
                null,
                false,
                $this->systemKey
            );//创建最终记录（完成）
        }

        return $this->response(['code' => 'YES','name' => 'ResCompleteDone','message' => '','data' => ["result"=>"success"] ]);
    }

    public function exists($course_id) {
        $course = CourseService::findOne($course_id);
        return !empty($course);
    }

    /**
     * 权限检查
     * @param $user_id
     * @param $course_id
     * @return bool
     */
    public function permissionCheck($user_id,$course_id) {
        $userDomainService = new UserDomainService();
        $domain = $userDomainService->getSearchListByUserId($user_id);
        if(empty($domain)) return false;

        $domain_id = ArrayHelper::map($domain, 'kid', 'kid');
        $domain_id = array_keys($domain_id);

        $resourceModel = new LnResourceDomain();
        $resourceCount = $resourceModel->find(false)
            ->andFilterWhere(['in','domain_id',$domain_id])
            ->andFilterWhere(['=','status',LnResourceDomain::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=','resource_type',LnResourceDomain::RESOURCE_TYPE_COURSE])
            ->andFilterWhere(['=','resource_id',$course_id])
            ->count();
        return $resourceCount > 0;
    }

    public function hasFavorite($user_id,$courseId) {
        $ret = SoCollect::findOne(['object_id' => $courseId, 'user_id' => $user_id, 'type' => '2', 'status' => SoCollect::STATUS_FLAG_NORMAL], false);
        return !empty($ret);
    }

    public function increaseIntegral($user_id,$company_id,$course_id,$action_code) {
        $pointRuleService = new PointRuleService();
        return $pointRuleService->checkActionForPoint($company_id,$user_id,$action_code,$this->systemKey,$course_id);
    }

    /**
     * 课程评分
     * @param $user_id
     * @param $course_id
     * @param $rate
     * @param $company_id
     * @return array
     */
    public function rate($user_id,$course_id,$rate,$company_id) {
        $service = new LearningCourseService();
        
        if ($service->courseRating($user_id, $course_id, $rate)) {
            $avg = $service->getCourseMarkByID($course_id);
            $count = $service->getCourseMarkCountByID($course_id);
            $jsonResult["result"] = "success";
            $jsonResult["avg"] = number_format($avg, 1);
            $jsonResult["count"] = $count;

            $this->increaseIntegral($user_id,$company_id,$course_id,'Mark-Course');
            return $this->response(['code' => 'OK','data' => $jsonResult]);
        }else{
            return ['result' => 'other', 'message' => '评分失败','code'=>'NO'];

        }
    }

    /**
     * 注册
     * @param $user_id
     * @param $course_id
     * @param $company_id
     * @return array
     */
    public function register($user_id,$course_id,$company_id) {
        $courseService = new LearningCourseService();
        $result_ = $courseService->regCourse($user_id, $course_id, LnCourseReg::REG_TYPE_SELF);
        if ($result_ !== true){
            $result = TMessageHelper::resultBuild($this->systemKey,$code="NO", null, null, ["result"=>"failed"]);
            return $result;
        }

        /*增加注册量*/
        LnCourse::addFieldNumber($course_id, 'register_number');
        $timelineService = new TimelineService();
        $timelineService->regCourseTimeline($user_id, $course_id);

        $record = new RecordService();
        $record->addByRegCourse($user_id, $course_id);
        $this->increaseIntegral($user_id,$company_id,$course_id,'Register-Online-Course');
        return $this->response(['code' => 'OK','data' => ["result"=>"success"]]);
    }

    /**
     * 课程报名
     * @param $user_id
     * @param $course_id
     * @param $company_id
     * @return array
     */
    public function enroll($user_id,$course_id,$company_id) {
        $course = LearningCourseService::findOne($course_id);
        $now = time();
        $reg_type = LnCourseEnroll::ENROLL_TYPE_ALTERNATE;
        
        if ($course->enroll_start_time != null && $course->enroll_start_time > $now){
            return $this->response(['code' => 'NO','message' => '未到报名时间','data' => ["result"=>"failed"]]);
        }
        if ($course->enroll_end_time != null && $course->enroll_end_time <= $now){
            return $this->response(['code' => 'NO','message' => '报名时间已经结束','data' => ["result"=>"failed"]]);
        }
        $courseService = new LearningCourseService();
        $count = $courseService->getEnrollNumber($course_id,[LnCourseEnroll::ENROLL_TYPE_REG,LnCourseEnroll::ENROLL_TYPE_ALLOW]);

        if ($course->limit_number > $count){
            $reg_type = LnCourseEnroll::ENROLL_TYPE_REG;
        } else {
            if ($course->is_allow_over != LnCourse::IS_ALLOW_OVER_YES) {
                return $this->response(['code' => 'NO','message' => '报名已经结束','data' => ["result"=>"failed"]]);
            }
            $alternate_count = $courseService->getEnrollNumber($course_id,LnCourseEnroll::ENROLL_TYPE_ALTERNATE);
            if ( !($course->allow_over_number != null && $course->allow_over_number > $alternate_count) ) {
                return $this->response(['code' => 'NO','message' => '报名已经结束','data' => ["result"=>"failed"]]);
            } else {
                $reg_type = LnCourseEnroll::ENROLL_TYPE_ALTERNATE;
            }
        }

        $enroll_info = array(
            'course_id' => $course_id,
            'user_id' => $user_id,
            'enroll_type' => $reg_type,
            'enroll_user_id' => $user_id,
            'enroll_method' => LnCourseEnroll::ENROLL_METHOD_SELF,
        );
        $courseService->saveEnrollInfo($enroll_info);
        LnCourse::addFieldNumber($course_id, 'register_number');/*增加报名量*/

        $this->increaseIntegral($user_id,$company_id,$course_id,'Register-Face-Course');

        return $this->response(['code' => 'OK','message' => '报名成功','data' =>["result"=>"success"] ]);
    }

    /**
     * @param $user_id
     * @param $course_id
     * @param $isReg
     * @param $modResId
     * @param $isCourseComplete
     * @param $isCourseRetake
     * @param $isCourseDoing
     * @param $currentAttempt
     * @param $uid
     * @return array
     */
    public function learnStatus($user_id,$course_id, $modResId)
    {
        $courseModel = LnCourse::findOne($course_id);
        $now = time();
        $status = [];
        $service = new LearningCourseService();
        $courseRegId = null;
        $isReg = $service->isUserRegCourse($user_id, $course_id, $courseRegId);
        $isCourseComplete = false;
        $isCourseRetake = false;
        $currentAttempt = 0;
        $isCourseDoing = false;
        if($isReg) {
            $courseCompleteService = new CourseCompleteService();
            $courseCompleteService->initCourseCompleteInfo($courseRegId, $course_id, $user_id);
            $courseCompleteFinalModel = $courseCompleteService->getLastCourseCompleteInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);
            $courseCompleteFinalId = $courseCompleteFinalModel->kid;
            $currentAttempt = $courseCompleteFinalModel->attempt_number;
            $courseCompleteService->checkCourseStatus($courseCompleteFinalModel, $isCourseDoing, $isCourseComplete, $isCourseRetake);
        }

        if (!empty($startTime) && $startTime > $now) {
            return [
                'code' => 'NOT_PUTAWAY',
                'label' => '还未上架'
            ];
        }
        if (!empty($endTime) && $endTime < $now) {
            return [
                'code' => 'TAKEN_OFF',
                'label' => '已下架'
            ];
        }
        $regInfo = $service->getUserRegInfo($user_id, $courseModel->kid);
        if ($courseModel->course_type == LnCourse::COURSE_TYPE_ONLINE) {
            if (!$isReg) {
                return [
                    'code' => 'REGISTER_ONLINE',
                    'label' => '注册'
                ];
            }
            if ($isCourseComplete) {
                if ($courseModel->max_attempt == 0 || intval($currentAttempt) < $courseModel->max_attempt) {
                    return [
                        'code' => 'RETAKE',
                        'label' => '重学'
                    ];
                } else {
                    return [
                        'code' => 'END_UP',
                        'label' => '已结束'
                    ];
                }
            }
            if ($courseModel->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_APPLING) {
                return [
                    'code' => 'APPROVING',
                    'label' => '审批中'
                ];
            }
            if ($courseModel->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_REJECTED) {
                return [
                    'code' => 'APPROVE_NOT_PASSED',
                    'label' => '审批未通过'
                ];
            }
            if ($courseModel->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_CANCELED) {
                return [
                    'code' => 'CANCELED',
                    'label' => '作废'
                ];
            }
            if ($isCourseRetake) {
                $status[] = [
                    'code' => 'QUIT_AND_LEARN',
                    'label' => '放弃'
                ];
            }
            if (empty($modResId)) {
                $status[] = [
                    'code' => 'GETTING_STARTED',
                    'label' => '开始'
                ];
                return $status;
            }
            $status[] = $isCourseDoing ? [
                'code' => 'CONTINUE_LEARN',
                'label' => '继续'
            ] : [
                'code' => 'GETTING_STARTED',
                'label' => '开始'
            ];
            return $status;
        }
        $enrollInfo = $service->getUserEnrollInfo($user_id, $courseModel->kid);
        if(empty($enrollInfo)) {
            if($courseModel->enroll_start_time != null && $courseModel->enroll_start_time > $now) {
                return [
                    'code' => 'NOT_STARTED',
                    'label' => '未开始'
                ];
            }
            if($courseModel->enroll_end_time != null && $courseModel->enroll_end_time < $now) {
                return [
                    'code' => 'REGISTRATION_ENDS',
                    'label' => '已结束'
                ];
            }
            if($courseModel->open_status == LnCourse::COURSE_END) {
                return [
                    'code' => 'END_UP',
                    'label' => '已结束'
                ];
            }
            return [
                'code' => 'REGISTER',
                'label' => '报名'
            ];
        }

        if($enrollInfo->enroll_type == LnCourseEnroll::ENROLL_TYPE_DISALLOW) {
            return [
                'code' => 'FAILED_TO_ENROLL',
                'label' => '报名失败'
            ];
        }
        
        if($enrollInfo->enroll_type == LnCourseEnroll::ENROLL_TYPE_REG || $enrollInfo->enroll_type == LnCourseEnroll::ENROLL_TYPE_ALTERNATE) {
            if($courseModel->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_APPLING) {
                return [
                    'code' => 'APPROVING',
                    'label' => '审批中'
                ];
            }
            if($courseModel->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_REJECTED) {
                return [
                    'code' => 'APPROVE_NOT_PASSED',
                    'label' => '审批未通过'
                ];
            }
            if($courseModel->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_CANCELED) {
                return [
                    'code' => 'CANCELED',
                    'label' => '作废'
                ];
            }
            return [
                'code' => 'ENROLL_APPROVING',
                'label' => '报名审核中'
            ];
        }
        if($courseModel->open_status == LnCourse::COURSE_NOT_START) {
            return [
                'code' => 'ENROLL_SUCCEED',
                'label' => '报名成功'
            ];
        }
        if($courseModel->open_status == LnCourse::COURSE_END) {
            return [
                'code' => 'END_UP',
                'label' => '已结束'
            ];
        }
        if($courseModel->open_end_time != null && $courseModel->open_end_time <= $now) {
            return [
                'code' => 'END_UP',
                'label' => '已结束'
            ];
        }
        if(!$isReg) {
            return [
                'code' => 'REGISTER',
                'label' => '报名'
            ];
        }
        if($isCourseComplete) {
            return [
                'code' => 'COMPLETED',
                'label' => '已完成'
            ];
        }
        if(empty($modResId)) {
            return [
                'code' => 'GETTING_STARTED',
                'label' => '开始'
            ];
        }

        return $isCourseDoing ? [
            'code' => 'CONTINUE_LEARN',
            'label' => '继续'
        ] : [
            'code' => 'GETTING_STARTED',
            'label' => '开始'
        ];
    }
    
    //deprecated
    //see common\services\learning\CourseService@genCatalogBottomMenu
    public function catalogBottomMenu($courseCompleteFinalId, $courseId, $isReg, $isCourseComplete, $isOnlineCourse, $isRandom, $openStatus, $courseMods = false, &$studyModResId) {

       $user_id =  Yii::$app->user->getId();
        $resourceService = new ResourceService();
        $scoreResult = $resourceService->getCourseResScoreDetail($courseId, $user_id);

        $map = [];//mod-red-id => score
        array_walk($scoreResult['data'],function($val) use(&$map){
            $map[$val['modResId']] = $val['score'];
        });

        $mode = self::PLAY_MODE_NORMAL;
        $catalogMenu = [];
        $resourceCompleteService = new ResourceCompleteService();
        if ($courseMods === false) {
            $resourceService = new ResourceService();
            $courseMods = $resourceService->getCourseMods($courseId);
        }

        if ($mode == self::PLAY_MODE_PREVIEW) {
            $isCourseComplete = false;
            $courseCompleteId = null;
        }

        $canRun = true;
        if (!$isOnlineCourse && $openStatus != LnCourse::COURSE_START) {
            $canRun = false;
        }
        $firstAvailableModResId = null;
        foreach ($courseMods as $mod) {
            $tmp = [];
            $tmp['mod_name'] = $mod['mod_name'];

            if ($mod['time'] != 0) {
                $tmp['time'] = "学时：" . $mod['time'] . "分钟";
            }

            if (!empty($mod['mod_desc'])) {
                $tmp['mod_desc'] = "模块描述：" . $mod['mod_desc'];
            }


            $tmp['items'] = [];
            if (!empty($mod['courseitems'])) {
                foreach ($mod['courseitems'] as $num => $resource) {
                    $itemId = $resource['itemId'];
                    $modResId = $resource['modResId'];
                    $componentId = $resource['componentId'];
                    $isCourseware = $resource['isCourseware'];
                    $modRes = $resource['modRes'];
                    $itemName = $resource['itemName'];
                    $publishStatus = $modRes->publish_status;
                    $item = $resource['item'];
                    $displayItem = true;
                    $learned = false;
                    $learning = false;



                    if (!$isOnlineCourse) {
                        /*预览模式也可见*/
                        if ($publishStatus == LnModRes::NO && $mode != self::PLAY_MODE_PREVIEW) {
                            $displayItem = false;
                        }
                    }

                    if ($displayItem) {

                        if (empty($firstAvailableModResId)) {
                            $firstAvailableModResId = $modResId;
                        }

                        if ($mode == self::PLAY_MODE_PREVIEW) {
                            $catalogMenu .= "<li class=''>";
                            if (empty($studyModResId)) {
                                $studyModResId = $modResId;
                            }
                        } else {
                            if ($isReg && !$isCourseComplete) {
                                $resourceCompleteService->checkResourceStatus($courseCompleteFinalId, $modResId, $learning, $learned);

                                if (!$learned && empty($studyModResId)) {
                                    $studyModResId = $modResId;
                                }
                            } else if ($isReg && $isCourseComplete) {
                                $learned = true;
                            } else {
                                $learned = false;
                            }
                        }

                        $componentModel = LnComponent::findOne($componentId);
                        $componentIcon = $componentModel->icon;
                        $componentTitle = $componentModel->title;
                        $componentCode = $componentModel->component_code;

                        $tmp['items'][] = [
                            'modResId' => $modResId,
                            'itemName' => $itemName,
                            'componentTitle' => $componentTitle,
                            'componentIcon' => $componentIcon,
                            'learned' => $learned,
                            'learning' => $learning,
                            'canRun' => $canRun,
                            'componentCode' => $componentCode,
                            'isReg' => $isReg,
                            'isCourseComplete' => $isCourseComplete,
                            'itemId' => $itemId,
                            'isCourseware' => $isCourseware,
                            'item' => $item,
                            'score' => isset($map[$modResId]) ? $map[$modResId] : '--'
                        ];
                    }

                    if ($canRun && !$isRandom && !$learned) {
                        $canRun = false;
                    }
                }
            }

            $catalogMenu[] = $tmp;
        }

        //如果循环发现没有合适的modResId，此时已完成的也可再学一次
        if (empty($studyModResId)) {
            $studyModResId = $firstAvailableModResId;
        }
        return $catalogMenu;
    }
}