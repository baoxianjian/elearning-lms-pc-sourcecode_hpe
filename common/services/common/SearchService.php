<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/5
 * Time: 14:19
 */

namespace common\services\common;


use common\base\BaseService;
use common\models\framework\FwCompany;
use common\models\framework\FwOrgnization;
use common\models\framework\FwPosition;
use common\models\framework\FwUser;
use common\models\framework\FwUserManager;
use common\models\learning\LnCourse;
use common\models\learning\LnExamination;
use common\models\learning\LnInvestigation;
use common\models\learning\LnResourceAudience;
use common\models\learning\LnResourceDomain;
use common\models\social\SoAudience;
use common\base\BaseActiveRecord;
use components\widgets\TPagination;
use Yii;
use yii\db\Query;

class SearchService extends BaseService
{

    /**
     * 根据Name获取岗位和用户信息
     * @param $name 关键字
     * @param $filterSelf 是否过滤自己
     * @return array
     */
    public function searchByName($name, $filterSelf = false)
    {
        $company_id = Yii::$app->user->identity->company_id;

        $query_org = new Query();
        $query_user = new Query();
        $query_org = $query_org
            ->from(FwPosition::tableName())
            ->andFilterWhere(['=', 'is_deleted', BaseActiveRecord::DELETE_FLAG_NO])
            ->andFilterWhere(['like', 'position_name', $name])
            ->andFilterWhere(['=', 'company_id', $company_id])
            ->select('kid,position_name as `name`,\'pos\' as `type`');

        if ($filterSelf) {
            $uid = Yii::$app->user->getId();

            $query_user->andFilterWhere(['<>', 'kid', $uid]);
        }
        $result = $query_user
            ->from(FwUser::tableName())
            ->andFilterWhere(['like', 'real_name', $name])
            ->andFilterWhere(['=', 'is_deleted', BaseActiveRecord::DELETE_FLAG_NO])
            ->andFilterWhere(['=', 'company_id', $company_id])
            ->select('kid,real_name as `name`,\'user\' as `type`')
            ->union($query_org)
            ->all();

        return $result;
    }

    /**
     * 根据Name获取用户信息
     * @param $name 关键字
     * @param $filterSelf 是否过滤自己
     * @return array
     */
    public function SearchPeopleByName($name, $filterSelf = false)
    {
        $company_id = Yii::$app->user->identity->company_id;

        $query = new Query();

        if ($filterSelf) {
            $uid = Yii::$app->user->getId();

            $query->andFilterWhere(['<>', 'kid', $uid]);
        }
        $result = $query
            ->from(FwUser::tableName())
            ->andFilterWhere(['like', 'real_name', $name])
            ->andFilterWhere(['=', 'is_deleted', BaseActiveRecord::DELETE_FLAG_NO])
            ->andFilterWhere(['=', 'company_id', $company_id])
            ->select('kid,real_name as `name`,email,concat(ifnull(`real_name`,`user_name`),"(",ifnull(`email`,`user_name`),")") as `title`')
            ->addOrderBy('real_name')
            ->all();

        return $result;
    }

    /**
     * 根据关键字搜索课程
     * @param string $user_id 用户id
     * @param array $domain_ids 域id列表
     * @param string $keyword 关键字
     * @param array $selected 已选项过滤
     * @param string $course_type 课程类型
     * @return array
     */
    public function SearchCourseByKeyword($user_id, $domain_ids, $keyword, $selected, $course_type = null)
    {
        $result = array();

        $currentTime = time();

        $domainQuery = LnResourceDomain::find(false);
        $domainQuery->select('resource_id')
            ->andFilterWhere(['in', 'domain_id', $domain_ids])
            ->andFilterWhere(['=', 'status', LnResourceDomain::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'resource_type', LnResourceDomain::RESOURCE_TYPE_COURSE])
            ->distinct();

        $domainQuerySql = $domainQuery->createCommand()->rawSql;

        $courseQuery = LnCourse::find(false);
        $courseQuery
            ->leftJoin(LnResourceAudience::tableName() . ' ra', LnCourse::tableName() . '.kid = ra.resource_id and ra.resource_type=\'1\' and ra.status=\'1\' and ra.is_deleted=\'0\'')
            ->leftJoin(SoAudience::tableName() . ' au', 'au.kid = ra.audience_id and au.status=\'1\' and au.is_deleted=\'0\'')
            ->andWhere(LnCourse::tableName() . '.kid in (' . $domainQuerySql . ')')
            ->andFilterWhere(['=', LnCourse::tableName() . '.status', LnCourse::STATUS_FLAG_NORMAL])
//            ->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES])
            ->andFilterWhere(['or', ['>=', LnCourse::tableName() . '.end_time', $currentTime], 'end_time is null'])
            ->andFilterWhere(['or', ['<=', LnCourse::tableName() . '.start_time', $currentTime], 'start_time is null'])
            ->select([LnCourse::tableName() . '.*', 'audienceName' => 'GROUP_CONCAT(au.audience_name)'])
            ->groupBy(LnCourse::tableName() . '.kid');

        //关键字搜索
        if ($keyword) {
            $courseQuery->andFilterWhere(['or', ['like', 'course_name', $keyword], ['like', 'course_desc_nohtml', $keyword]]);
        }

        //排除已选择课程
        if ($selected) {
            $courseQuery->andFilterWhere(['not in', LnCourse::tableName() . '.kid', $selected]);
        }

        //筛选课程类型
        if ($course_type !== null) {
            $courseQuery->andFilterWhere(['=', 'course_type', $course_type]);
        }

        $count = $courseQuery->count();
        $pages = new TPagination(['defaultPageSize' => '5', 'totalCount' => $count]);
        $result['page'] = $pages;

        $courses = $courseQuery->orderBy(LnCourse::tableName() . '.updated_at desc')
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        $result['data'] = $courses;

        return $result;
    }

