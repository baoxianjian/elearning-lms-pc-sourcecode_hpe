<?php

namespace common\models\learning;

use common\models\framework\FwUser;
use Yii;
use \common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ln_res_complete}}".
 *
 * @property string $kid
 * @property string $course_complete_id
 * @property string $course_reg_id
 * @property string $user_id
 * @property string $course_id
 * @property string $mod_id
 * @property string $mod_res_id
 * @property string $courseware_id
 * @property string $courseactivity_id
 * @property string $component_id
 * @property string $resource_type
 * @property string $is_passed
 * @property string $complete_grade
 * @property string $complete_score
 * @property string $score_before
 * @property string $score_after
 * @property string $complete_status
 * @property string $complete_method
 * @property string $complete_type
 * @property string $course_version
 * @property string $resource_version
 * @property integer $start_at
 * @property integer $end_at
 * @property integer $last_record_at
 * @property integer $learning_duration
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnCourseComplete $lnCourseComplete
 */
class LnResComplete extends BaseActiveRecord
{
    const RESOURCE_TYPE_COURSEWARE = '0';
    const RESOURCE_TYPE_COURSEACTIVITY = '1';

    const COMPLETE_STATUS_NOTSTART = '0';
    const COMPLETE_STATUS_DOING = '1';
    const COMPLETE_STATUS_DONE = '2';

    const COMPLETE_TYPE_PROCESS = '0';
    const COMPLETE_TYPE_FINAL = '1';
    const COMPLETE_TYPE_BACKUP = '2';

    const IS_PASSED_NO = "0";
    const IS_PASSED_YES = "1";

    const COMPLETE_METHOD_MASTER = "0";//掌握通过
    const COMPLETE_METHOD_COMPLETE = "1";//完成通过

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_res_complete}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['course_complete_id', 'user_id'], 'required'],
            [['created_at', 'updated_at', 'start_at', 'end_at','last_record_at', 'learning_duration'], 'integer'],
            [['kid', 'course_complete_id', 'user_id', 'course_id', 'mod_id',
                'course_reg_id','mod_res_id','courseware_id','courseactivity_id','component_id',
                'course_version','resource_version',
                'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],
            [['complete_grade','complete_score','score_before','score_after'], 'number'],

            [['resource_type'], 'string', 'max' => 1],
            [['resource_type'], 'in', 'range' => [self::RESOURCE_TYPE_COURSEWARE, self::RESOURCE_TYPE_COURSEACTIVITY]],
            [['resource_type'], 'default', 'value'=> self::RESOURCE_TYPE_COURSEWARE],

            [['is_passed'], 'string', 'max' => 1],
            [['is_passed'], 'in', 'range' => [self::IS_PASSED_NO, self::IS_PASSED_YES]],
            [['is_passed'], 'default', 'value'=> self::IS_PASSED_NO],

            [['complete_status'], 'string', 'max' => 1],
            [['complete_status'], 'in', 'range' => [self::COMPLETE_STATUS_NOTSTART, self::COMPLETE_STATUS_DOING, self::COMPLETE_STATUS_DONE]],
            [['complete_status'], 'default', 'value'=> self::COMPLETE_STATUS_NOTSTART],

            [['complete_type'], 'string', 'max' => 1],
            [['complete_type'], 'in', 'range' => [self::COMPLETE_TYPE_PROCESS, self::COMPLETE_TYPE_FINAL, self::COMPLETE_TYPE_BACKUP]],
            [['complete_type'], 'default', 'value'=> self::COMPLETE_TYPE_PROCESS],

            [['complete_method'], 'in', 'range' => [self::COMPLETE_METHOD_COMPLETE, self::COMPLETE_METHOD_MASTER]],
            [['complete_method'], 'default', 'value'=> self::COMPLETE_METHOD_COMPLETE],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

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
            'kid' => Yii::t('common', 'kid'),
            'course_complete_id' => Yii::t('common', 'course_complete_id'),
            'user_id' => Yii::t('common', 'user_id'),
            'course_id' => Yii::t('common', 'course_id'),
            'course_reg_id' => Yii::t('common', 'course_reg_id'),
            'mod_res_id' => Yii::t('common', 'mod_res_id'),
            'courseware_id' => Yii::t('common', 'courseware_id'),
            'courseactivity_id' => Yii::t('common', 'courseactivity_id'),
            'component_id' => Yii::t('common', 'component_id'),
            'mod_id' => Yii::t('common', 'mod_id'),
            'resource_type' => Yii::t('common', 'resource_type'),
            'is_passed' => Yii::t('common', 'is_passed'),
            'complete_grade' => Yii::t('common', 'complete_grade'),
            'complete_score' => Yii::t('common', 'complete_score'),
            'score_before' => Yii::t('common', 'score_before'),
            'score_after' => Yii::t('common', 'score_after'),
            'complete_type' => Yii::t('common', 'complete_type'),
            'complete_method' => Yii::t('common', 'complete_method'),
            'course_version' => Yii::t('common', 'course_version'),
            'resource_version' => Yii::t('common', 'resource_version'),
            'complete_status' => Yii::t('common', 'complete_status'),
            'start_at' => Yii::t('common', 'start_at'),
            'end_at' => Yii::t('common', 'end_at'),
            'last_record_at' => Yii::t('common', 'last_record_at'),
            'learning_duration' => Yii::t('common', 'learning_duration'),
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
    public function getLnCourseComplete()
    {
        return $this->hasOne(LnCourseComplete::className(), ['kid' => 'course_complete_id'])
            ->onCondition([LnCourseComplete::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    public function getCompleteStatusText()
    {
        if ($this->complete_status === self::COMPLETE_STATUS_NOTSTART) {
            return Yii::t('frontend', 'complete_status_nostart');
        } elseif ($this->complete_status === self::COMPLETE_STATUS_DOING) {
            return Yii::t('frontend', 'complete_status_doing');
        } elseif ($this->complete_status === self::COMPLETE_STATUS_DONE) {
            return Yii::t('frontend', 'complete_status_done');
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwUser()
    {
        return $this->hasOne(FwUser::className(), ['kid' => 'user_id'])
            ->onCondition([FwUser::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnModRes()
    {
        return $this->hasOne(LnModRes::className(), ['kid' => 'mod_res_id'])
            ->onCondition([LnModRes::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
