<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/7/24
 * Time: 14:28
 */

namespace common\services\message;


use Yii;
use common\models\framework\FwUser;
use common\models\learning\LnCourse;
use common\models\learning\LnExamination;
use common\models\learning\LnInvestigation;
use common\models\learning\LnResourceDomain;
use common\models\message\MsSubscribeSetting;
use common\models\message\MsSubscribeType;
use common\models\message\MsTimeline;
use common\models\social\SoQuestion;
use common\models\social\SoRecord;
use common\services\framework\UserDomainService;
use common\base\BaseActiveRecord;
use common\helpers\TStringHelper;
use yii\helpers\ArrayHelper;

class TimelineService extends MsTimeline
{
    const TITLE_TEMPLATE_SHARE_QUESTION = '{p1}分享了问答 [{p2}]';
    const TITLE_TEMPLATE_SHARE_COURSE = '{p1}分享了课程 [{p2}]';
    const TITLE_TEMPLATE_SHARE_RECORD = '{p1}分享了{p2}“{p3}”';

    /**
     * 记录分享推送动态
     * @param $user_id 分享者id
     * @param $share_users 被分享对象
     * @param SoRecord $record 记录对象
     */
    public function PushTimelineByShare($user_id, $share_users, SoRecord $record)
    {
        $user = FwUser::findOne($user_id);

        $obj_type = '';
        $title = '';
        if ($record->record_type === SoRecord::RECORD_TYPE_WEB) {
            $obj_type = MsTimeline::OBJECT_TYPE_RECORD_WEB;
            $title = str_replace(['{p1}', '{p2}', '{p3}'], [$user->real_name, '网页', $record->title], self::TITLE_TEMPLATE_SHARE_RECORD);
        } elseif ($record->record_type === SoRecord::RECORD_TYPE_EVENT) {
            $obj_type = MsTimeline::OBJECT_TYPE_RECORD_EVENT;
            $title = str_replace(['{p1}', '{p2}', '{p3}'], [$user->real_name, '事件', $record->title], self::TITLE_TEMPLATE_SHARE_RECORD);
        } elseif ($record->record_type === SoRecord::RECORD_TYPE_BOOK) {
            $obj_type = MsTimeline::OBJECT_TYPE_RECORD_BOOK;
            $title = str_replace(['{p1}', '{p2}', '{p3}'], [$user->real_name, '书籍', $record->title], self::TITLE_TEMPLATE_SHARE_RECORD);
        } elseif ($record->record_type === SoRecord::RECORD_TYPE_EXP) {
            $obj_type = MsTimeline::OBJECT_TYPE_RECORD_EXP;
            $title = str_replace(['{p1}', '{p2}', '{p3}'], [$user->real_name, '经验', $record->title], self::TITLE_TEMPLATE_SHARE_RECORD);
        }

        foreach ($share_users as $user) {
            $model = new MsTimeline();
            $model->owner_id = $user['user_id'];
            $model->sender_id = $user_id;
            $model->object_id = $record->kid;
            $model->object_type = $obj_type;
            $model->title = $title;
            $model->content = $record->content;
            $model->start_at = $record->start_at ? strtotime($record->start_at) : time();
            $model->end_at = null;
            $model->complete_status = MsTimeline::COMPLETE_STATUS_UNDONE;
            $model->from_type = MsTimeline::FROM_TYPE_PUSH;
            $model->timeline_type = MsTimeline::TIMELINE_TYPE_SOCIAL;
            $model->type_code = MsTimeline::TYPE_ATTENTION_USER;
            $model->url = $record->url;
            $model->duration = $record->duration;
            if ($record->attach_original_filename && $record->attach_url) {
                $model->attach_original_filename = $record->attach_original_filename;
                $model->attach_url = $record->attach_url;
            }
            $model->button_type = MsTimeline::BUTTON_TYPE_LOOK;

            $model->save();
        }
    }

    /**
     * 取得用户待完成时间树数据
     * @param $id 用户id
     * @param null $time 排序规则
     * @param $limit 返回记录条数
     * @param $offset 分页偏移量
     * @param $current_time 当前时间
     * @return null
     */
    public function getTodoByUid($id, $time = null, $limit, $offset, $current_time)
    {
        $subscribeTypes = self::getUserSubscribeType($id, MsTimeline::TIMELINE_TYPE_TODO);

        if ($subscribeTypes === null || count($subscribeTypes) === 0) {
            return null;
        }

        $query = MsTimeline::find(false)
            ->select([MsTimeline::tableName() . '.*', 'c.course_type', 'course_end_at' => 'c.end_time'])
            ->leftJoin(LnCourse::tableName() . ' c', MsTimeline::tableName() . '.object_id = c.kid AND ' . MsTimeline::tableName() . ".object_type = '" . MsTimeline::OBJECT_TYPE_COURSE . "'")
//            ->leftJoin(LnExamination::tableName() . ' e', MsTimeline::tableName() . '.object_id = e.kid AND ' . MsTimeline::tableName() . '.object_type = \'' . MsTimeline::OBJECT_TYPE_EXAM . '\'')
//            ->leftJoin(LnInvestigation::tableName() . ' i', MsTimeline::tableName() . '.object_id = i.kid AND ' . MsTimeline::tableName() . '.object_type = \'' . MsTimeline::OBJECT_TYPE_SURVEY . '\'')
            ->andFilterWhere(['=', 'owner_id', $id])
            ->andFilterWhere(['in', 'type_code', $subscribeTypes])
            ->andFilterWhere(['=', 'complete_status', MsTimeline::COMPLETE_STATUS_UNDONE]);

        if ($time == null) {
            $query = $query->orderBy('is_stick desc,' . MsTimeline::tableName() . '.created_at desc');
        } else if ($time == 1) {
            $query = $query->orderBy('is_stick desc,ISNULL(' . MsTimeline::tableName() . '.end_at),' . MsTimeline::tableName() . '.end_at, ' . MsTimeline::tableName() . '.created_at desc');
        } else if ($time == 2) {
            $query = $query->orderBy('is_stick desc,' . MsTimeline::tableName() . '.created_at desc');
        }

        // 防止有新数据时，重复读取数据
        $query->andFilterWhere(['<', MsTimeline::tableName() . '.created_at', $current_time]);

        $result['data'] = $query
            ->limit($limit)
            ->offset($offset)
            ->all();

        return $result;
    }

