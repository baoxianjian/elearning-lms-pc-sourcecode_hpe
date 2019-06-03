<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/4/29
 * Time: 16:52
 */

namespace frontend\controllers\resource;

use common\base\BaseActiveRecord;
use common\helpers\TExportHelper;
use common\helpers\TFileModelHelper;
use common\helpers\TStringHelper;
use common\helpers\TTimeHelper;
use common\models\framework\FwApprovalFlow;
use common\models\framework\FwCompany;
use common\models\framework\FwUser;
use common\models\learning\LnCertification;
use common\models\learning\LnComponent;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseactivity;
use common\models\learning\LnCourseCategory;
use common\models\learning\LnCourseCertification;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnCourseEnroll;
use common\models\learning\LnCourseMods;
use common\models\learning\LnCourseOwner;
use common\models\learning\LnCourseReg;
use common\models\learning\LnCourseSignIn;
use common\models\learning\LnCourseTeacher;
use common\models\learning\LnCourseware;
use common\models\learning\LnCoursewareScorm;
use common\models\learning\LnExamination;
use common\models\learning\LnHomework;
use common\models\learning\LnInvestigation;
use common\models\learning\LnModRes;
use common\models\learning\LnResComplete;
use common\models\learning\LnResourceAudience;
use common\models\learning\LnResourceDomain;
use common\models\learning\LnScormScoes;
use common\models\learning\LnTeacher;
use common\models\message\MsTimeline;
use common\models\social\SoAnswer;
use common\models\social\SoCollect;
use common\models\social\SoQuestion;
use common\models\social\SoQuestionCare;
use common\models\social\SoUserAttention;
use common\services\framework\DictionaryService;
use common\services\framework\PointRuleService;
use common\services\framework\TagService;
use common\services\framework\TreeNodeService;
use common\services\framework\UserCompanyService;
use common\services\framework\UserDomainService;
use common\services\framework\UserService;
use common\services\interfaces\service\ToolInterface;
use common\services\learning\ComponentService;
use common\services\learning\CourseCategoryService;
use common\services\learning\CourseCompleteService;
use common\services\learning\CourseEnrollService;
use common\services\learning\CourseService;
use common\services\learning\CourseSignInService;
use common\services\learning\CourseSignInSettingService;
use common\services\learning\CoursewareService;
use common\services\learning\ExaminationService;
use common\services\learning\RecordService;
use common\services\learning\ResourceAudienceService;
use common\services\learning\ResourceCompleteService;
use common\services\learning\ResourceDomainService;
use common\services\learning\ResourceService;
use common\services\learning\TeacherManageService;
use common\services\message\MessageService;
use common\services\message\PushMessageService;
use common\services\message\TaskService;
use common\services\message\TimelineService;
use common\services\scorm\ScormScoesService;
use common\services\scorm\ScormScoesTrackService;
use common\services\scorm\ScormService;
use common\services\social\AnswerService;
use common\services\social\AudienceManageService;
use common\services\social\CollectService;
use common\services\social\QuestionCareService;
use common\services\social\QuestionService;
use common\services\social\ShareService;
use common\viewmodels\api\ResponseModel;
use components\widgets\TPagination;
use frontend\base\BaseFrontController;
use frontend\controllers\CommonController;
use yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CourseController extends BaseFrontController
{
    public $layout = 'frame';

    const PLAY_MODE_NORMAL = 'normal';
    const PLAY_MODE_PREVIEW = 'preview';

    const LEARNING_DURATION = "30";//太快会影响性能

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['except'] = ['scan-view', 'get-tab-scan-question', 'get-scan-question', 'get-scan-question-answer', 'get-scorm-status', 'record-scorm-data'];
        return $behaviors;
    }

    /**
     * 课程呈现首页
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /*获取分类*/
    public function actionGetCategory()
    {
        $this->layout = false;

        $isMobile = false;
        if (Yii::$app->session->has("isMobile")) {
            $isMobile = Yii::$app->session->get("isMobile");
        }

        $courseCategoryService = new CourseCategoryService();
//        $courseCategories = $courseCategoryService->ListCourseCategroySelect();
//        $catelog = array();
//        if ($courseCategories) {
//            foreach ($courseCategories as $key => $val) {
//                $courseAll = $courseCategoryService->getCourseCategoryCourse($key, $isMobile);
//                $count_category = count($courseAll);
//                $catelog[] = array('kid' => $key, 'category_name' => $val, 'count' => $count_category);
//            }
//        }
//        $courseAll = $courseCategoryService->getCourseCategoryCourse(null);
//        $count = count($courseAll);

//        var_dump($courseCategoryService->GetAllCategory($isMobile));

        $result = $courseCategoryService->BuildCategoryTree($courseCategoryService->GetAllCategory($isMobile));
        $content = str_replace('@@@all_count@@@', $result['count'], $result['data']);
//        echo ($content);
        return $this->renderAjax('get-category', [
            'category' => $content,
//            'catalog' => $catelog,
//            'count' => $count,
        ]);
    }

    /**
     * 在线课程管理页面
     * @return string
     */
    public function actionManage()
    {
        $userId = Yii::$app->user->getId();
        $companyService = new UserCompanyService();
        $manageCompanyList = $companyService->getUserManagedCompanyList($userId);
        $companyList = array();
        if (!empty($manageCompanyList) && count($manageCompanyList) > 0) {
            $companyList = FwCompany::findAll(['kid' => $manageCompanyList]);
        }
        return $this->render('manage', [
            'companyList' => $companyList,
        ]);
    }

    /**
     * 面授课程管理页面
     * @return string
     */
    public function actionManageFace()
    {
        return $this->render('manage-face');
    }

    /**
     * 在线课程管理列表
     * @return string
     */
    public function actionList()
    {
        $pageSize = $this->defaultPageSize;
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }

        $service = new CourseService();
        $params = Yii::$app->request->queryParams;
        $params['course_type'] = LnCourse::COURSE_TYPE_ONLINE;
        $dataProvider = $service->Search($params);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
        $dataProvider->setPagination($page);
        return $this->renderAjax('list', [
            'page' => $page,
            'searchModel' => $service,
            'dataProvider' => $dataProvider,
            'pageSize' => $pageSize,
            'visable' => $params['visable'],
            'TreeNodeKid' => Yii::$app->request->get('TreeNodeKid'),
        ]);
    }

    /**
     * 面授课程管理列表
     * @return string
     */
    public function actionListFace()
    {
        $pageSize = $this->defaultPageSize;
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }

        $service = new CourseService();
        $params = Yii::$app->request->queryParams;
        $params['course_type'] = LnCourse::COURSE_TYPE_FACETOFACE;
        $dataProvider = $service->Search($params);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
        $dataProvider->setPagination($page);
        return $this->renderAjax('list-face', [
            'page' => $page,
            'searchModel' => $service,
            'dataProvider' => $dataProvider,
            'pageSize' => $pageSize,
            'visable' => $params['visable'],
            'TreeNodeKid' => Yii::$app->request->get('TreeNodeKid'),
        ]);
    }

    public function actionCopyOption()
    {
        $userId = Yii::$app->user->getId();
        $userDomainService = new UserDomainService();
        $domainIds = $userDomainService->getManagedListByUserId($userId);
        $companyId = Yii::$app->request->get('companyId');
        if (!empty($domainIds)) {
            foreach ($domainIds as $key => $val) {
                if ($val->company_id != $companyId) {
                    unset($domainIds[$key]);
                }
            }
        }
        $courseId = Yii::$app->request->get('courseId');
        $categoryService = new CourseCategoryService();
        $courseCategory = $categoryService->ListCourseCategroySelect($companyId);
        $course = LnCourse::findOne($courseId);
        $company = FwCompany::findOne($companyId);
        return $this->renderAjax('copy-option', [
            'category' => $courseCategory,
            'domain' => $domainIds,
            'course' => $course,
            'company' => $company,
        ]);
    }

    /*在线复制*/
    public function actionOnlineCopy($id = null, $origin_course_id, $companyId)
    {
        if (empty($origin_course_id) || empty($companyId)) {
            $this->redirect(Yii::$app->urlManager->createUrl(['resource/course/manage']));
        }
        $this->clearCourseCookie();
        $model = !empty($id) ? $this->findModel($id) : $this->findModel($origin_course_id);
        $post_data = Yii::$app->request->post();
        if (Yii::$app->request->isPost && $model->load($post_data)) {
            $model->course_name = Html::encode(Html::decode($model->course_name));
            $model->start_time = $model->start_time ? $model->start_time : date('Y-m-d');
            if ($model->theme_url == "") {
                $model->theme_url = null;
            }
            //特殊处理
            if (isset($post_data['LnCourse']['is_display_pc']) && $post_data['LnCourse']['is_display_pc'] == LnCourse::DISPLAY_PC_YES) {
                $model->is_display_pc = LnCourse::DISPLAY_PC_YES;
            } else {
                $model->is_display_pc = LnCourse::DISPLAY_PC_NO;
            }
            if (isset($post_data['LnCourse']['is_display_mobile']) && $post_data['LnCourse']['is_display_mobile'] == LnCourse::DISPLAY_MOBILE_YES) {
                $model->is_display_mobile = LnCourse::DISPLAY_MOBILE_YES;
            } else {
                $model->is_display_mobile = LnCourse::DISPLAY_MOBILE_NO;
            }
            //end
            $courseService = new CourseCategoryService();
            $course_category_id = $courseService->getCourseCategoryIdByTreeNodeId($model->category_id);/*node-kid->category_id*/
            $model->category_id = $course_category_id;
            $domain_id = explode(',', Yii::$app->request->post('domain_id'));
            $tree_node_id = Yii::$app->request->post('tree_node_id');
            $tag = Yii::$app->request->post('tag');
            $teacher_id = Yii::$app->request->post('teacher_id');
            $certification_id = Yii::$app->request->post('certification_id');
            $audienceId = Yii::$app->request->post('audience_id');
        } else {
            $model->kid = null;
            $model->course_name = Html::encode(Html::decode($model->course_name)).Yii::t('common', 'copies');
            $model->start_time = !empty($model->start_time) ? date('Y-m-d', $model->start_time) : date('Y-m-d');
            $model->end_time = !empty($model->end_time) ? date('Y-m-d', $model->end_time) : null;
            $loginUserCompanyId = Yii::$app->user->identity->company_id;
            if ($loginUserCompanyId == $companyId) {
                /*category_id转换成tree_node_id*/
                $courseCategories = new LnCourseCategory();
                $findOne = $courseCategories->findOne($model->category_id);
                if (!empty($findOne)) {
                    $tree_node_id = $findOne->tree_node_id;
                } else {
                    $tree_node_id = "";
                }

                $resourceDomain = new ResourceDomainService();
                $resourceDomain->resource_id = $origin_course_id;
                $resourceDomain->resource_type = LnResourceDomain::RESOURCE_TYPE_COURSE;
                $domain_id = $resourceDomain->getContentList($resourceDomain);
                /*tag-teacher*/
                $tagService = new TagService();
                $tag_arr = $tagService->getTagValue($origin_course_id);
                $tag = array();
                if ($tag_arr) {
                    foreach ($tag_arr as $val) {
                        $tag[] = $val->tag_value;
                    }
                }
                $teacherService = new LnCourseTeacher();
                $teacher_id = $teacherService->getCourseTeacher($origin_course_id, 'teacher_id');
                $certificationModel = new LnCertification();
                $certificationResult = $certificationModel->getCourseCertification($origin_course_id);
                if (!empty($certificationResult)) {
                    $certification_id = $certificationResult->certification_id;
                } else {
                    $certification_id = '';
                }
                $resourceAudienceService = new ResourceAudienceService();
                $audienceId = $resourceAudienceService->getResourceAudience($origin_course_id, $model->company_id, LnResourceAudience::RESOURCE_TYPE_COURSE);
            } else {
                $tree_node_id = "";
                $domain_id = array();
                $tag = array();
                $teacher_id = '';
                $certification_id = '';
                $audienceId = array();
            }
            $model->company_id = $companyId;
        }
        $uid = Yii::$app->user->getId();
        $userDomainService = new UserDomainService();
        $domain = $userDomainService->getManagedListByUserId($uid);
        $dictionary_service = new DictionaryService();

        /*课程级别字典*/
        $dictionary_level_list = $dictionary_service->getDictionaryArray('course_level');
        /*语言*/
        $dictionary_lang_list = $dictionary_service->getDictionaryArray('course_language');
        /*币种*/
        $dictionary_currency_list = $dictionary_service->getDictionaryArray('currency');
        /*单位*/
        $course_period_unit_list = $model->getCoursePeriodUnits();
        /*审批级别*/
        $dictionary_approval_list = $dictionary_service->getDictionaryArray('approval_flow');

        if (!empty($teacher_id)) {
            $searchTearchManager = new TeacherManageService();
            $teacherResult = $searchTearchManager->courseSearchTeacher(null, $teacher_id, false);
        }
        if (!empty($certification_id)) {
            $certificationM = new LnCertification();
            $certificationResult = $certificationM->findOne($certification_id);
        }
        /*js api更新*/
        $tags = array();
        if (!empty($tag)) {
            foreach ($tag as $itmes) {
                $tags[] = array('kid' => null, 'title' => urlencode($itmes));
            }
            $tags = array('results' => $tags);
            $tag = urldecode(json_encode($tags));
        } else {
            $tag = "";
        }

        $teach_arr = array();
        if (!empty($teacherResult)) {
            foreach ($teacherResult as $teach_item) {
                $teach_arr[] = array('kid' => $teach_item['kid'], 'title' => urlencode($teach_item['teacher_name']) . '(' . $teach_item['email'] . ')');
            }
            $teach_arr = array('results' => $teach_arr);
            $teacherResult = urldecode(json_encode($teach_arr));
        } else {
            $teacherResult = "";
        }

        if (!empty($certificationResult)) {
            $certificationTemp = array('results' => array(array('kid' => $certificationResult->kid, 'title' => urlencode($certificationResult->certification_name))));
            $certificationResult = urldecode(json_encode($certificationTemp));
        } else {
            $certificationResult = "";
        }

        $audience = array();
        if (!empty($audienceId)) {
            $audienceService = new AudienceManageService();
            $resourceAudienceResult = $audienceService->getAudienceByKid($audienceId);
            foreach ($resourceAudienceResult as $val) {
                $audience[] = array('kid' => $val->kid, 'title' => urlencode($val->audience_name) . '(' . $val->audience_code . ')');
            }
            $audience = array('results' => $audience);
            $audience = urldecode(json_encode($audience));
        }

        $course_time = isset($post_data['course_time']) ? $post_data['course_time'] : time();
        setcookie('course_time_' . $course_time, $course_time, time() + 7200, '/');
        if ($id) {
            setcookie('courseId_' . $course_time, $id, time() + 7200, '/');
        }
        if (!empty($domain)) {
            foreach ($domain as $key => $val) {
                if ($val->company_id != $companyId) {
                    unset($domain[$key]);
                }
            }
        }
        return $this->render('edit', [
            'model' => $model,
            'origin_course_id' => $origin_course_id,
            'domain' => $domain,
            'domain_id' => $domain_id,
            'tree_node_id' => $tree_node_id,
            'dictionary_level_list' => $dictionary_level_list,
            'dictionary_lang_list' => $dictionary_lang_list,
            'dictionary_currency_list' => $dictionary_currency_list,
            'resource' => $post_data['resource'],
            'tag' => $tag,
            'teacher' => $teacherResult,
            'certification' => $certificationResult,
            'course_time' => $course_time,
            'course_period_unit_list' => $course_period_unit_list,
            'dictionary_approval_list' => $dictionary_approval_list,
            'is_copy' => LnCourse::IS_COPY_YES,
            'audience' => $audience,
        ]);
    }

    /*面授复制*/
    public function actionFaceCopy($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (empty($id)) return ['result' => 'fail'];
        $model = $this->findModel($id);
        $courseService = new CourseService();
        $courseService->courseCopy($model, null, true);/*复制课程*/
        return ['result' => 'success'];
    }

    /**
     * 保存复制课程
     * @param $origin_course_id
     * @return array
     */
    public function actionCopySave($origin_course_id, $companyId, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) {
            $this->redirect(Yii::$app->urlManager->createUrl(['resource/course/online-copy', 'id' => $origin_course_id, 'companyId' => $companyId]));
        }
        $data = Yii::$app->request->post();
        $course_time = $_COOKIE['course_time_' . $data['course_time']];
        if (isset($_COOKIE['courseId_' . $course_time]) && $data['course_time'] == $course_time) {
            $id = isset($_COOKIE['courseId_' . $course_time]) ? $_COOKIE['courseId_' . $course_time] : $id;
        }
        $model = !empty($id) ? LnCourse::findOne($id) : new LnCourse();
        $model->load($data);
        $model->start_time = $model->start_time ? strtotime($model->start_time) : time();
        $model->end_time = $model->end_time ? strtotime($model->end_time) + 86399 : null;
        $model->enroll_end_time = $model->enroll_end_time ? $model->enroll_end_time : null;
        $model->open_end_time = $model->open_end_time ? $model->open_end_time : null;
        $courseService = new CourseService();
        $model->course_version = $courseService->getCourseVersion($model->kid);
        $model->course_code = $model->setCourseCode($model->kid);
        $model->short_code = $courseService->GenerateShortCode();

        if ($model->theme_url == "") {
            $model->theme_url = null;
        } else {
            /*临时文件则移动文件到coursetheme目录下并生成课程库封面图*/
            if (stripos($model->theme_url, '/temp/')) {
                $TFileModelHelper = new TFileModelHelper();
                $res = $TFileModelHelper->moveFile($model->theme_url, '/upload/coursetheme');/*复制一份*/
                $TFileModelHelper->image_resize($res, 378, 225, 'crop');
                $model->theme_url = $res;
            }
        }
        if ($model->max_attempt == "") {
            $model->max_attempt = 0;
        }
        if (!empty($model->course_desc_nohtml)) {
            $model->course_desc_nohtml = strip_tags(Html::decode($model->course_desc_nohtml));
        }
        $model->origin_course_id = $origin_course_id;
        $model->release_at = null;
        /*判断是否全部 调查或考试*/
        if (!empty($data['resource'])) {
            $is_survey_only = LnCourse::IS_SURVEY_ONLY_NO;
            $is_exam_only = LnCourse::IS_EXAM_ONLY_NO;
            $survey = $exam = 0;
            $resourceCount = 0;
            foreach ($data['resource'] as $items) {
                if (!empty($items['coursewares'])) {
                    foreach ($items['coursewares'] as $k => $val) {
                        $resourceCount += count($items['coursewares'][$k]);
                    }
                }
                if (!empty($items['activity'])) {
                    $exam = !empty($items['activity']['examination']) ? $exam + count($items['activity']['examination']) : $exam;
                    $survey = !empty($items['activity']['investigation']) ? $survey + count($items['activity']['investigation']) : $survey;
                    foreach ($items['activity'] as $k => $val) {
                        $resourceCount += count($items['activity'][$k]);
                    }
                }
            }
            if ($resourceCount > 0 && $resourceCount == $survey) {
                $is_survey_only = LnCourse::IS_SURVEY_ONLY_YES;
            }
            if ($resourceCount > 0 && $resourceCount == $exam) {
                $is_exam_only = LnCourse::IS_EXAM_ONLY_YES;
            }
            $model->is_survey_only = $is_survey_only;
            $model->is_exam_only = $is_exam_only;
        }
        $model->vendor_id = empty($model->vendor_id) ? null : $model->vendor_id;
        $model->training_address_id = empty($model->training_address_id) ? null : $model->training_address_id;
        $model->needReturnKey = true;
        if ($model->save() === false) {
            $errmsg = $model->getErrors();
            if (empty($errmsg)) {
                $errmsg = Yii::t('frontend', 'course_save_failed');
            }
            return ['result' => 'fail', 'errmsg' => $errmsg];
        }
        $domain_id = Yii::$app->request->post('domain_id');
        $domain = explode(',', $domain_id);

        $resourceDomainService = new ResourceDomainService();
        $resourceDomainService->updateStatus($model->kid, LnResourceDomain::RESOURCE_TYPE_COURSE, LnResourceDomain::STATUS_FLAG_STOP);

        /*资源所属域*/
        foreach ($domain as $j) {
            $findOne = LnResourceDomain::findOne(['resource_id' => $model->kid, 'domain_id' => $j]);
            $resourceDomainA = !empty($findOne->kid) ? $findOne : new LnResourceDomain();
            $resourceDomainA->resource_id = $model->kid;
            $resourceDomainA->start_at = $model->start_time;
            $resourceDomainA->end_at = $model->end_time;
            $resourceDomainA->resource_type = LnResourceDomain::RESOURCE_TYPE_COURSE;/*资源类型课程*/
            $resourceDomainA->domain_id = $j;
            $resourceDomainA->company_id = $companyId;
            $resourceDomainA->status = LnResourceDomain::STATUS_FLAG_NORMAL;/*资源状态*/
            $resourceDomainA->is_deleted = LnResourceDomain::DELETE_FLAG_NO;
            $resourceDomainA->save();
        }
        /*保存受众资源*/
        $audienceResourceService = new ResourceAudienceService();
        $audienceResourceService->updateResourceAudience($model->kid, LnResourceAudience::STATUS_FLAG_STOP, LnResourceAudience::RESOURCE_TYPE_COURSE);
        $audience_id = Yii::$app->request->post('audience_id');
        if (!empty($audience_id)) {
            $audience = explode(',', $audience_id);
            foreach ($audience as $m) {
                $audienceData = $audienceResourceService->getDataByAudienceId($m, $model->kid);
                if (empty($audienceData)) {
                    $audienceData = new LnResourceAudience();
                }
                $audienceData->resource_id = $model->kid;
                $audienceData->audience_id = $m;
                $audienceData->company_id = $model->company_id;
                $audienceData->resource_type = LnResourceAudience::RESOURCE_TYPE_COURSE;
                $audienceData->status = LnResourceAudience::STATUS_FLAG_NORMAL;
                $audienceData->start_at = $model->start_time;
                $audienceData->end_at = $model->end_time;
                $audienceResourceService->saveData($audienceData);
            }
        }
        $resourceService = new ResourceService();
        $resourceService->SetCourseResource($data['resource'], $model->kid, LnCourse::IS_COPY_YES, $domain);
        /*课程所有者,在线是需要储存完全所有者,面授需要储存讲师与完全所有者*/
        $user_id = Yii::$app->user->getId();
        $lnowner = new LnCourseOwner();
        $lnowner->addRelationship($model, $user_id, LnCourseOwner::OWNER_TYPE_ALL);
        /*添加标签 */
        $tag = Yii::$app->request->post('tag');
        if (!empty($tag)) {
            $tagSerice = new TagService();
            $tagSerice->addTag($tag, $model->kid, $companyId, 'course', $model->end_time);
        }
        /*添加证书*/
        $certification_id = Yii::$app->request->post('certification_id');
        $certificationModel = new LnCourseCertification();
        $certificationModel->stopRelation($model->kid);
        if (!empty($certification_id)) {
            $certificationModel->addRelation($model, $certification_id);
        }
        /*添加讲师*/
        if ($model->course_type == LnCourse::COURSE_TYPE_ONLINE) {/*在线*/
            $teacher_id = Yii::$app->request->post('teacher_id');
            if (!empty($teacher_id)) {
                $teacherModel = new LnCourseTeacher();
                $teacherModel->addRelation($model, $teacher_id);
            }
        }
        //暂存
        if ($data['LnCourse']['status'] == LnCourse::STATUS_FLAG_TEMP) {
            setcookie('courseId_' . $course_time, $model->kid, time() + 7200, '/');
        } else {
            setcookie('courseId_' . $course_time, '', -1, '/');
            setcookie('course_time_' . $data['course_time'], '', -1, '/');
        }
        return ['result' => 'success', 'id' => $model->kid, 'resourceCount' => $resourceCount, 'exam' => $exam, 'survey' => $survey];
    }

    /**
     * 新版课程详情
     * @param $id
     * @return string
     */
    public function actionView($id)
    {
        $userId = Yii::$app->user->getId();
        $isManager = Yii::$app->user->identity->manager_flag == FwUser::MANAGER_FLAG_YES ? true : false;
        $service = new CourseService();
        $info = $service->detail($userId, $id, $isManager, true, true);
        if ($info['number'] == '200' && $info['code'] == 'OK') {
            $detail = $info['data'];
        } else {
            $this->redirect(Yii::$app->urlManager->createUrl(['/resource/course/index']));
            Yii::$app->end();
        }
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
            'isReg' => $detail['isReg'],
            'isCourseComplete' => $detail['isCourseComplete'],
            'isCourseRetake' => $detail['isCourseRetake'],
            'rating' => $detail['rating'],
            'rating_count' => $detail['rating_count'],
            'canRating' => $detail['canRating'],
            'catalogMenu' => $detail['catalogMenu'],
            'modResId' => $detail['modResId'],
            'isManager' => $isManager,
            'certificationTemplatesUrl' => $detail['certificationTemplatesUrl'],
            'teacher' => $detail['teacher'],
            'isOnlineCourse' => $detail['isOnlineCourse'],
            'isRandom' => $detail['isRandom'],
            'openStatus' => $detail['openStatus'],
            'enrollRegNumber' => $detail['enrollRegNumber'],
            'enrollAlternatenNumber' => $detail['enrollAlternatenNumber'],
            'btn' => $detail['learnStatus'],
            'enrollInfo' => $detail['enrollInfo'],
            'isSignin' => $detail['isSignin'],
            'currentAttempt' => $detail['currentAttempt'],
            'signStatusData' => $detail['sign_status_data'],
        ]);
    }

    /**
     * 生成二维码
     */
    public function actionQrScanCode($code)
    {
        $url = Yii::$app->urlManager->getHostInfo();
        $url = $url . Yii::$app->urlManager->createUrl(['resource/course/scan-view', 'code' => $code]);
        ToolInterface::genQRCode($url);
    }

    public function actionScanView($code)
    {
        $this->layout = 'frame-nologin';
        $courseService = new CourseService();

        $courseModel = $courseService->GetCourseByShortCode($code);
        $id = $courseModel->kid;
        if (!empty($courseModel)) {
            $queryParams = Yii::$app->request->getQueryParams();
            if (!empty($queryParams)) {
                //二维码签到功能
                if (isset($queryParams['action']) && $queryParams['action'] != "") {
                    $action = $queryParams['action'];
                }

                if (isset($queryParams['userId']) && $queryParams['userId'] != "") {
                    $userId = $queryParams['userId'];
                }

                if (!empty($action) && !empty($userId)) {
                    if ($action == "signin") {
                        //面授课程并未开始 不允许签到
                        if ($courseModel->course_type == LnCourse::COURSE_TYPE_FACETOFACE && $courseModel->open_status == LnCourse::COURSE_NOT_START) {
                            $errorMsg = Yii::t('api', 'err_course_sign');
                            return ['success' => 'failure', 'data' => ResponseModel::getErrorResponse(BaseActiveRecord::$defaultKey, 'failure', $errorMsg, $errorMsg)];
                        }

                        //面授课程已结束 不允许签到
                        if ($courseModel->course_type == LnCourse::COURSE_TYPE_FACETOFACE && $courseModel->open_status == LnCourse::COURSE_END) {
                            $errorMsg = Yii::t('api', 'err_course_sign_end');
                            return ['success' => 'failure', 'data' => ResponseModel::getErrorResponse(BaseActiveRecord::$defaultKey, 'failure', $errorMsg, $errorMsg)];
                        }

                        Yii::$app->response->format = Response::FORMAT_JSON;

                        $data = new LnCourseSignIn();
                        $data->sign_time = time();
                        $data->course_id = $id;
                        $data->user_id = $userId;
                        $data->sign_user_id = $userId;
                        $data->sign_system = LnCourseSignIn::SIGN_SYSTEM_APP;
                        $data->sign_type = LnCourseSignIn::SIGN_TYPE_SELF;
                        $courseService = new CourseService();

                        $errorMsg = null;
                        if ($courseService->saveSignInfo($data, $errorMsg)) {
                            //return ['result' => 'success', 'message' => $errorMsg];
                            return ['success' => 'success', 'data' => ResponseModel::wrapResponseObject(['result' => 'success'], $queryParams['system_key'])];
                        } else {
                            //return ['result' => 'failure', 'message' => $errorMsg];
                            return ['success' => 'failure', 'data' => ResponseModel::getErrorResponse(BaseActiveRecord::$defaultKey, 'failure', $errorMsg, $errorMsg)];
                        }
                    }
                }
            }

            $status = $courseModel->status;
            $startTime = $courseModel->start_time;
            $endTime = $courseModel->end_time;
            $currentTime = time();
            if ($status != LnCourse::STATUS_FLAG_NORMAL) {
                //未发布的课程不能学习
                $this->redirect(Yii::$app->urlManager->createUrl(['resource/course/index']));
            }

            if (!empty($startTime) && $startTime > $currentTime) {
                //发布开始时间大于现在的课程不能学习
                $this->redirect(Yii::$app->urlManager->createUrl(['resource/course/index']));
            }

            if (!empty($endTime) && $endTime < $currentTime) {
                //发布结束时间小于现在的课程不能学习
                $this->redirect(Yii::$app->urlManager->createUrl(['resource/course/index']));
            }
        } else {
            //不存在的课程不能学习
            $this->redirect(Yii::$app->urlManager->createUrl(['resource/course/index']));
        }

        $courseModel = $this->findModel($id);

        $isManager = Yii::$app->user->identity->manager_flag == FwUser::MANAGER_FLAG_YES ? true : false;

        $courseService = new CourseService();
        $isOnlineCourse = $courseModel->course_type == LnCourse::COURSE_TYPE_ONLINE ? true : false;
        $isRandom = $courseModel->mod_type == LnCourse::MOD_TYPE_RANDOM ? true : false;
        $openStatus = $courseModel->open_status;

        $rating = number_format($courseService->getCourseMarkByID($id), 1);
        $rating_count = $courseService->getCourseMarkCountByID($id);
        $studyModResId = null;
        $catalogMenu = $courseService->genCatalogMenu(null, $id, false, false, self::PLAY_MODE_PREVIEW, $isOnlineCourse, $isRandom, $openStatus, false, $studyModResId);

        /*选择最后一个未完成后课件作为开始课件*/
        $modResId = null;

        /*增加课程访问量*/
        LnCourse::addFieldNumber($id, 'visit_number');

        /*获取课程证书*/
        $certificationModel = new LnCourseCertification();
        $certificationTemplatesUrl = $certificationModel->getTemplatesUrl($courseModel->kid);
        /*获取课程讲师*/
        $teacherModel = new LnCourseTeacher();
        $teacher = $teacherModel->getTeacherAll($courseModel->kid);
        /*获取报名人数*/
        $enrollRegNumber = 0;
        $enrollAlternatenNumber = 0;
        if ($courseModel->course_type == LnCourse::COURSE_TYPE_FACETOFACE) {
            $enrollRegNumber = $courseService->getEnrollNumber($courseModel->kid, [LnCourseEnroll::ENROLL_TYPE_REG, LnCourseEnroll::ENROLL_TYPE_ALLOW]);
            $enrollAlternatenNumber = $courseService->getEnrollNumber($courseModel->kid, LnCourseEnroll::ENROLL_TYPE_ALTERNATE);
        }

        return $this->render('scan-view', [
            'model' => $courseModel,
            'rating' => $rating,
            'rating_count' => $rating_count,
            //    'canRating' => $canRating,
            'catalogMenu' => $catalogMenu,
            'modResId' => $modResId,
            'certificationTemplatesUrl' => $certificationTemplatesUrl,
            'teacher' => $teacher,
            'isOnlineCourse' => $isOnlineCourse,
            'isRandom' => $isRandom,
            'openStatus' => $openStatus,
            'enrollRegNumber' => $enrollRegNumber,
            'enrollAlternatenNumber' => $enrollAlternatenNumber,
            'id' => $id,
        ]);
    }

    /**
     * 课程报名
     * @param $id
     * @return array
     */
    public function actionGetEnrollCount($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = Yii::$app->user->getId();
        $enrollService = new CourseEnrollService();
        $res = $enrollService->courseEnroll($id, $userId);
        return $res;
    }

    /**
     * @param $id
     * @param $userid
     * @return bool|string
     */
    public function actionGetCourseRegId($id, $userid)
    {
        $reg_id = LnCourseReg::findOne(['course_id' => $id, 'user_id' => $userid], false)->kid;
        if (!empty($reg_id)) {
            return $reg_id;
        } else {
            return false;
        }
    }

    /**
     * 手动添加报名学员
     * @param $id
     * @param $user_id
     * @return array
     */
    public function actionGetEnrollCountOther($id, $user_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $enrollService = new CourseEnrollService();
        $res = $enrollService->courseEnroll($id, $user_id);
        return $res;
    }

    /*面授课程管理*/
    public function actionOfflineSubDetail($id)
    {
        $courseModel = $this->findModel($id);
        $courseService = new CourseService();
        $rating = number_format($courseService->getCourseMarkByID($id), 1);
        $rating_count = $courseService->getCourseMarkCountByID($id);

        $resourceService = new ResourceService();
        $courseMods = $resourceService->getCourseMods($id);
        /*课程报名*/
        $params = Yii::$app->request->queryParams;
        /*申报学员*/
        $params['enroll_type'] = LnCourseEnroll::ENROLL_TYPE_REG;
        $enroll_sb = $courseService->searchCourseEnroll($id, $params);
        /*候补学员*/
        $params['enroll_type'] = LnCourseEnroll::ENROLL_TYPE_ALTERNATE;
        $enroll_hb = $courseService->searchCourseEnroll($id, $params);

        $enrollRegNumber = $courseService->getEnrollNumber($courseModel->kid, [LnCourseEnroll::ENROLL_TYPE_REG, LnCourseEnroll::ENROLL_TYPE_ALLOW]);

        return $this->render('offline-sub-detail', [
            'model' => $courseModel,
            'rating' => $rating,
            'rating_count' => $rating_count,
            'courseMods' => $courseMods,
            'enroll_sb' => $enroll_sb,
            'enroll_hb' => $enroll_hb,
            'enrollRegNumber' => $enrollRegNumber,
        ]);
    }

    /*加载学员数据*/
    public function actionGetCourseEnroll($id)
    {
        $type = isset($_GET['type']) ? $_GET['type'] : '0';
        $courseModel = $this->findModel($id);
        $courseService = new CourseService();
        /*查询报名名额*/
        $enroll[0] = $courseService->getEnrollNumber($id, LnCourseEnroll::ENROLL_TYPE_REG);
        /*报名成功*/
        $enroll[1] = $courseService->getEnrollNumber($id, LnCourseEnroll::ENROLL_TYPE_ALLOW);
        /*候补名额*/
        $enroll[2] = $courseService->getEnrollNumber($id, LnCourseEnroll::ENROLL_TYPE_ALTERNATE);
        /*拒绝名额*/
        $enroll[3] = $courseService->getEnrollNumber($id, LnCourseEnroll::ENROLL_TYPE_DISALLOW);
        /*报名名额剩余*/
        $enroll[4] = $courseModel->limit_number - $enroll[0] - $enroll[1];
        $enroll[4] = $enroll[4] < 0 ? 0 : $enroll[4];

        /*课程报名*/
        $params = Yii::$app->request->queryParams;
        $search_params = $params;
        $params['enroll_type'] = array(LnCourseEnroll::ENROLL_TYPE_REG, LnCourseEnroll::ENROLL_TYPE_ALLOW, LnCourseEnroll::ENROLL_TYPE_DISALLOW);

        $forceShowAll = 'False';
        if (Yii::$app->request->getQueryParam('PageShowAll') != null && Yii::$app->request->getQueryParam('PageShowAll') == 'True') {
            $forceShowAll = 'True';
        }
        $params['showAll'] = $forceShowAll;

        $dictService = new DictionaryService();
        $dictValue = $dictService->getDictionaryValueByCode('system', 'is_demo');

        $params['isDemo'] = $dictValue;

        /*课程报名数据*/
        $enrollService = new CourseEnrollService();
        $result = $enrollService->searchCourseEnroll($id, $params);

        $enrollService = new CourseEnrollService();
        $regularCount = $enrollService->getCourseRegularStudent($id, true);

        return $this->renderAjax('get-course-enroll', [
            'type' => $type,
            'model' => $courseModel,
            'result' => $result,
            'enroll' => $enroll,
            'params' => $search_params,
            'forceShowAll' => $forceShowAll,
            'regularCount' => $regularCount,
            'isDemo' => $dictValue,
        ]);
    }

    /*加载学员数据*/
    public function actionGetCourseStandby($id)
    {
        $type = isset($_GET['type']) ? $_GET['type'] : '0';
        $courseModel = $this->findModel($id);
        $courseService = new CourseService();
        /*查询报名名额*/
        $enroll[0] = $courseService->getEnrollNumber($id, LnCourseEnroll::ENROLL_TYPE_REG);
        /*报名成功*/
        $enroll[1] = $courseService->getEnrollNumber($id, LnCourseEnroll::ENROLL_TYPE_ALLOW);
        /*候补名额*/
        $enroll[2] = $courseService->getEnrollNumber($id, LnCourseEnroll::ENROLL_TYPE_ALTERNATE);
        /*拒绝名额*/
        $enroll[3] = $courseService->getEnrollNumber($id, LnCourseEnroll::ENROLL_TYPE_DISALLOW);
        /*报名名额剩余*/
        $enroll[4] = $courseModel->limit_number - $enroll[0] - $enroll[1];
        $enroll[4] = $enroll[4] < 0 ? 0 : $enroll[4];
        /*课程报名*/
        $params = Yii::$app->request->queryParams;
        $search_params = $params;

        /*课程报名数据*/
        $enrollService = new CourseEnrollService();
        $result = $enrollService->searchCourseEnroll($id, $params);
        //  print_r($params);die;
        return $this->renderAjax('get-course-standby', [
            'type' => $type,
            'model' => $courseModel,
            'result' => $result,
            'enroll' => $enroll,
            'params' => $search_params,
        ]);
    }

    /**
     * 更新课程报名人员状态
     * @return array
     */
    public function actionSetCourseEnrollStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $type = Yii::$app->request->post('type');
        $data = LnCourseEnroll::findOne($id);
        if (!empty($data->kid)) {
            /*if ($data->enroll_type != LnCourseEnroll::ENROLL_TYPE_REG){
                return ['result' => 'fail', 'errmsg' => '数据禁止编辑'];
            }*/
            $model = $this->findModel($data->course_id);
            $userId = Yii::$app->user->getId();
            $courseService = new CourseService();
            $pushService = new PushMessageService();

            if ($type == LnCourseEnroll::ENROLL_TYPE_ALLOW) {/*通过*/
                $count = $courseService->getEnrollNumber($data->course_id, LnCourseEnroll::ENROLL_TYPE_ALLOW);
                if ($count >= $model->limit_number) {
                    return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'enroll_is_full')];
                }
                $data->enroll_type = LnCourseEnroll::ENROLL_TYPE_ALLOW;
                $data->approved_by = $userId;
                $data->approved_at = time();
                if ($data->save()) {
                    LnCourse::addFieldNumber($data->course_id, 'enroll_number');/*增加报名成功量*/
                    $courseService->setCourseRegState($data->course_id, $data->user_id, LnCourseReg::REG_STATE_APPROVED);/*更新注册状态*/
                    /*添加积分*/
                    $pointRuleService = new PointRuleService();
                    $user = FwUser::find(false)->andFilterWhere(['kid' => $data->user_id])->select('company_id')->one();
                    $companyId = $user->company_id;
                    $result = $pointRuleService->checkActionForPoint($companyId, $data->user_id, 'Register-Face-Course', 'Learning-Portal', $data->course_id);
                    /*添加时间轴与消息*/
                    $timelineService = new TimelineService();
                    $timelineService->enrollCourseTimeline($data->user_id, $userId, $model->kid);
                    /*更新时间轴*/
                    if ($model->open_status == LnCourse::COURSE_START) {
                        $timelineService->updateButtonType($data->user_id, $model->kid, MsTimeline::OBJECT_TYPE_COURSE, MsTimeline::BUTTON_TYPE_PROCESS);
                    }
                    $messageService = new MessageService();
                    $messageService->pushByCourseRegApproval($userId, $data->course_id, $data->user_id, true);
                    $recodeService = new RecordService();
                    $recodeService->addByEnrollCourse($data->user_id, $data->course_id);
                    /*发送邮件*/
                    $pushService->sendMailByCourseEnroll($model, $userId, $data->user_id, $type);

                    return ['result' => 'success', 'courseId' => $model->kid, 'userId' => $data->user_id, 'status' => 'courseAllowToUser'];
                }
                return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'update_data_failed')];
            } elseif ($type == LnCourseEnroll::ENROLL_TYPE_DISALLOW) {/*拒绝*/
                $data->enroll_type = LnCourseEnroll::ENROLL_TYPE_DISALLOW;
                if ($data->save()) {
                    $courseService->setCourseRegState($data->course_id, $data->user_id, LnCourseReg::REG_STATE_REJECTED);/*更新注册状态*/
                    /*发送邮件通知学员*/
                    $pushService->sendMailByCourseEnroll($model, $userId, $data->user_id, $type);

                    return ['result' => 'success', 'courseId' => $model->kid, 'userId' => $data->user_id, 'status' => 'courseDisallowToUser'];
                }
                return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'update_data_failed')];
            }
            return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'unable_to_operate')];
        } else {
            return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'temp_no_data')];
        }
    }

    /*报名操作后发送邮件*/
    public function actionEnrollSendEmail($courseId, $userId, $status)
    {
        $courseService = new CourseService();
        $courseService->sendEmailToEnrollUser($courseId, $userId, $status);

        $courseService->sendWechatMessageToEnrollUser($courseId, $userId, $status);
    }

    /*候补加入报名中*/
    public function actionMoveCourseEnroll()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $data = LnCourseEnroll::findOne($id);
        if (!empty($data->kid)) {
            if ($data->enroll_type != LnCourseEnroll::ENROLL_TYPE_ALTERNATE) {
                return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'data_edit_limited')];
            }
            $model = $this->findModel($data->course_id);
            $courseService = new CourseService();
            $count = $courseService->getEnrollNumber($data->course_id, [LnCourseEnroll::ENROLL_TYPE_REG, LnCourseEnroll::ENROLL_TYPE_ALLOW]);
            if ($count >= $model->limit_number) {
                return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'enroll_is_full')];
            }
            $data->enroll_type = LnCourseEnroll::ENROLL_TYPE_REG;
            if ($data->update() !== false) {
                return ['result' => 'success', 'errmsg' => Yii::t('frontend', 'edit_sucess')];
            } else {
                return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'edit_failed')];
            }
        } else {
            return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'temp_no_data')];
        }
    }

    /*课程查看*/
    public function actionSee($id)
    {
        $model = $this->findModel($id);
//        $domainService = new UserDomainService();
        $uid = Yii::$app->user->getId();
//        $domain = $domainService->GetSearchListByUserId($uid);
//        /*获取课程域*/
//        $resourceDomain = LnResourceDomain::find(false)->andFilterWhere(['resource_id' => $model->kid])->andFilterWhere(['status' => $model->kid])->distinct()->select('domain_id')->asArray()->all();
//        if ($resourceDomain){
//            $resourceDomain = ArrayHelper::map($resourceDomain, 'domain_id', 'domain_id');
//            $resourceDomain = array_keys($resourceDomain);
//        }
        /*资源模块*/
        $service = new ResourceService();
        $modules = $service->getCourseMods($id);

        return $this->renderAjax('see', [
            'model' => $model,
//            'domain' => $domain,
//            'resource' => $resourceDomain,
            'modules' => $modules,
        ]);
    }

    /*获取学员列表*/
    public function actionGetStudentUser()
    {
        $uid = Yii::$app->user->getId();
        $keyword = Yii::$app->request->get('keyword');
        if (empty($keyword)) $keyword = "";
        $service = new UserService();
        $team_users = $service->getUserByReportManager($uid, $keyword);
        if (empty($team_users)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'no_students_can_be_assigned')];
        } else {
            return $this->renderAjax('get-student-user', [
                'users' => $team_users,
            ]);
        }
    }

    /**
     * 指派弹窗
     * 已注册未完成不发时间轴发消息
     * 已注册已完成不发时间轴与消息
     * 未注册添加注册，添加历程
     * @param string $id
     * @return array
     **/
    public function actionPanelTaskPush($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (empty($id)) return ['result' => 'fail'];
        $uid = Yii::$app->user->getId();
        $users = Yii::$app->request->post('users');
        if (empty($users)) return ['result' => 'fail'];
        $plan_com_at = Yii::$app->request->post('plan_com_at');
        if (!empty($plan_com_at)) {
            $plan_com_at = strtotime($plan_com_at);
        } else {
            $plan_com_at = strtotime("+30 day");
        }
        $model = $this->findModel($id);
        $courseService = new CourseService();
        $courseCompleteService = new CourseCompleteService();
        $message = array();
        $timeline = array();
        foreach ($users as $items) {
            $courseReg = $courseService->getUserRegInfo($items, $id);
            if ($courseReg) {
                $isReg = true;
                $isCourseComplete = $courseCompleteService->isCourseComplete($courseReg->kid);
            } else {
                $isReg = false;
                $isCourseComplete = false;
                LnCourse::addFieldNumber($id, 'register_number');
                $courseService->regCourse($items, $id, LnCourseReg::REG_TYPE_MANAGER, $uid);
            }
            if ($isReg && !$isCourseComplete) {
                $message[] = $items;
                $timeline[] = $items;
                /*更新时间轴时间*/
//                if ($plan_com_at){
//                    $mstime = MsTimeline::findOne(['owner_id'=>$items,'object_id'=>$id,'object_type'=>MsTimeline::OBJECT_TYPE_COURSE],false);
//                    if ($mstime) {
//                        $mstime->end_at = $plan_com_at;
//                        $mstime->update();
//                    }
//                }
            } elseif ($isReg && $isCourseComplete) {

            } else {
                $message[] = $items;
                $timeline[] = $items;
            }
        }
        if (!empty($message)) {
            $service = new MessageService();
            $service->pushByAssignCourse($uid, $id, $message, $plan_com_at);
        }

        if (!empty($timeline)) {
            $timelineService = new TimelineService();
            $timelineService->pushByPushCourse($uid, $id, $timeline, $plan_com_at);
            $record = new RecordService();
            $record->addByPushCourse('2', $uid, $id, $timeline);
        }
        $taskService = new TaskService();
        $domain_id = Yii::$app->user->identity->domain_id;
        $company_id = Yii::$app->user->identity->company_id;
        $taskService->saveManagerPushTask($uid, array($id), $users, $plan_com_at, $domain_id, $company_id);
        return ['result' => 'success'];
    }

    /**
     * Displays a single LnCourse model.
     * @param string $id
     * @return mixed
     */
    public function actionPreview($id)
    {
        $this->layout = 'modalWin';

        $isReg = false;
        $courseRegId = null;
        $canRating = false;
        $isCourseComplete = false;
        $rating = 0;

        $courseModel = $this->findModel($id);
        $isOnlineCourse = $courseModel->course_type == LnCourse::COURSE_TYPE_ONLINE ? true : false;
        $isRandom = $courseModel->mod_type == LnCourse::MOD_TYPE_RANDOM ? true : false;
        $openStatus = $courseModel->open_status;
        $studyModResId = null;
        $courseService = new CourseService();
//        $catalogMenu = $courseService->genCatalogBottomMenuHtml(null, $id, $isReg, $isCourseComplete, self::PLAY_MODE_PREVIEW, $isOnlineCourse, $isRandom, $openStatus, false);

        /*modRes*/
        $modRes = LnModRes::findOne(['course_id' => $id]);

        $isCopy = Yii::$app->request->get('isCopy');
        return $this->renderAjax('preview', [
            'model' => $courseModel,
            'isReg' => $isReg,
            'isCourseComplete' => $isCourseComplete,
            'rating' => $rating,
            'canRating' => $canRating,
//            'catalogMenu' => $catalogMenu,
            'modResId' => $modRes->kid,
            'isCopy' => $isCopy,
        ]);
    }

    /*preview-iframe*/
    public function actionPreviewIframe($id)
    {
        $this->layout = 'modalWin';

        $isReg = false;
        $courseRegId = null;
        $canRating = false;
        $isCourseComplete = false;
        $rating = 0;

        $courseModel = $this->findModel($id);
        $isOnlineCourse = $courseModel->course_type == LnCourse::COURSE_TYPE_ONLINE ? true : false;
        $isRandom = $courseModel->mod_type == LnCourse::MOD_TYPE_RANDOM ? true : false;
        $openStatus = $courseModel->open_status;
        $courseService = new CourseService();
        $studyModResId = null;
        $catalogMenu = $courseService->genCatalogMenu(null, $id, $isReg, $isCourseComplete, self::PLAY_MODE_PREVIEW, $isOnlineCourse, $isRandom, $openStatus, false, $studyModResId);
        $rating_count = $courseService->getCourseMarkCountByID($id);
        /*modRes*/
        $modRes = LnModRes::findOne(['course_id' => $id]);
        /*获取课程证书*/
        $certificationModel = new LnCourseCertification();
        $certificationTemplatesUrl = $certificationModel->getTemplatesUrl($courseModel->kid);
        /*获取课程讲师*/
        $teacherModel = new LnCourseTeacher();
        $teacher = $teacherModel->getTeacherAll($courseModel->kid);
        return $this->render('preview-iframe', [
            'model' => $this->findModel($id),
            'isOnlineCourse' => $isOnlineCourse,
            'isReg' => $isReg,
            'isCourseComplete' => $isCourseComplete,
            'rating' => $rating,
            'canRating' => $canRating,
            'catalogMenu' => $catalogMenu,
            'modResId' => $modRes->kid,
            'certificationTemplatesUrl' => $certificationTemplatesUrl,
            'teacher' => $teacher,
            'rating_count' => $rating_count,
            'mode' => self::PLAY_MODE_PREVIEW
        ]);
    }


    public function actionUpdateDuration($mode = self::PLAY_MODE_PREVIEW, $courseCompleteProcessId, $courseCompleteFinalId, $resCompleteProcessId, $resCompleteFinalId)
    {
        $this->enableCsrfValidation = false;
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $currentTime = time();
            $duration = $this::LEARNING_DURATION;

            $commonUserService = new UserService();
            $commonUserService->keepOnline(Yii::$app->user->getId());

            if ($mode == self::PLAY_MODE_NORMAL) {
                if (!empty($courseCompleteProcessId) && !empty($courseCompleteFinalId)) {
                    $courseCompleteService = new CourseCompleteService();

                    $courseCompleteService->setLastRecordAt($courseCompleteProcessId, $currentTime, $duration);
                    $courseCompleteService->setLastRecordAt($courseCompleteFinalId, $currentTime, $duration);
                }

                if (!empty($resCompleteProcessId) && !empty($resCompleteFinalId)) {
                    $resourceCompleteService = new ResourceCompleteService();
                    $resourceCompleteService->setLastRecordAt($resCompleteProcessId, $currentTime, $duration);
                    $resourceCompleteService->setLastRecordAt($resCompleteFinalId, $currentTime, $duration);
                }

                return ['result' => 'success'];
            } else {
                return ['result' => 'success'];
            }
        } else {
            return ['result' => 'success'];
        }
    }

    private function clearCourseCookie()
    {
        if (!empty($_COOKIE)) {
            foreach ($_COOKIE as $key => $item) {
                if (strpos($key, 'course') !== false) {
                    setcookie($key, '', time() - 1, ' /');
                }
            }
        }
    }

    /**
     * 在线课程添加
     * @return mixed
     */
    public function actionEdit($id = null)
    {
        //setcookie('courseId', '', -1, '/');//清空cookie
        $this->clearCourseCookie();
        $model = $id ? LnCourse::findOne($id) : new LnCourse();
        if (!empty($id) && $model->IsCourseReg()) {
            $this->redirect(Url::toRoute(['/resource/course/afteredit-view', 'id' => $id]));
            Yii::$app->end();
        }
        $post_data = Yii::$app->request->post();
        if (Yii::$app->request->isPost && $model->load($post_data)) {
            $model->start_time = $model->start_time ? $model->start_time : date('Y-m-d');
            if ($model->theme_url == "") {
                $model->theme_url = null;
            }
            $model->course_name = Html::encode(Html::decode($model->course_name));
            //特殊处理
            if (isset($post_data['LnCourse']['is_display_pc']) && $post_data['LnCourse']['is_display_pc'] == LnCourse::DISPLAY_PC_YES) {
                $model->is_display_pc = LnCourse::DISPLAY_PC_YES;
            } else {
                $model->is_display_pc = LnCourse::DISPLAY_PC_NO;
            }
            if (isset($post_data['LnCourse']['is_display_mobile']) && $post_data['LnCourse']['is_display_mobile'] == LnCourse::DISPLAY_MOBILE_YES) {
                $model->is_display_mobile = LnCourse::DISPLAY_MOBILE_YES;
            } else {
                $model->is_display_mobile = LnCourse::DISPLAY_MOBILE_NO;
            }
            //end
            $courseService = new CourseCategoryService();
            $course_category_id = $courseService->getCourseCategoryIdByTreeNodeId($model->category_id);/*node-kid->category_id*/
            $model->category_id = $course_category_id;
            $domain_id = explode(',', Yii::$app->request->post('domain_id'));
            $tree_node_id = Yii::$app->request->post('tree_node_id');
            $tag = Yii::$app->request->post('tag');
            $teacher_id = Yii::$app->request->post('teacher_id');
            $certification_id = Yii::$app->request->post('certification_id');
            $audienceId = Yii::$app->request->post('audience_id');
        } else {
            if ($id) {
                $model->course_name = Html::encode(Html::decode($model->course_name));
                $model->start_time = !empty($model->start_time) ? date('Y-m-d', $model->start_time) : date('Y-m-d');
                $model->end_time = !empty($model->end_time) ? date('Y-m-d', $model->end_time) : null;
                /*category_id转换成tree_node_id*/
                $courseCategories = new LnCourseCategory();
                $findOne = $courseCategories->findOne($model->category_id);
                if (!empty($findOne)) {
                    $tree_node_id = $findOne->tree_node_id;
                } else {
                    $tree_node_id = "";
                }

                $resourceDomain = new ResourceDomainService();
                $resourceDomain->resource_id = $id;
                $resourceDomain->resource_type = LnResourceDomain::RESOURCE_TYPE_COURSE;
                $domain_id = $resourceDomain->getContentList($resourceDomain);
                /*tag-teacher*/
                $tagService = new TagService();
                $tag_arr = $tagService->getTagValue($id);
                $tag = array();
                if ($tag_arr) {
                    foreach ($tag_arr as $val) {
                        $tag[] = $val->tag_value;
                    }
                }
                $teacherService = new LnCourseTeacher();
                $teacher_id = $teacherService->getCourseTeacher($id, 'teacher_id');
                $certificationModel = new LnCertification();
                $certificationResult = $certificationModel->getCourseCertification($id);
                if (!empty($certificationResult)) {
                    $certification_id = $certificationResult->certification_id;
                } else {
                    $certification_id = '';
                }
                $resourceAudienceService = new ResourceAudienceService();
                $audienceId = $resourceAudienceService->getResourceAudience($id, $model->company_id, LnResourceAudience::RESOURCE_TYPE_COURSE);
            } else {
                $domain_id = array();
                $model->start_time = date('Y-m-d');
                $model->course_price = 0;
                $model->course_type = LnCourse::COURSE_TYPE_ONLINE;
                $model->default_credit = 100;
                $model->course_period = 1; //45
                $model->course_period_unit = $model->getCoursePeriodUnitByCourseType($model->course_type);
                $model->is_display_pc = 1;
                $model->is_display_mobile = 1;
//                $model->max_attempt = 0;
                $tree_node_id = "-1";
                $model->company_id = Yii::$app->user->identity->company_id;
                $audienceId = array();
            }
        }
        $uid = Yii::$app->user->getId();
        $userDomainService = new UserDomainService();
        $domain = $userDomainService->getManagedListByUserId($uid);
        $dictionary_service = new DictionaryService();

        /*课程级别字典*/
        $dictionary_level_list = $dictionary_service->getDictionaryArray('course_level');
        /*语言*/
        $dictionary_lang_list = $dictionary_service->getDictionaryArray('course_language');
        /*币种*/
        $dictionary_currency_list = $dictionary_service->getDictionaryArray('currency');
        //单位
        $course_period_unit_list = $model->getCoursePeriodUnits();
        /*审批级别*/
        $dictionary_approval_list = $dictionary_service->getDictionaryArray('approval_flow');

        if (!empty($teacher_id)) {
            $searchTearchManager = new TeacherManageService();
            $teacherResult = $searchTearchManager->courseSearchTeacher(null, $teacher_id, false);
        }
        if (!empty($certification_id)) {
            $certificationM = new LnCertification();
            $certificationResult = $certificationM->findOne($certification_id);
        }
        /*js api更新*/
        $tags = array();
        if (!empty($tag)) {
            foreach ($tag as $itmes) {
                $tags[] = array('kid' => null, 'title' => urlencode($itmes));
            }
            $tags = array('results' => $tags);
            $tag = urldecode(json_encode($tags));
        } else {
            $tag = "";
        }

        $teach_arr = array();
        if (!empty($teacherResult)) {
            foreach ($teacherResult as $teach_item) {
                $teach_arr[] = array('kid' => $teach_item['kid'], 'title' => urlencode($teach_item['teacher_name']) . '(' . $teach_item['email'] . ')');
            }
            $teach_arr = array('results' => $teach_arr);
            $teacherResult = urldecode(json_encode($teach_arr));
        } else {
            $teacherResult = "";
        }

        if (!empty($certificationResult)) {
            $certificationTemp = array('results' => array(array('kid' => $certificationResult->kid, 'title' => urlencode($certificationResult->certification_name))));
            $certificationResult = urldecode(json_encode($certificationTemp));
        } else {
            $certificationResult = "";
        }

        $audience = array();
        if (!empty($audienceId)) {
            $audienceService = new AudienceManageService();
            $resourceAudienceResult = $audienceService->getAudienceByKid($audienceId);
            foreach ($resourceAudienceResult as $val) {
                $audience[] = array('kid' => $val->kid, 'title' => urlencode($val->audience_name) . '(' . $val->audience_code . ')');
            }
            $audience = array('results' => $audience);
            $audience = urldecode(json_encode($audience));
        }

        $course_time = isset($post_data['course_time']) ? $post_data['course_time'] : time();
        setcookie('course_time_' . $course_time, $course_time, time() + 7200, '/');
        if ($id) {
            setcookie('courseId_' . $course_time, $id, time() + 7200, '/');
        }
        return $this->render('edit', [
            'model' => $model,
            'origin_course_id' => null,
            'domain' => $domain,
            'domain_id' => $domain_id,
            'tree_node_id' => $tree_node_id,
            'dictionary_level_list' => $dictionary_level_list,
            'dictionary_lang_list' => $dictionary_lang_list,
            'dictionary_currency_list' => $dictionary_currency_list,
            'resource' => $post_data['resource'],
            'tag' => $tag,
            'teacher' => $teacherResult,
            'certification' => $certificationResult,
            'course_time' => $course_time,
            'course_period_unit_list' => $course_period_unit_list,
            'dictionary_approval_list' => $dictionary_approval_list,
            'is_copy' => LnCourse::IS_COPY_NO,
            'audience' => $audience,
        ]);
    }

    /**
     * 面授课程添加
     * @return mixed
     */
    public function actionEditFace($id = null)
    {
        $this->clearCourseCookie();
        $model = $id ? LnCourse::findOne($id) : new LnCourse();
        $post_data = Yii::$app->request->post();
        if (Yii::$app->request->isPost && $model->load($post_data)) {
            $model->start_time = $model->start_time ? $model->start_time : date('Y-m-d');
            if ($model->theme_url == "") {
                $model->theme_url = null;
            }
            $model->course_name = Html::encode(Html::decode($model->course_name));
            //特殊处理
            if (isset($post_data['LnCourse']['is_display_pc']) && $post_data['LnCourse']['is_display_pc'] == LnCourse::DISPLAY_PC_YES) {
                $model->is_display_pc = LnCourse::DISPLAY_PC_YES;
            } else {
                $model->is_display_pc = LnCourse::DISPLAY_PC_NO;
            }
            if (isset($post_data['LnCourse']['is_display_mobile']) && $post_data['LnCourse']['is_display_mobile'] == LnCourse::DISPLAY_MOBILE_YES) {
                $model->is_display_mobile = LnCourse::DISPLAY_MOBILE_YES;
            } else {
                $model->is_display_mobile = LnCourse::DISPLAY_MOBILE_NO;
            }
            //end
            $courseCategories = new LnCourseCategory();
            $findOne = $courseCategories->findOne($model->category_id);
            $tree_node_id = $findOne->tree_node_id;
            $domain_id = explode(',', Yii::$app->request->post('domain_id'));
            $audienceId = explode(',', Yii::$app->request->post('audience_id'));
            $tag = Yii::$app->request->post('tag');
            $certification_id = Yii::$app->request->post('certification_id');
            $course_temp = Yii::$app->request->post('course');
            if (!empty($course_temp)) {
                foreach ($course_temp as $k => $val) {
                    $course_temp[$k]['enroll_start_time'] = !empty($val['enroll_start_time']) ? strtotime($val['enroll_start_time']) : "";
                    $course_temp[$k]['enroll_end_time'] = !empty($val['enroll_end_time']) ? strtotime($val['enroll_end_time']) + 86399 : "";
                    $course_temp[$k]['open_start_time'] = !empty($val['open_start_time']) ? strtotime($val['open_start_time']) : "";
                    $course_temp[$k]['open_end_time'] = !empty($val['open_end_time']) ? strtotime($val['open_end_time']) + 86399 : "";
                }
            }
        } else {
            if ($id) {
                $model->course_name = Html::encode(Html::decode($model->course_name));
                $model->start_time = $model->start_time ? date('Y-m-d', $model->start_time) : date('Y-m-d');
                $model->end_time = $model->end_time ? date('Y-m-d', $model->end_time) : null;
                /*category_id转换成tree_node_id*/
                $courseCategories = new LnCourseCategory();
                $findOne = $courseCategories->findOne($model->category_id);
                $tree_node_id = $findOne->tree_node_id;

                $resourceDomain = new ResourceDomainService();
                $resourceDomain->resource_id = $id;
                $resourceDomain->resource_type = '1';
                $domain_id = $resourceDomain->getContentList($resourceDomain);
                /*tag-teacher*/
                $tagService = new TagService();
                $tag_arr = $tagService->getTagValue($id);
                $tag = array();
                if ($tag_arr) {
                    foreach ($tag_arr as $val) {
                        $tag[] = $val->tag_value;
                    }
                }
                $teacherService = new LnCourseTeacher();
                $teacher_id = $teacherService->getCourseTeacher($id, 'teacher_id');
                $certificationModel = new LnCertification();
                $certificationResult = $certificationModel->getCourseCertification($id);
                if (!empty($certificationResult)) {
                    $certification_id = $certificationResult->certification_id;
                } else {
                    $certification_id = "";
                }
                $course_temp = array(
                    array(
                        'enroll_start_time' => $model->enroll_start_time,
                        'enroll_end_time' => $model->enroll_end_time,
                        'open_start_time' => $model->open_start_time,
                        'open_end_time' => $model->open_end_time,
                        'limit_number' => $model->limit_number,
                        'is_allow_over' => $model->is_allow_over,
                        'allow_over_number' => $model->allow_over_number,
                        'training_address' => $model->training_address,
                        'training_address_id' => $model->training_address_id,
                        'vendor' => $model->vendor,
                        'vendor_id' => $model->vendor_id,
                        'teacher_id' => $teacher_id,
                    )
                );
                $resourceAudienceService = new ResourceAudienceService();
                $audienceId = $resourceAudienceService->getResourceAudience($id, $model->company_id, LnResourceAudience::RESOURCE_TYPE_COURSE);
            } else {
                $domain_id = array();
                $model->start_time = date('Y-m-d');
                $model->course_price = 0;
                $model->course_type = LnCourse::COURSE_TYPE_FACETOFACE;
                $model->default_credit = 100;
                $model->course_period = 1; //45
                $model->course_period_unit = $model->getCoursePeriodUnitByCourseType($model->course_type);
                $model->is_display_pc = 1;
                $model->is_display_mobile = 1;
//                $model->max_attempt = 0;
                $tree_node_id = "-1";
                $course_temp = array();
                $certification_id = "";
                $audienceId = array();
            }
        }
        $uid = Yii::$app->user->getId();
        $userDomainService = new UserDomainService();
        $domain = $userDomainService->getManagedListByUserId($uid);
        $dictionary_service = new DictionaryService();
        /*课程级别字典*/
        $dictionary_level_list = $dictionary_service->getDictionaryArray('course_level');
        /*语言*/
        $dictionary_lang_list = $dictionary_service->getDictionaryArray('course_language');
        /*币种*/
        $dictionary_currency_list = $dictionary_service->getDictionaryArray('currency');
        //单位
        $course_period_unit_list = $model->getCoursePeriodUnits();
        /*审批级别*/
        $dictionary_approval_list = $dictionary_service->getDictionaryArray('approval_flow');

        if (!empty($certification_id)) {
            $certificationM = new LnCertification();
            $certificationResult = $certificationM->findOne($certification_id);
        }

        /*js api更新*/
        $tags = array();
        if (!empty($tag)) {
            foreach ($tag as $itmes) {
                $tags[] = array('kid' => null, 'title' => urlencode($itmes));
            }
            $tags = array('results' => $tags);
            $tag = urldecode(json_encode($tags));
        } else {
            $tag = "";
        }

        if (!empty($certificationResult)) {
            $certificationTemp = array('results' => array(array('kid' => $certificationResult->kid, 'title' => urlencode($certificationResult->certification_name))));
            $certificationResult = urldecode(json_encode($certificationTemp));
        } else {
            $certificationResult = "";
        }

        $audience = array();
        if (!empty($audienceId)) {
            $audienceService = new AudienceManageService();
            $resourceAudienceResult = $audienceService->getAudienceByKid($audienceId);
            foreach ($resourceAudienceResult as $val) {
                $audience[] = array('kid' => $val->kid, 'title' => urlencode($val->audience_name) . '(' . $val->audience_code . ')');
            }
            $audience = array('results' => $audience);
            $audience = urldecode(json_encode($audience));
        }

        $course_time = isset($post_data['course_time']) ? $post_data['course_time'] : time();
        setcookie('course_time_' . $course_time, $course_time, time() + 7200, '/');
        if ($id) {
            setcookie('courseId_' . $course_time, $id, time() + 7200, '/');
        }

        return $this->render('edit-face', [
            'model' => $model,
            'domain' => $domain,
            'domain_id' => $domain_id,
            'tree_node_id' => $tree_node_id,
            'dictionary_level_list' => $dictionary_level_list,
            'dictionary_lang_list' => $dictionary_lang_list,
            'dictionary_currency_list' => $dictionary_currency_list,
            'resource' => $post_data['resource'],
            'course_temp' => $course_temp ? json_encode($course_temp) : "",
            'tag' => $tag,
            'certification' => $certificationResult,
            'course_time' => $course_time,
            'course_period_unit_list' => $course_period_unit_list,
            'dictionary_approval_list' => $dictionary_approval_list,
            'audience' => $audience,
        ]);
    }

    /**
     * 课程模块编辑及预览、组件添加
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionContent($id = null, $origin_course_id = null)
    {
        $model = $id ? LnCourse::findOne($id) : new LnCourse();
        $post_data = Yii::$app->request->post();
        if (Yii::$app->request->isPost && $model->load($post_data)) {
            $model->start_time = $model->start_time ? $model->start_time : date('Y-m-d');
            if ($model->theme_url == "") {
                $model->theme_url = null;
            }
            //特殊处理
            if (isset($post_data['LnCourse']['is_display_pc']) && $post_data['LnCourse']['is_display_pc'] == LnCourse::DISPLAY_PC_YES) {
                $model->is_display_pc = LnCourse::DISPLAY_PC_YES;
            } else {
                $model->is_display_pc = LnCourse::DISPLAY_PC_NO;
            }
            if (isset($post_data['LnCourse']['is_display_mobile']) && $post_data['LnCourse']['is_display_mobile'] == LnCourse::DISPLAY_MOBILE_YES) {
                $model->is_display_mobile = LnCourse::DISPLAY_MOBILE_YES;
            } else {
                $model->is_display_mobile = LnCourse::DISPLAY_MOBILE_NO;
            }
            //end
            $tree_node_id = $model->category_id;
            $courseService = new CourseCategoryService();
            $course_category_id = $courseService->getCourseCategoryIdByTreeNodeId($tree_node_id);
            $model->category_id = $course_category_id;
            $domain_id = Yii::$app->request->post('domain_id');
            $tag = Yii::$app->request->post('tag');
            $teacher_id = Yii::$app->request->post('teacher_id');
            $certification_id = Yii::$app->request->post('certification_id');
            $audience_id = Yii::$app->request->post('audience_id');
        }
        $model->course_name = TStringHelper::clean_xss($model->course_name);
        $service = new ResourceService();
        $modules = null;
        $domain_id = is_array($domain_id) ? join(',', $domain_id) : $domain_id;
        $audience_id = is_array($audience_id) ? join(',', $audience_id) : $audience_id;
        $resource_temp = $post_data['resource'];
        $component_config = '';
        $component_rescore = '';
        if (!empty($id) && empty($resource_temp)) {
            $modules = $service->getCourseMods($id, true, $domain_id);
        } elseif (!empty($origin_course_id) && empty($resource_temp)) {
            $modules = $service->getCourseMods($origin_course_id, true, $domain_id, LnCourse::COURSE_TYPE_ONLINE, true);
        } elseif (!empty($resource_temp)) {
            $modules = $service->GetPostResource($resource_temp, $domain_id);
            foreach ($resource_temp as $kiss => $val) {
                $mod_num = $val['mod_num'];
                if (!empty($val['config'])) {
                    foreach ($val['config'] as $sk => $ssval) {
                        $json_config = json_decode($ssval, true);
                        $component_config .= '<input type="hidden" value="' . htmlspecialchars($ssval) . '" id="con_' . $mod_num . '_' . $json_config['kid'] . '" name="resource[' . $mod_num . '][config][' . $json_config['kid'] . ']" data-name="config" data-title="' . htmlspecialchars($json_config['title']) . '" data-isfinish="' . $json_config['isfinish'] . '" data-score="' . $json_config['score'] . '" data-componet="' . $json_config['componet'] . '" data-iscore="' . $json_config['isscore'] . '" data-kid="' . $json_config['kid'] . '">';
                        if (intval($json_config['isfinish']) == 1) {
                            $component_config .= '<input id="dir_' . $mod_num . '_' . $json_config['kid'] . '" name="direct" type="hidden" value="' . $json_config['kid'] . '">';
                        }
                    }
                }
                if (!empty($val['rescore'])) {
                    foreach ($val['rescore'] as $kk => $sval) {
                        $json_decode = json_decode($sval, true);
                        $component_rescore .= '<input id="socl_' . $mod_num . '_' . $json_decode['id'] . '" data-score="' . $json_decode['score'] . '" data-id="' . $json_decode['id'] . '" data-modnum="' . $mod_num . '" name="resource[' . $mod_num . '][rescore][' . $json_decode['id'] . ']" type="hidden" value="' . htmlspecialchars($sval) . '">';
                    }
                }
            }
        }
        $ModForm = new LnCourseMods();

        $componentService = new ComponentService();
        $is_setting_component = $componentService->getRecordScore();

        if ($post_data['is_copy'] == LnCourse::IS_COPY_YES) {
            $model->status = LnCourse::STATUS_FLAG_TEMP;
        }

        return $this->render('content', [
            'model' => $model,
            'origin_course_id' => $origin_course_id,
            'ModForm' => $ModForm,
            'modules' => $modules,
            'domain_id' => $domain_id,
            'audience_id' => $audience_id,
            'tree_node_id' => $tree_node_id,
            'tag' => isset($tag) ? $tag : '',
            'teacher' => isset($teacher_id) ? $teacher_id : '',
            'certification_id' => isset($certification_id) ? $certification_id : '',
            'course_time' => $post_data['course_time'],
            'component_config' => $component_config,
            'component_rescore' => $component_rescore,
            'is_setting_component' => $is_setting_component,
            'is_copy' => $post_data['is_copy'],
        ]);
    }

    /**
     * 面授课程报名信息
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionOfflineInfo($id = null)
    {
        $model = $id ? LnCourse::findOne($id) : new LnCourse();
        $action = Yii::$app->request->post('action');
        $post_data = Yii::$app->request->post();
        $model->load($post_data);
        $model->course_name = TStringHelper::clean_xss($model->course_name);
        //特殊处理
        if (isset($post_data['LnCourse']['is_display_pc']) && $post_data['LnCourse']['is_display_pc'] == LnCourse::DISPLAY_PC_YES) {
            $model->is_display_pc = LnCourse::DISPLAY_PC_YES;
        } else {
            $model->is_display_pc = LnCourse::DISPLAY_PC_NO;
        }
        if (isset($post_data['LnCourse']['is_display_mobile']) && $post_data['LnCourse']['is_display_mobile'] == LnCourse::DISPLAY_MOBILE_YES) {
            $model->is_display_mobile = LnCourse::DISPLAY_MOBILE_YES;
        } else {
            $model->is_display_mobile = LnCourse::DISPLAY_MOBILE_NO;
        }
        //end
        if ($action == 'edit') {
            $model->start_time = $model->start_time ? $model->start_time : date('Y-m-d');
            if ($model->theme_url == "") {
                $model->theme_url = null;
            }
            $tree_node_id = $model->category_id;
            $courseCategoryService = new CourseCategoryService();
            $course_category_id = $courseCategoryService->getCourseCategoryIdByTreeNodeId($tree_node_id);
            $model->category_id = $course_category_id;
        } elseif ($action == 'content') {

        }
        $domain_id = Yii::$app->request->post('domain_id');
        $audience_id = Yii::$app->request->post('audience_id');
        $tag = Yii::$app->request->post('tag');
        $certification_id = Yii::$app->request->post('certification_id');
        $course_temp = Yii::$app->request->post('course_temp');
        if ($course_temp) {
            $course_temp = json_decode($course_temp, true);
            if (is_array($course_temp)) {
                $searchTearchManager = new TeacherManageService();
                $courseService = new CourseService();
                $coursewareService = new CoursewareService();
                foreach ($course_temp as $i => $t) {
                    if (!empty($t['teacher_id'])) {
                        $teacher_id = $t['teacher_id'];
                        unset($t['teacher_id']);
                        $teacherResult = $searchTearchManager->courseSearchTeacher(null, $teacher_id, false);
                        $course_temp[$i]['teacher_id'] = $teacherResult;
                    }
                    if (!empty($t['training_address'])) {
                        $course_temp[$i]['training_address'] = $courseService->getTrainingAddressByName($t['training_address'], $t['training_address_id']);
                    }
                    if (!empty($t['vendor'])) {
                        $course_temp[$i]['vendor'] = $coursewareService->getVendorByName($t['vendor'], $t['vendor_id']);
                    }
                }
            }
        }
        return $this->render('offline-info', [
            'id' => $id,
            'model' => $model,
            'domain_id' => is_array($domain_id) ? join(',', $domain_id) : $domain_id,
            'audience_id' => is_array($audience_id) ? join(',', $audience_id) : $audience_id,
            'resource' => $post_data['resource'],
            'course_temp' => $course_temp,
            'tag' => isset($tag) ? $tag : '',
            'certification_id' => isset($certification_id) ? $certification_id : '',
            'course_time' => $post_data['course_time'],
        ]);
    }

    public function actionAftereditView($id = null)
    {

        //baoxianjian
        //setcookie('courseId', '', -1, '/');//清空cookie
        $this->clearCourseCookie();
        $model = $id ? LnCourse::findOne($id) : new LnCourse();
        $post_data = Yii::$app->request->post();
        $courseService = new CourseService();
        if (Yii::$app->request->isPost && $model->load($post_data)) {
            $model->start_time = $model->start_time ? $model->start_time : date('Y-m-d');
            if ($model->theme_url == "") {
                $model->theme_url = null;
            }
            //特殊处理
            if (isset($post_data['LnCourse']['is_display_pc']) && $post_data['LnCourse']['is_display_pc'] == LnCourse::DISPLAY_PC_YES) {
                $model->is_display_pc = LnCourse::DISPLAY_PC_YES;
            } else {
                $model->is_display_pc = LnCourse::DISPLAY_PC_NO;
            }
            if (isset($post_data['LnCourse']['is_display_mobile']) && $post_data['LnCourse']['is_display_mobile'] == LnCourse::DISPLAY_MOBILE_YES) {
                $model->is_display_mobile = LnCourse::DISPLAY_MOBILE_YES;
            } else {
                $model->is_display_mobile = LnCourse::DISPLAY_MOBILE_NO;
            }
            //end
            $courseService = new CourseCategoryService();
            $course_category_id = $courseService->getCourseCategoryIdByTreeNodeId($model->category_id);/*node-kid->category_id*/
            $model->category_id = $course_category_id;
            $domain_id = explode(',', Yii::$app->request->post('domain_id'));
            $tree_node_id = Yii::$app->request->post('tree_node_id');
            //$temp = Yii::$app->request->post('temp');
            $tag = Yii::$app->request->post('tag');
            $teacher_id = Yii::$app->request->post('teacher_id');
            $certification_id = Yii::$app->request->post('certification_id');
        } else {
            if ($id) {
                $model->start_time = $model->start_time ? date('Y-m-d', $model->start_time) : date('Y-m-d');
                $model->end_time = $model->end_time ? date('Y-m-d', $model->end_time) : null;
                /*category_id转换成tree_node_id*/
                $courseCategories = new LnCourseCategory();
                $findOne = $courseCategories->findOne($model->category_id);
                if (!empty($findOne)) {
                    $tree_node_id = $findOne->tree_node_id;
                } else {
                    $tree_node_id = "";
                }

                $resourceDomain = new ResourceDomainService();
                $resourceDomain->resource_id = $id;
                $resourceDomain->resource_type = '1';
                $domain_id = $resourceDomain->getContentList($resourceDomain);
                /*tag-teacher*/
                $tagService = new TagService();
                $tag_arr = $tagService->getTagValue($id);
                $tag = array();
                if ($tag_arr) {
                    foreach ($tag_arr as $val) {
                        $tag[] = $val->tag_value;
                    }
                }
                $teacherService = new LnCourseTeacher();
                $teacher_id = $teacherService->getCourseTeacher($id, 'teacher_id');
                $certificationModel = new LnCertification();
                $certificationResult = $certificationModel->getCourseCertification($id);
                if (!empty($certificationResult)) {
                    $certification_id = $certificationResult->certification_id;
                } else {
                    $certification_id = '';
                }
            } else {
                $domain_id = array();
                $model->start_time = date('Y-m-d');
                $model->course_price = 0;
                $model->course_type = LnCourse::COURSE_TYPE_ONLINE;
                $model->default_credit = 100;
                $model->course_period = 1; //45
                $model->course_period_unit = $model->getCoursePeriodUnitByCourseType($model->course_type);
                $model->is_display_pc = 1;
                $model->is_display_mobile = 1;
//                $model->max_attempt = 0;
                $tree_node_id = "-1";


            }
        }
        $uid = Yii::$app->user->getId();
        $courseCategoryService = new CourseCategoryService();
        $courseCategories = $courseCategoryService->ListCourseCategroySelect();
        $userDomainService = new UserDomainService();
        $domain = $userDomainService->getManagedListByUserId($uid);
        $dictionary_service = new DictionaryService();


        /*课程级别字典*/
        $dictionary_level_list = $dictionary_service->getDictionaryArray('course_level');
        /*语言*/
        $dictionary_lang_list = $dictionary_service->getDictionaryArray('course_language');
        /*币种*/
        $dictionary_currency_list = $dictionary_service->getDictionaryArray('currency');
        //单位
        $course_period_unit_list = $model->getCoursePeriodUnits();

        if (!empty($teacher_id)) {
            $searchTearchManager = new TeacherManageService();
            $teacherResult = $searchTearchManager->courseSearchTeacher(null, $teacher_id, false);
        }
        if (!empty($certification_id)) {
            $certificationM = new LnCertification();
            $certificationResult = $certificationM->findOne($certification_id);
        }
        /*js api更新*/
        $tags = array();
        if (!empty($tag)) {
            foreach ($tag as $itmes) {
                $tags[] = array('kid' => null, 'title' => urlencode($itmes));
            }
            $tags = array('results' => $tags);
            $tag = urldecode(json_encode($tags));
        } else {
            $tag = "";
        }

        $teach_arr = array();
        if (!empty($teacherResult)) {
            foreach ($teacherResult as $teach_item) {
                $teach_arr[] = array('kid' => $teach_item['kid'], 'title' => urlencode($teach_item['teacher_name']) . '(' . $teach_item['email'] . ')');
            }
            $teach_arr = array('results' => $teach_arr);
            $teacherResult = urldecode(json_encode($teach_arr));
        } else {
            $teacherResult = "";
        }

        if (!empty($certificationResult)) {
            $certificationTemp = array('results' => array(array('kid' => $certificationResult->kid, 'title' => urlencode($certificationResult->certification_name))));
            $certificationResult = urldecode(json_encode($certificationTemp));
        } else {
            $certificationResult = "";
        }

        $course_time = isset($post_data['course_time']) ? $post_data['course_time'] : time();
        setcookie('course_time_' . $course_time, $course_time, time() + 7200, '/');
        if ($id) {
            setcookie('courseId_' . $course_time, $id, time() + 7200, '/');
        }
        return $this->render('aftersaveedit', [
            'model' => $model,
            'courseCategories' => $courseCategories,
            'domain' => $domain,
            'domain_id' => $domain_id,
            'tree_node_id' => $tree_node_id,
            'dictionary_level_list' => $dictionary_level_list,
            'dictionary_lang_list' => $dictionary_lang_list,
            'dictionary_currency_list' => $dictionary_currency_list,
            'resource' => $post_data['resource'],
            'tag' => $tag,
            'teacher' => $teacherResult,
            'certification' => $certificationResult,
            'course_time' => $course_time,
            'course_period_unit_list' => $course_period_unit_list
        ]);
    }

    public function actionAftersaveedit()
    {

        $this->clearCourseCookie();
        $kid = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
        if (!empty($_POST)) {
            $post_data = $_POST;
            $course = $post_data['LnCourse'];
            $result = LnCourse::findOne($kid);
            $result->course_name = $course['course_name'];
            $result->course_desc_nohtml = $course['course_desc_nohtml'];
            $result->course_desc = $course['course_desc'];
            $result->theme_url = $course['theme_url'];
            $result->course_level = $course['course_level'];
            $result->course_period = $course['course_period'];
            $result->max_attempt = $course['max_attempt'];
            $result->course_period_unit = $course['course_period_unit'];
            $result->default_credit = $course['default_credit'];
            $result->course_language = $course['course_language'];
            $result->course_price = $course['course_price'];
            $result->currency = $course['currency'];
            $result->category_id = $course['category_id'];
            $result->is_display_pc = isset($course['is_display_pc']) ? 1 : 0;
            $result->is_display_mobile = isset($course['is_display_mobile']) ? 1 : 0;
            $result->start_time = strtotime($course['start_time']);
            if (empty($course['end_time'])) {
                $result->end_time = null;
            } else {
                $result->end_time = strtotime($course['end_time']) + 86399;
            }
            $result->start_time = $result->start_time ? $result->start_time : strtotime(date('Y-m-d'));
            if ($result->theme_url == "") {
                $result->theme_url = null;
            }
            if (!empty($result->theme_url)) {
                if (stripos($result->theme_url, '/temp/')) {
                    $TFileModelHelper = new TFileModelHelper();
                    $res = $TFileModelHelper->moveFile($result->theme_url, '/upload/coursetheme');/*复制一份*/
                    $TFileModelHelper->image_resize($res, 378, 225, 'crop');
                    $result->theme_url = $res;
                }
            }

            $tree_node_id = $result->category_id;
            $courseService = new CourseCategoryService();
            $course_category_id = $courseService->getCourseCategoryIdByTreeNodeId($tree_node_id);
            $result->category_id = $course_category_id;
            /*添加标签 */
            $tag = Yii::$app->request->post('tag');
            if (!empty($tag)) {
                $tagSerice = new TagService();
                $companyId = Yii::$app->user->identity->company_id;
                $tagSerice->addTag($tag, $result->kid, $companyId, 'course', $result->end_time);
            }
            /*添加证书*/
            $certification_id = Yii::$app->request->post('certification_id');
            $certificationModel = new LnCourseCertification();
            $certificationModel->stopRelation($result->kid);
            if (!empty($certification_id)) {
                $certificationModel->addRelation($result, $certification_id);
            }
            /*添加讲师*/
            if ($result->course_type == LnCourse::COURSE_TYPE_ONLINE) {/*在线*/
                $teacher_id = Yii::$app->request->post('teacher_id');
                $teacherModel = new LnCourseTeacher();
                $teacherModel->addRelation($result, $teacher_id);
            } else {/*面授*/
                $teacherModel = new LnCourseTeacher();
                $teacher_id = $post_data['teacher_id'];
                $teacherModel->addRelation($result, $teacher_id);
                foreach ($teacher_id as $vo) {
                    $findTeacher = LnTeacher::findOne($vo);
                    $lnowner = new LnCourseOwner();
                    $lnowner->addRelationship($result, $findTeacher->user_id, '1');/*面授都是讲师*/
                }
            }
            if ($result->course_type == LnCourse::COURSE_TYPE_FACETOFACE) {
                $result->enroll_start_time = strtotime($course['enroll_start_time']);
                $result->enroll_end_time = strtotime($course['enroll_end_time']) + 86399;
            }
            $result->needReturnKey = true;
            if ($result->save()) {

                $domain = Yii::$app->request->post('domain_id');
                $resourceDomainService = new ResourceDomainService();
                $resourceDomainService->updateStatus($result->kid, LnResourceDomain::RESOURCE_TYPE_COURSE, LnResourceDomain::STATUS_FLAG_STOP);

                $companyId = Yii::$app->user->identity->company_id;
                /*资源所属域*/
                foreach ($domain as $j) {
                    $findOne = LnResourceDomain::findOne(['resource_id' => $result->kid, 'domain_id' => $j]);
                    $resourceDomainA = !empty($findOne->kid) ? $findOne : new LnResourceDomain();
                    $resourceDomainA->resource_id = $result->kid;
                    $resourceDomainA->start_at = $result->start_time;
                    $resourceDomainA->end_at = $result->end_time;
                    $resourceDomainA->company_id = $companyId;
                    $resourceDomainA->resource_type = LnResourceDomain::RESOURCE_TYPE_COURSE;/*资源类型课程*/
                    $resourceDomainA->domain_id = $j;
                    $resourceDomainA->status = LnResourceDomain::STATUS_FLAG_NORMAL;/*资源状态*/
                    $resourceDomainA->is_deleted = LnResourceDomain::DELETE_FLAG_NO;
                    $resourceDomainA->save();
                }
                if ($result->course_type == LnCourse::COURSE_TYPE_FACETOFACE) {
                    return $this->render('manage-face');
                } else {
                    return $this->render('manage');
                }
            } else {
                return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'exam_network_err')];
            }
        }
    }

    public function actionAftereditFaceView()
    {
        $kid = isset($_GET['id']) ? $_GET['id'] : '';
        $model = $kid ? LnCourse::findOne($kid) : new LnCourse();
        $courseService = new CourseService();
        $training_address = $courseService->getTrainingAddressByName($model->training_address, $model->training_address_id);
        $coursewareService = new CoursewareService();
        $vendor = $coursewareService->getVendorByName($model->vendor, $model->vendor_id);
        return $this->render('aftersaveedit-face', [
            'model' => $model,
            'training_address' => $training_address,
            'vendor' => $vendor,
        ]);
    }

    public function actionAftersaveeditFace()
    {
        $kid = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
        if (!empty($_POST)) {
            $post_data = $_POST;
            $course = $post_data['course'];
            $result = LnCourse::findOne($kid);

            $result->start_time = strtotime($course['start_time']);
            if (empty($course['end_time'])) {
                $result->end_time = null;
            } else {
                $result->end_time = strtotime($course['end_time']) + 86399;
            }

            if ($result->course_type == LnCourse::COURSE_TYPE_FACETOFACE) {
                $result->enroll_start_time = strtotime($course['enroll_start_time']);
                $result->enroll_end_time = strtotime($course['enroll_end_time']) + 86399;
            }
            $result->training_address = !empty($course['training_address']) ? $course['training_address'] : null;
            $result->training_address_id = !empty($course['training_address_id']) ? $course['training_address_id'] : null;
            $result->vendor = !empty($course['vendor']) ? $course['vendor'] : null;
            $result->vendor_id = !empty($course['vendor_id']) ? $course['vendor_id'] : null;

            if ($result->save()) {

                if ($result->course_type == LnCourse::COURSE_TYPE_FACETOFACE) {
                    return $this->render('manage-face');
                } else {
                    return $this->render('manage');
                }
            } else {
                return false;
            }
        }
    }

    /**
     * 课程模块编辑及预览、组件添加
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionContentFace($id = null)
    {
        if (!Yii::$app->request->isPost) {/*不是POST数据直接跳转*/
            $this->redirect(Yii::$app->urlManager->createUrl(['resource/course/manage-face']));;
        }
        $post_data = Yii::$app->request->post();
        $model = $id ? LnCourse::findOne($id) : new LnCourse();
        $model->load($post_data);
        $domain_id = Yii::$app->request->post('domain_id');
        $audience_id = Yii::$app->request->post('audience_id');
        $tag = Yii::$app->request->post('tag');
        $course = Yii::$app->request->post('course');
        if ($course) {
            foreach ($course as $i => $k) {
                $course[$i]['enroll_start_time'] = !empty($k['enroll_start_time']) ? strtotime($k['enroll_start_time']) : '';
                $course[$i]['enroll_end_time'] = !empty($k['enroll_end_time']) ? strtotime($k['enroll_end_time']) + 86399 : '';
                $course[$i]['open_start_time'] = !empty($k['open_start_time']) ? strtotime($k['open_start_time']) : '';
                $course[$i]['open_end_time'] = !empty($k['open_end_time']) ? strtotime($k['open_end_time']) + 86399 : '';
            }
        }
        $certification_id = Yii::$app->request->post('certification_id');
        $model->course_name = TStringHelper::clean_xss($model->course_name);
        $service = new ResourceService();
        $modules = null;
        $resource_temp = $post_data['resource'];
        $component_config = '';
        $component_rescore = '';
        if (!empty($id) && empty($resource_temp)) {
            $modules = $service->getCourseMods($id, true, $domain_id, LnCourse::COURSE_TYPE_FACETOFACE);
        } else if (!empty($resource_temp)) {
            $modules = $service->GetPostResource($resource_temp, $domain_id, LnCourse::COURSE_TYPE_FACETOFACE);
            foreach ($resource_temp as $kiss => $val) {
                $mod_num = $val['mod_num'];
                if (!empty($val['config'])) {
                    foreach ($val['config'] as $sk => $ssval) {
                        $json_config = json_decode($ssval, true);
                        $component_config .= '<input type="hidden" value="' . htmlspecialchars($ssval) . '" id="con_' . $mod_num . '_' . $json_config['kid'] . '" name="resource[' . $mod_num . '][config][' . $json_config['kid'] . ']" data-name="config" data-title="' . htmlspecialchars($json_config['title']) . '" data-isfinish="' . $json_config['isfinish'] . '" data-score="' . $json_config['score'] . '" data-componet="' . $json_config['componet'] . '" data-iscore="' . $json_config['isscore'] . '" data-kid="' . $json_config['kid'] . '">';
                    }
                }
                if (!empty($val['rescore'])) {
                    foreach ($val['rescore'] as $kk => $sval) {
                        $json_decode = json_decode($sval, true);
                        $component_rescore .= '<input id="socl_' . $json_decode['modnum'] . '_' . $json_decode['id'] . '" data-score="' . $json_decode['score'] . '" data-id="' . $json_decode['id'] . '" data-modnum="' . $json_decode['modnum'] . '" name="resource[' . $json_decode['modnum'] . '][rescore][' . $json_decode['id'] . ']" type="hidden" value="' . htmlspecialchars($sval) . '">';
                    }
                }
            }
        }
        $ModForm = new LnCourseMods();
        $componentService = new ComponentService();
        $is_setting_component = $componentService->getRecordScore();
        return $this->render('content-face', [
            'model' => $model,
            'ModForm' => $ModForm,
            'modules' => $modules,
            'domain_id' => $domain_id,
            'audience_id' => $audience_id,
            'tag' => $tag ? $tag : '',
            'course' => $course ? json_encode($course) : "",
            'certification_id' => $certification_id ? $certification_id : '',
            'course_time' => $post_data['course_time'],
            'component_config' => $component_config,
            'component_rescore' => $component_rescore,
            'is_setting_component' => $is_setting_component,
        ]);
    }

    /**
     * 暂存课程模块、模块与组件关联
     * @return array
     */
    public function actionTempMod($id = "", $preview = 0)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) {
            $this->redirect(Yii::$app->urlManager->createUrl(['resource/course/edit', 'id' => $id]));
        }
        $model_data = Yii::$app->request->post();
        $course_time = $_COOKIE['course_time_' . $model_data['course_time']];
        if (isset($_COOKIE['courseId_' . $course_time]) && $model_data['course_time'] == $course_time) {
            $id = isset($_COOKIE['courseId_' . $course_time]) ? $_COOKIE['courseId_' . $course_time] : $id;
        }
        $model = !empty($id) ? LnCourse::findOne($id) : new LnCourse();
        $model->load($model_data);
        if (empty($model->company_id)) {
            $companyId = Yii::$app->user->identity->company_id;
            $model->company_id = $companyId;
        } else {
            $companyId = $model->company_id;
        }
        $model->start_time = $model->start_time ? strtotime($model->start_time) : time();
        $model->end_time = $model->end_time ? strtotime($model->end_time) + 86399 : null;
        $model->enroll_end_time = $model->enroll_end_time ? $model->enroll_end_time : null;
        $model->open_end_time = $model->open_end_time ? $model->open_end_time : null;
        $courseService = new CourseService();
        $model->course_version = $courseService->getCourseVersion($model->kid);
        $model->course_code = $model->setCourseCode($model->kid);
        $model->short_code = $courseService->GenerateShortCode();

        if ($model->theme_url == "") {
            $model->theme_url = null;
        } else {
            /*临时文件则移动文件到coursetheme目录下并生成课程库封面图*/
            if (stripos($model->theme_url, '/temp/')) {
                $TFileModelHelper = new TFileModelHelper();
                $res = $TFileModelHelper->moveFile($model->theme_url, '/upload/coursetheme');/*复制一份*/
                $TFileModelHelper->image_resize($res, 378, 225, 'crop');
                $model->theme_url = $res;
            }
        }
        if ($model->max_attempt == "") {
            $model->max_attempt = 0;
        }
        if (!empty($model->course_desc_nohtml)) {
            $model->course_desc_nohtml = strip_tags(Html::decode($model->course_desc_nohtml));
        }

        if ($id && $preview) {
            unset($model->status);/*预览不改变当前课程的状态*/
        }
        if ($model->status == LnCourse::STATUS_FLAG_NORMAL) {/*发布状态更新发布时间*/
            $model->release_at = time();
        }
        $model->vendor_id = empty($model->vendor_id) ? null : $model->vendor_id;
        $model->training_address_id = empty($model->training_address_id) ? null : $model->training_address_id;
        /*判断是否全部 调查或考试*/
        if (!empty($model_data['resource'])) {
            $is_survey_only = LnCourse::IS_SURVEY_ONLY_NO;
            $is_exam_only = LnCourse::IS_EXAM_ONLY_NO;
            $survey = $exam = 0;
            $resourceCount = 0;
            foreach ($model_data['resource'] as $items) {
                if (!empty($items['coursewares'])) {
                    foreach ($items['coursewares'] as $k => $val) {
                        $resourceCount += count($items['coursewares'][$k]);
                    }
                }
                if (!empty($items['activity'])) {
                    $exam = !empty($items['activity']['examination']) ? $exam + count($items['activity']['examination']) : $exam;
                    $survey = !empty($items['activity']['investigation']) ? $survey + count($items['activity']['investigation']) : $survey;
                    foreach ($items['activity'] as $k => $val) {
                        $resourceCount += count($items['activity'][$k]);
                    }
                }
            }
            if ($resourceCount > 0 && $resourceCount == $survey) {
                $is_survey_only = LnCourse::IS_SURVEY_ONLY_YES;
            }
            if ($resourceCount > 0 && $resourceCount == $exam) {
                $is_exam_only = LnCourse::IS_EXAM_ONLY_YES;
            }
            $model->is_survey_only = $is_survey_only;
            $model->is_exam_only = $is_exam_only;
        }
        $model->needReturnKey = true;
        if (empty($model->course_name)) {
            return ['result' => 'fail', 'errmsg' => Yii::t('frontend', '{value}_not_null', ['value' => Yii::t('common', 'course_name')])];
        }
        if ($model->save() === false) {
            $errmsg = $model->getErrors();
            if (empty($errmsg)) {
                $errmsg = Yii::t('frontend', 'save_failed');
            }
            return ['result' => 'fail', 'errmsg' => $errmsg];
        }

        $domain_id = Yii::$app->request->post('domain_id');
        $domain = explode(',', $domain_id);
        $resourceDomainService = new ResourceDomainService();
        $resourceDomainService->updateStatus($model->kid, LnResourceDomain::RESOURCE_TYPE_COURSE, LnResourceDomain::STATUS_FLAG_STOP);
        /*资源所属域*/
        foreach ($domain as $j) {
            $findOne = LnResourceDomain::findOne(['resource_id' => $model->kid, 'domain_id' => $j]);
            $resourceDomainA = !empty($findOne->kid) ? $findOne : new LnResourceDomain();
            $resourceDomainA->resource_id = $model->kid;
            $resourceDomainA->start_at = $model->start_time;
            $resourceDomainA->end_at = $model->end_time;
            $resourceDomainA->resource_type = LnResourceDomain::RESOURCE_TYPE_COURSE;/*资源类型课程*/
            $resourceDomainA->domain_id = $j;
            $resourceDomainA->company_id = $companyId;
            $resourceDomainA->status = LnResourceDomain::STATUS_FLAG_NORMAL;/*资源状态*/
            $resourceDomainA->is_deleted = LnResourceDomain::DELETE_FLAG_NO;
            $resourceDomainA->save();
        }
        /*保存受众资源*/
        $audienceResourceService = new ResourceAudienceService();
        $audienceResourceService->updateResourceAudience($model->kid, LnResourceAudience::STATUS_FLAG_STOP, LnResourceAudience::RESOURCE_TYPE_COURSE);
        $audience_id = Yii::$app->request->post('audience_id');
        if (!empty($audience_id)) {
            $audience = explode(',', $audience_id);
            foreach ($audience as $m) {
                $audienceData = $audienceResourceService->getDataByAudienceId($m, $model->kid);
                if (empty($audienceData)) {
                    $audienceData = new LnResourceAudience();
                }
                $audienceData->resource_id = $model->kid;
                $audienceData->audience_id = $m;
                $audienceData->company_id = $model->company_id;
                $audienceData->resource_type = LnResourceAudience::RESOURCE_TYPE_COURSE;
                $audienceData->status = LnResourceAudience::STATUS_FLAG_NORMAL;
                $audienceData->start_at = $model->start_time;
                $audienceData->end_at = $model->end_time;
                $audienceResourceService->saveData($audienceData);
            }
        }

        $resourceService = new ResourceService();
        $result = $resourceService->SetCourseResource($model_data['resource'], $model->kid);
        /*课程所有者,在线是需要储存完全所有者,面授需要储存讲师与完全所有者*/
        $user_id = Yii::$app->user->getId();
        $lnowner = new LnCourseOwner();
        $lnowner->addRelationship($model, $user_id, LnCourseOwner::OWNER_TYPE_ALL);
        /*添加标签 */
        $tag = Yii::$app->request->post('tag');
        if (!empty($tag)) {
            $tagSerice = new TagService();
            $tagSerice->addTag($tag, $model->kid, $companyId, 'course', $model->end_time);
        }
        /*添加证书*/
        $certification_id = Yii::$app->request->post('certification_id');
        $certificationModel = new LnCourseCertification();
        $certificationModel->stopRelation($model->kid);
        if (!empty($certification_id)) {
            $certificationModel->addRelation($model, $certification_id);
        }
        /*添加讲师*/
        if ($model->course_type == LnCourse::COURSE_TYPE_ONLINE) {/*在线*/
            $teacher_id = Yii::$app->request->post('teacher_id');
            if (!empty($teacher_id)) {
                $teacherModel = new LnCourseTeacher();
                $teacherModel->addRelation($model, $teacher_id);
            }
        } else {/*面授*/
            $course = Yii::$app->request->post('course_temp');
            if (!empty($course)) $course = json_decode($course, true);
            if (is_array($course)) {
                if (count($course) > 1) $model->course_name .= Yii::t('frontend', 'the_first_step');
                $model->enroll_start_time = $course[0]['enroll_start_time'];
                $model->enroll_end_time = $course[0]['enroll_end_time'];
                $model->open_start_time = $course[0]['open_start_time'];
                $model->open_end_time = $course[0]['open_end_time'];
                $model->limit_number = $course[0]['limit_number'];
                $model->is_allow_over = isset($course[0]['is_allow_over']) ? 1 : 0;
                $model->allow_over_number = $course[0]['allow_over_number'];
                $model->training_address = empty($course[0]['training_address']) ? null : TStringHelper::clean_xss($course[0]['training_address']);
                $model->training_address_id = empty($course[0]['training_address_id']) ? null : $course[0]['training_address_id'];
                $model->vendor = empty($course[0]['vendor']) ? null : TStringHelper::clean_xss($course[0]['vendor']);
                $model->vendor_id = empty($course[0]['vendor_id']) ? null : $course[0]['vendor_id'];
                $model->update();
                $teacherModel = new LnCourseTeacher();
                $teacher_id = $course[0]['teacher_id'];
                $teacherModel->addRelation($model, $teacher_id);
                foreach ($teacher_id as $vo) {
                    $findTeacher = LnTeacher::findOne($vo);
                    $lnowner = new LnCourseOwner();
                    $lnowner->addRelationship($model, $findTeacher->user_id, LnCourseOwner::OWNER_TYPE_TEACHER);/*面授都是讲师*/
                }
                if (count($course) > 1) {
                    unset($course[0]);
                    $tempCourse = isset($_COOKIE['course_more_' . $model->kid]) ? $_COOKIE['course_more_' . $model->kid] : null;
                    if (!empty($tempCourse)) {
                        //防止刷新造成数据重复
                        $tempCourse = explode(':::', $tempCourse);
                        /*$courseService->deleteTempCopy($tempCourse);*/
                        setcookie('course_more_' . $model->kid, '', -1, '/');
                    }
                    $kids = $courseService->courseCopy($model, $course, false, $tempCourse);/*复制课程*/
                    if ($model->status == LnCourse::STATUS_FLAG_TEMP && is_array($kids)) {
                        setcookie('course_more_' . $model->kid, join(':::', $kids), time() + 7200, '/');
                    }
                }
            }
        }
        //暂存
        if ($model_data['LnCourse']['status'] == LnCourse::STATUS_FLAG_TEMP) {
            setcookie('courseId_' . $course_time, $model->kid, time() + 7200, '/');
        } else {
            setcookie('courseId_' . $course_time, '', -1, '/');
            setcookie('course_time_' . $model_data['course_time'], '', -1, '/');
        }
        return ['result' => 'success', 'id' => $model->kid, 'resourceCount' => $resourceCount, 'exam' => $exam, 'survey' => $survey];
    }

    /*发布课程*/
    public function actionPublish($id, $sync = "")
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (empty($id)) return ['result' => 'failure'];
        $model = LnCourse::findOne($id);
        $model->status = LnCourse::STATUS_FLAG_NORMAL;
        $model->release_at = time();
        if (false !== $model->update()) {
            /*同步发布分期课程*/
            if ($sync == 'all') {
                $attribute = ['status' => LnCourse::STATUS_FLAG_NORMAL, 'release_at' => time()];
                $condition = "origin_course_id=:origin_course_id and status='" . LnCourse::STATUS_FLAG_TEMP . "'";
                $params = [':origin_course_id' => $id];
                LnCourse::updateAll($attribute, $condition, $params);
                LnCourse::removeFromCacheByKid($model->kid);/*清除缓存*/
            }
            return ['result' => 'success'];
        } else {
            return ['result' => 'failure'];
        }
    }

    /**
     * 检测课程名称是否重复
     * @param string $id
     * @return array
     */
    public function actionCheckCourseName($id = "")
    {
        $this->layout = false;
        Yii::$app->response->format = Response::FORMAT_JSON;
        $course_name = Yii::$app->request->get('course_name');
        if (empty($course_name)) return ['result' => 'success'];
        $companyId = Yii::$app->request->get('companyId');
        $server = new CourseService();
        $count = $server->getSimilarCourse($companyId, $course_name, $id);
        if ($count > 0) {
            return ['result' => 'failure'];
        } else {
            return ['result' => 'success'];
        }
    }


    /**
     * 获取学习状态
     * @param $model
     * @param $isReg
     * @param $isCourseComplete
     * @param $isCourseRetake
     * @param $isCourseDoing
     * @param $enrollInfo
     * @return string 学习按钮
     */
    private function getLearnStatus($model, $isReg, $modResId, $isCourseComplete, $isCourseRetake, $isCourseDoing, $enrollInfo, $currentAttempt, $uid)
    {
        $button = "";

        $currentTime = time();
        //已经注册成功的课，不做如下控制
        if (!empty($startTime) && $startTime > $currentTime) {
            $button = '<a href="#" class="btn btn-default btn-sm pull-right" id="regBtn">' . Yii::t('frontend', 'no_shelf') . '</a>';
        }

        if (!empty($endTime) && $endTime < $currentTime) {
            //发布结束时间小于现在的课程不能学习
            $button = '<a href="#" class="btn btn-default btn-sm pull-right" id="regBtn">' . Yii::t('frontend', 'under_shelf') . '</a>';
        }
        $service = new CourseService();
        $regInfo = $service->getUserRegInfo($uid, $model->kid);
        //如果是在线课程
        if ($model->course_type == LnCourse::COURSE_TYPE_ONLINE) {
            if (!$isReg) {
                //如果未注册，则显示注册
                if (empty($button))
                    $button = '<a href="javascript:RegisterCourse(\'' . $model->kid . '\');" class="btn btn-success btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">' . Yii::t('common', 'signup') . '</a>';
            } else {
                if ($isCourseComplete) {
                    $maxAttempt = $model->max_attempt;
                    if ($maxAttempt == 0 || intval($currentAttempt) < $maxAttempt) {
                        //如果已完成，显示重新学习
                        if (empty($button))
                            $button = '<a href="javascript:RestartConfirm(\'' . $model->kid . '\');" class="btn btn-success btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">' . Yii::t('frontend', 'reset_study') . '</a>';
                    } else {
                        if (empty($button))
                            $button = '<a href="#" class="btn btn-default btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">' . Yii::t('common', 'status_2') . '</a>';
                    }
                } else {
                    if ($model->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_APPLING) {
                        $button = '<a href="#" class="btn btn-default btn-sm pull-right" style="position: absolute; right: 0px;">' . Yii::t('frontend', 'approvaling') . '</a>';
                    } else if ($model->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_REJECTED) {
                        $button = '<a href="#" class="btn btn-default btn-sm pull-right" style="position: absolute; right: 0px;">' . Yii::t('frontend', 'approval_no_pass') . '</a>';
                    } else if ($model->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_CANCELED) {
                        $button = '<a href="#" class="btn btn-default btn-sm pull-right" style="position: absolute; right: 0px;">' . Yii::t('frontend', 'invalid') . '</a>';
                    } else {
                        if ($isCourseRetake) {
                            //如果是重学状态需要多显示一个放弃重学
                            if (empty($button))
                                $button = '<a href="javascript:GiveupRestartConfirm(\'' . $model->kid . '\');" class="btn btn-success btn-sm pull-right" id="regBtn" style="position: absolute; right: 90px;">' . Yii::t('frontend', 'give_up_learning') . '</a>';
                        }
                        if (!empty($modResId)) {
                            //如果有可学内容
                            if (!$isCourseDoing) {
                                //如果未开始，显示开始学习
                                $button .= '<a href="' . Yii::$app->urlManager->createUrl(['resource/course/play', 'modResId' => $modResId]) . '" class="btn btn-success btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">' . Yii::t('frontend', 'start_learning') . '</a>';
                            } else {
                                //如果已开始，显示继续学习
                                $button .= '<a href="' . Yii::$app->urlManager->createUrl(['resource/course/play', 'modResId' => $modResId]) . '" class="btn btn-success btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">' . Yii::t('frontend', 'continue_learning') . '</a>';
                            }
                        } else {
                            //显示不能点的开始学习
                            if (empty($button))
                                $button .= '<a href="#" class="btn btn-default btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">' . Yii::t('frontend', 'start_learning') . '</a>';
                        }
                    }
                }
            }
        } else {
            $time = time();
            if (empty($enrollInfo)) {
                //如果未报名
                if ($model->enroll_start_time != null && $model->enroll_start_time > $time) {
                    //如果报名开始时间不为空，且报名开始时间还未到，则显示报名未开始，
                    if (empty($button))
                        $button = '<a href="#" class="btn btn-default btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">' . Yii::t('common', 'complete_eroll_status_0') . '</a>';
                } else if ($model->enroll_end_time != null && $model->enroll_end_time < $time) {
                    //否则如果报名结束时间不为空，且报名结束时间已过，则显示报名已结束，
                    if (empty($button))
                        $button = '<a href="#" class="btn btn-default btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">' . Yii::t('common', 'status_enroll_2') . '</a>';
                } else if ($model->open_status == LnCourse::COURSE_END) {
                    if (empty($button))
                        $button = '<a href="#" class="btn btn-default btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">' . Yii::t('common', 'status_2') . '</a>';
                } else {
                    //否则显示报名
                    if (empty($button))
                        $button = '<a href="javascript:EnrollCourse();" class="btn btn-success btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">' . Yii::t('frontend', 'enroll') . '</a>';
                }
            } else if ($enrollInfo->enroll_type == LnCourseEnroll::ENROLL_TYPE_DISALLOW) {
                if (empty($button))
                    $button = '<a href="#" class="btn btn-default btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">' . Yii::t('frontend', 'enroll_failed') . '</a>';
            } else if ($enrollInfo->enroll_type == LnCourseEnroll::ENROLL_TYPE_REG || $enrollInfo->enroll_type == LnCourseEnroll::ENROLL_TYPE_ALTERNATE) {
                if ($model->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_APPLING) {
                    $button = '<a href="#" class="btn btn-default btn-sm pull-right" style="position: absolute; right: 0px;">' . Yii::t('frontend', 'approvaling') . '</a>';
                } else if ($model->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_REJECTED) {
                    $button = '<a href="#" class="btn btn-default btn-sm pull-right" style="position: absolute; right: 0px;">' . Yii::t('frontend', 'approval_no_pass') . '</a>';
                } else if ($model->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_CANCELED) {
                    $button = '<a href="#" class="btn btn-default btn-sm pull-right" style="position: absolute; right: 0px;">' . Yii::t('frontend', 'invalid') . '</a>';
                }
                if (empty($button))
                    $button = '<a href="#" class="btn btn-default btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">' . Yii::t('frontend', 'enroll_allowing') . '</a>';
            } else {
                if ($model->open_status == LnCourse::COURSE_NOT_START) {
                    // 如果还未开课，显示待开课
                    if (empty($button))
                        $button = '<a href="#" class="btn btn-default btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">' . Yii::t('frontend', 'enroll_success') . '</a>';
                } else if ($model->open_status == LnCourse::COURSE_END) {
                    //否则如果课程已经结束，显示已结束
                    if (empty($button))
                        $button = '<a href="#" class="btn btn-default btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">' . Yii::t('common', 'status_2') . '</a>';
                } else {
                    /*if ($model->open_end_time != null && $model->open_end_time <= $time) {
                        if (empty($button))
                            $button = '<a href="#" class="btn btn-default btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">已结束</a>';
                    } else {*/
                    if (!$isReg) {
                        //如果未注册，则显示注册（这是异常情况，理论上在报名成功时，就已经自动注册)
                        if (empty($button))
                            $button = '<a href="javascript:RegisterCourse(\'' . $model->kid . '\');" class="btn btn-success btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">' . Yii::t('frontend', 'enroll') . '</a>';
                    } else {
                        if ($isCourseComplete) {
                            //如果已完成，显示已完成
                            if (empty($button))
                                $button = '<a href="#" class="btn btn-default btn-sm pull-right" id="regBtn" style="position: absolute; right: 0px;">' . Yii::t('common', 'status_2') . '</a>';
                        } else {
                            if (!empty($modResId)) {
                                //如果有可学内容
                                if (!$isCourseDoing) {
                                    //如果未开始，显示开始学习
                                    if (empty($button))
                                        $button = '<a href="' . Yii::$app->urlManager->createUrl(['resource/course/play', 'modResId' => $modResId]) . '" class="btn btn-success btn-sm pull-right" id="regBtn" style="position: absolute; right: 130px;">' . Yii::t('frontend', 'start_learning') . '</a>';
                                } else {
                                    //如果已开始，显示继续学习
                                    if (empty($button))
                                        $button = '<a href="' . Yii::$app->urlManager->createUrl(['resource/course/play', 'modResId' => $modResId]) . '" class="btn btn-success btn-sm pull-right" id="regBtn" style="position: absolute; right: 130px;">' . Yii::t('frontend', 'continue_learning') . '</a>';
                                }
                            } else {
                                //显示不能点的开始学习
                                if (empty($button))
                                    $button .= '<a href="#" class="btn btn-default btn-sm pull-right" id="regBtn" style="position: absolute; right: 130px;">' . Yii::t('frontend', 'start_learning') . '</a>';
                            }
                        }
                    }
                    /* }*/
                }
            }
        }

        return $button;
    }

    /**
     * Deletes an existing LnCourse model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            if (Yii::$app->request->isAjax) {
                $model = $this->findModel($id);
                if (isset($model) && $model != null) {
                    $key = $model->kid;
                    $model->delete();

                    $service = new ResourceDomainService();
                    $list = LnResourceDomain::findAll(['resource_id' => $id, 'resource_type' => LnResourceDomain::RESOURCE_TYPE_COURSE], false);
                    foreach ($list as $val) {
                        $service->StopRelationship($val);
                    }
                    $courseService = new CourseService();
                    $courseService->DeleteModResRelationship($id);
                    /*停用标签关系*/
                    $tagService = new TagService();
                    $tagService->stopCourseRelationShip($model->kid);
