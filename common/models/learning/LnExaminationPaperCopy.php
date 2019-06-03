<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_examination_paper_copy}}".
 *
 * @property string $kid
 * @property string $category_id
 * @property string $examination_paper_id
 * @property string $exam_quest_category_id
 * @property string $company_id
 * @property string $question_from
 * @property string $title
 * @property string $code
 * @property string $description
 * @property string $default_total_score
 * @property string $result_output_type
 * @property string $examination_paper_type
 * @property string $examination_paper_level
 * @property integer $examination_question_number
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
 * @property LnExamination[] $lnExaminations
 * @property LnExaminationPaper $lnExaminationPaper
 * @property LnExamPaperQuestCopy[] $lnExamPaperQuestionCopies
 */
class LnExaminationPaperCopy extends BaseActiveRecord
{

    const QUESTION_FROM_PAPER = '0'; //试题来源 试卷
    const QUESTION_FROM_QUESTION = '1'; //试题来源 试题

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_examination_paper_copy}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'company_id', 'title', 'code', 'examination_paper_level'], 'required'],
            [['description'], 'string'],
            [['default_total_score'], 'number'],
            [['examination_question_number', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'category_id', 'examination_paper_id', 'exam_quest_category_id', 'company_id', 'code', 'examination_paper_level', 'paper_version', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['question_from', 'result_output_type', 'examination_paper_type', 'is_deleted'], 'string', 'max' => 1],
            [['created_from','updated_from'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 500]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => 'Kid',
            'category_id' => 'Category ID',
            'examination_paper_id' => 'Examination Paper ID',
            'exam_quest_category_id' => 'Examination Question Category ID',
            'company_id' => 'Company ID',
            'question_from' => 'Question From',
            'title' => 'Title',
            'code' => 'Code',
            'description' => 'Description',
            'default_total_score' => 'Default Total Score',
            'result_output_type' => 'Result Output Type',
            'examination_paper_type' => 'Examination Paper Type',
            'examination_paper_level' => 'Examination Paper Level',
            'examination_question_number' => 'Examination Question Number',
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
    public function getLnExaminations()
    {
        return $this->hasMany(LnExamination::className(), ['examination_paper_copy_id' => 'kid'])
            ->onCondition([LnExamination::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExaminationPaper()
    {
        return $this->hasOne(LnExaminationPaper::className(), ['kid' => 'examination_paper_id'])
            ->onCondition([LnExaminationPaper::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnExamPaperQuestionCopies()
    {
        return $this->hasMany(LnExamPaperQuestCopy::className(), ['examination_paper_copy_id' => 'kid'])
            ->onCondition([LnExamPaperQuestCopy::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