    /**
     * 取得用户待完成时间树新数据
     * @param $id 用户id
     * @param null $time 排序规则
     * @param $start_time 开始时间
     * @param $end_time 结束时间
     * @return null
     */
    public function getTodoNewByUid($id, $time = null, $start_time, $end_time)
    {
        $subscribeTypes = self::getUserSubscribeType($id, MsTimeline::TIMELINE_TYPE_TODO);

        if ($subscribeTypes == null && count($subscribeTypes) == 0) {
            return null;
        }

        $query = MsTimeline::find(false)
            ->select([MsTimeline::tableName() . '.*', 'c.course_type', 'course_end_at' => 'c.end_time'])
            ->leftJoin(LnCourse::tableName() . ' c', MsTimeline::tableName() . '.object_id = c.kid AND ' . MsTimeline::tableName() . '.object_type = \'' . MsTimeline::OBJECT_TYPE_COURSE . '\'')
            ->andFilterWhere(['=', 'owner_id', $id])
            ->andFilterWhere(['in', 'type_code', $subscribeTypes])
            ->andFilterWhere(['=', 'complete_status', MsTimeline::COMPLETE_STATUS_UNDONE]);

        if ($time == null) {
            $query = $query->orderBy('is_stick desc,' . MsTimeline::tableName() . '.created_at desc');
        } else if ($time == 1) {
            $query = $query->orderBy('is_stick desc,ISNULL(' . MsTimeline::tableName() . '.end_at),' . MsTimeline::tableName() . '.end_at, ' . MsTimeline::tableName() . '.created_at desc');
        } else if ($time == 2) {
            $query = $query->orderBy('is_stick desc,' . MsTimeline::tableName() . '.created_at desc');
        }

        // 防止有新数据时，重复读取数据
        $query->andFilterWhere(['>=', MsTimeline::tableName() . '.created_at', $start_time]);
        $query->andFilterWhere(['<', MsTimeline::tableName() . '.created_at', $end_time]);

        $result['data'] = $query
            ->all();

        return $result;
    }

    /**
     * 取得用户社交圈时间树数据
     * @param $id 用户id
     * @param $limit    返回记录条数
     * @param $offset   分页偏移量
     * @param $current_time   当前时间
     * @return mixed
     */
    public function getSocialByUid($id, $limit, $offset, $current_time)
    {
        $subscribeTypes = self::getUserSubscribeType($id, MsTimeline::TIMELINE_TYPE_SOCIAL);

        if ($subscribeTypes == null && count($subscribeTypes) == 0) {
            return null;
        }

        $query = MsTimeline::find(false)
            ->select(['sender' => 'u.real_name', MsTimeline::tableName() . '.*'])
            ->innerJoin(FwUser::tableName() . ' u', 'sender_id = u.kid  AND u.is_deleted = \'0\'')
            ->andFilterWhere(['=', 'owner_id', $id])
            ->andFilterWhere(['in', 'type_code', $subscribeTypes])
            ->orderBy('created_at desc');

        // 防止有新数据时，重复读取数据
        $query->andFilterWhere(['<', MsTimeline::tableName() . '.created_at', $current_time]);

        $result['data'] = $query
            ->limit($limit)
            ->offset($offset)
            ->all();

        return $result;
    }

    /**
     * 取得用户社交圈时间树数据
     * @param $id 用户id
     * @param $start_time    开始时间
     * @param $end_time   结束时间
     * @return mixed
     */
    public function getSocialNewByUid($id, $start_time, $end_time)
    {
        $subscribeTypes = self::getUserSubscribeType($id, MsTimeline::TIMELINE_TYPE_SOCIAL);

        if ($subscribeTypes == null && count($subscribeTypes) == 0) {
            return null;
        }

        $query = MsTimeline::find(false)
            ->select(['sender' => 'u.real_name', MsTimeline::tableName() . '.*'])
            ->innerJoin(FwUser::tableName() . ' u', 'sender_id = u.kid  AND u.is_deleted = \'0\'')
            ->andFilterWhere(['=', 'owner_id', $id])
            ->andFilterWhere(['in', 'type_code', $subscribeTypes])
            ->orderBy('created_at desc');

        // 防止有新数据时，重复读取数据
        $query->andFilterWhere(['>=', MsTimeline::tableName() . '.created_at', $start_time]);
        $query->andFilterWhere(['<', MsTimeline::tableName() . '.created_at', $end_time]);

        $result['data'] = $query
            ->all();

        return $result;
    }

    /**
     * 用户自主注册课程时间树
     * @param $user_id 用户id
     * @param $course_id 课程id
     */
    public function regCourseTimeline($user_id, $course_id)
    {
        $model = self::hasUndone($user_id, $course_id, MsTimeline::OBJECT_TYPE_COURSE);

        if ($model === null) {
            $courseModel = LnCourse::findOne($course_id);
            $model = new MsTimeline();
            $model->owner_id = $user_id;
            $model->sender_id = $user_id;
            $model->object_id = $course_id;
            $model->object_type = MsTimeline::OBJECT_TYPE_COURSE;
            $model->title = $courseModel->course_name;
            $model->content = $courseModel->course_desc_nohtml ? $courseModel->course_desc_nohtml : '暂时没有课程介绍';
            if ($courseModel->theme_url) {
                $model->image_url = $courseModel->theme_url;
            }
            $model->start_at = time();
            $model->end_at = null;//自主注册课程永久有效
            $model->complete_status = MsTimeline::COMPLETE_STATUS_UNDONE;
            $model->from_type = MsTimeline::FROM_TYPE_SELF;
            $model->timeline_type = MsTimeline::TIMELINE_TYPE_TODO;
            $model->type_code = MsTimeline::TYPE_COURSE;
            $model->button_type = MsTimeline::BUTTON_TYPE_GO;

            $model->save();
        }
    }

