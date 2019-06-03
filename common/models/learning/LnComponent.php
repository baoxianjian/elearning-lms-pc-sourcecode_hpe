<?php

namespace common\models\learning;

use Yii;
use \common\base\BaseActiveRecord;
use yii\caching\DbDependency;

/**
 * This is the model class for table "{{%ln_component}}".
 *
 * @property string $kid
 * @property string $component_code
 * @property string $component_type
 * @property string $component_category
 * @property string $transfer_type
 * @property string $title
 * @property string $description
 * @property string $icon
 * @property string $file_type
 * @property string $is_display_pc
 * @property string $is_display_mobile
 * @property string $is_allow_download
 * @property string $is_allow_reuse
 * @property string $is_need_upload
 * @property string $is_use_vendor
 * @property integer $default_time
 * @property integer $default_credit
 * @property string $feature_content
 * @property string $feature_content_type
 * @property string $window_mode
 * @property string $is_record_score
 * @property string $complete_rule
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
 * @property LnCourseware[] $lnCourseware
 */
class LnComponent extends BaseActiveRecord
{
    public $courseware;

    const RESOURCE_CODE = "0";
    const ACTIVE_CODE = "1";

    const COMPONENT_COURSE = "0";
    const COMPONENT_MEDIA = "1";
    const COMPONENT_ACTIVITY = "2";

    const DISPLAY_PC_NO = "0";
    const DISPLAY_PC_YES = "1";

    const DISPLAY_MOBILE_NO = "0";
    const DISPLAY_MOBILE_YES = "1";

    const ALLOW_DOWNLOAD_NO = "0";
    const ALLOW_DOWNLOAD_YES = "1";

    const COMPELET_RULE_BROW = "0";
    const COMPELET_RULE_SCORE = "1";

    const IS_RECORD_NO = "0";
    const IS_RECORD_YES = "1";

    const TRANSFER_TYPE_NORMAL = "0";
    const TRANSFER_TYPE_RTMP = "1";

    const FEATURE_CONTENT_TYPE_NONE = "0";
    const FEATURE_CONTENT_TYPE_FILENAME = "1";
    const FEATURE_CONTENT_TYPE_EXTENSION = "2";

    const WINDOW_MODE_SMALL = '0';
    const WINDOW_MODE_BIG = '1';

    const COMPONENT_CODE_SCORM = 'scorm'; //scorm
    const COMPONENT_CODE_BOOK = 'book'; //图书
    const COMPONENT_CODE_AICC = 'aicc'; //aicc
    const COMPONENT_CODE_INVESTIGATION = 'investigation'; //调查
    const COMPONENT_CODE_HOMEWORK = 'homework'; //作业
    const COMPONENT_CODE_EXAMINATION = 'examination'; //考试
    const COMPONENT_CODE_HTML = 'html'; //html插件

