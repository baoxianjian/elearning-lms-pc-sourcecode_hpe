<?php

namespace common\models\learning;

use common\models\framework\FwUser;
use Yii;
use \common\base\BaseActiveRecord;
use yii\caching\DbDependency;


/**
 * This is the model class for table "{{%ln_examination_question}}".
 *
 * @property string $kid
 * @property string $category_id
 * @property string $company_id
 * @property string $title
 * @property string $code
 * @property string $examination_question_type
 * @property string $result_output_type
 * @property string $is_allow_change_score
 * @property string $description
 * @property string $answer
 * @property string $examination_question_level
 * @property string $default_score
 * @property string $question_version
 * @property integer $sequence_number
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnExamPaperQuestion[] $lnExamPaperQuestions
 * @property LnExamQuestionCategory $lnExamQuestionCategory
 * @property LnExamQuestionOption[] $lnExamQuestionOptions
 */
class LnExaminationQuestion extends BaseActiveRecord
{
    const EXAMINATION_QUESTION_TYPE_RADIO = '0';//单选题
    const EXAMINATION_QUESTION_TYPE_CHECKBOX = '1';//多选题
    const EXAMINATION_QUESTION_TYPE_INPUT = '2';//填空题
    const EXAMINATION_QUESTION_TYPE_JUDGE = '3';//判断题
    const EXAMINATION_QUESTION_TYPE_QA = '4';//问答题

    const RESULT_OUTPUT_TYPE_AUTO = '0';//自动阅卷
    const RESULT_OUTPUT_TYPE_MANUAL = '1';//人工阅卷

    const EXAMINATION_QUESTION_LEVEL_EASY = 'easy';//容易
    const EXAMINATION_QUESTION_LEVEL_MEDIUM = 'intermediate';//中等
    const EXAMINATION_QUESTION_LEVEL_DIFFICULT = 'hard';//困难
    
    const IS_ALLOW_CHANGE_SCORE_NO = '0';//0：不允许，
    const IS_ALLOW_CHANGE_SCORE_YES = '1';//1：允许'



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_examination_question}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'company_id', 'title', 'code', 'examination_question_level'], 'required'],
            [['description', 'answer'], 'string'],
            [['default_score','sequence_number'], 'number'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'category_id', 'company_id', 'code', 'examination_question_level', 'question_version', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 1500],
            [['created_from','updated_from'], 'string', 'max' => 50],
            [['examination_question_type', 'result_output_type', 'is_allow_change_score', 'is_deleted'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'kid'),
            'category_id' => Yii::t('common', 'category_id'),
            'company_id' => Yii::t('common', 'company_id'),
            'title' => Yii::t('common', 'title'),
            'code' => Yii::t('common', 'code'),
            'examination_question_type' => Yii::t('common', 'examination_question_type'),
            'result_output_type' => Yii::t('common', 'result_output_type'),
            'is_allow_change_score' => Yii::t('common', 'is_allow_change_score'),
            'description' => Yii::t('common', 'description'),
            'answer' => Yii::t('common', 'answer'),
            'examination_question_level' => Yii::t('common', 'examination_question_level'),
            'default_score' =>  Yii::t('common', 'default_score'),
            'question_version' => Yii::t('common', 'question_version'),
            'sequence_number' => Yii::t('common', 'sequence_number'),
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
    public function getLnExamPaperQuestions()
    {
        return $this->hasMany(LnExamPaperQuestion::className(), ['examination_question_id' => 'kid'])
            ->onCondition([LnExamPaperQuestion::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnExamQuestionCategory()
    {
        return $this->hasOne(LnExamQuestionCategory::className(), ['kid' => 'category_id'])
            ->onCondition([LnExamQuestionCategory::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnExamQuestionOptions()
    {
        return $this->hasMany(LnExamQuestionOption::className(), ['examination_question_id' => 'kid'])
            ->onCondition([LnExamQuestionOption::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /*获取分类名称*/
    public function getExamQuestionCategoryName($examination_question_type = null){
        if (is_null($examination_question_type)){
            $examination_question_type = $this->examination_question_type;
        }
        if ($examination_question_type == self::EXAMINATION_QUESTION_TYPE_RADIO){
            return Yii::t('frontend', 'exam_pq_danxuanti');
        }else if ($examination_question_type == self::EXAMINATION_QUESTION_TYPE_CHECKBOX){
            return Yii::t('frontend', 'exam_pq_duoxuanti');
        }else if ($examination_question_type == self::EXAMINATION_QUESTION_TYPE_INPUT){
            return Yii::t('frontend', 'exam_pq_diankongti');
        }else if ($examination_question_type == self::EXAMINATION_QUESTION_TYPE_JUDGE){
            return Yii::t('frontend', 'exam_pq_panduanti');
        }else if ($examination_question_type == self::EXAMINATION_QUESTION_TYPE_QA){
            return Yii::t('frontend', 'exam_pq_wendati');
        }
    }

    /*获取创建者名称*/
    public function getExamQuestionCreateBy($created_by=null){
        if (empty($created_by)){
            $created_by = $this->created_by;
        }
        $user = FwUser::findOne($created_by);
        if($user){
            $name = $user->getDisplayName();
            return $name;
        }
        else
        {
            return "";
        }
    }
    
    /**
     * 根据中文难度级别返回字符
     */
    public function getExamLevel($levelZh){
    	if (empty($levelZh)) return ;
        $hard = Yii::t('frontend', 'exam_hard');
        $middle = Yii::t('frontend', 'exam_middle');
        $ease = Yii::t('frontend', 'exam_ease');
    	if ($levelZh == $hard){
    		$level = LnExaminationQuestion::EXAMINATION_QUESTION_LEVEL_DIFFICULT;
    	}else if ($levelZh == $middle){
    		$level = LnExaminationQuestion::EXAMINATION_QUESTION_LEVEL_MEDIUM;
    	}else if ($levelZh == $ease){
    		$level = LnExaminationQuestion::EXAMINATION_QUESTION_LEVEL_EASY;
    	}
    	return $level;
    }
}