    /**
     * 课程报名成功时间树
     * @param $user_id 用户id
     * @param $course_id 课程id
     */
    public function enrollCourseTimeline($owner_id, $user_id, $course_id)
    {
        $model = self::hasUndone($owner_id, $course_id, MsTimeline::OBJECT_TYPE_COURSE);

        if ($model === null) {
            $courseModel = LnCourse::findOne($course_id);
            $model = new MsTimeline();
            $model->owner_id = $owner_id;
            $model->sender_id = $user_id;
            $model->object_id = $course_id;
            $model->object_type = MsTimeline::OBJECT_TYPE_COURSE;
            $model->title = '【报名成功】' . $courseModel->course_name;
            $model->content = $courseModel->course_desc_nohtml ? $courseModel->course_desc_nohtml : '暂时没有课程介绍';
            if ($courseModel->theme_url) {
                $model->image_url = $courseModel->theme_url;
            }
            $model->start_at = $courseModel->open_start_time;/*课程开课时间*/
            $model->end_at = $courseModel->open_end_time;/*课程结束时间*/
            $model->complete_status = MsTimeline::COMPLETE_STATUS_UNDONE;
            $model->from_type = MsTimeline::FROM_TYPE_SELF;
            $model->timeline_type = MsTimeline::TIMELINE_TYPE_TODO;
            $model->type_code = MsTimeline::TYPE_COURSE;/*此项需要刘程确认*/
            $model->button_type = MsTimeline::BUTTON_TYPE_NO_START;

            $model->save();
        }
    }

    /**
     * 查找未完成记录
     * @param $user_id 用户id
     * @param $object_id 对象id
     * @param $object_type 对象类型
     * @return static
     */
    public function hasUndone($user_id, $object_id, $object_type, $timeline_type = MsTimeline::TIMELINE_TYPE_TODO)
    {
        $condition = [
            'owner_id' => $user_id,
            'object_id' => $object_id,
            'object_type' => $object_type,
            'timeline_type' => $timeline_type,
            'complete_status' => MsTimeline::COMPLETE_STATUS_UNDONE,
        ];
        return MsTimeline::findOne($condition);
    }

    /**
     * 查找时间轴记录
     * @param string $user_id 用户id
     * @param string $object_id 对象id
     * @param string $object_type 对象类型
     * @param string $timeline_type 时间轴类型
     * @return static
     */
    public function findTimeline($user_id, $object_id, $object_type, $timeline_type = MsTimeline::TIMELINE_TYPE_TODO)
    {
        $condition = [
            'owner_id' => $user_id,
            'object_id' => $object_id,
            'object_type' => $object_type,
            'timeline_type' => $timeline_type,
        ];
        return MsTimeline::findOne($condition);
    }

    /**
     * 取得问答录数据
     * @param $id    用户id
     * @param $limit    返回记录条数
     * @param $offset   分页偏移量
     * @param $current_time   当前时间
     * @return null
     */
    public function getQaByUid($id, $limit, $offset, $current_time)
    {
        $subscribeTypes = self::getUserSubscribeType($id, MsTimeline::TIMELINE_TYPE_QA);

        if ($subscribeTypes == null && count($subscribeTypes) == 0) {
            return null;
        }

        $query = MsTimeline::find(false)
            ->select(['sender' => 'u.real_name', MsTimeline::tableName() . '.*'])
            ->innerJoin(SoQuestion::tableName() . ' q', MsTimeline::tableName() . '.object_id = q.kid AND q.is_deleted = \'0\'')
            ->innerJoin(FwUser::tableName() . ' u', 'q.created_by = u.kid  AND u.is_deleted = \'0\'')
            ->andFilterWhere(['=', 'owner_id', $id])
            ->andFilterWhere(['in', 'type_code', $subscribeTypes])
            ->orderBy('is_stick desc,q.updated_at desc');

        // 防止有新数据时，重复读取数据
        $query->andFilterWhere(['<', MsTimeline::tableName() . '.created_at', $current_time]);

        $result['data'] = $query
            ->limit($limit)
            ->offset($offset)
            ->all();

        return $result;
    }

    /**
     * 取得问答录数据
     * @param $id    用户id
     * @param $current_time   当前时间
     * @return null
     */
    public function getQaNewByUid($id, $start_time, $end_time)
    {
        $subscribeTypes = self::getUserSubscribeType($id, MsTimeline::TIMELINE_TYPE_QA);

        if ($subscribeTypes == null && count($subscribeTypes) == 0) {
            return null;
        }

        $query = MsTimeline::find(false)
            ->select(['sender' => 'u.real_name', MsTimeline::tableName() . '.*'])
            ->innerJoin(SoQuestion::tableName() . ' q', MsTimeline::tableName() . '.object_id = q.kid AND q.is_deleted = \'0\'')
            ->innerJoin(FwUser::tableName() . ' u', 'q.created_by = u.kid  AND u.is_deleted = \'0\'')
            ->andFilterWhere(['=', 'owner_id', $id])
            ->andFilterWhere(['in', 'type_code', $subscribeTypes])
            ->orderBy('is_stick desc,q.updated_at desc');

        $query->andFilterWhere(['>', MsTimeline::tableName() . '.created_at', $start_time]);
        $query->andFilterWhere(['<', MsTimeline::tableName() . '.created_at', $end_time]);

        $result['data'] = $query
            ->all();

        return $result;
    }

    /**
     * 取得用户订阅的消息类型
     * @param string $uid 用户id
     * @param string $type 分类：1：待完成 2：问答录 3：新鲜事 4：社交圈
     * @return array
     */
    public function getUserSubscribeType($uid, $type)
    {
        $cacheKey = 'UserSubscribeType_' . $uid . '_' . $type;

        if (Yii::$app->cache->exists($cacheKey)) {
            return Yii::$app->cache->get($cacheKey);
        }

        $tableName = MsSubscribeType::tableName();
        $query = MsSubscribeType::find(false);
        $query->leftJoin(MsSubscribeSetting::tableName() . ' s', "$tableName.kid = s.type_id" .
            " and s.user_id = '$uid' and s.is_deleted = '" . BaseActiveRecord::DELETE_FLAG_NO . "'")
            ->andFilterWhere(['or', ['=', 's.status', MsSubscribeSetting::STATUS_ON],
                ['and', 's.status is null', ['=', 'default_status', MsSubscribeSetting::STATUS_ON]]])
            ->andFilterWhere(['=', 'type', $type])
            ->select('type_code');

        $data = $query->all();

        if ($data == null || count($data) == 0) {
            $result = null;
        } else {
            $result = ArrayHelper::map($data, 'type_code', 'type_code');
        }

        Yii::$app->cache->set($cacheKey, $result);

        return $result;
    }

