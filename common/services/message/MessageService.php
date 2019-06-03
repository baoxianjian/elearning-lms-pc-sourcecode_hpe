<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/5/21
 * Time: 10:56
 */

namespace common\services\message;

use common\base\BaseService;
use common\models\framework\FwUser;
use common\models\learning\LnCourse;
use common\models\learning\LnExamination;
use common\models\learning\LnInvestigation;
use common\models\learning\LnInvestigationResult;
use common\models\learning\LnResourceDomain;
use common\models\message\MsMessage;
use common\models\message\MsMessageUser;
use common\models\message\MsTaskItem;
use common\models\message\MsTimeline;
use common\models\social\SoAnswer;
use common\models\social\SoAnswerComment;
use common\models\social\SoQuestion;
use common\models\social\SoQuestionCare;
use common\models\social\SoRecord;
use common\models\social\SoShare;
use common\models\social\SoUserAttention;
use common\services\framework\UserDomainService;
use common\base\BaseActiveRecord;
use common\helpers\TArrayHelper;
use common\helpers\TStringHelper;
use components\widgets\TPagination;
use Yii;
use yii\helpers\ArrayHelper;
use yii\db\Query;
use common\models\message\MsSelectTemp;
use common\models\learning\LnExamPaperQuestTemp;


class MessageService extends BaseService
{
    const REG_TYPE_MANAGER = 'manager';
    const REG_STATE_SUCCESS = '1';

    const MESSAGE_TEMPLATE_SHARE_WEB = '{user}给你分享了一个网页，请在社交圈查看详情。';
    const MESSAGE_TEMPLATE_SHARE_EVENT = '{user}给你分享了一个事件，请在社交圈查看详情。';
    const MESSAGE_TEMPLATE_SHARE_BOOK = '{user}给你分享了一本书籍，请在社交圈查看详情。';
    const MESSAGE_TEMPLATE_SHARE_EXP = '{user}给你分享了一段经验，请在社交圈查看详情。';

    const MESSAGE_TEMPLATE_PUSH_COURSE = '{p1}给你指派了课程《{p2}》，请尽快完成。';
    const MESSAGE_TEMPLATE_PUBLISH_RESOURCE = '课程《{p1}》发布了新资源。';
    const MESSAGE_TEMPLATE_QUESTION_AT = '{p1}请你回答关于“{p2}”的问题。';
    const MESSAGE_TEMPLATE_MY_QUESTION_ANSWER = '{p1}回答了你提的关于“{p2}”的问题。';
    const MESSAGE_TEMPLATE_CARE_QUESTION_ANSWER = '{p1}回答了你关注的关于“{p2}”的问题。';
    const MESSAGE_TEMPLATE_ANSWER_COMMENT = '{p1}评论了你在问题“{p2}”中的回答。';
    const MESSAGE_TEMPLATE_SHARE_QUESTION = '{p1}分享了关于“{p2}”的问题。';
    const MESSAGE_TEMPLATE_SHARE_COURSE = '{p1}分享了课程《{p2}》。';
    const MESSAGE_TEMPLATE_REG_APPROVAL = '你报名参加课程《{p1}》审核通过。';


    /**
     * 根据用户ID返回消息条数
     * @param $id 用户id
     * @param $type 消息类型
     * @return int 消息条数
     */
    public function getMessageCountByUid($id, $type)
    {
        $messageUserModel = new MsMessageUser();
        $result = $messageUserModel->find(false)
            ->innerJoinWith("msMessage")
            ->andFilterWhere(['=', 'receive_status', MsMessageUser::STATUS_UNRECEIVE])
            ->andFilterWhere(['=', 'message_type', $type])
            ->andFilterWhere(['=', 'user_id', $id])
            ->count(MsMessageUser::tableName() . ".kid");

        return $result;
    }

    /**
     * 根据用户ID返回消息条数(新鲜事)
     * @param string $userId 用户id
     * @param string $companyId 企业id
     * @param int $regTime 注册时间
     * @return int 消息条数
     */
    public function getNewsMessageCountByUid($userId, $companyId, $regTime)
    {
        $messageUserModel = new MsMessageUser();
        $messageUserSql = $messageUserModel->find(false)
            ->andFilterWhere(['=', 'receive_status', MsMessageUser::STATUS_RECEIVE])
            ->andFilterWhere(['=', 'msg_type', MsMessageUser::TYPE_SPECIAL])
            ->andFilterWhere(['=', 'user_id', $userId])
            ->select("msg_id")
            ->distinct()
            ->createCommand()
            ->getRawSql();

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
            ->andWhere('resource_id not in (' . $messageUserSql . ')')
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
            ->andFilterWhere(['>=', 'created_at', $regTime]);

        return $courseQuery->count('kid');
    }

