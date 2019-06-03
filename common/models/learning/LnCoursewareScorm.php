<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_courseware_scorm}}".
 *
 * @property string $kid
 * @property string $scorm_version
 * @property string $launch_scorm_sco_id
 * @property string $display_activity_name
 * @property string $display_structure_player
 * @property string $display_structure_entry
 * @property string $skip_structure_view
 * @property string $display_attempt_status
 * @property string $disable_preview
 * @property integer $width
 * @property integer $height
 * @property string $nav_display
 * @property integer $nav_position_top
 * @property integer $nav_position_left
 * @property integer $max_attempt
 * @property string $force_new_attempt
 * @property string $force_completed
 * @property string $auto_continue
 * @property string $auto_commit
 * @property string $total_score
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnCourseware $lnCourseware
 * @property LnScormScoes[] $lnScormScoes
 */
class LnCoursewareScorm extends BaseActiveRecord
{
    const DISPLAY_ACTIVITY_NAME_NO = "0";
    const DISPLAY_ACTIVITY_NAME_YES = "1";

    const DISPLAY_STRUCTURE_PLAYER_NO = "0";
    const DISPLAY_STRUCTURE_PLAYER_YES = "1";

    const DISPLAY_STRUCTURE_ENTRY_NO = "0";
    const DISPLAY_STRUCTURE_ENTRY_YES = "1";

    const SKIP_STRUCTURE_VIEW_NO = "0";
    const SKIP_STRUCTURE_VIEW_YES = "0";

    const DISPLAY_ATTEMPT_STATUS_NO = "0";
    const DISPLAY_ATTEMPT_STATUS_YES = "1";

    const DISABLE_PREVIEW_NO = "0";
    const DISABLE_PREVIEW_YES = "1";

    const NAV_DISPLAY_NO = "0";
    const NAV_DISPLAY_YES = "1";

    const MAX_ATTEMPT_NO = "0";
    const MAX_ATTEMPT_YES = "1";

    const FORCE_NEW_ATTEMPT_NO = "0";
    const FORCE_NEW_ATTEMPT_YES = "1";

    const FORCE_COMPLETED_NO = "0";
    const FORCE_COMPLETED_YES = "1";

    const AUTO_CONTINUE_NO = "0";
    const AUTO_CONTINUE_YES = "1";

    const AUTO_COMMIT_NO = "0";
    const AUTO_COMMIT_YES = "1";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_courseware_scorm}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            [['courseware_id'], 'required'],
            [['width', 'height', 'nav_position_top', 'nav_position_left', 'max_attempt', 'created_at', 'updated_at'], 'integer'],
            [['total_score'], 'number'],
            [['kid', 'launch_scorm_sco_id', 'scorm_version', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['display_activity_name', 'display_structure_player', 'display_structure_entry', 'skip_structure_view',
                'display_attempt_status', 'disable_preview', 'nav_display', 'force_new_attempt', 'force_completed',
                'auto_continue', 'auto_commit', 'is_deleted'], 'string', 'max' => 1],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['display_activity_name'], 'in', 'range' => [self::DISPLAY_ACTIVITY_NAME_NO, self::DISPLAY_ACTIVITY_NAME_YES]],
            [['display_activity_name'], 'default', 'value'=> self::DISPLAY_ACTIVITY_NAME_YES],

            [['display_structure_player'], 'in', 'range' => [self::DISPLAY_STRUCTURE_PLAYER_NO, self::DISPLAY_STRUCTURE_PLAYER_YES]],
            [['display_structure_player'], 'default', 'value'=> self::DISPLAY_STRUCTURE_PLAYER_YES],

            [['display_structure_entry'], 'in', 'range' => [self::DISPLAY_STRUCTURE_ENTRY_NO, self::DISPLAY_STRUCTURE_ENTRY_YES]],
            [['display_structure_entry'], 'default', 'value'=> self::DISPLAY_STRUCTURE_ENTRY_YES],

            [['skip_structure_view'], 'in', 'range' => [self::SKIP_STRUCTURE_VIEW_NO, self::SKIP_STRUCTURE_VIEW_YES]],
            [['skip_structure_view'], 'default', 'value'=> self::SKIP_STRUCTURE_VIEW_NO],

            [['display_attempt_status'], 'in', 'range' => [self::DISPLAY_ATTEMPT_STATUS_NO, self::DISPLAY_ATTEMPT_STATUS_YES]],
            [['display_attempt_status'], 'default', 'value'=> self::DISPLAY_ATTEMPT_STATUS_YES],

            [['disable_preview'], 'in', 'range' => [self::DISABLE_PREVIEW_NO, self::DISABLE_PREVIEW_YES]],
            [['disable_preview'], 'default', 'value'=> self::DISABLE_PREVIEW_NO],

            [['width'], 'default', 'value'=> 0],
            [['height'], 'default', 'value'=> 0],

            [['nav_display'], 'in', 'range' => [self::NAV_DISPLAY_NO, self::NAV_DISPLAY_YES]],
            [['nav_display'], 'default', 'value'=> self::NAV_DISPLAY_YES],

            [['nav_position_top'], 'default', 'value'=> 0],
            [['nav_position_left'], 'default', 'value'=> 0],
            [['max_attempt'], 'default', 'value'=> 0],

            [['force_new_attempt'], 'in', 'range' => [self::FORCE_NEW_ATTEMPT_NO, self::FORCE_NEW_ATTEMPT_YES]],
            [['force_new_attempt'], 'default', 'value'=> self::FORCE_NEW_ATTEMPT_YES],

            [['force_completed'], 'in', 'range' => [self::FORCE_COMPLETED_NO, self::FORCE_COMPLETED_YES]],
            [['force_completed'], 'default', 'value'=> self::FORCE_COMPLETED_NO],

            [['auto_continue'], 'in', 'range' => [self::AUTO_CONTINUE_NO, self::AUTO_CONTINUE_YES]],
            [['auto_continue'], 'default', 'value'=> self::AUTO_CONTINUE_NO],

            [['auto_commit'], 'in', 'range' => [self::AUTO_COMMIT_NO, self::AUTO_COMMIT_YES]],
            [['auto_commit'], 'default', 'value'=> self::AUTO_COMMIT_NO],

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
            'scorm_version' => Yii::t('common', 'scorm_version'),
            'display_activity_name' => Yii::t('common', 'display_activity_name'),
            'display_structure_player' => Yii::t('common', 'display_structure_player'),
            'display_structure_entry' => Yii::t('common', 'display_structure_entry'),
            'skip_structure_view' => Yii::t('common', 'skip_structure_view'),
            'display_attempt_status' => Yii::t('common', 'display_attempt_status'),
            'disable_preview' => Yii::t('common', 'disable_preview'),
            'width' => Yii::t('common', 'width'),
            'height' => Yii::t('common', 'height'),
            'nav_display' => Yii::t('common', 'nav_display'),
            'nav_position_top' => Yii::t('common', 'nav_position_top'),
            'nav_position_left' => Yii::t('common', 'nav_position_left'),
            'max_attempt' => Yii::t('common', 'max_attempt'),
            'force_new_attempt' => Yii::t('common', 'force_new_attempt'),
            'force_completed' => Yii::t('common', 'force_completed'),
            'auto_continue' => Yii::t('common', 'auto_continue'),
            'auto_commit' => Yii::t('common', 'auto_commit'),
            'total_score' => Yii::t('common', 'total_score'),
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
    public function getLnScormScoes()
    {
        return $this->hasMany(LnScormScoes::className(), ['scorm_id' => 'kid'])
            ->onCondition([LnScormScoes::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