    /**
     * 取得订阅的消息类型
     * @param $type 分类：1：待完成 2：问答录 3：新鲜事 4：社交圈
     * @return array
     */
    public function getSubscribeType($type)
    {
        $data = MsSubscribeType::GetSubscribeType($type);

        $result = [];
        foreach ($data as $d) {
            $result[] = $d->type_code;
        }

        return $result;
    }


    /**
     * 修改时间树状态 => 完成
     * @param string $object_id 对象id
     * @param string $object_type 对象类型
     * @param string $timeline_type 时间树类型
     * @param string $user_id 用户id
     * @throws \Exception
     */
    public function setComplete($object_id, $object_type, $timeline_type = MsTimeline::TIMELINE_TYPE_TODO, $user_id = null)
    {
        if ($user_id === null) {
            $model = new MsTimeline();

            $params = [
                ':object_id' => $object_id,
                ':object_type' => $object_type,
                ':timeline_type' => $timeline_type,
                ':complete_status' => MsTimeline::COMPLETE_STATUS_UNDONE,
            ];

            $condition = 'object_id = :object_id and object_type = :object_type and timeline_type = :timeline_type and complete_status = :complete_status ';

            $attributes = [
                'complete_status' => MsTimeline::COMPLETE_STATUS_DONE,
                'end_at' => time(),
            ];

            $model->updateAll($attributes, $condition, $params);
        } else {
            $model = new MsTimeline();

            $params = [
                ':owner_id' => $user_id,
                ':object_id' => $object_id,
                ':object_type' => $object_type,
                ':timeline_type' => $timeline_type,
                ':complete_status' => MsTimeline::COMPLETE_STATUS_UNDONE,
            ];

            $condition = 'owner_id = :owner_id and object_id = :object_id and object_type = :object_type and timeline_type = :timeline_type and complete_status = :complete_status ';

            $attributes = [
                'complete_status' => MsTimeline::COMPLETE_STATUS_DONE,
                'end_at' => time(),
            ];

            $model->updateAll($attributes, $condition, $params);
        }
    }

    /**
     * 动态分享推送动态
     * @param $user_id 分享者id
     * @param $share_users 被分享对象
     * @param $timeline_id 时间树id
     */
    public function PushTimelineByID($user_id, $share_users, $timeline_id)
    {
        $user = FwUser::findOne($user_id);

        $timeline = MsTimeline::findOne($timeline_id);

        // 替换标题中的分享者姓名
        $index = mb_strpos($timeline->title, '分享了');
        $title = $user->real_name . mb_substr($timeline->title, $index);

        foreach ($share_users as $user) {
            $model = new MsTimeline();
            $model->owner_id = $user['user_id'];
            $model->sender_id = $user_id;
            $model->object_id = $timeline->object_id;
            $model->object_type = $timeline->object_type;
            $model->title = $title;
            $model->content = $timeline->content;
            $model->start_at = time();
            $model->end_at = null;
            $model->complete_status = MsTimeline::COMPLETE_STATUS_UNDONE;
            $model->from_type = MsTimeline::FROM_TYPE_PUSH;
            $model->timeline_type = MsTimeline::TIMELINE_TYPE_SOCIAL;
            $model->type_code = MsTimeline::TYPE_ATTENTION_USER;
            $model->url = $timeline->url;
            $model->attach_original_filename = $timeline->attach_original_filename;
            $model->attach_url = $timeline->attach_url;
            $model->button_type = MsTimeline::BUTTON_TYPE_LOOK;

            $model->save();
        }
    }

