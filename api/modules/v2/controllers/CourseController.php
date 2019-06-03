<?php
/**
 * Created by PhpStorm.
 * User: chenli
 * Date: 8/25/15
 * Time: 1:26 PM
 */

namespace api\modules\v2\controllers;


use api\base\BaseOpenApiController;

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
use common\services\learning\CourseService;
use common\services\social\QuestionService;
use common\services\learning\ResourceCompleteService;
use common\services\learning\ResourceService;
use common\services\scorm\ScormService;
use common\services\social\ShareService;
use common\services\message\TimelineService;
use common\services\framework\UserService;
use common\services\framework\DictionaryService;
use yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\helpers\Url;
use common\models\framework\FwUser;
use common\models\learning\LnTeacher;

use common\services\api\CourseService as ApiCourseService;

class CourseController extends BaseOpenApiController
{
    public $modelClass = 'api\services\UserService';
    const PLAY_MODE_NORMAL = 'normal';
    const PLAY_MODE_PREVIEW = 'preview';
    const LEARNING_DURATION = "30";


    /**
     * 重学课程
     * @return array
     */
    public function actionRestartCourse()
    {
        $isGet = Yii::$app->request->isGet;
        $courseService = new ApiCourseService($this->systemKey);
        if($isGet) {
            return $courseService->exception(['number' => '003','name' => 'RestartCourse']);
        }

        return $courseService->setRetakeCourse($this->user->kid);
    }


    /**
     * 放弃重学课程
     * @return array
     */
    public function actionGiveupRestartCourse()
    {
        $isGet = Yii::$app->request->isGet;
        $courseService = new ApiCourseService($this->systemKey);
        if($isGet) {
            return $courseService->exception(['number' => '003','name' => 'RestartCourse']);
        }
        return $courseService->quitRetakeCourse($this->user->kid);
    }

    /**
     * 对于非scorm/aicc课件的完成记录
     * @return array
     */
    public function actionResCompleteDone(){
        $code = null;
        $name = "actionResCompleteDone";
        $message = null;
        $isGet = Yii::$app->request->isGet;
        $courseService = new ApiCourseService($this->systemKey);
        if($isGet) {
            return $courseService->exception(['number' => '003','name' => $name]);
        }

        $bodyParams = $courseService->decryptBodyParam();
        if($bodyParams === false) {
            return $courseService->exception(['number' => '004','param' => 'post','name' => $name]);
        }

        $courseRegId = isset($bodyParams['course_reg_id']) ? $bodyParams['course_reg_id'] : null;
        $modResId = isset($bodyParams['mod_res_id']) ? $bodyParams['mod_res_id'] : null;
        if(empty($courseRegId)) {
            return $courseService->exception(['code' => null,'number' => '001','name' => $name,'param' => 'course_reg_id']);
        }
        if(empty($modResId)) {
            return $courseService->exception(['code' => null,'number' => '001','name' => $name,'param' => 'mod_res_id']);
        }

        return $courseService->markComplete($courseRegId,$modResId);
    }


