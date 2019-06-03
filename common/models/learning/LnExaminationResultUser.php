<?php

namespace common\models\learning;

use common\models\framework\FwUser;
use Yii;
use \common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ln_examination_result_user}}".
 *
 * @property string $kid
 * @property string $examination_id
 * @property string $examination_paper_user_id
 * @property string $company_id
 * @property string $user_id
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
 * @property string $examination_score
 * @property string $examination_version
 * @property string $result_type
 * @property string $examination_status
 * @property integer $all_number
 * @property integer $correct_number
 * @property integer $error_number
 * @property string $correct_rate
 * @property integer $start_at
 * @property integer $end_at
 * @property integer $last_record_at
 * @property integer $examination_duration
 * @property integer $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $created_ip
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $updated_ip
 * @property string $is_deleted
 *
 * @property LnExamination $examination
 */
class LnExaminationResultUser extends BaseActiveRecord
{
    const EXAMINATION_STATUS_NOT = '0';//未开始
    const EXAMINATION_STATUS_START = '1';//进行中
    const EXAMINATION_STATUS_END = '2';//已结束

    const RESULT_TYPE_PROCESS = '0';//过程结果
    const RESULT_TYPE_FINALLY = '1';//最终结果

    public $user_real_name;
    public $user_email;
    public $user_mobile;
    public $attempt_strategy;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_examination_result_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['examination_id', 'examination_paper_user_id', 'company_id', 'user_id'], 'required'],
            [['course_attempt_number', 'examination_attempt_number', 'all_number', 'correct_number', 'error_number', 'start_at', 'end_at', 'last_record_at', 'examination_duration', 'version', 'created_at', 'updated_at'], 'integer'],
            [['examination_score', 'correct_rate'], 'number'],
            [['examination_id', 'examination_paper_user_id', 'company_id', 'user_id', 'course_id', 'course_reg_id', 'mod_id', 'mod_res_id', 'courseactivity_id', 'component_id', 'course_complete_id', 'res_complete_id', 'examination_version', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['result_type', 'examination_status', 'is_deleted'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => 'Kid',
            'examination_id' => 'Examination ID',
            'examination_paper_user_id' => 'Examination Paper User ID',
            'company_id' => 'Company ID',
            'user_id' => 'User ID',
            'course_id' => 'Course ID',
            'course_reg_id' => 'Course Reg ID',
            'mod_id' => 'Mod ID',
            'mod_res_id' => 'Mod Res ID',
            'courseactivity_id' => 'Courseactivity ID',
            'component_id' => 'Component ID',
            'course_complete_id' => 'Course Complete ID',
            'res_complete_id' => 'Res Complete ID',
            'course_attempt_number' => 'Course Attempt Number',
            'examination_attempt_number' => 'Examination Attempt Number',
            'examination_score' => 'Examination Score',
            'examination_version' => 'Examination Version',
            'result_type' => 'Result Type',
            'examination_status' => 'Examination Status',
            'all_number' => 'All Number',
            'correct_number' => 'Correct Number',
            'error_number' => 'Error Number',
            'correct_rate' => 'Correct Rate',
            'start_at' => 'Start At',
            'end_at' => 'End At',
            'last_record_at' => 'Last Record At',
            'examination_duration' => 'Examination Duration',
            'version' => 'Version',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'created_from' => 'Created From',
            'created_ip' => 'Created Ip',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
            'updated_from' => 'Updated From',
            'updated_ip' => 'Updated Ip',
            'is_deleted' => 'Is Deleted',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExamination()
    {
        return $this->hasOne(LnExamination::className(), ['kid' => 'examination_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwUser()
    {
        return $this->hasOne(FwUser::className(), ['kid' => 'user_id'])
            ->onCondition([FwUser::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
