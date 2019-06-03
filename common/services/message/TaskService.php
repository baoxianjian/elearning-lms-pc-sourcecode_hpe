<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/7/3
 * Time: 14:07
 */

namespace common\services\message;

use common\base\BaseService;
use common\models\framework\FwDomain;
use common\models\framework\FwOrgnization;
use common\models\framework\FwUser;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnCourseReg;
use common\models\learning\LnExamination;
use common\models\learning\LnExaminationResultUser;
use common\models\learning\LnInvestigation;
use common\models\learning\LnInvestigationResult;
use common\models\learning\LnModRes;
use common\models\learning\LnRelatedUser;
use common\models\learning\LnResComplete;
use common\models\message\MsPushObject;
use common\models\message\MsPushResult;
use common\models\message\MsPushResultTemp;
use common\models\message\MsTask;
use common\models\message\MsTaskItem;
use common\models\message\MsTimeline;
use common\services\framework\OrgnizationService;
use common\services\framework\UserService;
use common\services\learning\CourseCompleteService;
use common\services\learning\CourseService;
use common\services\learning\ExaminationService;
use common\services\learning\RecordService;
use common\base\BaseActiveRecord;
use common\helpers\TArrayHelper;
use common\helpers\TTimeHelper;
use components\widgets\TPagination;
use Yii;
use yii\data\Pagination;
use yii\db\Query;
use yii\helpers\ArrayHelper;


class TaskService extends BaseService
{

    /**
     * 团队经理推送任务保存
     * @param $manager_id 经理id
     * @param $courses 课程id列表
     * @param $users 推送学员id列表
     */
    public function saveManagerPushTask($manager_id, $courses, $users, $plan_com_at = "", $domain = "", $companyId = "")
    {
        // 学员数组转换为字符串 ，用逗号隔开
        $usersStr = implode(',', $users);
        $usersNum = count($users);

        //任务
        $task = new MsTask();
        $task->task_sponsor_id = $manager_id;
        $task->task_code = time() . rand(1000, 9999);
        $task->task_type = MsTask::TASK_TYPE_MANAGER;
        $task->task_status = MsTask::TASK_STATUS_DONE;
        $task->push_user_list = $usersStr;
        $task->push_user_count = $usersNum;
        $task->complete_type = 1;
        $task->domain_id = $domain;
        $task->company_id = $companyId;
        $task->needReturnKey = true;
        $task->save();

        foreach ($users as $u) {
            //任务推送对象
            $pushObject = new MsPushObject();
            $pushObject->task_id = $task->kid;
            $pushObject->obj_type = MsPushObject::OBJ_TYPE_PER;
            $pushObject->obj_id = $u;
            $pushObject->save();
        }

        $i = 1;
        foreach ($courses as $c) {
            $model = LnCourse::findOne($c);

            if (empty($model)) {
                continue;
            }

            //任务事项
            $taskItem = new MsTaskItem();
            $taskItem->task_id = $task->kid;
            $taskItem->item_id = $c;
            $taskItem->item_title = $model->course_name;
            $taskItem->item_type = MsTaskItem::ITEM_TYPE_COURSE;
            $taskItem->sequence_number = $i;
            $taskItem->plan_complete_at = $plan_com_at;
            $taskItem->save();
            $i++;
        }
    }