    /**
     * 根据用户ID返回消息(新鲜事)
     * @param string $userId 用户id
     * @param string $companyId 企业id
     * @param int $regTime 注册时间
     * @param int $size 数量
     * @param int $offset 偏移量
     * @return mixed
     */
    public function getNewsMessageByUid($userId, $companyId, $regTime, $size, $offset = -1)
    {
//        $messageUserModel = new MsMessageUser();
//        $messageUserSql = $messageUserModel->find(false)
//            ->andFilterWhere(['=', 'receive_status', MsMessageUser::STATUS_RECEIVE])
//            ->andFilterWhere(['=', 'msg_type', MsMessageUser::TYPE_SPECIAL])
//            ->andFilterWhere(['=', 'user_id', $user_id])
//            ->select("msg_id")
//            ->distinct()
//            ->createCommand()
//            ->getRawSql();

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
//            ->andWhere('resource_id not in ('.$messageUserSql.')')
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
            ->select(LnCourse::tableName() . '.*,m.receive_status')
            ->leftJoin(MsMessageUser::tableName() . ' m', 'm.msg_id=' . LnCourse::tableName() . '.kid and m.msg_type=\'' . MsMessageUser::TYPE_SPECIAL . '\' and m.is_deleted=\'' . MsMessageUser::DELETE_FLAG_NO . '\' and m.user_id=\'' . $userId . '\'')
            ->andWhere($audienceFilterSql)
            ->andWhere(LnCourse::tableName() . '.kid in (' . $domainQuerySql . ')')
            ->andFilterWhere(['=', 'status', LnCourse::STATUS_FLAG_NORMAL])
//            ->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES])
            ->andFilterWhere(['or', ['>=', 'end_time', $currentTime], 'end_time is null'])
            ->andFilterWhere(['or', ['<=', 'start_time', $currentTime], 'start_time is null'])
            ->andFilterWhere(['>=', LnCourse::tableName() . '.created_at', $regTime]);

        $count = $courseQuery
            ->count();
        $pages = new TPagination(['defaultPageSize' => $size, 'totalCount' => $count]);

        $result['data'] = $courseQuery
            ->limit($pages->limit)
            ->offset($offset < 0 ? $pages->offset : $offset)
            ->orderBy('receive_status,created_at desc')
            ->asArray()
            ->all();

        $result['pages'] = $pages;

        return $result;
    }

    /**
     * 有用，请不要删除
     * @param string $select_id
     * @param string $mission_id
     */
    public function saveSelected($select_id, $mission_id)
    {

        $select = new MsSelectTemp();
        $select->select_id = $select_id;
        $select->mission_id = $mission_id;
        $select->save();

    }

    /**
     * 有用，请不要删除
     * @param string $select_id
     * @param string $mission_id
     */
    public function saveLnExamPaperQuestTemp($select_id, $mission_id)
    {

        $select = new LnExamPaperQuestTemp();
        $select->examination_question_id = $select_id;
        $select->examination_paper_batch = $mission_id;
        $select->sequence_number = 0;
        $select->is_read = LnExamPaperQuestTemp::IS_READ_YES;
        $select->save();

    }


    /**
     * 根据用户ID返回消息
     * @param $id 用户id
     * @param $type 消息类型
     * @param $limit    返回记录条数
     * @param $offset   分页偏移量
     * @return mixed
     */
    public function getMessageByUid($id, $type, $time = null, $limit, $offset)
    {
        $messageUserModel = new MsMessageUser();
        $messageUserSql = $messageUserModel->find(false)
            ->andFilterWhere(['=', 'user_id', $id])
            ->select("msg_id")
            ->distinct()
            ->createCommand()
            ->getRawSql();

        $query = MsMessage::find(false)
            ->andWhere('kid in (' . $messageUserSql . ')')
            ->andFilterWhere(['=', 'message_type', $type]);

        if ($time == null) {
            $query = $query->orderBy('created_at desc');
        } else if ($time == 1) {
            $query = $query->orderBy('end_time, created_at desc');
        } else if ($time == 2) {
            $query = $query->orderBy('created_at desc');
        }

        $result['data'] = $query
            ->limit($limit)
            ->offset($offset)
            ->all();

        return $result;
    }

    /**
     * 发布问题消息推送
     * @param FwUser $user 用户
     * @param SoQuestion $question 问题
     */
    public function pushMessageByQuestion(FwUser $user, SoQuestion $question, $at_users)
    {
        //添加消息主体(@对象)
        $message = new MsMessage();
        $message->title = '【问答】' . $question->title;
        $message->content = $question->question_content;
        $message->object_id = $question->kid;
        $message->sender_id = $user->kid;
        $message->end_time = null;
        $message->msg_status = "";
        $message->message_type = MsMessage::MESSAGE_TYPE_QA;
        $message->data_from = MsMessage::DATA_FROM_AT;
        $message->needReturnKey = true;
        $message->save();

        self::addMessageUserList($message->kid, $at_users);
//        foreach ($at_users as $u) {
//            //向@用户推送消息
//            self::addMessageUser($message->kid, $u);
//        }

        //根据问题标签推送,目前用户没有设置个人感兴趣标签，暂时不推送
//        if (isset($question->tags) && $question->tags != null) {
//            $tags = explode(',', $question->tags);
//
//            $service = new TagService();
//
//            $tagCateId = $service->getTagCateIdByCateCode('interest');
//            $companyId = $user->company_id;
//
//            foreach ($tags as $tag) {
//                $relationshipUserList = $service->GetRelationshipListByValue($companyId, $tagCateId, $tag);
//                foreach ($relationshipUserList as $val) {
//                    $message_user = new MsMessageUser();
//                    $message_user->msg_id = $message->kid;
//                    $message_user->user_id = $val->subject_id;
//                    $message_user->receive_status = MsMessageUser::STATUS_UNRECEIVE;
//                    $message_user->save();
//                }
//            }
//        }
    }

    /**
     * 有用，请不要删除了
     * @param string $select_id
     * @param string $mission_id
     */
    public function deleteSelected($select_id, $mission_id)
    {
        $query = new Query();
        $query->createCommand()
            ->delete('eln_ms_select_temp',
                [
                    'select_id' => $select_id,
                    'mission_id' => $mission_id
                ])
            ->execute();


    }