    /**
     * 取得新鲜事数据
     * @param string $userId 用户id
     * @param string $companyId 企业id
     * @param int $regTime 注册时间
     * @param int $limit 记录条数
     * @param int $offset 偏移量
     * @param int $current_time 当前时间
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getNewsByUid($userId, $companyId, $regTime, $limit, $offset, $current_time)
    {
        $subscribeTypes = self::getUserSubscribeType($userId, MsTimeline::TIMELINE_TYPE_NEWS);

        if ($subscribeTypes == null && count($subscribeTypes) == 0) {
            return null;
        }

        $courses_type = [];
        if (in_array(MsTimeline::TYPE_NEW_ONLINE_COURSE, $subscribeTypes)) {
            $courses_type[] = LnCourse::COURSE_TYPE_ONLINE;
        }
        if (in_array(MsTimeline::TYPE_NEW_FACETOFACE_COURSE, $subscribeTypes)) {
            $courses_type[] = LnCourse::COURSE_TYPE_FACETOFACE;
        }

        if ($courses_type == null && count($courses_type) == 0) {
            return null;
        }

        $currentTime = time();

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
            ->andWhere('kid in (' . $domainQuerySql . ')')
            ->andFilterWhere(['=', 'status', LnCourse::STATUS_FLAG_NORMAL])
//            ->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES])
            ->andFilterWhere(['or', ['>=', 'end_time', $currentTime], 'end_time is null'])
            ->andFilterWhere(['or', ['<=', 'start_time', $currentTime], 'start_time is null'])
            ->andFilterWhere(['in', 'course_type', $courses_type])
            ->andFilterWhere(['>=', 'created_at', $regTime]);


        $courseQuery->orderBy('release_at desc');

        // 防止有新数据时，重复读取数据
        $courseQuery->andFilterWhere(['<', LnCourse::tableName() . '.created_at', $current_time]);

        $result = $courseQuery
            ->limit($limit)
            ->offset($offset)
            ->all();

        return $result;
    }

    /**
     * 取得新鲜事最新数据
     * @param string $userId 用户id
     * @param string $companyId 企业id
     * @param int $start_time 开始时间
     * @param int $end_time 结束时间
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getNewsNewByUid($userId, $companyId, $start_time, $end_time)
    {
        $subscribeTypes = self::getUserSubscribeType($userId, MsTimeline::TIMELINE_TYPE_NEWS);

        if ($subscribeTypes == null && count($subscribeTypes) == 0) {
            return null;
        }

        $courses_type = [];
        if (in_array(MsTimeline::TYPE_NEW_ONLINE_COURSE, $subscribeTypes)) {
            $courses_type[] = LnCourse::COURSE_TYPE_ONLINE;
        }
        if (in_array(MsTimeline::TYPE_NEW_FACETOFACE_COURSE, $subscribeTypes)) {
            $courses_type[] = LnCourse::COURSE_TYPE_FACETOFACE;
        }

        if ($courses_type == null && count($courses_type) == 0) {
            return null;
        }

        $currentTime = time();

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
            ->andWhere('kid in (' . $domainQuerySql . ')')
            ->andFilterWhere(['=', 'status', LnCourse::STATUS_FLAG_NORMAL])
//            ->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES])
            ->andFilterWhere(['or', ['>=', 'end_time', $currentTime], 'end_time is null'])
            ->andFilterWhere(['or', ['<=', 'start_time', $currentTime], 'start_time is null'])
            ->andFilterWhere(['in', 'course_type', $courses_type]);


        $courseQuery->orderBy('release_at desc');

        // 防止有新数据时，重复读取数据
        $courseQuery->andFilterWhere(['>=', LnCourse::tableName() . '.created_at', $start_time]);
        $courseQuery->andFilterWhere(['<', LnCourse::tableName() . '.created_at', $end_time]);

        $result = $courseQuery
            ->all();

        return $result;
    }

    /**
     * 指派课程 时间树添加
     * @param $sponsor_id 指派人id
     * @param $course_id 课程id
     * @param $uid_list 推送对象id列表
     * @param $end_at 结束时间
     * @return bool
     * @version v4
     */
    public function pushByPushCourse($sponsor_id, $course_id, $uid_list, $end_at = null)
    {
        //指派人id
        $user = FwUser::findOne($sponsor_id);
        $course = LnCourse::findOne($course_id);
        /*增加面授在线判断课程状态*/
        if ($course->course_type == LnCourse::COURSE_TYPE_FACETOFACE) {
            if ($course->open_status == LnCourse::COURSE_NOT_START) {
                $button_type = MsTimeline::BUTTON_TYPE_NO_START;
            } else if ($course->open_status == LnCourse::COURSE_START) {
                $button_type = MsTimeline::BUTTON_TYPE_PROCESS;
            }
        } else {
            $button_type = MsTimeline::BUTTON_TYPE_GO;
        }
        if ($course == null) {
            return false;
        }

        if ($uid_list == null || count($uid_list) == 0) {
            return false;
        }

        $saveList = array();

        foreach ($uid_list as $uid) {
            $model = self::hasUndone($uid, $course_id, MsTimeline::OBJECT_TYPE_COURSE);
            if ($model !== null) {
                self::updateTimelineEndtime($model, $end_at);
                continue;
            }

            // 添加时间轴记录
            $model = new MsTimeline();
            $model->owner_id = $uid;
            $model->sender_id = $sponsor_id;
            $model->object_id = $course->kid;
            $model->object_type = MsTimeline::OBJECT_TYPE_COURSE;
            $model->title = $course->course_name;
            $model->content = $course->course_desc_nohtml ? $course->course_desc_nohtml : '暂时没有课程介绍';
            if ($course->theme_url) {
                $model->image_url = $course->theme_url;
            }
            $model->complete_status = MsTimeline::COMPLETE_STATUS_UNDONE;
            $model->start_at = time();// 目前推送任务开始时间未设置，暂时设为当前时间
            $model->end_at = $end_at;
            $model->from_type = MsTimeline::FROM_TYPE_PUSH;
            $model->timeline_type = MsTimeline::TIMELINE_TYPE_TODO;
            $model->type_code = MsTimeline::TYPE_COURSE;
            $model->button_type = $button_type;

            $saveList[] = $model;
//            $model->save();
        }

        BaseActiveRecord::batchInsertSqlArray($saveList);
        return true;
    }

    /**
     * 提问@时 时间树添加
     * @param $publisher_id 提问人id
     * @param SoQuestion $question 问题
     * @param $uid_list @用户id列表
     * @return bool
     * @version v4
     */
    public function pushByQuestionAt($publisher_id, SoQuestion $question, $uid_list)
    {
//        $user = FwUser::findOne($publisher_id);

        if ($question == null) {
            return false;
        }

        if ($uid_list == null || count($uid_list) == 0) {
            return false;
        }

        foreach ($uid_list as $uid) {
            // 添加时间轴记录
            $model = new MsTimeline();
            $model->owner_id = $uid;
            $model->sender_id = $publisher_id;
            $model->object_id = $question->kid;
            $model->object_type = MsTimeline::OBJECT_TYPE_QUESTION;
            $model->title = $question->title;
            $model->content = $question->question_content;
            $model->complete_status = MsTimeline::COMPLETE_STATUS_UNDONE;
            $model->start_at = time();// 目前推送任务开始时间未设置，暂时设为当前时间
            $model->end_at = null;
            $model->from_type = MsTimeline::FROM_TYPE_PUSH;
            $model->timeline_type = MsTimeline::TIMELINE_TYPE_QA;
            $model->type_code = MsTimeline::TYPE_AT_QUESTION;
            $model->button_type = MsTimeline::BUTTON_TYPE_LOOK;

            $model->save();
        }
        return true;
    }

