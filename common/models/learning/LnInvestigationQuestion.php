<?php

namespace common\models\learning;

use Yii;

use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ln_investigation_question}}".
 *
 * @property string $kid
 * @property string $investigation_id
 * @property string $question_title
 * @property string $question_type
 * @property string $question_description
 * @property integer $sequence_number
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
 * @property LnInvestigationOption[] $lnInvestigationOptions
 * @property LnInvestigation $lnInvestigation
 * @property LnInvestigationResult[] $lnInvestigationResults
 */
class LnInvestigationQuestion extends BaseActiveRecord
{
    const QUESTION_TYPE_SINGLE = '0';
    const QUESTION_TYPE_MULTIPLE = '1';
    const QUESTION_TYPE_QA = '2';
    const QUESTION_TYPE_PAGE_SPLIT = '3';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_investigation_question}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'investigation_id', 'question_title', 'sequence_number'], 'required'],
            [['question_description'], 'string'],
            [['sequence_number', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'investigation_id', 'question_version', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['question_title'], 'string'],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['question_type'], 'string', 'max' => 1],
            [['question_type'], 'in', 'range' => [self::QUESTION_TYPE_SINGLE, self::QUESTION_TYPE_MULTIPLE, self::QUESTION_TYPE_QA, self::QUESTION_TYPE_PAGE_SPLIT]],

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
            'kid' =>Yii::t('common', 'kid'),
            'investigation_id' =>  Yii::t('common', 'investigation_id'),
            'question_title' =>  Yii::t('common', 'question_title'),
            'question_type' => Yii::t('common', 'question_type'),
            'question_description' =>  Yii::t('common', 'question_description'),
            'sequence_number' => Yii::t('common', 'sequence_number'),
            'question_version' =>  Yii::t('common', 'question_version'),
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
    public function getLnInvestigationOptions()
    {
        return $this->hasMany(LnInvestigationOption::className(), ['investigation_question_id' => 'kid'])
          ->onCondition([LnInvestigationOption::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnInvestigation()
    {
        return $this->hasOne(LnInvestigation::className(), ['kid' => 'investigation_id'])
           ->onCondition([LnInvestigation::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnInvestigationResults()
    {
        return $this->hasMany(LnInvestigationResult::className(), ['investigation_question_id' => 'kid'])
           ->onCondition([LnInvestigationResult::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
