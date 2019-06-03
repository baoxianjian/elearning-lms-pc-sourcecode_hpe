<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/5/27
 * Time: 16:20
 */

namespace common\services\learning;


use common\models\framework\FwUser;
use common\models\learning\LnCertification;
use common\models\learning\LnCourse;
use common\models\learning\LnExamination;
use common\models\learning\LnInvestigation;
use common\models\learning\LnRecord;
use common\models\learning\LnUserCertification;
use common\models\social\SoQuestion;
use common\models\social\SoRecord;
use common\base\BaseActiveRecord;
use Yii;

class RecordService extends LnRecord
{
    const RECORD_TEMPLATE_COURSE_1 = '我在{time}{verb}了{category}《{acivity}》';
    const RECORD_TEMPLATE_COURSE_2 = '我在{time}{verb}了{category}《{acivity}》，获得{result}个学分';
    const RECORD_TEMPLATE_COURSE_3 = '{who}在{time}给你{verb}了{category}《{acivity}》';
    const RECORD_TEMPLATE_COURSE_4 = '我{verb}参加课程《{acivity}》审核通过';
    const RECORD_TEMPLATE_QUESTION_1 = '我在{time}{verb}了关于"{acivity}"的问题';
    const RECORD_TEMPLATE_QUESTION_2 = '我在{time}{verb}【{result}】回答关于“{acivity}”的问题';
    const RECORD_TEMPLATE_RECORD_1 = '我在{time}{verb}了{category}"{acivity}"';
    const RECORD_TEMPLATE_EXAM_1 = '{who}在{time}给你{verb}了{category}《{acivity}》';
    const RECORD_TEMPLATE_EXAM_2 = '我在{time}{verb}了{category}《{acivity}》，获得{result}分';
    const RECORD_TEMPLATE_SURVEY_1 = '{who}在{time}给你{verb}了{category}《{acivity}》';
    const RECORD_TEMPLATE_SURVEY_2 = '我在{time}{verb}了{category}《{acivity}》';

    /**
     * 注册课程->添加学习记录
     * @param $user_id 用户id
     * @param $course_id 课程id
     */
    public function addByRegCourse($user_id, $course_id)
    {
        $course = LnCourse::findOne($course_id);

        $model = new LnRecord();
        $model->user_id = $user_id;
        $model->object_id = $course->kid;
        $model->object_type = LnRecord::OBJECT_TYPE_COURSE;
        $model->record_category = LnRecord::RECORD_CATEGORY_COURSE;
        $model->learning_verb = '注册';
        $model->learning_acivity = $course->course_name;
        $model->learning_result = null;
        $model->record_type = LnRecord::RECORD_TYPE_PROCESS;
        $model->data_from = LnRecord::DATA_FROM_LOCAL;
        $model->record_format = '1';
        $model->save();
    }

    /**
     * 学习课程->添加学习记录
     * @param string $user_id 用户id
     * @param string $course_id 课程id
     */
    public function addByLearningCourse($user_id, $course_id)
    {
        $course = LnCourse::findOne($course_id);

        $model = new LnRecord();
        $model->user_id = $user_id;
        $model->object_id = $course->kid;
        $model->object_type = LnRecord::OBJECT_TYPE_COURSE;
        $model->record_category = LnRecord::RECORD_CATEGORY_COURSE;
        $model->learning_verb = Yii::t('common', 'learn_button');
        $model->learning_acivity = $course->course_name;
        $model->learning_result = null;
        $model->record_type = LnRecord::RECORD_TYPE_PROCESS;
        $model->data_from = LnRecord::DATA_FROM_LOCAL;
        $model->record_format = '1';
        $model->save();
    }

    /**
     * 完成课程->添加学习记录
     * @param string $user_id 用户id
     * @param string $course_id 课程id
     * @param string $result 学习结果：得分
     */
    public function addByCompletedCourse($user_id, $course_id, $result)
    {
        $cacheKey = 'CourseCompleteRecord_' . $user_id . '_' . $course_id;

        // 避免重复添加
        if (Yii::$app->cache->exists($cacheKey)) {
            return true;
        }

        $course = LnCourse::findOne($course_id);

        if (!empty($course)) {
            $model = new LnRecord();
            $model->user_id = $user_id;
            $model->object_id = $course_id;
            $model->object_type = LnRecord::OBJECT_TYPE_COURSE;
            $model->record_category = LnRecord::RECORD_CATEGORY_COURSE;
            $model->learning_verb = '完成';
            $model->learning_acivity = $course->course_name;
            $model->learning_result = $result;
            $model->record_type = LnRecord::RECORD_TYPE_PROCESS;
            $model->data_from = LnRecord::DATA_FROM_LOCAL;
            $model->record_format = '2';
            $model->save();

            Yii::$app->cache->add($cacheKey, time(), 60);
        }
    }