    /**
     * 获取课程详情
     * @return array
     */
    public function actionGetDetail() {
        $userId = Yii::$app->user->getId();
        $isManager = Yii::$app->user->identity->manager_flag == FwUser::MANAGER_FLAG_YES ? true : false;
        $service = new CourseService();
        
        $isGet = Yii::$app->request->isGet;
        $apiCourseService = new ApiCourseService($this->systemKey);
        $code = null;
        $name = "course_detail";
        $message = null;
        if($isGet) {
            return $apiCourseService->exception(['number' => '003','name' => $name]);
        }
        $bodyParams = $apiCourseService->decryptBodyParam();
        if($bodyParams === false) {
            return $apiCourseService->exception(['number' => '004','param' => 'post','name' => $name]);
        }
        $courseId = $bodyParams['course_id'];
        if(empty($courseId)) {
            return $apiCourseService->exception(['code' => null,'number' => '001','name' => $name,'param' => 'course_id']);
        }

        $detail = $service->detail(
            $userId, 
            $courseId, 
            $isManager, 
            isset($bodyParams['require_menu']), 
            true,
            CourseService::PLAY_MODE_NORMAL,
            isset($bodyParams['require_score']),
            true
        );

        $_tmp = [];
        $teacher_type = [
            LnTeacher::TEACHER_TYPE_ASSISTANT => 'teacher_type_assistant',
            LnTeacher::TEACHER_TYPE_INTERNAL => 'teacher_type_internal',
            LnTeacher::TEACHER_TYPE_EXTERNAL => 'teacher_type_external'
        ];
        foreach($detail['data']['teacher'] as $t) {
            $_t = is_callable([$t,'toArray']) ? $t->toArray() : $t;
            $_t['teacher_type'] = Yii::t('common',$teacher_type[$t['teacher_type']]);
            $_tmp[] = $_t;
        }
        $detail['data']['teacher'] = $_tmp;
        unset($_tmp);
        $enrollTemp = $detail['data']['enrollInfo'];
        $detail['data']['enrollInfo'] = $enrollTemp->attributes;
        if(!is_array($detail['data']['learnStatus'][0])) {
            $detail['data']['learnStatus'] = [$detail['data']['learnStatus']];
        }
        return $apiCourseService->response($detail);
    }

