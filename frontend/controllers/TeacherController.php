<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 */

namespace frontend\controllers;

use common\models\framework\FwUser;
use common\models\learning\LnComponent;
use common\models\learning\LnCourseactivity;
use common\models\learning\LnCourseReg;
use common\models\learning\LnCourseware;
use common\models\learning\LnExamination;
use common\models\learning\LnExaminationResultUser;
use common\models\learning\LnHomework;
use common\models\learning\LnHomeworkResult;
use common\models\learning\LnHomeworkFile;
use common\models\learning\LnResComplete;
use common\services\framework\DictionaryService;
use common\services\learning\CourseCertificationService;
use common\services\learning\CourseEnrollService;
use common\services\learning\CourseSignInSettingService;
use common\services\learning\ExaminationService;
use common\services\learning\HomeworkService;
use common\services\learning\ResourceCompleteService;
use common\services\scorm\ScormService;
use frontend\base\BaseFrontController;
use common\services\learning\CourseCompleteService;
use common\services\learning\CourseService;
use common\services\learning\ResourceService;
use common\services\learning\CertificationService;
use common\services\learning\InvestigationService;
use common\services\learning\ComponentService;
use common\models\learning\LnInvestigation;
use common\models\learning\LnInvestigationQuestion;
use common\models\learning\LnInvestigationOption;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseMods;
use common\models\learning\LnResourceDomain;
use common\models\learning\LnModRes;
use common\models\learning\LnCourseEnroll;
use common\models\learning\LnCourseSignIn;
use components\widgets\TPagination;
use frontend\viewmodels\message\SendMailForm;
use Symfony\Component\Validator\Constraints\True;
use yii\db\Query;
use yii\helpers\Url;

use Yii;
use yii\db;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use common\models\learning\LnCourseOwner;
use common\models\framework\FwUserPosition;
use common\helpers\TTimeHelper;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnCourseCertification;
use common\models\learning\LnUserCertification;
use common\models\learning\LnCourseTeacher;
use common\services\social\QuestionService;
use common\models\social\SoUserAttention;
use common\services\message\TimelineService;
use common\models\message\MsTimeline;
use common\services\learning\RecordService;
use common\services\learning\TeacherManageService;


class TeacherController extends BaseFrontController
{
    public $layout = 'frame';
    const PLAY_MODE_NORMAL = 'normal';
    const PLAY_MODE_PREVIEW = 'preview';

    public function actionIndex()
    {
        $uid = Yii::$app->user->getId();
        // 统计 未开课 已开课 已结束的课程
        $courseService = new CourseService ();
        $statCourse = $courseService->teacherStatCourse($uid);
        $timeCourse = $courseService->teacherGetYearCourse($uid);

        $teacherManageService = new TeacherManageService();
        $teacher = $teacherManageService->findInnerTeacherByUserId($uid);
        $teacherList = array();
        foreach ($timeCourse as $c) {
            $teacherStr=$teacherManageService->getTeacherNamesByCourseId($c->kid);
            $teacherList[$c->kid] = $teacherStr;
        }

        return $this->render('index', [
            'statCourse' => $statCourse,
            'timeCourse' => $timeCourse,
            'teacher' => $teacher,
            'teacherList' => $teacherList
        ]);
    }


    /**
     * 未开始的课程
     * @return string
     */
    public function actionCourseBefore()
    {
        $this->layout = 'none';
        $uid = Yii::$app->user->getId();

        $courseService = new CourseService();
        $re = $courseService->teacherGetCourse($uid, 'before');

        return $this->render('coursebefore', [
            'course' => $re['course'],
            'page' => $re['page'],
        ]);
    }


    /**
     * 进行中的课程
     * @return string
     */
    public function actionCourseStart()
    {
        $this->layout = 'none';
        $uid = Yii::$app->user->getId();

        $courseService = new CourseService();
        $re = $courseService->teacherGetCourse($uid, 'start');
        return $this->render('coursestart', [
            'course' => $re['course'],
            'page' => $re['page'],
        ]);
    }

    //结束的课程
    public function actionCourseEnd()
    {
        $this->layout = 'none';
        $uid = Yii::$app->user->getId();

        $courseService = new CourseService();
        $re = $courseService->teacherGetCourse($uid, 'end');


        return $this->render('courseend', [
            'course' => $re['course'],
            'page' => $re['page'],
            'now' => time()
        ]);
    }


    //编辑
    public function actionCourseJson()
    {
        $uid = Yii::$app->user->getId();
        $year = Yii::$app->request->getQueryParam('year');
        if (!$year) {
            $year = date('Y');
        }

        $courseService = new CourseService ();
        $yearCourse = $courseService->teacherGetYearCourse($uid, $year);

        $jsonArray = array();
        $nowtime = time();
        if (count($yearCourse) > 0) {
            foreach ($yearCourse as $c) {
                $temp = array();
                $temp['title'] = $c->course_name;
                $temp['start'] = $c->open_start_time ? date('Y-m-d', $c->open_start_time) : '';
                $temp['end'] = date('Y-m-d H:i:s', ($c->open_end_time + 86400));
                if ($c->open_status == LnCourse::COURSE_START) {
                    $temp['color'] = '#f56a40';
                } elseif ($c->open_status == LnCourse::COURSE_END) {
                    $temp['color'] = '#ccc';
                }
                $temp['url'] = Yii::$app->urlManager->createUrl(['teacher/detail', 'id' => $c->kid]);
                $jsonArray[] = $temp;
            }

        }

        print_r(json_encode($jsonArray));
        exit();

    }