//                    $tagList = $tagService->getTagValue($model->kid);
//                    if (!empty($tagList)){
//                        foreach($tagList as $item) {
//                            $tagService->StopRelationship($item);
//                        }
//                    }

                    return ['result' => 'success'];
                } else {
                    return ['result' => 'failure'];
                }
            } else {
                return ['result' => 'failure'];
            }
        } catch (Exception $ex) {
            return ['result' => 'failure'];
        }
    }

    /**
     * Finds the LnCourse model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return LnCourse the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = LnCourse::findOne($id)) !== null) {
            return $model;
        } else {
            //throw new NotFoundHttpException(Yii::t('common','data_not_exist'));
            header('Location: ' . Url::toRoute(['/resource/course/index']));
        }
    }

    /**
     * 课程注册
     * @param $id
     * @return array
     */
    public function actionReg($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;
        $courseService = new CourseService();
        $result = $courseService->regCourse($userId, $id, LnCourseReg::REG_TYPE_SELF);
        if (!$result) {
            return ['result' => $result];
        }
        /*在线注册成功后的操作*/
        $res = $courseService->regCourseSuccess($id, $userId, $companyId);
        return $res;
    }

    /*取消报名，减少注册*/
    public function actionDelEnroll()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $enrollId = Yii::$app->request->post('id');

        if (!$enrollId) {
            exit('Id is null.');
        }

        $courseService = new CourseService();
        $result = $courseService->delEnrollCourse($enrollId);
        if (!$result) {
            return ['result' => $result];
        } else {
            /*减少注册量*/
            LnCourse::subFieldNumber($enrollId, 'register_number');

            return ['result' => 'success'];
        }
    }

    /*重学课程*/
    public function actionRestartCourse($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = Yii::$app->user->getId();
        $courseService = new CourseService();
        $courseCompleteService = new CourseCompleteService();
        $courseRegId = $courseService->getUserRegInfo($userId, $id)->kid;
        $courseCompleteService->resetCourseCompleteInfo($courseRegId, $id);
        // 清空session中scorm缓存
        $scormService = new ScormScoesTrackService();
        $scormService->cleanSessionList();

        return ['result' => 'success'];
    }


    /*放弃重学课程*/
    public function actionGiveupRestartCourse($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = Yii::$app->user->getId();
        $courseService = new CourseService();
        $courseCompleteService = new CourseCompleteService();
        $courseRegId = $courseService->getUserRegInfo($userId, $id)->kid;
        $courseCompleteService->giveupResetCourseCompleteInfo($courseRegId);

        return ['result' => 'success'];
    }

    /*重学课件*/
    public function actionRestartModres($mode, $modResId, $courseId, $courseCompleteFinalId = null, $courseCompleteProcessId = null, $scoId = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = Yii::$app->user->getId();
        $courseService = new CourseService();
        $courseRegId = $courseService->getUserRegInfo($userId, $courseId)->kid;
        $courseCompleteService = new CourseCompleteService();

        if (empty($courseCompleteFinalId)) {
            $courseCompleteFinalModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);
        } else {
            $courseCompleteFinalModel = LnCourseComplete::findOne($courseCompleteFinalId);
        }

        if (!empty($courseCompleteFinalModel)) {
            if (empty($courseCompleteProcessId)) {
                $courseCompleteProcessModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_PROCESS);
            } else {
                $courseCompleteProcessModel = LnCourseComplete::findOne($courseCompleteProcessId);
            }
            $courseCompleteFinalId = $courseCompleteFinalModel->kid;
            $courseCompleteProcessId = $courseCompleteProcessModel->kid;

            $attempt = $courseCompleteProcessModel->attempt_number;

            $resCompleteService = new ResourceCompleteService();
            $scormScoesTrackService = new ScormScoesTrackService();

