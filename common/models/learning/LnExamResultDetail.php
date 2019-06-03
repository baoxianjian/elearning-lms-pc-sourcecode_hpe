<?php

namespace common\models\learning;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ln_exam_result_detail}}".
 *
 * @property string $kid
 * @property string $examination_question_user_id
 * @property string $examination_paper_user_id
 * @property string $examination_option_user_id
 * @property string $examination_result_process_id
 * @property string $examination_result_final_id
 * @property string $company_id
 * @property string $user_id
 * @property string $examination_id
 * @property string $course_id
 * @property string $course_reg_id
 * @property string $mod_id
 * @property string $mod_res_id
 * @property string $courseactivity_id
 * @property string $component_id
 * @property string $course_complete_id
 * @property string $res_complete_id
 * @property integer $course_attempt_number
 * @property integer $examination_attempt_number
 * @property string $question_title
 * @property string $examination_question_type
 * @property string $question_description
 * @property string $option_title
 * @property string $option_description
 * @property string $option_stand_result
 * @property string $option_result
 * @property string $examination_version
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
 * @property LnExamQuestionUser $examQuestionUser
 */
class LnExamResultDetail extends BaseActiveRecord
{
    const OPTION_RESULT_YES = 'yes';
    const OPTION_RESULT_NO = 'no';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_exam_result_detail}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['examination_question_user_id', 'examination_paper_user_id', 'examination_result_process_id', 'examination_result_final_id', 'company_id', 'user_id', 'examination_id', 'question_title', 'option_title', 'option_result'], 'required'],
            [['course_attempt_number', 'examination_attempt_number', 'version', 'created_at', 'updated_at'], 'integer'],
            [['question_description', 'option_title', 'option_description', 'option_result'], 'string'],
            [['kid', 'examination_question_user_id', 'examination_paper_user_id', 'examination_option_user_id', 'examination_result_process_id', 'examination_result_final_id', 'company_id', 'user_id', 'examination_id', 'course_id', 'course_reg_id', 'mod_id', 'mod_res_id', 'courseactivity_id', 'component_id', 'course_complete_id', 'res_complete_id', 'examination_version', 'question_version', 'option_version', 'created_by', 'created_from', 'updated_by', 'updated_from'], 'string', 'max' => 50],
            [['question_title', 'option_stand_result'], 'string', 'max' => 500],
            [['examination_question_type', 'is_deleted'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => '个人考试明细结果ID',
            'examination_question_user_id' => '个人试题ID',
            'examination_paper_user_id' => '个人试卷ID',
            'examination_option_user_id' => '个人试题选项ID',
            'examination_result_process_id' => '个人考试过程结果ID',
            'examination_result_final_id' => '个人考试最终结果ID',
            'company_id' => '企业ID',
            'user_id' => '用户ID',
            'examination_id' => '考试ID',
            'course_id' => '课程ID',
            'course_reg_id' => '课程注册ID',
            'mod_id' => '模块ID',
            'mod_res_id' => '模块资源ID',
            'courseactivity_id' => '活动ID',
            'component_id' => '组件ID',
            'course_complete_id' => '课程完成ID',
            'res_complete_id' => '资源完成ID',
            'course_attempt_number' => '课程尝试次数',
            'examination_attempt_number' => '考试尝试次数',
            'question_title' => '试题标题',
            'examination_question_type' => '试题类型；0：单选题，1：多选题，2：填空题，3：判断题，4：问答题，5：分页符',
            'question_description' => '试题描述',
            'option_title' => '选项标题',
            'option_description' => '选项描述',
            'option_stand_result' => '选项标准答案；填空题用',
            'option_result' => '选项答案',
            'examination_version' => '考试版本',
            'question_version' => '试题版本',
            'option_version' => '选项版本',
            'version' => '版本号',
            'created_by' => '创建人ID',
            'created_at' => '创建时间',
            'created_from' => '创建来源',
            'updated_by' => '更新人ID',
            'updated_at' => '更新时间',
            'updated_from' => '更新来源',
            'is_deleted' => '删除标记；0：正常，1：已删除',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExaminationQuestionUser()
    {
        return $this->hasOne(LnExamQuestionUser::className(), ['kid' => 'examination_question_user_id']);
    }
}