    /**
     * 团队经理任务保存
     * @param string $manager_id 经理id
     * @param MsTaskItem $taskItem 任务对象
     * @param array $users 推送学员列表
     * @param $domain_id 域id
     * @param $company_id 公司id
     * @return bool|string
     */
    public function SaveManagerTask($manager_id, MsTaskItem $taskItem, $users, $domain_id, $company_id)
    {
        // 学员数组转换为字符串 ，用逗号隔开
        $usersStr = implode(',', $users);
        $usersNum = count($users);

        //任务
        $task = new MsTask();
        $task->task_sponsor_id = $manager_id;
        $task->task_code = $task->getTaskCode($company_id);
        $task->task_type = MsTask::TASK_TYPE_MANAGER;
        $task->task_status = MsTask::TASK_STATUS_DONE;
        $task->push_user_list = $usersStr;
        $task->push_user_count = $usersNum;
        $task->complete_type = MsTask::COMPLETE_TYPE_ALL_SUCCESS;
        $task->domain_id = $domain_id;
        $task->company_id = $company_id;
        $task->needReturnKey = true;
        $task->save();

        //任务事项
        $taskItem->task_id = $task->kid;
        $taskItem->save();

        $objectArray = array();
        $resultArray = array();
        $relatedArray = array();

        $related_object_type = '';

        if ($taskItem->item_type === MsTaskItem::ITEM_TYPE_COURSE) {
            $related_object_type = LnRelatedUser::OBJECT_TYPE_COURSE;
        } elseif ($taskItem->item_type === MsTaskItem::ITEM_TYPE_EXAM) {
            $related_object_type = LnRelatedUser::OBJECT_TYPE_EXAM;
        } elseif ($taskItem->item_type === MsTaskItem::ITEM_TYPE_SURVEY) {
            $related_object_type = LnRelatedUser::OBJECT_TYPE_SURVEY;
        }

        foreach ($users as $u) {
            //任务推送对象
            $pushObject = new MsPushObject();
            $pushObject->task_id = $task->kid;
            $pushObject->obj_type = MsPushObject::OBJ_TYPE_PER;
            $pushObject->obj_id = $u;
            $objectArray[] = $pushObject;

            //任务推送结果
            $pushResult = new MsPushResult();
            $pushResult->task_id = $task->kid;
            $pushResult->user_id = $u;
            $pushResult->push_status = MsPushResult::PUSH_STATUS_SUCCESS;
            $resultArray[] = $pushResult;

            if (!$this->isPush($taskItem->item_id, $related_object_type, $company_id, $u)) {
                $relatedUser = new LnRelatedUser();
                $relatedUser->learning_object_id = $taskItem->item_id;
                $relatedUser->learning_object_type = $related_object_type;
                $relatedUser->company_id = $company_id;
                $relatedUser->user_id = $u;
                $relatedUser->start_at = time();
                $relatedUser->status = LnRelatedUser::STATUS_FLAG_NORMAL;
                $relatedArray[] = $relatedUser;
            }
        }

        $errMsg = '';
        if (BaseActiveRecord::batchInsertSqlArray($objectArray, $errMsg) &&
            BaseActiveRecord::batchInsertSqlArray($resultArray, $errMsg) &&
            BaseActiveRecord::batchInsertSqlArray($relatedArray, $errMsg)
        ) {
            $timelineService = new TimelineService();
            $messageService = new MessageService();
            $recordService = new RecordService();

            if ($taskItem->item_type === MsTaskItem::ITEM_TYPE_COURSE) {
                $courseService = new CourseService();
                $compeleteService = new CourseCompleteService();

                $updateUser = array();
                $sendMsgUser = array();
                $timeLineUser = array();

                foreach ($users as $uid) {
                    $regModel = $courseService->getUserRegInfo($uid, $taskItem->item_id);

                    // 判断课程是否注册
                    if ($regModel) {
                        // 判断课程是否完成
                        if ($compeleteService->isCourseComplete($regModel->kid)) {
                            continue;
                        } else {
                            $updateUser[] = $uid;
                            $sendMsgUser[] = $uid;
                        }
                    } else {
                        //课程与用户注册
                        $courseService->regCourse($uid, $taskItem->item_id, LnCourseReg::REG_TYPE_MANAGER, $manager_id);
                        LnCourse::addFieldNumber($taskItem->item_id, 'register_number');
                        $sendMsgUser[] = $uid;
                        $timeLineUser[] = $uid;
                    }
                }

                if (count($sendMsgUser) > 0) {
                    $messageService->pushByPushCourse($manager_id, $taskItem->item_id, $users, $taskItem->plan_complete_at);
                }

                if (count($updateUser) > 0) {
                    $timelineService->batchUpdateTimelineEndtime($updateUser, $taskItem->item_id, MsTimeline::OBJECT_TYPE_COURSE, $taskItem->plan_complete_at);
                }

                if (count($timeLineUser) > 0) {
                    $timelineService->pushByPushCourse($manager_id, $taskItem->item_id, $timeLineUser, $taskItem->plan_complete_at);
                    $recordService->addByPushCourse("2", $manager_id, $taskItem->item_id, $timeLineUser);
                }
            } elseif ($taskItem->item_type === MsTaskItem::ITEM_TYPE_EXAM) {
                $sendMsgUser = array();
                $timelineService->addByPushExam($manager_id, $taskItem->item_id, $users, $taskItem->plan_complete_at, $sendMsgUser);
                if (count($sendMsgUser) > 0) {
                    $messageService->addByPushExam($manager_id, $taskItem->item_id, $sendMsgUser, $taskItem->plan_complete_at);
                }
                $recordService->addByPushExam("2", $manager_id, $taskItem->item_id, $users);
            } elseif ($taskItem->item_type === MsTaskItem::ITEM_TYPE_SURVEY) {
                $sendMsgUser = array();
                $timelineService->addByPushSurvey($manager_id, $taskItem->item_id, $users, $taskItem->plan_complete_at, $sendMsgUser);
                if (count($sendMsgUser) > 0) {
                    $messageService->addByPushSurvey($manager_id, $taskItem->item_id, $sendMsgUser, $taskItem->plan_complete_at);
                }
                $recordService->addByPushSurvey("2", $manager_id, $taskItem->item_id, $users);
            }
        } else {
            return $errMsg;
        }
    }

    /**
     * 团队经理课程 注册及消息推送
     * @param $courses
     * @param $users
     */
    public function pushMessageAndReg($courses, $users, $planTime)
    {
        //指派人id
        $sponsor_id = Yii::$app->user->getId();

        $courseService = new CourseService();
        $compeleteService = new CourseCompleteService();
        $timelineService = new TimelineService();
        $course_query = LnCourse::find(false);

        //        $user_query = FwUser::find(false);

        $course_list = $course_query
            ->andFilterWhere(['in', 'kid', $courses])
            ->all();

        foreach ($course_list as $course) {
            $sendMsgUser = array();
            $timeLineUser = array();

            foreach ($users as $user) {
                $regModel = $courseService->getUserRegInfo($user, $course->kid);

                // 判断课程是否注册
                if ($regModel) {
                    // $courseService->regCourse($user, $course->kid, LnCourseReg::REG_TYPE_MANAGER, $sponsor_id); 20151203 修复重复注册Bug
                    // 判断课程是否完成
                    if ($compeleteService->isCourseComplete($regModel->kid)) {
                        continue;
                    } else {
                        $sendMsgUser[] = $user;
                        isset($planTime[$course->kid]) ? ($timeLineUser[] = $user) : '';
                    }
                } else {
                    //课程与用户注册
                    $courseService->regCourse($user, $course->kid, LnCourseReg::REG_TYPE_MANAGER, $sponsor_id);
                    LnCourse::addFieldNumber($course->kid, 'register_number');
                    $sendMsgUser[] = $user;
                    $timeLineUser[] = $user;
                }
            }

            if ($sendMsgUser) {
                $messageService = new MessageService();
                $messageService->pushByPushCourse($sponsor_id, $course->kid, $sendMsgUser);
            }

            if ($timeLineUser) {
                $endTime = isset($planTime[$course->kid]) ? $planTime[$course->kid] : null;
                $timelineService->pushByPushCourse($sponsor_id, $course->kid, $timeLineUser, $endTime);
            }

        }
    }

