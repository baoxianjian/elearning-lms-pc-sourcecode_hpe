<?php
/**
 * Created by PhpStorm.
 * User: liuc
 * Date: 2016/5/23
 * Time: 14:33
 */

namespace common\services\learning;


use common\models\framework\FwUser;
use common\models\framework\FwUserDisplayInfo;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnCourseEnroll;
use common\models\learning\LnCourseReg;
use common\models\learning\LnUserCertification;
use common\models\message\MsTimeline;
use common\services\framework\PointRuleService;
use common\services\message\MessageService;
use common\services\message\PushMessageService;
use common\services\message\TimelineService;
use components\widgets\TPagination;
use Yii;
use yii\db\Query;

class CourseEnrollService extends LnCourseEnroll
{
    /**
     * 批量设置报名状态
     * @param $courseId 课程id
     * @param $enrollIds 报名表ids
     * @param $type 类型
     * @param $approvedId 审批者id
     * @return bool|string
     */
    public function BatchSetEnrollStatus($courseId, $enrollIds, $type, $approvedId)
    {
        $courseService = new CourseService();
        $pushService = new PushMessageService();

        $course = LnCourse::findOne($courseId);

        if (!$course) {
            return '课程不存在';
        }

        $enrollCount = count($enrollIds);

        if ($type == LnCourseEnroll::ENROLL_TYPE_ALLOW) {
            //课程名额判断
            $count = $courseService->getEnrollNumber($courseId, LnCourseEnroll::ENROLL_TYPE_ALLOW);
            if (($count + $enrollCount) > $course->limit_number) {
                return '报名名额不足';
            }
        }

//        $result = $this->buildInCondition('user_id', $userIds);
//
//        $params = array_merge([
//            ':course_id' => $courseId,
//        ], $result['params']);
//
//        $condition = 'user_id in (' . $result['where'] . ') and course_id = :course_id';
//
//        $attributes = [
//            'enroll_type' => $type,
//            'approved_by' => '',
//            'approved_at' => time(),
//        ];
//
//        $row = $course->updateAll($attributes, $condition, $params);

        foreach ($enrollIds as $id) {
            $data = LnCourseEnroll::findOne($id);
            if ($data) {
                if ($data->enroll_type !== LnCourseEnroll::ENROLL_TYPE_REG) {
                    continue;
//                    return ['result' => 'fail', 'errmsg' => '数据禁止编辑'];
                }
                if ($type == LnCourseEnroll::ENROLL_TYPE_ALLOW) {/*通过*/
                    $count = $courseService->getEnrollNumber($data->course_id, LnCourseEnroll::ENROLL_TYPE_ALLOW);
                    if ($count >= $course->limit_number) {
                        return '报名名额已满';
                    }
                    $data->enroll_type = LnCourseEnroll::ENROLL_TYPE_ALLOW;
                    $data->approved_by = $approvedId;
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
                        $timelineService->enrollCourseTimeline($data->user_id, $approvedId, $data->course_id);
                        /*更新时间轴*/
                        if ($course->open_status == LnCourse::COURSE_START) {
                            $timelineService->updateButtonType($data->user_id, $course->kid, MsTimeline::OBJECT_TYPE_COURSE, MsTimeline::BUTTON_TYPE_PROCESS);
                        }
                        $messageService = new MessageService();
                        $messageService->pushByCourseRegApproval($approvedId, $data->course_id, $data->user_id, true);
                        $recodeService = new RecordService();
                        $recodeService->addByEnrollCourse($data->user_id, $data->course_id);
                        /*发送邮件*/
                        $pushService->sendMailByCourseEnroll($course, $approvedId, $data->user_id, $type);
                        // 发送微信通知
//                        $courseService->sendWechatMessageToEnrollUser($data->course_id, $data->user_id, 'courseAllowToUser');
                    } else {
                        return '更新数据失败';
                    }
                } elseif ($type == LnCourseEnroll::ENROLL_TYPE_DISALLOW) {/*拒绝*/
                    $data->enroll_type = LnCourseEnroll::ENROLL_TYPE_DISALLOW;
                    if ($data->save()) {
                        $courseService->setCourseRegState($data->course_id, $data->user_id, LnCourseReg::REG_STATE_REJECTED);/*更新注册状态*/
                        /*发送邮件通知学员*/
                        $pushService->sendMailByCourseEnroll($course, $approvedId, $data->user_id, $type);
                        // 发送微信通知
//                        $courseService->sendWechatMessageToEnrollUser($data->course_id, $data->user_id, 'courseDisallowToUser');
                    } else {
                        return '更新数据失败';
                    }
                }
            } else {
                return '报名信息不存在';
            }
        }