    /**
     * 有用，请不要删除了
     * @param string $select_id
     * @param string $mission_id
     */
    public function deleteExaminationPaperQuestionTemp($select_id, $mission_id)
    {
        $query = new Query();
        $query->createCommand()
            ->delete('{{%ln_exam_paper_quest_temp}}',
                [
                    'examination_question_id' => $select_id,
                    'examination_paper_batch' => $mission_id
                ])
            ->execute();


    }

    /**
     * 记录分享消息推送
     * @param $uid 分享用户id
     * @param $share_users 被分享用户
     * @param SoRecord $record 记录
     */
    public function PushMessageByShare($uid, $share_users, SoRecord $record)
    {
//        $user = FwUser::findOne($uid);
//
//        $template = '';
//        if ($record->record_type === SoRecord::RECORD_TYPE_WEB) {
//            $template = self::MESSAGE_TEMPLATE_SHARE_WEB;
//        } else if ($record->record_type === SoRecord::RECORD_TYPE_EVENT) {
//            $template = self::MESSAGE_TEMPLATE_SHARE_EVENT;
//        } else if ($record->record_type === SoRecord::RECORD_TYPE_BOOK) {
//            $template = self::MESSAGE_TEMPLATE_SHARE_BOOK;
//        } else if ($record->record_type === SoRecord::RECORD_TYPE_EXP) {
//            $template = self::MESSAGE_TEMPLATE_SHARE_EXP;
//        }
//        $title = str_replace('{user}', $user->real_name, $template);

        $recordTypeText = '';
        if ($record->record_type === SoRecord::RECORD_TYPE_WEB) {
            $recordTypeText = '【网页】';
        } else if ($record->record_type === SoRecord::RECORD_TYPE_EVENT) {
            $recordTypeText = '【事件】';
        } else if ($record->record_type === SoRecord::RECORD_TYPE_BOOK) {
            $recordTypeText = '【书籍】';
        } else if ($record->record_type === SoRecord::RECORD_TYPE_EXP) {
            $recordTypeText = '【经验】';
        }

        //添加消息主体
        $message = new MsMessage();
//        $message->title = $title;
        $message->title = $recordTypeText . $record->title;
        $message->content = $record->content;
        $message->object_id = $record->kid;
        $message->object_type = MsMessage::OBJECT_TYPE_RECORD;
        $message->sender_id = $uid;
        $message->message_type = MsMessage::MESSAGE_TYPE_SOCIAL;
        $message->msg_status = '';
        $message->data_from = MsMessage::DATA_FROM_PERSON;
        $message->needReturnKey = true;
        $message->save();
        // 推送
        foreach ($share_users as $u) {
            if ($uid == $u->user_id) {
                continue;
            }
            self::addMessageUser($message->kid, $u->user_id);
        }
    }

    /**
     * 根据用户ID返回消息
     * @param $id 用户id
     * @param $type 消息类型
     * @return mixed
     */
    public function getMessageAndPage($id, $type, $size, $offset = -1)
    {
        $query = MsMessage::find(false);

        $query->leftJoin(MsMessageUser::tableName() . ' t1', MsMessage::tableName() . '.kid=t1.msg_id and t1.is_deleted = \'0\'')
            ->leftJoin(FwUser::tableName() . ' t2', MsMessage::tableName() . '.sender_id=t2.kid and t2.is_deleted = \'0\'')
            ->andFilterWhere(['=', 't1.user_id', $id])
            ->andFilterWhere(['=', 'message_type', $type]);

        $count = $query->count();
        $pages = new TPagination(['defaultPageSize' => $size, 'totalCount' => $count]);

        $query
            ->select([MsMessage::tableName() . '.*', 'receive_status' => 't1.receive_status', 'sender' => 't2.real_name'])
            ->limit($pages->limit)
            ->offset($offset < 0 ? $pages->offset : $offset)
            ->orderBy('t1.receive_status,' . MsMessage::tableName() . '.created_at desc');

        $result['data'] = $query
            ->all();

        $result['pages'] = $pages;

        return $result;
    }

    /**
     * 标记已读（通用）
     * @param $uid 用户id
     * @param $ids 消息id
     * @return bool
     */
    public function markRead($uid, $ids)
    {
        $model = new MsMessageUser();


        $kids = TStringHelper::ArrayToString($ids);

        if (!empty($kids)) {
            $attributes = [
                'receive_status' => MsMessageUser::STATUS_RECEIVE,
            ];

            $result = $model->updateAll($attributes, "receive_status <> '" . MsMessageUser::STATUS_RECEIVE . "' and user_id = '" . $uid . "' and msg_id in (" . $kids . ")");

            if ($result > 0) {
                return true;
            }
            return false;
        }
    }

    /**
     * 标记已读（新鲜事）
     * @param $uid
     * @param $id
     * @return bool
     */
    public function NewsMarkRead($uid, $id)
    {
        foreach ($id as $v) {
            $model = new MsMessageUser();
            $model->user_id = $uid;
            $model->msg_id = $v;
            $model->receive_status = MsMessageUser::STATUS_RECEIVE;
            $model->msg_type = MsMessageUser::TYPE_SPECIAL;
            $model->save();
        }
        return true;
    }