    /**
     * 团队经理获取任务
     * @param $manager_id 经理id
     * @param $type 任务类型
     * @return mixed
     */
    public function getManagerTask($manager_id, $type, $key)
    {
        $taskModel = MsTask::find(false);

        $taskModel
            ->innerJoin(MsTaskItem::tableName() . ' item', MsTask::tableName() . '.kid = item.task_id and item.is_deleted = \'0\'')
            ->andWhere(['=', 'task_sponsor_id', $manager_id])
            ->andWhere(['=', 'task_type', MsTask::TASK_TYPE_MANAGER]);

        if ($type !== 'all') {
            $taskModel->andFilterWhere(['=', 'item.item_type', $type]);
        }

        if (!empty($key)) {
            $taskModel->andFilterWhere(['like', 'item_title', $key]);
        }

        $count = $taskModel->count(MsTask::tableName() . '.kid');
        $pages = new TPagination(['defaultPageSize' => '5', 'totalCount' => $count]);
        $nowTime = time();

        $task = $taskModel
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->select(MsTask::tableName() . '.kid,push_user_list')
            ->distinct()
            ->orderBy(MsTask::tableName() . '.created_at desc')
            ->asArray()
            ->all();

        if ($task) {
            foreach ($task as &$t) {
                $taskItem = MsTaskItem::findOne(['task_id' => $t['kid']]);
                $t['item_id'] = $taskItem->item_id;
                $t['item_type'] = $taskItem->item_type;
                $t['plan_complete_at'] = $taskItem->plan_complete_at;

                if ($taskItem->plan_complete_at && ($diffTime = $taskItem->plan_complete_at - $nowTime) >= 0) {
                    $t['plan_complete_str'] = TTimeHelper::managerTimeToStr($diffTime);
                } else {
                    $t['plan_complete_str'] = '已结束';
                }

                $user_ids = explode(',', $t['push_user_list']);
                $t['count'] = count($user_ids);

                if ($taskItem->item_type == MsTaskItem::ITEM_TYPE_COURSE) {
                    $query = new Query();

                    $course = $query
                        ->select('t2.kid,t2.course_name,t2.theme_url,m1.item_id,m1.plan_complete_at')
                        ->from('{{%ms_task_item}} as m1')
                        ->leftjoin('{{%ln_course}} as t2', 'm1.item_id = t2.kid and t2.is_deleted = \'0\' and m1.is_deleted = \'0\'')
                        ->andWhere(['task_id' => $t['kid']])
                        ->all();

                    $t['course_name'] = $course[0]['course_name'];
//                    $t['item_id'] = $course[0]['item_id'];
                    //取得课程封面
                    $t['theme'] = $course[0]['theme_url'] ? $course[0]['theme_url'] : '';

                    $t['courseResNum'] = LnModRes::find(false)->andWhere(['course_id' => $course[0]['kid']])->count();

                    $query = new Query();
                    $user = $query
                        ->from('{{%ms_push_object}} as t1')
                        ->leftJoin('{{%ln_course_complete}} as t2', 't1.is_deleted=\'0\' and t2.is_deleted=\'0\' and t1.obj_type=\'4\' and t1.obj_id = t2.user_id and t2.complete_type=\'1\' and (t2.complete_status=\'2\' or t2.is_retake=\'1\') and t2.course_id ="' . $t['item_id'] . '"')
                        ->leftjoin('{{%fw_user}} as t3', 't3.kid = t1.obj_id and t3.is_deleted=\'0\'')
                        ->groupBy('t1.kid')
                        ->where(['t1.task_id' => $t['kid']])
                        ->select('t2.kid as iscomplete,t3.real_name,t3.thumb,t3.kid,t3.email')
                        ->all();

//                    $t['count'] = count($user);
                    $t['un_users'] = array();

                    foreach ($user as $u) {
                        if (!$u['iscomplete']) {
                            $u['resCompleteNum'] = LnResComplete::find(false)
                                ->andWhere(['user_id' => $u['kid'], 'course_id' => $course[0]['kid']])
                                ->andWhere(['complete_type' => LnResComplete::COMPLETE_TYPE_FINAL, 'complete_status' => LnResComplete::COMPLETE_STATUS_DONE])
                                ->count();
                            $t['un_users'][] = $u;
                        }
                    }
                    $t['un_count'] = count($t['un_users']);
                } elseif ($taskItem->item_type == MsTaskItem::ITEM_TYPE_EXAM) {
                    $investigationModel = LnExamination::findOne($taskItem->item_id);
                    $t['course_name'] = $investigationModel->title;
                    $t['courseResNum'] = 1;

                    $query = FwUser::find(false);
                    $query->leftJoin(LnExaminationResultUser::tableName() . ' e', FwUser::tableName() . '.kid = e.user_id and e.examination_id = \'' . $taskItem->item_id . '\' and e.result_type=\'0\' and e.is_deleted = \'0\'')
                        ->andFilterWhere(['in', FwUser::tableName() . '.kid', $user_ids])
                        ->select('e.examination_status,real_name,thumb,email,' . FwUser::tableName() . '.kid');

                    $user = $query->asArray()->all();
                    $t['un_users'] = array();

                    foreach ($user as $u) {
                        if ($u['examination_status'] !== LnExaminationResultUser::EXAMINATION_STATUS_END) {
                            $u['resCompleteNum'] = 0;
                            $t['un_users'][] = $u;
                        }
                    }
                    $t['un_count'] = count($t['un_users']);
                } elseif ($taskItem->item_type == MsTaskItem::ITEM_TYPE_SURVEY) {
                    $investigationModel = LnInvestigation::findOne($taskItem->item_id);
                    $t['course_name'] = $investigationModel->title;
                    $t['courseResNum'] = 1;

                    $query = FwUser::find(false);
                    $query->leftJoin(LnInvestigationResult::tableName() . ' i', FwUser::tableName() . '.kid = i.user_id and i.investigation_id = \'' . $taskItem->item_id . '\' and i.is_deleted = \'0\' and i.course_id is null and i.course_reg_id is null and i.course_complete_id is null')
                        ->andFilterWhere(['in', FwUser::tableName() . '.kid', $user_ids])
                        ->select('i.investigation_id as iscomplete,real_name,thumb,email,' . FwUser::tableName() . '.kid')
                        ->distinct('i.investigation_id');

                    $user = $query->asArray()->all();
                    $t['un_users'] = array();

                    foreach ($user as $u) {
                        if (!$u['iscomplete']) {
                            $u['resCompleteNum'] = 0;
                            $t['un_users'][] = $u;
                        }
                    }
                    $t['un_count'] = count($t['un_users']);
                }
            }
        }

        $result['task'] = $task;
        $result['pages'] = $pages;

        return $result;
    }