    /**
     * 发布问题->添加学习记录
     * @param $user_id 提问人id
     * @param SoQuestion $question 问题
     */
    public function addBySubQuestion($user_id, SoQuestion $question)
    {
        $model = new LnRecord();
        $model->user_id = $user_id;
        $model->object_id = $question->kid;
        $model->object_type = LnRecord::OBJECT_TYPE_QUESTION;
        $model->record_category = LnRecord::RECORD_CATEGORY_QUESTION;
        $model->learning_verb = '提出';
        $model->learning_acivity = $question->title;
        $model->learning_result = null;
        $model->record_type = LnRecord::RECORD_TYPE_PROCESS;
        $model->data_from = LnRecord::DATA_FROM_LOCAL;
        $model->record_format = '1';
        $model->save();
    }

    /**
     * 关注问题->添加学习记录
     * @param $user_id 用户id
     * @param $question_id 问题id
     */
    public function addByCareQuestion($user_id, $question_id)
    {
        $question = SoQuestion::findOne($question_id);

        $model = new LnRecord();
        $model->user_id = $user_id;
        $model->object_id = $question->kid;
        $model->object_type = LnRecord::OBJECT_TYPE_QUESTION;
        $model->record_category = LnRecord::RECORD_CATEGORY_QUESTION;
        $model->learning_verb = '关注';
        $model->learning_acivity = $question->title;
        $model->learning_result = null;
        $model->record_type = LnRecord::RECORD_TYPE_PROCESS;
        $model->data_from = LnRecord::DATA_FROM_LOCAL;
        $model->record_format = '1';
        $model->save();
    }

    /**
     * 回答问题->添加学习记录
     * @param $user_id 用户id
     * @param $question_id 问题id
     */
    public function addByAnswerQuestion($user_id, $question_id)
    {
        $question = SoQuestion::findOne($question_id);

        $model = new LnRecord();
        $model->user_id = $user_id;
        $model->object_id = $question->kid;
        $model->object_type = LnRecord::OBJECT_TYPE_QUESTION;
        $model->record_category = LnRecord::RECORD_CATEGORY_QUESTION;
        $model->learning_verb = '回复';
        $model->learning_acivity = $question->title;
        $model->learning_result = null;
        $model->record_type = LnRecord::RECORD_TYPE_PROCESS;
        $model->data_from = LnRecord::DATA_FROM_LOCAL;
        $model->record_format = '1';
        $model->save();
    }

    /**
     * 记录(网页、事件、书籍、经验)->添加学习记录
     * @param $user_id 用户id
     * @param $record_id 记录id
     * @param $record_category 记录类型(网页、事件、书籍、经验)
     */
    public function addByRecord($user_id, $record_id, $record_category)
    {
        $record = SoRecord::findOne($record_id);

        $model = new LnRecord();
        $model->user_id = $user_id;
        $model->object_id = $record->kid;
        $model->object_type = LnRecord::OBJECT_TYPE_RECORD;
        $model->record_category = $record_category;
        $model->learning_verb = '记录';
        $model->learning_acivity = $record->title;
        $model->learning_result = null;
        $model->record_type = LnRecord::RECORD_TYPE_PROCESS;
        $model->data_from = LnRecord::DATA_FROM_LOCAL;
        $model->record_format = '1';
        $model->start_at = $record->start_at;
        $model->end_at = $record->end_at;
        $model->duration = $record->duration;
        $model->save();
    }

    /**
     * 提问@时 添加学习记录
     * @param $publisher_id
     * @param $question_id
     * @param $uid_list
     * @return bool
     */
    public function addByQuestionAt($publisher_id, $question_id, $uid_list)
    {
        $query = FwUser::find(false);

        $users = $query->andFilterWhere(['in', 'kid', $uid_list])->all();

        $question = SoQuestion::findOne($question_id);

        if ($question == null) {
            return false;
        }

        if ($users == null || count($users) == 0) {
            return false;
        }

        $i = 0;
        $target = [];
        foreach ($users as $user) {
            $i++;
            $target[] = $user->real_name;
            if ($i == 3) {
                break;
            }
        }

        $target = implode('、', $target);

        if (count($users) > 3) {
            $target .= '...';
        }

        $model = new LnRecord();
        $model->user_id = $publisher_id;
        $model->object_id = $question->kid;
        $model->object_type = LnRecord::OBJECT_TYPE_QUESTION;
        $model->record_category = LnRecord::RECORD_CATEGORY_QUESTION;
        $model->learning_verb = '请';
        $model->learning_acivity = $question->title;
        $model->learning_result = $target;
        $model->record_type = LnRecord::RECORD_TYPE_PROCESS;
        $model->data_from = LnRecord::DATA_FROM_LOCAL;
        $model->record_format = '2';
        $model->save();

        return true;
    }