    /**
     * 判断消息是否已读
     * @param $uid 用户id
     * @param $msg_id 消息id
     * @param bool $is_special 是否特殊
     * @return bool true:已读, false:未读
     */
    public function IsRead($uid, $msg_id, $is_special = false)
    {
        $condition = array();
        $condition['user_id'] = $uid;
        $condition['msg_id'] = $msg_id;
        $condition['msg_type'] = $is_special ? MsMessageUser::TYPE_SPECIAL : MsMessageUser::TYPE_NORMAL;

        $model = MsMessageUser::findOne($condition, false);

        if ($model === null) {
            return false;
        }

        return $model->receive_status === MsMessageUser::STATUS_RECEIVE;
    }

    /**
     * 团队经理发送课程提醒
     * @param $cid 课程id
     * @param $uids 学员id[Array]
     */
    /**
     * 团队经理发送任务提醒
     * @param string $item_id 任务事项id
     * @param string $item_type 任务事项类型
     * @param int $plan_complete_at 完成时间
     * @param array $uids 学员id
     */
    public function sendTaskRemindByManager($item_id, $item_type, $plan_complete_at, $uids)
    {
        if ($item_type === MsTaskItem::ITEM_TYPE_COURSE) {
            $course = LnCourse::findOne($item_id);
            $title = '【课程】' . $course->course_name;
            $content = $course->course_desc_nohtml;
            $object_type = MsMessage::OBJECT_TYPE_COURSE;
        } elseif ($item_type === MsTaskItem::ITEM_TYPE_EXAM) {
            $exam = LnExamination::findOne($item_id);
            $title = '【考试】' . $exam->title;
            $content = $exam->description;
            $object_type = MsMessage::OBJECT_TYPE_EXAM;
        } elseif ($item_type === MsTaskItem::ITEM_TYPE_SURVEY) {
            $investigation = LnInvestigation::findOne($item_id);
            $title = '【调查】' . $investigation->title;
            $content = $investigation->description;
            $object_type = MsMessage::OBJECT_TYPE_SURVEY;
        }

        //指派人id
        $sponsor_id = Yii::$app->user->getId();

        //添加消息主体
        $message = new MsMessage();
        $message->title = $title;
        $message->content = $content ? $content : "暂时没有内容";
        $message->end_time = $plan_complete_at;
        $message->object_id = $item_id;
        $message->object_type = $object_type;
        $message->message_type = MsMessage::MESSAGE_TYPE_TODO;
        $message->sender_id = $sponsor_id;
        $message->msg_status = "";
        $message->data_from = MsMessage::DATA_FROM_MANAGER_PUSH;
        $message->needReturnKey = true;
        $message->save();

        self::addMessageUserList($message->kid, $uids);
//        foreach ($uids as $uid) {
//            // 发消息
//            self::addMessageUser($message->kid, $uid);
//        }
    }

    /**
     * 推送课程 消息推送
     * @param $push_user_id 推送人id
     * @param $course_id 课程id
     * @param $uid_list 推送对象id列表
     * @param $end_at 结束时间
     * @return bool
     * @version v4
     */
    public function pushByPushCourse($push_user_id, $course_id, $uid_list, $end_at = null)
    {
        //指派人id
//        $user = FwUser::findOne($push_user_id);
        $course = LnCourse::findOne($course_id);

        if ($course == null) {
            return false;
        }

        if ($uid_list == null || count($uid_list) == 0) {
            return false;
        }

//        $title = str_replace(['{p1}','{p2}'], [$user->real_name,$course->course_name], self::MESSAGE_TEMPLATE_PUSH_COURSE);

        //添加消息主体
        $message = new MsMessage();
        $message->title = '【课程】' . $course->course_name;
        $message->content = $course->course_desc_nohtml ? $course->course_desc_nohtml : "暂时没有内容";
        $message->object_id = $course->kid;
        $message->object_type = MsMessage::OBJECT_TYPE_COURSE;
        $message->sender_id = $push_user_id;
        $message->message_type = MsMessage::MESSAGE_TYPE_TODO;
        $message->end_time = $end_at;
        $message->msg_status = "";
        $message->data_from = MsMessage::DATA_FROM_MANAGER_PUSH;
        $message->needReturnKey = true;
        $message->save();

        self::addMessageUserList($message->kid, $uid_list);
//        foreach ($uid_list as $uid) {
//            // 发消息
//            self::addMessageUser($message->kid, $uid);
//        }

        return true;
    }

    /**
     * 指派课程 消息推送
     * @param $sponsor_id 指派人id
     * @param $course_id 课程id
     * @param $uid_list 推送对象id列表
     * @param $end_at 结束时间
     * @return bool
     * @version v4
     */
    public function pushByAssignCourse($sponsor_id, $course_id, $uid_list, $end_at = null)
    {
        //指派人id
//        $user = FwUser::findOne($sponsor_id);
        $course = LnCourse::findOne($course_id);

        if ($course == null) {
            return false;
        }

        if ($uid_list == null || count($uid_list) == 0) {
            return false;
        }

//        $title = str_replace(['{p1}','{p2}'], [$user->real_name,$course->course_name], self::MESSAGE_TEMPLATE_PUSH_COURSE);

        //添加消息主体
        $message = new MsMessage();
        $message->title = '【课程】' . $course->course_name;
        $message->content = $course->course_desc_nohtml ? $course->course_desc_nohtml : "暂时没有内容";
        $message->object_id = $course->kid;
        $message->object_type = MsMessage::OBJECT_TYPE_COURSE;
        $message->sender_id = $sponsor_id;
        $message->message_type = MsMessage::MESSAGE_TYPE_TODO;
        $message->end_time = $end_at;
        $message->msg_status = "";
        $message->data_from = MsMessage::DATA_FROM_PERSON;
        $message->needReturnKey = true;
        $message->save();

        self::addMessageUserList($message->kid, $uid_list);
//        foreach ($uid_list as $uid) {
//            // 发消息
//            self::addMessageUser($message->kid, $uid);
//        }

        return true;
    }

