<?php

namespace common\models\learning;

use common\models\framework\FwUser;
use Yii;
use \common\base\BaseActiveRecord;
use yii\caching\DbDependency;

/**
 * This is the model class for table "{{%ln_examination}}".
 *
 * @property string $kid
 * @property string $examination_paper_copy_id
 * @property string $category_id
 * @property string $examination_paper_id
 * @property string $exam_quest_category_id
 * @property string $company_id
 * @property string $title
 * @property string $code
 * @property string $examination_mode
 * @property string $question_from
 * @property string $description
 * @property string $pre_description
 * @property string $after_description
 * @property integer $start_at
 * @property integer $end_at
 * @property integer $limit_time
 * @property string $random_mode
 * @property string $question_disorder
 * @property string $option_disorder
 * @property string $result_output_type
 * @property string $answer_view
 * @property integer $limit_attempt_number
 * @property string $attempt_strategy
 * @property integer $random_number
 * @property integer $each_page_number
 * @property string $pass_grade
 * @property string $examination_range
 * @property string $release_status
 * @property string $examination_version
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnExaminationPaperCopy $lnExaminationPaperCopy
 * @property LnExaminationCategory $lnExaminationCategory
 * @property LnExaminationPaperUser[] $lnExaminationPaperUsers
 */
class LnExamination extends BaseActiveRecord
{
    public $modResId = null;

    const EXAMINATION_MODE_TEST = '0'; //测试模式
    const EXAMINATION_MODE_EXERCISE = '1';//练习模式

    const EXAMINATION_RANGE_SELF = '0'; //独立使用
    const EXAMINATION_RANGE_COURSE = '1';//课程内部

    const QUESTION_DISORDER_NO = '0';//题目乱序 否
    const QUESTION_DISORDER_YES = '1';//题目乱序 是

    const OPTIOIN_DISORDER_NO = '0';//选项乱序 否
    const OPTIOIN_DISORDER_YES = '1';//选项乱序 是

    const RESULT_OUTPUT_TYPE_NO = '0';//阅卷类型 自动阅卷
    const RESULT_OUTPUT_TYPE_YES = '1';//阅卷类型 人工阅卷

    const ANSWER_VIEW_NO = '0';//答案解析 不允许查看
    const ANSWER_VIEW_YES = '1';//答案解析 允许查看

    const ATTEMPT_STRATEGY_TOP = '0';//尝试策略 最高分尝试
    const ATTEMPT_STRATEGY_LAST = '1';//尝试策略 最后一次尝试
    const ATTEMPT_STRATEGY_AVG = '2';//尝试策略 平均分尝试
    const ATTEMPT_STRATEGY_FIRST = '3';//尝试策略 第一次尝试

    const RELEASE_STATUS_NO = '0';//发布状态 未发布
    const RELEASE_STATUS_YES = '1';//发布状态 已发布
    const RELEASE_STATUS_END = '2';//发布状态 已结束

    const RANDOM_MODE_NO = '0'; //随机模式；0：否，1：是
    const RANDOM_MODE_YES = '1';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_examination}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'company_id', 'title', 'code'], 'required'],
            [['description', 'pre_description', 'after_description'], 'string'],
            [['start_at', 'end_at', 'limit_time', 'limit_attempt_number', 'random_number', 'each_page_number', 'version', 'created_at', 'updated_at'], 'integer'],
            [['pass_grade'], 'number'],
            [['kid', 'examination_paper_copy_id', 'category_id', 'examination_paper_id', 'exam_quest_category_id', 'company_id', 'code', 'examination_version', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 500],
            [['examination_mode', 'random_mode', 'question_from', 'question_disorder', 'option_disorder', 'result_output_type', 'answer_view', 'attempt_strategy', 'examination_range', 'release_status', 'is_deleted'], 'string', 'max' => 1],
            [['created_from','updated_from'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'kid'),
            'examination_paper_copy_id' => Yii::t('common', 'examination_paper_copy_id'),
            'category_id' => Yii::t('common', 'category_id'),
            'examination_paper_id' => Yii::t('common', 'examination_paper_id'),
            'exam_quest_category_id' => Yii::t('common', 'exam_quest_category_id'),
            'company_id' => Yii::t('common', 'company_id'),
            'title' => Yii::t('common', 'title'),
            'code' => Yii::t('common', 'code'),
            'examination_mode' => Yii::t('common', 'examination_mode'),
            'question_from' => Yii::t('common', 'question_from'),
            'description' => Yii::t('common', 'description'),
            'pre_description' => Yii::t('common', 'pre_description'),
            'after_description' => Yii::t('common', 'after_description'),
            'start_at' => Yii::t('common', 'start_at'),
            'end_at' => Yii::t('common', 'end_at'),
            'limit_time' => Yii::t('common', 'limit_time'),
            'random_mode' => Yii::t('common', 'random_mode'),
            'question_disorder' => Yii::t('common', 'question_disorder'),
            'option_disorder' => Yii::t('common', 'option_disorder'),
            'result_output_type' => Yii::t('common', 'result_output_type'),
            'answer_view' => Yii::t('common', 'answer_view'),
            'limit_attempt_number' => Yii::t('common', 'limit_attempt_number'),
            'attempt_strategy' => Yii::t('common', 'attempt_strategy'),
            'random_number' => Yii::t('common', 'random_number'),
            'each_page_number' => Yii::t('common', 'each_page_number'),
            'pass_grade' => Yii::t('common', 'pass_grade'),
            'examination_range' => Yii::t('common', 'examination_range'),
            'release_status' => Yii::t('common', 'release_status'),
            'examination_version' => Yii::t('common', 'examination_version'),
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
    public function getLnExaminationPaperCopy()
    {
        return $this->hasOne(LnExaminationPaperCopy::className(), ['kid' => 'examination_paper_copy_id'])
            ->onCondition([LnExaminationPaperCopy::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnExaminationCategory()
    {
        return $this->hasOne(LnExaminationCategory::className(), ['kid' => 'category_id'])
            ->onCondition([LnExaminationCategory::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnExaminationPaperUsers()
    {
        return $this->hasMany(LnExaminationPaperUser::className(), ['examination_id' => 'kid'])
            ->onCondition([LnExaminationPaperUser::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /*获取创建者名称*/
    public function getExaminationCreateBy($created_by = null){
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

    public function getPaperQuestionNumber(){
        $paper_id = $this->examination_paper_copy_id;
        $findOne = LnExaminationPaperCopy::findOne($paper_id,false);
        if ($findOne->kid){
            return $findOne->examination_question_number;
        }else{
            return 0;
        }
    }
}