    /**
     * 获取课程课件列表
     * @return array
     */
    public function actionGetCatalogBottomMenu(){
        $isGet = Yii::$app->request->isGet;
        $apiCourseService = new ApiCourseService($this->systemKey);
        $code = null;
        $name = "course_detail";
        $message = null;
        if($isGet) {
            return $apiCourseService->exception(['number' => '000','name' => $name]);
        }
        $bodyParams = $apiCourseService->decryptBodyParam();
        if($bodyParams === false) {
            return $apiCourseService->exception(['number' => '010','param' => 'post','name' => $name]);
        }
        $validator = function($key,&$param) {
            if(!isset($param[$key])) return false;
            if($param[$key] == "") return false;
            return true;
        };
        $rules = [
            'courseCompleteId' =>   ['code' => '001','validator' => $validator],
            'courseId' =>           ['code' => '002','validator' => $validator],
            'isReg' =>              ['code' => '003','validator' => $validator],
            'isCourseComplete' =>   ['code' => '004','validator' => $validator],
            'isOnlineCourse' =>     ['code' => '005','validator' => $validator],
            'isRandom' =>           ['code' => '006','validator' => $validator],
            'openStatus' =>         ['code' => '007','validator' => $validator]
        ];
        foreach($rules as $field => $item) {
            $func = $item['validator'];
            if(!$func($field,$bodyParams)) {
                return $apiCourseService->exception(['code' => null,'number' => $item['code'],'param' => $field]);
                break;
            }
        }
        $courseCompleteId = $courseId = $isReg = $isCourseComplete = $isOnlineCourse = $isRandom = $openStatus = null;
        extract($bodyParams,EXTR_OVERWRITE);

        $mode = ApiCourseService::PLAY_MODE_NORMAL;
        $resourceService = new ResourceService();
        $resourceCompleteService = new ResourceCompleteService();
        $courseMods = $resourceService->getCourseMods($courseId);

        $canRun = true;

        if (!$isOnlineCourse && $openStatus != LnCourse::COURSE_START) //如果是面试课程然后未到开课时间是不能学习的
        {
            $canRun = false;
        }


        $catalogArray = array();
        $j = 0;
        foreach($courseMods as $mod){
            $catalogArray[$j]["time"] = $mod["time"];
            $catalogArray[$j]["name"] = $mod["mod_name"];
            $catalogArray[$j]["mod_desc"] = $mod["mod_desc"];

            $chapterArray = array();
            $i = 0;
            foreach($mod["courseitems"] as $num => $resource){
                $chapterItem["itemId"] = $courseCompleteId["itemId"];
                $componentModel = LnComponent::findOne($resource['componentId']);
                $chapterItem["componentIcon"] = $componentModel->icon;
                $chapterItem["componentTitle"] = $componentModel->title;
                $chapterItem["componentCode"] = $componentModel->component_code;
                $chapterItem["itemId"] = $resource["itemId"];
                $chapterItem["modResId"] = $resource["modResId"];
                $chapterItem["isCourseware"] = $resource["isCourseware"];
                $item = $resource['item'];

                if($resource["isCourseware"]){
                    $chapterItem["courseware_name"] = $item["courseware_name"];
                    $chapterItem["courseware_time"] = $item["courseware_time"];
                    $chapterItem["courseware_type"] = $item["courseware_type"];
                    $chapterItem["is_display_mobile"] = $item["is_display_mobile"];
                    $chapterItem["is_display_pc"] = $item["is_display_pc"];
                    $chapterItem["courseware_id"] = $item["kid"];

                }
                else{
                    if($componentModel->component_code == "examination"){ //考试

                        $chapterItem["courseware_name"] = $item["title"];
                        $chapterItem["status"] = $item["release_status"];
                        $chapterItem["courseware_id"] = $item["kid"];

                    }
                    else if($componentModel->component_code == "homework"){ //作业
                        $chapterItem["courseware_name"] = $item["title"];
                        $chapterItem["homework_mode"] = $item["homework_mode"];
                        $chapterItem["requirement"] = $item["requirement"];
                        $chapterItem["courseware_id"] = $item["kid"];

                    }
                    else{

                        $chapterItem["courseware_name"] = $item["title"];
                        $chapterItem["status"] = $item["status"];
                        $chapterItem["investigation_type"] = $item["investigation_type"];
                        $chapterItem["courseware_id"] = $item["kid"];

                    }
                }

                $learned = false;
                if ($isReg && !$isCourseComplete) {
                    $learned = $resourceCompleteService->isResComplete($courseCompleteId, $resource["modResId"]);
                    $learning = $resourceCompleteService->isResDoing($courseCompleteId, $resource["modResId"]);

                } else if ($isReg && $isCourseComplete){
                    $learned = true;
                } else {
                    $learned = false;
                }

                $chapterItem["learning"] = $learning == null ? false : $learning;
                $chapterItem["learned"] = $learned;
                if ($canRun && !$isRandom && !$learned)
                {
                    $canRun = false;
                }
                $chapterItem["canRun"] = $canRun;

                //======================判断是否显示======================
                $displayItem = true;
                if (!$isOnlineCourse) {
                    /*预览模式也可见*/
                    if ($resource['modRes']->publish_status == LnModRes::NO && $mode != ApiCourseService::PLAY_MODE_PREVIEW)
                    {
                        $displayItem = false;
                    }
                }
                if($displayItem){
                    $chapterArray[] = $chapterItem;
                    $i++;
                }
                //======================判断是否显示END======================
            }

            $catalogArray[$j]["chapters"] = $chapterArray;
            $j++;
        }
        $result = TMessageHelper::resultBuild($this->systemKey,$code="OK", $name="catalogBottomMenu", $message, $catalogArray);
        return $result;

    }

