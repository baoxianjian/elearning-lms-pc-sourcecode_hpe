<?php

namespace common\models\learning;

use Yii;
use \common\base\BaseActiveRecord;
use yii\caching\DbDependency;

/**
 * This is the model class for table "{{%ln_exam_question_user}}".
 *
 * @property string $kid
 * @property string $examination_paper_user_id
 * @property string $company_id
 * @property string $user_id
 * @property string $examination_id
 * @property string $course_id
 * @property string $course_reg_id
 * @property string $mod_id
 * @property string $mod_res_id
 * @property string $courseactivity_id
 * @property string $component_id
 * @property integer $attempt
 * @property string $title
 * @property string $code
 * @property string $examination_question_type
 * @property string $result_output_type
 * @property string $description
 * @property string $examination_question_level
 * @property string $default_score
 * @property string $examination_question_score
 * @property integer $sequence_number
 * @property string $submit_status
 * @property string $examination_version
 * @property string $paper_version
 * @property string $question_version
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnExamQuestOptionUser[] $lnExamQuestOptionUsers
 * @property LnExaminationPaperUser $lnExaminationPaperUser
 */
class LnExamQuestionUser extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_exam_question_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['examination_paper_user_id', 'company_id', 'user_id', 'examination_id', 'attempt', 'title', 'code', 'examination_question_level', 'sequence_number'], 'required'],
            [['attempt', 'sequence_number', 'version', 'created_at', 'updated_at'], 'integer'],
            [['description'], 'string'],
            [['default_score', 'examination_question_score'], 'number'],
            [['kid', 'examination_paper_user_id', 'company_id', 'user_id', 'examination_id', 'course_id', 'course_reg_id', 'mod_id', 'mod_res_id', 'courseactivity_id', 'component_id', 'code', 'examination_question_level', 'examination_version', 'paper_version', 'question_version', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 1500],
            [['created_from','updated_from'], 'string', 'max' => 50],
            [['examination_question_type', 'result_output_type', 'submit_status', 'is_deleted'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => 'Kid',
            'examination_paper_user_id' => 'Examination Paper User ID',
            'company_id' => 'Company ID',
            'user_id' => 'User ID',
            'examination_id' => 'Examination ID',
            'course_id' => 'Course ID',
            'course_reg_id' => 'Course Reg ID',
            'mod_id' => 'Mod ID',
            'mod_res_id' => 'Mod Res ID',
            'courseactivity_id' => 'Courseactivity ID',
            'component_id' => 'Component ID',
            'attempt' => 'Attempt',
            'title' => 'Title',
            'code' => 'Code',
            'examination_question_type' => 'Examination Question Type',
            'result_output_type' => 'Result Output Type',
            'description' => 'Description',
            'examination_question_level' => 'Examination Question Level',
            'default_score' => 'Default Score',
            'examination_question_score' => 'Examination Question Score',
            'sequence_number' => 'Sequence Number',
            'submit_status' => 'Submit Status',
            'examination_version' => 'Examination Version',
            'paper_version' => 'Paper Version',
            'question_version' => 'Question Version',
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
    public function getLnExamQuestOptionUsers()
    {
        return $this->hasMany(LnExamQuestOptionUser::className(), ['examination_question_user_id' => 'kid'])
            ->onCondition([LnExamQuestOptionUser::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnExaminationPaperUser()
    {
        return $this->hasOne(LnExaminationPaperUser::className(), ['kid' => 'examination_paper_user_id'])
            ->onCondition([LnExaminationPaperUser::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
