<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/5/28
 * Time: 10:20
 */

namespace common\services\social;


use common\models\framework\FwCompany;
use common\models\framework\FwUser;
use common\models\framework\FwUserManager;
use common\models\learning\LnCourse;
use common\models\message\MsTimeline;
use common\models\social\SoQuestion;
use common\models\social\SoRecord;
use common\models\social\SoShare;
use common\models\social\SoShareUser;
use common\services\message\MessageService;
use common\services\message\TimelineService;
use common\services\social\UserAttentionService;
use common\base\BaseActiveRecord;
use common\helpers\TArrayHelper;
use yii\db\Query;
use Yii;
use common\helpers\TTimeHelper;
use yii\helpers\ArrayHelper;

class ShareService extends SoShare
{

    public function getSharePageDataById($id, $time, $size, $page)
    {
        $time_condition = '';
        if ($time == 1) {
            $time_condition = 'created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 WEEK)) ';
        } else if ($time == 2) {
            $time_condition = 'created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) ';
        } else if ($time == 3) {
            $time_condition = 'created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 MONTH)) ';
        }

        $query = SoShare::find(false);

        $result = $query
            ->andWhere($time_condition)
            ->andFilterWhere(['user_id' => $id])
            ->orderBy('created_at desc')
            ->limit($size)
            ->offset($this->getOffset($page, $size))
            ->all();
        return $result;
    }

//    public function getOffset($page, $size)
//    {
//        $_page = (int)$page - 1;
//
//        return $size < 1 ? 0 : $_page * $size;
//    }

    /**
     * 取得分享总数排名前4名
     * @return array
     */
    public function getShareTop($uid)
    {
        //$org_id = Yii::$app->user->identity->orgnization_id;
        $month_begin = strtotime(TTimeHelper::getCurrentMonthFirstDay());

        $month_end = strtotime(TTimeHelper::getNextMonthFirstDay());

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
            ->from('{{%so_share}} as t1')
            ->leftJoin('{{%fw_user}} as t2', 't1.user_id = t2.kid')
            ->andWhere(['>=', 't1.created_at', $year_begin])
            ->andWhere(['<=', 't1.created_at', $year_end])
            ->andWhere('t2.kid in (' . $userManageQuerySql . ')')//根据领导获取下属信息
            ->andWhere(['t1.is_deleted' => SoShare::DELETE_FLAG_NO])
            ->andWhere(['t2.is_deleted' => FwUser::DELETE_FLAG_NO])
            ->groupBy('t1.user_id')
            ->orderBy('y_count desc')
            ->select('t1.user_id,t2.real_name,t2.thumb,t2.gender,count(t1.kid) as y_count,t2.email')
            ->limit(5)
            ->all();

        foreach ($result as &$v) {
            $uid = $v['user_id'];
            $data = new SoShare();
            $allCount = $data->find(false)
                ->andWhere(['user_id' => $uid])
                ->count();
            $v['a_count'] = $allCount;
        }

        return $result;
    }

    /**
     * 课程分享
     * @param $userId 用户id
     * @param $courseId 课程id
     * @param $courseTitle 课程标题
     * @param $content 分享内容(用户心得)
     * @param $atUserList @用户列表
     * @return bool
     */
    public function CourseShare($userId, $courseId, $courseTitle, $content, $atUserList = null)
    {
        $share = new SoShare();
        $share->user_id = $userId;
        $share->title = $courseTitle;
        $share->content = $content;
        $share->obj_id = $courseId;
        $share->type = SoShare::SHARE_TYPE_COURSE;
        $share->save();

        $attentionService = new UserAttentionService();
        //获取所有关注对象
        $attentionUserList = $attentionService->getAllUserId($userId);
        $attentionUserIdList = TArrayHelper::get_array_key($attentionUserList, 'user_id');

        $service = new MessageService();
        $service->pushMessageByCourseShare($userId, $share, $attentionUserIdList, $atUserList);

        $timelineService = new TimelineService();
        $timelineService->pushByShareCourse($userId, $attentionUserIdList, $courseId, $content, $atUserList);

        self::ShareUserSave($share, $attentionUserIdList, $atUserList);

        return true;
    }