    /**
     * 讲师发布资源 消息推送
     * @param $teacher_id 讲师id
     * @param $course_id 课程id
     * @param $resource_id 资源id
     * @param $uid_list 推送对象id列表
     * @param $end_at 结束时间
     * @return bool
     * @version v4
     */
    public function pushByPublishResource($teacher_id, $course_id, $resource_id, $uid_list, $end_at = null)
    {
        //指派人id
//        $user = FwUser::findOne($teacher_id);
        $course = LnCourse::findOne($course_id);
//        $resource = LnModRes::findOne($resource_id);

        if ($course == null) {
            return false;
        }

        if ($uid_list == null || count($uid_list) == 0) {
            return false;
        }

        $title = str_replace('{p1}', $course->course_name, self::MESSAGE_TEMPLATE_PUBLISH_RESOURCE);;

        //添加消息主体
        $message = new MsMessage();
        $message->title = $title;
        $message->content = $course->course_desc_nohtml ? $course->course_desc_nohtml : "暂时没有内容";
        $message->object_id = $course->kid;
        $message->object_type = MsMessage::OBJECT_TYPE_COURSE;
        $message->sender_id = $teacher_id;
        $message->message_type = MsMessage::MESSAGE_TYPE_TODO;
        $message->end_time = $end_at;
        $message->data_from = MsMessage::DATA_FROM_TEACHER_PUSH;
        $message->save();
        $message->needReturnKey = true;

        self::addMessageUserList($message->kid, $uid_list);
//        foreach ($uid_list as $uid) {
//            // 发消息
//            self::addMessageUser($message->kid, $uid);
//        }

        return true;
    }

    /**
     * 回复问题时推送消息
     * @param $uid 用户id
     * @param SoAnswer $answer 回复主体
     */
    public function pushByAnswer($uid, SoAnswer $answer)
    {
        $question = SoQuestion::findOne($answer->question_id, false);

        // 非提问人自己回答
        if ($uid != $question->created_by) {
//            $content = '你提的问题有一个新的回答';
            //添加消息主体(提问人)
            $message = new MsMessage();
            $message->title = '【问答】' . $question->title;
            $message->content = $question->question_content;
            $message->object_id = $question->kid;
            $message->sender_id = $uid;
            $message->end_time = '0';
            $message->msg_status = '';
            $message->message_type = MsMessage::MESSAGE_TYPE_QA;
            $message->data_from = MsMessage::DATA_FROM_ANSWER;
            $message->needReturnKey = true;
            $message->save();

            self::addMessageUser($message->kid, $question->created_by);
        }

//        $content = '你关注的问题有一个新的回答';

        //添加消息主体(关注问题)
        $message = new MsMessage();
        $message->title = '【问答】' . $question->title;
        $message->content = $question->question_content;
        $message->object_id = $question->kid;
        $message->sender_id = $uid;
        $message->end_time = '0';
        $message->msg_status = '';
        $message->message_type = MsMessage::MESSAGE_TYPE_QA;
        $message->data_from = MsMessage::DATA_FROM_CARE_QUESTION;
        $message->needReturnKey = true;
        $message->save();

//        //关注条件
//        $condition = [
//            'question_id' => $question->kid
//        ];

        //获取所有关注用户
        $user_attention = SoQuestionCare::find(false)
            ->andFilterWhere(['=', 'status', SoQuestionCare::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'question_id', $question->kid])
            ->andFilterWhere(['<>', 'user_id', $answer->created_by])
            ->all();

        if (!empty($user_attention) && count($user_attention) > 0) {
            $user_attention = ArrayHelper::map($user_attention, 'user_id', 'user_id');
            $user_attention = array_keys($user_attention);

            self::addMessageUserList($message->kid, $user_attention);
//            foreach ($user_attention as $val) {
//                self::addMessageUser($message->kid, $val->user_id);
//            }
        }
    }

    /**
     * 关注用户消息发送
     * @param $care_id 被关注用户id
     */
    public function SendByCarePerson($care_id)
    {
        $uid = Yii::$app->user->getId();
        $real_name = Yii::$app->user->getIdentity()->real_name;

        $content = $real_name . '已关注我';
        //添加消息主体(提问人)
        $message = new MsMessage();
        $message->title = $content;
        $message->content = $content;
        $message->object_id = $care_id;
        $message->object_type = MsMessage::OBJECT_TYPE_PERSON;
        $message->sender_id = $uid;
        $message->message_type = MsMessage::MESSAGE_TYPE_SOCIAL;
        $message->msg_status = '';
        $message->data_from = MsMessage::DATA_FROM_SYSTEM;
        $message->needReturnKey = true;
        $message->save();

        self::addMessageUser($message->kid, $care_id);
    }

