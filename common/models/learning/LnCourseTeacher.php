<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use common\services\learning\CourseService;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%ln_course_teacher}}".
 *
 * @property string $kid
 * @property string $course_id
 * @property string $teacher_id
 * @property string $status
 * @property string $teacher_type
 * @property integer $start_at
 * @property integer $end_at
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnCourse $lnCourse
 * @property LnTeacher $lnTeacher
 */
class LnCourseTeacher extends BaseActiveRecord
{
    const TEACHER_TYPE_MAIN = "1";//主讲
    const TEACHER_TYPE_ASSISTANT = "2";//辅讲

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_course_teacher}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['course_id', 'teacher_id', 'start_at'], 'required'],
            [['start_at', 'end_at', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'course_id', 'teacher_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['teacher_type'], 'string', 'max' => 1],
            [['teacher_type'], 'in', 'range' => [self::TEACHER_TYPE_MAIN, self::TEACHER_TYPE_ASSISTANT]],
            [['teacher_type'], 'default', 'value'=> self::TEACHER_TYPE_MAIN],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

            [['is_deleted'], 'string', 'max' => 1],
            [['is_deleted'], 'in', 'range' => [self::DELETE_FLAG_NO, self::DELETE_FLAG_YES]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'kid'),
            'course_id' => Yii::t('common', 'course_id'),
            'teacher_id' => Yii::t('common', 'teacher_id'),
            'status' => Yii::t('common', 'status'),
            'teacher_type' => Yii::t('common', 'teacher_type'),
            'start_at' => Yii::t('common', 'start_at'),
            'end_at' => Yii::t('common', 'end_at'),
            'version' => Yii::t('common', 'version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_from' => Yii::t('common', 'created_from'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'is_deleted' => Yii::t('common', 'is_deleted'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourse()
    {
        return $this->hasOne(LnCourse::className(), ['kid' => 'course_id'])
            ->onCondition([LnCourse::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnTeacher()
    {
        return $this->hasOne(LnTeacher::className(), ['kid' => 'teacher_id'])
            ->onCondition([LnTeacher::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
    /**
     * 查询对应的讲师关系
     * @param $subjectId 主体id
     * @return array|null|[]
     */
    public function GetCourseTeacher($courseId, $field = true)
    {
        $model = new LnCourseTeacher();

        $query = $model->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'status', self::STATUS_FLAG_NORMAL]);

        $result = $query->all();

        if ($result != null) {
            if ($field === true){
                return $result;
            }else{
                if (isset($result) && $result != null) {
                    $teacher = ArrayHelper::map($result, $field, $field);
                    return array_keys($teacher);
                }else{
                    return null;
                }
            }

        } else {
            return null;
        }
    }
    /**
     * 获取课程讲师
     */
    public function getTeacherAll($courseId){
        $courseTeacher = $this->GetCourseTeacher($courseId);
        if (!empty($courseTeacher)) {
            $selectedList = ArrayHelper::map($courseTeacher, 'teacher_id', 'teacher_id');
            $selected_keys = array_keys($selectedList);
            $findAll = LnTeacher::findAll(['kid' => $selected_keys]);
            return $findAll;
        }
        else {
            return null;
        }
    }

    /**
     * 添加课程讲师关系
     */
    public function addRelation(LnCourse $course, $teacher_id){
        if (!is_array($teacher_id)){
            $teacher_id = explode(',', $teacher_id);
        }
        $courseService = new CourseService();
        $courseService->stopCourseTeacher($course->kid);
        if (empty($teacher_id)) return ;

        foreach ($teacher_id as $val){
            $result = $this->findOne(['course_id'=>$course->kid, 'teacher_id'=>$val],false);
            $model = !empty($result->kid) ? $result : $model = new LnCourseTeacher();
            $model->status = self::STATUS_FLAG_NORMAL;
            $model->start_at = $course->start_time;
            $model->end_at = $course->end_time;
            //$teacherModel = LnTeacher::findOne($val,false);
            //$model->teacher_type = $teacherModel->teacher_type;
            $model->teacher_type = LnCourseTeacher::TEACHER_TYPE_MAIN;//默认主讲
            $model->teacher_id = $val;
            if ($model->kid){
                $model->update();
            }else{
                $model->course_id = $course->kid;
                $model->save();
            }
        }
    }
}