    /**
     * 获取任务列表
     * @param $sponsor_id 指派人id
     * @param $size 条数
     * @param $search_key 搜索key
     * @param $search_date 搜索日期
     * @param $search_type 搜索类型
     * @return array
     */
    public function getTaskList($sponsor_id, $size, $search_key, $search_date, $search_type)
    {
        $result = array();

        $query = MsTask::find(false);

        $query->leftJoin(MsTaskItem::tableName() . ' item', MsTask::tableName() . '.kid=item.task_id and item.is_deleted=\'0\'')
            ->select([MsTask::tableName() . '.*', 'item_count' => 'count(item.kid)'])
            ->groupBy('item.task_id');

        $query->andFilterWhere(['=', 'task_sponsor_id', $sponsor_id])
            ->andFilterWhere(['=', 'task_type', MsTask::TASK_TYPE_ADMIN]);

        if ($search_key != null && $search_key != '') {
            $query->andFilterWhere(['like', 'task_code', $search_key]);

        }
        if ($search_date != null && $search_date != '') {
            $time = strtotime($search_date);
            $start_time = TTimeHelper::getCurrentDayStart($time);
            $end_time = TTimeHelper::getCurrentDayEnd($time);

            $query->andFilterWhere(['>=', MsTask::tableName() . '.created_at', strtotime($start_time)])
                ->andFilterWhere(['<=', MsTask::tableName() . '.created_at', strtotime($end_time)]);
        }

        if ($search_type != null && $search_type != '-1') {
            if ($search_type == '0') {
                $query->andFilterWhere(['=', 'task_status', MsTask::TASK_STATUS_PROGRESS]);
            } elseif ($search_type == '1') {
                $query->andFilterWhere(['=', 'task_status', MsTask::TASK_STATUS_DONE])
                    ->andFilterWhere(['=', 'complete_type', MsTask::COMPLETE_TYPE_ALL_SUCCESS]);
            } elseif ($search_type == '2') {
                $query->andFilterWhere(['=', 'task_status', MsTask::TASK_STATUS_DONE])
                    ->andFilterWhere(['=', 'complete_type', MsTask::COMPLETE_TYPE_ALL_FAIL]);
            } elseif ($search_type == '3') {
                $query->andFilterWhere(['=', 'task_status', MsTask::TASK_STATUS_DONE])
                    ->andFilterWhere(['=', 'complete_type', MsTask::COMPLETE_TYPE_PART_SUCCESS]);
            }
        }

        $pages = new Pagination(['defaultPageSize' => $size, 'totalCount' => $query->count()]);
        $result['pages'] = $pages;

        $query->orderBy('created_at desc');
        $result['data'] = $query->limit($pages->limit)->offset($pages->offset)->all();

        return $result;
    }

    /**
     * 获取任务总数
     * @param $sponsor_id 指派人id
     * @return int|string
     */
    public function getTaskCount($sponsor_id)
    {

        $query = MsTask::find(false);

        $query->andFilterWhere(['=', 'task_sponsor_id', $sponsor_id]);

        return $query->count();
    }