    /**
     * 面授课程报名成功 时间树添加
     * @param $approval_id 审批人id
     * @param $course_id 课程id
     * @param $reg_user_id 学员id
     * @return bool
     * @version v4
     */
    public function pushByCourseRegApproval($approval_id, $course_id, $reg_user_id)
    {
        $course = LnCourse::findOne($course_id);

        if ($course == null) {
            return false;
        }

        if ($approval_id == null || $approval_id == '') {
            return false;
        }

        if ($reg_user_id == null || $reg_user_id == '') {
            return false;
        }

        // 添加时间轴记录
        $model = new MsTimeline();
        $model->owner_id = $reg_user_id;
        $model->sender_id = $approval_id;
        $model->object_id = $course->kid;
        $model->object_type = MsTimeline::OBJECT_TYPE_COURSE;
        $model->title = $course->course_name;
        $model->content = $course->course_desc_nohtml ? $course->course_desc_nohtml : '暂时没有课程介绍';
        if ($course->theme_url) {
            $model->image_url = $course->theme_url;
        }
        $model->complete_status = MsTimeline::COMPLETE_STATUS_UNDONE;
        $model->start_at = time();// 目前推送任务开始时间未设置，暂时设为当前时间
        $model->end_at = null;
        $model->from_type = MsTimeline::FROM_TYPE_PUSH;
        $model->timeline_type = MsTimeline::TIMELINE_TYPE_TODO;
        $model->type_code = MsTimeline::TYPE_COURSE;
        $model->button_type = MsTimeline::BUTTON_TYPE_NO_START;

        $model->save();

        return true;
    }

    /**
     * 提出问题 时间树添加
     * @param $user_id 提问人
     * @param SoQuestion $question 问题
     * @return bool
     * @version v4
     */
    public function pushBySubQuestion($user_id, SoQuestion $question)
    {
        if ($question == null) {
            return false;
        }

        if ($user_id == null || $user_id == '') {
            return false;
        }

        // 添加时间轴记录
        $model = new MsTimeline();
        $model->owner_id = $user_id;
        $model->sender_id = $user_id;
        $model->object_id = $question->kid;
        $model->object_type = MsTimeline::OBJECT_TYPE_QUESTION;
        $model->start_at = time();
        $model->end_at = null;
        $model->title = $question->title;
        $model->content = $question->question_content;
        $model->complete_status = MsTimeline::COMPLETE_STATUS_UNDONE;
        $model->from_type = MsTimeline::FROM_TYPE_SELF;
        $model->timeline_type = MsTimeline::TIMELINE_TYPE_QA;
        $model->type_code = MsTimeline::TYPE_SUB_QUESTION;
        $model->button_type = MsTimeline::BUTTON_TYPE_LOOK;

        $model->save();
    }

    /**
     * 关注问题 时间树添加
     * @param $user_id 关注人id
     * @param $question_id 问题id
     */
    public function pushByCareQuestion($user_id, $question_id)
    {
        $timeline = self::hasUndone($user_id, $question_id, MsTimeline::OBJECT_TYPE_QUESTION, MsTimeline::TIMELINE_TYPE_QA);

        if ($timeline == null) {
            $question = SoQuestion::findOne($question_id);

            // 添加时间轴记录
            $model = new MsTimeline();
            $model->owner_id = $user_id;
            $model->sender_id = $user_id;
            $model->object_id = $question_id;
            $model->object_type = MsTimeline::OBJECT_TYPE_QUESTION;
            $model->start_at = time();
            $model->end_at = null;
            $model->title = $question->title;
            $model->content = $question->question_content;
            $model->complete_status = MsTimeline::COMPLETE_STATUS_UNDONE;
            $model->from_type = MsTimeline::FROM_TYPE_SELF;
            $model->timeline_type = MsTimeline::TIMELINE_TYPE_QA;
            $model->type_code = MsTimeline::TYPE_ATTENTION_QUESTION;
            $model->button_type = MsTimeline::BUTTON_TYPE_LOOK;

            $model->save();
        }
    }

    /**
     * 分享问题 时间树添加
     * @param $user_id 分享者id
     * @param $share_users 分享对象id列表
     * @param $question_id 问题id
     * @param $content 分享心得
     * @return bool
     */
    public function pushByShareQuestion($user_id, $share_users, $question_id, $content)
    {
        $user = FwUser::findOne($user_id);
        $question = SoQuestion::findOne($question_id);

        if ($user == null) {
            return false;
        }

        if ($question == null) {
            return false;
        }

        if ($share_users == null || count($share_users) == 0) {
            return false;
        }

        $title = str_replace(['{p1}', '{p2}'], [$user->real_name, $question->title], self::TITLE_TEMPLATE_SHARE_QUESTION);

        foreach ($share_users as $uid) {
            // 添加时间轴记录
            $model = new MsTimeline();
            $model->owner_id = $uid;
            $model->sender_id = $user_id;
            $model->object_id = $question->kid;
            $model->object_type = MsTimeline::OBJECT_TYPE_QUESTION;
            $model->title = $title;
            $model->content = $content;
            $model->complete_status = MsTimeline::COMPLETE_STATUS_UNDONE;
            $model->start_at = time();
            $model->end_at = null;
            $model->from_type = MsTimeline::FROM_TYPE_PUSH;
            $model->timeline_type = MsTimeline::TIMELINE_TYPE_SOCIAL;
            $model->type_code = MsTimeline::TYPE_ATTENTION_USER;
            $model->button_type = MsTimeline::BUTTON_TYPE_LOOK;

            $model->save();
        }
        return true;
    }