//            $scormAICCSessionService = new ScormAICCSessionService();


            if ($mode == "mod") {
                //清空当前课件
                $resCompleteService->resetResCompleteInfo($courseCompleteFinalId, LnResComplete::COMPLETE_TYPE_FINAL, $modResId);
                $resCompleteService->resetResCompleteInfo($courseCompleteProcessId, LnResComplete::COMPLETE_TYPE_PROCESS, $modResId);
                $scormScoesTrackService->resetScoesTrackInfo($courseRegId, $modResId, $attempt);
//                $scormAICCSessionService->ResetScoesSessionInfo($courseRegId, $modResId, $attempt);
            } else {
                //清空当前单元
                $resCompleteFinalModel = $resCompleteService->getLastResCompleteInfo($courseRegId, $modResId, LnResComplete::COMPLETE_TYPE_FINAL, $courseCompleteFinalId);
                if (!empty($resCompleteFinalModel)) {
                    if ($resCompleteFinalModel->complete_status == LnResComplete::COMPLETE_STATUS_DONE) {
                        $resCompleteFinalModel->complete_status = LnResComplete::COMPLETE_STATUS_DOING;
                        $resCompleteFinalModel->save();
                    }
                }

                $resCompleteProcessModel = $resCompleteService->getLastResCompleteInfo($courseRegId, $modResId, LnResComplete::COMPLETE_TYPE_PROCESS, $courseCompleteProcessId);
                if (!empty($resCompleteProcessModel)) {
                    if ($resCompleteProcessModel->complete_status == LnResComplete::COMPLETE_STATUS_DONE) {
                        $resCompleteProcessModel->complete_status = LnResComplete::COMPLETE_STATUS_DOING;
                        $resCompleteProcessModel->save();
                    }
                }

                $scormScoesTrackService->resetScoesTrackInfo($courseRegId, $modResId, $attempt, $scoId);
//                $scormAICCSessionService->ResetScoesSessionInfo($courseRegId, $modResId, $attempt,$scoId);
            }

            return ['result' => 'success'];
        } else {
            $message = Yii::t('frontend', 'alert_warning_course_finish');
            return ['result' => 'other', 'message' => $message];
        }
    }

    /*开始学习*/
    public function actionPlay($modResId, $scoId = null, $courseCompleteFinalId = null, $courseCompleteProcessId = null)
    {
        $this->layout = "frame-bigScreenPage";

        //课程模块及组件
        $modResModel = LnModRes::findOne($modResId);
        $courseId = $modResModel->course_id;

        $userId = Yii::$app->user->getId();

        $courseService = new CourseService();
        $courseRegId = null;

        $isReg = $courseService->isUserRegCourse($userId, $courseId, $courseRegId);

        $courseCompleteService = new CourseCompleteService();

        if ($isReg) {
            if (!empty($courseCompleteFinalId)) {
                $courseCompleteFinalModel = LnCourseComplete::findOne($courseCompleteFinalId);
            } else {
                $courseCompleteProcessModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_PROCESS);
                $courseCompleteFinalModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);

                $courseCompleteProcessId = $courseCompleteProcessModel->kid;
                $courseCompleteFinalId = $courseCompleteFinalModel->kid;
            }
            $attempt = strval($courseCompleteFinalModel->attempt_number);

            if (empty($courseCompleteFinalId)) {
                $courseCompleteFinalModel = $courseCompleteService->getLastCourseCompleteInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);
                $courseCompleteFinalId = $courseCompleteFinalModel->kid;
            }
            if (empty($courseCompleteProcessId)) {
                $courseCompleteProcessModel = $courseCompleteService->getLastCourseCompleteInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_PROCESS);
                $courseCompleteProcessId = $courseCompleteProcessModel->kid;
            }

            $courseCompleteService->checkCourseStatus($courseCompleteFinalModel, $isCourseDoing, $isCourseComplete, $isCourseRetake);
        } else {
            $isCourseComplete = false;
            $isCourseDoing = false;
            $isCourseRetake = false;
            $attempt = "1";
            $courseCompleteProcessId = "";
            $courseCompleteFinalId = "";
        }