    /**
     * 推送任务
     * @param $sponsor_id 指派人id
     * @param $task_id 任务id
     * @param $domain_id 域id
     * @param $company_id 公司id
     * @param $task_type 任务类型
     * @param $items 任务事项
     * @param $objects 推送对象
     * @param $is_time_push 是否定时推送
     * @param $push_prepare_at 推送时间
     * @return bool|string
     */
    public function pushTask($sponsor_id, $task_id, $domain_id, $company_id, $task_type, $items, $objects, $is_time_push, $push_prepare_at, $is_temp)
    {
        if ($task_id === null) {
            $task = new MsTask();
            $task->task_code = $task->getTaskCode($company_id);
        } else {
            $task = MsTask::findOne($task_id);
            MsTaskItem::physicalDeleteAll(['task_id' => $task_id]);
            MsPushObject::physicalDeleteAll(['task_id' => $task_id]);
        }
        $task->task_sponsor_id = $sponsor_id;
        $task->domain_id = $domain_id;
        $task->company_id = $company_id;
        $task->task_type = $task_type;
        $task->task_status = MsTask::TASK_STATUS_TODO;
        $task->complete_type = MsTask::COMPLETE_TYPE_UNDONE;

        if ($is_time_push) {
            $task->push_prepare_at = $push_prepare_at;
        } else {
            $task->push_prepare_at = 0;
        }

        if ($is_temp === 'yes') {
            $task->status = MsTask::STATUS_FLAG_TEMP;
        } elseif ($is_temp === 'no') {
            $task->status = MsTask::STATUS_FLAG_NORMAL;
        }

        $task->needReturnKey = true;
        if (!$task->save()) {
            return false;
        } else {
            foreach ($items as $item) {
                $item->task_id = $task->kid;
            }

            BaseActiveRecord::batchInsertSqlArray($items);

            $objectIds = TArrayHelper::get_array_key($objects, 'obj_id');
            $saveObjectList = [];
            $subIds = [];
            foreach ($objects as $object) {
                if (in_array($object->obj_id, $subIds)) {
                    continue;
                }

                $object->task_id = $task->kid;

                if ($object->obj_type === MsPushObject::OBJ_TYPE_ORG) {
                    $subList = $this->getSubObject($object->obj_id, MsPushObject::OBJ_TYPE_ORG, $objectIds, $company_id, $task->kid, $subIds);
                    if ($subList !== null) {
                        $saveObjectList = array_merge($saveObjectList, $subList);
                    }
                }

                $saveObjectList[] = $object;
            }

            BaseActiveRecord::batchInsertSqlArray($saveObjectList);
        }

        return $task->kid;
    }

    /**
     * 获取子对象
     * @param string $parentId 父对象ID
     * @param string $objectType 对象类型
     * @param array $currentObjectIds 当前对象ID列表
     * @param string $companyId 公司ID
     * @param string $taskId 任务ID
     * @param array $subIds 子对象ID列表
     * @return array|null
     */
    private function getSubObject($parentId, $objectType, $currentObjectIds, $companyId, $taskId, &$subIds)
    {
        if ($objectType === MsPushObject::OBJ_TYPE_ORG) {
            if (in_array($parentId, $subIds)) {
                return null;
            }
            $orgService = new OrgnizationService();
            $orgList = $orgService->getSubOrgByParentId($parentId, $companyId);

            $temp = TArrayHelper::get_array_key($orgList, 'kid');
            $subIds = array_merge($subIds, $temp);

            if ($orgList !== null) {
                $result = [];
                foreach ($orgList as $index => $item) {
                    if (in_array($item, $currentObjectIds)) {
                        continue;
                    }
                    $model = new MsPushObject();
                    $model->obj_id = $item['kid'];
                    $model->obj_type = MsPushObject::OBJ_TYPE_ORG;
                    $model->task_id = $taskId;

                    $result[] = $model;
                }

                return $result;
            }
            return null;
        }
    }

    /**
     * 重新推送任务
     * @param $sponsor_id 指派人id
     * @param $task_id 任务id
     * @return bool
     */
    public function repushTask($sponsor_id, $task_id)
    {
        $task = MsTask::findOne(['kid' => $task_id, 'task_sponsor_id' => $sponsor_id]);

        if ($task != null && $task->task_status === MsTask::TASK_STATUS_DONE &&
            $task->complete_type != MsTask::COMPLETE_TYPE_ALL_SUCCESS
        ) {
            $task->task_status = MsTask::TASK_STATUS_PROGRESS;
            return $task->save();
        }

        return false;
    }