        return true;
    }

    /**
     * 获取课程报名数据
     * @param $courseId
     * @param $params
     */
    public function getCourseEnroll($courseId, $params = array())
    {
        $userTable = FwUserDisplayInfo::tableName();
        $model = LnCourseEnroll::find(false);
        $model->leftJoin($userTable, $userTable . '.user_id=' . LnCourseEnroll::tableName() . '.user_id');
        $model->andFilterWhere(['=', LnCourseEnroll::tableName() . '.course_id', $courseId])
            ->andFilterWhere(['=', LnCourseEnroll::tableName() . '.enroll_type', LnCourseEnroll::ENROLL_TYPE_ALLOW]);
        /*关键词查询*/
        if (!empty($params['keyword'])) {
            $model->andFilterWhere(['or', 
            ['like', $userTable . '.real_name', $params['keyword']],
            ['like', $userTable . '.user_no', $params['keyword']], 
            ['like', $userTable . '.email', $params['keyword']], 
            ['like', $userTable . '.mobile_no', $params['keyword']], 
            ['like', $userTable . '.telephone_no', $params['keyword']], 
            ['like', $userTable . '.orgnization_name', $params['keyword']], 
            ['like', $userTable . '.position_name', $params['keyword']]]);
        }
        //姓名/工号/部门/岗位
        if (!empty($params['keyword2'])) {
            $model->andFilterWhere(['or', 
            ['like', $userTable . '.real_name', $params['keyword2']],
            ['like', $userTable . '.user_no', $params['keyword2']], 
            ['like', $userTable . '.orgnization_name', $params['keyword2']], 
            ['like', $userTable . '.position_name', $params['keyword2']]]);
        }
        
        $completeTable = LnCourseComplete::tableName();
        /*完成表*/
        $model->leftJoin($completeTable, $completeTable . '.user_id=' . LnCourseEnroll::tableName() . '.user_id and ' . $completeTable . '.course_id=' . LnCourseEnroll::tableName() . '.course_id and ' . $completeTable . '.complete_type='.LnCourseComplete::COMPLETE_TYPE_FINAL);
        /*是否及格*/
        $passed = intval($params['is_passed']);
        if (!empty($passed)) {
            $passed = $passed == 2 ? LnCourseComplete::IS_PASSED_NO : LnCourseComplete::IS_PASSED_YES;
            $model->andFilterWhere(['=', $completeTable . '.is_passed', $passed]);
        }
        /*学员证书表*/
        $certificationTable = LnUserCertification::tableName();
        $model->leftJoin($certificationTable, $certificationTable . '.user_id=' . LnCourseEnroll::tableName() . '.user_id and ' . $certificationTable . '.course_id=' . LnCourseEnroll::tableName() . '.course_id');
        /*是否颁发*/
        $isCertification = intval($params['is_certification']);
        if (!empty($isCertification) && $params['certification']) {
            if ($isCertification == 2) {
                $model->andWhere("ISNULL({$certificationTable}.`status`) OR {$certificationTable}.`status` <> '" . LnUserCertification::STATUS_FLAG_NORMAL . "'");
            } else {
                $model->andFilterWhere(['=', $certificationTable . '.status', LnUserCertification::STATUS_FLAG_NORMAL]);
            }
        }
        $model->select([
            LnCourseEnroll::tableName() . '.*',
            $completeTable . '.is_passed',
            $certificationTable . '.status as certification_status',
            $userTable . '.real_name',
            $userTable . '.user_no',
            $userTable . '.email',
            $userTable . '.mobile_no',
            $userTable . '.telephone_no',
            $userTable . '.orgnization_name',
            $userTable . '.position_name',
        ])->groupBy(LnCourseEnroll::tableName() . '.user_id');
        
        //按报名时间排序
        $model->orderBy('enroll_time ASC');
        
        
        $count = $model->count('kid');

        $res = array();
        $page = null;
        if ($count > 0) {
            if ($params['full'] == 'False') {
                $page = new TPagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
                $res = $model->offset($page->offset)->limit($page->limit)->asArray()->all();
            }else{
                $res = $model->asArray()->all();
            }

            if (!empty($res)) {
                $courseCompleteService = new CourseCompleteService();
                foreach ($res as $k => $v) {
                    /*获取注册ID*/
                    $userCourseReg = LnCourseReg::findOne(['course_id' => $courseId, 'user_id' => $v['user_id']]);
                    /*判断是否学习*/
                    if (!$courseCompleteService->checkCourseCompleteInfoExist($userCourseReg->kid, LnCourseComplete::COMPLETE_TYPE_FINAL)) {
                        if (!empty($userCourseReg)) {
                            /*初始化课程完成记录*/
                            $courseCompleteService->initCourseCompleteInfo($userCourseReg->kid, $courseId, $v['user_id']);
                        }
                        //$res[$k]['is_passed'] = false;
                    }/* else {
                        //判断是否及格
                        $res[$k]['is_passed'] = $courseCompleteService->isUserPass($userCourseReg->kid, $courseId, $v['user_id']);
                    }*/
                }
            }
        }

        return ['data' => $res, 'page' => $page];
    }

    /**
     * 获取课程正式学员
     * @param string $courseId 课程id
     * @param bool $returnCount 仅返回count
     * @return array|int|string|\yii\db\ActiveRecord[]
     */
    public function getCourseRegularStudent($courseId, $returnCount = false)
    {
        $query = LnCourseEnroll::find(false);

        $query
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'approved_state', LnCourseEnroll::APPROVED_STATE_APPROVED])
            ->andFilterWhere(['=', 'enroll_type', LnCourseEnroll::ENROLL_TYPE_ALLOW]);

        if ($returnCount) {
            return $query->count('1');
        }

        return $query->all();
    }

    private function buildInCondition($column, $values)
    {
        $result = [];
        $params = [];
        foreach ($values as $index => $value) {
            $name = ':' . $column . $index;
            $params[$name] = $value;
        }

        $result['where'] = implode(',', array_keys($params));
        $result['params'] = $params;

        return $result;
    }

    /**
     * 课程报名
     * @param $courseId
     * @param $userId
     * @return array
     */
    public function courseEnroll($courseId, $userId){
        $data = LnCourse::findOne($courseId);
        $time = time();
        if ($data->enroll_start_time != null && $data->enroll_start_time > $time) {
            return ['result' => 'fail', 'errcode' => 'time_fail', 'errmsg' => Yii::t('frontend', 'no_registration_time')];
        }
        if ($data->enroll_end_time != null && $data->enroll_end_time <= $time) {
            return ['result' => 'fail', 'errcode' => 'time_fail', 'errmsg' => Yii::t('frontend', 'registration_period_has_ended')];
        }
        $courseService = new CourseService();
        $count = $courseService->getEnrollNumber($courseId, [LnCourseEnroll::ENROLL_TYPE_REG, LnCourseEnroll::ENROLL_TYPE_ALLOW]);
        if ($data->limit_number > $count) {
            /*报名及注册*/
            $enroll_info = array(
                'course_id' => $courseId,
                'user_id' => $userId,
                'enroll_type' => LnCourseEnroll::ENROLL_TYPE_REG,
                'enroll_user_id' => $userId,
                'enroll_method' => LnCourseEnroll::ENROLL_METHOD_SELF,
            );
            $result = $courseService->saveEnrollInfo($enroll_info);
            if ($result['result'] == 'fail') {
                return $result;
            }
            LnCourse::addFieldNumber($courseId, 'register_number');/*增加注册量*/
            return ['result' => 'success', 'errcode' => 'normal', 'div' => 'signUpResult_note'];
        } else {
            if ($data->is_allow_over == LnCourse::IS_ALLOW_OVER_YES) {
                $alternate_count = $courseService->getEnrollNumber($courseId, LnCourseEnroll::ENROLL_TYPE_ALTERNATE);
                if ($data->allow_over_number != null && $data->allow_over_number > $alternate_count) {
                    /*候选报名*/
                    $enroll_info = array(
                        'course_id' => $courseId,
                        'user_id' => $userId,
                        'enroll_type' => LnCourseEnroll::ENROLL_TYPE_ALTERNATE,
                        'enroll_user_id' => $userId,
                        'enroll_method' => LnCourseEnroll::ENROLL_METHOD_SELF,
                    );
                    $courseService->saveEnrollInfo($enroll_info);
                    LnCourse::addFieldNumber($courseId, 'register_number');/*增加注册量*/
                    return ['result' => 'success', 'errcode' => 'normal', 'div' => 'signUpResult_note'];
                } else {
                    return ['result' => 'fail', 'errcode' => 'not_allow', 'errmsg' => Yii::t('common', 'status_enroll_2')];
                }
            } else {
                return ['result' => 'fail', 'errcode' => 'not_allow', 'errmsg' => Yii::t('common', 'status_enroll_2')];
            }
        }
    }

    /**
     * 手动添加报名学
     * @param $courseId
     * @param $userId
     * @return array
     */
    public function courseEnrollOther($courseId, $userId){
        $data = lnCourse::findOne($courseId);
        $time = time();
        //未到报名时间
        if ($data->enroll_start_time != null && $data->enroll_start_time > $time) {
            return ['result' => 'fail', 'errcode' => 'time_fail', 'errmsg' => Yii::t('frontend', 'no_registration_time')];
        }
        //报名时间已经结束
        if ($data->enroll_end_time != null && $data->enroll_end_time <= $time) {
            return ['result' => 'fail', 'errcode' => 'time_fail', 'errmsg' => Yii::t('frontend', 'registration_period_has_ended')];
        }
        //课程已结束
        if ($data->open_status == LnCourse::COURSE_END) {
            return ['result' => 'fail', 'errcode' => 'open_fail', 'errmsg' => Yii::t('common', 'status_enroll_2')];
        }
        $courseService = new CourseService();
        $count = $courseService->getEnrollNumber($courseId, [LnCourseEnroll::ENROLL_TYPE_REG, LnCourseEnroll::ENROLL_TYPE_ALLOW]);
        if ($data->limit_number > $count) {
            /*报名及注册*/
            $enroll_info = array(
                'course_id' => $courseId,
                'user_id' => $userId,
                'enroll_type' => LnCourseEnroll::ENROLL_TYPE_REG,
                'enroll_user_id' => $userId,
                'enroll_method' => LnCourseEnroll::ENROLL_METHOD_ADMIN,
            );
            $result = $courseService->saveOtherEnrollInfo($enroll_info);
            if ($result['result'] == 'fail') {
                return $result;
            }
            LnCourse::addFieldNumber($courseId, 'register_number');/*增加注册量*/
            return ['result' => 'success', 'errcode' => 'normal', 'div' => 'signUpResult_note'];
        } else {
            if ($data->is_allow_over == LnCourse::IS_ALLOW_OVER_YES) {
                $alternate_count = $courseService->getEnrollNumber($courseId, LnCourseEnroll::ENROLL_TYPE_ALTERNATE);
                if ($data->allow_over_number != null && $data->allow_over_number > $alternate_count) {
                    /*候选报名*/
                    $enroll_info = array(
                        'course_id' => $courseId,
                        'user_id' => $userId,
                        'enroll_type' => LnCourseEnroll::ENROLL_TYPE_ALTERNATE,
                        'enroll_user_id' => $userId,
                        'enroll_method' => LnCourseEnroll::ENROLL_METHOD_ADMIN,
                    );
                    $courseService->saveOtherEnrollInfo($enroll_info);
                    LnCourse::addFieldNumber($courseId, 'register_number');/*增加注册量*/
                    return ['result' => 'success', 'errcode' => 'normal', 'div' => 'signUpResult_note'];
                } else {
                    return ['result' => 'fail', 'errcode' => 'not_allow', 'errmsg' => Yii::t('common', 'status_enroll_2')];
                }
            } else {
                return ['result' => 'fail', 'errcode' => 'not_allow', 'errmsg' => Yii::t('common', 'status_enroll_2')];
            }
        }
    }

    /**
     * 获取面授课件报名数据
     * @param $courseId
     * @param $params
     * @return array
     */
    public function searchCourseEnroll($courseId, $params = null, $justReturnCount = false){
        $enrollModel = new Query();
        $enrollModel->from(LnCourseEnroll::tableName() . ' as len')
            ->leftJoin(FwUserDisplayInfo::tableName() . ' as t1', 't1.user_id = len.user_id')
            ->distinct()
            ->select('t1.real_name,t1.orgnization_name,t1.orgnization_name_path,t1.user_no,t1.location,t1.position_name,t1.email,len.kid,len.user_id,len.enroll_time,len.enroll_type,len.enroll_method,len.approved_state,t1.position_mgr_level_txt');
        $enrollModel->andWhere("len.is_deleted='0'")
            ->andWhere("t1.status='1' and t1.is_deleted='0'");
        if (!empty($params['keyword'])) {
            $params['keyword'] = trim($params['keyword']);
            $enrollModel->where("t1.real_name like '%{$params['keyword']}%' or t1.orgnization_name like '%{$params['keyword']}%' or t1.position_name like '%{$params['keyword']}%'");
        }
        if (isset($params['enroll_type']) && is_array($params['enroll_type'])) {
            $enrollModel->andFilterWhere(['in', 'len.enroll_type', $params['enroll_type']]);
        } elseif (isset($params['enroll_type']) && !is_array($params['enroll_type'])) {
            $enrollModel->andFilterWhere(['=', 'len.enroll_type', $params['enroll_type']]);
        }
        if (isset($params['approved_state'])) {
            $enrollModel->andFilterWhere(['=', 'len.approved_state', $params['approved_state']]);
        }

        if (isset($params['isDemo']) && $params['isDemo'] === '0') {
            $enrollModel->andFilterWhere(['<>', 'len.approved_state', LnCourseEnroll::APPROVED_STATE_APPLING]);
        }

        $enrollModel->andFilterWhere(['=', 'len.course_id', $courseId])
            ->andFilterWhere(['=', 'len.is_deleted', LnCourseEnroll::DELETE_FLAG_NO]);

        if (isset($params['filter']) && $params['filter'] == 2) {
            if (isset($params['isDemo']) && $params['isDemo'] === '1') {
                $enrollModel->andFilterWhere(['or', ['=', 'approved_state', LnCourseEnroll::APPROVED_STATE_APPLING], ['=', 'enroll_type', LnCourseEnroll::ENROLL_TYPE_REG]]);
            } else {
                $enrollModel->andFilterWhere(['=', 'approved_state', LnCourseEnroll::APPROVED_STATE_APPROVED])
                    ->andFilterWhere(['=', 'enroll_type', LnCourseEnroll::ENROLL_TYPE_REG]);
            }
        } elseif (isset($params['filter']) && $params['filter'] == 3) {
            $enrollModel->andFilterWhere(['=', 'enroll_type', LnCourseEnroll::ENROLL_TYPE_ALLOW]);
        } elseif (isset($params['filter']) && $params['filter'] == 4) {
            $enrollModel->andFilterWhere(['=', 'approved_state', LnCourseEnroll::APPROVED_STATE_REJECTED]);
        } elseif (isset($params['filter']) && $params['filter'] == 5) {
            $enrollModel->andFilterWhere(['=', 'enroll_type', LnCourseEnroll::ENROLL_TYPE_DISALLOW]);
        } else {

        }

        if (isset($params['sort']) && $params['sort'] == 2) {
            $enrollModel->orderBy('len.enroll_method asc,t1.orgnization_name desc,len.enroll_time');
        } else {
            $enrollModel->orderBy('len.approved_state,len.enroll_type,len.enroll_time');
        }
        $count = $enrollModel->count();
        if ($justReturnCount) {
            return $count;
        }

        if (isset($params['showAll']) && $params['showAll'] === 'True') {
            $pages = new TPagination(['defaultPageSize' => $count, 'totalCount' => $count]);
            $data = $enrollModel->all();
        } else {
            $pages = new TPagination(['defaultPageSize' => 12, 'totalCount' => $count]);
            $data = $enrollModel->offset($pages->offset)->limit($pages->limit)->all();
        }
        $result = array(
            'count' => $count,
            'pages' => $pages,
            'data' => $data,
        );
        return $result;
    }
}