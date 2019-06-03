<?php

namespace common\models\learning;

use Yii;
use \common\base\BaseActiveRecord;
use yii\caching\DbDependency;

/**
 * This is the model class for table "{{%ln_exam_quest_option_user}}".
 *
 * @property string $kid
 * @property string $examination_question_user_id
 * @property string $option_title
 * @property string $option_description
 * @property string $default_score
 * @property string $is_right_option
 * @property string $option_stand_result
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
 * @property LnExamQuestionUser $lnExamQuestionUser
 */
class LnExamQuestOptionUser extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_exam_quest_option_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['examination_question_user_id', 'option_title', 'sequence_number'], 'required'],
            [['option_title', 'option_description'], 'string'],
            [['default_score'], 'number'],
            [['sequence_number', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'examination_question_user_id', 'option_version', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['is_right_option', 'is_deleted'], 'string', 'max' => 1],
            [['created_from','updated_from'], 'string', 'max' => 50],
            [['option_stand_result'], 'string', 'max' => 500]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => 'Kid',
            'examination_question_user_id' => 'Examination Question User ID',
            'option_title' => 'Option Title',
            'option_description' => 'Option Description',
            'default_score' => 'Default Score',
            'is_right_option' => 'Is Right Option',
            'option_stand_result' => 'Option Stand Result',
            'sequence_number' => 'Sequence Number',
            'option_version' => 'Option Version',
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
    public function getLnExamQuestionUser()
    {
        return $this->hasOne(LnExamQuestionUser::className(), ['kid' => 'examination_question_user_id'])
            ->onCondition([LnExamQuestionUser::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