    /**
     * 取得任务事项
     * @param $task_id 任务id
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getTaskItemByTaskId($task_id)
    {
        $query_course = MsTaskItem::find(false);
        $query_exam = MsTaskItem::find(false);
        $query_survey = MsTaskItem::find(false);

        $query = new Query();

        $query_course->innerJoin(LnCourse::tableName() . ' c', MsTaskItem::tableName() . '.item_type=\'0\' and ' . MsTaskItem::tableName() . '.item_id=c.kid and c.is_deleted=\'0\'')
            ->select([MsTaskItem::tableName() . '.*', 'item_name' => 'c.course_name'])
            ->andFilterWhere(['=', 'task_id', $task_id]);

        $query_exam->innerJoin(LnExamination::tableName() . ' e', MsTaskItem::tableName() . '.item_type=\'1\' and ' . MsTaskItem::tableName() . '.item_id=e.kid and e.is_deleted=\'0\'')
            ->select([MsTaskItem::tableName() . '.*', 'item_name' => 'e.title'])
            ->andFilterWhere(['=', 'task_id', $task_id]);

        $query_survey->innerJoin(LnInvestigation::tableName() . ' i', MsTaskItem::tableName() . '.item_type=\'2\' and ' . MsTaskItem::tableName() . '.`item_id`=i.`kid` and i.is_deleted=\'0\'')
            ->select([MsTaskItem::tableName() . '.*', 'item_name' => 'i.title'])
            ->andFilterWhere(['=', 'task_id', $task_id]);

        $sql = $query_course->union($query_exam)->union($query_survey)->createCommand()->rawSql;

        $query->from("($sql) item")->orderBy('item.sequence_number');

        return $query->all();
    }

    /**
     * 取得任务对象列表
     * @param $task_id 任务id
     * @param $key 查询关键字
     * @param bool $is_fail 查询失败对象
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getTaskObjectByTaskId($task_id, $key, $size, $is_fail = false)
    {
        if (!$is_fail) {
            $query = MsPushResult::find(false);

            $query->leftJoin(FwUser::tableName() . ' u', MsPushResult::tableName() . '.user_id=u.kid and u.is_deleted=\'0\'')
                ->leftJoin(FwOrgnization::tableName() . ' o', 'u.orgnization_id=o.kid and o.is_deleted=\'0\'')
                ->select([MsPushResult::tableName() . '.*', 'object_name' => 'u.real_name', 'org_name' => 'o.orgnization_name']);
        } else {
            $query = MsPushResultTemp::find(false);

            $query->leftJoin(FwUser::tableName() . ' u', MsPushResultTemp::tableName() . '.user_id=u.kid and u.is_deleted=\'0\'')
                ->leftJoin(FwOrgnization::tableName() . ' o', 'u.orgnization_id=o.kid and o.is_deleted=\'0\'')
                ->select([MsPushResultTemp::tableName() . '.*', 'object_name' => 'u.real_name', 'org_name' => 'o.orgnization_name']);
        }

        $query->andFilterWhere(['=', 'task_id', $task_id]);

        if ($key != null && $key != '') {
            $query->andFilterWhere(['like', 'u.real_name', $key]);
        }

        $count = $query->count();
        $page = new TPagination(['defaultPageSize' => $size, 'totalCount' => $count]);

        $query->offset($page->offset)->limit($page->limit);

        $result['data'] = $query->all();
        $result['page'] = $page;

        return $result;
    }

    /**
     * 删除任务
     * @param $task_id 任务id
     * @return bool|int
     */
    public function deleteTask($task_id)
    {
//        $task = MsTask::findOne(['kid' => $task_id, 'task_status' => MsTask::TASK_STATUS_DONE]);
        $task = MsTask::findOne($task_id);

        if ($task) {
            $condition = 'task_id=:task_id';
            $params = ['task_id' => $task_id];
            MsPushResultTemp::deleteAll($condition, $params);
            MsPushResult::deleteAll($condition, $params);
            MsPushObject::deleteAll($condition, $params);
            MsTaskItem::deleteAll($condition, $params);
            return $task->delete();
        } else {
            return false;
        }
    }

    /**
     * 立即推送任务
     * @param $sponsor_id 指派人id
     * @param $task_id 任务id
     * @return bool
     */
    public function immediatelyPushTask(MsTask $task, $sponsor_id, $task_id)
    {
        if ($task != null && $task->task_status === MsTask::TASK_STATUS_TODO) {
            $task->push_prepare_at = 0;
            return $task->save();
        }

        return false;
    }

