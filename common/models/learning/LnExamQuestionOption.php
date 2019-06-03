<?php

namespace common\models\learning;

use Yii;
use \common\base\BaseActiveRecord;
use yii\caching\DbDependency;

/**
 * This is the model class for table "{{%ln_exam_question_option}}".
 *
 * @property string $kid
 * @property string $examination_question_id
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
 * @property LnExaminationQuestion $lnExaminationQuestion
 */
class LnExamQuestionOption extends BaseActiveRecord
{

    const IS_RIGHT_OPTION_YES = '1';//正确选择
    const IS_RIGHT_OPTION_NO = '0';

    const JUDGE_OPTION_RESULT_RIGHT = '1'; //判断题答案为:正确
    const JUDGE_OPTION_RESULT_WRONG = '0'; //判断题答案为:错误

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_exam_question_option}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['examination_question_id', 'option_title', 'sequence_number'], 'required'],
            [['option_title', 'option_description'], 'string'],
            [['default_score'], 'number'],
            [['sequence_number', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'examination_question_id', 'option_version', 'created_by', 'updated_by'], 'string', 'max' => 50],
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
            'examination_question_id' => 'Examination Question ID',
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
    public function getLnExaminationQuestion()
    {
        return $this->hasOne(LnExaminationQuestion::className(), ['kid' => 'examination_question_id'])
            ->onCondition([LnExaminationQuestion::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    public function getExaminationQuestionOptionVersion($kid=null){
        if (empty($kid)) return date('Ymd') . '001';
        $model = new LnExamQuestionOption();
        $result = $model->findOne($kid);
        $option_version = $result->option_version;
        if (substr($option_version, 0, 8) == date('Ymd')) {
            $last_version = substr($option_version, -3);
            return date('Ymd') . sprintf("%03d", intval($last_version) + 1);
        } else {
            return date('Ymd') . '001';
        }
    }
}