    /**
     * 根据用户id取得学习历程（通用）
     * @param $user_id
     * @param $type
     * @param $size
     * @param $page
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getRecordByUserId($user_id, $type, $size, $page, $current_time)
    {
        $query = LnRecord::find(false);

        // 防止有新数据时，重复读取数据
        $query->andFilterWhere(['<', 'created_at', $current_time]);

        $query->andFilterWhere(['=', 'user_id', $user_id])
            ->andFilterWhere(['=', 'record_category', $type])
            ->orderBy('created_at desc')
            ->limit($size)
            ->offset(self::getOffset($page, $size));

        return $query->all();
    }

    /**
     * 根据用户id取得学习历程（记录专用）
     * @param $user_id
     * @param $type
     * @param $size
     * @param $page
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getRecordAndDataByUserId($user_id, $type, $size, $page, $current_time)
    {
        $query = LnRecord::find(false);

        $query->leftJoin(SoRecord::tableName() . ' sr', LnRecord::tableName() . '.object_id=sr.kid and ' . LnRecord::tableName() . '.object_type=\'record\'')
            ->andFilterWhere(['=', LnRecord::tableName() . '.user_id', $user_id])
            ->andFilterWhere(['=', 'record_category', $type])
            ->select(LnRecord::tableName() . '.*,sr.content,sr.url,sr.attach_original_filename,sr.attach_url')
            ->orderBy(LnRecord::tableName() . '.created_at desc')
            ->limit($size)
            ->offset(self::getOffset($page, $size));

        // 防止有新数据时，重复读取数据
        $query->andFilterWhere(['<', LnRecord::tableName() . '.created_at', $current_time]);

        return $query->asArray()->all();
    }

    /**
     * 根据用户id取得学习历程（证书专用）
     * @param $user_id
     * @param $type
     * @param $size
     * @param $page
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getCertAndDataByUserId($user_id, $type, $size, $page, $current_time)
    {
        $query = LnRecord::find(false);

        $query->leftJoin(LnUserCertification::tableName() . ' c', LnRecord::tableName() . '.object_id=c.kid and ' . LnRecord::tableName() . '.object_type=\'' . LnRecord::OBJECT_TYPE_CERT . '\'')
            ->andFilterWhere(['=', LnRecord::tableName() . '.user_id', $user_id])
            ->andFilterWhere(['=', 'c.is_deleted', self::DELETE_FLAG_NO])
            ->andFilterWhere(['=', 'record_category', $type])
            ->select(LnRecord::tableName() . '.*')
            ->orderBy(LnRecord::tableName() . '.created_at desc')
            ->limit($size)
            ->offset(self::getOffset($page, $size));

        // 防止有新数据时，重复读取数据
        $query->andFilterWhere(['<', LnRecord::tableName() . '.created_at', $current_time]);

        return $query->asArray()->all();
    }

    /**
     * 获取证书->添加学习记录
     * @param $user_id
     * @param $cert_id
     * @param $user_cert_id
     * @return bool
     */
    public function addByCertification($user_id, $cert_id, $user_cert_id, $systemKey = null)
    {
        $cert = LnCertification::findOne($cert_id);
        if ($cert == null) {
            return false;
        }

        $model = new LnRecord();
        $model->user_id = $user_id;
        $model->object_id = $user_cert_id;
        $model->object_type = LnRecord::OBJECT_TYPE_CERT;
        $model->record_category = LnRecord::RECORD_CATEGORY_CERT;
        $model->learning_verb = '获得';
        $model->learning_acivity = $cert->certification_name;
        $model->learning_result = null;
        $model->record_type = LnRecord::RECORD_TYPE_PROCESS;
        $model->data_from = LnRecord::DATA_FROM_LOCAL;
        $model->record_format = '1';

        $model->systemKey = $systemKey;
        $model->save();
    }