//        echo '$courseCompleteFinalId:';
//        var_dump($courseCompleteFinalId);
//        echo '$courseCompleteProcessId:';
//        var_dump($courseCompleteProcessId);
//        echo '$isReg:';
//        var_dump($isReg);
//        echo '$isCourseComplete:';
//        var_dump($isCourseComplete);
//        echo '$isCourseDoing:';
//        var_dump($isCourseDoing);
//        exit();
        // update 20160614 liucheng fix ELRD-2821
        if ((!empty($courseCompleteFinalId) && !empty($courseCompleteProcessId))
            || ($isReg && $isCourseComplete === false)
        ) {
            if (!$isCourseDoing && $isCourseComplete === false) {// update 20160614 liucheng fix ELRD-2821
                /*增加学习量*/
                LnCourse::addFieldNumber($courseId, 'learned_number');

                //添加学习记录（学习课程）
                $recordService = new RecordService();
                $recordService->addByLearningCourse($userId, $courseId);

                /*更新时间轴状态*/
                $timelineService = new TimelineService();
                $timelineService->updateButtonType($userId, $courseId, MsTimeline::OBJECT_TYPE_COURSE, MsTimeline::BUTTON_TYPE_CONTINUE);
            }

            $courseModel = LnCourse::findOne($courseId);
            $maxAttempt = $courseModel->max_attempt;
            if (($maxAttempt != 0 && intval($attempt) > $maxAttempt) || $isCourseComplete) {
                //超过课程最大尝试次数
                $this->redirect(Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $courseId]));
            }
            $courseName = $courseModel->course_name;
            $modType = $courseModel->mod_type;
            $courseType = $courseModel->course_type;
            $componentId = $modResModel->component_id;
            $compoentModel = LnComponent::findOne($componentId);
            $componentCode = $compoentModel->component_code;

            if ($modResModel->res_type == LnModRes::RES_TYPE_COURSEWARE) {
                $courseWareId = $modResModel->courseware_id;
                $model = LnCourseware::findOne($courseWareId);
                $itemName = $model->courseware_name;

                $scormService = new ScormService();
                if ($scormService->isScormComponent($componentCode) && empty($scoId)) {

                    $scorm = $scormService->getScormByCoursewareId($courseWareId);
                    $scormScoesService = new ScormScoesService();
                    $scormScoesTrackService = new ScormScoesTrackService();
                    $scoes = $scormScoesService->getScormScoesByScormId($scorm->kid, null, "sco");

                    $scoId = "";


                    foreach ($scoes as $sco) {
                        $scormScoId = $sco->kid;
//                        $scormScoesTrackService->removeTrackSessionData($courseRegId, $modResId, $scormScoId, $attempt);

                        if (empty($scoId)) {
                            $result = $scormScoesTrackService->checkIsScormScoesCompletedByAttempt($courseRegId, $modResId, $scormScoId, $attempt, $scorm, true);
                            if (!$result) {
                                //没通过的SCO才需要再学习
                                $scoId = $scormScoId;
                            }
                        }
                    }

                    if (empty($scoId)) {
                        $scoId = $scorm->launch_scorm_sco_id;
                    }

                }
            } else {
                $courseActivityId = $modResModel->courseactivity_id;
                $activityModel = LnCourseactivity::findOne($courseActivityId);
                if ($componentCode == "investigation") {
                    $model = LnInvestigation::findOne($activityModel->object_id);
                    $itemName = $model->title;
                } else if ($componentCode == "homework") {
                    $model = LnHomework::findOne($activityModel->object_id);
                    $itemName = $model->title;
                } else if ($componentCode == "examination") {
                    $model = LnExamination::findOne($activityModel->object_id);
                    $itemName = $model->title;
                } else {
                    $model = null;
                    $itemName = null;
                }
            }

            $iframe = Yii::$app->request->get('iframe');
            $resultUserId = Yii::$app->request->get('resultUserId');

            $resourceCompleteService = new ResourceCompleteService();
            $resourceCompleteService->checkResourceStatus($courseCompleteFinalId, $modResId, $learning, $learned);

            return $this->render('play', [
                'modResId' => $modResId,
                'courseName' => $courseName,
                'itemName' => $itemName,
                'courseId' => $courseId,
                'componentCode' => $componentCode,
                'model' => $model,
                'courseRegId' => $courseRegId,
                'scoId' => $scoId,
                'userId' => $userId,
                'attempt' => $attempt,
                'courseCompleteFinalId' => $courseCompleteFinalId,
                'courseCompleteProcessId' => $courseCompleteProcessId,
                'modType' => $modType,
                'courseType' => $courseType,
                'duration' => self::LEARNING_DURATION,
                'iframe' => $iframe,
                'resultUserId' => $resultUserId,
                'currentCoursewareStatus' => $learned
            ]);
        } else {
            return $this->redirect(['view', 'id' => $courseId]);
        }

    }

    /*开始预览*/
    public function actionPlayPreview($modResId, $scoId = null)
    {
        $this->layout = "modalWin";
        //课程模块及组件
        $modResModel = LnModRes::findOne($modResId);
        $courseId = $modResModel->course_id;

        $courseCompleteProcessId = "";
        $courseCompleteFinalId = "";
        $attempt = "1";

        $courseModel = LnCourse::findOne($courseId);
        $courseName = $courseModel->course_name;
        $componentId = $modResModel->component_id;
        $compoentModel = LnComponent::findOne($componentId);
        $componentCode = $compoentModel->component_code;

        if ($modResModel->res_type == LnModRes::RES_TYPE_COURSEWARE) {
            $courseWareId = $modResModel->courseware_id;
            $model = LnCourseware::findOne($courseWareId);

            $scormService = new ScormService();
            if ($scormService->isScormComponent($componentCode) && empty($scoId)) {

                $scorm = $scormService->getScormByCoursewareId($courseWareId);

                $scoId = "";

                if (empty($scoId)) {
                    $scoId = $scorm->launch_scorm_sco_id;
                }

            }
            $itemName = $model->courseware_name;
        } else if ($modResModel->res_type == LnModRes::RES_TYPE_COURSEACTIVITY) {
            $model = new LnCourseware();
            if ($componentCode == 'examination') {
                $itemId = $modResModel->courseactivity_id;
                $courseActivityModel = LnCourseactivity::findOne($itemId);
                $examinationModel = new ExaminationService();
                $examination = $examinationModel->getExaminationByCopyOne($courseActivityModel->object_id);
                $itemName = $examination['title'] . '&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . Yii::t('frontend', 'total_{value}', ['value' => $examination['examination_question_number']]) . '</strong>';
            } else {
                $itemName = null;
                $componentCode .= '-preview';
            }
        }


        return $this->render('play-preview', [
            'modResId' => $modResId,
            'courseName' => $courseName,
            'courseId' => $courseId,
            'componentCode' => $componentCode,
            'model' => $model,
            'scoId' => $scoId,
            'attempt' => $attempt,
            'courseCompleteFinalId' => $courseCompleteFinalId,
            'courseCompleteProcessId' => $courseCompleteProcessId,
            'itemName' => $itemName,
        ]);
    }

    public function actionPlayControl($modResId = null, $courseId = null, $courseRegId = null, $scoId = null, $itemId = null, $attempt = "1",
                                      $courseCompleteFinalId = null, $courseCompleteProcessId = null, $modType = LnCourse::MOD_TYPE_RANDOM, $mode = self::PLAY_MODE_PREVIEW)
    {
        if (Yii::$app->request->isAjax && !empty($modResId) && !empty($courseId)) {

            $downloadUrl = null;
            $isAllowDownload = false;
            $nextCanRun = true;
            $isCourseware = true;
//            $courseModel = LnCourse::findOne($courseId);
            $isRandom = $modType == LnCourse::MOD_TYPE_RANDOM ? true : false;

            $modResModel = LnModRes::findOne($modResId);

//            $userId = Yii::$app->user->getId();
            $courseService = new CourseService();
            $courseCompleteService = new CourseCompleteService();
            $resourceCompleteService = new ResourceCompleteService();
            $isResComplete = false;
            $isCourseComplete = false;
            $isCourseDoing = false;
            $isResDoing = false;

            $scoCount = 0;
            $previousModresId = "";
            $previousScoId = "";
            $nextScoId = "";
            $nextModresId = "";

            $previousComponentCode = "";
            $nextComponentCode = "";

            $canAttempt = true;
            $scormService = new ScormService();

            if (!empty($modResModel)) {
                $componentCode = LnComponent::findOne($modResModel->component_id)->component_code;

                $maxAttempt = $modResModel->max_attempt;
                if ($maxAttempt != 0 && intval($attempt) > $maxAttempt) {
                    $canAttempt = false;
                }
            } else {
                $componentCode = "";
            }


            if ($mode == self::PLAY_MODE_NORMAL && $canAttempt) {
                if (!empty($courseRegId)) {
                    $courseCompleteFinalModel = LnCourseComplete::findOne($courseCompleteFinalId);

                    $courseCompleteService->checkCourseStatus($courseCompleteFinalModel, $isCourseDoing, $isCourseComplete, $isRetake);

                    $resourceCompleteService->checkResourceStatus($courseCompleteFinalId, $modResId, $isResDoing, $isResComplete);
                }


                if (!empty($modResModel)) {
                    $modId = $modResModel->mod_id;
                    $mod = LnCourseMods::findOne($modId);

                    $modNumber = $mod->mod_num;
                    $modResNumber = $modResModel->sequence_number;

                    if ($modNumber == 1) {
                        $previousModModel = null;
                        if ($modResNumber == 1) {
                            $previousModresModel = null;
                        } else {
                            $previousModresModel = $courseService->GetModResByDirect($courseId, $modId, $modResNumber, "previous");
                        }
                    } else {
                        $previousModModel = $courseService->GetModByDirect($courseId, $modNumber, "previous");
                        $previousModresModel = $courseService->GetModResByDirect($courseId, $modId, $modResNumber, "previous");
                    }

                    $modCount = $courseService->GetAvailableModCount($courseId);
                    $modResCount = $courseService->GetAvailableModResCount($courseId);

                    if ($modCount == 1) {
                        $nextModModel = null;
                        if ($modResCount == 1) {
                            $nextModresModel = null;
                        } else {
                            if ($modResNumber == $modResCount) {
                                $nextModresModel = null;
                            } else {
                                $nextModresModel = $courseService->GetModResByDirect($courseId, $modId, $modResNumber, "next");
                            }
                        }
                    } else {
                        if ($modCount == $modNumber) {
                            $nextModModel = null;
                        } else {
                            $nextModModel = $courseService->GetModByDirect($courseId, $modNumber, "next");
                        }
                        $nextModresModel = $courseService->GetModResByDirect($courseId, $modId, $modResNumber, "next");
                    }


                    if (empty($previousModModel) && empty($previousModresModel)) {
                        $previousModresId = "";
                    } else {
                        if (empty($previousModresModel)) {
                            $newModId = $previousModModel->kid;
                            $newPreviousModresModel = $courseService->GetModResByDirect($courseId, $newModId, null, "previous");
                            if (!empty($newPreviousModresModel)) {
                                $previousModresId = $newPreviousModresModel->kid;

                                $previousComponentCode = LnComponent::findOne(LnModRes::findOne($previousModresId)->component_id)->component_code;
                            } else {
                                $previousModresId = "";
                            }
                        } else {
                            $previousModresId = $previousModresModel->kid;
                            $previousComponentCode = LnComponent::findOne(LnModRes::findOne($previousModresId)->component_id)->component_code;
                        }
                    }


                    if (empty($nextModModel) && empty($nextModresModel)) {
                        $nextModresId = "";
                    } else {
                        if (empty($nextModresModel)) {
                            $newModId = $nextModModel->kid;
                            $newNextModresModel = $courseService->GetModResByDirect($courseId, $newModId, null, "next");
                            if (!empty($newNextModresModel)) {
                                $nextModresId = $newNextModresModel->kid;

                                $nextComponentCode = LnComponent::findOne(LnModRes::findOne($nextModresId)->component_id)->component_code;
                            } else {
                                $nextModresId = "";
                            }
                        } else {
                            $nextModresId = $nextModresModel->kid;
                            $nextComponentCode = LnComponent::findOne(LnModRes::findOne($nextModresId)->component_id)->component_code;
                        }
                    }


                    if ($scormService->isScormComponent($componentCode)) {


                        if (!empty($scoId)) {
                            $sco = LnScormScoes::findOne($scoId);
                            if (!empty($sco)) {
                                $scoNumber = $sco->sequence_number;
                                $scormId = $sco->scorm_id;
                                $scormScoesService = new ScormScoesService();

                                $scoCount = $scormScoesService->getAvailableScoCount($scormId, "sco");
                                if ($scoCount == 1) {
                                    $previousScoModel = null;
                                } else {
                                    $previousScoModel = $scormScoesService->getScoByDirect($scormId, "sco", $scoNumber, "previous");
                                }

                                if ($scoCount == ($scoNumber - 1)) {
                                    $nextScoModel = null;
                                } else {
                                    $nextScoModel = $scormScoesService->getScoByDirect($scormId, "sco", $scoNumber, "next");
                                }

                                if (empty($previousScoModel)) {
                                    $previousScoId = "";
                                } else {
                                    if (empty($previousScoId)) {
                                        $previousScoId = $previousScoModel->kid;
                                    }
                                }

                                if (empty($nextScoModel)) {
                                    $nextScoId = "";
                                } else {
                                    if (empty($nextScoId)) {
//                                        $scorm = LnCoursewareScorm::findOne($sco->scorm_id);
//                                        $result = $scormScoesTrackService->CheckIsScormScoesCompletedByAttempt($courseRegId, $modResId, $scoId, $userId, $attempt, $scorm);
//                                        if ($result) {
//                                            //只有当前单元完成，才显示下一单元
//                                            $nextScoId = $nextScoModel->kid;
//                                        }

                                        //2016/1/15:发现moodle实际是不控制是否能学下一单元的。
                                        $nextScoId = $nextScoModel->kid;
                                    }
                                }
                            }
                        }
                    }


                }
            }

            if ($modResModel->res_type == LnModRes::RES_TYPE_COURSEWARE) {
                $isCourseware = true;
                $itemId = $modResModel->courseware_id;
                $coursewareModel = LnCourseware::findOne($itemId);

                if (!$scormService->isScormComponent($componentCode))
                    $isAllowDownload = $coursewareModel->is_allow_download == LnCourseware::ALLOW_DOWNLOAD_YES ? true : false;

                $itemName = $coursewareModel->courseware_name;
//                        $fileModel = LnFiles::findOne($coursewareModel->file_id);
//
//                        $downloadFileName = "";
//
//                        if (!empty($coursewareName)) {
//                            if (!empty($fileModel->file_extension)) {
//                                $downloadFileName = $coursewareName . '.' . $fileModel->file_extension;
//                            } else {
//                                $downloadFileName = $coursewareName;
//                            }
//                        }

                $downloadUrl = Url::toRoute(['/resource/down', 'id' => $coursewareModel->file_id, 'file_name' => urlencode($itemName)]);
            } else {
                $isCourseware = true;
                $itemId = $modResModel->courseactivity_id;
                $courseActivityModel = LnCourseactivity::findOne($itemId);
                if ($courseActivityModel->object_type == 'examination') {
                    $examinationModel = new ExaminationService();
                    $examination = $examinationModel->GetExaminationByCopyOne($courseActivityModel->object_id);
                    $itemName = $examination['title'] . (!empty($examination['examination_question_number']) ? '&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . Yii::t('frontend', 'total_{value}', ['value' => $examination['examination_question_number']]) . '</strong>' : '');
                } else {
                    $itemName = $courseActivityModel->activity_name;
                }
            }

            if (!$isRandom && !$isResComplete) {
                $nextCanRun = false;
            }

            return $this->renderAjax('play-control', [
                'modResId' => $modResId,
                'scoId' => $scoId,
                'itemId' => $itemId,
                'isCourseware' => $isCourseware,
                'attempt' => $attempt,
                'courseId' => $courseId,
                'isCourseComplete' => $isCourseComplete,
                'isResComplete' => $isResComplete,
                'isCourseDoing' => $isCourseDoing,
                'isResDoing' => $isResDoing,
                'courseCompleteProcessId' => $courseCompleteProcessId,
                'courseCompleteFinalId' => $courseCompleteFinalId,
                'componentCode' => $componentCode,
                'previousModresId' => $previousModresId,
                'previousScoId' => $previousScoId,
                'nextScoId' => $nextScoId,
                'nextModresId' => $nextModresId,
                'previousComponentCode' => $previousComponentCode,
                'nextComponentCode' => $nextComponentCode,
                'nextCanRun' => $nextCanRun,
                'downloadUrl' => $downloadUrl,
                'isAllowDownload' => $isAllowDownload,
                'itemName' => $itemName,
                'scoCount' => $scoCount,
                'canAttempt' => $canAttempt,
            ]);
        }
    }

    public function actionCatalog($modResId = null, $courseId = null, $courseRegId = null, $scoId = null, $coursewareId = null, $attempt = "1",
                                  $courseCompleteFinalId = null, $courseCompleteProcessId = null, $modType = LnCourse::MOD_TYPE_RANDOM, $courseType = LnCourse::COURSE_TYPE_ONLINE,
                                  $mode = self::PLAY_MODE_PREVIEW)
    {
        $courseService = new CourseService();
        if (Yii::$app->request->isAjax && !empty($modResId) && !empty($courseId)) {
            $courseCompleteService = new CourseCompleteService();

            //课程模块及组件
            $currentTime = time();
            $userId = Yii::$app->user->getId();
//            $courseService = new CourseService();
//            $courseRegInfo = $courseService->getUserRegInfo($userId, $courseId);
//            if (!empty($courseRegInfo)){
//                $courseRegId = $courseRegInfo->kid;
//            }else{
//                $courseRegId = null;
//            }
            $show_point = 0;

            if ($courseRegId == null) {
                $isReg = false;
            } else {
                $isReg = true;
            }

            $isComplete = false;

            if ($mode == self::PLAY_MODE_NORMAL &&
                ((!empty($courseCompleteFinalId) && !empty($courseCompleteProcessId)) || !$courseCompleteService->isCourseComplete($courseRegId))
            ) {
                $modResModel = LnModRes::findOne($modResId);
//                $componentId = $modResModel->component_id;


//                $componentCode = LnComponent::findOne($componentId)->component_code;

                if (empty($courseCompleteFinalId) || empty($courseCompleteProcessId)) {
//                    $courseCompleteService->initCourseCompleteInfo($courseRegId, $courseId);

                    $courseCompleteProcessModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_PROCESS);
                    $courseCompleteFinalModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);

                    $courseCompleteProcessId = $courseCompleteProcessModel->kid;
                    $courseCompleteFinalId = $courseCompleteFinalModel->kid;
                } else {
                    $courseCompleteProcessModel = LnCourseComplete::findOne($courseCompleteProcessId);
                    $courseCompleteFinalModel = LnCourseComplete::findOne($courseCompleteFinalId);

                }

                //初次打开页面，课程进入学习中状态
                $courseCompleteService->changeCourseCompleteStatusToDoing($courseCompleteProcessModel, $currentTime);
                $courseCompleteService->changeCourseCompleteStatusToDoing($courseCompleteFinalModel, $currentTime);

                $resourceCompleteService = new ResourceCompleteService();

                $finalModel = $resourceCompleteService->checkResourceStatus($courseCompleteFinalId, $modResId, $isDoing, $isComplete);
                //只记录一次完成情况
                if (!$isComplete) {
                    $processModel = $resourceCompleteService->getLastResCompleteNonDoneInfo($courseRegId,
                        $modResId, LnResComplete::COMPLETE_TYPE_PROCESS, $courseCompleteProcessId);


                    if (empty($processModel)) {
                        $resCompleteProcessId = $resourceCompleteService->addResCompleteDoingInfo($courseCompleteProcessId, $courseRegId, $modResId, LnResComplete::COMPLETE_TYPE_PROCESS);//创建过程记录（进行中）
                    } else {
                        $resCompleteProcessId = $processModel->kid;
//                        $resourceCompleteService->setLastRecordAt($resCompleteProcessId, $currentTime, $duration);
                    }

//                    $finalModel = $resourceCompleteService->getLastResCompleteNonDoneInfo($courseRegId,
//                        $modResId, LnResComplete::COMPLETE_TYPE_FINAL, $courseCompleteFinalId);
                    if (empty($finalModel)) {
                        $resCompleteFinalId = $resourceCompleteService->addResCompleteDoingInfo($courseCompleteFinalId, $courseRegId, $modResId, LnResComplete::COMPLETE_TYPE_FINAL);//创建最终记录（进行中）
                    } else {
                        $resCompleteFinalId = $finalModel->kid;
//                        $resourceCompleteService->setLastRecordAt($resCompleteFinalId, $currentTime, $duration);
                    }


//                    $courseCompleteService->setLastRecordAt($courseCompleteProcessId, $currentTime, $duration);
//                    $courseCompleteService->setLastRecordAt($courseCompleteFinalId, $currentTime, $duration);
//                    $scormService = new ScormService();

                    //对于完成规则为浏览即完成，打开页面自动完成
                    if ($modResModel->complete_rule == LnModRes::COMPLETE_RULE_BROWSE) {
                        $isMobile = false;
                        if (Yii::$app->session->has("isMobile")) {
                            $isMobile = Yii::$app->session->get("isMobile");
                        }
                        if (empty($coursewareId)) {
                            $coursewareId = $modResModel->courseware_id;
                        }
                        $coursewareModel = LnCourseware::findOne($coursewareId);
                        if (($isMobile && $coursewareModel->is_display_mobile == LnCourseware::DISPLAY_MOBILE_YES) ||
                            (!$isMobile && $coursewareModel->is_display_pc == LnCourseware::DISPLAY_PC_YES)
                        ) {
                            $courseComplete = false;
                            $getCetification = false;
                            $courseId = null;
                            $certificationId = null;
                            //PC端可见，就必须要是PC端访问才行；移动端可见，就必须要是移动端访问才行
                            $resourceCompleteService->addResCompleteDoneInfo($courseCompleteProcessId, $courseRegId, $modResId, LnResComplete::COMPLETE_TYPE_PROCESS);//创建过程记录（完成）
                            $resourceCompleteService->addResCompleteDoneInfo($courseCompleteFinalId, $courseRegId, $modResId, LnResComplete::COMPLETE_TYPE_FINAL, null, null, false, null, true, false, $courseComplete, $getCetification, $courseId, $certificationId);//创建最终记录（完成）

                            //edit by baoxianjian 11:27 2016/3/31
                            $pointRuleService = new PointRuleService();
                            $pointResult = $pointRuleService->countCourseAndCetificationPoint($courseComplete, $getCetification, $courseId, $certificationId);

                        }
                    }
                } else {
                    //这里提供CompleteId主要是为了能继续记录学习时长
                    $processModel = $resourceCompleteService->getLastResCompleteInfo($courseRegId,
                        $modResId, LnResComplete::COMPLETE_TYPE_PROCESS, $courseCompleteProcessId);
                    if (!empty($processModel)) {
                        $resCompleteProcessId = $processModel->kid;
                    } else {
                        $resCompleteProcessId = "";
                    }

                    $finalModel = $resourceCompleteService->getLastResCompleteInfo($courseRegId,
                        $modResId, LnResComplete::COMPLETE_TYPE_FINAL, $courseCompleteFinalId);

                    if (!empty($finalModel)) {
                        $resCompleteFinalId = $finalModel->kid;
                    } else {
                        $resCompleteFinalId = "";
                    }
                }
            } else {
                $courseCompleteProcessId = "";
                $courseCompleteFinalId = "";
                $resCompleteProcessId = "";
                $resCompleteFinalId = "";
                $courseCompleteFinalModel = null;
            }

            $isOnlineCourse = $courseType == LnCourse::COURSE_TYPE_ONLINE ? true : false;
            $isRandom = $modType == LnCourse::MOD_TYPE_RANDOM ? true : false;
            $openStatus = LnCourse::COURSE_START;
            $courseMods = false;

            $studyModResId = null;

            $catalogMenu = $courseService->genCatalogMenu($courseCompleteFinalId, $courseId, $isReg, $courseComplete, $mode, $isOnlineCourse, $isRandom, $openStatus, $courseMods, $studyModResId);

            //$pointRuleService=new PointRuleService();
            //$pointResult=$pointRuleService->countCourseAndCetificationPoint($courseComplete,$getCetification,$courseId,$certificationId);

            return $this->renderAjax('catalog', [
                'currentModResId' => $modResId,
                'currentScoId' => $scoId,
                'courseRegId' => $courseRegId,
                'catalogMenu' => $catalogMenu,
                'courseCompleteProcessId' => $courseCompleteProcessId,
                'courseCompleteFinalId' => $courseCompleteFinalId,
                'resCompleteProcessId' => $resCompleteProcessId,
                'resCompleteFinalId' => $resCompleteFinalId,
                'mode' => $mode,
                'attempt' => $attempt,
                'pointResult' => $pointResult
//                'duration' => $this::LEARNING_DURATION
            ]);
        } else if (Yii::$app->request->isAjax && !empty($coursewareId) && $mode == self::PLAY_MODE_PREVIEW) {
            //课程模块及组件
            $courseCompleteProcessId = "";
            $courseCompleteFinalId = "";
            $resCompleteProcessId = "";
            $resCompleteFinalId = "";
            $isReg = true;
            $isComplete = false;
            $isOnlineCourse = true;
            $isRandom = true;
            $openStatus = LnCourse::COURSE_START;
            $courseMods = false;
            $courseRegId = "";

            $catalogMenu = $courseService->genCatalogMenu($courseCompleteFinalId, $courseId, $isReg, $isComplete, self::PLAY_MODE_PREVIEW, $isOnlineCourse, $isRandom, $openStatus, $courseMods);

            return $this->renderAjax('catalog', [
                'currentModResId' => $modResId,
                'currentScoId' => $scoId,
                'courseRegId' => $courseRegId,
                'catalogMenu' => $catalogMenu,
                'courseCompleteProcessId' => $courseCompleteProcessId,
                'courseCompleteFinalId' => $courseCompleteFinalId,
                'resCompleteProcessId' => $resCompleteProcessId,
                'resCompleteFinalId' => $resCompleteFinalId,
                'mode' => $mode,
            ]);
        }
    }

    public function actionListCourse($current_time, $ids = null, $page = 1, $order = 'new', $type = null)
    {
        $this->layout = 'none';

        if ($ids) {
            $ids = explode(',', $ids);
        }

        $uid = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;

        $size = 9;

        $service = new ResourceService();

        $isMobile = false;
        if (Yii::$app->session->has("isMobile")) {
            $isMobile = Yii::$app->session->get("isMobile");
        }

        $resource = $service->getResource($uid, $companyId, $ids, $type, $size, $size < 1 ? 0 : ((int)$page - 1) * $size, $order, $isMobile, $current_time);

        return $this->renderAjax('/common/list-course', [
            'data' => $resource,
        ]);
    }

    /**
     * 课程分享
     * @return array
     */
    public function actionShare()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isPost && Yii::$app->request->post()) {
            $user_id = Yii::$app->user->identity->kid;
            $title = Yii::$app->request->post('title');
            $content = Yii::$app->request->post('content');
            $obj_id = Yii::$app->request->post('obj_id');
            $users = Yii::$app->request->post('users');

            $service = new ShareService();
            if ($service->CourseShare($user_id, $obj_id, $content, $title, $users)) {
                return ['result' => 'success'];
            } else {
                return ['result' => 'failure'];
            }
        }
        return ['result' => 'failure'];
    }

    /**
     * 课程问题分享
     * @return array
     */
    public function actionShareQuestion()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isPost && Yii::$app->request->post()) {
            $user_id = Yii::$app->user->identity->kid;
            $obj_id = Yii::$app->request->post('question_id');

            $service = new ShareService();
            if ($service->CourseQuestionShare($user_id, $obj_id)) {
                return ['result' => 'success'];
            } else {
                return ['result' => 'failure'];
            }
        }
        return ['result' => 'failure'];
    }

    /**
     * 课程问题分享
     * @return array
     */
    public function actionFavQuestion()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->user->getId();
        $questionId = Yii::$app->request->post('question_id');

        $service = new QuestionService();

        if ($service->isCollect($id, $questionId)) {
            if ($service->cancelCollect($id, $questionId)) {
                return ['result' => 'cancel'];
            }
        } else {
            $model = new SoCollect();
            $result = $model->addCollect($id, $questionId, SoCollect::TYPE_QUESTION);
            if ($result) {
                return ['result' => 'success'];
            } else {
                return ['result' => 'fail'];
            }
        }
    }

    /**
     * 课程问题关注
     * @return array
     */
    public function actionAttentionQuestion()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $uid = Yii::$app->user->getId();
        $question_id = Yii::$app->request->post('question_id');

        $careService = new QuestionCareService();

        $careModel = new SoQuestionCare();
        $careModel->user_id = $uid;
        $careModel->question_id = $question_id;

        $service = new QuestionService();
        if ($careService->IsRelationshipExist($careModel)) {
            // 停止关注
            $careService->StopRelationship($careModel);

            // 删除时间树
            $timelineService = new TimelineService();

            $timelineModel = new MsTimeline();
            $timelineModel->owner_id = $uid;
            $timelineModel->object_id = $question_id;
            $timelineModel->object_type = MsTimeline::OBJECT_TYPE_QUESTION;
            $timelineModel->type_code = MsTimeline::TYPE_ATTENTION_QUESTION;

            $timelineService->deleteTimeline($timelineModel);

            SoQuestion::subFieldNumber($question_id, 'attention_num');
            return ['result' => 'cancel'];
        } else {
            // 添加关注关系
            $careService->startRelationship($careModel);
            // 增加关注统计值
            SoQuestion::addFieldNumber($question_id, 'attention_num');

            // 添加时间树
            $timelineService = new TimelineService();
            $timelineService->pushByCareQuestion($uid, $question_id);

            // 学习历程添加
            $recordService = new RecordService();
            $recordService->addByCareQuestion($uid, $question_id);

            return ['result' => 'success'];
        }
    }

    /**
     * 课程收藏及取消收藏
     * @return array
     */
    public function actionCollection()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isPost && Yii::$app->request->post()) {
            $user_id = Yii::$app->user->identity->kid;
            $obj_id = Yii::$app->request->post('obj_id');
            $service = new CollectService();
            $collectModel = new SoCollect();
            $collectModel->object_id = $obj_id;
            $collectModel->type = SoCollect::TYPE_COURSE;
            $collectModel->user_id = $user_id;

            if ($service->IsRelationshipExist($collectModel)) {
                $service->StopRelationship($collectModel);
                return ['result' => 'success'];
            } else {
                $service->startRelationship($collectModel);
                /*添加积分*/
                $pointRuleService = new PointRuleService();
                $pointResult = $pointRuleService->curUserCheckActionForPoint('Collect-Course', 'Community', $obj_id);
                return ['result' => 'success', 'pointResult' => $pointResult];

            }
        }
        return ['result' => 'failure'];
    }


    /**
     * 课程的域树
     * @param int $course_id
     * @return string
     */
    public function actionDomain($course_id = -1)
    {
        $treeTypeCode = "domain";
        $treeFlag = $treeTypeCode;
        $suffix = '';
        if (isset($treeFlag) && $treeFlag != null && $treeFlag != '')
            $suffix = '_' . $treeFlag;


        $treeDataUrl = yii\helpers\Url::toRoute(['../resource/course/domain-tree-data', 'course_id' => $course_id]);
        return $this->renderAjax('tree-node/multi-select', [
            'formType' => 'course-domain',
            'TreeType' => $treeTypeCode,
            'treeDataUrl' => $treeDataUrl,
            'treeState' => "False",
            'treeFlag' => $treeTypeCode,
            'needRegister' => 'True',
            'checkRoute' => 'tree-node/multi-select-tree'
        ]);
    }

    public function actionDomainTreeData($course_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $service = new TreeNodeService();

        //$otherService = CourseDomainService::className();//可根据需要换所需的服务

        $otherKid = $course_id;

        $result = $service->listTreeData(null, $otherKid);

        return $result;
    }

    public function actionRating()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $user_id = Yii::$app->user->getId();

        $course_id = Yii::$app->request->post('course_id');
        $rating = Yii::$app->request->post('rating');

        $service = new CourseService();

        if ($service->courseRating($user_id, $course_id, $rating)) {
            /*增加积分*/
            $pointRuleService = new PointRuleService();
            $pointResult = $pointRuleService->curUserCheckActionForPoint('Mark-Course', 'Learning-Portal', $course_id);

            $avg = $service->getCourseMarkByID($course_id);
            $count = $service->getCourseMarkCountByID($course_id);

            return ['result' => 'success', 'message' => ['avg' => number_format($avg, 1), 'count' => $count], 'pointResult' => $pointResult];

        }

        return ['result' => 'other', 'message' => Yii::t('frontend', 'score_failed')];
    }

    /*提交课程问题*/
    public function actionSetCourseQuestion($id)
    {
        if (Yii::$app->request->isAjax && !empty($id)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model = new SoQuestion();
            $uid = Yii::$app->user->getId();
            $model->user_id = $uid;
            $model->obj_id = $id;
            $companyId = Yii::$app->user->identity->company_id;
            $model->company_id = $companyId;
            $model->question_type = SoQuestion::QUESTION_TYPE_COURSE;
            $model->question_content = Yii::$app->request->post('question_content');
            $model->title = Yii::$app->request->post('question_title');
            $at_users = Yii::$app->request->post('users');
            $tag = Yii::$app->request->post('tag');
            $questionService = new QuestionService();
            $model->tags = $tag;
            if ($questionService->CreateQuestion($model, FwUser::findOne($uid))) {
                /*复制课程标签*/
//                $copyModel = new FwTag();
//                $copyModel->setCopyTag($id, $model->kid);
                // 消息推送
                $messageService = new MessageService();
                /*$messageService->pushMessageByQuestion($user, $model, null);//问题分享*/
//                $messageService->pushByQuestionAt($uid, $model, $at_users);
                // 学习记录添加
                $recordService = new RecordService();
                // 时间树 推送
                $timelineService = new TimelineService();
                $timelineService->pushBySubQuestion($uid, $model);
                if (!empty($at_users) && count($at_users) > 0) {
                    $timelineService->pushByQuestionAt($uid, $model, $at_users);
                    $questionService->saveAtUser($model->kid, $at_users);
                    $recordService->addByQuestionAt($uid, $model->kid, $at_users);
                    $messageService->pushByQuestionAt($uid, $model, $at_users);
                } else {
                    $recordService->addBySubQuestion($uid, $model);
                }
                return ['result' => 'success'];
            } else {
                $errors = array_values($model->getErrors());
                $message = '';
                for ($i = 0; $i < count($errors); $i++) {
                    $message .= $errors[$i][0] . '<br />';
                }
                return ['result' => 'other', 'message' => $message];
            }
        } else {
            return ['result' => 'failure'];
        }
    }

    /*获取课程问题记录*/
    public function actionGetTabQuestion($courseId = "", $preview = 0)
    {
//        $model = $courseId ? $this->findModel($courseId) : LnCourse::find(false);
        return $this->renderAjax('get-tab-question', [
            'courseId' => $courseId,
            'preview' => $preview,
        ]);
    }

    /*获取课程问题记录(浏览用)*/
    public function actionGetTabScanQuestion($courseId = "", $preview = 0)
    {
        $model = $courseId ? $this->findModel($courseId) : LnCourse::find(false);
        return $this->renderAjax('get-tab-scan-question', [
            'model' => $model,
            'preview' => $preview,
        ]);
    }

    /*获取课程问题记录*/
    public function actionGetQuestion($courseId, $preview = 0)
    {
        $this->layout = false;
        $pageNo = Yii::$app->request->getQueryParam('page');
        $pageNo = $pageNo ? $pageNo : 1;
        $pageSize = $this->defaultPageSize;
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }
        $service = new QuestionService();
        $companyId = Yii::$app->user->identity->company_id;
        $count = $service->getQuestionCountByCourseId($courseId, $companyId);
        $pages = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
        $result = $service->getCourseQuestionWithID($courseId, null, $pageSize, $pageNo, 't1.created_at asc', $companyId);
        $soUserAttention = new SoUserAttention();
        return $this->render('get-question', [
            'result' => $result,
            'soUserAttention' => $soUserAttention,
            'pages' => $pages,
            'pageNo' => $pageNo,
            'courseId' => $courseId,
            'preview' => $preview,
        ]);
    }

    /**
     * @param answer_id
     * @return string
     */
    public function actionGetAnswerComments($answer_id)
    {
        $answerService = new AnswerService();
        $list = $answerService->getAnswerCommentData($answer_id);
        return $this->renderAjax('get-answer-comments', [
            'list' => $list,
        ]);
    }

    /**
     * @param $answer_id
     * @return string
     */
    public function actionSetAnswerComments($answer_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $content = Yii::$app->request->post('content');
        if (empty($content)) return ['result' => 'fail'];
        $uid = Yii::$app->user->getId();
        $answerService = new AnswerService();
        $result = $answerService->addComments($answer_id, $uid, $content);
        if ($result) {
            $answer = $answerService->getAnswer($answer_id);
            /*添加积分*/
            $pointRuleService = new PointRuleService();
            $pointResult = $pointRuleService->curUserCheckActionForPoint('Comment-Course-Question', 'Community', $answer->question_id);
            return ['result' => 'success', 'pointResult' => $pointResult];
        } else {
            return ['result' => 'fail'];
        }
    }

    /*获取课程问题记录(浏览用)*/
    public function actionGetScanQuestion($courseId)
    {
        $pageNo = Yii::$app->request->getQueryParam('page');
        $pageNo = $pageNo ? $pageNo : 1;
        $pageSize = $this->defaultPageSize;
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }
        $service = new QuestionService();
        $count = $service->getQuestionCountByCourseId($courseId);
        $pages = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
        $result = $service->getCourseQuestion($courseId, $pageSize, $pageNo, 't1.created_at asc', $count);
        $soUserAttention = new SoUserAttention();
        return $this->renderAjax('get-scan-question', [
            'result' => $result,
            'soUserAttention' => $soUserAttention,
            'pages' => $pages,
            'pageNo' => $pageNo,
            'courseId' => $courseId,
        ]);
    }

    /*课程问题回复*/
    public function actionReplyQuestion()
    {
        $this->layout = false;
        Yii::$app->response->format = Response::FORMAT_JSON;
        $answer_content = Yii::$app->request->post('answer_content');
        $question_id = Yii::$app->request->post('questionId');
        if (empty($answer_content) || empty($question_id)) {
            return ['result' => 'failure'];
        }
        $user_id = Yii::$app->user->getId();
        $soAnswer = new SoAnswer();
        $soAnswer->question_id = $question_id;
        $soAnswer->user_id = $user_id;
        $soAnswer->answer_content = $answer_content;
        $soAnswer->needReturnKey = true;
        if ($soAnswer->save() !== false) {
            /*添加积分*/
            $pointRuleService = new PointRuleService();
            $pointResult = $pointRuleService->curUserCheckActionForPoint('Reply-Course-Question', 'Community', $question_id);
        }

        SoQuestion::addFieldNumber($question_id, "answer_num");
        /*消息推送*/
        $messageService = new MessageService();
        $messageService->QuestionAnswerToSub($soAnswer);
        $messageService->QuestionAnswerToCare($soAnswer);

        // 学习历程添加
        $recordService = new RecordService();
        $recordService->addByAnswerQuestion($user_id, $question_id);
        return ['result' => 'success', 'pointResult' => $pointResult];
    }

    /*问题答案*/
    public function actionGetQuestionAnswer($questionId, $preview = 0)
    {
        $this->layout = false;
        $service = new QuestionService();
        SoQuestion::addFieldNumber($questionId, "browse_num");/*增加浏览数据*/
        $result = $service->getQuestionAnswer($questionId, 't1.created_at asc');
        $soUserAttention = new SoUserAttention();
        $uid = Yii::$app->user->getId();
        return $this->render('get-question-answer', [
            'uid' => $uid,
            'result' => $result,
            'preview' => $preview,
            'soUserAttention' => $soUserAttention,
        ]);
    }

    /*问题答案(浏览用)*/
    public function actionGetScanQuestionAnswer($questionId, $preview = 0)
    {
        $service = new QuestionService();
        SoQuestion::addFieldNumber($questionId, "browse_num");/*增加浏览数据*/
        $result = $service->getQuestionAnswer($questionId, 't1.created_at asc');
        return $this->renderAjax('get-scan-question-answer', [
            'result' => $result,
            'preview' => $preview,
        ]);
    }

    /*问题关注*/
    public function actionQuestionCare($questionId)
    {
        $user_id = Yii::$app->user->getId();
        $questionModel = new SoQuestionCare();
        $questionModel->user_id = $user_id;
        $questionModel->question_id = $questionId;
        $questionCare = new QuestionCareService();
        if ($questionCare->IsRelationshipExist($questionModel)) {
            $questionCare->StopRelationship($questionModel);
        } else {
            $questionCare->startRelationship($questionModel);
        }
        return ['result' => 'success'];
    }

    /*关注人*/
    public function actionMemberAttention()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if ($result = CommonController::actionAttentionUser()) {
            $msg = $result['status'] == 1 ? Yii::t('frontend', 'cancel_attention') : Yii::t('common', 'attention');
            return ['result' => 'success', 'msg' => $msg];
        } else {
            return ['result' => 'fail'];
        }
    }

    public function actionRecordScormData($courseRegId, $modResId, $scoId, $coursewareId, $attempt, $courseCompleteProcessId, $courseCompleteFinalId, $userId, $withSessionStr)
    {
        if (Yii::$app->request->isPost && !empty($courseRegId) && !empty($modResId)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $withSession = $withSessionStr == "True" ? true : false;
            $currentSessionKey = Yii::$app->session->getId();
            $sessionKey = $currentSessionKey; //sessionKey是用来判断发送成绩信息的人和当前用户是不是同一个人，避免作弊。（为了测试方便，暂时屏蔽此功能）
            if ($currentSessionKey == $sessionKey) {

                $scormScoesTrackService = new ScormScoesTrackService();
                $scormService = new ScormService();
                $scorm = $scormService->getScormByCoursewareId($coursewareId);
                $trackdata = $scormScoesTrackService->getScoesTrackResultByAttempt($courseRegId, $modResId, $scoId, $attempt, $withSession);
//                $getData = Yii::$app->request->getQueryParams();
                $postData = Yii::$app->request->getBodyParams();
//                if (empty($postData)) {
//                    //测试同事可以通过get方式提交参数
//                    $postData = Yii::$app->request->getQueryParams();
//                }
//                Yii::getLogger()->log("courseRegId:". $courseRegId, Logger::LEVEL_ERROR);
//                Yii::getLogger()->log("modResId:". $modResId, Logger::LEVEL_ERROR);
//                Yii::getLogger()->log("scoId:". $scoId, Logger::LEVEL_ERROR);
//                Yii::getLogger()->log("coursewareId:". $coursewareId, Logger::LEVEL_ERROR);
//                Yii::getLogger()->log("attempt:". $attempt, Logger::LEVEL_ERROR);
//                Yii::getLogger()->log("courseCompleteProcessId:". $courseCompleteProcessId, Logger::LEVEL_ERROR);
//                Yii::getLogger()->log("courseCompleteFinalId:". $courseCompleteFinalId, Logger::LEVEL_ERROR);
//                Yii::getLogger()->log("userId:". $userId, Logger::LEVEL_ERROR);
//                Yii::getLogger()->log("postData:". json_encode($postData), Logger::LEVEL_ERROR);

                $request = "";
                $courseComplete = false;
                $getCetification = false;
                $courseId = null;
                $certificationId = null;
                if (!empty($postData) && !empty($postData['datalist']) && count($postData['datalist']) > 0) {
//                if (!empty($getData) && count($getData) > 0) {
                    $scormScoesTrackService->batchInsertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scorm, $scoId, $userId, $postData['datalist'], $attempt,
                        $trackdata, $withSession, null, $courseComplete, $getCetification, $courseId, $certificationId);

//                        if (substr($element, 0, 15) == 'adl.nav.request') {
//                            // SCORM 2004 Sequencing Request.
//                            $search = array('@continue@', '@previous@', '@\{target=(\S+)\}choice@', '@exit@',
//                                '@exitAll@', '@abandon@', '@abandonAll@');
//                            $replace = array('continue_', 'previous_', '\1', 'exit_', 'exitall_', 'abandon_', 'abandonall');
//                            $action = preg_replace($search, $replace, $value);
//
//                            if ($action != $value) {
//                                // Evaluating navigation request.
//                                //下面这句今后可能有用，用来替换 $valid = 'true';
//                                //$valid = $scormService->scorm_seq_overall($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$scoId, $userId, $action, $attempt);
//                                $valid = 'true';
//
//                                // Set valid request.
//                                $search = array('@continue@', '@previous@', '@\{target=(\S+)\}choice@');
//                                $replace = array('true', 'true', 'true');
//                                $matched = preg_replace($search, $replace, $value);
//                                if ($matched == 'true') {
//                                    $request = 'adl.nav.request_valid["' . $action . '"] = "' . $valid . '";';
//                                }
//                            }
//                        }
//                    }
                }

                $pointRuleService = new PointRuleService();
                $pointResult = $pointRuleService->countCourseAndCetificationPoint($courseComplete, $getCetification, $courseId, $certificationId);

                /*
                Yii::$app->session->set('point_show_point',$show_point);
                Yii::$app->session->set('point_name',$point_name);
                Yii::$app->session->set('point_available_point',$r['available_point']);
                */

                return ['result' => 'true', 'message' => "", 'request' => $request, 'pointResult' => $pointResult];
            } else {
                return ['result' => 'false', 'message' => "0", 'request' => "", 'show_point' => 0];
            }
        }
    }


    public function actionGetScormStatus($courseCompleteFinalId, $courseRegId, $modResId, $userId, $scoId, $attempt)
    {
        $this->enableCsrfValidation = false;
        if (Yii::$app->request->isPost && !empty($courseRegId) && !empty($modResId)) {
            $withSession = true;
            Yii::$app->response->format = Response::FORMAT_JSON;
            $scormId = LnScormScoes::findOne($scoId)->scorm_id;
            $scorm = LnCoursewareScorm::findOne($scormId);
            $scormScoesTrackService = new ScormScoesTrackService();

            $element = "cmi.core.lesson_status";
            if ($scorm->scorm_version == ScormScoesTrackService::SCORM_13) {
                $element = "cmi.completion_status";
            }
            $track = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId, $modResId, $scoId, $element, $attempt, $withSession);

            if ($track == null)
                $status = "";
            else {
                $status = $track->value;
            }

            $resourceCompleteService = new ResourceCompleteService();
            $isResComplete = $resourceCompleteService->isResComplete($courseCompleteFinalId, $modResId);
            $isResCompleteStr = $isResComplete ? "1" : "0";

            return ['status' => $status,
                'isResCompleteStr' => $isResCompleteStr];
        }
    }

    /**
     * 暂存课程模块、模块与组件关联
     * @return array
     */
    public function actionTeacherSave($id, $preview = 0)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model_data = Yii::$app->request->post();
        $model = LnCourse::findOne($id);
        //保存资源模块
        $resourceService = new ResourceService();
        $return = $resourceService->SetCourseResource($model_data['resource'], $model->kid);
        if (!empty($model_data['resource'])) {
            $is_survey_only = LnCourse::IS_SURVEY_ONLY_NO;
            $is_exam_only = LnCourse::IS_EXAM_ONLY_NO;

            $survey = $exam = 0;
            $resourceCount = 0;
            foreach ($model_data['resource'] as $items) {
                if (!empty($items['coursewares'])) {
                    foreach ($items['coursewares'] as $k => $val) {
                        $resourceCount += count($items['coursewares'][$k]);
                    }
                }
                if (!empty($items['activity'])) {
                    $exam = !empty($items['activity']['examination']) ? $exam + count($items['activity']['examination']) : $exam;
                    $survey = !empty($items['activity']['investigation']) ? $survey + count($items['activity']['investigation']) : $survey;
                    foreach ($items['activity'] as $k => $val) {
                        $resourceCount += count($items['activity'][$k]);
                    }
                }
            }
            if ($resourceCount > 0 && $resourceCount == $survey) {
                $is_survey_only = LnCourse::IS_SURVEY_ONLY_YES;
            }
            if ($resourceCount > 0 && $resourceCount == $exam) {
                $is_exam_only = LnCourse::IS_EXAM_ONLY_YES;
            }
            $model->is_survey_only = $is_survey_only;
            $model->is_exam_only = $is_exam_only;
            $model->needReturnKey = true;
            $model->save();
        }
        /*编辑课程时重新记分*/
        $courseCompleteService = new CourseCompleteService();
        $courseCompleteService->resetCourseResComplete($id);
        return ['result' => 'success', 'id' => $model->kid];
    }

    public function actionDownloadCalendar($courseId, $type)
    {
        $model = $this->findModel($courseId);
        if (!empty($model)) {

            if ($type == "open") {
                $startTime = $model->open_start_time;
                $endTime = $model->open_end_time;
                $name = $model->course_name . Yii::t('frontend', 'course_open');
            } else if ($type == "enroll") {
                $startTime = $model->enroll_start_time;
                $endTime = $model->enroll_end_time;
                $name = $model->course_name . Yii::t('frontend', 'enroll');
            }

            if (!empty($startTime) && empty($endTime)) {
                $endTime = $startTime;
            }

            $location = $model->training_address;
            $description = $model->course_desc;

            if (!empty($startTime) && !empty($endTime) && $startTime != 0 && $endTime != 0) {

                $endTime = $endTime + (24 * 3600);//因为是全天事件，所以要加一天
                $startDate = date("Y-m-d", $startTime);
                $endDate = date("Y-m-d", $endTime);
                $startTime = "09:00:00";
                $endTime = "18:00:00";

                $ics_data = "BEGIN:VCALENDAR\n";
                $ics_data .= "VERSION:2.0\n";
                $ics_data .= "PRODID:E-Learning Platform\n";
                $ics_data .= "METHOD:PUBLISH\n";
                $ics_data .= "X-WR-CALNAME:Learning-Schedule\n";

                # Change the timezone if needed
                $ics_data .= "X-WR-TIMEZONE:Asia/China\n";
                $ics_data .= "BEGIN:VTIMEZONE\n";
//                $ics_data .= "TZID:Asia/China\n";
//                $ics_data .= "BEGIN:DAYLIGHT\n";
//                $ics_data .= "TZOFFSETFROM:-0500\n";
//                $ics_data .= "TZOFFSETTO:-0400\n";
//                $ics_data .= "DTSTART:1403086496\n";
//                $ics_data .= "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU\n";
//                $ics_data .= "TZNAME:EDT\n";
//                $ics_data .= "END:DAYLIGHT\n";
//                $ics_data .= "BEGIN:STANDARD\n";
//                $ics_data .= "TZOFFSETFROM:-0400\n";
//                $ics_data .= "TZOFFSETTO:-0500\n";
//                $ics_data .= "DTSTART:1403086496\n";
//                $ics_data .= "RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU\n";
//                $ics_data .= "TZNAME:EST\n";
//                $ics_data .= "END:STANDARD\n";
                $ics_data .= "END:VTIMEZONE\n";

                # Replace HTML tags
                $search = array("/<br>/", "/&/", "/→/", "/←/", "/,/", "/;/");
                $replace = array("\\n", "&", "-->", "<--", "\\,", "\\;");

                $name = preg_replace($search, $replace, $name);
                $location = preg_replace($search, $replace, $location);
                $description = preg_replace($search, $replace, $description);

                # Change TimeZone if needed
                $ics_data .= "BEGIN:VEVENT\n";
                $ics_data .= "DTSTART;VALUE=DATE:" . $startDate . "\n"; //."T".$startTime.
                $ics_data .= "DTEND;VALUE=DATE:" . $endDate . "\n";//. "T" . $endTime . "\n"
                $ics_data .= "DTSTAMP:" . date('Ymd') . "T" . date('His') . "Z\n";

                if (!empty($location))
                    $ics_data .= "LOCATION:" . $location . "\n";

                if (!empty($description))
                    $ics_data .= "DESCRIPTION:" . $description . "\n";

                $ics_data .= "SUMMARY:" . $name . "\n";
                $ics_data .= "UID:E-Learning-" . $courseId . "-" . $type . "\n";
                $ics_data .= "SEQUENCE:0\n";
                $ics_data .= "END:VEVENT\n";

                $ics_data .= "END:VCALENDAR\n";

                # Download the File
                $filename = "event_" . $type . "_calendar.ics";
                header("Content-type:text/calendar");
                header("Content-Disposition: attachment; filename=$filename");
                return $ics_data;
            } else {
                return null;
            }
        } else {
            return null;
        }

    }

    /**
     * 加载课程资源列表
     * @param string $id
     * @param int $pageSize
     * @return string
     */
    public function actionGetCourseScore($id, $pageSize = 5)
    {
        $resourceService = new ResourceService();
        $count = $resourceService->GetResourceCount($id);
        $pages = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);

        $courseMods = $resourceService->GetCourseResource($id, $pageSize, $pages->getOffset());

        return $this->renderAjax('get-course-score', [
            'courseId' => $id,
            'courseMods' => $courseMods,
            'pages' => $pages,
        ]);
    }

    public function actionGetCourseConfig($id)
    {
        $resourceService = new ResourceService();
        $courseMods = $resourceService->GetCourseResourceConfigAll($id);
        $courseType = $resourceService->GetCourseType($id);
        // $courseModsDetail = $resourceService->getCourseMods($id);
        //  $components =  $resourceService->getComponents('',false);
        $wareids = array();
        $activityids = array();
        $componentids = array();
        $name = array('direct' => '', 'normal' => '');
        $wares = array();
        $activities = array();
        $componentsfinal = array();

        foreach ($courseMods as $v) {
            if (!empty($v->courseware_id)) {
                $wareids[] = $v->courseware_id;
            }
            if (!empty($v->courseactivity_id)) {
                $activityids[] = $v->courseactivity_id;
            }
            $componentids[] = $v->component_id;
            if ($v->direct_complete_course == LnModRes::DIRECT_COMPLETE_COURSE_YES) {
                $data['direct'][] = $v;
            } elseif ($v->direct_complete_course == LnModRes::DIRECT_COMPLETE_COURSE_NO) {
                $data['normal'][] = $v;
            }
        }
        //   $wareids = substr($wareids,0,strlen($wareids)-1);
        //   $activityids = substr($activityids,0,strlen($activityids)-1);
        $wareNames = $resourceService->GetCourseResourceCoursewareDetail($wareids);
        $activitieNames = $resourceService->GetCourseResourceCourseActivityDetail($activityids);
        $componentNames = $resourceService->GetCourseResourceComponentNames($componentids);
        foreach ($wareNames as $v) {
            $wares[$v->kid] = $v->courseware_name;
        }
        foreach ($activitieNames as $v) {
            $activities[$v->kid] = $v->activity_name;
        }
        foreach ($componentNames as $v) {
            $componentsfinal[$v->kid] = $v->title;
        }
        $name['wares'] = $wares;
        $name['activities'] = $activities;
        return $this->renderAjax('get-course-config', [
            'courseType' => $courseType,
            'data' => $data,
            'name' => $name,
            'component' => $componentsfinal,
        ]);
    }

    /**
     * 加载课程资源成绩详情
     * @param $id
     * @return string
     */
    public function actionGetScoreDetail()
    {
        $modResId = Yii::$app->request->get('id');
        $params = Yii::$app->request->getQueryParams();
        $params['defaultPageSize'] = $this->defaultPageSize;
        $resourceCompleteService = new ResourceCompleteService();
        $result = $resourceCompleteService->getResCompleteData($modResId, $params);
        return $this->renderAjax('get-score-detail', [
            'resCompletes' => $result['data'],
            'pages' => $result['pages'],
        ]);
    }

    /**
     * 导出成绩详情
     * @param $id
     */
    public function actionExportScoreDetail($id)
    {
        $modRes = LnModRes::findOne($id);
        $resourceCompleteService = new ResourceCompleteService();
        $resCompletes = $resourceCompleteService->getResCompleteAllData($id);

//        $content = "No.\t姓名\t邮箱\t手机\t状态\t开始时间\t完成时间\t成绩\n";

        $header = Yii::t('frontend', 'headers');
        $data = array();
        $i = 0;
        foreach ($resCompletes as $res) {
            $data[$i][0] = $res->fwUser->real_name;
            $data[$i][1] = $res->fwUser->email;
            $data[$i][2] = $res->fwUser->mobile_no;
            $data[$i][3] = $res->getCompleteStatusText();
            $data[$i][4] = ($res->start_at ? TTimeHelper::toDateTime($res->start_at, TTimeHelper::DATE_FORMAT_1) : '');
            $data[$i][5] = ($res->end_at ? TTimeHelper::toDateTime($res->end_at, TTimeHelper::DATE_FORMAT_1) : '');
            $data[$i][6] = ($res->complete_score ? $res->complete_score : '');
            $i++;
        }

        TExportHelper::exportCsv($header, $data, $modRes->getResourceName());
    }

    /**
     * 删除课程目录
     * @param $tree_node_id
     * @return array
     */
    public function actionDeleteCategory($tree_node_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $service = new CourseCategoryService();
        //$category_id = $service->getCourseCategoryIdByTreeNodeId($tree_node_id);
        $service->deleteRelateData($tree_node_id);
        return ['result' => 'success'];
    }

    /**
     * 学员签到
     * @return array
     */
    public function actionSignin()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $courseId = Yii::$app->request->get('id');
        $userId = Yii::$app->user->getId();
        $data = new LnCourseSignIn();
        $data->sign_time = time();
        $data->course_id = $courseId;
        $data->user_id = $userId;
        $data->sign_user_id = $userId;
        $data->sign_system = LnCourseSignIn::SIGN_SYSTEM_PC;
        $data->sign_type = LnCourseSignIn::SIGN_TYPE_SELF;
        $errorMsg = null;
        $courseSignInService = new CourseSignInService();
        $result = $courseSignInService->studentSignIn($data);
        if ($result['result'] == '1') {
            return ['result' => 'success', 'errmsg' => $result['']];
        } else {
            return ['result' => 'failure', 'errmsg' => $result];
        }
    }

    /**
     * 查看学员签到记录
     * @return string
     */
    public function actionGetSigninRecord()
    {
        $id = Yii::$app->request->get('id');
        $date = Yii::$app->request->get('date');
        $userId = Yii::$app->user->getId();
        $courseSignInSettingService = new CourseSignInSettingService();
        $signDates = $courseSignInSettingService->getSignDatesByCourseId($id);
        $courseSignInService = new CourseSignInService();
        $result = $courseSignInService->getStudentSignInList($id, array('sign_date' => $date, 'uid' => $userId));
        return $this->renderAjax('signin-record', [
            'selectDate' => $date,
            'signDates' => $signDates,
            'result' => $result,
        ]);
    }

    public function actionOnlineDetail($id)
    {
        $course = LnCourse::findOne($id);

        return $this->render('online-detail', [
            'course' => $course,
        ]);
    }

    /**
     * 资源完成配置
     * @param $id
     * @return string
     */
    public function actionResourceConfig($id)
    {
        //$model = $this->findModel($id);
        $service = new ResourceService();
        $list = $service->getCourseMods($id);
//var_dump($list);
        return $this->renderAjax('resource_config', [
            'list' => $list,
        ]);
    }

    /**
     * 面授课程培训场地查询
     * @return array|bool|yii\db\ActiveRecord[]
     */
    public function actionGetTrainingAddress()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $keyword = Yii::$app->request->get('q');
        $companyId = Yii::$app->request->get('companyId');
        if (empty($companyId)) $companyId = Yii::$app->user->identity->company_id;
        $service = new CourseService();
        $result = $service->getCourseTrainingAddress($keyword, $companyId);
        $data = array();
        if (!empty($result)) {
            foreach ($result as $items) {
                $title = $items['address_name'] . (!empty($items['address_code']) ? '(' . $items['address_code'] . ')' : '');
                $data[] = array('kid' => $items['kid'], 'title' => $title);
            }
            $data = array('results' => $data);
        } else {
            $data = array('results' => '');
        }
        return $data;
    }

    /**
     * 课程审批
     * @param $courseId
     * @param $userId
     */
    public function actionCourseApproval($courseId, $userId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $courseModel = $this->findModel($courseId);
        if (empty($courseModel)) return ['result' => 'fail', 'errmsg' => Yii::t('frontend', '{value}_not_exists', ['value' => Yii::t('common', 'course')])];
        if ($courseModel->approval_rule == LnCourse::COURSE_APPROVAL_DEFAULT) {
            return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'course_not_need_approval')];
        }
        $courseReg = LnCourseReg::findOne(['course_id' => $courseId, 'user_id' => $userId]);
        if ($courseReg->reg_state == LnCourseReg::REG_STATE_APPLING) {
            $approved_by = Yii::$app->user->getId();
            $approved_at = time();
            $courseReg->approved_by = $approved_by;
            $courseReg->approved_at = $approved_at;
            if ($courseReg->save() !== false) {
                $courseService = new CourseService();
                $courseService->setCourseRegState($courseId, $userId, LnCourseReg::REG_STATE_APPROVED);
                /*面授更新审批状态*/
                if ($courseModel->course_type == LnCourse::COURSE_TYPE_FACETOFACE) {
                    $courseEnroll = LnCourseEnroll::findOne(['course_id' => $courseId, 'user_id' => $userId]);
                    $courseEnroll->approved_by = $approved_by;
                    $courseEnroll->approved_at = $approved_at;
                    $courseEnroll->approved_state = LnCourseEnroll::APPROVED_STATE_APPROVED;
                    $courseEnroll->save();
                } else {
                    /*在线审批课程*/
                    if ($courseModel->course_type == LnCourse::COURSE_TYPE_ONLINE && $courseModel->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT) {
                        /*添加积分*/
                        $pointRuleService = new PointRuleService();
                        $user = FwUser::find(false)->andFilterWhere(['kid' => $userId])->select('company_id')->one();
                        $companyId = $user->company_id;
                        $pointRuleService->checkActionForPoint($companyId, $userId, 'Register-Online-Course', 'Learning-Portal', $courseId);
                    }
                }
                /*更新审批流程表*/
                $approvalFlow = FwApprovalFlow::findOne(['event_id' => $courseId, 'applier_id' => $userId]);
                if (!empty($approvalFlow)) {
                    $approvalFlow->approval_status = FwApprovalFlow::APPROVAL_STATUS_APPROVED;
                    $approvalFlow->approved_by = $approved_by;
                    $approvalFlow->approved_at = $approved_at;
                    $approvalFlow->save();
                }
                return ['result' => 'success', 'errmsg' => 'ok'];
            }
        } else {
            return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'course_approvaled')];
        }
    }

    public function actionBatchSetCourseEnrollStatus()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $ids = Yii::$app->request->post('ids');
            $courseId = Yii::$app->request->post('cid');
            $type = Yii::$app->request->post('type');

            if (!isset($ids) || count($ids) === 0) {
                return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'params_wrong')];
            }
            if (!isset($courseId) || $courseId === '') {
                return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'params_wrong')];
            }
            if ($type !== LnCourseEnroll::ENROLL_TYPE_ALLOW && $type !== LnCourseEnroll::ENROLL_TYPE_DISALLOW) {
                return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'params_wrong')];
            }

            $userId = Yii::$app->user->getId();

            $service = new CourseEnrollService();
            $result = $service->BatchSetEnrollStatus($courseId, $ids, $type, $userId);

            if ($result === true) {
                return ['result' => 'success'];
            } else {
                return ['result' => 'fail', 'msg' => $result];
            }
        }
    }

    public function actionExportCourseEnrollUser($id, $type = 'admin')
    {
        /*课程报名*/
        $params = Yii::$app->request->queryParams;

        $params['showAll'] = 'True';
        $enrollService = new CourseEnrollService();

        if ($type === 'admin') {
            $params['enroll_type'] = array(LnCourseEnroll::ENROLL_TYPE_REG, LnCourseEnroll::ENROLL_TYPE_ALLOW, LnCourseEnroll::ENROLL_TYPE_DISALLOW);
            /*课程报名数据*/
            $result = $enrollService->searchCourseEnroll($id, $params);
            TExportHelper::courseEnrollUserByAdmin($result['data']);
        } elseif ($type === 'teacher') {
            $params['enroll_type'] = LnCourseEnroll::ENROLL_TYPE_ALLOW;
            $result = $enrollService->searchCourseEnroll($id, $params);
            TExportHelper::courseEnrollUserByTeacher($result['data']);
        }
    }

    /**
     * 面授课程手动添加报名学员
     * @param $id 课程id
     * @return array
     */
    public function actionManualEnroll($id)
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $data = $this->findModel($id);
            $time = time();
