<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_user_display_info}}".
 *
 * @property string $kid
 * @property string $user_id
 * @property string $nick_name
 * @property string $user_name
 * @property string $real_name
 * @property string $company_id
 * @property string $company_name
 * @property string $orgnization_id
 * @property string $orgnization_name
 * @property string $position_id
 * @property string $position_name
 * @property string $domain_id
 * @property string $domain_name
 * @property string $reporting_manager_id
 * @property string $reporting_manager_name
 * @property string $cost_center_id
 * @property string $cost_center_name
 * @property string $email
 * @property string $user_no
 * @property string $gender
 * @property string $birthday
 * @property string $id_number
 * @property string $theme
 * @property string $status
 * @property string $user_type
 * @property string $mobile_no
 * @property string $home_phone_no
 * @property string $language
 * @property string $timezone
 * @property string $thumb
 * @property string $location
 * @property string $telephone_no
 * @property string $employee_status
 * @property string $employee_status_txt
 * @property string $onboard_day
 * @property string $rank
 * @property string $rank_txt
 * @property string $work_place
 * @property string $work_place_txt
 * @property string $position_mgr_level
 * @property string $position_mgr_level_txt
 * @property string $manager_flag
 * @property integer $valid_start_at
 * @property integer $valid_end_at
 * @property integer $user_sequence_number
 * @property integer $org_sequence_number
 * @property string $remark
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
 * @property FwUser $fwUser
 */
class FwUserDisplayInfo extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_user_display_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_name', 'real_name', 'company_id', 'orgnization_id', 'position_id'], 'required'],
            [['birthday', 'onboard_day'], 'safe'],
            [['valid_start_at', 'valid_end_at', 'user_sequence_number', 'org_sequence_number', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'user_id', 'nick_name', 'company_id', 'company_name', 'orgnization_id', 'orgnization_name', 'domain_id', 'domain_name', 'reporting_manager_id', 'reporting_manager_name', 'cost_center_id', 'cost_center_name', 'id_number', 'language', 'timezone', 'location', 'employee_status_txt', 'rank', 'rank_txt', 'work_place', 'work_place_txt', 'position_mgr_level_txt', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['user_name', 'real_name', 'email'], 'string', 'max' => 255],
            [['position_id', 'position_name', 'thumb', 'remark'], 'string', 'max' => 500],
            [['user_no', 'gender', 'theme', 'mobile_no', 'home_phone_no', 'telephone_no'], 'string', 'max' => 30],
            [['status', 'user_type', 'manager_flag', 'is_deleted'], 'string', 'max' => 1],
            [['employee_status'], 'string', 'max' => 2],
            [['position_mgr_level'], 'string', 'max' => 5]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'kid'),
            'user_id' => Yii::t('common', 'user_id'),
            'nick_name' => Yii::t('common', 'nick_name'),
            'user_name' => Yii::t('common', 'user_name'),
            'real_name' => Yii::t('common', 'real_name'),
            'company_id' => Yii::t('common', 'company_id'),
            'company_name' => Yii::t('common', 'company_name'),
            'orgnization_id' => Yii::t('common', 'orgnization_id'),
            'orgnization_name' => Yii::t('common', 'orgnization_name'),
            'position_id' => Yii::t('common', 'position_id'),
            'position_name' => Yii::t('common', 'position_name'),
            'domain_id' => Yii::t('common', 'domain_id'),
            'domain_name' => Yii::t('common', 'domain_name'),
            'reporting_manager_id' => Yii::t('common', 'reporting_manager_id'),
            'reporting_manager_name' => Yii::t('common', 'reporting_manager_name'),
            'cost_center_id' => Yii::t('common', 'cost_center_id'),
            'cost_center_name' => Yii::t('common', 'cost_center_name'),
            'email' => Yii::t('common', 'email'),
            'user_no' => Yii::t('common', 'user_no'),
            'gender' => Yii::t('common', 'gender'),
            'birthday' => Yii::t('common', 'birthday'),
            'id_number' => Yii::t('common', 'id_number'),
            'theme' => Yii::t('common', 'theme'),
            'status' => Yii::t('common', 'status'),
            'user_type' => Yii::t('common', 'user_type'),
            'mobile_no' => Yii::t('common', 'mobile_no'),
            'home_phone_no' => Yii::t('common', 'home_phone_no'),
            'language' => Yii::t('common', 'language'),
            'timezone' => Yii::t('common', 'timezone'),
            'thumb' => Yii::t('common', 'thumb'),
            'location' => Yii::t('common', 'location'),
            'telephone_no' => Yii::t('common', 'telephone_no'),
            'employee_status' => Yii::t('common', 'employee_status'),
            'employee_status_txt' => Yii::t('common', 'employee_status_txt'),
            'onboard_day' => Yii::t('common', 'onboard_day'),
            'rank' => Yii::t('common', 'rank'),
            'rank_txt' => Yii::t('common', 'rank_txt'),
            'work_place' => Yii::t('common', 'work_place'),
            'work_place_txt' => Yii::t('common', 'work_place_txt'),
            'position_mgr_level' => Yii::t('common', 'position_mgr_level'),
            'position_mgr_level_txt' => Yii::t('common', 'position_mgr_level_txt'),
            'manager_flag' => Yii::t('common', 'manager_flag'),
            'valid_start_at' => Yii::t('common', 'valid_start_at'),
            'valid_end_at' => Yii::t('common', 'valid_end_at'),
            'user_sequence_number' => Yii::t('common', 'user_sequence_number'),
            'org_sequence_number' => Yii::t('common', 'org_sequence_number'),
            'remark' => Yii::t('common', 'remark'),
            'version' => Yii::t('common', 'version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_ip' => Yii::t('common', 'created_ip'),
            'created_from' => Yii::t('common', 'created_from'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_ip' => Yii::t('common', 'updated_ip'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'is_deleted' => Yii::t('common', 'is_deleted'),
        ];
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