    //获取课程课件列表
    public function getCatalogBottomMenu($courseRegId,$courseCompleteId,$courseId,$isReg,$isCourseComplete,$isOnlineCourse,$isRandom,$openStatus){
        $mode = ApiCourseService::PLAY_MODE_NORMAL;
        $resourceService = new ResourceService();
        $resourceCompleteService = new ResourceCompleteService();
        $courseMods = $resourceService->getCourseMods($courseId);

        $canRun = true;

        if (!$isOnlineCourse && $openStatus != LnCourse::COURSE_START) //如果是面试课程然后未到开课时间是不能学习的
        {
            $canRun = false;
        }


        $catalogArray = array();
        $j = 0;
        foreach($courseMods as $mod){
            $catalogArray[$j]["time"] = $mod["time"];
            $catalogArray[$j]["name"] = $mod["mod_name"];
            $catalogArray[$j]["mod_desc"] = $mod["mod_desc"];

            $chapterArray = array();
            $i = 0;
            foreach($mod["courseitems"] as $num => $resource){

                $chapterItem["itemId"] = $courseCompleteId["itemId"];
                $componentModel = LnComponent::findOne($resource["componentId"]);
                $chapterItem["componentIcon"] = $componentModel->icon;
                $chapterItem["componentTitle"] = $componentModel->title;
                $chapterItem["componentCode"] = $componentModel->component_code;
                $chapterItem["itemId"] = $resource["itemId"];
                $chapterItem["modResId"] = $resource["modResId"];
                $chapterItem["isCourseware"] = $resource["isCourseware"];
                $item = $resource['item'];

                if($resource["isCourseware"]){
                    $chapterItem["courseware_name"] = $item["courseware_name"];
                    $chapterItem["courseware_time"] = $item["courseware_time"];
                    $chapterItem["courseware_type"] = $item["courseware_type"];
                    $chapterItem["is_display_mobile"] = $item["is_display_mobile"];
                    $chapterItem["is_display_pc"] = $item["is_display_pc"];
                    $chapterItem["courseware_id"] = $item["kid"];

                }
                else{
                    if($componentModel->component_code == "examination"){ //考试

                        $chapterItem["courseware_name"] = $item["title"];
                        $chapterItem["status"] = $item["release_status"];
                        $chapterItem["courseware_id"] = $item["kid"];

                    }
                    else if($componentModel->component_code == "homework"){ //作业
                        $chapterItem["courseware_name"] = $item["title"];
                        $chapterItem["homework_mode"] = $item["homework_mode"];
                        $chapterItem["requirement"] = $item["requirement"];
                        $chapterItem["courseware_id"] = $item["kid"];

                    }
                    else{

                        $chapterItem["courseware_name"] = $item["title"];
                        $chapterItem["status"] = $item["status"];
                        $chapterItem["investigation_type"] = $item["investigation_type"];
                        $chapterItem["courseware_id"] = $item["kid"];

                    }

                }

                $learned = false;
                $learning = null;
                if ($isReg && !$isCourseComplete) {
                    $learned = $resourceCompleteService->isResComplete($courseCompleteId, $resource["modResId"]);
                    $learning = $resourceCompleteService->isResDoing($courseCompleteId, $resource["modResId"]);

                } else if ($isReg && $isCourseComplete){
                    $learned = true;
                } else {
                    $learned = false;
                }

                $chapterItem["learning"] = $learning == null ? false : $learning;
                $chapterItem["learned"] = $learned;
                if ($canRun && !$isRandom && !$learned)
                {
                    $canRun = false;
                }
                $chapterItem["canRun"] = $canRun;
                //======================判断是否显示======================
                $displayItem = true;
                if (!$isOnlineCourse) {
                    /*预览模式也可见*/
                    if ($resource['modRes']->publish_status == LnModRes::NO && $mode != self::PLAY_MODE_PREVIEW)
                    {
                        $displayItem = false;
                    }
                }
                if($displayItem){
                    $chapterArray[] = $chapterItem;
                    $i++;
                }
                //======================判断是否显示END======================
            }

            $catalogArray[$j]["chapters"] = $chapterArray;
            $j++;
        }

        return $catalogArray;
    }