    /**
     * 课程问题分享
     * @param $user_id 用户id
     * @param $question_id 问题id
     */
    public function CourseQuestionShare($user_id, $question_id)
    {
        $question = SoQuestion::findOne($question_id);
        $share = new SoShare();
        $share->user_id = $user_id;
        $share->title = $question->title;
        $share->content = $question->question_content;
        $share->obj_id = $question_id;
        $share->type = SoShare::SHARE_TYPE_QUESTION;
        $share->save();
        SoQuestion::addFieldNumber($question_id, "share_num");

        $attentionService = new UserAttentionService();
        //获取所有关注对象
        $user_attention = $attentionService->getAllUserId($user_id);
        if (isset($user_attention) && $user_attention != null) {
            $user_attention = ArrayHelper::map($user_attention, 'user_id', 'user_id');
            $user_attention = array_keys($user_attention);
        }
        // 时间轴添加
        $timelineService = new TimelineService();
        $timelineService->pushByShareQuestion($user_id, $user_attention, $share->obj_id, $share->content);
        $service = new MessageService();
        $service->pushMessageByQuestionShare($user_id, $share, $user_attention);

        self::ShareUserSave($share, $user_attention);

        return true;
    }

    public static function ShareUserSave(SoShare $share, $user_array, $at_users = null)
    {
        $tempUsers = [];
        $saveModels = [];

        if ($at_users !== null) {
            foreach ($at_users as $v) {
                $tempUsers[] = $v['kid'];

                $shareUser = new SoShareUser();
                $shareUser->share_id = $share->kid;
                $shareUser->user_id = $v['kid'];
                $saveModels[] = $shareUser;
            }
        }

        foreach ($user_array as $val) {
            if (in_array($val, $tempUsers)) {
                continue;
            }

            $shareUser = new SoShareUser();
            $shareUser->share_id = $share->kid;
            $shareUser->user_id = $val;
            $saveModels[] = $shareUser;
        }

        BaseActiveRecord::batchInsertSqlArray($saveModels);
    }

    public function getShareByUid($uid, $size, $page)
    {
        $query = new Query();

        $result = $query->from('eln_so_share_view')
            ->andFilterWhere(['user_id' => $uid])
            ->orderBy('created_at desc')
            ->limit($size)
            ->offset($this->getOffset($page, $size))
            ->all();
        return $result;
    }

    public function SocialShare($user_id, $share_users, $timeline_id)
    {
        $timeline = MsTimeline::findOne($timeline_id);

        $type = '';
        $title = '';
        $content = '';
        if ($timeline->object_type === MsTimeline::OBJECT_TYPE_COURSE) {
            $type = SoShare::SHARE_TYPE_COURSE;
            $course = LnCourse::findOne($timeline->object_id);
            $title = $course->course_name;
            $content = $timeline->content;
        } else if ($timeline->object_type === MsTimeline::OBJECT_TYPE_QUESTION) {
            $type = SoShare::SHARE_TYPE_QUESTION;
            $question = SoQuestion::findOne($timeline->object_id);
            $title = $question->title;
            $content = $timeline->content;
        } else if ($timeline->object_type === MsTimeline::OBJECT_TYPE_RECORD_WEB
            || $timeline->object_type === MsTimeline::OBJECT_TYPE_RECORD_EVENT
            || $timeline->object_type === MsTimeline::OBJECT_TYPE_RECORD_BOOK
            || $timeline->object_type === MsTimeline::OBJECT_TYPE_RECORD_EXP
        ) {
            $type = SoShare::SHARE_TYPE_RECORD;
            $record = SoRecord::findOne($timeline->object_id);
            $title = $record->title;
            $content = $record->content;
        }

        $share = new SoShare();
        $share->title = $title;
        $share->content = $content;
        $share->type = $type;
        $share->obj_id = $timeline->object_id;
        $share->user_id = $user_id;
        $share->save();

        if (isset($share_users) && $share_users != null) {
            $user_array = ArrayHelper::map($share_users, 'user_id', 'user_id');
            $user_array = array_keys($user_array);
        }
        self::ShareUserSave($share, $user_array);
    }
}