    /**
     * 课程模块编辑及预览、组件添加
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionEdit($id)
    {
        $uid = Yii::$app->user->getId();
        $courseService = new CourseService ();
        $model = $courseService->teacherGetOneCourse($uid, $id);
        if (!$model || ($model->open_status == LnCourse::COURSE_END)) {
            header('location:' . Yii::$app->urlManager->createUrl(['/site/no-authority']));
            exit();
        }

        // 查询课程的域
        $domainIds = LnResourceDomain::find(false)
            ->andWhere(["resource_id" => $id, 'resource_type' => LnResourceDomain::RESOURCE_TYPE_COURSE, 'status' => LnResourceDomain::STATUS_FLAG_NORMAL])
            ->distinct('domain_id')
            ->select("domain_id")
            ->all();
        if (isset($domainIds) && $domainIds != null) {
            $domainIds = ArrayHelper::map($domainIds, 'domain_id', 'domain_id');
            $domainIds = array_keys($domainIds);
        }
        $domainIds = is_array($domainIds) ? join(',', $domainIds) : '';
        $service = new ResourceService();
        $modules = $service->getCourseMods($id, true, $domainIds, LnCourse::COURSE_TYPE_FACETOFACE);

        $componentService = new ComponentService();
        $is_setting_component = $componentService->getRecordScore();

        return $this->render('edit', [
            'model' => $model,
            'modules' => $modules,
            'domain_id' => $domainIds,
            'is_setting_component' => $is_setting_component,
        ]);
    }


    //详情
    public function actionDetail($id)
    {
        $uid = Yii::$app->user->getId();
        $courseService = new CourseService ();
        $courseModel = $courseService->teacherGetOneCourse($uid, $id);

        if (!$courseModel) {
            header('location:' . Yii::$app->urlManager->createUrl(['/site/no-authority']));
            exit();
        }

        $rating = number_format($courseService->getCourseMarkByID($id), 1);
        $rating_count = $courseService->getCourseMarkCountByID($id);

        /*获取课程证书*/
        $certificationModel = new LnCourseCertification();
        $certificationTemplatesUrl = $certificationModel->getTemplatesUrl($id);

//        if ($courseModel->course_type === LnCourse::COURSE_TYPE_ONLINE) {
//            $view = 'detail-online';
//        } elseif ($courseModel->course_type === LnCourse::COURSE_TYPE_FACETOFACE) {
        $view = 'detail';
//        }

        $isCourseOnline = $courseModel->course_type == LnCourse::COURSE_TYPE_ONLINE ? TRUE : FALSE;

        $enrollRegNumber = $courseService->getEnrollNumber($courseModel->kid, [LnCourseEnroll::ENROLL_TYPE_REG, LnCourseEnroll::ENROLL_TYPE_ALLOW]);

