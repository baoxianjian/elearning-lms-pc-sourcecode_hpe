<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_mod_res}}".
 *
 * @property string $kid
 * @property string $mod_id
 * @property string $courseware_id
 * @property string $courseactivity_id
 * @property string $component_id
 * @property string $course_id
 * @property string $res_type
 * @property integer $sequence_number
 * @property integer $res_score
 * @property integer $res_time
 * @property string $res_name
 * @property string $score_scale
 * @property string $pass_grade
 * @property string $score_strategy
 * @property string $attempt_strategy
 * @property string $direct_complete_course
 * @property string $is_record_score
 * @property string $complete_rule
 * @property string $publish_status
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnComponent $lnComponent
 * @property LnCourseactivity $lnCourseactivity
 * @property LnCourseware $lnCourseware
 * @property LnCourseMods $lnCourseMods
 */
class LnModRes extends BaseActiveRecord
{
    const RES_TYPE_COURSEWARE = "0";
    const RES_TYPE_COURSEACTIVITY = "1";

    const PUBLIC_STATUS_NO = "0";
    const PUBLIC_STATUS_YES = "1"; 

    const IS_HAVE_SOCRE_SCALE_NO = "0";
    const IS_HAVE_SOCRE_SCALE_YES = "1";

    const DIRECT_COMPLETE_COURSE_NO = "0";
    const DIRECT_COMPLETE_COURSE_YES = "1";

    const SCORE_STRATEGY_HIGHEST = "0";
    const SCORE_STRATEGY_AVERAGE = "1";
    const SCORE_STRATEGY_SUM = "2";
    const SCORE_STRATEGY_OBJECTS = "3";

    const ATTEMPT_STRATEGY_HIGHEST = "0";
    const ATTEMPT_STRATEGY_LAST = "1";
    const ATTEMPT_STRATEGY_AVERAGE = "2";
    const ATTEMPT_STRATEGY_FIRST = "3";

    const COMPLETE_RULE_BROWSE = "0";
    const COMPLETE_RULE_SCORE = "1";
    const COMPLETE_RULE_SUBMIT = "2";

    const SCORE_PERCENT = 100;

    const FINISH_COURSE_ALLYES = "1";

    const IS_RECORD_SCORE_NO = '0';//是否计分： 0 否
    const IS_RECORD_SCORE_YES = '1';//是否计分： 1 是

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_mod_res}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mod_id', 'component_id', 'course_id', 'res_type'], 'required'],
            [['sequence_number', 'created_at', 'updated_at'], 'integer'],
            [['score_scale', 'pass_grade','res_score','res_time'], 'number'],
            [['kid', 'mod_id', 'courseware_id', 'courseactivity_id', 'component_id', 'course_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['res_type', 'score_strategy', 'direct_complete_course', 'is_deleted'], 'string', 'max' => 1],
            [['created_from','updated_from'], 'string', 'max' => 50],
//            [['score_scale'], 'default', 'value'=> 0],
            [['res_name'], 'string', 'max' => 500],

            [['direct_complete_course'], 'in', 'range' => [self::DIRECT_COMPLETE_COURSE_NO, self::DIRECT_COMPLETE_COURSE_YES]],
            [['direct_complete_course'], 'default', 'value' => self::DIRECT_COMPLETE_COURSE_NO],

            [['complete_rule'], 'in', 'range' => [self::COMPLETE_RULE_BROWSE, self::COMPLETE_RULE_SCORE, self::COMPLETE_RULE_SUBMIT]],
            [['complete_rule'], 'default', 'value'=> self::COMPLETE_RULE_SCORE],

            [['is_record_score'], 'in', 'range' => [self::NO, self::YES]],
            [['is_record_score'], 'default', 'value' => self::YES],

            [['publish_status'], 'in', 'range' => [self::NO, self::YES]],
            [['publish_status'], 'default', 'value' => self::NO],

            [['score_strategy'], 'in', 'range' => [self::SCORE_STRATEGY_HIGHEST, self::SCORE_STRATEGY_AVERAGE, self::SCORE_STRATEGY_SUM, self::SCORE_STRATEGY_OBJECTS]],
            [['score_strategy'], 'default', 'value' => self::SCORE_STRATEGY_AVERAGE],

            [['attempt_strategy'], 'in', 'range' => [self::ATTEMPT_STRATEGY_HIGHEST, self::ATTEMPT_STRATEGY_LAST, self::ATTEMPT_STRATEGY_AVERAGE, self::ATTEMPT_STRATEGY_FIRST]],
            [['attempt_strategy'], 'default', 'value' => self::ATTEMPT_STRATEGY_LAST],

            [['res_type'], 'in', 'range' => [self::RES_TYPE_COURSEWARE, self::RES_TYPE_COURSEACTIVITY]],
            [['res_type'], 'default', 'value' => self::RES_TYPE_COURSEWARE],

            [['version'], 'number'],
            [['version'], 'default', 'value' => 1],

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
            'mod_id' => Yii::t('common', 'mod_id'),
            'courseware_id' => Yii::t('common', 'courseware_id'),
            'courseactivity_id' => Yii::t('common', 'courseactivity_id'),
            'component_id' => Yii::t('common', 'component_id'),
            'course_id' => Yii::t('common', 'course_id'),
            'res_type' => Yii::t('common', 'res_type'),
            'is_record_score' => Yii::t('common', 'is_record_score'),
            'complete_rule' => Yii::t('common', 'complete_rule'),
            'sequence_number' => Yii::t('common', 'sequence_number'),
            'res_name' =>  Yii::t('common', 'res_name'),
            'res_score' =>  Yii::t('common', 'res_score'),
            'res_time' =>  Yii::t('common', 'res_time'),
            'score_scale' => Yii::t('common', 'score_scale'),
            'score_strategy' => Yii::t('common', 'score_strategy'),
            'direct_complete_course' => Yii::t('common', 'direct_complete_course'),
            'publish_status' => Yii::t('common', 'publish_status'),
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
    public function getLnComponent()
    {
        return $this->hasOne(LnComponent::className(), ['kid' => 'component_id'])
            ->onCondition([LnComponent::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourseactivity()
    {
        return $this->hasOne(LnCourseactivity::className(), ['kid' => 'courseactivity_id'])
            ->onCondition([LnCourseactivity::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourseware()
    {
        return $this->hasOne(LnCourseware::className(), ['kid' => 'courseware_id'])
            ->onCondition([LnCourseware::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourseMods()
    {
        return $this->hasOne(LnCourseMods::className(), ['kid' => 'mod_id'])
            ->onCondition([LnCourseMods::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /*资源排序号*/
    public function getSequenceNumber($mod_id)
    {
        $last = LnModRes::find()->where(['mod_id' => $mod_id])->count("kid");
        return $last + 1;
    }

    public function getResourceName()
    {
        $resourceName = '';
        if ($this->res_type === self::RES_TYPE_COURSEWARE) {
            $resourceName = LnCourseware::findOne($this->courseware_id)->courseware_name;
        } elseif ($this->res_type === self::RES_TYPE_COURSEACTIVITY) {
            $resourceName = LnCourseactivity::findOne($this->courseactivity_id)->activity_name;
        }
        return $resourceName;
    }

    public function getPublishStatusText()
    {
        if ($this->publish_status === LnModRes::PUBLIC_STATUS_NO) {
            return Yii::t('frontend', 'publish_status_no');
        } elseif ($this->publish_status === LnModRes::PUBLIC_STATUS_YES) {
            return Yii::t('frontend', 'publish_status_yes');
        }
    }
}