    /**
     * 分享课程 时间树添加
     * @param $user_id 分享者id
     * @param $share_users 分享对象id列表
     * @param $course_id 课程id
     * @param $content 分享心得
     * @param $at_users @用户列表
     * @return bool
     */
    public function pushByShareCourse($user_id, $share_users, $course_id, $content, $at_users = null)
    {
        $user = FwUser::findOne($user_id);
        $course = LnCourse::findOne($course_id);

        if ($user == null) {
            return false;
        }

        if ($course == null) {
            return false;
        }

        if (($share_users == null || count($share_users) == 0) && ($at_users == null || count($at_users) == 0)) {
            return false;
        }

        $title = str_replace(['{p1}', '{p2}'], [$user->real_name, $course->course_name], self::TITLE_TEMPLATE_SHARE_COURSE);

        $tempUsers = [];
        $saveModels = [];

        if ($at_users !== null) {
            foreach ($at_users as $v) {
                $tempUsers[] = $v['kid'];
                // 添加时间轴记录
                $model = new MsTimeline();
                $model->owner_id = $v['kid'];
                $model->sender_id = $user_id;
                $model->object_id = $course->kid;
                $model->object_type = MsTimeline::OBJECT_TYPE_COURSE;
                $model->title = $title;
                $model->content = $content;
                if ($course->theme_url) {
                    $model->image_url = $course->theme_url;
                }
                $model->complete_status = MsTimeline::COMPLETE_STATUS_UNDONE;
                $model->start_at = time();
                $model->end_at = null;
                $model->from_type = MsTimeline::FROM_TYPE_PUSH;
                $model->timeline_type = MsTimeline::TIMELINE_TYPE_SOCIAL;
                $model->type_code = MsTimeline::TYPE_AT_SHARE;
                $model->button_type = MsTimeline::BUTTON_TYPE_LOOK;

                $saveModels[] = $model;
            }
        }

        foreach ($share_users as $uid) {
            if (in_array($uid, $tempUsers)) {
                continue;
            }

            // 添加时间轴记录
            $model = new MsTimeline();
            $model->owner_id = $uid;
            $model->sender_id = $user_id;
            $model->object_id = $course->kid;
            $model->object_type = MsTimeline::OBJECT_TYPE_COURSE;
            $model->title = $title;
            $model->content = $content;
            if ($course->theme_url) {
                $model->image_url = $course->theme_url;
            }
            $model->complete_status = MsTimeline::COMPLETE_STATUS_UNDONE;
            $model->start_at = time();
            $model->end_at = null;
            $model->from_type = MsTimeline::FROM_TYPE_PUSH;
            $model->timeline_type = MsTimeline::TIMELINE_TYPE_SOCIAL;
            $model->type_code = MsTimeline::TYPE_ATTENTION_USER;
            $model->button_type = MsTimeline::BUTTON_TYPE_LOOK;

            $saveModels[] = $model;
        }

        BaseActiveRecord::batchInsertSqlArray($saveModels);

        return true;
    }

