<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_exam_paper_quest_copy}}".
 *
 * @property string $kid
 * @property string $examination_paper_copy_id
 * @property string $examination_question_copy_id
 * @property string $default_score
 * @property string $relation_type
 * @property integer $sequence_number
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
 * @property LnExamQuestionCopy $lnExamQuestionCopy
 * @property LnExaminationPaperCopy $lnExaminationPaperCopy
 */
class LnExamPaperQuestCopy extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_exam_paper_quest_copy}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['examination_paper_copy_id', 'sequence_number', 'start_at'], 'required'],
            [['default_score'], 'number'],
            [['sequence_number', 'start_at', 'end_at', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'examination_paper_copy_id', 'examination_question_copy_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],
            [['relation_type', 'status', 'is_deleted'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => 'Kid',
            'examination_paper_copy_id' => 'Examination Paper Copy ID',
            'examination_question_copy_id' => 'Examination Question Copy ID',
            'default_score' => 'Default Score',
            'relation_type' => 'Relation Type',
            'sequence_number' => 'Sequence Number',
            'status' => 'Status',
            'start_at' => 'Start At',
            'end_at' => 'End At',
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
    public function getLnExamQuestionCopy()
    {
        return $this->hasOne(LnExamQuestionCopy::className(), ['kid' => 'examination_question_copy_id'])
            ->onCondition([LnExamQuestionCopy::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnExaminationPaperCopy()
    {
        return $this->hasOne(LnExaminationPaperCopy::className(), ['kid' => 'examination_paper_copy_id'])
            ->onCondition([LnExaminationPaperCopy::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