//            if ($data->enroll_start_time != null && $data->enroll_start_time > $time) {
//                return ['result' => 'fail', 'errcode' => 'time_fail', 'errmsg' => Yii::t('frontend', 'no_registration_time')];
//            }
//            if ($data->enroll_end_time != null && $data->enroll_end_time <= $time) {
//                return ['result' => 'fail', 'errcode' => 'time_fail', 'errmsg' => Yii::t('frontend', 'registration_period_has_ended')];
//            }
            if ($data->open_status == LnCourse::COURSE_END) {
                return ['result' => 'fail', 'errcode' => 'open_fail', 'errmsg' => Yii::t('frontend', 'course_completed')];
            }

            $enrollUserIds = Yii::$app->request->post('userIds');

            $userCount = count($enrollUserIds);
            $errCount = 0;

            foreach ($enrollUserIds as $userId) {
                $courseService = new CourseService();
                $enroll_info = array(
                    'course_id' => $id,
                    'user_id' => $userId,
                    'enroll_type' => LnCourseEnroll::ENROLL_TYPE_REG,
                    'enroll_user_id' => $userId,
                    'enroll_method' => LnCourseEnroll::ENROLL_METHOD_MANUAL,
                );
                $result = $courseService->saveOtherEnrollInfo($enroll_info);
                if ($result['result'] == 'success') {
                    LnCourse::addFieldNumber($id, 'register_number');/*增加注册量*/
                } elseif ($result['result'] == 'fail') {
                    $errCount++;
                }
            }
            if ($errCount === $userCount) {
                return ['result' => 'fail', 'errcode' => 'not_allow', 'errmsg' => Yii::t('frontend', 'exam_re_enroll')];/*已经报过名*/
            }
            return ['result' => 'success', 'errcode' => 'normal', 'div' => 'signUpResult_note'];
        }
    }

    public function actionDeleteManualEnroll($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $enrollId = Yii::$app->request->post('id');

        if (!$enrollId) {
            exit('Id is null.');
        }

        $courseService = new CourseService();
        $result = $courseService->delEnrollCourse($enrollId);
        if (!$result) {
            return ['result' => $result];
        } else {
            /*减少注册量*/
            LnCourse::subFieldNumber($enrollId, 'register_number');

            return ['result' => 'success'];
        }
    }

}