    /**
     * 课程评分
     * @return array
     */
    public function actionRating(){
        $isGet = Yii::$app->request->isGet;
        $apiCourseService = new ApiCourseService($this->systemKey);
        $code = null;
        $name = "course_rate";
        $message = null;
        if($isGet) {
            return $apiCourseService->exception(['number' => '000','name' => $name]);
        }
        $bodyParams = $apiCourseService->decryptBodyParam();
        if($bodyParams === false) {
            return $apiCourseService->exception(['number' => '004','param' => 'post','name' => $name]);
        }

        if(!isset($bodyParams['course_id']) || empty($bodyParams['course_id']) || !isset($bodyParams['rating']) || empty($bodyParams['rating'])) {
            return $apiCourseService->exception(['number' => '001','param' => 'course_id & rating is required.','code' => 'NO']);
        }

        $userId = $this->user->kid;
        $courseId = $bodyParams['course_id'];
        $rating = floatval($bodyParams['rating']);

        return $apiCourseService->rate($userId,$courseId,$rating,$this->user->company_id);
    }


    /**
     * 注册
     * @return array
     */
    public function actionReg()
    {
        $isGet = Yii::$app->request->isGet;
        $apiCourseService = new ApiCourseService($this->systemKey);
        $code = null;
        $name = "course_reg";
        $message = null;
        if($isGet) {
            return $apiCourseService->exception(['number' => '003','name' => $name]);
        }
        $bodyParams = $apiCourseService->decryptBodyParam();
        if($bodyParams === false) {
            return $apiCourseService->exception(['number' => '004','param' => 'post','name' => $name]);
        }

        if(!isset($bodyParams['course_id']) || empty($bodyParams['course_id'])) {
            return $apiCourseService->exception(['number' => '001','param' => 'course_id is required.','code' => 'NO']);
        }

        return $apiCourseService->register($this->user->kid,$bodyParams['course_id'],$this->user->company_id);
    }


    /**
     * 获取课程问题记录
     * @return array
     */
    public function actionGetCourseQuestion(){
        $isGet = Yii::$app->request->isGet;
        $apiCourseService = new ApiCourseService($this->systemKey);
        $code = null;
        $name = "course_question";
        $message = null;
        if($isGet) {
            return $apiCourseService->exception(['number' => '003','name' => $name]);
        }
        $bodyParams = $apiCourseService->decryptBodyParam();
        if($bodyParams === false) {
            return $apiCourseService->exception(['number' => '004','param' => 'post','name' => $name]);
        }
        if(!isset($bodyParams['page']) || !isset($bodyParams['course_id'])) {
            return $apiCourseService->exception(['number' => '001','param' => 'course_id & page is required.']);
        }

        $size = 10;
        $page = $size < 1 ? 0 : (intval($bodyParams['page']) - 1) * $size;
        $service = new QuestionService();
        $data = $service->getCourseQuestion($bodyParams['course_id'], $size, $page, 't1.created_at asc');
        $result = TMessageHelper::resultBuild($this->systemKey,$code='OK', $name, $message, $data);

        return $result;

    }


