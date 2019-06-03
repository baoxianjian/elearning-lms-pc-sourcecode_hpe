<?php

namespace common\models\learning;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ln_investigation_option}}".
 *
 * @property string $kid
 * @property string $investigation_question_id
 * @property string $investigation_id
 * @property string $option_title
 * @property string $option_description
 * @property integer $sequence_number
 * @property string $option_version
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnInvestigationQuestion $lnInvestigationQuestion
 */
class LnInvestigationOption extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_investigation_option}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['investigation_question_id', 'investigation_id', 'option_title', 'sequence_number'], 'required'],
            [['option_description'], 'string'],
            [['sequence_number', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'investigation_question_id', 'investigation_id', 'option_version', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['option_title'], 'string'],
            [['created_from','updated_from'], 'string', 'max' => 50],

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
            'investigation_question_id' =>Yii::t('common', 'investigation_question_id'),
            'investigation_id' =>  Yii::t('common', 'investigation_id'),
            'option_title' =>   Yii::t('common', 'option_title'),
            'option_description' =>  Yii::t('common', 'option_description'),
            'sequence_number' => Yii::t('common', 'sequence_number'),
            'option_version' =>  Yii::t('common', 'option_version'),
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
    public function getLnInvestigationQuestion()
    {
        return $this->hasOne(LnInvestigationQuestion::className(), ['kid' => 'investigation_question_id']) 
             ->onCondition([LnInvestigationQuestion::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