    /**
     * 根据公司id获取用户信息
     * @param $company_id 公司id
     * @param bool $filterSelf 是否过滤自己
     * @return array
     */
    public function GetPeopleByCompanyId($company_id, $filterSelf = false)
    {
        $query = new Query();

        if ($filterSelf) {
            $uid = Yii::$app->user->getId();

            $query->andFilterWhere(['<>', 'kid', $uid]);
        }
        $result = $query
            ->from(FwUser::tableName())
            ->andFilterWhere(['=', 'is_deleted', BaseActiveRecord::DELETE_FLAG_NO])
            ->andFilterWhere(['=', 'company_id', $company_id])
            ->select('kid')
            ->all();

        return $result;
    }

    /**
     * 根据Name获取任务推送对象
     * @param string $name 关键字
     * @param string $domainId 域ID
     * @param bool $filterSelf 是否过滤自己
     * @return array
     */
    public function SearchObjectByName($name, $domainId, $filterSelf = false)
    {
        $company_id = Yii::$app->user->identity->company_id;
        $uid = Yii::$app->user->getId();

        $query_org = new Query();
        $query_pos = new Query();
        $query_aud = new Query();
        $query_user = new Query();

        $query_org = $query_org
            ->from(FwOrgnization::tableName())
            ->andFilterWhere(['=', 'is_deleted', BaseActiveRecord::DELETE_FLAG_NO])
            ->andFilterWhere(['like', 'orgnization_name', $name])
            ->andFilterWhere(['=', 'company_id', $company_id])
            ->select('kid,concat("[' . Yii::t('common', 'department') . ']",`orgnization_name`) as `name`,orgnization_code as code,\'1\' as `type`');
        $query_pos = $query_pos
            ->from(FwPosition::tableName())
            ->andFilterWhere(['=', 'is_deleted', BaseActiveRecord::DELETE_FLAG_NO])
            ->andFilterWhere(['like', 'position_name', $name])
            ->andFilterWhere(['or', ['=', 'company_id', $company_id], 'company_id is null'])
            ->select('kid,concat("[' . Yii::t('common', 'position') . ']",`position_name`) as `name`,position_code as code,\'2\' as `type`');

        $query_aud = $query_aud
            ->from(SoAudience::tableName())
            ->andFilterWhere(['=', 'is_deleted', BaseActiveRecord::DELETE_FLAG_NO])
            ->andFilterWhere(['=', 'status', BaseActiveRecord::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'owner_id', $uid])
            ->andFilterWhere(['like', 'audience_name', $name])
            ->andFilterWhere(['or', ['=', 'company_id', $company_id], 'company_id is null'])
            ->select('kid,concat("[' . Yii::t('common', '受众') . ']",`audience_name`) as `name`,audience_code as code,\'3\' as `type`');

        if ($filterSelf) {
            $query_user->andFilterWhere(['<>', 'kid', $uid]);
        }
        $result = $query_user
            ->from(FwUser::tableName())
            ->andFilterWhere(['like', 'real_name', $name])
            ->andFilterWhere(['=', 'is_deleted', BaseActiveRecord::DELETE_FLAG_NO])
            ->andFilterWhere(['=', 'company_id', $company_id])
            ->andFilterWhere(['=', 'domain_id', $domainId])
            ->select('kid,concat(`real_name`,"(",ifnull(`email`,`user_name`),")") as `name`,user_no as code,\'4\' as `type`')
            ->union($query_org)
            ->union($query_aud)
            ->union($query_pos)
            ->all();

        return $result;
    }