        return $this->render($view, ['courseModel' => $courseModel,
            'rating' => $rating,
            'rating_count' => $rating_count,
            'certsUrl' => $certificationTemplatesUrl,
            'isCourseOnline' => $isCourseOnline,
            'enrollRegNumber' => $enrollRegNumber,
        ]);
    }

    //发布课件
    public function actionRelease()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $uid = Yii::$app->user->getId();
            $cid = Yii::$app->request->post('cid');
            $mid = Yii::$app->request->post('mid');
            $resid = Yii::$app->request->post('resid');

            //检查课程是否存在
            $courseService = new CourseService ();
            $model = $courseService->teacherGetOneCourse($uid, $cid);
            if (!$model) {
                return ['result' => 'fail'];
            }

            //检查模块是否存在
            $courseMods = LnCourseMods::find(false)->andWhere(['course_id' => $cid, 'kid' => $mid])->orderBy('mod_num')->all();
            if (!$courseMods) {
                return ['result' => 'fail'];
            }

            // 检查资源课件是否存在
            $modRes = LnModRes::findOne($resid);
            if (!$modRes) {
                return ['result' => 'fail'];
            } else {
                $modRes->publish_status = LnModRes::YES;
                $modRes->save();

            }
            return ['result' => 'success'];
        }
    }

    public function actionCstatus()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $uid = Yii::$app->user->getId();
            $cid = Yii::$app->request->post('cid');
            $status = Yii::$app->request->post('status');

            //检查课程是否存在
            $courseService = new CourseService ();
            $model = $courseService->teacherGetOneCourse($uid, $cid);
            if (!$model) {
                return ['result' => 'fail', 'msg' => Yii::t('frontend', 'course_null')];
            }

            //    		$modSave = LnCourse::findOne($cid,false);
            if ($status == 'start') {
//                if ($model->enroll_end_time > time()) {
//                    return ['result' => 'fail', 'msg' => '早于报名截止时间，不能开课！'];
//                }
                $model->open_status = LnCourse:: COURSE_START;
                $model->save();

                /*更新时间轴状态*/
                $timelineService = new TimelineService();
                $timelineService->updateButtonType(null, $cid, MsTimeline::OBJECT_TYPE_COURSE, MsTimeline::BUTTON_TYPE_PROCESS);
            } else if ($status == 'end') {
                $model->open_status = LnCourse:: COURSE_END;
                $model->needReturnKey = true;
                $model->save();
                /*未审核全部设置成失败*/
                $courseService->updateCourseEnrollStatus($model->kid);

                /*更新时间轴状态*/
                $timelineService = new TimelineService();
                $timelineService->setComplete($cid, MsTimeline::OBJECT_TYPE_COURSE);

                //课程结束后，所有学员的完成状态自动为已完成
                $enrollResult = $courseService->getAllEnrollApprovedUser($cid);

                if (!empty($enrollResult) && count($enrollResult) > 0) {
                    $courseCompleteService = new CourseCompleteService();

                    foreach ($enrollResult as $enroll) {
                        $userId = $enroll->user_id;
                        $courseRegModel = $courseService->getUserRegInfo($userId, $cid);

                        if (!empty($courseRegModel)) {
                            $courseRegId = $courseRegModel->kid;
                            $courseFinalCompleteModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);
                            if (empty($courseFinalCompleteModel)) {
                                $courseCompleteService->initCourseCompleteInfo($courseRegId, $courseRegModel->course_id, $courseRegModel->user_id);
                                $courseFinalCompleteModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);
                            }

                            if (!empty($courseFinalCompleteModel)) {
                                $courseFinalCompleteId = $courseFinalCompleteModel->kid;
                                $courseCompleteService->addCourseCompleteDoneInfoForFaceToFace($courseFinalCompleteId);
                            }

                            $courseProcessCompleteModel = $courseCompleteService->getLastCourseCompleteNonDoneInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_PROCESS);
                            if (!empty($courseProcessCompleteModel)) {
                                $courseProcessCompleteId = $courseProcessCompleteModel->kid;
                                $courseCompleteService->addCourseCompleteDoneInfoForFaceToFace($courseProcessCompleteId);
                            }
                        }
                    }
                }
            }

            return ['result' => 'success', 'msg' => Yii::t('frontend', 'status_update_sucess')];

        }

    }


    //详情 介绍
    public function actionDetailIntro($id)
    {
        //$this->layout = 'none';
        $uid = Yii::$app->user->getId();
        $courseService = new CourseService ();
        $courseModel = $courseService->teacherGetOneCourse($uid, $id);

        if (!$courseModel) {
            exit();
        }

        $resourceService = new ResourceService();
        $courseMods = $resourceService->getCourseMods($id);

        return $this->renderAjax('detailIntro', [
            'courseModel' => $courseModel,
            'courseMods' => $courseMods,
        ]);
    }

    //详情 报名学员
    public function actionDetailCourseEnroll($id)
    {
        $this->layout = 'none';

        $uid = Yii::$app->user->getId();
        $courseService = new CourseService();
        $courseModel = $courseService->teacherGetOneCourse($uid, $id);
        if (!$courseModel) {
            exit();
        }

        $forceShowAll = 'False';
        if (Yii::$app->request->getQueryParam('PageShowAll') != null && Yii::$app->request->getQueryParam('PageShowAll') == 'True') {
            $forceShowAll = 'True';
        }

        $param['keyword'] = Yii::$app->request->getQueryParam('keyword');
        $param['sort'] = Yii::$app->request->getQueryParam('sort');
        $param['enroll_type'] = LnCourseEnroll::ENROLL_TYPE_ALLOW;
        $param['showAll'] = $forceShowAll;

        $courseService = new CourseService ();
        $result = $courseService->searchCourseEnroll($id, $param);

        return $this->renderAjax('detail-course-enroll', [
            'students' => $result['data'],
            'pages' => $result['pages'],
            'id' => $id,
            'param' => $param,
            'forceShowAll' => $forceShowAll,
        ]);
    }

    public function actionItemCompleteInfo($courseId, $modResId, $user_id = null)
    {
        $pageSize = $this->defaultPageSize;
        $courseService = new CourseService();

        $result = $courseService->GetEnrollUserList($courseId, $modResId, $user_id);

        $count = $result->count();
        $pages = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);

        $datas = $result->offset($pages->offset)->limit($pages->limit)->all();

        $modResModel = LnModRes::findOne($modResId);
        if (!empty($modResModel)) {
            $isCourseware = $modResModel->res_type == LnModRes::RES_TYPE_COURSEWARE ? true : false;
            if ($isCourseware) {
                $itemName = LnCourseware::findOne($modResModel->courseware_id)->courseware_name;
            } else {
                $itemName = LnCourseactivity::findOne($modResModel->courseactivity_id)->activity_name;

            }
        }

        return $this->renderAjax('item-complete-info', [
            'datas' => $datas,
            'pages' => $pages,
            'courseId' => $courseId,
            'modResId' => $modResId,
            'itemName' => $itemName
        ]);
    }

    //详情  颁发证书
    public function actionDetailCertification($id, $iframe = null)
    {
        $this->layout = 'none';
        $uid = Yii::$app->user->getId();
        $courseService = new CourseService ();
        $courseModel = $courseService->teacherGetOneCourse($uid, $id);
        if (!$courseModel) {
            exit();
        }

        //add by baoxianjian 15:06 2016/1/13 
        $canPushCertification = false;
        if ($courseModel->open_status == LnCourse::COURSE_START) {
            $canPushCertification = true;
        }

        $param['keyword'] = Yii::$app->request->getQueryParam('keyword');
        $param['sort'] = Yii::$app->request->getQueryParam('sort');
        $param['enroll_type'] = LnCourseEnroll::ENROLL_TYPE_ALLOW;

        $courseService = new CourseService ();
        $result = $courseService->searchCourseEnroll($id, $param);
        $courseCert = LnCourseCertification::findOne(['course_id' => $id]);
        $certification_id = $courseCert['certification_id'];

        if ($result['data']) {
            foreach ($result['data'] as &$v) {
                $userId = $v['user_id'];
                $courseRegModel = $courseService->getUserRegInfo($userId, $id);
                if (!empty($courseRegModel)) {
                    $courseRegId = $courseRegModel->kid;
                    $courseCompleteService = new CourseCompleteService();
                    $studyStatus = $courseCompleteService->isCourseCompleteOrRetake($courseRegId);
                    $v['studystatus'] = $studyStatus ? 1 : 0;
                } else {
                    $v['studystatus'] = 0;
                }

                $isGetCert = LnUserCertification::findOne(["certification_id" => $certification_id, "user_id" => $v['user_id']]);
                if ($isGetCert) {
                    if ($isGetCert->status == LnUserCertification::STATUS_FLAG_NORMAL) {
                        $v['iscert'] = 1;
                    } else {
                        $v['iscert'] = 2;
                    }
                } else {
                    $v['iscert'] = 0;
                }
            }
        }

        return $this->renderAjax('detailCertification', [
            'students' => $result['data'],
            'pages' => $result['pages'],
            'id' => $id,
            'param' => $param,
            'canPushCertification' => $canPushCertification,
            'iframe' => $iframe,
        ]);
    }

    //颁发证书
    public function actionPushCertification($id)
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $sid = Yii::$app->request->post('sid');

            $courseModel = LnCourse::findOne($id);
            if (!$courseModel) {
                return ['result' => 'fail', 'msg' => Yii::t('frontend', 'course_does_not_exist')];
            }

            //add by baoxianjian 14:08 2016/1/13
            if ($courseModel->open_status != LnCourse::COURSE_START) {
                return ['result' => 'fail', 'msg' => Yii::t('frontend', 'course_not_doing')];
            }

            $courseCert = LnCourseCertification::findOne(['course_id' => $id, 'status' => LnCourseCertification::STATUS_FLAG_NORMAL]);
            if (!$courseCert) {
                return ['result' => 'fail', 'msg' => Yii::t('frontend', 'course_no_certifi')];
            }

            $enrollResult = LnCourseEnroll::find(false)
                ->andFilterWhere(['=', 'course_id', $id])
                ->andFilterWhere(['=', 'user_id', $sid])
                ->andFilterWhere(['=', 'enroll_type', LnCourseEnroll::ENROLL_TYPE_ALLOW])
                ->one();

            if (!$enrollResult) {
                return ['result' => 'fail', 'msg' => Yii::t('frontend', 'enroll_no_course')];
            }

            $user[] = $sid;

            $certService = new CertificationService();

            $certService->teacherCertificationUsers($courseCert, $user, $id);

            return ['result' => 'success', 'msg' => Yii::t('frontend', 'issue_it_sucess')];

        }
    }

    //颁发证书
    public function actionPushCertificationAll($id)
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $courseService = new CourseService ();
            $courseModel = LnCourse::findOne($id);
            if (!$courseModel) {
                return ['result' => 'fail', 'msg' => Yii::t('frontend', 'course_does_not_exist')];
            }

            $courseCert = LnCourseCertification::findOne(['course_id' => $id, 'status' => LnCourseCertification::STATUS_FLAG_NORMAL]);
            if (!$courseCert) {
                return ['result' => 'fail', 'msg' => Yii::t('frontend', 'course_no_certifi')];
            }

            $user = array();
            $enrollResult = $courseService->getAllEnrollApprovedUser($id);
            if (!empty($enrollResult) && $enrollResult > 0) {
                foreach ($enrollResult as $enroll) {
                    $user[] = $enroll->user_id;
                }
            }

            if (!$user) {
                return ['result' => 'fail', 'msg' => Yii::t('frontend', 'enroll_no_one_course')];
            }

            $certService = new CertificationService();
            $certService->teacherCertificationUsers($courseCert, $user, $id);

            return ['result' => 'success', 'msg' => Yii::t('frontend', 'issue_it_sucess')];

        }
    }

    //详情   成绩
    public function actionDetailScore($id, $iframe = null, $header = 'hide', $showHomework = true)
    {
        $uid = Yii::$app->user->getId();
        $courseService = new CourseService ();
        $courseModel = $courseService->teacherGetOneCourse($uid, $id);
        if (!$courseModel) {
            exit();
        }
        $forceShowAll = 'False';
        if (Yii::$app->request->getQueryParam('PageShowAll') != null && Yii::$app->request->getQueryParam('PageShowAll') == 'True') {
            $forceShowAll = 'True';
        }
        $params['keyword'] = Yii::$app->request->getQueryParam('keyword');
        $params['sort'] = Yii::$app->request->getQueryParam('sort');
        $params['enroll_type'] = LnCourseEnroll::ENROLL_TYPE_ALLOW;
        $params['showAll'] = $forceShowAll;

        // 计算课程之间的天数
        /*$courseDay = 0;
        if (($courseModel->open_start_time) < ($courseModel->open_end_time)) {
            $timeMarker = strtotime(date('Y-m-d', $courseModel->open_end_time)) - strtotime(date('Y-m-d', $courseModel->open_start_time));
            $courseDay = $timeMarker / (24 * 3600) + 1;
        }*/

        $courseDay = 0;
        $courseService = new CourseService ();
        if ($courseModel->course_type == LnCourse::COURSE_TYPE_FACETOFACE) {
            /*课程报名数据*/
            $enrollService = new CourseEnrollService();
            $result = $enrollService->searchCourseEnroll($id, $params);
            /*获取签到设置*/
            $signInService = new CourseSignInSettingService();
            $courseDay = $signInService->getCourseSignInSettingTimes($id);
        } else {
            $result = $courseService->searchCourseReg($id, $params);
        }
        if ($result['data']) {
            foreach ($result['data'] as &$v) {
                $signCount = LnCourseSignIn::find(false)
                    ->andWhere(['course_id' => $id, 'user_id' => $v['user_id']])
                    ->count();

                $v['signcount'] = $signCount;
                $v['signall'] = $courseDay;
                /*在线增加审批功能*/
                if ($courseModel->course_type == LnCourse::COURSE_TYPE_ONLINE && $courseModel->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT) {
                    $courseReg = $courseService->getUserRegInfo($v['user_id'], $id);
                    if (!empty($courseReg) && $courseReg->reg_state == LnCourseReg::REG_STATE_APPLING) {
                        $v['approval'] = 1;/*需要审批*/
                    } else {
                        $v['approval'] = 0;
                    }
                } else {
                    $v['approval'] = 0;
                }
            }
        }


        return $this->renderAjax('detailScore', [
            'model' => $courseModel,
            'students' => $result['data'],
            'pages' => $result['pages'],
            'id' => $id,
            'param' => $params,
            'iframe' => $iframe,
            'header' => $header,
            'showHomework' => $showHomework,
            'ShowAll' => $forceShowAll,
        ]);
    }

    //详情 个人成绩
    public function actionDetailScorePerson($id, $iframe = null, $header = 'hide', $showHomework = true)
    {
        $userId = Yii::$app->request->get('userId');
        $user = FwUser::findOne($userId);
        $courseService = new CourseService ();
        $courseModel = $courseService->teacherGetOneCourse($userId, $id);
        if (!$courseModel || !$userId) {
            exit();
        }
        $resourceService = new ResourceService();
        $result = $resourceService->getCourseResDetail($id, $userId);

        /*获取计分课件*/
        $resourceService = new ResourceService();
        $scoreComponentCount = $resourceService->GetCourseScoreCount($id);

        return $this->renderAjax('detailScorePerson', [
            'courseModel' => $courseModel,
            'courseRes' => $result['data'],
            'pages' => $result['pages'],
            'total_score' => $result['total_score'],
            'user' => $user,
            'userId' => $userId,
            'iframe' => $iframe,
            'header' => $header,
            'scoreComponentCount' => intval($scoreComponentCount),
            'showHomework' => $showHomework,
        ]);
    }

    //详情 证书
    public function actionDetailCourseCert($id)
    {
        $this->layout = 'none';
        $uid = Yii::$app->user->getId();

        $courseService = new CourseService ();
        $courseModel = $courseService->teacherGetOneCourse($uid, $id);

        if (!$courseModel) {
            exit();
        }

        /*获取课程证书*/
        $certificationModel = new LnCourseCertification();
        $certificationTemplatesUrl = $certificationModel->getTemplatesUrl($courseModel->kid);

        return $this->render('detailCourseCert', [
            'certificationTemplatesUrl' => $certificationTemplatesUrl,

        ]);
    }

    //详情 讲师
    public function actionDetailCourseTeacher($id)
    {
        $this->layout = 'none';
        $uid = Yii::$app->user->getId();

        $courseService = new CourseService ();
        $courseModel = $courseService->teacherGetOneCourse($uid, $id);

        if (!$courseModel) {
            exit();
        }

        /*获取课程讲师*/
        $teacherModel = new LnCourseTeacher();
        $teacher = $teacherModel->getTeacherAll($courseModel->kid);
        //print_r($courseRes) ;
        return $this->render('detailCourseTeacher', [
            'teacher' => $teacher,
        ]);
    }

    //详情 讲师
    public function actionDetailCourseAnswer($id, $preview = 0)
    {
        $courseId = $id;
        $this->layout = false;
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
        return $this->render('detailCourseAnswer', [
            'result' => $result,
            'soUserAttention' => $soUserAttention,
            'pages' => $pages,
            'pageNo' => $pageNo,
            'courseId' => $courseId,
            'preview' => $preview,
        ]);
    }

    public function actionPlayCourse($modResId, $scoId = null)
    {
        $modRes = LnModRes::findOne($modResId);

        $courseCompleteProcessId = "";
        $courseCompleteFinalId = "";
        $attempt = "1";

        $component = LnComponent::findOne($modRes->component_id);
        $componentCode = $component->component_code;

        if ($modRes->res_type == LnModRes::RES_TYPE_COURSEWARE) {
            $threeModel = LnCourseware::findOne($modRes->courseware_id);
            $resourceName = $threeModel->courseware_name;
            $scormService = new ScormService();
            if ($scormService->isScormComponent($componentCode) && empty($scoId)) {
                $scorm = $scormService->getScormByCoursewareId($modRes->courseware_id);
                $scoId = "";
                if (empty($scoId)) {
                    $scoId = $scorm->launch_scorm_sco_id;
                }
            }
        } else {
            $threeModel = LnCourseactivity::findOne($modRes->courseactivity_id);
            $objectType = $threeModel->object_type;
            if ($objectType == 'investigation') {
                $activityModel = LnInvestigation::findOne($threeModel->object_id);
                $componentCode .= '-preview';
            } else if ($objectType == 'examination') {
                $activityModel = LnExamination::findOne($threeModel->object_id);
            } else if ($objectType == 'homework') {
                $activityModel = LnHomework::findOne($threeModel->object_id);
            }
            $resourceName = $activityModel->title;
        }

        return $this->renderAjax('playCourse', [
            'resourceName' => $resourceName,
            'componentCode' => $componentCode,
            'courseId' => $modRes->course_id,
            'modResId' => $modResId,
            'scoId' => $scoId,
            'attempt' => $attempt,
            'courseCompleteFinalId' => $courseCompleteFinalId,
            'courseCompleteProcessId' => $courseCompleteProcessId
        ]);
    }

    public function actionHomeworkPlayerelse($courseId = null, $courseRegId = null, $modResId = null, $coursewareId = null, $courseCompleteFinalId = null, $courseCompleteProcessId = null, $attempt = "1", $mode = self::PLAY_MODE_PREVIEW, $isMobile = null)
    {
        if (isset($_REQUEST['user_id'])) {
            $user_id = $_REQUEST['user_id'];
        } else {
            $user_id = Yii::$app->user->getId();
        }

        if (Yii::$app->request->isAjax && (!empty($modResId) || !empty($coursewareId))) {
            if (!empty($modResId)) {
                $modResModel = LnModRes::findOne($modResId);
                $coursewareId = $modResModel->courseactivity_id;
            }

            $componentCode = "investigation";

            $attempt = Yii::$app->request->getQueryParam('attempt');

            $courseactivityModel = LnCourseactivity::findOne($coursewareId);

            if ($isMobile === null) {
                if (Yii::$app->session->has("isMobile")) {
                    $isMobile = Yii::$app->session->get("isMobile");
                    $browserName = Yii::$app->session->get("browserName");
                } else {
                    $isMobile = false;
                }
            } else {
                $isMobile == 1 ? true : false;
            }

            if (!$isMobile && $courseactivityModel->is_display_pc == LnCourseactivity::DISPLAY_PC_NO) {
                return $this->renderAjax('none-player', [
                    'modResId' => $modResId,
                    'mode' => $mode,
                    'courseId' => $courseId,
                    'coursewareId' => $coursewareId,
                    'attempt' => $attempt,
                    'courseCompleteProcessId' => $courseCompleteProcessId,
                    'courseCompleteFinalId' => $courseCompleteFinalId,
                    'componentCode' => $componentCode,
                    'errorClient' => 'pc'
                ]);
            } else if ($isMobile && $courseactivityModel->is_display_mobile == LnCourseactivity::DISPLAY_MOBILE_NO) {
                return $this->renderAjax('none-player', [
                    'modResId' => $modResId,
                    'mode' => $mode,
                    'courseId' => $courseId,
                    'coursewareId' => $coursewareId,
                    'attempt' => $attempt,
                    'courseCompleteProcessId' => $courseCompleteProcessId,
                    'courseCompleteFinalId' => $courseCompleteFinalId,
                    'componentCode' => $componentCode,
                    'errorClient' => 'mobile'
                ]);
            } else {
                $investigationService = new InvestigationService();
                $homework = $investigationService->getHomeworkInfoByModResId($modResId);
                $uploadBatch = date("YmdHis");
                $model = !empty($id) ? LnHomework::findOne($id) : new LnHomework();
                $teacherfiles = array();
                if (!empty($homework['kid'])) {
                    $teacherfiles = LnHomeworkFile::findAll(['homework_id' => $homework['kid'], 'homework_file_type' => '0'], false);
                }
                $studentfiles = array();
                if (!empty($homework['kid'])) {
                    $studentfiles = LnHomeworkFile::findAll(['homework_id' => $homework['kid'], 'homework_file_type' => '1', 'user_id' => $user_id, 'course_complete_id' => $courseCompleteProcessId], false);
                }
                $resCompleteId = LnResComplete::find(false)->andFilterWhere(['course_complete_id' => $courseCompleteFinalId, 'course_id' => $courseId, 'user_id' => $user_id, 'mod_id' => $homework['mod_id'], 'mod_res_id' => $modResId, 'courseactivity_id' => $coursewareId, 'complete_type' => '1'])->one()->kid;//->createCommand()->getRawSql();
                $completetype = LnResComplete::find(false)->andFilterWhere(['course_complete_id' => $courseCompleteFinalId, 'course_id' => $courseId, 'user_id' => $user_id, 'mod_id' => $homework['mod_id'], 'mod_res_id' => $modResId, 'courseactivity_id' => $coursewareId, 'complete_type' => '1'])->one()->complete_status;//->createCommand()->getRawSql();
                $homeworkresult = LnHomeworkResult::findOne(['user_id' => $user_id, 'homework_id' => $homework['kid'], 'mod_res_id' => $modResId,], false);

                $view = $completetype;
                if (empty($courseCompleteFinalId)) {
                    $view = 2;
                }
                return $this->renderAjax('homework-result-one', [
                    'uploadBatch' => $uploadBatch,
                    "id" => $homework['kid'],
                    "modResId" => $modResId,
                    "courseId" => $courseId,
                    "courseactivityId" => $coursewareId,
                    "courseCompleteFinalId" => $courseCompleteFinalId,
                    "resCompleteId" => $resCompleteId,
                    "courseCompleteProcessId" => $courseCompleteProcessId,
                    "mod_id" => $homework['mod_id'],
                    "course_reg_id" => $courseRegId,
                    'attempt' => $attempt,
                    "courseactivity_id" => $homework['courseactivity_id'],
                    "component_id" => $homework['component_id'],
                    'result' => $homework,
                    'teacherfiles' => $teacherfiles,
                    'studentfiles' => $studentfiles,
                    'componentCode' => $componentCode,
                    "mode" => $mode,
                    'homeworkresult' => $homeworkresult,
                    "view" => $view,
                ]);
            }
        }
    }

    /* ========================== 成绩查看 ========================== */
    /**
     * Scorm成绩查看
     */
    public function actionScormResult()
    {
        $params = Yii::$app->request->getQueryParams();
        $params['defaultPageSize'] = $this->defaultPageSize;

        $resourceCompleteService = new ResourceCompleteService();
        $result = $resourceCompleteService->getResCompleteData($params['modResId'], $params);

        $itemName = LnCourseware::findOne($params['itemId'])->courseware_name;

        return $this->renderAjax('scorm-result', [
            'datas' => $result['data'],
            'pages' => $result['pages'],
            'courseId' => $params['courseId'],
            'itemId' => $params['itemId'],
            'modResId' => $params['modResId'],
            'itemName' => $itemName,
            'componentCode' => $params['componentCode'],
            'params' => $params,
        ]);
    }

    /**
     * Aicc成绩查看
     */
    public function actionAiccResult()
    {
        $params = Yii::$app->request->getQueryParams();
        $params['defaultPageSize'] = $this->defaultPageSize;

        $resourceCompleteService = new ResourceCompleteService();
        $result = $resourceCompleteService->getResCompleteData($params['modResId'], $params);

        $itemName = LnCourseware::findOne($params['itemId'])->courseware_name;

        return $this->renderAjax('aicc-result', [
            'datas' => $result['data'],
            'pages' => $result['pages'],
            'courseId' => $params['courseId'],
            'itemId' => $params['itemId'],
            'modResId' => $params['modResId'],
            'itemName' => $itemName,
            'componentCode' => $params['componentCode'],
            'params' => $params,
        ]);
    }

    /*------------------------投票调查 start----------------------------------*/
    /**
     * Investigation成绩查看
     */
    public function actionInvestigationResult()
    {
        $courseId = isset($_REQUEST['courseId']) ? $_REQUEST['courseId'] : '';
        $modResId = isset($_REQUEST['modResId']) ? $_REQUEST['modResId'] : '';
        $itemId = isset($_REQUEST['itemId']) ? $_REQUEST['itemId'] : '';
        $InvestigationService = new InvestigationService();
        $data = $InvestigationService->getInvestigation($itemId);

        if ($data['investigation_type'] == 1) {
            foreach ($data['question'][0]['option'] as $k => $v) {
                $count[$v["kid"]] = $InvestigationService->getVotecount($v['kid'], $courseId, $modResId);
            }
        }
        $sumCount = $InvestigationService->getVoteuser($courseId, $modResId);;

        if ($sumCount == 0) $sumCount = 1;

        if (isset($count)) {
            return $this->renderAjax('investigation-result', [
                'data' => $data,
                'count' => $count,
                'sumcount' => $sumCount,
                'courseid' => $courseId,
                'modresid' => $modResId,
                'itemId' => $itemId
            ]);
        } else {
            return $this->renderAjax('investigation-result', [
                'data' => $data,
                'courseid' => $courseId,
                'modresid' => $modResId,
                'itemId' => $itemId
            ]);
        }

    }

    //获取投票结果
    public function actionGetVoteResult()
    {
        $courseId = isset($_REQUEST['courseId']) ? $_REQUEST['courseId'] : '';
        $modResId = isset($_REQUEST['modResId']) ? $_REQUEST['modResId'] : '';
        $itemId = isset($_REQUEST['itemId']) ? $_REQUEST['itemId'] : '';
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : LnInvestigation::ANSWER_TYPE_REALNAME;
        $category = isset($_REQUEST['category']) ? $_REQUEST['category'] : '';
        $keywords = Yii::$app->request->get('keywords');
        $status = Yii::$app->request->get('status');
        $defaultPageSize = $this->defaultPageSize;
        $InvestigationService = new InvestigationService();
        $res = $InvestigationService->getVoteResultComplete($itemId, $courseId, $modResId, $type, $defaultPageSize, $status, $keywords);

        return $this->renderAjax('vote-result', [
            'data' => $res['data'],
            'type' => $type,
            'pages' => $res['pages'],
            'category' => $category,
            'courseid' => $courseId,
            'modresid' => $modResId,
            'inkid' => $itemId,
            'status' => $status,
            'keywords' => $keywords,
        ]);
    }

    //获取调查详情
    public function actionQuestionaireResult()
    {
        $itemId = isset($_REQUEST['itemId']) ? $_REQUEST['itemId'] : '';
        $userId = isset($_REQUEST['userId']) ? $_REQUEST['userId'] : '';
        $courseId = isset($_REQUEST['courseId']) ? $_REQUEST['courseId'] : '';
        $modResId = isset($_REQUEST['modResId']) ? $_REQUEST['modResId'] : '';
        $target = isset($_REQUEST['target']) ? $_REQUEST['target'] : null;

        $InvestigationService = new InvestigationService();
        $result = $InvestigationService->getQuestionairedetail($itemId, $userId, $courseId, $modResId);
        $resultdata = array();
        if (!empty($result)) {
            foreach ($result as $k => $v) {
                $option[$k]['investigation_question_id'] = $v['investigation_question_id'];
                $option[$k]['investigation_option_id'] = $v['investigation_option_id'];
                $resultdata[$v['investigation_question_id']] = $v;
            }

            foreach ($option as $key => $val) {
                $resultdata[$val['investigation_question_id']]['sequence_number'][] = LnInvestigationOption::findOne($val['investigation_option_id'])->sequence_number;
            }
        }

        $data = $InvestigationService->getInvestigation($itemId);

        if ($target) {
            return $this->render('questionaire-result', [
                'data' => $data,
                'resultdata' => $resultdata,
                'courseid' => $courseId,
                'modresid' => $modResId,
                'inkid' => $itemId,
                'target' => $target,
            ]);
        }else{
            return $this->renderAjax('questionaire-result', [
                'data' => $data,
                'resultdata' => $resultdata,
                'courseid' => $courseId,
                'modresid' => $modResId,
                'inkid' => $itemId
            ]);
        }
    }

    /*------------------------投票调查 end----------------------------------*/

    /**
     * 考试列表
     * @return string
     */
    public function actionExaminationResult()
    {
        $params = Yii::$app->request->getQueryParams();
        $courseId = Yii::$app->request->get('courseId');
        $modResId = Yii::$app->request->get('modResId');
        $object_id = Yii::$app->request->get('itemId');
        $params['defaultPageSize'] = $this->defaultPageSize;
        $courseActivity = LnCourseactivity::find(false)->andFilterWhere(['course_id' => $courseId, 'mod_res_id' => $modResId, 'object_id' => $object_id])->one();
        $resourceCompleteService = new ResourceCompleteService();
        $result = $resourceCompleteService->getResCompleteData($modResId, $params);
        if (!empty($result['data'])) {
            $data = array();
            $examService = new ExaminationService();
            foreach ($result['data'] as $key => $items) {
                //if ($items->complete_status == LnResComplete::COMPLETE_STATUS_DONE){
                $items['score_before'] = $examService->GetExaminationGrade($items['kid'], $object_id, $courseId, $modResId, $courseActivity->mod_id);
                // }
                $data[$key] = $items;
            }
            $result['data'] = $data;
        }
        $model = LnExamination::findOne($object_id);

        return $this->renderAjax('examination-result', [
            'courseId' => $courseId,
            'modResId' => $modResId,
            'mod_id' => $courseActivity->mod_id,
            'object_id' => $courseActivity->kid,
            'examination_id' => $object_id,
            'data' => $result['data'],
            'model' => $model,
            'page' => $result['pages'],
            'params' => $params,
            'componentCode' => $params['componentCode'],
        ]);
    }

    /**
     * Homework成绩查看
     */
    public function actionHomeworkResult()
    {
        $params = Yii::$app->request->getQueryParams();
        $courseId = Yii::$app->request->get('courseId');
        $modResId = Yii::$app->request->get('modResId');
        $object_id = Yii::$app->request->get('itemId');
        $params['defaultPageSize'] = $this->defaultPageSize;
        $params['score_status'] = Yii::$app->request->get('score_status');
        $params['keyword'] = Yii::$app->request->get('keyword');

        $resourceCompleteService = new ResourceCompleteService();
        $result = $resourceCompleteService->getResCompleteData($params['modResId'], $params);
        $model = LnHomework::findOne($object_id);

        return $this->renderAjax('homework-result', [
            'courseId' => $courseId,
            'modResId' => $modResId,
            'itemId' => $object_id,
            'data' => $result['data'],
            'model' => $model,
            'page' => $result['pages'],
            'params' => $params,
            'componentCode' => $params['componentCode']
        ]);
    }

    /**
     * 查看作业
     * @return string
     */
    public function actionHomeworkPlayer()
    {
        $itemId = isset($_REQUEST['itemId']) ? $_REQUEST['itemId'] : '';
        $userId = isset($_REQUEST['userId']) ? $_REQUEST['userId'] : '';
        $courseId = isset($_REQUEST['courseId']) ? $_REQUEST['courseId'] : '';
        $modResId = isset($_REQUEST['modResId']) ? $_REQUEST['modResId'] : '';
        $companyId = Yii::$app->user->identity->company_id;

        if (empty($userId)) {
            $userId = Yii::$app->user->getId();
        }
        $InvestigationService = new InvestigationService();
        $homework = $InvestigationService->getHomeworkInfoByModResId($modResId);
        $HomeworkService = new HomeworkService();
        $homeworkResult = $HomeworkService->getUserHomeworkResult($userId, $courseId, $homework['mod_id'], $modResId, $companyId, $itemId);
        return $this->renderAjax('homework-result-one', [
            'title' => $homework['title'],
            'result' => $homeworkResult
        ]);
    }

    /**
     * 其它组件
     * @return string
     */
    public function actionOtherResult()
    {
        $params = Yii::$app->request->getQueryParams();
        $params['defaultPageSize'] = $this->defaultPageSize;
        $params['score_status'] = Yii::$app->request->get('score_status');
        $params['keyword'] = Yii::$app->request->get('keyword');

        $resourceCompleteService = new ResourceCompleteService();
        $result = $resourceCompleteService->getResCompleteData($params['modResId'], $params);

        $modResModel = LnModRes::findOne($params['modResId']);
        $itemName = "";
        if (!empty($modResModel)) {
            $isCourseware = $modResModel->res_type == LnModRes::RES_TYPE_COURSEWARE ? true : false;
            if ($isCourseware) {
                $itemName = LnCourseware::findOne($modResModel->courseware_id)->courseware_name;
            } else {
                $itemName = LnCourseactivity::findOne($modResModel->courseactivity_id)->activity_name;
            }
        }


        return $this->renderAjax('other-result', [
            'datas' => $result['data'],
            'pages' => $result['pages'],
            'courseId' => $params['courseId'],
            'modResId' => $params['modResId'],
            'itemName' => $itemName,
            'componentCode' => 'other',
            'params' => $params,
        ]);
    }

    /**
     * 成绩管理
     * @param $id
     */
    public function actionDetailGrade($id){
        $params = Yii::$app->request->getQueryParams();
        $params['defaultPageSize'] = $this->defaultPageSize;
        $page = Yii::$app->request->get('page');
        $iframe = Yii::$app->request->get('iframe');
        if (!isset($params['full'])){
            $params['full'] = 'False';
        }
        /*获取课程证书*/
        $courseCertificationService = new CourseCertificationService();
        $certification = $courseCertificationService->checkHasCourseCertification($id);
        $params['certification'] = $certification;
        $courseService = new CourseService();
        $result = $courseService->getCourseGrade($id, $params);
        return $this->renderAjax('detailGrade', [
            'id' => $id,
            'result' => $result,
            'page' => $page,
            'iframe' => $iframe,
            'params' => $params,
            'certification' => $certification,
        ]);
    }

    /**
     * 设置学员学习是否及格
     * @param $id
     * @return array
     */
    public function actionSetCoursePass($id){
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (empty($id)) return ['result' => 'fail', 'errmsg' => Yii::t('common', 'param_{value}_error')];
        $pass = Yii::$app->request->post('pass');
        $user = Yii::$app->request->post('user');
        if (empty($user)) return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'please_select_handle_object')];
        $courseService = new CourseCompleteService();
        $res = $courseService->setCourseCompletePassStatus($id, $pass, $user);
        return $res;
    }

    /**
     * 颁发证书与取消证书
     * @param $id
     * @return array
     */
    public function actionSetCourseCertification($id){
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (empty($id)) return ['result' => 'fail', 'errmsg' => Yii::t('common', 'param_{value}_error')];
        $courseCertificationService = new CourseCertificationService();
        $courseCert = $courseCertificationService->checkHasCourseCertification($id);
        if (!$courseCert) {
            return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'tip_for_no_credentialstip')];
        }
        $certification = Yii::$app->request->post('certification');
        $user = Yii::$app->request->post('user');
        if (empty($user)) return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'please_select_handle_object')];
        /*颁发*/
        if ($certification == LnUserCertification::IS_ISSUE_YES) {
            $certService = new CertificationService();
            $certService->teacherCertificationUsers($courseCert, $user, $id);
            return ['result' => 'success', 'errmsg' => ''];
        }else{
            /*取消*/
            $return = $courseCertificationService->cancelUserCourseCertification($id, $user);
            return $return;
        }
    }
}