    /**
     * 获取课程库列表
     * $page 和 $offset 二选一,都有值时使用offset
     * 
     * @param $current_time
     * @param null $ids
     * @param int $page
     * @param string $order
     * @param null $type
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function actionGetList($current_time=null, $ids = null, $page = 1, $order = 'new', $type = null,$offset = 0,$limit = 10)
    {
        if(Yii::$app->request->isPost) {
            $params = Yii::$app->request->getBodyParams();
            $current_time = $params['current_time'];
            $ids = $params['ids'];
            $page = $params['page'];
            $order = $params['order'];
            $type = $params['type'];
            $offset = $params['offset'];
            $limit = $params['limit'];
        }
        $this->layout = 'none';

        if ($ids) {
            $ids = explode(',', $ids);
        }

        $user_id = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;

        $offset = Yii::$app->request->getQueryParam('offset');
        if(!$offset) {
            $offset = ((int)$page - 1) * $limit;
        }
        
        $service = new ResourceService();

        $isMobile = false;
        if (Yii::$app->session->has("isMobile")) {
            $isMobile = Yii::$app->session->get("isMobile");
        }

        $resource = $service->getResource(
            $user_id,
            $companyId,
            $ids, 
            $type,
            $limit,
            $offset, 
            $order, 
            $isMobile, 
            $current_time
        );

        $domain = Yii::$app->request->hostInfo;
        $map = [
            'kid' => 'course_id',
            'course_name' => 'course_name',
            'course_desc_nohtml' => 'course_desc',
            'created_at' => 'created_at',
            'category_id' => 'category_id',
            'updated_at' => 'updated_at',
            'course_type' => 'course_type',
            'theme_url' => ['as' => 'theme_url','filter' => function($val) use($domain){
                return  empty($val) ? $domain."/static/frontend/images/course_theme_big.png" : $domain.$val;
            }]
        ];
        
        $ret = [];
        foreach($resource as $res) {
            $tmp = [];
            foreach($map as $field => $set) {
                $filter = is_array($set) && isset($set['filter']) ? $set['filter'] : function($val) {return $val;};
                $key = is_array($set) ? $set['as'] : $set;
                $tmp[$key] = $filter($res->$field);
            }
            $ret[] = $tmp;
            unset($tmp);
        }
        return TMessageHelper::resultBuild($this->systemKey, $code ='OK', null, null, $ret);
    }

    //www.lms.com/api/v2/course/get-catalog-list?system_key=hpe-ios&access_token=88edd1e9d3d5cd9b555c207661628d02&course_id=27C817BC-D9A9-3368-0339-D44A94AEC440&user_id=E06E7FAC-D976-A1A4-AF8E-82B846CEBCA
    /**
     * 获取课程类别列表
     * @return array
     */
    public function actionGetCatalogList()
    {

        $code = "CatalogList";
        $name = null;
        $message = null;


        $isMobile = false;
        $userId = $this->user->kid;
        $companyId = $this->user->company_id;


        $courseCategoryService = new CourseCategoryService();
        $data = $courseCategoryService->GetAllCategory($isMobile,$userId,$companyId);

        $catelog = array();
        foreach($data as $res){

            $catelog[] =array_merge($res->attributes,['count'=>$res->course_count]);;

        }

        $result = TMessageHelper::resultBuild($this->systemKey, $code ='OK', $name, $message, $catelog);
        return $result;
    }


    /**
     * 课程分享
     * @return array
     */
    public function actionShare()
    {
        $isGet = Yii::$app->request->isGet;
        $apiCourseService = new ApiCourseService($this->systemKey);
        $code = null;
        $name = "CourseShare";
        $message = null;
        if($isGet) {
            return $apiCourseService->exception(['number' => '003','name' => $name]);
        }
        $bodyParams = $apiCourseService->decryptBodyParam();
        if($bodyParams === false) {
            return $apiCourseService->exception(['number' => '004','param' => 'post','name' => $name]);
        }
        $validators = [
            'required' => function($val) {
                return !empty($val);
            }
        ];
        $rules = [
            'course_id' => 'required',
            'title' => 'required',
            'content' => 'required'
        ];
        foreach($rules as $field => $rule) {
            if(! $validators[$rule]($bodyParams[$field])) {
                return $apiCourseService->exception(['number' => '001','code' => $code,'param' => $field]);
                break;
            }
        }
        $course_id = $title = $content = $atUserKids = null;
        extract($bodyParams,EXTR_OVERWRITE);
        $atUserList=null;
        $user_id = $this->user->kid;
        if(!empty($atUserKids)){
            $atUserList=FwUser::findByKids(explode(',',$atUserKids));
        }

        $service = new ShareService();
        if ($service->CourseShare($user_id, $course_id,$title , $content, $atUserList)) {
            return $apiCourseService->response(['code' => 'OK','message' => 'share success','data' => ['result' => 'success']]);
        } else {
            return $apiCourseService->response(['code' => 'OK','message' => 'share failed','data' => ['result' => 'failed']]);
        }
    }