    /**
     * 根据关键字搜索考试
     * @param $company_id 公司id
     * @param $keyword 关键字
     * @param $selected 已选项过滤
     * @return array
     */
    public function SearchExamByKeyword($company_id, $keyword, $selected)
    {
        $result = array();

        $currentTime = time();

        $examQuery = LnExamination::find(false);
        $examQuery
            ->andFilterWhere(['=', 'examination_range', LnExamination::EXAMINATION_RANGE_SELF])
            ->andFilterWhere(['=', 'release_status', LnExamination::RELEASE_STATUS_YES])
            ->andFilterWhere(['=', 'company_id', $company_id])
            ->andFilterWhere(['or', ['>=', 'end_at', $currentTime], 'end_at is null']);
//            ->andFilterWhere(['or', ['<=', 'start_at', $currentTime], 'start_at is null']);

        //关键字搜索
        if ($keyword) {
            $examQuery->andFilterWhere(['or', ['like', 'title', $keyword], ['like', 'description', $keyword]]);
        }

        //排除已选择考试
        if ($selected) {
            $examQuery->andFilterWhere(['not in', 'kid', $selected]);
        }

        $count = $examQuery->count();
        $pages = new TPagination(['defaultPageSize' => '5', 'totalCount' => $count]);

        $result['page'] = $pages;

        $exams = $examQuery->orderBy('updated_at desc')
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        $result['data'] = $exams;

        return $result;
    }

    /**
     * 根据关键字搜索调查
     * @param $company_id 公司id
     * @param $keyword 关键字
     * @param $selected 已选项过滤
     * @return array
     */
    public function SearchSurveyByKeyword($company_id, $keyword, $selected)
    {
        $result = array();

        $currentTime = time();

        $surveyQuery = LnInvestigation::find(false);
        $surveyQuery
            ->andFilterWhere(['=', 'investigation_range', LnInvestigation::INVESTIGATION_RANGE_NORMAL])
            ->andFilterWhere(['=', 'status', LnInvestigation::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'company_id', $company_id])
            ->andFilterWhere(['or', ['>=', 'end_at', $currentTime], 'end_at is null']);
//            ->andFilterWhere(['or', ['<=', 'start_at', $currentTime], 'start_at is null']);

        //关键字搜索
        if ($keyword) {
            $surveyQuery->andFilterWhere(['or', ['like', 'title', $keyword], ['like', 'description', $keyword]]);
        }

        //排除已选择考试
        if ($selected) {
            $surveyQuery->andFilterWhere(['not in', 'kid', $selected]);
        }

        $count = $surveyQuery->count();
        $pages = new TPagination(['defaultPageSize' => '5', 'totalCount' => $count]);

        $result['page'] = $pages;

        $exams = $surveyQuery->orderBy('updated_at desc')
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        $result['data'] = $exams;

        return $result;
    }

    /**
     * 获取直线经理下所有用户
     * @param string $reportManagerId 直线经理ID
     * @param string $keyword 关键字
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getUserByReportManager($reportManagerId, $keyword = "")
    {
        $cacheKey = 'ReportManagerUserList_' . $reportManagerId . $keyword;

        if (Yii::$app->cache->exists($cacheKey)) {
            return Yii::$app->cache->get($cacheKey);
        }

        $userModel = FwUser::findOne($reportManagerId);
        $reportingModel = FwCompany::findOne($userModel->company_id)->reporting_model;
        $userManageQuery = FwUserManager::find(false);
        $userManageQuery->select('user_id')
            ->andFilterWhere(['=', 'manager_id', $reportManagerId])
            ->andFilterWhere(['=', 'status', FwUserManager::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'reporting_model', $reportingModel])
            ->distinct();
        $userManageQuerySql = $userManageQuery->createCommand()->rawSql;
        //获取用户信息
        $userList = FwUser::find(false);
        $userList->joinWith('fwUserPositions.fwPosition', false)
            ->andWhere(FwUser::tableName() . '.kid in (' . $userManageQuerySql . ')');//根据领导获取下属信息

        if ($keyword) {
            $userList->andFilterWhere(['like', 'real_name', $keyword]);
        }
        $userList->select(FwUser::tableName() . '.kid,user_name,real_name,email,position_name');
        $users = $userList->orderBy('user_name')
            ->asArray()
            ->all();

        Yii::$app->cache->add($cacheKey, $users, 60 * 10);

        return $users;
    }
}