    /**
     * 团队经理获取学习管理员推送的任务
     * @param $manager_id 经理id
     * @param $type 任务类型
     * @return mixed
     */
    public function getAdminTask($manager_id, $type, $key)
    {
        $service = new UserService();
        $team_users = $service->getUserByReportManager($manager_id);

        if (!empty($team_users) && count($team_users) > 0) {
            $team_users = ArrayHelper::map($team_users, 'kid', 'kid');
            $team_users = array_keys($team_users);
        }

        $query = MsTaskItem::find(false);

        $query
            ->innerJoin(MsPushResult::tableName() . ' t1', MsTaskItem::tableName() . '.task_id = t1.task_id and t1.is_deleted = \'0\'')
            ->innerJoin(MsTask::tableName() . ' t2', MsTaskItem::tableName() . '.task_id = t2.kid and t2.is_deleted = \'0\'')
            ->select(MsTaskItem::tableName() . '.kid,item_id,item_type,plan_complete_at, GROUP_CONCAT(t1.user_id) as push_user_list')
            ->andFilterWhere(['=', 't2.task_type', MsTask::TASK_TYPE_ADMIN])
            ->andFilterWhere(['in', 't1.user_id', $team_users])
            ->groupBy(MsTaskItem::tableName() . '.kid,item_id,plan_complete_at');

        if ($type !== 'all') {
            $query->andFilterWhere(['=', 'item_type', $type]);
        }

        if (!empty($key)) {
            $query->andFilterWhere(['like', 'item_title', $key]);
        }

        $count = $query->count();
        $pages = new TPagination(['defaultPageSize' => '5', 'totalCount' => $count]);
        $nowTime = time();

        $task = $query
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->distinct()
            ->orderBy(MsTaskItem::tableName() . '.created_at desc')
            ->asArray()
            ->all();

        if ($task) {
            foreach ($task as &$t) {
                $taskItem = MsTaskItem::findOne($t['kid']);
//                $t['item_id'] = $t['kid'];

                if ($taskItem->plan_complete_at && ($diffTime = $taskItem->plan_complete_at - $nowTime) >= 0) {
                    $t['plan_complete_str'] = TTimeHelper::managerTimeToStr($diffTime);
                } else {
                    $t['plan_complete_str'] = '已结束';
                }

                $user_ids = explode(',', $t['push_user_list']);
                $t['count'] = count($user_ids);

                if ($taskItem->item_type == MsTaskItem::ITEM_TYPE_COURSE) {
                    $course = LnCourse::findOne($taskItem->item_id);

                    $t['course_name'] = $course->course_name;

                    //取得课程封面
                    $t['theme'] = $course->theme_url ? $course->theme_url : '';

                    $t['courseResNum'] = LnModRes::find(false)->andWhere(['course_id' => $course->kid])->count();

                    $t['un_users'] = array();

                    $query = FwUser::find(false);
                    $user = $query->leftJoin(LnCourseComplete::tableName() . ' t1', FwUser::tableName() . '.kid=t1.user_id and t1.is_deleted=\'0\' and complete_type=\'1\' and (complete_status=\'2\' or is_retake=\'1\') and course_id =\'' . $course->kid . '\'')
                        ->andFilterWhere(['in', FwUser::tableName() . '.kid', $user_ids])
                        ->select(FwUser::tableName() . '.kid,t1.kid as is_complete,real_name,thumb,email')
                        ->asArray()
                        ->all();

                    foreach ($user as $u) {
                        if (!$u['is_complete']) {
                            $u['resCompleteNum'] = LnResComplete::find(false)
                                ->andWhere(['user_id' => $u['kid'], 'course_id' => $course->kid])
                                ->andWhere(['complete_type' => LnResComplete::COMPLETE_TYPE_FINAL, 'complete_status' => LnResComplete::COMPLETE_STATUS_DONE])
                                ->count();
                            $t['un_users'][] = $u;
                        }
                    }
                    $t['un_count'] = count($t['un_users']);
                } elseif ($taskItem->item_type == MsTaskItem::ITEM_TYPE_EXAM) {
                    $examModel = LnExamination::findOne($taskItem->item_id);
                    $t['course_name'] = $examModel->title;
                    $t['courseResNum'] = 1;

                    $query = FwUser::find(false);
                    $query->leftJoin(LnExaminationResultUser::tableName() . ' e', FwUser::tableName() . '.kid = e.user_id and e.result_type=\'1\' and e.examination_id = \'' . $taskItem->item_id . '\' and e.is_deleted = \'0\'')
                        ->andFilterWhere(['in', FwUser::tableName() . '.kid', $user_ids])
                        ->select('e.examination_status as iscomplete,real_name,thumb,email,' . FwUser::tableName() . '.kid');

                    $user = $query->asArray()->all();
                    $t['un_users'] = array();

                    foreach ($user as $u) {
                        if ($u['iscomplete'] !== LnExaminationResultUser::EXAMINATION_STATUS_END) {
                            $u['resCompleteNum'] = 0;
                            $t['un_users'][] = $u;
                        }
                    }
                    $t['un_count'] = count($t['un_users']);
                } elseif ($taskItem->item_type == MsTaskItem::ITEM_TYPE_SURVEY) {
                    $investigationModel = LnInvestigation::findOne($taskItem->item_id);
                    $t['course_name'] = $investigationModel->title;
                    $t['courseResNum'] = 1;

                    $query = FwUser::find(false);
                    $query->leftJoin(LnInvestigationResult::tableName() . ' i', FwUser::tableName() . '.kid = i.user_id and i.investigation_id = \'' . $taskItem->item_id . '\' and i.is_deleted = \'0\' and i.course_id is null and i.course_reg_id is null and i.course_complete_id is null')
                        ->andFilterWhere(['in', FwUser::tableName() . '.kid', $user_ids])
                        ->select('i.investigation_id as iscomplete,real_name,thumb,email,' . FwUser::tableName() . '.kid')
                        ->distinct('i.investigation_id');

                    $user = $query->asArray()->all();
                    $t['un_users'] = array();

                    foreach ($user as $u) {
                        if (!$u['iscomplete']) {
                            $u['resCompleteNum'] = 0;
                            $t['un_users'][] = $u;
                        }
                    }
                    $t['un_count'] = count($t['un_users']);
                }
            }
        }

        $result['task'] = $task;
        $result['pages'] = $pages;

        return $result;
    }

    /**
     * 团队经理获取成员考试任务
     * @param $user_id 成员id
     * @param $type 完成类型
     * @param $key 关键字
     * @return mixed
     */
    public function getExamTaskByUserId($user_id, $type, $key, $limit, $offset, $current_time)
    {
        $query = MsTaskItem::find(false);

        $query
            ->innerJoin(MsPushResult::tableName() . ' t1', MsTaskItem::tableName() . '.task_id = t1.task_id and t1.is_deleted = \'0\'')
            ->innerJoin(LnExamination::tableName() . ' t2', MsTaskItem::tableName() . '.item_id = t2.kid and t2.is_deleted = \'0\'')
            ->leftJoin(LnExaminationResultUser::tableName() . ' t3', 't2.kid = t3.examination_id and t3.user_id=t1.user_id and t3.result_type=\'1\' and t3.is_deleted = \'0\'')
//            ->select('t1.kid as task_item_id,t2.kid as examination_id,t3.kid as result_id,t2.title,t2.examination_mode,t2.start_at,t2.end_at,pass_grade,t2.attempt_strategy,t2.release_status')
            ->select('t2.kid as examination_id,t3.kid as result_id,t2.title,t2.examination_mode,t2.start_at,t2.end_at,pass_grade,t2.attempt_strategy,t2.release_status')
            ->andFilterWhere(['=', 't1.user_id', $user_id])
            ->andFilterWhere(['<', MsTaskItem::tableName() . '.created_at', $current_time])
            ->distinct();

        if ($type !== 'all') {
            if ($type === 'finished') {
                $query->andWhere('t3.kid is not null');
            } elseif ($type === 'unfinished') {
                $query->andWhere('t3.kid is null');
            }
        }

        if (!empty($key)) {
            $query->andFilterWhere(['like', 'item_title', $key]);
        }
//        var_dump($query->createCommand()->rawSql);

        $task = $query
            ->offset($offset)
            ->limit($limit)
            ->distinct()
            ->orderBy(MsTaskItem::tableName() . '.created_at desc')
            ->asArray()
            ->all();

        if ($task) {
            $examService = new ExaminationService();
            foreach ($task as &$t) {
                $t['exam_result'] = $examService->GetExaminationByUserResultAll($user_id, $t['examination_id']);
                $t['exam_grade'] = $examService->GetExaminationGrade($user_id, $t['examination_id']);
            }
        }

        return $task;
    }