    const COMPLETE_RULE_BROWSE ="0";
    const COMPLETE_RULE_SCORE = "1";
    const COMPLETE_RULE_SUBMIT = "2";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_component}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['component_code', 'title', 'component_category', 'sequence_number','component_type'], 'required', 'on' => 'manage'],
            [['description'], 'safe'],
            [['sequence_number', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'component_code', 'component_type', 'component_category', 'file_type', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['title', 'icon','feature_content'], 'string', 'max' => 500],
            [['created_from','updated_from'], 'string', 'max' => 50],
            [['default_time', 'default_credit'], 'number'],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['complete_rule'], 'in', 'range' => [self::COMPLETE_RULE_BROWSE, self::COMPLETE_RULE_SCORE,self::COMPLETE_RULE_SUBMIT]],
            [['complete_rule'], 'default', 'value'=> self::COMPLETE_RULE_SCORE],

            [['is_record_score'], 'in', 'range' => [self::NO, self::YES]],
            [['is_record_score'], 'default', 'value' => self::YES],

            [['is_use_vendor'], 'in', 'range' => [self::NO, self::YES]],
            [['is_use_vendor'], 'default', 'value' => self::YES],

            [['is_deleted'], 'string', 'max' => 1],
            [['is_deleted'], 'in', 'range' => [self::DELETE_FLAG_NO, self::DELETE_FLAG_YES]],

            [['transfer_type'], 'string', 'max' => 1],
            [['transfer_type'], 'in', 'range' => [self::TRANSFER_TYPE_NORMAL, self::TRANSFER_TYPE_RTMP]],

            [['component_type'], 'string', 'max' => 1],
            [['component_type'], 'in', 'range' => [self::RESOURCE_CODE, self::COMPONENT_MEDIA, self::COMPONENT_ACTIVITY]],

            [['component_category'], 'string', 'max' => 1],
            [['component_category'], 'in', 'range' => [self::COMPONENT_COURSE, self::ACTIVE_CODE]],

            [['component_category'], 'string', 'max' => 1],
            [['component_category'], 'in', 'range' => [self::COMPONENT_COURSE, self::ACTIVE_CODE]],

            [['is_display_pc'], 'string', 'max' => 1],
            [['is_display_pc'], 'in', 'range' => [self::DISPLAY_PC_NO, self::DISPLAY_PC_YES]],
            [['is_display_pc'], 'default', 'value'=> self::DISPLAY_PC_YES],

            [['is_display_mobile'], 'string', 'max' => 1],
            [['is_display_mobile'], 'in', 'range' => [self::DISPLAY_MOBILE_NO, self::DISPLAY_MOBILE_YES]],
            [['is_display_mobile'], 'default', 'value'=> self::DISPLAY_MOBILE_YES],

            [['is_allow_download'], 'string', 'max' => 1],
            [['is_allow_download'], 'in', 'range' => [self::ALLOW_DOWNLOAD_NO, self::ALLOW_DOWNLOAD_YES]],
            [['is_allow_download'], 'default', 'value'=> self::ALLOW_DOWNLOAD_YES],

            [['is_allow_reuse'], 'string', 'max' => 1],
            [['is_allow_reuse'], 'in', 'range' => [self::NO, self::YES]],
            [['is_allow_reuse'], 'default', 'value'=> self::YES],

            [['is_need_upload'], 'string', 'max' => 1],
            [['is_need_upload'], 'in', 'range' => [self::NO, self::YES]],
            [['is_need_upload'], 'default', 'value'=> self::YES],

            [['feature_content_type'], 'string', 'max' => 1],
            [['feature_content_type'], 'in', 'range' => [self::FEATURE_CONTENT_TYPE_NONE, self::FEATURE_CONTENT_TYPE_FILENAME, self::FEATURE_CONTENT_TYPE_EXTENSION]],
            [['feature_content_type'], 'default', 'value'=> self::FEATURE_CONTENT_TYPE_NONE],

            [['window_mode'], 'string', 'max' => 1],
            [['window_mode'], 'in', 'range' => [self::WINDOW_MODE_SMALL, self::WINDOW_MODE_BIG]],
            [['window_mode'], 'default', 'value'=> self::WINDOW_MODE_BIG],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'kid'),
            'component_code' => Yii::t('common', 'component_code'),
            'component_category' => Yii::t('common', 'component_category'),
            'component_type' => Yii::t('common', 'component_type'),
            'title' => Yii::t('common', 'title'),
            'description' => Yii::t('common', 'description'),
            'icon' => Yii::t('common', 'icon'),
            'file_type' => Yii::t('common', 'file_type'),
            'transfer_type' => Yii::t('common', 'transfer_type'),
            'is_display_pc' => Yii::t('common', 'is_display_pc'),
            'is_display_mobile' => Yii::t('common', 'is_display_mobile'),
            'is_allow_download' => Yii::t('common', 'is_allow_download'),
            'default_time' => Yii::t('common', 'default_time'),
            'default_credit' => Yii::t('common', 'component_default_credit'),
            'sequence_number' => Yii::t('common', 'sequence_number'),
            'feature_content' => Yii::t('common', 'feature_content'),
            'feature_content_type' => Yii::t('common', 'feature_content_type'),
            'window_mode' => Yii::t('common', 'window_mode'),
            'is_record_score' => Yii::t('common', 'is_record_score'),
            'complete_rule' => Yii::t('common', 'complete_rule'),
            'is_allow_reuse' => Yii::t('common', 'is_allow_reuse'),
            'is_need_upload' => Yii::t('common', 'is_need_upload'),
            'is_use_vendor' => Yii::t('common', 'is_use_vendor'),
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


    public function getComponentTypeText(){
        $type = $this->component_type;
        if ($type == self::RESOURCE_CODE)
            return Yii::t('common', 'resource');
        else  if ($type == self::ACTIVE_CODE)
            return Yii::t('common', 'active');
    }

    public function getComponentCategoryText(){
        $type = $this->component_category;
        if ($type == self::COMPONENT_COURSE)
            return Yii::t('common', 'component_course');
        else  if ($type == self::COMPONENT_MEDIA)
            return Yii::t('common', 'component_media');
        else  if ($type == self::COMPONENT_ACTIVITY)
            return Yii::t('common', 'component_activity');
    }

    public function getDisplayPCText()
    {
        $result = $this->is_display_pc;
        if ($result == self::DISPLAY_PC_NO)
            return Yii::t('common', 'display_pc_no');
        else  if ($result == self::DISPLAY_PC_YES)
            return Yii::t('common', 'display_pc_yes');
    }

    public function getDisplayMobileText()
    {
        $result = $this->is_display_mobile;
        if ($result == self::DISPLAY_MOBILE_NO)
            return Yii::t('common', 'display_mobile_no');
        else  if ($result == self::DISPLAY_MOBILE_YES)
            return Yii::t('common', 'display_mobile_yes');
    }

    public function getAllowDownloadText()
    {
        $result = $this->is_allow_download;
        if ($result == self::ALLOW_DOWNLOAD_NO)
            return Yii::t('common', 'no');
        else  if ($result == self::ALLOW_DOWNLOAD_YES)
            return Yii::t('common', 'yes');
    }

    public function getTransferTypeText()
    {
        $result = $this->transfer_type;
        if ($result == self::TRANSFER_TYPE_NORMAL)
            return Yii::t('common', 'transfer_type_normal');
        else  if ($result == self::TRANSFER_TYPE_RTMP)
            return Yii::t('common', 'transfer_type_rtmp');
    }

    public function getNeedUploadText()
    {
        $result = $this->is_need_upload;
        if ($result == self::NO)
            return Yii::t('common', 'no');
        else  if ($result == self::YES)
            return Yii::t('common', 'yes');
    }

    public function getAllowReuseText()
    {
        $result = $this->is_allow_reuse;
        if ($result == self::NO)
            return Yii::t('common', 'no');
        else  if ($result == self::YES)
            return Yii::t('common', 'yes');
    }

    public function getFeatureContentTypeText()
    {
        $result = $this->feature_content_type;
        if ($result == self::FEATURE_CONTENT_TYPE_NONE)
            return Yii::t('common', 'feature_content_type_none');
        else if ($result == self::FEATURE_CONTENT_TYPE_FILENAME)
            return Yii::t('common', 'feature_content_type_filename');
        else if ($result == self::FEATURE_CONTENT_TYPE_EXTENSION)
            return Yii::t('common', 'feature_content_type_extension');
    }

    public function getWindowModeText()
    {
        $result = $this->window_mode;
        if ($result == self::WINDOW_MODE_BIG)
            return Yii::t('common', 'window_mode_big');
        else if ($result == self::WINDOW_MODE_SMALL)
            return Yii::t('common', 'window_mode_small');
    }

    public function getCompleteRuleText()
    {
        $result = $this->complete_rule;
        if ($result == self::COMPLETE_RULE_BROWSE)
            return Yii::t('common', 'complete_rule_browse');
        else if ($result == self::COMPLETE_RULE_SCORE)
            return Yii::t('common', 'complete_rule_score');
    }

    public function getIsRecordScoreText()
    {
        $result = $this->is_record_score;
        if ($result == self::NO)
            return Yii::t('common', 'no');
        else
            return Yii::t('common', 'yes');
    }

    public function getIsUseVendorText()
    {
        $result = $this->is_use_vendor;
        if ($result == self::NO)
            return Yii::t('common', 'no');
        else
            return Yii::t('common', 'yes');
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCoursewares()
    {
        return $this->hasMany(LnCourseware::className(), ['component_id' => 'kid'])
            ->onCondition([LnCourseware::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

}