    /**
     * 取消证书->添加学习记录
     * @param $user_id
     * @param $cert_id
     * @param $user_cert_id
     * @return bool
     */
    public function addByCancelCertification($user_id, $cert_id, $user_cert_id)
    {
        $cert = LnCertification::findOne($cert_id);
        if ($cert == null) {
            return false;
        }

        $model = new LnRecord();
        $model->user_id = $user_id;
        $model->object_id = $user_cert_id;
        $model->object_type = LnRecord::OBJECT_TYPE_CERT;
        $model->record_category = LnRecord::RECORD_CATEGORY_CERT;
        $model->learning_verb = '取消';
        $model->learning_acivity = $cert->certification_name;
        $model->learning_result = null;
        $model->record_type = LnRecord::RECORD_TYPE_PROCESS;
        $model->data_from = LnRecord::DATA_FROM_LOCAL;
        $model->record_format = '2';
        $model->save();
    }

    /**
     * 推送/指派课程->添加学习记录
     * @param $type 类型：1：推送、2：指派
     * @param $push_user_id 推送人id
     * @param $course_id 课程id
     * @param $uid_list 推送对象id列表
     */
    public function addByPushCourse($type, $push_user_id, $course_id, $uid_list)
    {
        $course = LnCourse::findOne($course_id);

        $user = FwUser::findOne($push_user_id);

        $saveList = array();

        foreach ($uid_list as $uid) {
            $model = new LnRecord();
            $model->user_id = $uid;
            $model->object_id = $course->kid;
            $model->object_type = LnRecord::OBJECT_TYPE_COURSE;
            $model->record_category = LnRecord::RECORD_CATEGORY_COURSE;
            $model->learning_verb = ($type == 1 ? '推送' : '指派');
            $model->learning_acivity = $course->course_name;
            $model->learning_result = null;
            $model->record_type = LnRecord::RECORD_TYPE_PROCESS;
            $model->data_from = LnRecord::DATA_FROM_LOCAL;
            $model->record_format = '3';
            $model->push_user_id = $push_user_id;
            $model->push_user_name = $user->real_name;
            $saveList[] = $model;
        }
        BaseActiveRecord::batchInsertSqlArray($saveList);
    }


    /**
     * 课程报名通过->添加学习记录
     * @param $user_id 用户id
     * @param $course_id 课程id
     */
    public function addByEnrollCourse($user_id, $course_id)
    {
        $course = LnCourse::findOne($course_id);

        $model = new LnRecord();
        $model->user_id = $user_id;
        $model->object_id = $course->kid;
        $model->object_type = LnRecord::OBJECT_TYPE_COURSE;
        $model->record_category = LnRecord::RECORD_CATEGORY_COURSE;
        $model->learning_verb = '报名';
        $model->learning_acivity = $course->course_name;
        $model->learning_result = null;
        $model->record_type = LnRecord::RECORD_TYPE_PROCESS;
        $model->data_from = LnRecord::DATA_FROM_LOCAL;
        $model->record_format = '4';
        $model->push_user_id = null;
        $model->push_user_name = null;
        $model->save();
    }

//    public function getOffset($page, $size)
//    {
//        $_page = (int)$page - 1;
//
//        return $size < 1 ? 0 : $_page * $size;
//    }


    public function getRecordPageDataById($id, $time, $size, $page)
    {
        $time_condition = '';
        if ($time == 1) {
            $time_condition = 'created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 WEEK)) ';
        } else if ($time == 2) {
            $time_condition = 'created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) ';
        } else if ($time == 3) {
            $time_condition = 'created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 MONTH)) ';
        }

        $query = SoRecord::find(false);
        //$count = $query
        //    ->andFilterWhere(['user_id' => $id])->count();
        $result = $query
            ->andWhere($time_condition)
            ->andFilterWhere(['user_id' => $id])
            ->limit($size)
            ->offset($this->getOffset($page, $size))
            ->orderBy('created_at desc')
            ->all();
        return $result;
    }

