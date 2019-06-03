<?php

namespace  common\models\learning;

use Yii;

use common\base\BaseActiveRecord;
/**
 * This is the model class for table "{{%ln_investigation_result}}".
 *
 * @property string $kid
 * @property string $investigation_question_id
 * @property string $investigation_id
 * @property string $question_title
 * @property string $question_type
 * @property string $question_description
 * @property string $option_title
 * @property string $option_result
 * @property string $option_description
 * @property string $investigation_option_id
 * @property string $course_id
 * @property string $course_reg_id
 * @property string $user_id
 * @property string $mod_id
 * @property string $mod_res_id
 * @property string $courseactivity_id
 * @property string $component_id
 * @property string $investigation_version
 * @property string $question_version
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
 * @property LnInvestigation $lnInvestigation
 */
class LnInvestigationResult extends BaseActiveRecord
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
        return '{{%ln_investigation_result}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'investigation_question_id', 'investigation_id', 'question_title',  'user_id'], 'required'],
            [['question_description', 'option_description'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'investigation_question_id', 'investigation_id', 'investigation_option_id', 'course_id', 'course_reg_id', 'user_id', 'mod_id', 'mod_res_id', 'courseactivity_id', 'component_id', 'investigation_version', 'question_version', 'option_version', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['question_title', 'option_result', 'option_title'], 'string'],
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
            'investigation_question_id' => Yii::t('common', 'investigation_question_id'),
            'investigation_id' =>  Yii::t('common', 'investigation_id'),
            'question_title' => Yii::t('common', 'question_title'),
            'question_type' => Yii::t('common', 'question_type'),
            'question_description' =>Yii::t('common', 'question_description'),
            'option_title' => Yii::t('common', 'option_title'),
            'option_result' => Yii::t('common', 'option_result'),
            'option_description' => Yii::t('common', 'option_description'),
            'investigation_option_id' => Yii::t('common', 'investigation_option_id'),
            'course_id' => Yii::t('common', 'course_id'),
            'course_reg_id' =>Yii::t('common', 'course_reg_id'),
            'user_id' => Yii::t('common', 'user_id'),
            'mod_id' => Yii::t('common', 'mod_id'),
            'mod_res_id' => Yii::t('common', 'mod_res_id'),
            'courseactivity_id' =>Yii::t('common', 'courseactivity_id'),
            'component_id' => Yii::t('common', 'component_id'),
            'investigation_version' => Yii::t('common', 'investigation_version'),
            'question_version' =>Yii::t('common', 'question_version'),
            'option_version' => Yii::t('common', 'option_version'),
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnInvestigation()
    {
        return $this->hasOne(LnInvestigation::className(), ['kid' => 'investigation_id'])
           ->onCondition([LnInvestigation::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
