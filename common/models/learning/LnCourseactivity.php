<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_courseactivity}}".
 *
 * @property string $kid
 * @property string $course_id
 * @property string $component_id
 * @property string $object_id
 * @property string $object_type
 * @property string $activity_name
 * @property integer $start_at
 * @property integer $end_at
 * @property string $resource_version
 * @property string $is_display_pc
 * @property string $is_display_mobile
 * @property string $is_allow_download
 * @property integer $default_credit
 * @property integer $default_time
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
 * @property LnCourse $lnCourse
 * @property LnModRes[] $lnModRes
 */
class LnCourseactivity extends BaseActiveRecord
{
    const DISPLAY_PC_NO = "0";
    const DISPLAY_PC_YES = "1";

    const DISPLAY_MOBILE_NO = "0";
    const DISPLAY_MOBILE_YES = "1";

    const ALLOW_DOWNLOAD_NO = "0";
    const ALLOW_DOWNLOAD_YES = "1";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_courseactivity}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['course_id', 'component_id', 'activity_name'], 'required'],
            [['start_at', 'end_at', 'default_time', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'course_id', 'component_id','object_id','object_type', 'resource_version', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['activity_name'], 'string', 'max' => 500],
            [['created_from','updated_from'], 'string', 'max' => 50],
            [['default_credit'], 'number'],
            [['is_display_pc'], 'string', 'max' => 1],
            [['is_display_pc'], 'in', 'range' => [self::DISPLAY_PC_NO, self::DISPLAY_PC_YES]],
            [['is_display_pc'], 'default', 'value'=> self::DISPLAY_PC_YES],

            [['is_display_mobile'], 'string', 'max' => 1],
            [['is_display_mobile'], 'in', 'range' => [self::DISPLAY_MOBILE_NO, self::DISPLAY_MOBILE_YES]],
            [['is_display_mobile'], 'default', 'value'=> self::DISPLAY_MOBILE_NO],

            [['is_allow_download'], 'string', 'max' => 1],
            [['is_allow_download'], 'in', 'range' => [self::ALLOW_DOWNLOAD_NO, self::ALLOW_DOWNLOAD_YES]],
            [['is_allow_download'], 'default', 'value'=> self::ALLOW_DOWNLOAD_NO],

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
            'course_id' => Yii::t('common', 'course_id'),
            'component_id' => Yii::t('common', 'component_id'),
            'object_id' => Yii::t('common', 'object_id'),
            'object_type' => Yii::t('common', 'object_type'),
            'activity_name' => Yii::t('common', 'activity_name'),
            'default_credit' => Yii::t('common', 'default_credit'),
            'default_time' => Yii::t('common', 'default_time'),
            'start_at' => Yii::t('common', 'start_at'),
            'end_at' => Yii::t('common', 'end_at'),
            'resource_version' => Yii::t('common', 'resource_version'),
            'is_display_pc' => Yii::t('common', 'is_display_pc'),
            'is_display_mobile' => Yii::t('common', 'is_display_mobile'),
            'is_allow_download' => Yii::t('common', 'is_allow_download'),
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
    public function getLnCourse()
    {
        return $this->hasOne(LnCourse::className(), ['kid' => 'course_id'])
            ->onCondition([LnCourse::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnModRes()
    {
        return $this->hasMany(LnModRes::className(), ['courseactivity_id' => 'kid'])
            ->onCondition([LnModRes::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /*获取活动名称*/
    public function getCoursewareName($courseactivity_id){
        $find = LnCourseactivity::findOne($courseactivity_id,false);
        return $find->activity_name;
    }

    /*
     * 版本号
     * 规则：日期+sprintf("%03d", course_version);
     * @param string $courseId
     * @return string
     */
    public static function getResourceVersion($id="")
    {
        if (empty($id)) return date('Ymd') . '001';
        $lncourseactivity = new LnCourseactivity();
        $result = $lncourseactivity->findOne($id);
        $resource_version = $result->resource_version;
        if (substr($resource_version, 0, 8) == date('Ymd')) {
            $last_version = substr($resource_version, -3);
            return date('Ymd') . sprintf("%03d", intval($last_version) + 1);
        } else {
            return date('Ymd') . '001';
        }
    }
}
