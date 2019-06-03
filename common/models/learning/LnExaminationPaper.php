<?php

namespace common\models\learning;

use Yii;
use common\base\BaseActiveRecord;
use common\models\framework\FwUser;

/**
 * This is the model class for table "{{%ln_examination_paper}}".
 *
 * @property string $kid
 * @property string $category_id
 * @property string $company_id
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
 * @property LnExamPaperCategory $lnExamPaperCategory
 * @property LnExaminationPaperCopy[] $lnExaminationPaperCopies
 * @property LnExamPaperQuestion[] $lnExamPaperQuestions
 */
class LnExaminationPaper extends  BaseActiveRecord
{
    const RELATION_TYPE_QUESTION = '0'; //试题
    const RELATION_TYPE_PAGE = '1'; //分页符

    const RESULT_OUTPUT_TYPE_AUTO = '0';//自动阅卷
    const RESULT_OUTPUT_TYPE_MANUAL = '1';//人工阅卷
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_examination_paper}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'category_id', 'company_id', 'title', 'code', 'examination_paper_level'], 'required'],
            [['description'], 'string'],
            [['default_total_score'], 'number'],
            [['examination_question_number', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'category_id', 'company_id', 'code', 'examination_paper_level', 'paper_version', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 500],
            [['created_from','updated_from'], 'string', 'max' => 50],
            [['result_output_type', 'examination_paper_type', 'is_deleted'], 'string', 'max' => 1]
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
            'company_id' => 'Company ID',
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
    public function getLnExamPaperCategory()
    {
        return $this->hasOne(LnExamPaperCategory::className(), ['kid' => 'category_id'])
            ->onCondition([LnExamPaperCategory::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnExaminationPaperCopies()
    {
        return $this->hasMany(LnExaminationPaperCopy::className(), ['examination_paper_id' => 'kid'])
            ->onCondition([LnExaminationPaperCopy::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnExamPaperQuestions()
    {
        return $this->hasMany(LnExamPaperQuestion::className(), ['examination_paper_id' => 'kid'])
            ->onCondition([LnExamPaperQuestion::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
    
    public function getCreatedBy(){
    	$user = FwUser::findOne($this->created_by);
    	if($user){
            $name = $user->getDisplayName();
            return $name;
    	}
    	else
    	{
    		return "";
    	}
    }
}