    /**
     * 课程分享消息推送
     * @param string $userId 分享用户id
     * @param SoShare $share 分享主体
     * @param array $attentionUserIdList 关注用户ID列表
     * @param array $atUserList @用户列表
     */
    public function pushMessageByCourseShare($userId, SoShare $share, $attentionUserIdList, $atUserList = null)
    {
        //添加关注消息主体
        $message = new MsMessage();
        $message->title = '【课程】' . $share->title;
        $message->content = $share->content ? $share->content : "暂时没有内容";
        $message->object_id = $share->obj_id;
        $message->object_type = MsMessage::OBJECT_TYPE_COURSE;
        $message->sender_id = $userId;
        $message->message_type = MsMessage::MESSAGE_TYPE_SOCIAL;
        $message->msg_status = '';
        $message->data_from = MsMessage::DATA_FROM_PERSON;
        $message->needReturnKey = true;
        $message->save();

        self::addMessageUserList($message->kid, $attentionUserIdList);

        if ($atUserList !== null) {
            $atUserIdList = TArrayHelper::get_array_key($atUserList, 'kid');
            self::addMessageUserList($message->kid, $atUserIdList);
        }
//        //获取所有关注对象
//        $user_attention = SoUserAttention::find(false)
//            ->andFilterWhere(['=', 'status', SoUserAttention::STATUS_FLAG_NORMAL])
//            ->andFilterWhere(['=', 'attention_id', $userId])
//            ->andFilterWhere(['<>', 'user_id', $userId])
//            ->all();
//
//        if (!empty($user_attention) && count($user_attention) > 0) {
//            $user_attention = ArrayHelper::map($user_attention, 'user_id', 'user_id');
//            $user_attention = array_keys($user_attention);
//            // 推送关注对象
//            self::addMessageUserList($message->kid, $user_attention);
////            foreach ($user_attention as $val) {
////                self::addMessageUser($message->kid, $val->user_id);
////            }
//        }
    }

    /**
     * 问题分享消息推送
     * @param string $uid 分享用户id
     * @param SoShare $share 分享主体
     * @param $uid_list 分享用户id列表
     */
    public function pushMessageByQuestionShare($uid, SoShare $share, $uid_list)
    {
//        $user = FwUser::findOne($uid);

//        $title = str_replace(['{p1}', '{p2}'], [$user->real_name, $share->title], self::MESSAGE_TEMPLATE_SHARE_QUESTION);

        //添加关注消息主体
        $message = new MsMessage();
        $message->title = '【问答】' . $share->title;
        $message->content = $share->content ? $share->content : "暂时没有内容";
        $message->object_id = $share->obj_id;
        $message->object_type = MsMessage::OBJECT_TYPE_QUESTION;
        $message->sender_id = $uid;
        $message->message_type = MsMessage::MESSAGE_TYPE_SOCIAL;
        $message->msg_status = '';
        $message->data_from = MsMessage::DATA_FROM_PERSON;
        $message->needReturnKey = true;
        $message->save();

        // 推送关注对象
        foreach ($uid_list as $val) {
            if ($uid == $val) {
                continue;
            }
            self::addMessageUser($message->kid, $val);
        }
    }

    /**
     * 提问@时消息推送
     * @param $publisher_id 提问人id
     * @param SoQuestion $question 问题id
     * @param $uid_list @用户id列表
     * @return bool
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

//        $title = str_replace('{p1}', $user->real_name, self::MESSAGE_TEMPLATE_QUESTION_AT);
//        $title = str_replace('{p2}', $question->title, $title);

        //添加消息主体
        $message = new MsMessage();
        $message->title = '【问答】' . $question->title;
        $message->content = $question->question_content;
        $message->object_id = $question->kid;
        $message->object_type = MsMessage::OBJECT_TYPE_QUESTION;
        $message->sender_id = $publisher_id;
        $message->message_type = MsMessage::MESSAGE_TYPE_QA;
        $message->msg_status = '';
        $message->data_from = MsMessage::DATA_FROM_AT;
        $message->needReturnKey = true;
        $message->save();
        if (!empty($uid_list)) {
            self::addMessageUserList($message->kid, $uid_list);
//            foreach ($uid_list as $uid) {
//                // 推送消息
//                self::addMessageUser($message->kid, $uid);
//            }
        }

        return true;
    }

    /**
     * 回答问题消息推送(提问人)
     * @param SoAnswer $answer 回答对象
     */
    public function QuestionAnswerToSub(SoAnswer $answer)
    {
//        $user = FwUser::findOne($answer->created_by);
        $question = SoQuestion::findOne($answer->question_id);

        // 非提问人自己回答
        if ($answer->created_by != $question->created_by) {
//            $title = str_replace('{p1}', $user->real_name, self::MESSAGE_TEMPLATE_MY_QUESTION_ANSWER);
//            $title = str_replace('{p2}', $question->title, $title);

            //添加消息主体(提醒提问人)
            $message = new MsMessage();
            $message->title = '【问答】' . $question->title;
            $message->content = $answer->answer_content;
            $message->object_id = $question->kid;
            $message->object_type = MsMessage::OBJECT_TYPE_QUESTION;
            $message->sender_id = $answer->created_by;
            $message->msg_status = '';
            $message->message_type = MsMessage::MESSAGE_TYPE_QA;
            $message->data_from = MsMessage::DATA_FROM_ANSWER;
            $message->needReturnKey = true;
            $message->save();

            self::addMessageUser($message->kid, $question->created_by);
        }
    }