    /**
     * 团队经理获取成员调查任务
     * @param $user_id 成员id
     * @param $type 完成类型
     * @param $key 关键字
     * @return mixed
     */
    public function getSurveyTaskByUserId($user_id, $type, $status, $key, $limit, $offset, $current_time)
    {
        $query = MsTaskItem::find(false);

        $query
            ->innerJoin(MsPushResult::tableName() . ' t1', MsTaskItem::tableName() . '.task_id = t1.task_id and t1.is_deleted = \'0\'')
            ->innerJoin(LnInvestigation::tableName() . ' t2', MsTaskItem::tableName() . '.item_id = t2.kid and ' . MsTaskItem::tableName() . '.item_type=\'2\' and t2.is_deleted = \'0\'')
            ->leftJoin(LnInvestigationResult::tableName() . ' t3', 't2.kid = t3.investigation_id and t1.user_id=t3.user_id and t3.course_id is null and t3.course_reg_id is null and t3.is_deleted = \'0\'')
            ->select('t1.kid AS task_item_id,t2.kid as investigation_id,t2.title,t2.start_at,t2.end_at,t2.investigation_type,t2.answer_type,t3.investigation_id AS result_investigation_id,max(t3.updated_at) as complete_at')
            ->groupBy('t2.kid,t1.user_id')
            ->andFilterWhere(['=', 't1.user_id', $user_id])
//            ->andFilterWhere(['=', 't3.user_id', $user_id])
            ->andFilterWhere(['<', MsTaskItem::tableName() . '.created_at', $current_time]);

        if ($type !== 'all') {
            $query->andFilterWhere(['=', 't2.investigation_type', $type]);
        }

        if ($status !== 'all') {
            if ($status === 'finished') {
                $query->andWhere('t3.kid is not null');
            } elseif ($status === 'unfinished') {
                $query->andWhere('t3.kid is null');
            }
        }

        if (!empty($key)) {
            $query->andFilterWhere(['like', 'item_title', $key]);
        }
//        var_dump($query->createCommand()->rawSql);

        $task = $query
            ->offset($offset)
            ->limit($limit)
            ->distinct()
            ->orderBy(MsTaskItem::tableName() . '.created_at desc')
            ->asArray()
            ->all();

        return $task;
    }

    public function GetTaskDataByTaskID($task_id)
    {
        $task = MsTask::findOne($task_id);

        $domain = FwDomain::findOne($task->domain_id);

//        $query = MsTaskItem::find(false);
//
//        $items = $query->joinWith('lnCourse')
//            ->joinWith('lnExamination')
//            ->joinWith('lnInvestigation')
//            ->andWhere(['task_id' => $task_id])
//            ->all();

        $items = MsTaskItem::findAll(['task_id' => $task_id]);

//        $objects = MsPushObject::findAll(['task_id' => $task_id]);

        $query = MsPushObject::find(false);

        $objects = $query->joinWith('fwDomain')
            ->joinWith('fwOrgnization')
            ->joinWith('fwPosition')
            ->joinWith('fwUser')
            ->andWhere(['task_id' => $task_id])
            ->all();

        return [
            'task' => $task,
            'items' => $items,
            'objects' => $objects,
            'domain' => $domain,
        ];
    }

    /**
     * 是否推送过
     * @param $object_id 学习对象id
     * @param $object_type 学习对象类型
     * @param $company_id 公司id
     * @param $user_id 用户id
     * @return bool
     */
    public function isPush($object_id, $object_type, $company_id, $user_id)
    {
        $condition = [
            'user_id' => $user_id,
            'learning_object_id' => $object_id,
            'learning_object_type' => $object_type,
            'company_id' => $company_id,
            'status' => LnRelatedUser::STATUS_FLAG_NORMAL
        ];

        $query = LnRelatedUser::find(false);

        return $query->where($condition)->count('kid') > 0;
    }

    /**
     * 根据课程删除推送对象
     * @param $userId 用户id
     * @param $courseId 课程id
     */
    public function deleteTaskUserByCourse($userId, $courseId)
    {
        if ($userId && $courseId) {
            $query = MsTaskItem::find(false);
            $query->andFilterWhere(['=', 'item_id', $courseId])
                ->andFilterWhere(['=', 'item_type', MsTaskItem::ITEM_TYPE_COURSE])
                ->select('task_id')
                ->distinct();

            $subSql = $query->createCommand()->rawSql;

            $params = [
                ':user_id' => $userId,
            ];

            $condition = "user_id = :user_id and task_id in ($subSql)";

            MsPushResult::deleteAll($condition, $params);
        }
    }
}