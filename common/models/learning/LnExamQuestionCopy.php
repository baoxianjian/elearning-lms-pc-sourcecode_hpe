<?php

namespace common\models\learning;

use Yii;
use \common\base\BaseActiveRecord;
use yii\caching\DbDependency;

/**
 * This is the model class for table "{{%ln_exam_question_copy}}".
 *
 * @property string $kid
 * @property string $category_id
 * @property string $examination_question_id
 * @property string $company_id
 * @property string $title
 * @property string $code
 * @property string $examination_question_type
 * @property string $result_output_type
 * @property string $description
 * @property string $examination_question_level
 * @property string $default_score
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
 * @property LnExamPaperQuestCopy[] $lnExamPaperQuestionCopies
 * @property LnExamQuestOptionCopy[] $lnExamQuestionOptionCopies
 */
class LnExamQuestionCopy extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_exam_question_copy}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'examination_question_id', 'company_id', 'title', 'code', 'examination_question_level'], 'required'],
            [['description'], 'string'],
            [['default_score'], 'number'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'category_id', 'examination_question_id', 'company_id', 'code', 'examination_question_level', 'question_version', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 1500],
            [['created_from','updated_from'], 'string', 'max' => 50],
            [['examination_question_type', 'result_output_type', 'is_deleted'], 'string', 'max' => 1]
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
            'examination_question_id' => 'Examination Question ID',
            'company_id' => 'Company ID',
            'title' => 'Title',
            'code' => 'Code',
            'examination_question_type' => 'Examination Question Type',
            'result_output_type' => 'Result Output Type',
            'description' => 'Description',
            'examination_question_level' => 'Examination Question Level',
            'default_score' => 'Default Score',
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
    public function getLnExamPaperQuestionCopies()
    {
        return $this->hasMany(LnExamPaperQuestCopy::className(), ['examination_question_copy_id' => 'kid'])
            ->onCondition([LnExamPaperQuestCopy::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnExamQuestionOptionCopies()
    {
        return $this->hasMany(LnExamQuestOptionCopy::className(), ['examination_question_copy_id' => 'kid'])
            ->onCondition([LnExamQuestOptionCopy::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }



    /*获取分类名称*/
    public function getExamQuestionCategoryName($examination_question_type = null){
        if (is_null($examination_question_type)){
            $examination_question_type = $this->examination_question_type;
        }
        if ($examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO){
            return '单选题';
        }else if ($examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX){
            return '多选题';
        }else if ($examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_INPUT){
            return '填空题';
        }else if ($examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE){
            return '判断题';
        }else if ($examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_QA){
            return '问答题';
        }
    }
}
