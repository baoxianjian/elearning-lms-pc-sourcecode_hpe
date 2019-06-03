<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_course_certification}}".
 *
 * @property string $kid
 * @property string $certification_id
 * @property string $course_id
 * @property string $get_condition
 * @property string $certification_price
 * @property string $status
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
 * @property LnCertification $lnCertification
 * @property LnCourse $course
 */
class LnCourseCertification extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_course_certification}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['certification_id', 'course_id', 'start_at'], 'required'],
            [['start_at', 'end_at', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'certification_id', 'course_id', 'get_condition', 'certification_price', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

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
            'certification_id' => Yii::t('common', 'certification_id'),
            'course_id' => Yii::t('common', 'course_id'),
            'get_condition' => Yii::t('common', 'get_condition'),
            'certification_price' => Yii::t('common', 'certification_price'),
            'status' => Yii::t('common', 'status'),
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
    public function getLnCertification()
    {
        return $this->hasOne(LnCertification::className(), ['kid' => 'certification_id'])
            ->onCondition([LnCertification::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
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
     * 添加课程证书关系
     */
    public function addRelation(LnCourse $course, $certification_id){
        if (empty($certification_id)) return ;
        $result = $this->findOne(['course_id'=>$course->kid, 'certification_id'=>$certification_id],false);
        $model = $result ? LnCourseCertification::findOne($result->kid) : $model = new LnCourseCertification();
        $model->status = self::STATUS_FLAG_NORMAL;
        $model->start_at = $course->start_time;
        $model->end_at = $course->end_time;
        if ($result){
            $model->update();
        }else{
            $model->course_id = $course->kid;
            $model->certification_id = $certification_id;
            $model->save();
        }
    }
    /**
     * 停用课程证书关系
     */
    public function stopRelation($courseId){
        $attributes = ['status'=>self::STATUS_FLAG_STOP];
        $condition = "course_id=:course_id";
        $param = [
            ':course_id'=>$courseId
        ];
        $this->updateAll($attributes,$condition,$param);
    }

    /*获取课程证书图片 */
    public function getTemplatesUrl($courseId){
        $courseCertification = LnCourseCertification::findOne(['course_id'=>$courseId,'status'=>LnCourseCertification::STATUS_FLAG_NORMAL]);
        if ($courseCertification){
            $certification = LnCertification::findOne($courseCertification->certification_id);
            //$certificationTemp = LnCertificationTemplate::findOne($certification->certification_template_id);
            return $certification->file_path ? $certification->file_path . "preview.png" : "";
        }else{
            return false;
        }
    }
}