    /**
     * 推送/指派考试->添加学习记录
     * @param $type 类型：1：推送、2：指派
     * @param $push_user_id 推送人id
     * @param $exam_id 考试id
     * @param $uid_list 推送对象id列表
     */
    public function addByPushExam($type, $push_user_id, $exam_id, $uid_list)
    {
        $exam = LnExamination::findOne($exam_id);

        $user = FwUser::findOne($push_user_id);

        $saveList = array();

        foreach ($uid_list as $uid) {
            $model = new LnRecord();
            $model->user_id = $uid;
            $model->object_id = $exam->kid;
            $model->object_type = LnRecord::OBJECT_TYPE_EXAM;
            $model->record_category = LnRecord::RECORD_CATEGORY_EXAM;
            $model->learning_verb = ($type == 1 ? '推送' : '指派');
            $model->learning_acivity = $exam->title;
            $model->learning_result = null;
            $model->record_type = LnRecord::RECORD_TYPE_PROCESS;
            $model->data_from = LnRecord::DATA_FROM_LOCAL;
            $model->record_format = '1';
            $model->push_user_id = $push_user_id;
            $model->push_user_name = $user->real_name;
            $saveList[] = $model;
        }
        BaseActiveRecord::batchInsertSqlArray($saveList);
    }

    /**
     * 推送/指派调查->添加学习记录
     * @param $type 类型：1：推送、2：指派
     * @param $push_user_id 推送人id
     * @param $survey_id 调查id
     * @param $uid_list 推送对象id列表
     */
    public function addByPushSurvey($type, $push_user_id, $survey_id, $uid_list)
    {
        $investigation = LnInvestigation::findOne($survey_id);

        $user = FwUser::findOne($push_user_id);

        $saveList = array();

        foreach ($uid_list as $uid) {
            $model = new LnRecord();
            $model->user_id = $uid;
            $model->object_id = $investigation->kid;
            $model->object_type = LnRecord::OBJECT_TYPE_SURVEY;
            $model->record_category = LnRecord::RECORD_CATEGORY_SURVEY;
            $model->learning_verb = ($type == 1 ? '推送' : '指派');
            $model->learning_acivity = $investigation->title;
            $model->learning_result = null;
            $model->record_type = LnRecord::RECORD_TYPE_PROCESS;
            $model->data_from = LnRecord::DATA_FROM_LOCAL;
            $model->record_format = '1';
            $model->push_user_id = $push_user_id;
            $model->push_user_name = $user->real_name;
            $saveList[] = $model;
        }
        BaseActiveRecord::batchInsertSqlArray($saveList);
    }

    /**
     * 完成考试->添加学习记录
     * @param $user_id 用户id
     * @param $exam_id 考试id
     * @param $result 学习结果：得分
     */
    public function addByCompletedExam($user_id, $exam_id, $result)
    {
        $exam = LnExamination::findOne($exam_id);

        if (!empty($exam)) {
            $model = new LnRecord();
            $model->user_id = $user_id;
            $model->object_id = $exam_id;
            $model->object_type = LnRecord::OBJECT_TYPE_EXAM;
            $model->record_category = LnRecord::RECORD_CATEGORY_EXAM;
            $model->learning_verb = '完成';
            $model->learning_acivity = $exam->title;
            $model->learning_result = (string)$result;
            $model->record_type = LnRecord::RECORD_TYPE_PROCESS;
            $model->data_from = LnRecord::DATA_FROM_LOCAL;
            $model->record_format = '2';
            $model->save();
        }
    }

    /**
     * 完成调查->添加学习记录
     * @param $user_id 用户id
     * @param $survey_id 调查id
     * @param $result 学习结果：得分
     */
    public function addByCompletedSurvey($user_id, $survey_id)
    {
        $survey = LnInvestigation::findOne($survey_id);

        if (!empty($survey)) {
            $model = new LnRecord();
            $model->user_id = $user_id;
            $model->object_id = $survey_id;
            $model->object_type = LnRecord::OBJECT_TYPE_SURVEY;
            $model->record_category = LnRecord::RECORD_CATEGORY_SURVEY;
            $model->learning_verb = '完成';
            $model->learning_acivity = $survey->title;
//            $model->learning_result = $result;
            $model->record_type = LnRecord::RECORD_TYPE_PROCESS;
            $model->data_from = LnRecord::DATA_FROM_LOCAL;
            $model->record_format = '2';
            $model->save();
        }
    }

    /**
     * 完成考试->更新学习记录
     * @param string $user_id 用户id
     * @param string $exam_id 考试id
     * @param string $result 学习结果：得分
     * @return bool
     */
    public function updateByCompletedExam($user_id, $exam_id, $result)
    {
        $model = LnRecord::findOne(['user_id' => $user_id, 'object_id' => $exam_id, 'object_type' => LnRecord::OBJECT_TYPE_EXAM, 'learning_verb' => '完成']);

        if (!empty($model)) {
            $model->learning_result = (string)$result;
            return $model->save();
        }
        return false;
    }
}