    /**
     * 课程报名
     * @return array
     */
    public function actionGetEnroll(){
        $isGet = Yii::$app->request->isGet;
        $apiCourseService = new ApiCourseService($this->systemKey);
        $code = null;
        $name = "courseEnroll";
        $message = null;
        if($isGet) {
            return $apiCourseService->exception(['number' => '003','name' => $name]);
        }
        $bodyParams = $apiCourseService->decryptBodyParam();
        if($bodyParams === false) {
            return $apiCourseService->exception(['number' => '004','param' => 'post','name' => $name]);
        }
        if(!isset($bodyParams['course_id']) || empty($bodyParams['course_id'])) {
            return $apiCourseService->exception(['code' => $code,'number' => '001','param' => 'course_id']);
        }
        
        return $apiCourseService->enroll($this->user->kid,$bodyParams['course_id'],$this->user->company_id);
    }

    // deprecated by GROOT at 2016.05.23
    public function actionUpdateDuration($mode = ApiCourseService::PLAY_MODE_PREVIEW,$courseCompleteProcessId,$courseCompleteFinalId,$resCompleteProcessId,$resCompleteFinalId)
    {
        $code = "";
        $name = null;
        $message = null;
        $currentTime = time();
        $duration = ApiCourseService::LEARNING_DURATION;

        $commonUserService = new UserService();
        $commonUserService->keepOnline($this->user->kid);

        if ($mode == ApiCourseService::PLAY_MODE_NORMAL) {
            if (!empty($courseCompleteProcessId) && !empty($courseCompleteFinalId)) {
                $courseCompleteService = new CourseCompleteService();

                $courseCompleteService->setLastRecordAt($courseCompleteProcessId, $currentTime, $duration,$this->systemKey);
                $courseCompleteService->setLastRecordAt($courseCompleteFinalId, $currentTime, $duration,$this->systemKey);
            }

            if (!empty($resCompleteProcessId) && !empty($resCompleteFinalId)) {
                $resourceCompleteService = new ResourceCompleteService();
                $resourceCompleteService->setLastRecordAt($resCompleteProcessId, $currentTime, $duration,$this->systemKey);
                $resourceCompleteService->setLastRecordAt($resCompleteFinalId, $currentTime, $duration,$this->systemKey);
            }

            return ['result' => 'success'];
        }
        else {
            return ['result' => 'success'];
        }
    }

    /**
     * 获取学习状态
     * @param $course_id
     * @param $modResId
     * @return array
     */
    public function actionGetLearnStatus($course_id, $mod_res_id) {
        $courseModel = new CourseService();
        $status = $courseModel->learnStatus(Yii::$app->user->id,$course_id,$mod_res_id);
        function arrayDeep($array) {
            if(!is_array($array)) return 0;
            $level = 0;
            foreach($array as $arr) {
                $tmp = arrayDeep($arr);
                if($tmp > $level) $level = $tmp;
            }
            return $level + 1;
        }
        //一律转换为二维数组
        $level = arrayDeep($status);
        if($level == 1) {
            $status = [$status];
        }
        Yii::$app->response->format = 'json';
        return ['code' => 'OK','result' => json_encode($status)];
    }


    /**
     * 推荐课程
     * @return array
     */
    public function actionRecommend() {
        $user_id = Yii::$app->user->getId();
        $service = new CourseService();

        $data = $service->getRecommendCourse(
            $user_id, 
            Yii::$app->request->getQueryParam('take',3),
            Yii::$app->request->getQueryParam('force_mobile','true') == 'true'
        );

        $tmp = [];
        foreach ($data as $m) {
            $tmp[] = $m->attributes;
        }
        $data = $tmp;
        return [
            'code' => 'OK',
            'result' => json_encode($data)
        ];
    }
}