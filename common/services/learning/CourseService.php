<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/4
 * Time: 15:03
 */

namespace common\services\learning;

use common\base\BaseActiveRecord;
use common\models\framework\FwCompany;
use common\models\framework\FwTag;
use common\models\framework\FwTagReference;
use common\models\framework\FwUserManager;
use common\models\framework\FwUserDisplayInfo;
use common\models\learning\LnComponent;
use common\models\learning\LnCourseactivity;
use common\models\learning\LnCourseEnroll;
use common\models\learning\LnCourseMark;
use common\models\learning\LnCourseMods;
use common\models\learning\LnCourseRecommend;
use common\models\learning\LnCourseware;
use common\models\learning\LnCoursewareBook;
use common\models\learning\LnHomework;
use common\models\learning\LnHomeworkFile;
use common\models\learning\LnResComplete;
use common\models\learning\LnResourceAudience;
use common\models\learning\LnResourceDomain;
use common\models\learning\LnTrainingAddress;
use common\models\message\MsMessage;
use common\models\message\MsMessageUser;
use common\services\common\MailService;
use common\services\framework\DictionaryService;
use common\services\framework\ExternalSystemService;
use common\services\framework\TagService;
use common\services\framework\WechatService;
use common\services\framework\WechatTemplateService;
use common\services\framework\UserDomainService;
use common\services\framework\PointRuleService;
use common\helpers\TArrayHelper;
use common\helpers\TStringHelper;
use common\helpers\TTimeHelper;
use common\helpers\TURLHelper;
use common\services\message\TaskService;
use common\services\message\TimelineService;
use common\traits\HelperTrait;
use common\traits\ResponseTrait;
use components\widgets\TPagination;
use yii;
use common\models\framework\FwUser;
use common\models\framework\FwDictionaryCategory;
use common\models\framework\FwDictionary;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnCourseReg;
use common\models\learning\LnModRes;
use common\models\learning\LnCourseOwner;
use common\models\learning\LnTeacher;
use common\models\learning\LnCourseTeacher;
use common\models\learning\LnCourseCertification;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use common\eLearningLMS;
use yii\helpers\ArrayHelper;
use yii\db\Expression;
use common\models\learning\LnCourseSignIn;
use common\models\framework\FwUserSpecialApprover;
use common\services\framework\UserService;
use common\services\framework\ApprovalFlowService;

class CourseService extends LnCourse
{
    const PLAY_MODE_NORMAL = 'normal';
    const PLAY_MODE_PREVIEW = 'preview';

    use ResponseTrait, HelperTrait;

    /**
     * 课程列表与查询
     * @param $params
     * @return ActiveDataProvider
     */
    public function Search($params)
    {

        $query = LnCourse::find(false);
        $domain = [];
        $domain_id = isset($params['domain_id']) && $params['domain_id'] ? $params['domain_id'] : "";
        if (isset($params['TreeNodeKid']) && $params['TreeNodeKid']) {
            $courseCategoryService = new CourseCategoryService();
            // $categories = $courseCategoryService->getCategoriesByTreeNode($params['TreeNodeKid']);
            $categories = $courseCategoryService->getCourseCategoryIdByTreeNodeId($params['TreeNodeKid']);
            if ($categories) {
                $query->andFilterWhere(['=', 'category_id', $categories]);
            }
        }
        if (empty($domain_id)) {
            $userId = \Yii::$app->user->getId();
            $userDomainService = new UserDomainService();
            $GetSearchListByUserId = $userDomainService->getManagedListByUserId($userId);
            if ($GetSearchListByUserId) {
                foreach ($GetSearchListByUserId as $t) {
                    $domain[] = $t->kid;
                }
            }
        } else {
            $domain[] = $domain_id;
        }

        if (isset($params['visable']) && $params['visable'] != "") {
            $query->andFilterWhere([$params['visable'] => '1']);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

//        if (!$this->validate()) {
//            return $dataProvider;
//        }

        if (!empty($domain)) {

            $query->innerJoinWith('lnResourceDomains')
                ->andFilterWhere(['in', LnResourceDomain::tableName() . '.domain_id', $domain])
                ->andFilterWhere(['=', LnResourceDomain::tableName() . '.resource_type', LnResourceDomain::RESOURCE_TYPE_COURSE])
                ->andFilterWhere(['=', LnResourceDomain::tableName() . '.status', LnResourceDomain::STATUS_FLAG_NORMAL])
                ->distinct();
        } else {
            $query->andWhere('kid is null');
        }

        if (!empty($this->course_name)) {
            $keywords = TStringHelper::clean_xss($this->course_name);
            $query->andWhere("course_code like '%{$keywords}%' OR course_name like '%{$keywords}%' OR course_desc_nohtml like '%{$keywords}%'");
        }
        if (isset($params['course_type'])) {
            $query->andFilterWhere(['=', 'course_type', $params['course_type']]);
        }
        $dataProvider->setSort(false);
        $query->addOrderBy([LnCourse::tableName() . '.created_at' => SORT_DESC]);
        /* echo ($query->createCommand()->getRawSql());*/
        return $dataProvider;
    }

    /**
     * 根据字典分类与值获取字典详细信息
     * @return string
     */
    public function getDictionaryText($cate_code, $val)
    {
        if (empty($cate_code)) return;
        $dictionaryCategoryService = new FwDictionaryCategory();
        $findOne = $dictionaryCategoryService->findOne(['cate_code' => $cate_code], false);
        if (empty($findOne)) return;
        $dictionaryService = new FwDictionary();
        $valInfo = $dictionaryService->findOne(['dictionary_category_id' => $findOne->kid, 'dictionary_value' => $val], false);
        if (empty($valInfo)) return;
        return $valInfo->dictionary_name;
    }

    /**
     * 取得最新的几条课程
     * @param int $size 返回记录条数
     * @param bool $isMobile 是否移动端
     * @param array $filterKids 排除Kid列表
     * @return array|yii\db\ActiveRecord[]
     */
    public function getNewCoursesList($size, $isMobile = false, $filterKids = null)
    {
        $currentTime = time();

        $userId = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;

        $userDomainService = new UserDomainService();
        $domainIds = $userDomainService->getSearchListByUserId($userId);

        if (isset($domainIds) && $domainIds != null) {
            $domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');

            $domainIds = array_keys($domainIds);
        }

        $domainQuery = LnResourceDomain::find(false);
        $domainQuery->select('resource_id')
            ->andFilterWhere(['in', 'domain_id', $domainIds])
            ->andFilterWhere(['=', 'status', LnResourceDomain::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'resource_type', LnResourceDomain::RESOURCE_TYPE_COURSE])
            ->distinct();

        $domainQuerySql = $domainQuery->createCommand()->rawSql;

        $tableName = LnCourse::tableName();
        // 按课程受众过滤
        $audienceFilterSql = "NOT EXISTS(SELECT kid FROM eln_ln_resource_audience WHERE `status`='1' and resource_type='1' and resource_id=$tableName.kid and company_id='$companyId') OR" .
            " EXISTS(SELECT sam.kid FROM eln_ln_resource_audience ra INNER JOIN eln_so_audience sa ON ra.`status`='1' and ra.resource_type='1' and ra.company_id='$companyId' and ra.audience_id=sa.kid " .
            "and ra.is_deleted='0' and sa.`status`='1' and sa.company_id='$companyId' and sa.is_deleted='0' LEFT JOIN eln_so_audience_member sam ON sa.kid=sam.audience_id and sa.is_deleted='0' and sam.is_deleted='0' " .
            "WHERE $tableName.kid=ra.resource_id AND sam.user_id='$userId')";

        $courseQuery = LnCourse::find(false);
        $courseQuery
            ->andWhere($audienceFilterSql)
            ->andWhere(LnCourse::tableName() . '.kid in (' . $domainQuerySql . ')')
            ->andFilterWhere(['=', LnCourse::tableName() . '.status', LnCourse::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['or', ['>=', 'end_time', $currentTime], 'end_time is null'])
            ->andFilterWhere(['or', ['<=', 'start_time', $currentTime], 'start_time is null'])
            ->andFilterWhere(['or', ['>=', 'open_end_time', $currentTime], 'open_end_time is null']);

        if ($isMobile) {
            $courseQuery->andFilterWhere(['=', 'is_display_mobile', LnCourse::DISPLAY_MOBILE_YES]);
        } else {
            $courseQuery->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES]);
        }

        if ($filterKids) {
            $courseQuery->andFilterWhere(['not in', 'kid', $filterKids]);
        }

        $result = $courseQuery
            ->orderBy(LnCourse::tableName() . '.release_at desc')
            ->limit($size)
            ->all();

        return $result;
    }


    /**
     * 取得最新的几条课程
     * @param $limit 返回记录条数
     * @param $offset 分页偏移量
     * @return mixed
     */
    public function getNewCourses($limit, $offset, $isMobile = false)
    {
        $currentTime = time();

        $user_id = Yii::$app->user->getId();

        $userDomainService = new UserDomainService();
        $domainIds = $userDomainService->getSearchListByUserId($user_id);

        if (isset($domainIds) && $domainIds != null) {
            $domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');

            $domainIds = array_keys($domainIds);
        }

        $domainQuery = LnResourceDomain::find(false);
        $domainQuery->select('resource_id')
            ->andFilterWhere(['in', 'domain_id', $domainIds])
            ->andFilterWhere(['=', 'status', LnResourceDomain::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'resource_type', LnResourceDomain::RESOURCE_TYPE_COURSE])
            ->distinct();

        $domainQuerySql = $domainQuery->createCommand()->rawSql;

        $courseQuery = LnCourse::find(false);
        $courseQuery
            ->andWhere(LnCourse::tableName() . '.kid in (' . $domainQuerySql . ')')
            ->andFilterWhere(['=', LnCourse::tableName() . '.status', LnCourse::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['or', ['>=', 'end_time', $currentTime], 'end_time is null'])
            ->andFilterWhere(['or', ['<=', 'start_time', $currentTime], 'start_time is null']);

        if ($isMobile) {
            $courseQuery->andFilterWhere(['=', 'is_display_mobile', LnCourse::DISPLAY_MOBILE_YES]);
        } else {
            $courseQuery->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES]);
        }

        $courseQuery
            ->leftJoin(FwUser::tableName() . ' u', LnCourse::tableName() . '.created_by = u.kid')
            ->select([LnCourse::tableName() . '.*', 'editor' => 'u.real_name'])
            ->orderBy(LnCourse::tableName() . '.release_at desc');

        $result = $courseQuery
            ->limit($limit)
            ->offset($offset)
            ->all();