    /**
     * 回答问题消息推送(关注问题者)
     * @param SoAnswer $answer 回答对象
     */
    public function QuestionAnswerToCare(SoAnswer $answer)
    {
//        $user = FwUser::findOne($answer->created_by);
        $question = SoQuestion::findOne($answer->question_id);

//        $title = str_replace('{p1}', $user->real_name, self::MESSAGE_TEMPLATE_CARE_QUESTION_ANSWER);
//        $title = str_replace('{p2}', $question->title, $title);

        //添加消息主体(提醒关注问题的用户)
        $message = new MsMessage();
        $message->title = '【问答】' . $question->title;
        $message->content = $answer->answer_content;
        $message->object_id = $question->kid;
        $message->object_type = MsMessage::OBJECT_TYPE_QUESTION;
        $message->sender_id = $answer->created_by;
        $message->msg_status = '';
        $message->message_type = MsMessage::MESSAGE_TYPE_QA;
        $message->data_from = MsMessage::DATA_FROM_CARE_QUESTION;
        $message->needReturnKey = true;
        $message->save();


        //获取所有关注用户
        $user_attention = SoQuestionCare::find(false)
            ->andFilterWhere(['=', 'question_id', $question->kid])
            ->andFilterWhere(['=', 'status', SoQuestionCare::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['<>', 'user_id', $answer->created_by])
            ->all();

        if (!empty($user_attention) && count($user_attention) > 0) {
            $user_attention = ArrayHelper::map($user_attention, 'user_id', 'user_id');
            $user_attention = array_keys($user_attention);

            self::addMessageUserList($message->kid, $user_attention);
//            foreach ($user_attention as $val) {
//                self::addMessageUser($message->kid, $val->user_id);
//            }
        }

    }

    /**
     * 评论回答消息推送(回答者)
     * @param SoAnswerComment $comment 评论对象
     */
    public function AnswerComment(SoAnswerComment $comment)
    {
//        $user = FwUser::findOne($comment->created_by);
        $answer = SoAnswer::findOne($comment->answer_id);
        $question = SoQuestion::findOne($answer->question_id);

        // 非回答人自己评论
        if ($comment->created_by != $answer->created_by) {
//            $title = str_replace('{p1}', $user->real_name, self::MESSAGE_TEMPLATE_ANSWER_COMMENT);
//            $title = str_replace('{p2}', $question->title, $title);

            //添加消息主体(提醒回答人)
            $message = new MsMessage();
            $message->title = '【问答】' . $question->title;
            $message->content = $comment->comment_content;
            $message->object_id = $question->kid;
            $message->object_type = MsMessage::OBJECT_TYPE_QUESTION;
            $message->sender_id = $comment->created_by;
            $message->msg_status = '';
            $message->message_type = MsMessage::MESSAGE_TYPE_QA;
            $message->data_from = MsMessage::DATA_FROM_COMMENT_ANSWER;
            $message->needReturnKey = true;
            $message->save();

            self::addMessageUser($message->kid, $answer->created_by);
        }
    }

    /**
     * 面授课程报名成功 消息推送
     * @param $approval_id 审批人id
     * @param $course_id 课程id
     * @param $reg_user_id 学员id
     * @return bool
     * @version v4
     */
    public function pushByCourseRegApproval($approval_id, $course_id, $reg_user_id, $enroll = false)
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

//        $title = str_replace('{p1}', $course->course_name, self::MESSAGE_TEMPLATE_REG_APPROVAL);

        //添加消息主体
        $message = new MsMessage();
        $message->title = '【课程】' . $course->course_name;
        $message->content = $course->course_desc_nohtml ? $course->course_desc_nohtml : "暂时没有内容";
        $message->object_id = $course->kid;
        $message->object_type = MsMessage::OBJECT_TYPE_COURSE;
        $message->sender_id = $approval_id;
        $message->msg_status = "";
        $message->message_type = MsMessage::MESSAGE_TYPE_TODO;
        $message->data_from = MsMessage::DATA_FROM_SYSTEM;

        if ($enroll) {
            $message->end_time = $course->open_end_time;
        }
        $message->needReturnKey = true;
        $message->save();

        // 推送消息
        self::addMessageUser($message->kid, $reg_user_id);

        return true;
    }

    /**
     * 记录分享消息推送
     * @param $uid 分享用户id
     * @param $share_users 被分享用户
     * @param SoRecord $record 记录
     */
    public function PushMessageByTimelineShare($uid, $share_users, $timeline_id)
    {
        $timeline = MsTimeline::findOne($timeline_id);

        $type = '';
        $typeText = '';
        $title = '';
        if ($timeline->object_type === MsTimeline::OBJECT_TYPE_COURSE) {
            $type = MsMessage::OBJECT_TYPE_COURSE;
            $typeText = '【课程】';
            $course = LnCourse::findOne($timeline->object_id);
            $title = $course->course_name;
        } else if ($timeline->object_type === MsTimeline::OBJECT_TYPE_QUESTION) {
            $type = MsMessage::OBJECT_TYPE_QUESTION;
            $typeText = '【问答】';
            $question = SoQuestion::findOne($timeline->object_id);
            $title = $question->title;
        } else if ($timeline->object_type === MsTimeline::OBJECT_TYPE_RECORD_WEB) {
            $type = MsMessage::OBJECT_TYPE_RECORD;
            $typeText = '【网页】';
            $record = SoRecord::findOne($timeline->object_id);
            $title = $record->title;
        } else if ($timeline->object_type === MsTimeline::OBJECT_TYPE_RECORD_EVENT) {
            $type = MsMessage::OBJECT_TYPE_RECORD;
            $typeText = '【事件】';
            $record = SoRecord::findOne($timeline->object_id);
            $title = $record->title;
        } else if ($timeline->object_type === MsTimeline::OBJECT_TYPE_RECORD_BOOK) {
            $type = MsMessage::OBJECT_TYPE_RECORD;
            $typeText = '【书籍】';
            $record = SoRecord::findOne($timeline->object_id);
            $title = $record->title;
        } else if ($timeline->object_type === MsTimeline::OBJECT_TYPE_RECORD_EXP) {
            $type = MsMessage::OBJECT_TYPE_RECORD;
            $typeText = '【经验】';
            $record = SoRecord::findOne($timeline->object_id);
            $title = $record->title;
        }

        //添加消息主体
        $message = new MsMessage();
        $message->title = $typeText . $title;
        $message->content = $timeline->content;
        $message->object_id = $timeline->object_id;
        $message->object_type = $type;
        $message->sender_id = $uid;
        $message->message_type = MsMessage::MESSAGE_TYPE_SOCIAL;
        $message->msg_status = '';
        $message->data_from = MsMessage::DATA_FROM_PERSON;
        $message->needReturnKey = true;
        $message->save();
        // 推送
        foreach ($share_users as $u) {
            if ($uid == $u->user_id) {
                continue;
            }
            self::addMessageUser($message->kid, $u->user_id);
        }
    }

