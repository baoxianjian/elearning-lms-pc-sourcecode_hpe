<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_course_question}}".
 *
 * @property string $kid
 * @property string $course_id
 * @property string $module_id
 * @property string $question_id
 * @property string $question_type
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnCourseMods $lnCourseMods
 * @property LnCourse $lnCourse
 */
class LnCourseQuestion extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_course_question}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['question_id', 'question_type'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'course_id', 'module_id', 'question_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['question_type', 'is_deleted'], 'string', 'max' => 1],
            [['created_from','updated_from'], 'string', 'max' => 50],

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
            'course_id' => Yii::t('common', 'course_id'),
            'module_id' => Yii::t('common', 'module_id'),
            'question_id' => Yii::t('common', 'question_id'),
            'question_type' => Yii::t('common', 'question_type'),
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
    public function getLnCourseMods()
    {
        return $this->hasOne(LnCourseMods::className(), ['kid' => 'module_id'])
            ->onCondition([LnCourseMods::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourse()
    {
        return $this->hasOne(LnCourse::className(), ['kid' => 'course_id'])
            ->onCondition([LnCourse::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