        return $result;
    }

    /*获取我的课程*/
    public function getMyCourse($uid)
    {
        $query = new Query();
        $result = $query
            ->from(LnCourseReg::tableName() . ' reg')
            ->leftJoin(LnCourse::tableName() . ' c', 'reg.course_id= c.kid')
            ->where(['user_id' => $uid])
            ->orderBy('reg_time')
            ->select('reg.*,c.course_name,c.course_desc,c.end_time')
            ->all();

        return $result;
    }


    /**
     * 获取用户注册信息
     * @param $uid
     * @param $courseId
     * @return array|null|yii\db\ActiveRecord
     */
    public function getUserRegInfo($uid, $courseId, $courseType = null)
    {
//        $courseModel = LnCourse::findOne(['kid'=>$courseId],false);
        $model = new LnCourseReg();
        $query = $model->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'user_id', $uid]);
        /*if ($courseType != null && $courseType == LnCourse::COURSE_TYPE_FACETOFACE) {
            $query->andFilterWhere(['in', 'reg_state', [LnCourseReg::REG_STATE_APPLING, LnCourseReg::REG_STATE_APPROVED]]);
        } else {
            $query->andFilterWhere(['=', 'reg_state', LnCourseReg::REG_STATE_APPROVED]);
        }*/
        $result = $query->addOrderBy(['updated_at' => SORT_DESC])
            ->one();
        return $result;
    }

    /**
     * 判断用户是否注册了课程
     * @param $uid
     * @param $courseId
     * @return bool
     */
    public function isUserRegCourse($uid, $courseId, &$regCourseId, $withSession = true)
    {
        if (!empty($uid)) {
            $sessionKey = "UserCourseReg_UserId_" . $uid . "_CourseId_" . $courseId;

            if ($withSession && Yii::$app->session->has($sessionKey)) {
                $regCourseId = Yii::$app->session->get($sessionKey);
                return true;
            } else {
                $result = $this->getUserRegInfo($uid, $courseId);

                if ($result == null) {
                    return false;
                } else {
                    $regCourseId = $result->kid;
                    if ($withSession) {
                        Yii::$app->session->set($sessionKey, $regCourseId);
                    }
                    return true;
                }
            }
        }
    }

    /**
     * 取得课程完成总数排名前5名
     * @return array
     */
    public function getCourseCompleteTop($uid)
    {
        $year_begin = strtotime(TTimeHelper::getCurrentYearFirstDay());

        $year_end = strtotime(TTimeHelper::getNextYearFirstDay()) - 1;

        $userModel = FwUser::findOne($uid);
        $reportingModel = FwCompany::findOne($userModel->company_id)->reporting_model;
        $userManageQuery = FwUserManager::find(false);
        $userManageQuery->select('user_id')
            ->andFilterWhere(['=', 'manager_id', $uid])
            ->andFilterWhere(['=', 'status', FwUserManager::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'reporting_model', $reportingModel])
            ->distinct();
        $userManageQuerySql = $userManageQuery->createCommand()->rawSql;

        $query = new Query();
        $result = $query
            ->from('{{%ln_course_complete}} as t1')
            ->leftJoin('{{%fw_user}} as t2', 't1.user_id = t2.kid')
            ->andWhere(['>=', 't1.updated_at', $year_begin])
            ->andWhere(['<=', 't1.updated_at', $year_end])
            ->andWhere('t2.kid in (' . $userManageQuerySql . ')')//根据领导获取下属信息
            ->andWhere(['t1.complete_type' => LnCourseComplete::COMPLETE_TYPE_FINAL])
            ->andFilterWhere(['or', ['=', 't1.complete_status', LnCourseComplete::COMPLETE_STATUS_DONE],
                ['=', 't1.is_retake', LnCourseComplete::IS_RETAKE_YES]])
//            ->andWhere(['t1.complete_status' => LnCourseComplete::COMPLETE_STATUS_DONE])
            ->andWhere(['t1.is_deleted' => LnCourseComplete::DELETE_FLAG_NO])
            ->andWhere(['t2.is_deleted' => FwUser::DELETE_FLAG_NO])
            ->groupBy('t1.user_id')
            ->orderBy('y_count desc')
            ->select('t1.user_id,t2.real_name,t2.thumb,t2.gender,count(t1.user_id) as y_count,t2.email')
            ->limit(5)
            ->all();

        $courseCompleteService = new CourseCompleteService();
        if ($result) {
            foreach ($result as &$v) {
                $uid = $v['user_id'];
                $allCount = $courseCompleteService->getUserCompleteCourseCount($uid, null, null);
                $v['a_count'] = $allCount;
            }
        }

        return $result;
    }


    /**
     * 统计用户年度完成课程，累计完成课程
     * @param array $users
     * @return array
     */
    public function getCourseCompleteStat($users)
    {
        if (!$users) {
            return $users;
        }

        $year_begin = strtotime(TTimeHelper::getCurrentYearFirstDay());

        $year_end = strtotime(TTimeHelper::getNextYearFirstDay()) - 1;

        foreach ($users as &$u) {
            $courseCompleteService = new CourseCompleteService();
            $yearCount = $courseCompleteService->getUserCompleteCourseCount($u['kid'], $year_begin, $year_end);
            $u['y_count'] = $yearCount;

            $allCount = $courseCompleteService->getUserCompleteCourseCount($u['kid'], null, null);
            $u['a_count'] = $allCount;
        }

        return $users;
    }


    public function getManagerCourse($uid)
    {
        $query = new Query();

        // 取得指定经理注册的课程
        $course = $query
            ->from('{{%ln_course_reg}} as t1')
            ->leftJoin('{{%ln_course}} as t2', 't1.course_id = t2.kid and t2.is_deleted = 0')
            ->orderBy('t2.end_time')
            ->select('t2.kid,t2.course_name,t2.end_time,COUNT(t1.kid) as count')
            ->where(['sponsor_id' => $uid, 't1.reg_type' => LnCourseReg::REG_TYPE_MANAGER])
            ->all();

        if (count($course) == 1 && ($course[0]["kid"] == null || $course[0]["kid"] == '')) {
            return null;
        }

        foreach ($course as &$c) {
            // 取得每门课程未完成学员ID
            $sql = "SELECT" .
                "	t1.user_id " .
                "FROM" .
                "	eln_ln_course_reg t1 " .
                "WHERE" .
                "	t1.course_id = '" . $c['kid'] . "' " .
                "AND t1.sponsor_id = '$uid' " .
                "AND t1.reg_type = '" . LnCourseReg::REG_TYPE_MANAGER . "' " .
                "AND NOT EXISTS (" .
                "	SELECT" .
                "		*" .
                "	FROM" .
                "		eln_ln_course_complete t2" .
                "	WHERE" .
                "		t2.course_id = t1.course_id" .
                "	AND t2.user_id = t1.user_id" .
                "	AND t2.complete_status = 1" .
                ")";

            $uncompleted = eLearningLMS::queryAll($sql);
            $c['un_count'] = count($uncompleted);
            $un_user_ids = array();
            $un_user_status = array();
            foreach ($uncompleted as $u) {
                $un_user_ids[] = $u['user_id'];

                $query = new Query();
                $status = $query
                    ->from('{{%ln_course_complete}} as t1')
                    ->leftJoin('{{%ln_res_complete}} as t2', 't1.kid = t2.course_complete_id and t1.is_deleted = 0')
                    ->orderBy('t2.complete_status')
                    ->groupBy('t2.complete_status')
                    ->select('t2.complete_status,count(t2.complete_status) AS count')
                    ->where(['t1.user_id' => $u['user_id'], 't1.course_id' => $c['kid'], 't1.complete_status' => 0])
                    ->all();
                if ($status[0]['complete_status'] == '0') {
                    $un_user_status[$u['user_id']][0] = $status[0]['count'];

                    if (count($status) == 1) {
                        $un_user_status[$u['user_id']][1] = 0;
                    } else if (count($status) == 2) {
                        $un_user_status[$u['user_id']][1] = $status[1]['count'];
                    }
                } else if ($status[0]['complete_status'] == '1') {
                    $un_user_status[$u['user_id']][0] = 0;
                    $un_user_status[$u['user_id']][1] = $status[0]['count'];
                }
            }
            $un_users = FwUser::findAll(['kid' => $un_user_ids]);
            $c['un_users'] = $un_users;
            $c['un_users_status'] = $un_user_status;
        }

        return $course;
    }

    /**
     * 根据用户ID取得所有注册课程记录
     * @param $uid 用户ID
     * @param $time 时间段[1:一天 2:一月 3:三月 null:所有]
     * @param $size 条数
     * @param $page 页码
     * @return array
     */
    public function getAllRegCourseByUid($uid, $time, $size, $page)
    {
        $time_condition = '';
        if ($time == 1) {
            $time_condition = 'AND t1.created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 WEEK)) ';
        } else if ($time == 2) {
            $time_condition = 'AND t1.created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) ';
        } else if ($time == 3) {
            $time_condition = 'AND t1.created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 MONTH)) ';
        }

        $offset = $this->getOffset($page, $size);

        $sql = "SELECT\n" .
            "	t1.course_id,	t2.course_name, t2.course_desc, t2.course_desc_nohtml, '0' as end_time, t1.reg_time, t3.complete_grade,\n" .
            "	t3.complete_status,t3.complete_grade as grade,t3.end_at,\n" .
            "	(\n" .
            "		SELECT\n" .
            "			count(kid)\n" .
            "		FROM\n" .
            "			eln_ln_course_reg\n" .
            "		WHERE\n" .
            "			course_id = t1.course_id\n" .
            "	) AS reg_count\n" .
            "FROM\n" .
            "	eln_ln_course_reg t1\n" .
            "LEFT JOIN eln_ln_course t2 ON t1.course_id = t2.kid AND t1.is_deleted = 0\n" .// AND t2.is_deleted = 0\n" .
            "LEFT JOIN eln_ln_course_complete t3 ON t1.kid = t3.course_reg_id AND t3.complete_type = '1' AND t1.is_deleted = 0 AND t3.is_deleted = 0\n" .
            "WHERE t1.user_id = '$uid'\n" .
            $time_condition .
            "ORDER BY t3.complete_status,t1.reg_time desc\n" .
            "LIMIT $size OFFSET $offset";

        return eLearningLMS::queryAll($sql);
    }

    /**
     * 取得用户最近学习的课程
     * @param string $uid 用户id
     * @return null|static
     */
    public function getLastLearnCourse($uid)
    {
        $completeQuery = LnCourseComplete::find(false);

        $completeQuery
            ->andFilterWhere(['=', 'user_id', $uid])
            ->andFilterWhere(['=', 'complete_status', LnCourseComplete::COMPLETE_STATUS_DOING])
            ->andFilterWhere(['=', 'complete_type', LnCourseComplete::COMPLETE_TYPE_FINAL])
            ->addOrderBy(['updated_at' => SORT_DESC]);

        $complete_data = $completeQuery->one();

        if ($complete_data == null) {
            return null;
        }

        $data = LnCourse::findOne($complete_data->course_id);

        if ($data != null) {
            $result = [];
            $result['data'] = $data;
            $result['learning_duration'] = $complete_data->learning_duration;

            return $result;
        } else {
            return null;
        }
    }

    /*面授报名表*/
    public function enrollCourse($uid, $courseId, $enroll_type, $sponsor_id = null)
    {
        $courseData = $this->findOne($courseId);
        $time = time();
        if ($courseData->enroll_start_time < $time) {/*未到报名时间*/
            return 'not_to';
        }
        if ($courseData->enroll_end_time >= $time) {/*报名时间已过*/
            return 'has_passed';
        }
        $regModel = LnCourseEnroll::find(false);
        $regModel->andFilterWhere(['course_id' => $courseId]);
        $regModel->andFilterWhere(['enroll_type' => $enroll_type]);
        $reg_count = $regModel->count('kid');/*报名数*/
        if ($courseData->limit_number > $reg_count) {
            return $courseData->limit_number - $reg_count;/*返回剩余名额*/
        } else {
            /*返回候补名额数*/
            if ($courseData->is_allow_over == LnCourse::IS_ALLOW_OVER_YES) {

            }
        }

    }

    /**
     * 课程注册，判断是否需要审批
     * @author adophper 2016-02-29
     * @param string $courseId
     * $param string $userId
     */
    public function isCourseApproval($courseId, $userId)
    {
        $courseModel = LnCourse::findOne($courseId);
        if ($courseModel->approval_rule == LnCourse::COURSE_APPROVAL_DEFAULT) {
            return false;
        } else {
            $approvedBy = $this->getUserSpecialApproval($userId);
            if (empty($approvedBy)) {
                return false;
            } else {
                return $approvedBy;
            }
        }
    }

    /**
     * 获取用户审批者，没有就获取直线经理
     * @author adophper 2016-02-29
     * @param string $userId
     * @return bool|mixed
     */
    public function getUserSpecialApproval($userId)
    {
        $userSpecialApproval = FwUserSpecialApprover::find(false)->andFilterWhere(['user_id' => $userId])->select('approver_id')->one();
        if ($userSpecialApproval) {
            return $userSpecialApproval->approver_id;
        } else {
            $userService = new UserService();
            $manager = $userService->getUserManager($userId);
            if (empty($manager)) {
                return false;
            } else {
                return $manager->manager_id;
            }
        }
    }

    /**
     * 注册课程
     * @param $uid
     * @param $courseId
     * @param $ret_type
     * @param null $sponsor_id 指派人
     * @param false $oper 是否手动添加注册
     */
    /**
     * 注册课程
     * @param $uid 用户id
     * @param $courseId 课程id
     * @param $ret_type 注册的方式
     * @param null $sponsor_id 课程指派人ID
     * @param bool $oper 是否手动报名
     * @return bool
     */
    public function regCourse($uid, $courseId, $ret_type, $sponsor_id = null, $oper = false)
    {
        // 手动报名 跳过受众验证
        if (!$oper) {
            /*判断是否在受众范围内*/
            $resourceAudienceService = new ResourceAudienceService();
            $isCourseAudience = $resourceAudienceService->isResourceAudience($uid, $courseId);
            if (!$isCourseAudience) {
                return false;
            }
        }

        $courseData = LnCourse::findOne($courseId);
        /* 防止同一人多条记录 */
        $count = LnCourseReg::find(false)->andFilterWhere(['course_id' => $courseId, 'user_id' => $uid])->count('kid');
        if ($count) {
            return true;
        }
        $lcreg = new LnCourseReg();
        $lcreg->course_id = $courseId;
        $lcreg->user_id = $uid;
        $lcreg->sponsor_id = $sponsor_id;
        $lcreg->reg_time = time();
        $lcreg->reg_type = $ret_type;
        /*2016-03-01添加审批规则，$author: adophper*/
        if ($courseData->course_type == LnCourse::COURSE_TYPE_ONLINE) {
            $approvedBy = $this->isCourseApproval($courseId, $uid);
            if (empty($approvedBy)) {
                $reg_state = LnCourseReg::REG_STATE_APPROVED;
            } else {
                $approvalFlowService = new ApprovalFlowService();
                $approvalFlowKid = $approvalFlowService->addApprovalFlowOfCourse($courseId, $uid, $courseData->approval_rule, $approvedBy);
                if (empty($approvalFlowKid)) {
                    $reg_state = LnCourseReg::REG_STATE_APPROVED;
                } else {
                    $reg_state = LnCourseReg::REG_STATE_APPLING;
                }
            }
        } else {
            if ($oper) {
                $reg_state = LnCourseReg::REG_STATE_APPROVED;/*管理员手动添加报名学员*/
            } else {
                $reg_state = LnCourseReg::REG_STATE_APPLING;
            }
        }
        $lcreg->reg_state = $reg_state;
        $lcreg->needReturnKey = true;
        $result = $lcreg->save();
        if ($result !== false) {
            LnCourseReg::removeFromCacheByKid($lcreg->kid);
            LnCourse::removeFromCacheByKid($courseId);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 在线课程注册成功后的操作
     * @param $courseId
     * @param $userId
     * @param $companyId
     * @return array
     */
    public function regCourseSuccess($courseId, $userId, $companyId)
    {
        /*增加注册量*/
        LnCourse::addFieldNumber($courseId, 'register_number');
        $timelineService = new TimelineService();
        $timelineService->regCourseTimeline($userId, $courseId);
        $record = new RecordService();
        $record->addByRegCourse($userId, $courseId);
        $courseModel = LnCourse::findOne($courseId);
        $pointResult = [];
        if ($courseModel->approval_rule == LnCourse::COURSE_APPROVAL_DEFAULT) {
            /*添加积分*/
            $pointRuleService = new PointRuleService();
            $pointResult = $pointRuleService->checkActionForPoint($companyId, $userId, 'Register-Online-Course', 'Learning-Portal', $courseId);
        }
        return ['result' => 'success', 'pointResult' => $pointResult];
    }

    /*取消注册*/
    public function delEnrollCourse($kid)
    {
        $resData = LnCourseEnroll::findOne($kid);
        if (empty($resData)) {
            return false;
        }

        $regModel = LnCourseReg::findOne(['user_id' => $resData->user_id, 'course_id' => $resData->course_id]);

        $transaction = $resData->getDb()->beginTransaction();
        if ($resData->delete() && $regModel->delete()) {
            $transaction->commit();
            return true;
        } else {
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 更新注册及报名状态
     * @param $courseId
     * @param $userId
     * @param $approvedBy
     * @param $regState
     */
    public function setCourseRegState($courseId, $userId, $regState, $approvedBy = null)
    {
        $regData = LnCourseReg::find(false)
            ->andFilterWhere(['course_id' => $courseId, 'user_id' => $userId, 'reg_state' => LnCourseReg::REG_STATE_APPLING])//只对申请中的记录进行操作
            ->one();
        if (!empty($regData)) {
            $regData->reg_state = $regState;
            if ($regData->save() !== false) {
                return ['result' => 'success'];
            } else {
                return ['result' => 'fail'];
            }
        }
    }

    public function SendRegMessage($uid, $courseId, $sponsorId)
    {
        /*课程表*/
        $lncourse = LnCourse::findOne($courseId);
        //添加消息主体
        $message = new MsMessage();
        $message->title = $lncourse->course_name;
        $message->content = $lncourse->course_desc;
        $message->end_time = 0;//目前截止时间未设置，暂时设置为0，表示没有截止时间
        $message->object_id = $lncourse->kid;
        $message->message_type = MsMessage::SUB_COURSE_REG;
        $message->msg_status = MsMessage::STATUS_NEARLY;
        $message->sender_id = $sponsorId;
        $message->needReturnKey = true;
        $message->save();

        //添加消息和用户的关系
        $message_user = new MsMessageUser();
        $message_user->msg_id = $message->kid;
        $message_user->user_id = $uid;
        $message_user->receive_status = MsMessageUser::STATUS_UNRECEIVE;
        $message_user->save();
    }

//    public function getOffset($page, $size)
//    {
//        $_page = (int)$page - 1;
//
//        return $size < 1 ? 0 : $_page * $size;
//    }

    /*
     * 课程版本号
     * 规则：日期+sprintf("%03d", course_version);
     * @param string $courseId
     * @return string
     */
    public function getCourseVersion($courseId = "")
    {
        if (empty($courseId)) return date('Ymd') . '001';
        $lncourse = new LnCourse();
        $condition = ['kid' => $courseId];
        $result = $lncourse->findOne($condition, false);
        $course_version = $result->course_version;
        if (substr($course_version, 0, 8) == date('Ymd')) {
            $last_version = substr($course_version, -3);
            return date('Ymd') . sprintf("%03d", intval($last_version) + 1);
        } else {
            return date('Ymd') . '001';
        }
    }

    /**
     * 根据课程ID取得课程评分
     * @param string $course_id 课程id
     * @return int|mixed 得分平均值
     */
    public function getCourseMarkByID($course_id)
    {
        $query = LnCourseMark::find(false);

        $result = $query->andFilterWhere(['=', 'course_id', $course_id])
            ->average('course_mark');

        return $result ? $result : 0;
    }

    /**
     * 根据课程ID取得课程评分人数
     * @param string $course_id 课程id
     * @return int|mixed 人数
     */
    public function getCourseMarkCountByID($courseId)
    {
        $query = LnCourseMark::find(false);

        $result = $query->andFilterWhere(['=', 'course_id', $courseId])
            ->count(1);

        return $result ? $result : 0;
    }

    public function GetModResByCourseId($courseId)
    {
        $model = new LnModRes();
        $result = $model->find(false)
            ->innerJoinWith('lnCourseMods')
            ->andFilterWhere(['=', LnModRes::tableName() . '.course_id', $courseId])
            ->addOrderBy([LnCourseMods::tableName() . '.mod_num' => SORT_ASC])
            ->addOrderBy([LnModRes::tableName() . '.sequence_number' => SORT_ASC])
//            ->addOrderBy([LnModRes::tableName() . '.updated_at' => SORT_ASC])
            ->all();

        return $result;
    }

    public function GetAvailableModResCount($courseId)
    {
        $model = new LnModRes();
        $result = $model->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'publish_status', LnModRes::PUBLIC_STATUS_YES])
            ->count('kid');

        return $result;
    }

    public function GetCourseModsByCourseId($courseId)
    {
        $model = new LnCourseMods();
        $result = $model->find(false)
            ->innerJoinWith('lnModRes')
            ->andFilterWhere(['=', LnCourseMods::tableName() . '.course_id', $courseId])
            ->addOrderBy([LnCourseMods::tableName() . '.mod_num' => SORT_ASC])
            ->addOrderBy([LnModRes::tableName() . '.sequence_number' => SORT_ASC])
//            ->addOrderBy([LnModRes::tableName() . '.updated_at' => SORT_ASC])
            ->all();

        return $result;
    }

    public function GetAvailableModCount($courseId)
    {
        $model = new LnCourseMods();
        $result = $model->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->count('kid');

        return $result;
    }


    public function GetModResByDirect($courseId, $modId, $currentNumber, $direct)
    {
        $model = new LnModRes();
        $result = $model->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'mod_id', $modId])
            ->andFilterWhere(['=', 'publish_status', LnModRes::YES]);

        if ($direct == "previous") {
            if (!empty($currentNumber)) {
                $result->andFilterWhere(['<', 'sequence_number', $currentNumber]);
            }

            $result->addOrderBy(['sequence_number' => SORT_DESC]);
        } else {
            if (!empty($currentNumber)) {
                $result->andFilterWhere(['>', 'sequence_number', $currentNumber]);
            }

            $result->addOrderBy(['sequence_number' => SORT_ASC]);
        }

        return $result->limit(1)->offset(0)->one();
    }


    public function GetModByDirect($courseId, $currentNumber, $direct)
    {
        $modResModel = new LnModRes();
        $queryStr = $modResModel->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'publish_status', LnModRes::YES])
            ->select('mod_id')
            ->distinct()
            ->createCommand()
            ->getRawSql();

        $model = new LnCourseMods();
        $result = $model->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andWhere('kid in (' . $queryStr . ')');


        if ($direct == "previous") {
            $result->andFilterWhere(['<', 'mod_num', $currentNumber])
                ->addOrderBy(['mod_num' => SORT_DESC]);
        } else {
            $result->andFilterWhere(['>', 'mod_num', $currentNumber])
                ->addOrderBy(['mod_num' => SORT_ASC]);
        }

        return $result->limit(1)->offset(0)->one();
    }

    /**
     * 判断用户是否已对课程评分
     * @param string $user_id 用户id
     * @param string $course_id 课程id
     * @return bool true：已评; false：未评
     */
    public function isRating($user_id, $course_id, $withCache = true)
    {
        $cacheKey = "UserRatingCourse_User_" . $user_id . "_CourseId_" . $course_id;

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

        if (!$result && !$hasCache) {

            $model = new LnCourseMark();
            $data = $model->find(false)
                ->andFilterWhere(['=', 'course_id', $course_id])
                ->andFilterWhere(['=', 'user_id', $user_id])
                ->count('kid');

            if (!empty($data) && $data > 0) {
                $result = true;
                //如果已经评过分了，就没必要再从数据库取这值
                self::saveToCache($cacheKey, true, null, self::DURATION_DAY, $withCache);
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * 课程评分
     * @param $user_id 用户id
     * @param $course_id 课程id
     * @param $rating 评分
     * @return bool
     */
    public function courseRating($user_id, $course_id, $rating)
    {
        $model = new LnCourseMark();

        $model->user_id = $user_id;
        $model->course_id = $course_id;
        $model->course_mark = $rating;

        if ($model->save()) {
            $query = LnCourseMark::find(false);

            $query->andFilterWhere(['=', 'course_id', $course_id])
                ->select('avg(course_mark)');

            $course = new LnCourse();

            $params = [
                ':kid' => $course_id
            ];

            $condition = 'kid = :kid';

            $attributes = [
                'rated_number' => new Expression('rated_number + 1'),
                'average_rating' => new Expression('(' . $query->createCommand()->rawSql . ')'),
            ];

            $row = $course->updateAll($attributes, $condition, $params);
        }
        return $row > 0;
    }

    /**
     * 统计未开课 已开课 已完成
     * @param $uid 用户id
     * return array（）
     */

    public function teacherStatCourse($uid)
    {
        $beforeNum = $this->teacherGetCourseCount($uid, self::COURSE_NOT_START);
        $startNum = $this->teacherGetCourseCount($uid, self::COURSE_START);
        $endNum = $this->teacherGetCourseCount($uid, self::COURSE_END);

        return array('beforenum' => $beforeNum, 'startnum' => $startNum, 'endnum' => $endNum);
    }

    public function teacherGetCourseCount($uid, $openStatus)
    {
        $currentTime = time();

        $query = LnCourse::find(false);
        $query->leftjoin('{{%ln_course_teacher}} as n1',
            'n1.course_id = ' . LnCourse::tableName() . ".kid")
            ->leftjoin('{{%ln_teacher}} as n2', 'n1.teacher_id = n2.kid')
            ->andWhere(['=', LnCourse::tableName() . '.status', self::STATUS_FLAG_NORMAL])
            ->andWhere(['=', 'n2.user_id', $uid])
            ->andWhere(['=', "n1.status", LnCourseTeacher::STATUS_FLAG_NORMAL])
            ->andWhere(['=', "n1.is_deleted", "0"])
            ->andWhere(['=', "n2.is_deleted", "0"]);

        if ($openStatus === LnCourse::COURSE_START) {
            $query->andWhere(['or',
                ['and', ['=', LnCourse::tableName() . '.course_type', LnCourse::COURSE_TYPE_FACETOFACE],
                    ['=', LnCourse::tableName() . '.open_status', $openStatus]],
                ['and', ['=', LnCourse::tableName() . '.course_type', LnCourse::COURSE_TYPE_ONLINE],
                    ['or', ['>=', 'end_time', $currentTime], 'end_time is null'],
                    ['or', ['<=', 'start_time', $currentTime], 'start_time is null']]
            ]);
        } else {
            $query->andWhere(['=', LnCourse::tableName() . '.course_type', LnCourse::COURSE_TYPE_FACETOFACE])
                ->andWhere(['=', LnCourse::tableName() . ".open_status", $openStatus]);
        }
        $query->distinct(LnCourse::tableName() . ".kid");

        return $query->count(1);
    }


    /**
     * 获取课程
     * @param $uid 用户id
     * @param $type before未开始，start进行中，end已结束
     */
    public function teacherGetCourse($uid, $type = 'before')
    {
        $currentTime = time();

        $courseModel = LnCourse::find(false);

        $courseModel
            ->leftjoin('{{%ln_course_teacher}} as n1', 'n1.course_id = ' . LnCourse::tableName() . ".kid")
            ->leftjoin('{{%ln_teacher}} as n2', 'n1.teacher_id = n2.kid')
            ->andWhere(['=', LnCourse::tableName() . '.status', self::STATUS_FLAG_NORMAL])
            ->andWhere(['=', 'n2.user_id', $uid])
            ->andWhere(['=', "n1.status", LnCourseTeacher::STATUS_FLAG_NORMAL])
            ->andWhere(['=', "n1.is_deleted", "0"])
            ->andWhere(['=', "n2.is_deleted", "0"]);


        if ($type == "start") {
            $courseModel->andWhere(['or',
                ['and', ['=', LnCourse::tableName() . '.course_type', LnCourse::COURSE_TYPE_FACETOFACE],
                    ['=', LnCourse::tableName() . '.open_status', self::COURSE_START]],
                ['and', ['=', LnCourse::tableName() . '.course_type', LnCourse::COURSE_TYPE_ONLINE],
                    ['or', ['>=', 'end_time', $currentTime], 'end_time is null'],
                    ['or', ['<=', 'start_time', $currentTime], 'start_time is null']]
            ]);
        } elseif ($type == "end") {
            $courseModel->andWhere(['=', LnCourse::tableName() . '.course_type', LnCourse::COURSE_TYPE_FACETOFACE])
                ->andWhere(['=', LnCourse::tableName() . '.open_status', self::COURSE_END]);
        } else {
            $courseModel->andWhere(['=', LnCourse::tableName() . '.course_type', LnCourse::COURSE_TYPE_FACETOFACE])
                ->andWhere(['=', LnCourse::tableName() . '.open_status', self::COURSE_NOT_START]);
        }
        /*修正以前出现有分页，但数据为空的情况20160612*/
        $courseModel->distinct(LnCourse::tableName() . ".kid");

        $count = $courseModel->count(1);
        if ($count > 0) {
            $pages = new TPagination(['defaultPageSize' => '5', 'totalCount' => $count]);
            $course = $courseModel
                ->offset($pages->offset)
                ->limit($pages->limit)
                ->select(LnCourse::tableName() . ".*")
                ->orderBy('release_at desc')
                ->all();

            if (!empty($course)) {
                foreach ($course as $key => $item) {
                    if ($item->course_type == LnCourse::COURSE_TYPE_FACETOFACE) {
                        $item->register_number = $this->getEnrollNumber($item->kid);
                    } else {
                        $item->register_number = $this->getOnlineNumber($item->kid);
                    }
                }
            }
        } else {
            $course = null;
            $pages = null;
        }

        return array('course' => $course, 'page' => $pages);
    }

    /**
     * 统计在线报名人数
     * @param $courseId
     * @return int|string
     */
    public function getOnlineNumber($courseId)
    {
        $regModel = LnCourseReg::find(false);
        $count = $regModel->andFilterWhere(['course_id' => $courseId])
            ->count('kid');

        return $count;
    }

    /**
     * 获取一门课程
     * @param
     */
    public function teacherGetOneCourse($uid, $kid, $fields = null)
    {
        if (!empty($fields)) {
            $courseModel = LnCourse::find(false)->andFilterWhere(['kid' => $kid])->select($fields)->one();
        } else {
            $courseModel = LnCourse::findOne($kid);
        }
        return $courseModel;
    }

    /**
     * 获取一年的课程
     * @param $year
     */
    public function teacherGetYearCourse($uid, $year = '')
    {
        $courseModel = LnCourse::find(false);
        if ($year) {
            $lastTime = strtotime($year . '-01-01 00:00:00');
            $nextTime = strtotime(($year + 1) . '-01-01 00:00:00');
        } else {
            $lastTime = strtotime(TTimeHelper::getCurrentYearFirstDay());
            $nextTime = strtotime(TTimeHelper::getNextYearFirstDay()) - 1;
        }

        $courseModel
            ->leftjoin('{{%ln_course_teacher}} as n1', 'n1.course_id = ' . LnCourse::tableName() . ".kid")
            ->leftjoin('{{%ln_teacher}} as n2', 'n1.teacher_id = n2.kid')
            ->andWhere(['=', LnCourse::tableName() . '.course_type', LnCourse::COURSE_TYPE_FACETOFACE])
            ->andWhere(['=', LnCourse::tableName() . '.status', self::STATUS_FLAG_NORMAL])
            ->andWhere(['=', 'n2.user_id', $uid])
            ->andWhere(['=', "n1.status", LnCourseTeacher::STATUS_FLAG_NORMAL])
            ->andWhere(['=', "n1.is_deleted", "0"])
            ->andWhere(['=', "n2.is_deleted", "0"])
            ->andWhere(['>=', LnCourse::tableName() . '.open_start_time', $lastTime])
            ->andWhere(['<=', LnCourse::tableName() . '.open_start_time', $nextTime])
            ->distinct(LnCourse::tableName() . ".kid");

        $course = $courseModel
            ->select(LnCourse::tableName() . ".kid ,course_name,open_start_time,open_end_time,open_status ")//kid,course_name,open_start_time,open_end_time
            ->all();

        return $course;
    }


    /**
     * 获取用户课程状态总数统计
     * @param string $userId 用户id
     */
    public function getCourseStatusCount($userId)
    {
        $regModel = LnCourseReg::find(false);
        $regModel->andFilterWhere(['=', 'reg_state', LnCourseReg::REG_STATE_APPROVED])
            ->andFilterWhere(['=', 'user_id', $userId])
            ->select('course_id')
            ->distinct();
        $regCount = $regModel->count('course_id');


        $query = LnCourseComplete::find(false);

        $query->andFilterWhere(['=', 'user_id', $userId])
            ->andFilterWhere(['=', 'complete_type', LnCourseComplete::COMPLETE_TYPE_FINAL])
            ->andFilterWhere(['or', ['=', 'complete_status', LnCourseComplete::COMPLETE_STATUS_DONE],
                ['=', 'is_retake', LnCourseComplete::IS_RETAKE_YES]])
            ->select('course_id')
            ->distinct();
        $completeCount = $query->count('course_id');

        $result = [];
        $result[0] = $regCount;
        $result[1] = $completeCount;

        return $result;
    }

    /**
     * 统计课程复制次数
     * @param $courseId
     * @return int|string
     */
    public function getCourseCopyCount($courseId)
    {
        $count = LnCourse::find(false)
            ->andFilterWhere(['=', 'origin_course_id', $courseId])
            ->count('kid');

        return intval($count);
    }

    /**
     * 课程复制
     * @param LnCourse $course
     * @param null $remark
     * @param bool|true $is_copy
     * @throws \Exception
     */
    public function courseCopy(LnCourse $course, $remark = null, $is_copy = true, $tempCourse = null, $params = null)
    {
        //$attributes = LnCourse::findOne($course->kid,false);
        $attributes = $course->attributes;
        unset($attributes['kid']);
        unset($attributes['course_code']);
        unset($attributes['version']);
        unset($attributes['created_by']);
        unset($attributes['updated_by']);
        unset($attributes['updated_at']);
        if (empty($remark) && $is_copy) { /*复制课程*/
            $remark[0] = $attributes;
            $teacher = LnCourseTeacher::findAll(['course_id' => $course->kid, 'status' => LnCourseTeacher::STATUS_FLAG_NORMAL], false);
            if (!empty($teacher)) {
                $teacher_id = ArrayHelper::map($teacher, 'teacher_id', 'teacher_id');
                $teacher_id = array_keys($teacher_id);
                $remark[0]['teacher_id'] = $teacher_id;
            }
        }
        $user_id = Yii::$app->user->getId();
        if (!empty($params['company_id'])) {
            $companyId = $params['company_id'];
        } else {
            $companyId = Yii::$app->user->identity->company_id;
        }
        if (!$is_copy) {
            $attributes['course_name'] = str_replace('第1期', '', $attributes['course_name']);
        }
        $kids = array();
        foreach ($remark as $k => $val) {
            if (!empty($tempCourse)) {
                $model = !empty($tempCourse[$k - 1]) ? LnCourse::findOne($tempCourse[$k - 1]) : new LnCourse();
            } else {
                $model = new LnCourse();
            }
            $model->company_id = $companyId;
            $model->category_id = !empty($params['category_id']) ? $params['category_id'] : $attributes['category_id'];
            $model->course_desc = $attributes['course_desc'];
            $model->course_desc_nohtml = $attributes['course_desc_nohtml'];
            $model->course_level = $attributes['course_level'];
            $model->course_type = $attributes['course_type'];
            $model->reg_type = $attributes['reg_type'];
            $model->course_period = $attributes['course_period'];
            $model->course_period_unit = $attributes['course_period_unit'];
            $model->course_language = $attributes['course_language'];
            $model->currency = $attributes['currency'];
            $model->theme_url = $attributes['theme_url'];
            $model->course_price = $attributes['course_price'];
            $model->default_credit = $attributes['default_credit'];
            $model->pass_grade = $attributes['pass_grade'];
            $model->is_display_pc = $attributes['is_display_pc'];
            $model->is_display_mobile = $attributes['is_display_mobile'];
            $model->start_time = $attributes['start_time'];
            $model->end_time = $attributes['end_time'];
            $model->status = $is_copy ? LnCourse::STATUS_FLAG_TEMP : $attributes['status'];/*复制未发布状态*/
            $model->is_survey_only = $attributes['is_survey_only'] ? $attributes['is_survey_only'] : LnCourse::IS_SURVEY_ONLY_NO;
            $model->is_exam_only = $attributes['is_exam_only'] ? $attributes['is_exam_only'] : LnCourse::IS_EXAM_ONLY_NO;
            $model->is_annony_view = $attributes['is_annony_view'] ? $attributes['is_annony_view'] : LnCourse::IS_ANNONY_VIEW_NO;
            $model->is_course_project = $attributes['is_course_project'] ? $attributes['is_course_project'] : LnCourse::IS_COURSE_PROJECT_NO;
            $model->approval_rule = $attributes['approval_rule'];
            $model->is_recalculate_score = $attributes['is_recalculate_score'] ? $attributes['is_recalculate_score'] : LnCourse::IS_RECALCULATE_SCORE_NO;
            $model->is_allow_no_register = empty($attributes['is_allow_no_register']) ? LnCourse::IS_ALLOW_NO_REGISTER_NO : $attributes['is_allow_no_register'];
            $model->max_attempt = $attributes['max_attempt'];
            $model->release_at = time();
            $model->learned_number = 0;
            $model->register_number = 0;
            $model->rated_number = 0;
            $model->average_rating = 0;
            $model->visit_number = 0;
            $model->course_code = $model->setCourseCode();
            $model->course_version = self::getCourseVersion();
            $model->short_code = $this->GenerateShortCode();
            //if ($is_copy) $model->origin_course_id = $course->kid;
            $model->mod_type = $attributes['mod_type'];
            $model->origin_course_id = $is_copy ? $course->kid : null;
            if ($is_copy) {
                $getCourseCopyCount = $this->getCourseCopyCount($course->kid);
                $copyTitle = "";
                if ($getCourseCopyCount > 0) {
                    $i = 0;
                    while ($i < $getCourseCopyCount) {
                        $copyTitle .= Yii::t('common', 'copies');
                        $i++;
                    }
                }
                $copyTitle .= Yii::t('common', 'copies');
                $model->course_name = $attributes['course_name'] . $copyTitle;
            } else {
                $model->course_name = $attributes['course_name'] . Yii::t('frontend', 'the_{value}_part_of', ['value' => ($k + 1)]);
            }
            $model->enroll_start_time = $val['enroll_start_time'];
            $model->enroll_end_time = $val['enroll_end_time'];
            $model->open_start_time = $val['open_start_time'];
            $model->open_end_time = $val['open_end_time'];
            $model->limit_number = $val['limit_number'];
            $model->is_allow_over = !empty($val['is_allow_over']) ? 1 : 0;
            $model->allow_over_number = $val['allow_over_number'];
            $model->training_address = empty($val['training_address']) ? null : $val['training_address'];
            $model->training_address_id = empty($val['training_address_id']) ? null : $val['training_address_id'];
            $model->vendor = empty($val['vendor']) ? null : $val['vendor'];
            $model->vendor_id = empty($val['vendor_id']) ? null : $val['vendor_id'];
            $model->needReturnKey = true;
            if (!empty($tempCourse) && !empty($tempCourse[$k - 1])) {
                $result = $model->update();
            } else {
                $result = $model->save();
            }
            if ($result === false) {
                return false;
            }
            $kids[] = $model->kid;
            /*课程讲师*/
            $teacherModel = new LnCourseTeacher();
            $teacher_id = $val['teacher_id'];
            $teacherModel->addRelation($model, $teacher_id);
            if ($model->course_type == LnCourse::COURSE_TYPE_FACETOFACE) {/*面授*/
                $teacher_id = !is_array($teacher_id) ? explode(',', $teacher_id) : $teacher_id;
                foreach ($teacher_id as $vo) {
                    $findTeacher = LnTeacher::findOne($vo);
                    $lnowner = new LnCourseOwner();
                    $lnowner->addRelationship($model, $findTeacher->user_id, LnCourseOwner::OWNER_TYPE_TEACHER);
                }
            }
            /*标签复制*/
            $tagAll = FwTagReference::findAll(['subject_id' => $course->kid, 'status' => FwTagReference::STATUS_FLAG_NORMAL], false);
            if (!empty($tagAll)) {
                foreach ($tagAll as $t_vo) {
                    $tag = FwTag::findOne($t_vo->tag_id);

                    $tagModel = new FwTagReference();
                    $tagModel->tag_id = $t_vo->tag_id;
                    $tagModel->tag_category_id = $tag->tag_category_id;
                    $tagModel->tag_value = $tag->tag_value;
                    $tagModel->subject_id = $model->kid;
                    $tagModel->start_at = $t_vo->start_at;
                    $tagModel->end_at = $t_vo->end_at;
                    $tagModel->save();
                    $tag->reference_count++;
                    $tag->update();
                }
            }
            /*添加证书*/
            $certificationInfo = LnCourseCertification::findOne(['course_id' => $course->kid, 'status' => LnCourseCertification::STATUS_FLAG_NORMAL]);
            if (!empty($certificationInfo->certification_id)) {
                $certificationModel = new LnCourseCertification();
                $certificationModel->addRelation($model, $certificationInfo->certification_id);
            }
            /*课程所有者*/
            $lnowner = new LnCourseOwner();
            $lnowner->addRelationship($model, $user_id, LnCourseOwner::OWNER_TYPE_ALL);
            /*资源*/
            if (!empty($params['domain_id'])) {
                foreach ($params['domain_id'] as $r_val) {
                    $resourceModel = new LnResourceDomain();
                    $resourceModel->resource_id = $model->kid;
                    $resourceModel->domain_id = $r_val;
                    $resourceModel->company_id = $companyId;
                    $resourceModel->start_at = $model->start_time;
                    $resourceModel->end_at = $model->end_time;
                    $resourceModel->status = LnResourceDomain::STATUS_FLAG_NORMAL;
                    $resourceModel->resource_type = LnResourceDomain::RESOURCE_TYPE_COURSE;
                    $resourceModel->save();
                }
            } else {
                $resouceAll = LnResourceDomain::findAll(['resource_id' => $course->kid]);
                if (!empty($resouceAll)) {
                    foreach ($resouceAll as $r_val) {
                        $resourceModel = new LnResourceDomain();
                        $resourceModel->resource_id = $model->kid;
                        $resourceModel->domain_id = $r_val->domain_id;
                        $resourceModel->company_id = $r_val->company_id;
                        $resourceModel->start_at = $r_val->start_at;
                        $resourceModel->end_at = $r_val->end_at;
                        $resourceModel->status = $r_val->status;
                        $resourceModel->resource_type = $r_val->resource_type;
                        $resourceModel->save();
                    }
                }
            }
            /*受众资源*/
            if (!empty($params['audience_id'])) {
                foreach ($params['audience_id'] as $audience) {
                    $resourceAudienceModel = new LnResourceAudience();
                    $resourceAudienceModel->resource_id = $model->kid;
                    $resourceAudienceModel->audience_id = $audience;
                    $resourceAudienceModel->company_id = $model->company_id;
                    $resourceAudienceModel->start_at = $model->start_time;
                    $resourceAudienceModel->end_at = $model->end_time;
                    $resourceAudienceModel->status = LnResourceDomain::STATUS_FLAG_NORMAL;
                    $resourceAudienceModel->resource_type = LnResourceAudience::RESOURCE_TYPE_COURSE;
                    $resourceAudienceModel->save();
                }
            } else {
                $resouceAudienceAll = LnResourceAudience::findAll(['resource_id' => $course->kid]);
                if (!empty($resouceAudienceAll)) {
                    foreach ($resouceAudienceAll as $audience) {
                        $resourceAudienceModel = new LnResourceAudience();
                        $resourceAudienceModel->resource_id = $model->kid;
                        $resourceAudienceModel->audience_id = $audience->audience_id;
                        $resourceAudienceModel->company_id = $audience->company_id;
                        $resourceAudienceModel->start_at = $audience->start_at;
                        $resourceAudienceModel->end_at = $audience->end_at;
                        $resourceAudienceModel->status = $audience->status;
                        $resourceAudienceModel->resource_type = $audience->resource_type;
                        $resourceAudienceModel->save();
                    }
                }
            }

            /*复制模块*/
            $modAll = LnCourseMods::findAll(['course_id' => $course->kid], false);
            if (!empty($modAll)) {
                foreach ($modAll as $m_val) {
                    $courseModsModel = new LnCourseMods();
                    $courseModsModel->course_id = $model->kid;
                    $courseModsModel->mod_num = $m_val->mod_num;
                    $courseModsModel->mod_name = $m_val->mod_name;
                    $courseModsModel->mod_desc = $m_val->mod_desc;
                    $courseModsModel->needReturnKey = true;
                    $courseModsModel->save();
                    /*复制资源信息*/
                    $modResAll = LnModRes::findAll(['mod_id' => $m_val->kid]);
                    if (!empty($modResAll)) {
                        foreach ($modResAll as $mr_val) {
                            $modResModel = new LnModRes();
                            $modResModel->mod_id = $courseModsModel->kid;
                            if ($mr_val->res_type == LnModRes::RES_TYPE_COURSEWARE) {
                                $coursewareId = $mr_val->courseware_id;
                                $component = LnComponent::findOne($mr_val->component_id);
                                if ($component->component_code == LnComponent::COMPONENT_CODE_HTML){
                                    $coursewareId = $this->copyHtml($coursewareId);
                                }elseif ($component->component_code == LnComponent::COMPONENT_CODE_BOOK){
                                    $coursewareId = $this->copyBook($coursewareId);
                                }
                                $modResModel->courseware_id = $coursewareId;
                                $modResModel->courseactivity_id = $mr_val->courseactivity_id;
                            } else {
                                $modResModel->courseware_id = $mr_val->courseware_id;
                                $findActivityModel = LnCourseactivity::findOne($mr_val->courseactivity_id);
                                if (!empty($findActivityModel)) {
                                    $object_id = $findActivityModel->object_id;
                                    /*复制课程时作业新建*/
                                    if ($findActivityModel->object_type == LnComponent::COMPONENT_CODE_HOMEWORK){
                                        $homework_id = $this->copyHomework($findActivityModel->object_id);
                                        if (!$homework_id) continue;
                                        $object_id = $homework_id;
                                    }
                                    $addActivityModel = new LnCourseactivity();
                                    $addActivityModel->course_id = $model->kid;
                                    $addActivityModel->component_id = $findActivityModel->component_id;
                                    $addActivityModel->object_id = $object_id;
                                    $addActivityModel->mod_id = $courseModsModel->kid;
                                    $addActivityModel->mod_res_id = '';//下面再更新
                                    $addActivityModel->object_type = $findActivityModel->object_type;
                                    $addActivityModel->activity_name = $findActivityModel->activity_name;
                                    $addActivityModel->start_at = $findActivityModel->start_at;
                                    $addActivityModel->end_at = $findActivityModel->end_at;
                                    $addActivityModel->default_credit = $findActivityModel->default_credit;
                                    $addActivityModel->default_time = $findActivityModel->default_time;
                                    $addActivityModel->resource_version = LnCourseactivity::getResourceVersion();
                                    $addActivityModel->is_display_pc = $findActivityModel->is_display_pc;
                                    $addActivityModel->is_display_mobile = $findActivityModel->is_display_mobile;
                                    $addActivityModel->is_allow_download = $findActivityModel->is_allow_download;
                                    $addActivityModel->needReturnKey = true;
                                    if (!$addActivityModel->save()) {
                                        continue;
                                    }
                                    $modResModel->courseactivity_id = $addActivityModel->kid;
                                } else {
                                    continue;
                                }
                            }
                            $modResModel->component_id = $mr_val->component_id;
                            $modResModel->course_id = $model->kid;
                            $modResModel->res_type = $mr_val->res_type;
                            $modResModel->sequence_number = $mr_val->sequence_number;
                            $modResModel->score_scale = $mr_val->score_scale;
                            $modResModel->score_strategy = $mr_val->score_strategy;
                            $modResModel->attempt_strategy = $mr_val->attempt_strategy;
                            $modResModel->direct_complete_course = $mr_val->direct_complete_course;
                            $modResModel->publish_status = $is_copy ? ($model->course_type == LnCourse::COURSE_TYPE_ONLINE ? LnModRes::PUBLIC_STATUS_YES : LnModRes::PUBLIC_STATUS_NO) : $mr_val->publish_status;/*复制未发布状态*/
                            $modResModel->pass_grade = $mr_val->pass_grade;
                            $modResModel->transfer_total_score = $mr_val->transfer_total_score;
                            $modResModel->is_record_score = $mr_val->is_record_score;
                            $modResModel->complete_rule = $mr_val->complete_rule;
                            $modResModel->needReturnKey = true;
                            if ($modResModel->save()) {
                                /*活动组件*/
                                if ($mr_val->res_type == LnModRes::RES_TYPE_COURSEACTIVITY) {
                                    LnCourseactivity::updateAll(['mod_res_id' => $modResModel->kid], 'kid=:kid', [':kid' => $addActivityModel->kid]);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $kids;
    }

    /**
     * 复制作业
     * @param $originHomeworkId
     * @param null $companyId
     * @return bool|string
     */
    public function copyHomework($originHomeworkId, $companyId = null){
        if (empty($originHomeworkId)) return false;
        $homework = LnHomework::findOne($originHomeworkId);
        if (empty($homework)) return false;
        $companyId = !empty($companyId) ? $companyId : Yii::$app->user->identity->company_id;
        $model = new LnHomework();
        $model->company_id = $companyId;
        $model->title = $homework->title;
        $model->requirement = $homework->requirement;
        $model->finish_before_at = $homework->finish_before_at;
        $model->homework_mode = $homework->homework_mode;
        $model->description = $homework->description;
        $model->needReturnKey = true;
        if ($model->save()){
            $homeworkFile = LnHomeworkFile::findAll(['homework_id' => $originHomeworkId, 'homework_file_type' => LnHomeworkFile::FILE_TYPE_TEACHER]);
            if (!empty($homeworkFile)){
                $userId = Yii::$app->user->getId();
                $modelArray = array();
                foreach ( $homeworkFile as $item){
                    $homeworkFileModel = new LnHomeworkFile();
                    $homeworkFileModel->homework_id = $model->kid;
                    $homeworkFileModel->user_id = $userId ? $userId : $item->user_id;
                    $homeworkFileModel->company_id = $companyId ? $companyId : $item->company_id;
                    $homeworkFileModel->homework_file_type = $item->homework_file_type;
                    $homeworkFileModel->file_url = $item->file_url;
                    $homeworkFileModel->file_name = $item->file_name;
                    $homeworkFileModel->file_md5 = $item->file_md5;
                    $homeworkFileModel->file_size = $item->file_size;
                    $homeworkFileModel->mime_type = $item->mime_type;
                    $homeworkFileModel->file_extension = $item->file_extension;
                    $homeworkFileModel->file_extension = $item->file_extension;
                    $homeworkFileModel->course_id = '0';
                    $homeworkFileModel->course_reg_id = null;
                    $homeworkFileModel->mod_id = '0';
                    $homeworkFileModel->mod_res_id = '0';
                    $homeworkFileModel->courseactivity_id = '0';
                    $homeworkFileModel->component_id = '0';
                    $homeworkFileModel->course_complete_id = null;
                    $homeworkFileModel->res_complete_id = null;
                    $homeworkFileModel->course_attempt_number = 0;
                    array_push($modelArray, $homeworkFileModel);
                }
                $errMsg = "";
                if (!empty($modelArray)) {
                    BaseActiveRecord::batchInsertNormalMode($modelArray, $errmsg);
                }
            }
            return $model->kid;
        }else{
            return false;
        }
    }

    /**
     * 复制图书
     * @param $originBookId
     * @param null $companyId
     * @return bool|string
     */
    public function copyBook($originBookId, $companyId = null){
        if (empty($originBookId)) return false;
        $courseware = LnCourseware::findOne($originBookId);
        if (empty($courseware)) return false;
        $companyId = !empty($companyId) ? $companyId : Yii::$app->user->identity->company_id;
        $model = new LnCourseware();
        $model->file_id = $courseware->file_id;
        $model->component_id = $courseware->component_id;
        $model->courseware_category_id = $courseware->courseware_category_id;
        $model->company_id = $companyId;
        $model->embed_url = $courseware->embed_url;
        $model->embed_code = $courseware->embed_code;
        $model->courseware_type = $courseware->courseware_type;
        $model->courseware_code = $courseware->setCoursewareCode();
        $model->courseware_name = $courseware->courseware_name;
        $model->courseware_desc = $courseware->courseware_desc;
        $model->vendor = $courseware->vendor;
        $model->vendor_id = $courseware->vendor_id;
        $model->resource_version = $courseware->getCoursewareVersion();
        $model->courseware_time = $courseware->courseware_time;
        $model->default_credit = $courseware->default_credit;
        $model->start_at = $courseware->start_at;
        $model->end_at = $courseware->end_at;
        $model->is_display_pc = $courseware->is_display_pc;
        $model->is_display_mobile = $courseware->is_display_mobile;
        $model->is_allow_download = $courseware->is_allow_download;
        $model->file_update_freqency = $courseware->file_update_freqency;
        $model->entry_mode = $courseware->entry_mode;
        $model->display_position = $courseware->display_position;
        $model->entrance_address = $courseware->entrance_address;
        $model->needReturnKey = true;
        if ($model->save()){
            $coursewareBook = LnCoursewareBook::findOne(['courware_id' => $originBookId]);
            if (!empty($coursewareBook)){
                $coursewareBoolModel = new LnCoursewareBook();
                $coursewareBoolModel->courware_id = $model->kid;
                $coursewareBoolModel->book_name = $coursewareBook->book_name;
                $coursewareBoolModel->isbn_no = $coursewareBook->isbn_no;
                $coursewareBoolModel->author_name = $coursewareBook->author_name;
                $coursewareBoolModel->publisher_name = $coursewareBook->publisher_name;
                $coursewareBoolModel->original_book_name = $coursewareBook->original_book_name;
                $coursewareBoolModel->translator = $coursewareBook->translator;
                $coursewareBoolModel->publisher_date = $coursewareBook->publisher_date;
                $coursewareBoolModel->price = $coursewareBook->price;
                $coursewareBoolModel->page_number = $coursewareBook->page_number;
                $coursewareBoolModel->binding_layout = $coursewareBook->binding_layout;
                $coursewareBoolModel->description = $coursewareBook->description;
                $coursewareBoolModel->image_url = $coursewareBook->image_url;
                $coursewareBoolModel->external_url = $coursewareBook->external_url;
                $coursewareBoolModel->external_id = $coursewareBook->external_id;
                $coursewareBoolModel->external_date_type = $coursewareBook->external_date_type;
                $coursewareBoolModel->status = LnCoursewareBook::STATUS_FLAG_NORMAL;
                $coursewareBoolModel->save();
            }
            return $model->kid;
        }else{
            return false;
        }
    }

    /**
     * 复制HTML插件
     * @param $originHtmlId
     * @param null $companyId
     * @return bool|string
     */
    public function copyHtml($originHtmlId, $companyId = null){
        if (empty($originHtmlId)) return false;
        $courseware = LnCourseware::findOne($originHtmlId);
        if (empty($courseware)) return false;
        $companyId = !empty($companyId) ? $companyId : Yii::$app->user->identity->company_id;
        $model = new LnCourseware();
        $model->file_id = $courseware->file_id;
        $model->component_id = $courseware->component_id;
        $model->courseware_category_id = $courseware->courseware_category_id;
        $model->company_id = $companyId;
        $model->embed_url = $courseware->embed_url;
        $model->embed_code = $courseware->embed_code;
        $model->courseware_type = $courseware->courseware_type;
        $model->courseware_code = $courseware->setCoursewareCode();
        $model->courseware_name = $courseware->courseware_name;
        $model->courseware_desc = $courseware->courseware_desc;
        $model->vendor = $courseware->vendor;
        $model->vendor_id = $courseware->vendor_id;
        $model->resource_version = $courseware->getCoursewareVersion();
        $model->courseware_time = $courseware->courseware_time;
        $model->default_credit = $courseware->default_credit;
        $model->start_at = $courseware->start_at;
        $model->end_at = $courseware->end_at;
        $model->is_display_pc = $courseware->is_display_pc;
        $model->is_display_mobile = $courseware->is_display_mobile;
        $model->is_allow_download = $courseware->is_allow_download;
        $model->file_update_freqency = $courseware->file_update_freqency;
        $model->entry_mode = $courseware->entry_mode;
        $model->display_position = $courseware->display_position;
        $model->entrance_address = $courseware->entrance_address;
        $model->needReturnKey = true;
        if ($model->save()){
            return $model->kid;
        }else{
            return false;
        }
    }

    /**
     * 删除课程时删除资源数据
     * @param $courseId
     */
    public function DeleteModResRelationship($courseId)
    {
        LnCourseMods::deleteAll("course_id=:course_id", [':course_id' => $courseId]);
        LnModRes::deleteAll("course_id=:course_id", [':course_id' => $courseId]);
        LnCourseactivity::deleteAll("course_id=:course_id", [':course_id' => $courseId]);
    }


    /**
     * 获取已经设置权重的所有组件
     * @param $courseId
     * @return int|null|string
     */
    public function CountModResScaleCourseId($courseId)
    {
        if (!empty($courseId)) {
            $model = new LnModRes();
            $query = $model->find(false)
                ->andFilterWhere(['=', 'course_id', $courseId])
                ->andWhere("score_scale is not null")
                ->andFilterWhere(['=', 'direct_complete_course', LnModRes::DIRECT_COMPLETE_COURSE_NO])
                ->count('kid');

            return intval($query);
        } else {
            return 0;
        }
    }

    /**
     * 获取面授课程报名学员,更改时间：2016-06-06 @author adophper<hello@adophper.com>
     * @param $courseId
     * @param null $params
     * $param bool $justReturnCount 只返回条数
     * @return array,int
     */
    public function searchCourseEnroll($courseId, $params = null, $justReturnCount = false)
    {
        $courseEnrollService = new CourseEnrollService();
        $res = $courseEnrollService->searchCourseEnroll($courseId, $params, $justReturnCount);
        return $res;
    }

    /**
     * 获取面授课程报名学员,更改时间：2016-06-06 @author adophper<hello@adophper.com>
     * @param $courseId
     * @param null $params
     * @return array
     */
    public function newSearchCourseEnroll($courseId, $params = null)
    {
        $courseEnrollService = new CourseEnrollService();
        $res = $courseEnrollService->searchCourseEnroll($courseId, $params);
        return $res;
    }

    /**
     * 获取在线课程注册学员
     * @param $courseId
     * @param null $params
     * @return array
     */
    public function searchCourseReg($courseId, $params = null)
    {
        $enrollModel = new Query();
        $enrollModel->from(LnCourseReg::tableName() . ' as len')
            ->leftjoin(FwUserDisplayInfo::tableName() . ' as t1', 't1.user_id = len.user_id')
            ->distinct()
            ->select('t1.real_name,t1.orgnization_name,t1.position_name,t1.email,t1.mobile_no,t1.location,len.kid,len.user_id,len.reg_time');
        $enrollModel->andWhere("len.is_deleted='0'")
            ->andWhere("t1.status='1' and t1.is_deleted='0'");
        if (!empty($params['keyword'])) {
            $params['keyword'] = trim($params['keyword']);
            $enrollModel->where("t1.real_name like '%{$params['keyword']}%' or t1.orgnization_name like '%{$params['keyword']}%' or t1.position_name like '%{$params['keyword']}%'");
        }
        $enrollModel->andFilterWhere(['=', 'len.course_id', $courseId])
            ->andFilterWhere(['=', 'len.is_deleted', LnCourseEnroll::DELETE_FLAG_NO]);
        if (isset($params['sort']) && $params['sort'] == 2) {
            $enrollModel->orderBy('t1.orgnization_id desc');
        } else {
            $enrollModel->orderBy('t1.position_name asc');
        }
        $count = $enrollModel->count();
        if (isset($params['showAll']) && $params['showAll'] === 'True') {
            $pages = new TPagination(['defaultPageSize' => $count, 'totalCount' => $count]);
            $data = $enrollModel->all();
        } else {
            $pages = new TPagination(['defaultPageSize' => 24, 'totalCount' => $count]);
            $data = $enrollModel->offset($pages->offset)->limit($pages->limit)->all();
        }
        $result = array(
            'pages' => $pages,
            'data' => $data,
        );
        return $result;
    }

    /*讲师 课程详情 获取签到记录*/
    public function getSignRecordTeacher($courseId, $param, $down = false)
    {
        $time = isset($param['time']) ? $param['time'] : '';
        $startTime = strtotime(TTimeHelper::getCurrentDayStart($time));
        $endTime = strtotime(TTimeHelper::getCurrentDayEnd($time));

        $query = LnCourseEnroll::find(false);
        $masterTable = LnCourseEnroll::tableName();
        $query->leftJoin(FwUserDisplayInfo::tableName() . ' as t1', 't1.user_id = ' . $masterTable . '.user_id')
            ->leftJoin(LnCourseSignIn::tableName() . ' as lsn', 'lsn.user_id = ' . $masterTable . '.user_id and lsn.course_id=' . $masterTable . '.course_id and lsn.is_deleted=\'0\' and lsn.sign_time >' . $startTime . ' and lsn.sign_time<' . $endTime)
            ->andFilterWhere(['=', $masterTable . '.course_id', $courseId])
            ->andFilterWhere(['=', $masterTable . '.enroll_type', LnCourseEnroll::ENROLL_TYPE_ALLOW])
            ->andWhere("t1.status='1' and t1.is_deleted='0'");
        if ($param['signstatus'] == 1) {
            $query->andWhere('lsn.kid is not null');
        } else if ($param['signstatus'] == 2) {
            $query->andWhere('lsn.kid is null');
        }
        $query->select('t1.real_name,t1.orgnization_name,t1.location,' . $masterTable . '.kid,' . $masterTable . '.user_id,lsn.kid as signstautus,lsn.sign_time');
        if ($down) {
            $result = $query->all();
            return $result;
        }
        $count = $query->count();
        $pages = new TPagination(['defaultPageSize' => 10, 'totalCount' => $count]);
        $data = $query->offset($pages->offset)->limit($pages->limit)->all();
        $result = array(
            'pages' => $pages,
            'data' => $data,
        );
        return $result;
    }

    /**
     * 获取课程报名名额
     * 20160302 增加对审批状态的过滤
     * @param $courseId
     * @param null $enroll_type
     * @return int|string
     */
    public function getEnrollNumber($courseId, $enroll_type = null)
    {
        $count = LnCourseEnroll::find(false)
            ->andFilterWhere(['course_id' => $courseId, 'enroll_type' => $enroll_type])
            ->andFilterWhere(['in', 'approved_state', [LnCourseEnroll::APPROVED_STATE_APPLING, LnCourseEnroll::APPROVED_STATE_APPROVED]])
            ->count('kid');
        return $count;
    }

    /**
     * 获取用户报名信息
     * @param $uid
     * @param $courseId
     * @return array|null|yii\db\ActiveRecord
     */
    public function getUserEnrollInfo($uid, $courseId, $type = null)
    {
        $model = new LnCourseEnroll();
        $query = $model->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'user_id', $uid]);
        if ($type != null) {
            $query->andFilterWhere(['=', 'enroll_type', $type]);
        }
        $result = $query->addOrderBy(['updated_at' => SORT_DESC])->one();
        return $result;
    }

    /**
     * 判断用户是否已被允许报名
     * @param string $user_id 用户id
     * @param string $course_id 课程id
     * @return bool true：已同意; false：未同意
     */
    public function isEnroll($user_id, $course_id, $withCache = true)
    {
        $cacheKey = "UserEnrolledCourse_User_" . $user_id . "_CourseId_" . $course_id;

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

        if (!$result && !$hasCache) {
            $model = new LnCourseEnroll();
            $data = $model->find(false)
                ->andFilterWhere(['=', 'course_id', $course_id])
                ->andFilterWhere(['=', 'user_id', $user_id])
                ->andFilterWhere(['=', 'enroll_type', LnCourseEnroll::ENROLL_TYPE_ALLOW])
                ->count('kid');

            if (!empty($data) && $data > 0) {
                $result = true;
                //如果已经允许报名了，就没必要再从数据库取这值
                self::saveToCache($cacheKey, true, null, self::DURATION_DAY, $withCache);
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /*获取报名状态描述*/
    public function getEnrollStatusText($uid, $courseId)
    {
        $info = $this->getUserEnrollInfo($uid, $courseId);
        if (!empty($info->kid)) {
            $text = array(
                LnCourseEnroll::ENROLL_TYPE_REG => '报名审核中',
                LnCourseEnroll::ENROLL_TYPE_ALLOW => '报名成功',
                LnCourseEnroll::ENROLL_TYPE_ALTERNATE => '报名审核中',
                LnCourseEnroll::ENROLL_TYPE_DISALLOW => '报名失败',
            );
            return $text[$info->enroll_type];
        }
    }

    /*报名储存*/
    public function saveEnrollInfo($data)
    {
        $enrollModel = new LnCourseEnroll();
        $enrollResult = $enrollModel->find(false)
            ->andFilterWhere(['=', 'course_id', $data['course_id']])
            ->andFilterWhere(['=', 'user_id', $data['user_id']])
//            ->andFilterWhere(['<>','enroll_type',LnCourseEnroll::ENROLL_TYPE_DISALLOW])
            ->count('kid');

        if ($enrollResult > 0) {
            return ['result' => 'fail', 'errcode' => 'not_allow', 'errmsg' => Yii::t('frontend', 'exam_re_enroll')];/*已经报过名*/
        } else {
            $model = new LnCourseEnroll();
            $model->course_id = $data['course_id'];
            $model->user_id = $data['user_id'];
            $model->enroll_user_id = $data['enroll_user_id'];
            $model->enroll_type = $data['enroll_type'];
            $model->enroll_method = $data['enroll_method'];
            /*增加审批流程*/
            $approvedBy = $this->isCourseApproval($data['course_id'], $data['user_id']);
            if (empty($approvedBy)) {
                $approved_state = LnCourseEnroll::APPROVED_STATE_APPROVED;
            } else {
                $courseModel = LnCourse::findOne($data['course_id']);
                $approvalFlowService = new ApprovalFlowService();
                $approvalFlowKid = $approvalFlowService->addApprovalFlowOfCourse($data['course_id'], $data['user_id'], $courseModel->approval_rule, $approvedBy);
                if (empty($approvalFlowKid)) {
                    $approved_state = LnCourseEnroll::APPROVED_STATE_APPROVED;
                } else {
                    $approved_state = LnCourseEnroll::APPROVED_STATE_APPLING;
                }
            }
            $model->approved_state = $approved_state;
            $model->enroll_time = time();
            $result = $model->save();
            if ($result !== false) {
                $this->regCourse($data['user_id'], $data['course_id'], LnCourseReg::REG_TYPE_SELF);/*添加数据到注册表*/
                return ['result' => 'success'];
            }
        }
    }

    /**
     * 报名但是不注册储存
     * @param $data
     **/
    public function saveOtherEnrollInfo($data)
    {
        $enrollModel = new LnCourseEnroll();
        $enrollResult = $enrollModel->find(false)
            ->andFilterWhere(['=', 'course_id', $data['course_id']])
            ->andFilterWhere(['=', 'user_id', $data['user_id']])
//            ->andFilterWhere(['<>','enroll_type',LnCourseEnroll::ENROLL_TYPE_DISALLOW])
            ->all();

        if (!empty($enrollResult) && count($enrollResult) > 0) {
            return ['result' => 'fail', 'errcode' => 'not_allow', 'errmsg' => Yii::t('frontend', 'exam_re_enroll')];/*已经报过名*/
        } else {
            $model = new LnCourseEnroll();
            $model->course_id = $data['course_id'];
            $model->user_id = $data['user_id'];
            $model->enroll_user_id = $data['enroll_user_id'];
            $model->enroll_type = $data['enroll_type'];
            $model->enroll_method = $data['enroll_method'];
            $model->approved_state = LnCourseEnroll::APPROVED_STATE_APPROVED;
            $model->enroll_time = time();

            /*20160122修改*/
            $ret = $this->regCourse($data['user_id'], $data['course_id'], LnCourseReg::REG_TYPE_MANAGER, null, true);/*添加数据到注册表*/
            if ($ret) {
                $model->save();
                return ['result' => 'success'];
            } else {
                return ['result' => 'fail'];
            }
        }
    }

    /**
     * @param $courseId
     * @return FwUser
     * 20160122修改
     */
    public function GetEnrollUserList($courseId, $modResId, $user_id = null)
    {
        $courseModel = LnCourse::findOne($courseId);
        if ($courseModel->course_type == LnCourse::COURSE_TYPE_ONLINE) {
            $enrollModel = LnCourseReg::find(false)
                ->andFilterWhere(['=', 'course_id', $courseId])
                ->andFilterWhere(['=', 'reg_state', LnCourseReg::REG_STATE_APPROVED]);
        } else {
            $enrollModel = LnCourseEnroll::find(false)
                ->andFilterWhere(['=', 'course_id', $courseId])
                ->andFilterWhere(['=', 'enroll_type', LnCourseEnroll::ENROLL_TYPE_ALLOW]);
        }

        if (!empty($user_id)) {
            $enrollModel->andFilterWhere(['=', 'user_id', $user_id]);
        }
        $enrollUserSql = $enrollModel->select('user_id')
            ->createCommand()->getRawSql();
        $userModel = new FwUser();
        $result = $userModel->find(false)
            ->leftJoin(LnResComplete::tableName(),
                LnResComplete::tableName() . '.user_id = ' . FwUser::tableName() . '.kid '
                . 'and (((complete_status = :complete_status OR complete_status = :complete_status2) and complete_type = :complete_type_final) or complete_type = :complete_type_backup)   and mod_res_id = :mod_res_id ',
                [
                    ':complete_status' => LnResComplete::COMPLETE_STATUS_DONE,
                    ':complete_status2' => LnResComplete::COMPLETE_STATUS_DOING,
                    ':mod_res_id' => $modResId,
                    ':complete_type_final' => LnResComplete::COMPLETE_TYPE_FINAL,
                    ':complete_type_backup' => LnResComplete::COMPLETE_TYPE_BACKUP,
                ])
            ->andWhere(FwUser::tableName() . '.kid in (' . $enrollUserSql . ')')
            ->andFilterWhere(['=', 'status', FwUser::STATUS_FLAG_NORMAL])
            ->select([
                FwUser::tableName() . '.*',
                'res_complete_kid' => LnResComplete::tableName() . '.kid',
            ])
            ->addOrderBy([LnResComplete::tableName() . '.kid' => SORT_ASC]);

        return $result;
    }

    /**
     * 判断是否签到
     * @param $userId
     * @param $courseId
     * @param null $signTime
     * @return bool
     */
    public function isSigninInToday($userId, $courseId, $signTime = null, $withCache = true)
    {
        $cacheKey = "SigninInCourse_User_" . $userId . "_CourseId_" . $courseId;

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

        if (!$result && !$hasCache) {
            $model = new LnCourseSignIn();
            if (empty($signTime)) {
                $signTime = time();
            }
            $startTime = strtotime(TTimeHelper::getCurrentDayStart($signTime));
            $endTime = strtotime(TTimeHelper::getCurrentDayEnd($signTime));

            $data = $model->find(false)
                ->andFilterWhere(['=', 'course_id', $courseId])
                ->andFilterWhere(['=', 'user_id', $userId])
                ->andFilterWhere(['>=', 'sign_time', $startTime])
                ->andFilterWhere(['<=', 'sign_time', $endTime])
                ->count(1);

            if (!empty($data) && $data > 0) {
                $result = true;
                //如果已经签过到了，就没必要再从数据库取这值
                self::saveToCache($cacheKey, true, null, self::DURATION_DAY, $withCache);
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * 获取用户第一次签到记录
     * @param $userId
     * @param $courseId
     * @param null $signTime
     * @return bool
     */
    public function GetCourseUserFirstSignIn($userId, $courseId, $signTime = null)
    {
        $signModel = LnCourseSignIn::find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'user_id', $userId]);
        if (!empty($signTime)) {
            $startTime = strtotime(TTimeHelper::getCurrentDayStart($signTime));
            $endTime = strtotime(TTimeHelper::getCurrentDayEnd($signTime));
            $signModel->andFilterWhere(['>=', 'sign_time', $startTime])
                ->andFilterWhere(['<=', 'sign_time', $endTime]);
        }
        $signResult = $signModel->orderBy("sign_time asc")->one();

        return $signResult;
    }

    /**
     * @param LnCourseSignIn $data
     * @param $errorMsg
     * @return bool
     */
    public function saveSignInfo(LnCourseSignIn $data, &$errorMsg)
    {
        $enrollModel = new LnCourseEnroll();
        $enrollResult = $enrollModel->find(false)
            ->andFilterWhere(['=', 'course_id', $data->course_id])
            ->andFilterWhere(['=', 'user_id', $data->user_id])
            ->andFilterWhere(['=', 'enroll_type', LnCourseEnroll::ENROLL_TYPE_ALLOW])
            ->count('kid');

        if ($enrollResult > 0) {
            $signTime = $data->sign_time;
            $isSignin = $this->isSigninInToday($data->user_id, $data->course_id, $signTime);
            if ($isSignin) {
                $errorMsg = '今日已经签到过，无须重复签到';
                return false;
            } else {
                return $data->save();
            }
        } else {
            $errorMsg = '没有报名该课程，无需签到';
            return false;
        }
    }

    /**
     * 获取课程学员签到记录
     * @param $courseId
     * @param null $userId
     * @param int $pageSize
     * @return array
     */
    public function GetSigninRecord($courseId, $userId = null, $pageSize = 10)
    {
        $query = LnCourseSignIn::find(false);
        $query->andFilterWhere(['course_id' => $courseId]);
        if ($userId) {
            $query->andFilterWhere(['user_id' => $userId]);
        }
        $count = $query->count('kid');
        $page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
        $data = $query->offset($page->offset)->limit($page->limit)->asArray()->all();
        return ['data' => $data, 'page' => $page];
    }

    /**
     * 生成课程短代码
     * @return string
     */
    public function GenerateShortCode()
    {
        $totalModel = new LnCourse();
        $totalCount = $totalModel->find(true)->count('kid');
        $number = $totalCount + 1;
        $shortCode = TURLHelper::generateShortCode($number);

        $unCheckedExist = true;
        do {

            $model = new LnCourse();
            $result = $model->find(true)
                ->andFilterWhere(['=', 'short_code', $shortCode])
                ->count('kid');

            if (!empty($result) && $result > 0) {
                $shortCode = TURLHelper::generateShortCode();
            } else {
                $unCheckedExist = false;
            }

        } while ($unCheckedExist);

        return $shortCode;
    }

    public function GetCourseByShortCode($shortCode)
    {
        $courseModel = new LnCourse();

        $result = $courseModel->find(false)
            ->andFilterWhere(['=', 'short_code', $shortCode])
            ->one();

        return $result;
    }


    /**
     * 取得课程所有报名通过学员列表
     * @param $courseId 课程ID
     * @param null $field 返回字段
     * @return array|yii\db\ActiveRecord[]
     */
    public function getAllEnrollApprovedUser($courseId, $field = null)
    {
        $enrollResult = LnCourseEnroll::find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'enroll_type', LnCourseEnroll::ENROLL_TYPE_ALLOW])
            ->andFilterWhere(['=', 'approved_state', LnCourseEnroll::APPROVED_STATE_APPROVED]);

        if ($field !== null) {
            $enrollResult->select($field);
        }

        return $enrollResult->all();
    }

    /**
     * @param $courseId
     * @param $enrollType
     * @return int
     */
    public function updateCourseEnrollStatus($courseId, $enrollType = LnCourseEnroll::ENROLL_TYPE_DISALLOW)
    {
        $model = new LnCourseEnroll();
        $uid = Yii::$app->user->getId();
        $result = $model->updateAll(['enroll_type' => $enrollType, 'approved_by' => $uid, 'approved_at' => time()], 'course_id=:courseId and (enroll_type=:enroll_type_reg OR enroll_type=:enroll_type_alternate)', [':courseId' => $courseId, ':enroll_type_reg' => LnCourseEnroll::ENROLL_TYPE_REG, ':enroll_type_alternate' => LnCourseEnroll::ENROLL_TYPE_ALTERNATE]);
        if ($result !== false) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param $courseId
     * @param $uid
     * @param string $tpl
     * @return bool
     */
    public function sendEmailToEnrollUser($courseId, $uid, $tpl = 'courseAllowToUser')
    {
        $emailSwitch = false;
        if (isset(Yii::$app->params['email_switch'])) {
            $emailSwitch = Yii::$app->params['email_switch'];
        }

        if ($emailSwitch) {
            $user = FwUser::findOne($uid);
            $course = $this->findOne($courseId);
            if ($user && !empty($user->email)) {
                $userService = new UserService();
                if ($userService->isEmailRepeat($user->email)) {
                    return false;
                } else {
                    $subject = Yii::t('common', $tpl);
                    $subject = str_replace('{courseName}', $course->course_name, $subject);
                    return Yii::$app->mailer->compose(['html' => $tpl . '-html', 'text' => $tpl . '-text'], ['user' => $user, 'course' => $course])
                        ->setFrom([Yii::$app->params['supportEmail'] => Yii::t('system', 'system_robot')])
                        ->setTo($user->email)
                        ->setSubject($subject)
                        ->send();
                }
            }
            return false;
        } else
            return false;
    }

    /**
     * @param $courseId
     * @param $uid
     * @param string $msgCode
     * @return bool
     */
    public function sendWechatMessageToEnrollUser($courseId, $uid, $templateCode = 'courseAllowToUser')
    {
        $user = FwUser::findOne($uid);
        $companyId = $user->company_id;
        $wechatTemplateService = new WechatTemplateService();
        $templateModel = $wechatTemplateService->getWechatTemplateByCode($companyId, $templateCode);
        if (!empty($templateModel)) {
            $templateUrl = null;
            $templateId = $templateModel->wechat_template_id;
            $wechatService = new WechatService();
            $model = $wechatService->getWechatAccount($uid);
            if (!empty($model) && !empty($model->open_id)) {
                $courseModel = LnCourse::findOne($courseId);
                $toUserId = $model->open_id;
                if ($templateCode == "courseAllowToUser") {
                    $data = [
                        "courseName" => [
                            "value" => $courseModel->course_name,
                            "color" => "#173177"
                        ],
                        "openStartTime" => [
                            "value" => TTimeHelper::toDate($courseModel->open_start_time),
                            "color" => "#173177"
                        ],
                        "trainingAddress" => [
                            "value" => $courseModel->training_address,
                            "color" => "#173177"
                        ],
                        "remark" => [
                            "value" => '请记得到时参加，谢谢！',
                            "color" => "#173177"
                        ]
                    ];
                } else {
                    $data = [
                        "courseName" => [
                            "value" => $courseModel->course_name,
                            "color" => "#173177"
                        ],
                        "remark" => [
                            "value" => '请继续报名学习其它课程，谢谢',
                            "color" => "#173177"
                        ]
                    ];
                }
                $wechatService->sendMessageByTemplate($companyId, $toUserId, $templateId, $templateUrl, $data, $result, $errMessage);
            }

            return true;
        } else {
            return true;
        }
    }

    public function Getbook($type, $isbn, $name)
    {
        $externalSystemService = new ExternalSystemService();
        $api_address = $externalSystemService->getExternalSystemInfoByExternalSystemCode('douban-book')->api_address;
        if ($type == 'isbn') {
            $url = $api_address . 'isbn/' . $isbn;
        } elseif ($type == 'name') {
            $url = $api_address . "search?q=" . urlencode($name);
        } else {
            return false;
            exit();
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_HEADER, 0); // 不要http header 加快效率
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;

    }

    /**
     * @param $courseId
     */
    public function deleteTempCopy($kids)
    {
        if (empty($kids)) return;
        foreach ($kids as $kid) {
            $value = LnCourse::findOne($kid);
            $service = new ResourceDomainService();
            $list = LnResourceDomain::findAll(['resource_id' => $value->kid, 'resource_type' => LnResourceDomain::RESOURCE_TYPE_COURSE], false);
            foreach ($list as $val) {
                $service->StopRelationship($val);
            }
            /*停用标签关系*/
            $tagService = new TagService();
            $tagList = $tagService->getTagValue($value->kid);
            if (!empty($tagList)) {
                foreach ($tagList as $item) {
                    $tagService->stopRelationshipByTagId($item->kid);
                }
            }
            $value->delete();
        }
    }

    public function GetRegCourseByUserId($user_id, $key, $type, $limit, $offset, $current_time)
    {
        if ($type === 'all') {
            $query = LnCourseReg::find(false);
            $query->joinWith('lnCourse')
                ->joinWith('lnCourseComplete')
                ->andFilterWhere(['=', LnCourseReg::tableName() . '.reg_state', LnCourseReg::REG_STATE_APPROVED])
                ->andFilterWhere(['=', LnCourseReg::tableName() . '.user_id', $user_id])
                ->andFilterWhere(['<', LnCourseReg::tableName() . '.created_at', $current_time])
                ->orderBy(LnCourseReg::tableName() . '.updated_at desc')
                ->limit($limit)
                ->offset($offset);
        } elseif ($type === 'finished') {
            $query = LnCourseComplete::find(false);

            $query->joinWith('lnCourse')
                ->leftJoin(LnCourseReg::tableName() . ' reg', LnCourseComplete::tableName() . '.course_reg_id=reg.kid')
                ->andFilterWhere(['=', LnCourseComplete::tableName() . '.user_id', $user_id])
                ->andFilterWhere(['=', LnCourseComplete::tableName() . '.complete_type', LnCourseComplete::COMPLETE_TYPE_FINAL])
                ->andFilterWhere(['or',
                    ['=', LnCourseComplete::tableName() . '.complete_status', LnCourseComplete::COMPLETE_STATUS_DONE],
                    ['=', LnCourseComplete::tableName() . '.is_retake', LnCourseComplete::IS_RETAKE_YES]])
                ->orderBy('reg.updated_at desc')
                ->limit($limit)
                ->offset($offset);
        } elseif ($type === 'unfinished') {
            $query = LnCourseComplete::find(false);

            $completeSql = $query->andFilterWhere(['=', 'user_id', $user_id])
                ->andFilterWhere(['=', 'complete_type', LnCourseComplete::COMPLETE_TYPE_FINAL])
                ->andFilterWhere(['or', ['=', 'complete_status', LnCourseComplete::COMPLETE_STATUS_DONE],
                    ['=', 'is_retake', LnCourseComplete::IS_RETAKE_YES]])
                ->select('course_id')
                ->distinct()
                ->createCommand()->rawSql;

            $query = LnCourseReg::find(false);
            $query->joinWith('lnCourse')
                ->joinWith('lnCourseComplete')
                ->where(LnCourseReg::tableName() . '.course_id not in (' . $completeSql . ')')
                ->andFilterWhere(['=', LnCourseReg::tableName() . '.reg_state', LnCourseReg::REG_STATE_APPROVED])
                ->andFilterWhere(['=', LnCourseReg::tableName() . '.user_id', $user_id])
                ->andFilterWhere(['<', LnCourseReg::tableName() . '.created_at', $current_time])
                ->orderBy(LnCourseReg::tableName() . '.updated_at  desc')
                ->limit($limit)
                ->offset($offset);
        }

        if (!empty($key)) {
            $query->andFilterWhere(['like', LnCourse::tableName() . '.course_name', $key]);
        }
        $data = $query->all();

        $exts = array();

        foreach ($data as $v) {
            $exts[$v->kid]['count'] = LnModRes::find(false)->andWhere(['course_id' => $v->course_id])->count('kid');

            if ($type === 'finished') {
                $exts[$v->kid]['complete'] = $exts[$v->kid]['count'];
            } else {
                if ($v->lnCourseComplete->complete_status === LnCourseComplete::COMPLETE_STATUS_NOTSTART) {
                    $exts[$v->kid]['complete'] = 0;
                } elseif ($v->lnCourseComplete->complete_status === LnCourseComplete::COMPLETE_STATUS_DONE || $v->lnCourseComplete->is_retake === LnCourseComplete::IS_RETAKE_YES) {
                    $exts[$v->kid]['complete'] = $exts[$v->kid]['count'];
                } else {
                    $exts[$v->kid]['complete'] = LnResComplete::find(false)
                        ->andFilterWhere(['=', 'course_reg_id', $v->kid])
                        ->andWhere(['user_id' => $user_id, 'course_id' => $v->course_id])
                        ->andWhere(['complete_type' => LnResComplete::COMPLETE_TYPE_FINAL, 'complete_status' => LnResComplete::COMPLETE_STATUS_DONE])
                        ->count('kid');
                }
            }
        }

        return ['data' => $data, 'exts' => $exts];
    }

    /**
     * 查询场地返回json
     * @param $addressName
     * @return array|bool|string
     */
    public function getTrainingAddressByName($addressName, $addressId = null)
    {
        $addressName = preg_replace("/\(.+\)/", "", $addressName);
        if (empty($addressName)) return $addressName;
        if (!empty($addressId)) {
            $model = LnTrainingAddress::findOne($addressId);
            if (!empty($model)) {
                $title = urlencode($model->address_name) . (!empty($model->address_code) ? '(' . $model->address_code . ')' : '');
                $result = array('results' => array(array('kid' => $model->kid, 'title' => $title)));
                $result = urldecode(json_encode($result));
                return $result;
            } else {
                $result = array('results' => array(array('kid' => "", 'title' => urlencode($addressName))));
                $result = urldecode(json_encode($result));
                return $result;
            }
        } else {
            if (!empty($addressName)) {
                $result = array('results' => array(array('kid' => "", 'title' => urlencode($addressName))));
                $result = urldecode(json_encode($result));
                return $result;
            } else {
                return false;
            }
        }
    }

    /**
     * 课程查询场地
     * @param $keyword
     * @param $companyId
     * @return array|bool|yii\db\ActiveRecord[]
     */
    public function getCourseTrainingAddress($keyword, $companyId = null)
    {
        if (empty($keyword)) return false;
        $keyword = htmlspecialchars($keyword);
        if (empty($companyId)) $companyId = Yii::$app->user->identity->company_id;
        $model = LnTrainingAddress::find(false);
        $result = $model->andWhere("`address_name` like '%{$keyword}%' or `address_code` like '%{$keyword}%'")
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->andFilterWhere(['=', 'status', LnTrainingAddress::STATUS_FLAG_NORMAL])
            ->select('kid,address_name,address_code')
            ->asArray()
            ->all();

        if (empty($result)) {
            return false;
        }
        return $result;
    }

    /**
     * 取得推荐课程
     * @param $userId 用户id
     * @param $size 数量
     * @param bool $isMobile 是否移动端
     * @return array|yii\db\ActiveRecord[]
     */
    public function getRecommendCourse($userId, $size, $isMobile = false)
    {
        $currentTime = time();

        $companyId = Yii::$app->user->identity->company_id;

//        $tagService = new TagService();
//        $tagCodeId1 = $tagService->getTagCateIdByCateCode('interest');
//        $tagCodeId2 = $tagService->getTagCateIdByCateCode('course');
//
//        $tagQuery = FwTagReference::find(false);
//        $tagQuery->leftJoin(FwTag::tableName() . ' tag', FwTagReference::tableName() . '.tag_id=tag.kid and tag.is_deleted=\'0\'')
//            ->andFilterWhere(['=', FwTagReference::tableName() . '.subject_id', $user_id])
//            ->andFilterWhere(['=', 'tag.tag_category_id', $tagCodeId1])
//            ->andFilterWhere(['=', 'tag.company_id', $company_id])
//            ->select('tag.tag_value');
//
//        $tagQuerySql = $tagQuery->createCommand()->rawSql;
//
//        $tagQuery = FwTagReference::find(false);
//
//        $tagQuery->leftJoin(FwTag::tableName() . ' tag', FwTagReference::tableName() . '.tag_id=tag.kid and tag.is_deleted=\'0\'')
//            ->andWhere('tag.tag_value in (' . $tagQuerySql . ')')
//            ->andFilterWhere(['=', 'tag.tag_category_id', $tagCodeId2])
//            ->andFilterWhere(['=', 'tag.company_id', $company_id])
//            ->select(FwTagReference::tableName() . '.subject_id');
//
//        $tagQuerySql = $tagQuery->createCommand()->rawSql;

        $userDomainService = new UserDomainService();
        $domainIds = $userDomainService->getSearchListByUserId($userId);

        if (isset($domainIds) && $domainIds != null) {
            $domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');

            $domainIds = array_keys($domainIds);
        }

        $domainQuery = LnResourceDomain::find(false);
        $domainQuery->select('resource_id')
            ->andFilterWhere(['in', 'domain_id', $domainIds])
            ->andFilterWhere(['=', 'status', LnResourceDomain::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'resource_type', LnResourceDomain::RESOURCE_TYPE_COURSE])
            ->distinct();

        $domainQuerySql = $domainQuery->createCommand()->rawSql;

        $tableName = LnCourse::tableName();
        // 按课程受众过滤
        $audienceFilterSql = "NOT EXISTS(SELECT kid FROM eln_ln_resource_audience WHERE `status`='1' and resource_type='1' and resource_id=$tableName.kid and company_id='$companyId') OR" .
            " EXISTS(SELECT sam.kid FROM eln_ln_resource_audience ra INNER JOIN eln_so_audience sa ON ra.`status`='1' and ra.resource_type='1' and ra.company_id='$companyId' and ra.audience_id=sa.kid " .
            "and ra.is_deleted='0' and sa.`status`='1' and sa.company_id='$companyId' and sa.is_deleted='0' LEFT JOIN eln_so_audience_member sam ON sa.kid=sam.audience_id and sa.is_deleted='0' and sam.is_deleted='0' " .
            "WHERE $tableName.kid=ra.resource_id AND sam.user_id='$userId')";

        $courseQuery = LnCourse::find(false);
        $courseQuery->innerJoin(LnCourseRecommend::tableName() . ' cr', LnCourse::tableName() . '.kid = cr.course_id and cr.is_deleted=\'0\'')
            ->andWhere($audienceFilterSql)
            ->andWhere(LnCourse::tableName() . '.kid in (' . $domainQuerySql . ')')
            ->andFilterWhere(['=', LnCourse::tableName() . '.status', LnCourse::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['or', ['>=', 'end_time', $currentTime], 'end_time is null'])
            ->andFilterWhere(['or', ['<=', 'start_time', $currentTime], 'start_time is null'])
            ->andFilterWhere(['=', 'user_id', $userId]);

        if ($isMobile) {
            $courseQuery->andFilterWhere(['=', 'is_display_mobile', LnCourse::DISPLAY_MOBILE_YES]);
        } else {
            $courseQuery->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES]);
        }

        $result = $courseQuery
            ->orderBy('cr.recommend_ratio desc, ' . LnCourse::tableName() . '.release_at desc')
            ->limit($size)
            ->all();

        if ($result === null || count($result) < $size) {
            $tempSize = ($result === null) ? $size : ($size - count($result));

            if ($result !== null) {
                $kids = TArrayHelper::get_array_key($result, 'kid');
            }

            $courseQuery = LnCourse::find(false);
            $courseQuery
                ->andWhere($audienceFilterSql)
                ->andWhere(LnCourse::tableName() . '.kid in (' . $domainQuerySql . ')')
                ->andFilterWhere(['=', LnCourse::tableName() . '.status', LnCourse::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['or', ['>=', 'end_time', $currentTime], 'end_time is null'])
                ->andFilterWhere(['or', ['<=', 'start_time', $currentTime], 'start_time is null']);

            if ($kids) {
                $courseQuery->andFilterWhere(['not in', 'kid', $kids]);
            }

            if ($isMobile) {
                $courseQuery->andFilterWhere(['=', 'is_display_mobile', LnCourse::DISPLAY_MOBILE_YES]);
            } else {
                $courseQuery->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES]);
            }

            $addResult = $courseQuery
                ->orderBy(LnCourse::tableName() . '.release_at desc')
                ->limit($tempSize)
                ->all();

            $result = array_merge($result, $addResult);
        }

        return $result;
    }

    /**
     * 查找同企业下相同课程名称
     * @param $companyId
     * @param $courseName
     * @param null $kid
     * @return int|string
     */
    public function getSimilarCourse($companyId, $courseName, $kid = null)
    {
        if (empty($courseName)) return 0;
        $model = LnCourse::find(false);
        if (!empty($kid)) {
            $model->andWhere("kid <> '{$kid}'");
        }
        $count = $model->andFilterWhere(['=', 'company_id', $companyId])
            ->andFilterWhere(['=', 'course_name', $courseName])
            ->count('kid');
        print_r($model->createCommand()->getRawSql());
        return $count;
    }

    /**
     * 审批课程
     * @param string $courseId 课程ID
     * @param string $userId 用户ID
     * @param string $approvedBy 审批人ID
     * @param string $approvedReason 审批理由
     * @param string $approvedState 审批状态
     * @return bool 成功与否
     */
    public function approveCourse($courseId, $userId, $approvedBy, $approvedReason, $approvedState)
    {
        $courseModel = LnCourse::findOne($courseId);
        /*更新报名数据*/
        if (!empty($approvedBy) && $courseModel->course_type == LnCourse::COURSE_TYPE_FACETOFACE) {
            $enrollData = LnCourseEnroll::find(false)
                ->andFilterWhere(['course_id' => $courseId, 'user_id' => $userId, 'approved_state' => LnCourseEnroll::APPROVED_STATE_APPLING])
                ->one();

            if (!empty($enrollData)) {
                $enrollData->approved_state = $approvedState;
                $enrollData->approved_by = $approvedBy;
                $enrollData->approved_at = time();
                $enrollData->approved_reason = $approvedReason;
//                $enrollData->cancel_state = $approvedState;
                $enrollData->save();
            }

        }
        /*更新注册表*/
        $regState = LnCourseReg::find(false)->andFilterWhere(['course_id' => $courseId, 'user_id' => $userId, 'reg_state' => LnCourseReg::REG_STATE_APPLING])->one();

        if (!empty($regState)) {
            $regState->reg_state = $approvedState;
            $regState->approved_by = $approvedBy;
            $regState->approved_at = time();
            $regState->approved_reason = $approvedReason;
            if ($regState->save() !== false) {
                return true;
            } else {
                return false;
            }
        }
    }


    /**
     * 取消课程
     * @param string $courseId 课程ID
     * @param string $userId 用户ID
     * @param string $cancelBy 取消人ID
     * @param string $cancelReason 取消理由
     * @param string $cancelState 取消状态
     * @return bool 成功与否
     */
    public function cancelCourse($courseId, $userId, $cancelBy, $cancelReason, $cancelState)
    {
        $courseModel = LnCourse::findOne($courseId);
        /*更新报名数据*/
        if (!empty($cancelBy) && $courseModel->course_type == LnCourse::COURSE_TYPE_FACETOFACE) {
            $enrollData = LnCourseEnroll::find(false)
                ->andFilterWhere(['course_id' => $courseId, 'user_id' => $userId, 'approved_state' => LnCourseEnroll::APPROVED_STATE_APPROVED])
                ->one();

            if (!empty($enrollData)) {
//                $enrollData->approved_state = LnCourseEnroll::APPROVED_STATE_CANCELED;
//                $enrollData->approved_by = $cancelBy;
//                $enrollData->approved_at = time();
//                $enrollData->approved_reason = $cancelReason;
                $enrollData->cancel_state = $cancelState;
                $enrollData->cancel_by = $cancelBy;
                $enrollData->cancel_at = time();
                $enrollData->cancel_reason = $cancelReason;
                // 请假删除记录
                $enrollData->is_deleted = LnCourseEnroll::DISPLAY_FLAG_YES;

                if ($enrollData->save() !== false) {
                    // 删除注册表数据
                    LnCourseReg::deleteAll('course_id=:courseId and user_id=:userId', [':courseId' => $courseId, ':userId' => $userId]);

                    $mailService = new MailService();
                    $mailService->cancelCourse($userId, $courseModel);

                    $timelineService = new TimelineService();
                    $taskService = new TaskService();

                    $timelineService->deleteTimelineByTodoCourse($userId, $courseId);
                    $taskService->deleteTaskUserByCourse($userId, $courseId);

                    return true;
                } else {
                    return false;
                }
            }
        }

        return false;
        /*更新注册表*/
//        $regState = LnCourseReg::find(false)->andFilterWhere(['course_id' => $courseId, 'user_id' => $userId, 'reg_state' => LnCourseReg::REG_STATE_APPROVED])->one();
//
//        if (!empty($regState)) {
//            $regState->reg_state = LnCourseReg::REG_STATE_CANCELED;
//            $regState->approved_by = $cancelBy;
//            $regState->approved_at = time();
//            $regState->approved_reason = $cancelReason;
//            if ($regState->save() !== false) {
//                return true;
//            } else {
//                return false;
//            }
//        }
    }

    /**
     * 生成菜单
     * @param string $courseFinalCompleteId 课程最终完成记录ID
     * @param string $courseId 课程ID
     * @param bool $isReg 是否已经注册
     * @param bool $isCourseComplete 是否已经完成
     * @param string $mode 播放模式
     * @param bool $isOnlineCourse 是否在线课程
     * @param bool $isRandom 是否随机播放
     * @param string $openStatus 开课状态
     * @param bool $courseMods 课程模块
     * @param bool $require_score 是否包含成绩信息
     * @param bool $lecturer_mode 是否为讲师身份
     * @return array 菜单项
     */
    public function genCatalogMenu($courseFinalCompleteId, $courseId, $isReg, $isCourseComplete, $mode = self::PLAY_MODE_PREVIEW, $isOnlineCourse,
                                   $isRandom, $openStatus, $courseMods = false, &$studyModResId, $require_score = false, $lecturer_mode = false)
    {
        $resourceCompleteService = new ResourceCompleteService();

        $map = [];//mod-res-id => score
        if ($require_score) {
            $user_id = Yii::$app->user->getId();
            $resourceService = new ResourceService();
            $scoreResult = $resourceService->getCourseResScoreDetail($courseId, $user_id);
            array_walk($scoreResult['data'], function ($val) use (&$map) {
                $map[$val['modResId']] = $val['score'];
            });
        }

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
        foreach ($courseMods as $modNum => $mod) {

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

                    if ($require_score)
                        $courseMods[$modNum]['courseitems'][$num]['score'] = isset($map[$modResId]) ? $map[$modResId] : '--';

                    if (!$isOnlineCourse) {
                        /*预览模式也可见*/
                        if ($mode != self::PLAY_MODE_PREVIEW && !$lecturer_mode) {
                            $displayItem = $publishStatus == LnModRes::PUBLIC_STATUS_YES;
                        }
                    }

                    $courseMods[$modNum]['courseitems'][$num]['displayItem'] = $displayItem;

                    if ($displayItem) {

                        if (empty($firstAvailableModResId)) {
                            $firstAvailableModResId = $modResId;
                        }

                        if ($mode == self::PLAY_MODE_PREVIEW) {
//                            $catalogMenu .= $beginItemUnLearnStr;
                            if (empty($studyModResId)) {
                                $studyModResId = $modResId;
                            }
                        } else {
                            if ($isReg && !$isCourseComplete) {
                                $resourceCompleteService->checkResourceStatus($courseFinalCompleteId, $modResId, $learning, $learned);
//                                $learned = $resourceCompleteService->isResComplete($courseCompleteId, $modResId);
//                                $learning = $resourceCompleteService->IsResDoing($courseCompleteId, $modResId);


                                if ($learned) {
                                    $courseMods[$modNum]['courseitems'][$num]['learning_status'] = "learned";
//                                    $catalogMenu .= $beginItemLearnedStr;
                                } else if ($learning) {
                                    $courseMods[$modNum]['courseitems'][$num]['learning_status'] = "learning";
//                                    $catalogMenu .= $beginItemLearningStr;
                                } else {
                                    $courseMods[$modNum]['courseitems'][$num]['learning_status'] = "unlearn";
//                                    $catalogMenu .= $beginItemUnLearnStr;
                                }

                                if (!$learned && empty($studyModResId)) {
                                    $studyModResId = $modResId;
                                }
                            } else if ($isReg && $isCourseComplete) {
//                                $learned = true;
                                $courseMods[$modNum]['courseitems'][$num]['learning_status'] = "learned";
//                                $catalogMenu .= $beginItemLearnedStr;
                            } else {
//                                $learned = false;
                                $courseMods[$modNum]['courseitems'][$num]['learning_status'] = "unlearn";
//                                $catalogMenu .= $beginItemUnLearnStr;
                            }
                        }

                        $courseMods[$modNum]['courseitems'][$num]['mode'] = $mode;
                        if ($mode == self::PLAY_MODE_NORMAL) {
                            if ($isReg && !$isCourseComplete) {
                                $courseMods[$modNum]['courseitems'][$num]['canRun'] = $canRun;

                            } else {
                                $courseMods[$modNum]['courseitems'][$num]['canRun'] = false;
                            }
                        } else {
                            $courseMods[$modNum]['courseitems'][$num]['canRun'] = true;
                        }

                    }


                    if ($canRun && !$isRandom && !$learned) {
                        $canRun = false;
                    }
                }
            }
        }

        //如果循环发现没有合适的modResId，此时已完成的也可再学一次
        if (empty($studyModResId)) {
            $studyModResId = $firstAvailableModResId;
        }

        return $courseMods;

    }

    /**
     * 面授成绩管理
     * @param $id
     * @param $params
     */
    public function getCourseGrade($id, $params)
    {
        $model = LnCourse::findOne($id);
        /*判断课程类型*/
        if ($model->course_type == LnCourse::COURSE_TYPE_ONLINE) {
            return;
        }
        /*课程开课状态*/
        if ($model->open_status == LnCourse::COURSE_NOT_START) {
            return;
        }
        $courseEnrollService = new CourseEnrollService();
        $result = $courseEnrollService->getCourseEnroll($id, $params);
        return $result;
    }

    public function stopCourseTeacher($courseId)
    {
        $attributes = ['status' => self::STATUS_FLAG_STOP];
        $condition = "course_id=:course_id and status =:status";
        $param = [
            ':course_id' => $courseId,
            ':status' => self::STATUS_FLAG_NORMAL
        ];
        return LnCourseTeacher::updateAll($attributes, $condition, $param);
    }

    /**
     * 课程详情
     * @param $user_id
     * @param $course_id
     * @param bool|false $is_manager
     * @param bool|false $require_menu
     * @param bool|false $require_enroll_info
     * @return array
     */
    public function detail($user_id, $course_id, $is_manager = false, $require_menu = false, $require_enroll_info = false, $mode = self::PLAY_MODE_NORMAL, $require_score = false, $sort_menu = false)
    {
        $courseModel = LnCourse::findOne($course_id);
        $now = time();
        if (empty($courseModel)) {
            return ['number' => '404', 'code' => 'fail', 'param' => Yii::t('frontend', 'course_does_not_exist_or_is_not_published')];
        }
        $userDomainService = new UserDomainService();
        $domain = $userDomainService->getSearchListByUserId($user_id);
        if (empty($domain)) {
            return ['number' => '403', 'code' => 'fail', 'param' => Yii::t('common', 'no_permission_to_view_this_course')];
        }
        $domain_id = ArrayHelper::map($domain, 'kid', 'kid');
        $domain_id = array_keys($domain_id);

        $resourceDomainService = new ResourceDomainService();

        if (!$resourceDomainService->IsRelationshipDomainValidated($course_id, LnResourceDomain::RESOURCE_TYPE_COURSE, $domain_id)) {
            return ['number' => '403', 'code' => 'fail', 'param' => Yii::t('frontend', 'invalid_field')];
        }

        $courseRegId = null;
        /*判断是否注册*/
        $isReg = $this->isUserRegCourse($user_id, $course_id, $courseRegId);
        $isOnlineCourse = $courseModel->course_type == LnCourse::COURSE_TYPE_ONLINE ? true : false;
        $isRandom = $courseModel->mod_type == LnCourse::MOD_TYPE_RANDOM ? true : false;
        $openStatus = $courseModel->open_status;
        $modResId = null;
        $courseCompleteFinalId = null;
        $resourceService = new ResourceService();
        $courseMods = $resourceService->getCourseMods($course_id);
        $isSignin = false;

        if (!$isReg) {
            /*判断是否有受众关系*/
            $resourceAudienceService = new ResourceAudienceService();
            $isCourseAudience = $resourceAudienceService->isResourceAudience($user_id, $course_id);
            if (!$isCourseAudience) {
                return ['number' => '400', 'code' => 'fail', 'param' => Yii::t('frontend', 'is_not_within_the_audience')];
            }

            if (!empty($courseModel->start_time) && $courseModel->start_time > $now) {
                return ['number' => '400', 'code' => 'fail', 'param' => Yii::t('frontend', 'not_to_start_time')];
            }

            if (!empty($courseModel->end_time) && $courseModel->end_time < $now) {
                return ['number' => '400', 'code' => 'fail', 'param' => Yii::t('frontend', 'due_date')];
            }
            $isCourseDoing = false;
            $isCourseComplete = false;
            $isCourseRetake = false;
            $currentAttempt = 0;
        }
        $courseCompleteService = new CourseCompleteService();
//        $courseCompleteService->initCourseCompleteInfo($courseRegId, $course_id, $user_id);
        $courseCompleteFinalModel = $courseCompleteService->getLastCourseCompleteInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);
        $courseCompleteFinalId = $courseCompleteFinalModel->kid;
        $currentAttempt = $courseCompleteFinalModel->attempt_number;
        $courseCompleteService->checkCourseStatus($courseCompleteFinalModel, $isCourseDoing, $isCourseComplete, $isCourseRetake);

        $canRating = !$this->isRating($user_id, $course_id);
        if (!$isOnlineCourse) {
            $allow_enroll = $this->isEnroll($user_id, $course_id);
            if (!$allow_enroll) $canRating = false;

            $isSignin = $this->isSigninInToday($user_id, $course_id);
        }

        $rating = number_format($this->getCourseMarkByID($course_id), 1);
        $rating_count = $this->getCourseMarkCountByID($course_id);

        LnCourse::addFieldNumber($course_id, 'visit_number');

        /*获取课程证书*/
        $certificationModel = new LnCourseCertification();
        $certificationTemplatesUrl = $certificationModel->getTemplatesUrl($courseModel->kid);
        /*获取课程讲师*/
        $teacherModel = new LnCourseTeacher();
        $teacher = $teacherModel->getTeacherAll($courseModel->kid);
        /*获取报名人数*/
        $enrollInfo = null;
        $sign_status_data = null;
        if (!$isOnlineCourse) {
            $enrollRegNumber = $this->getEnrollNumber($courseModel->kid, [LnCourseEnroll::ENROLL_TYPE_REG, LnCourseEnroll::ENROLL_TYPE_ALLOW]);
            $enrollAlternatenNumber = $this->getEnrollNumber($courseModel->kid, LnCourseEnroll::ENROLL_TYPE_ALTERNATE);
            if ($require_enroll_info) $enrollInfo = $this->getUserEnrollInfo($user_id, $courseModel->kid);
            $allow_enroll = $this->isEnroll($user_id, $course_id);
            if (!$allow_enroll) $canRating = false;
            $courseSignInSettingService = new CourseSignInSettingService();
            $getSignData = $courseSignInSettingService->getRecentSignInSettingId($course_id, $now);/*查询签到配置*/
            if (!empty($getSignData)) {
                $courseSignInService = new CourseSignInService();
                $sign_status_data = $courseSignInService->getStudentSignInStatus($course_id, $user_id);
            }
        } else {
            $enrollRegNumber = 0;
            $enrollAlternatenNumber = 0;
        }

        $catalogMenu = [];
        if ($require_menu) {
            $catalogMenu = $this->genCatalogMenu(
                $courseCompleteFinalId,
                $course_id,
                $isReg,
                $isCourseComplete,
                $mode,
                $isOnlineCourse,
                $isRandom,
                $openStatus,
                $courseMods,
                $modResId,
                $require_score
            );
            if ($sort_menu) {
                $tmp = [];
                foreach ($catalogMenu as $m) {
                    $tmp[] = $m;
                }
                $catalogMenu = $tmp;
                unset($tmp);
            }
        }
        /*学习按钮*/
        $learnStatus = $this->learnStatus($user_id, $course_id, $modResId);

        $fields = [
            'theme_url',
            'kid',
            'course_code',
            'course_name',
            'default_credit',
            'course_period',
            'course_period_unit',
            'course_desc_nohtml',
            'course_level',
            'course_type',
            'open_status',
            'currency',
            'course_language',
            'is_display_mobile',
            'approval_rule',
            'created_at',
            'updated_at',
            'course_price',
            'training_address',
            'start_time',
            'end_time',
            'enroll_start_time',
            'enroll_end_time',
            'open_start_time',
            'open_end_time'
        ];
        $_course = [];
        foreach ($fields as $field) {
            if (!isset($courseModel->{$field})) {
                continue;
            }
            $_course[$field] = $courseModel->{$field};
        }
        $_course['course_Category_Name'] = $courseModel->getCourseCategoryText();

        $dictionaryService = new DictionaryService();
        $_course['course_level'] = $dictionaryService->getDictionaryNameByValue('course_level', $courseModel->course_level);

        $data = [
            'course' => $_course,
            'courseCompleteFinalId' => $courseCompleteFinalId,
            'isReg' => $isReg,
            'courseRegId' => $courseRegId,
            'isCourseComplete' => $isCourseComplete,
            'isCourseRetake' => $isCourseRetake,
            'rating' => $rating,
            'rating_count' => $rating_count,
            'canRating' => $canRating,
            'catalogMenu' => $catalogMenu,
            'modResId' => $modResId,
            'isManager' => $is_manager,
            'certificationTemplatesUrl' => $certificationTemplatesUrl,
            'teacher' => $teacher,
            'isOnlineCourse' => $isOnlineCourse,
            'isRandom' => $isRandom,
            'openStatus' => $openStatus,
            'enrollRegNumber' => $enrollRegNumber,
            'enrollAlternatenNumber' => $enrollAlternatenNumber,
            'learnStatus' => $learnStatus,
            'enrollInfo' => $enrollInfo,
            'isSignin' => $isSignin,
            'currentAttempt' => $currentAttempt,
            'sign_status_data' => $sign_status_data,
            'limit_number' => $courseModel->limit_number == null ? 0 : $courseModel->limit_number,
            'last_number' => $courseModel->limit_number - $enrollRegNumber,
            'allow_over_number' => $courseModel->allow_over_number == null ? 0 : $courseModel->allow_over_number,


        ];

        return ['code' => 'OK', 'param' => '', 'data' => $data, 'number' => 200];
    }

    /**
     * 学习按钮
     * @param $user_id
     * @param $course_id
     * @param $modResId
     * @return array
     */
    public function learnStatus($user_id, $course_id, $modResId)
    {
        $courseModel = LnCourse::findOne($course_id);
        $now = time();
        $status = [];
        $courseRegId = null;
        $isReg = $this->isUserRegCourse($user_id, $course_id, $courseRegId);
        $isCourseComplete = false;
        $isCourseRetake = false;
        $currentAttempt = 0;
        $isCourseDoing = false;
        if ($isReg) {
            $courseCompleteService = new CourseCompleteService();
            $courseCompleteService->initCourseCompleteInfo($courseRegId, $course_id, $user_id);
            $courseCompleteFinalModel = $courseCompleteService->getLastCourseCompleteInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);
            $courseCompleteFinalId = $courseCompleteFinalModel->kid;
            $currentAttempt = $courseCompleteFinalModel->attempt_number;
            $courseCompleteService->checkCourseStatus($courseCompleteFinalModel, $isCourseDoing, $isCourseComplete, $isCourseRetake);
        }
        /*未上架*/
        if (!empty($startTime) && $startTime > $now) {
            return [
                'code' => 'NOT_PUTAWAY',
                'label' => Yii::t('frontend', 'no_shelf'),
            ];
        }
        /*已下架*/
        if (!empty($endTime) && $endTime < $now) {
            return [
                'code' => 'TAKEN_OFF',
                'label' => Yii::t('frontend', 'under_shelf'),
            ];
        }
        $regInfo = $this->getUserRegInfo($user_id, $courseModel->kid);
        if ($courseModel->course_type == LnCourse::COURSE_TYPE_ONLINE) {
            /*注册*/
            if (!$isReg) {
                return [
                    'code' => 'REGISTER_ONLINE',
                    'label' => Yii::t('common', 'signup')
                ];
            }
            if ($isCourseComplete) {
                /*重学*/
                if ($courseModel->max_attempt == 0 || intval($currentAttempt) < $courseModel->max_attempt) {
                    return [
                        'code' => 'RETAKE',
                        'label' => Yii::t('frontend', 'reset_study')
                    ];
                } else {
                    /*已结束*/
                    return [
                        'code' => 'END_UP',
                        'label' => Yii::t('common', 'status_2')
                    ];
                }
            }
            /*审批中*/
            if ($courseModel->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_APPLING) {
                return [
                    'code' => 'APPROVING',
                    'label' => Yii::t('frontend', 'approvaling')
                ];
            }
            /*审批未通过*/
            if ($courseModel->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_REJECTED) {
                return [
                    'code' => 'APPROVE_NOT_PASSED',
                    'label' => Yii::t('frontend', 'approval_no_pass')
                ];
            }
            /*作废*/
            if ($courseModel->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_CANCELED) {
                return [
                    'code' => 'CANCELED',
                    'label' => Yii::t('frontend', 'invalid')
                ];
            }
            /*放弃*/
            if ($isCourseRetake) {
                $status[] = [
                    'code' => 'QUIT_AND_LEARN',
                    'label' => Yii::t('frontend', 'give_up_learning')
                ];
            }
            /*开始*/
            if (empty($modResId)) {
                $status[] = [
                    'code' => 'GETTING_STARTED',
                    'label' => Yii::t('frontend', 'start_learning')
                ];
                return $status;
            }
            /*继续:开始*/
            $status[] = $isCourseDoing ? [
                'code' => 'CONTINUE_LEARN',
                'label' => Yii::t('frontend', 'continue_learning')
            ] : [
                'code' => 'GETTING_STARTED',
                'label' => Yii::t('frontend', 'start_learning')
            ];
            return $status;
        }
        $enrollInfo = $this->getUserEnrollInfo($user_id, $courseModel->kid);
        if (empty($enrollInfo)) {
            /*未开始*/
            if ($courseModel->enroll_start_time != null && $courseModel->enroll_start_time > $now) {
                return [
                    'code' => 'NOT_STARTED',
                    'label' => Yii::t('common', 'complete_eroll_status_0')
                ];
            }
            /*已结束*/
            if ($courseModel->enroll_end_time != null && $courseModel->enroll_end_time < $now) {
                return [
                    'code' => 'REGISTRATION_ENDS',
                    'label' => Yii::t('common', 'status_enroll_2')
                ];
            }
            /*已结束*/
            if ($courseModel->open_status == LnCourse::COURSE_END) {
                return [
                    'code' => 'END_UP',
                    'label' => Yii::t('common', 'status_2')
                ];
            }
            /*开课后不能再报名*/
            if ($courseModel->open_status == LnCourse::COURSE_START){
                return [
                    'code' => 'REGISTRATION_ENDS',
                    'label' => Yii::t('common', 'status_2'),
                ];
            }
            /*报名*/
            return [
                'code' => 'REGISTER',
                'label' => Yii::t('frontend', 'enroll')
            ];
        }

        /*报名失败*/
        if ($enrollInfo->enroll_type == LnCourseEnroll::ENROLL_TYPE_DISALLOW) {
            return [
                'code' => 'FAILED_TO_ENROLL',
                'label' => Yii::t('frontend', 'enroll_failed')
            ];
        }

        if ($enrollInfo->enroll_type == LnCourseEnroll::ENROLL_TYPE_REG || $enrollInfo->enroll_type == LnCourseEnroll::ENROLL_TYPE_ALTERNATE) {
            /*审批中*/
            if ($courseModel->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_APPLING) {
                return [
                    'code' => 'APPROVING',
                    'label' => Yii::t('frontend', 'approvaling')
                ];
            }
            /*审批未通过*/
            if ($courseModel->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_REJECTED) {
                return [
                    'code' => 'APPROVE_NOT_PASSED',
                    'label' => Yii::t('frontend', 'approval_no_pass')
                ];
            }
            /*作废*/
            if ($courseModel->approval_rule != LnCourse::COURSE_APPROVAL_DEFAULT && $regInfo->reg_state == LnCourseReg::REG_STATE_CANCELED) {
                return [
                    'code' => 'CANCELED',
                    'label' => Yii::t('frontend', 'invalid')
                ];
            }
            /*报名审核中*/
            return [
                'code' => 'ENROLL_APPROVING',
                'label' => Yii::t('frontend', 'enroll_allowing')
            ];
        }
        /*报名成功*/
        if ($courseModel->open_status == LnCourse::COURSE_NOT_START) {
            return [
                'code' => 'ENROLL_SUCCEED',
                'label' => Yii::t('frontend', 'enroll_success')
            ];
        }
        /*已结束*/
        if ($courseModel->open_status == LnCourse::COURSE_END) {
            return [
                'code' => 'END_UP',
                'label' => Yii::t('common', 'status_2')
            ];
        }
        /*已结束*/
        /*if ($courseModel->open_end_time != null && $courseModel->open_end_time <= $now) {
            return [
                'code' => 'END_UP',
                'label' => Yii::t('common', 'status_2')
            ];
        }*/
        /*报名*/
        if (!$isReg) {
            return [
                'code' => 'REGISTER',
                'label' => Yii::t('frontend', 'enroll')
            ];
        }
        /*已完成*/
        if ($isCourseComplete) {
            return [
                'code' => 'COMPLETED',
                'label' => Yii::t('common', 'complete_status_2')
            ];
        }
        /*开始*/
        if (empty($modResId)) {
            return [
                'code' => 'GETTING_STARTED',
                'label' => Yii::t('frontend', 'start_learning')
            ];
        }

        /*继续:开始*/
        return $isCourseDoing ? [
            'code' => 'CONTINUE_LEARN',
            'label' => Yii::t('frontend', 'continue_learning')
        ] : [
            'code' => 'GETTING_STARTED',
            'label' => Yii::t('frontend', 'start_learning')
        ];
    }
}