    /**
     * 设置消息为已读
     * @param string $user_id 用户id
     * @param string $msg_id 消息id
     * @param string $type 消息类型
     */
    public static function SetReceive($user_id, $msg_id, $type)
    {
        if ($type === MsMessageUser::TYPE_NORMAL) {
            self::markRead($user_id, array($msg_id));
        } elseif ($type === MsMessageUser::TYPE_SPECIAL) {
            self::NewsMarkRead($user_id, array($msg_id));
        }
    }

    private function addMessageUser($messageId, $userId)
    {
        $message_user = new MsMessageUser();
        $message_user->msg_id = $messageId;
        $message_user->user_id = $userId;
        $message_user->receive_status = MsMessageUser::STATUS_UNRECEIVE;
        $message_user->msg_type = MsMessageUser::TYPE_NORMAL;
        $message_user->save();
    }

    private function addMessageUserList($messageId, $userIdList)
    {
        $saveModelList = [];
        foreach ($userIdList as $userId) {
            $messageUser = new MsMessageUser();
            $messageUser->msg_id = $messageId;
            $messageUser->user_id = $userId;
            $messageUser->receive_status = MsMessageUser::STATUS_UNRECEIVE;
            $messageUser->msg_type = MsMessageUser::TYPE_NORMAL;
            $saveModelList[] = $messageUser;
        }
        BaseActiveRecord::batchInsertSqlArray($saveModelList);
    }

    /**
     * 推送考试 消息推送
     * @param $push_user_id 推送人id
     * @param $exam_id 考试id
     * @param $uid_list 推送对象id列表
     * @param $end_at 结束时间
     * @return bool
     * @version v4.5
     */
    public function addByPushExam($push_user_id, $exam_id, $uid_list, $end_at = null)
    {
        //指派人id
        $exam = LnExamination::findOne($exam_id);

        if (empty($exam)) {
            return false;
        }

        if ($uid_list == null || count($uid_list) == 0) {
            return false;
        }

        //添加消息主体
        $message = new MsMessage();
        $message->title = '【考试】' . $exam->title;
        $message->content = $exam->description ? $exam->description : "暂时没有内容";
        $message->object_id = $exam->kid;
        $message->object_type = MsMessage::OBJECT_TYPE_EXAM;
        $message->sender_id = $push_user_id;
        $message->message_type = MsMessage::MESSAGE_TYPE_TODO;
        $message->end_time = $end_at;
        $message->msg_status = "";
        $message->data_from = MsMessage::DATA_FROM_MANAGER_PUSH;
        $message->needReturnKey = true;
        $message->save();

        self::addMessageUserList($message->kid, $uid_list);

//        foreach ($uid_list as $uid) {
//            // 发消息
//            self::addMessageUser($message->kid, $uid);
//        }

        return true;
    }

    /**
     * 推送调查 消息推送
     * @param $push_user_id 推送人id
     * @param $survey_id 调查id
     * @param $uid_list 推送对象id列表
     * @param $end_at 结束时间
     * @return bool
     * @version v4.5
     */
    public function addByPushSurvey($push_user_id, $survey_id, $uid_list, $end_at = null)
    {
        //指派人id
        $investigation = LnInvestigation::findOne($survey_id);

        if (empty($investigation)) {
            return false;
        }

        if ($uid_list == null || count($uid_list) == 0) {
            return false;
        }

        //添加消息主体
        $message = new MsMessage();
        $message->title = '【调查】' . $investigation->title;
        $message->content = $investigation->description ? $investigation->description : "暂时没有内容";
        $message->object_id = $investigation->kid;
        $message->object_type = MsMessage::OBJECT_TYPE_SURVEY;
        $message->sender_id = $push_user_id;
        $message->message_type = MsMessage::MESSAGE_TYPE_TODO;
        $message->end_time = $end_at;
        $message->msg_status = "";
        $message->data_from = MsMessage::DATA_FROM_MANAGER_PUSH;
        $message->needReturnKey = true;
        $message->save();

        self::addMessageUserList($message->kid, $uid_list);
//        foreach ($uid_list as $uid) {
//            // 发消息
//            self::addMessageUser($message->kid, $uid);
//        }

        return true;
    }
}