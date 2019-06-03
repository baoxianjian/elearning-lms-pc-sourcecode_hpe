<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/23
 * Time: 17:19
 */

namespace common\services\common;


use common\base\BaseService;
use common\models\framework\FwUser;
use common\models\learning\LnCourse;
use common\models\message\MsPushMsg;
use common\models\message\MsPushMsgObject;
use Yii;

class MailService extends BaseService
{
    public $htmlLayout = 'layouts/html';
    public $textLayout = 'layouts/text';

    /**
     * 取消面授报名通过
     * @param $userId 用户id
     * @param LnCourse $course 课程对象
     * @return array|bool
     * @throws \yii\db\Exception
     */
    public function cancelCourse($userId, LnCourse $course)
    {
        $user = FwUser::findOne($userId);

        $tpl = 'cancelCourseAllowToUser';
        $message = 'cancel_course_allow_to_user';

        $subject = Yii::t('common', $message, ['courseName' => $course->course_name]);
        $body = Yii::$app->mailer->render($tpl . '-html', ['user' => $user, 'course' => $course], $this->htmlLayout);

        $msg = new MsPushMsg();
        $msg->company_id = $user->company_id;
        $msg->sender_id = $course->created_by;
        $msg->title = $subject;
        $msg->content = $body;
        $msg->by_email = MsPushMsg::YES;
        $msg->cc_self = MsPushMsg::YES;
        $msg->send_method = MsPushMsg::SEND_METHOD_BATCH;

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