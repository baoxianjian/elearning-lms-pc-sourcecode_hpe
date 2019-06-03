<?php

namespace common\models\learning;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ln_exam_paper_question}}".
 *
 * @property string $kid
 * @property string $examination_paper_id
 * @property string $examination_question_id
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
 * @property LnExaminationQuestion $lnExaminationQuestion
 * @property LnExaminationPaper $lnExaminationPaper
 */
class LnExamPaperQuestion extends BaseActiveRecord
{
	
	const RELATION_TYPE_PAPER = '0';//关系分类；0：试题
	const RELATION_TYPE_HR = '1';//1：分页符号'
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_exam_paper_question}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['examination_paper_id', 'sequence_number', 'start_at'], 'required'],
            [['default_score'], 'number'],
            [['sequence_number', 'start_at', 'end_at', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'examination_paper_id', 'examination_question_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
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
            'examination_paper_id' => 'Examination Paper ID',
            'examination_question_id' => 'Examination Question ID',
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
    public function getLnExaminationQuestion()
    {
        return $this->hasOne(LnExaminationQuestion::className(), ['kid' => 'examination_question_id'])
            ->onCondition([LnExaminationQuestion::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnExaminationPaper()
    {
        return $this->hasOne(LnExaminationPaper::className(), ['kid' => 'examination_paper_id'])
            ->onCondition([LnExaminationPaper::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
