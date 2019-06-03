<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2016/5/14
 * Time: 10:56
 */

namespace common\services\message;

use common\base\BaseActiveRecord;
use common\models\framework\FwUser;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseEnroll;
use common\models\message\MsPushMsg;
use common\models\message\MsPushMsgObject;
use common\services\learning\CourseService;
use frontend\viewmodels\message\SendMailForm;
use Yii;
use yii\db\Exception;


class PushMessageService extends MsPushMsg
{
    public $htmlLayout = 'layouts/html';
    public $textLayout = 'layouts/text';

    /**
     * 发送邮件
     * @param string $companyId 公司id
     * @param string $senderId 发送者id
     * @param SendMailForm $sendMailForm
     * @return array|bool
     * @throws Exception
     */
    public function sendMail($companyId, $senderId, SendMailForm $sendMailForm)
    {
        if ($sendMailForm->sendUsers === 'all') {
            $sendMailForm->sendUsers = [];
            switch ($sendMailForm->scenes) {
                case 'enroll':
                    $courseService = new CourseService();
                    $data = $courseService->getAllEnrollApprovedUser($sendMailForm->objectId, 'user_id');
                    foreach ($data as $v) {
                        $sendMailForm->sendUsers[] = $v->user_id;
                    }
                    break;
            }
        } else {
            $sendMailForm->sendUsers = explode(',', $sendMailForm->sendUsers);
        }

        if (!isset($sendMailForm->sendUsers) || count($sendMailForm->sendUsers) === 0) {
            return Yii::t('frontend', 'choose_send');
        }

        if ($sendMailForm->ccEmail) {
            $sendMailForm->ccEmail = explode(';', $sendMailForm->ccEmail);
        }

        $msg = new MsPushMsg();
        $msg->company_id = $companyId;
        $msg->sender_id = $senderId;
        $msg->title = $sendMailForm->title;
        $msg->content = $sendMailForm->content;
        $msg->by_email = self::YES;
        $msg->send_method = $sendMailForm->sendMethod;
        $msg->cc_manager = $sendMailForm->ccManager;
        $msg->cc_self = $sendMailForm->ccSelf;
        $msg->by_sms = $sendMailForm->sendSMS;

        $transaction = $msg->getDb()->beginTransaction();
        if ($msg->save()) {
            $saveList = [];
            foreach ($sendMailForm->sendUsers as $uid) {
                $object = new MsPushMsgObject();
                $object->push_msg_id = $msg->kid;
                $object->push_flag = MsPushMsgObject::PUSH_FLAG_SEND;
                $object->obj_flag = MsPushMsgObject::OBJ_FLAG_SYSTEM;
                $object->obj_type = MsPushMsgObject::OBJ_TYPE_PER;
                $object->obj_range = MsPushMsgObject::OBJ_RANGE_SUB_NO;
                $object->obj_id = $uid;
                $object->needReturnKey = false;

                $saveList[] = $object;
            }

            foreach ($sendMailForm->ccEmail as $email) {
                $object = new MsPushMsgObject();
                $object->push_msg_id = $msg->kid;
                $object->push_flag = MsPushMsgObject::PUSH_FLAG_CC;
                $object->obj_flag = MsPushMsgObject::OBJ_FLAG_EXTERNAL;
                $object->obj_type = MsPushMsgObject::OBJ_TYPE_PER;
                $object->obj_range = MsPushMsgObject::OBJ_RANGE_SUB_NO;
                $object->ext_obj_address = $email;
                $object->needReturnKey = false;

                $saveList[] = $object;
            }

            $errMsg = '';
            $result = BaseActiveRecord::batchInsertSqlArray($saveList, $errMsg);
            if ($result) {
                $transaction->commit();
                return true;
            } else {
                $transaction->rollBack();
                return $errMsg;
            }
        } else {
            return $msg->getErrors();
        }
    }

    /**
     * 发送课程报名审批邮件
     * @param LnCourse $course 课程对象
     * @param string $senderId 发送者id
     * @param string $userId 报名用户id
     * @param string $type 审批类型
     * @return array|bool
     * @throws Exception
     */
    public function sendMailByCourseEnroll(LnCourse $course, $senderId, $userId, $type = LnCourseEnroll::ENROLL_TYPE_ALLOW)
    {
        $user = FwUser::findOne($userId);

        $tpl = 'courseAllowToUser';
        $message = 'course_allow_to_user';
        if ($type === LnCourseEnroll::ENROLL_TYPE_DISALLOW) {
            $tpl = 'courseDisallowToUser';
            $message = 'course_disallow_to_user';
        }

        $subject = Yii::t('common', $message, ['courseName' => $course->course_name]);
        $body = Yii::$app->mailer->render($tpl . '-html', ['user' => $user, 'course' => $course], $this->htmlLayout);

        $msg = new MsPushMsg();
        $msg->company_id = $user->company_id;
        $msg->sender_id = $senderId;
        $msg->title = $subject;
        $msg->content = $body;
        $msg->by_email = self::YES;
        $msg->send_method = MsPushMsg::SEND_METHOD_SINGLE;

        $transaction = $msg->getDb()->beginTransaction();
        if ($msg->save()) {
            $object = new MsPushMsgObject();
            $object->push_msg_id = $msg->kid;
            $object->push_flag = MsPushMsgObject::PUSH_FLAG_SEND;
            $object->obj_flag = MsPushMsgObject::OBJ_FLAG_SYSTEM;
            $object->obj_type = MsPushMsgObject::OBJ_TYPE_PER;
            $object->obj_range = MsPushMsgObject::OBJ_RANGE_SUB_NO;
            $object->obj_id = $userId;
            $object->needReturnKey = false;
            if ($object->save()) {
                $transaction->commit();
                return true;
            } else {
                $transaction->rollBack();
                return $object->getErrors();
            }
        } else {
            return $msg->getErrors();
        }
    }
}