    public function deleteTimeline(MsTimeline $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $params = [
                ':owner_id' => $targetModel->owner_id,
                ':object_id' => $targetModel->object_id,
                ':object_type' => $targetModel->object_type,
                ':type_code' => MsTimeline::TYPE_ATTENTION_QUESTION,
            ];

            $condition = 'owner_id = :owner_id and object_id = :object_id and object_type = :object_type and type_code = :type_code ';

            MsTimeline::deleteAll($condition, $params);
        }
    }

    /**
     * 更新时间轴结束时间
     * @param MsTimeline $model 时间轴对象
     * @param $end_at 结束时间
     */
    private function updateTimelineEndtime(MsTimeline $model, $end_at)
    {
        $model->end_at = $end_at;
        $model->save();
    }

    /**
     * 更新时间轴结束时间
     * @param $uid_list 用户id列表
     * @param $object_id 对象id
     * @param $object_type 对象类型
     * @param $end_at 结束时间
     * @param string $timeline_type 时间轴类型
     */
    public function batchUpdateTimelineEndtime($uid_list, $object_id, $object_type, $end_at, $timeline_type = MsTimeline::TIMELINE_TYPE_TODO)
    {
        $uids = TStringHelper::ArrayToString($uid_list);

        if (!empty($uids)) {
            $model = new MsTimeline();

            $params = [
                'object_id' => $object_id,
                'object_type' => $object_type,
                'timeline_type' => $timeline_type,
                'complete_status' => MsTimeline::COMPLETE_STATUS_UNDONE,
            ];

            $condition = 'owner_id in (' . $uids . ') and object_id = :object_id and object_type = :object_type and timeline_type = :timeline_type and complete_status = :complete_status';

            $attributes = [
                'end_at' => $end_at,
            ];

            $model->updateAll($attributes, $condition, $params);
        }
    }

    /**
     * 更新时间轴 按钮文字
     * @param $user_id
     * @param $object_id
     * @param $object_type
     * @param $button_type
     */
    public function updateButtonType($user_id = null, $object_id, $object_type, $button_type)
    {
        if ($user_id != null) {
            $model = self::hasUndone($user_id, $object_id, $object_type);
            if (!empty($model)) {
                $model->button_type = $button_type;
                $model->save();
            }
        } else {
            $model = new MsTimeline();

            $params = [
                ':object_id' => $object_id,
                ':object_type' => $object_type,
                ':complete_status' => MsTimeline::COMPLETE_STATUS_UNDONE,
            ];

            $condition = 'object_id = :object_id and object_type = :object_type and complete_status = :complete_status ';

            $attributes = [
                'button_type' => $button_type,
            ];

            $model->updateAll($attributes, $condition, $params);
        }
    }

    /**
     * 取得新数据统计
     * @param $user_id 用户id
     * @param $timeline_type 时间树类型
     * @param $filter_time 过滤时间
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetNewDataCount($user_id, $timeline_type, $filter_time)
    {
        $subscribeTypes = self::getUserSubscribeType($user_id, $timeline_type);

        if ($subscribeTypes == null && count($subscribeTypes) == 0) {
            return 0;
        }

        $query = MsTimeline::find(false);

        $query->andFilterWhere(['=', 'owner_id', $user_id])
            ->andFilterWhere(['>', 'created_at', $filter_time])
            ->andFilterWhere(['in', 'type_code', $subscribeTypes])
            ->andFilterWhere(['=', 'complete_status', MsTimeline::COMPLETE_STATUS_UNDONE])
            ->andFilterWhere(['=', 'timeline_type', $timeline_type]);

        return $query->count('kid');

    }

    /**
     * 取得新鲜事新数据统计
     * @param string $userId 用户id
     * @param string $companyId 企业id
     * @param $filter_time 过滤时间
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function GetNewsNewDataCount($userId, $companyId, $filter_time)
    {
        $subscribeTypes = self::getUserSubscribeType($userId, MsTimeline::TIMELINE_TYPE_NEWS);

        if ($subscribeTypes == null && count($subscribeTypes) == 0) {
            return 0;
        }

        $courses_type = [];
        if (in_array(MsTimeline::TYPE_NEW_ONLINE_COURSE, $subscribeTypes)) {
            $courses_type[] = LnCourse::COURSE_TYPE_ONLINE;
        }
        if (in_array(MsTimeline::TYPE_NEW_FACETOFACE_COURSE, $subscribeTypes)) {
            $courses_type[] = LnCourse::COURSE_TYPE_FACETOFACE;
        }

        if ($courses_type == null && count($courses_type) == 0) {
            return 0;
        }

        $currentTime = time();

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
            ->andWhere('kid in (' . $domainQuerySql . ')')
            ->andFilterWhere(['=', 'status', LnCourse::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES])
            ->andFilterWhere(['or', ['>=', 'end_time', $currentTime], 'end_time is null'])
            ->andFilterWhere(['or', ['<=', 'start_time', $currentTime], 'start_time is null'])
            ->andFilterWhere(['in', 'course_type', $courses_type]);

        // 读取新数据
        $courseQuery->andFilterWhere(['>', LnCourse::tableName() . '.created_at', $filter_time]);

        $result = $courseQuery
            ->count('kid');

        return $result;
    }

    /**
     * 时间轴 置顶
     * @param $user_id 用户id
     * @param MsTimeline $timeline 置顶对象
     * @return bool
     */
    public function StickTimeline($user_id, MsTimeline $timeline)
    {
        $query = MsTimeline::find(false);

        $cur = $query->andFilterWhere(['=', 'owner_id', $user_id])
            ->andFilterWhere(['=', 'complete_status', MsTimeline::COMPLETE_STATUS_UNDONE])
            ->max('is_stick');

        $timeline->is_stick = $cur + 1;
        return $timeline->save();
    }

    /**
     * 推送考试 时间轴添加
     * @param $sponsor_id 指派人id
     * @param $exam_id 考试id
     * @param $uid_list 推送用户id列表
     * @param $end_at 结束时间
     * @param $out_array 需要发送消息用户列表
     * @return bool
     */
    public function addByPushExam($sponsor_id, $exam_id, $uid_list, $end_at, &$out_array)
    {
        $exam = LnExamination::findOne($exam_id);

        if (empty($exam)) {
            return false;
        }

        $saveList = array();
        $updateList = array();

        foreach ($uid_list as $uid) {
            $model = self::findTimeline($uid, $exam_id, MsTimeline::OBJECT_TYPE_EXAM);
            if ($model !== null) {
                if ($model->complete_status === MsTimeline::COMPLETE_STATUS_DONE) {
                    continue;
                } elseif ($model->end_at != $end_at) {
                    $out_array[] = $uid;
                    $updateList[] = $uid;
                    continue;
                }
            }

            // 添加时间轴记录
            $model = new MsTimeline();
            $model->owner_id = $uid;
            $model->sender_id = $sponsor_id;
            $model->object_id = $exam_id;
            $model->object_type = MsTimeline::OBJECT_TYPE_EXAM;
            $model->title = $exam->title;
            $model->content = $exam->description ? $exam->description : '暂时没有介绍';
            $model->complete_status = MsTimeline::COMPLETE_STATUS_UNDONE;
            $model->start_at = time();// 目前推送任务开始时间未设置，暂时设为当前时间
            $model->end_at = $end_at;
            $model->from_type = MsTimeline::FROM_TYPE_PUSH;
            $model->timeline_type = MsTimeline::TIMELINE_TYPE_TODO;
            $model->type_code = MsTimeline::TYPE_EXAM;
            $model->button_type = MsTimeline::BUTTON_TYPE_GO;

            $saveList[] = $model;
            $out_array[] = $uid;
        }

        $this->batchUpdateTimelineEndtime($updateList, $exam_id, MsTimeline::OBJECT_TYPE_EXAM, $end_at);
        BaseActiveRecord::batchInsertSqlArray($saveList);
        return true;
    }

    /**
     * 推送调查 时间轴添加
     * @param $sponsor_id 指派人id
     * @param $survey_id 调查id
     * @param $uid_list 推送用户id列表
     * @param $end_at 结束时间
     * @param $out_array 需要发送消息用户列表
     * @return bool
     */
    public function addByPushSurvey($sponsor_id, $survey_id, $uid_list, $end_at, &$out_array)
    {
        $investigation = LnInvestigation::findOne($survey_id);

        if (empty($investigation)) {
            return false;
        }

        $saveList = array();
        $updateList = array();

        foreach ($uid_list as $uid) {
            $model = self::findTimeline($uid, $survey_id, MsTimeline::OBJECT_TYPE_SURVEY);
            if ($model !== null) {
                if ($model->complete_status === MsTimeline::COMPLETE_STATUS_DONE) {
                    continue;
                } elseif ($model->end_at != $end_at) {
                    $out_array[] = $uid;
                    $updateList[] = $uid;
                    continue;
                }
            }

            // 添加时间轴记录
            $model = new MsTimeline();
            $model->owner_id = $uid;
            $model->sender_id = $sponsor_id;
            $model->object_id = $survey_id;
            $model->object_type = MsTimeline::OBJECT_TYPE_SURVEY;
            $model->title = $investigation->title;
            $model->content = $investigation->description ? $investigation->description : '暂时没有介绍';
            $model->complete_status = MsTimeline::COMPLETE_STATUS_UNDONE;
            $model->start_at = time();// 目前推送任务开始时间未设置，暂时设为当前时间
            $model->end_at = $end_at;
            $model->from_type = MsTimeline::FROM_TYPE_PUSH;
            $model->timeline_type = MsTimeline::TIMELINE_TYPE_TODO;
            $model->type_code = MsTimeline::TYPE_SURVEY;
            $model->button_type = MsTimeline::BUTTON_TYPE_GO;

            $saveList[] = $model;
            $out_array[] = $uid;
        }

        $this->batchUpdateTimelineEndtime($updateList, $survey_id, MsTimeline::OBJECT_TYPE_SURVEY, $end_at);
        BaseActiveRecord::batchInsertSqlArray($saveList);
        return true;
    }

    /**
     * 删除时间轴
     * @param $userId 用户id
     * @param $courseId 课程id
     */
    public function deleteTimelineByTodoCourse($userId, $courseId)
    {
        if ($userId && $courseId) {
            $params = [
                ':owner_id' => $userId,
                ':object_id' => $courseId,
                ':object_type' => MsTimeline::OBJECT_TYPE_COURSE,
                ':type_code' => MsTimeline::TYPE_COURSE,
            ];

            $condition = 'owner_id = :owner_id and object_id = :object_id and object_type = :object_type and type_code = :type_code';

            MsTimeline::deleteAll($condition, $params);
        }
    }
}