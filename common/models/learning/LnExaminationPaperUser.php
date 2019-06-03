<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_examination_paper_user}}".
 *
 * @property string $kid
 * @property string $examination_id
 * @property string $examination_paper_copy_id
 * @property string $company_id
 * @property string $user_id
 * @property string $course_id
 * @property string $course_reg_id
 * @property string $mod_id
 * @property string $mod_res_id
 * @property string $courseactivity_id
 * @property string $component_id
 * @property integer $attempt
 * @property string $title
 * @property string $code
 * @property string $description
 * @property string $default_total_score
 * @property string $examination_paper_score
 * @property string $result_output_type
 * @property string $examination_paper_level
 * @property integer $examination_question_number
 * @property string $submit_status
 * @property string $examination_version
 * @property string $paper_version
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnExamination $lnExamination
 * @property LnExamQuestionUser[] $lnExamQuestionUsers
 */
class LnExaminationPaperUser extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_examination_paper_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['kid', 'examination_id', 'examination_paper_copy_id', 'company_id', 'user_id', 'attempt', 'title', 'code', 'examination_paper_level', 'created_by', 'created_at'], 'required'],
            [['attempt', 'examination_question_number', 'version', 'created_at', 'updated_at'], 'integer'],
            [['description'], 'string'],
            [['default_total_score', 'examination_paper_score'], 'number'],
            [['kid', 'examination_id', 'examination_paper_copy_id', 'company_id', 'user_id', 'course_id', 'course_reg_id', 'mod_id', 'mod_res_id', 'courseactivity_id', 'component_id', 'code', 'examination_paper_level', 'examination_version', 'paper_version', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 500],
            [['created_from','updated_from'], 'string', 'max' => 50],
            [['result_output_type', 'submit_status', 'is_deleted'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => 'Kid',
            'examination_id' => 'Examination ID',
            'examination_paper_copy_id' => 'Examination Paper Copy ID',
            'company_id' => 'Company ID',
            'user_id' => 'User ID',
            'course_id' => 'Course ID',
            'course_reg_id' => 'Course Reg ID',
            'mod_id' => 'Mod ID',
            'mod_res_id' => 'Mod Res ID',
            'courseactivity_id' => 'Courseactivity ID',
            'component_id' => 'Component ID',
            'attempt' => 'Attempt',
            'title' => 'Title',
            'code' => 'Code',
            'description' => 'Description',
            'default_total_score' => 'Default Total Score',
            'examination_paper_score' => 'Examination Paper Score',
            'result_output_type' => 'Result Output Type',
            'examination_paper_level' => 'Examination Paper Level',
            'examination_question_number' => 'Examination Question Number',
            'submit_status' => 'Submit Status',
            'examination_version' => 'Examination Version',
            'paper_version' => 'Paper Version',
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
    public function getLnExamination()
    {
        return $this->hasOne(LnExamination::className(), ['kid' => 'examination_id'])
            ->onCondition([LnExamination::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnExamQuestionUsers()
    {
        return $this->hasMany(LnExamQuestionUser::className(), ['examination_paper_user_id' => 'kid'])
            ->onCondition([LnExamQuestionUser::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
